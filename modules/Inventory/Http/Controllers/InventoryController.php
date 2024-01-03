<?php

namespace Modules\Inventory\Http\Controllers;

use Exception;
//use App\Models\Tenant\Item;
use Illuminate\Http\Request;
use Modules\Item\Models\ItemLot;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Modules\Item\Models\ItemLotsGroup;
use Modules\Inventory\Models\Inventory;
use Modules\Inventory\Models\Warehouse;
use Modules\Inventory\Models\ItemWarehouse;
use Modules\Inventory\Traits\InventoryTrait;
use Modules\Inventory\Models\InventoryKardex;
use Modules\Inventory\Models\InventoryTransaction;
use Modules\Inventory\Http\Requests\InventoryRequest;
use Modules\Inventory\Http\Resources\InventoryResource;
use Modules\Inventory\Http\Resources\InventoryCollection;
use App\Imports\StockImport;
use App\Mail\Tenant\InventoryEmail;
use App\Models\Tenant\AccountingEntries;
use App\Models\Tenant\AccountingEntryItems;
use App\Models\Tenant\Company;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Inventory as TenantInventory;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Excel;
use Illuminate\Support\Str;
use App\Models\Tenant\Item;
use Modules\Production\Models\Production;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Swift_Mailer;
use Swift_SmtpTransport;
use Modules\Item\Models\Category;


class InventoryController extends Controller
{
	use InventoryTrait;

	public function index()
	{
		return view('inventory::inventory.index');
	}

	public function columns()
	{
		$columns = [
			'name' => 'Nombre Producto',
			'internal_id' => 'Código interno',
			'warehouse'   => 'Almacén',
			'category_id_array'   => 'Categoria',

		];
		$categories_list = Category::get();

		return compact('columns', 'categories_list');
	}

	public function records(Request $request)
	{
		$column = $request->input('column');
		Log::info('column'.$column);

		if ($column == 'warehouse') {
			//Log::info('entro al if');
			$records = ItemWarehouse::with(['item', 'warehouse'])
							->whereHas('item', function ($query) use ($request) {
								$query->where('unit_type_id', '!=', 'ZZ');
								$query->whereNotIsSet();
							})
							->whereHas('warehouse', function ($query) use ($request) {
								$query->where('description', 'like', '%' . $request->value . '%');
							})
							->orderBy('item_id');
		}
		else
		{
			//Log::info('Entro en else '.$request);
			$records = $this->getCommonRecords($request);
		}

		return new InventoryCollection($records->paginate(config('tenant.items_per_page')));
	}

	/**
	 *
	 * Obtener registros
	 *
	 * @param  Request $request
	 * @return ItemWarehouse
	 */
	public function getCommonRecords($request)
	{
		//Log::info('Entro aqui'.$request->column.'-'.$request->value);
		return ItemWarehouse::with(['item', 'warehouse'])
							->whereHas('item', function ($query) use ($request) {
								$query->where('unit_type_id', '!=', 'ZZ');
								$query->whereNotIsSet();
								$query->where($request->column, 'like', '%' . str_replace('%2F','',$request->value) . '%');
							})
							->orderBy('item_id');
	}

	public function tables()
	{
		return [
			'items'      => $this->optionsItem(),
			'warehouses' => $this->optionsWarehouse(),
            'inventory_transactions' => InventoryTransaction::get(),
		];
	}

	public function record($id)
	{
		if (is_numeric($id)) {
			$record = new InventoryResource(ItemWarehouse::with(['item', 'warehouse'])->findOrFail($id));
		} else {
			request()->validate([
				'ids' => 'required|array|min:1'
			]);
			$data = ItemWarehouse::with(['item', 'warehouse'])
				->whereIn('id', request('ids'))
				->get();

			$record = InventoryResource::collection($data);
		}

		return $record;
	}

	public function tables_transaction($type)
	{
		return [
			//            'items' => $this->optionsItemFull(),
			'warehouses'             => $this->optionsWarehouse(),
			'inventory_transactions' => $this->optionsInventoryTransaction($type),
			'production_finalizada'  => Production::where('state_type_id', '03')->get(),
		];
	}

	public function filterProductionDate($filter_date)
	{
		$dates = Production::where('date_end', $filter_date)->get();
		return $dates;
	}

	public function searchItems(Request $request)
	{
		//Log::info('search - '.$request->value);
		$search = $request->input('search');
		//$print = $this->optionsItemFull($search, 20);
		//Log::info('print'.$search);

		return [
			'items' => $this->optionsItemFull($request, 20),
		];
	}

	public function ExtraDataList()
    {
        return view('inventory::extra_info.index');
   }

    public function store(Request $request)
	{
		$result = DB::connection('tenant')->transaction(function () use ($request) {
			$item_id = $request->input('item_id');
			$warehouse_id = $request->input('warehouse_id');
			$quantity = $request->input('quantity');

			$item_warehouse = ItemWarehouse::firstOrNew(['item_id' => $item_id,
				'warehouse_id'                                        => $warehouse_id]);
			if ($item_warehouse->id) {
				return [
					'success' => false,
					'message' => 'El producto ya se encuentra registrado en el almacén indicado.'
				];
			}

			// $item_warehouse->stock = $quantity;
			// $item_warehouse->save();

			$inventory = new Inventory();
			$inventory->type = 1;
			$inventory->description = 'Stock inicial';
			$inventory->item_id = $item_id;
			$inventory->warehouse_id = $warehouse_id;
			$inventory->quantity = $quantity;
			$inventory->save();

			return  [
				'success' => true,
				'message' => 'Producto registrado en almacén'
			];
		});

		return $result;
	}

	public function store_transaction(InventoryRequest $request)
	{
		$result = DB::connection('tenant')->transaction(function () use ($request) {
			// dd($request->all());
			$type = $request->input('type');
			$item_id = $request->input('item_id');
			$warehouse_id = $request->input('warehouse_id');
			$inventory_transaction_id = $request->input('inventory_transaction_id');
			$quantity = $request->input('quantity');
			$lot_code = $request->input('lot_code');
			$comments = $request->input('comments');
			$created_at = $request->input('created_at');
			$precio_perso = $request->input('purchase_mean_price');
			//Log::info('datos '.$request);
			$production_id = $request->input('production_id');
			//Log::info('precio_perso'.$precio_perso);

			$lots = ($request->has('lots')) ? $request->input('lots') : [];

			$item_warehouse = ItemWarehouse::firstOrNew(['item_id' => $item_id,
				'warehouse_id'                                        => $warehouse_id]);

			$inventory_transaction = InventoryTransaction::findOrFail($inventory_transaction_id);

			if ($type == 'output' && ($quantity > $item_warehouse->stock)) {
				return  [
					'success' => false,
					'message' => 'La cantidad no puede ser mayor a la que se tiene en el almacén.'
				];
			}

			$item = Item::where('id', $item_id)->first();

			$costoA = $item->purchase_mean_cost;
			$stockA = $item->stock;
			$totalA = $costoA * $stockA;

			$inventory = new Inventory();
			$inventory->type = null;
			$inventory->description = $inventory_transaction->name;
			$inventory->item_id = $item_id;
			$inventory->warehouse_id = $warehouse_id;
			$inventory->quantity = $quantity;
			$inventory->inventory_transaction_id = $inventory_transaction_id;
			$inventory->lot_code = $lot_code;
			$inventory->comments = $comments;
			$inventory->precio_perso = $precio_perso;
			$inventory->production_id= $production_id;

			if($created_at) {
			  $inventory->date_of_issue = $created_at;
			}

            //Log::info("ACTUAL " . $costoA . '-' . $totalA . ' NUEVO: ' . $costoN . "-" . $totalN);

			$inventory->save();

			$lots_enabled = isset($request->lots_enabled) ? $request->lots_enabled : false;

			if ($type == 'input') {
				foreach ($lots as $lot) {

					$inventory->lots()->create([
						'date'         => $lot['date'],
						'series'       => $lot['series'],
						'item_id'      => $item_id,
						'warehouse_id' => $warehouse_id,
						'has_sale'     => false,
						'state'        => $lot['state'],
					]);
				}

				if ($lots_enabled) {
					ItemLotsGroup::create([
						'code'         => $lot_code,
						'quantity'     => $quantity,
						'date_of_due'  => $request->date_of_due,
						'item_id'      => $item_id
					]);
				}
			} else {
				foreach ($lots as $lot) {
					if ($lot['has_sale']) {
						$item_lot = ItemLot::findOrFail($lot['id']);
						// $item_lot->delete();
						$item_lot->has_sale = true;
						$item_lot->state = 'Inactivo';
						$item_lot->save();
					}
				}

				if (isset($request->IdLoteSelected)) {
					$lot = ItemLotsGroup::find($request->IdLoteSelected);
					$lot->quantity = ($lot->quantity - $quantity);
					$lot->save();
				}
			}

            $this->createAccountingEntryTransactions($inventory,$inventory_transaction, $totalA, $stockA, $item);
            $this->generatePDF($inventory->id,$type);

			return  [
				'success' => true,
				'message' => ($type == 'input') ? 'Ingreso registrado correctamente' : 'Salida registrada correctamente',
                'id' => $inventory->id,
                'email' => 'carlos.farias@joinec.net'
            ];
		});

		return $result;
	}
    //CREAMOS EL ASIENTO CONTABLE DE UN INGRESO O SALIDA POR AJUSTE
    public function createAccountingEntryTransactions($inventory,$transaction, $totalA = null, $stockA = null, $item = null){

        try {

            $idauth = auth()->user()->id;
            $lista = AccountingEntries::where('user_id', '=', $idauth)->latest('id')->first();
            $ultimo = AccountingEntries::latest('id')->first();
            $configuration = Configuration::first();
			//Log::info('inventory'.$inventory);
			//Log::info('transaction'.$transaction);
			if($inventory->precio_perso == null)
			{
				//Log::info('Purchase mean cost');
				$valor = ($inventory->item->purchase_mean_cost * $inventory->quantity);
			}else{
				//Log::info('Precio Perso');

				//$item = Item::where('id', $item_id)->first();

				$costoN = floatval($inventory->precio_perso);
            	$stockN = floatval($inventory->quantity);
            	$totalN = $costoN * $stockN;

            	$stockT = $stockN + $stockA;
            	$costoT = $totalA + $totalN;
            	$costoT = round($costoT / $stockT, 4);

				$item->purchase_mean_cost = $costoT;

            	$item->save();

				$valor = ($inventory->precio_perso * $inventory->quantity);

			}
            //$valor = ($inventory->item->purchase_mean_cost * $inventory->quantity);
            if($valor < 0){
                $valor = ($valor * -1);
            }

            if (empty($lista)) {
                $seat = 1;
            } else {

                $seat = $lista->seat + 1;
            }

            if (empty($ultimo)) {
                $seat_general = 1;
            } else {
                $seat_general = $ultimo->seat_general + 1;
            }

            $comment = ($transaction->type == 'input')?'Ingreso de producto '.$inventory->item->description:'Salida de producto '.$inventory->item->description;

            $cabeceraC = new AccountingEntries();
            $cabeceraC->user_id = $idauth;
            $cabeceraC->seat = $seat;
            $cabeceraC->seat_general = $seat_general;
            $cabeceraC->seat_date = date('Y-m-d');
            $cabeceraC->types_accounting_entrie_id = 1;
            $cabeceraC->comment = $comment;
            $cabeceraC->serie = null;
            $cabeceraC->number = $seat;
            $cabeceraC->total_debe = $valor;
            $cabeceraC->total_haber = $valor;
            $cabeceraC->revised1 = 0;
            $cabeceraC->user_revised1 = 0;
            $cabeceraC->revised2 = 0;
            $cabeceraC->user_revised2 = 0;
            $cabeceraC->currency_type_id = $configuration->currency_type_id;
            $cabeceraC->doctype = 10;
            $cabeceraC->is_client = false;
            $cabeceraC->establishment_id = null;
            $cabeceraC->establishment = $inventory->warehouse;
            $cabeceraC->prefix = 'ASC';
            $cabeceraC->person_id = null;
            $cabeceraC->external_id = Str::uuid()->toString();
            $cabeceraC->document_id = 'AS' . $inventory->id;

            $cabeceraC->save();
            $cabeceraC->filename = 'ASC-' . $cabeceraC->id . '-' . date('Ymd');
            $cabeceraC->save();

            $arrayEntrys = [];
            $n = 1;

            $debeGlobal = 0;

            $cuentaPerson = null;
            $cuentaAnticipo = null;
            //Log::info($inventory->item);
            $cuentaItem = ($inventory->item->purchase_cta)?$inventory->item->purchase_cta:$configuration->cta_purchases;
            $cuentaMotivo = $transaction->cta_account;

            //Log::info($cuentaItem.' - '.$cuentaMotivo);

            $detalle = new AccountingEntryItems();
            $detalle->accounting_entrie_id = $cabeceraC->id;
            $detalle->account_movement_id = ($transaction->type == 'input')?$cuentaItem:$cuentaMotivo;
            $detalle->seat_line = 1;
            $detalle->debe = $valor;
            $detalle->haber =0;
            $detalle->save();

            $detalle2 = new AccountingEntryItems();
            $detalle2->accounting_entrie_id = $cabeceraC->id;
            $detalle2->account_movement_id = ($transaction->type == 'input')?$cuentaMotivo:$cuentaItem;
            $detalle2->seat_line = 2;
            $detalle2->debe = 0;
            $detalle2->haber = $valor;
            $detalle2->save();

        } catch (Exception $ex) {

            Log::error('Error al intentar generar el asiento contable de transaccion');
            Log::error($ex->getMessage());
        }

    }

	public function moveMultiples(Request $request)
	{
        $request->validate([
            'items' => 'required|array'
        ]);

		DB::connection('tenant')->beginTransaction();
		try {
			$items = $request->items;
			foreach ($items as $item) {
				$item_id = $item['item_id'];
				$warehouse_id = $item['warehouse_id'];
				$warehouse_new_id = $item['warehouse_new_id'];
				$quantity = $item['quantity'];
				$quantity_move = $item['quantity_move'];
				$detail = $item['detail'];
				if ($quantity_move <= 0) {
					throw new Exception("La cantidad del producto {$item['item_description']} a trasladar debe ser mayor a 0", 500);
				}

				if ($warehouse_id === $warehouse_new_id) {
					throw new Exception("El almacén destino del producto {$item['item_description']} no puede ser igual al de origen", 500);
				}
				if ($quantity < $quantity_move) {
					throw new Exception("La cantidad a trasladar del producto {$item['item_description']} no puede ser mayor al que se tiene en el almacén.", 500);
				}

				$inventory = new Inventory();
				$inventory->type = 2;
				$inventory->description = 'Traslado';
				$inventory->item_id = $item_id;
				$inventory->warehouse_id = $warehouse_id;
				$inventory->warehouse_destination_id = $warehouse_new_id;
				$inventory->quantity = $quantity_move;
				$inventory->detail = $detail;

				$inventory->save();
			}
			DB::connection('tenant')->commit();

			return response()->json([
				'success' => true,
				'message' => 'Productos trasladados con éxito'
			], 200);
		} catch (\Throwable $th) {
            DB::connection('tenant')->rollBack();

			return response()->json([
				'success' => false,
				'message' => $th->getMessage(),
			], 500);
		}
	}

	public function move(Request $request)
	{
		$result = DB::connection('tenant')->transaction(function () use ($request) {
			$id = $request->input('id');
			$item_id = $request->input('item_id');
			$warehouse_id = $request->input('warehouse_id');
			$warehouse_new_id = $request->input('warehouse_new_id');
			$quantity = $request->input('quantity');
			$quantity_move = $request->input('quantity_move');
			$lots = ($request->has('lots')) ? $request->input('lots') : [];
			$detail = $request->input('detail');

			if ($quantity_move <= 0) {
				return  [
					'success' => false,
					'message' => 'La cantidad a trasladar debe ser mayor a 0'
				];
			}

			if ($warehouse_id === $warehouse_new_id) {
				return  [
					'success' => false,
					'message' => 'El almacén destino no puede ser igual al de origen'
				];
			}
			if ($quantity < $quantity_move) {
				return  [
					'success' => false,
					'message' => 'La cantidad a trasladar no puede ser mayor al que se tiene en el almacén.'
				];
			}

			$inventory = new Inventory();
			$inventory->type = 2;
			$inventory->description = 'Traslado';
			$inventory->item_id = $item_id;
			$inventory->warehouse_id = $warehouse_id;
			$inventory->warehouse_destination_id = $warehouse_new_id;
			$inventory->quantity = $quantity_move;
			$inventory->detail = $detail;

			$inventory->save();

			foreach ($lots as $lot) {
				if ($lot['has_sale']) {
					$item_lot = ItemLot::findOrFail($lot['id']);
					$item_lot->warehouse_id = $inventory->warehouse_destination_id;
					$item_lot->update();
				}
			}

			return  [
				'success' => true,
				'message' => 'Producto trasladado con éxito'
			];
		});

		return $result;
	}

	public function remove(Request $request)
	{
		$result = DB::connection('tenant')->transaction(function () use ($request) {
			// dd($request->all());
			$item_id = $request->input('item_id');
			$warehouse_id = $request->input('warehouse_id');
			$quantity = $request->input('quantity');
			$quantity_remove = $request->input('quantity_remove');
			$lots = ($request->has('lots')) ? $request->input('lots') : [];

			//Transaction
			$item_warehouse = ItemWarehouse::where('item_id', $item_id)
										   ->where('warehouse_id', $warehouse_id)
										   ->first();
			if (!$item_warehouse) {
				return [
					'success' => false,
					'message' => 'El producto no se encuentra en el almacén indicado'
				];
			}

			if ($quantity < $quantity_remove) {
				return  [
					'success' => false,
					'message' => 'La cantidad a retirar no puede ser mayor al que se tiene en el almacén.'
				];
			}

			// $item_warehouse->stock = $quantity - $quantity_remove;
			// $item_warehouse->save();

			$inventory = new Inventory();
			$inventory->type = 3;
			$inventory->description = 'Retirar';
			$inventory->item_id = $item_id;
			$inventory->warehouse_id = $warehouse_id;
			$inventory->quantity = $quantity_remove;
			$inventory->save();

			foreach ($lots as $lot) {
				if ($lot['has_sale']) {
					$item_lot = ItemLot::findOrFail($lot['id']);
					$item_lot->delete();
				}
			}

			return  [
				'success' => true,
				'message' => 'Producto trasladado con éxito'
			];
		});

		return $result;
	}

	public function initialize()
	{
		$this->initializeInventory();
	}

	public function regularize_stock()
	{
		DB::connection('tenant')->transaction(function () {
			$item_warehouses = ItemWarehouse::get();

			foreach ($item_warehouses as $it_warehouse) {
				$inv_kardex = InventoryKardex::where([['item_id', $it_warehouse->item_id], ['warehouse_id', $it_warehouse->warehouse_id]])->sum('quantity');
				$it_warehouse->stock = $inv_kardex;
				$it_warehouse->save();
			}
		});

		return [
			'success' => true,
			'message' => 'Stock regularizado'
		];
	}

	public function stock(Request $request)
	{
		$result = DB::connection('tenant')->transaction(function () use ($request) {
			$id = $request->input('id');
			$item_id = $request->input('item_id');
			$warehouse_id = $request->input('warehouse_id');
			$quantity = $request->input('quantity');
			$quantity_real = $request->input('quantity_real');
			$lots = ($request->has('lots')) ? $request->input('lots') : [];

			if ($quantity_real <= 0) {
				return  [
					'success' => false,
					'message' => 'La cantidad de stock real debe ser mayor a 0'
				];
			}
			$type=1;
			$quantity_new=0;
			$quantity_new=$quantity_real-$quantity;
			if ($quantity_real<$quantity) {
				$quantity_new=$quantity-$quantity_real;
				$type=null;
			}

			$inventory = new Inventory();
			$inventory->type = $type;
			$inventory->description = 'Stock Real';
            $inventory->inventory_transaction_id = $request->inventory_transaction_id;
			$inventory->item_id = $item_id;
			$inventory->warehouse_id = $warehouse_id;
			$inventory->quantity = $quantity_new;

			if ($quantity_real<$quantity) {
				$inventory->inventory_transaction_id = 28;
			}

			$inventory->real_stock = $request->quantity_real;
			$inventory->system_stock = $request->quantity;

			$inventory->save();

			return  [
				'success' => true,
				'message' => 'Cantidad de stock actualizado con éxito',
                'id' => $inventory->id
			];
		});

        $inventory = Inventory::find($result['id']);
        $transaction = InventoryTransaction::find($inventory->inventory_transaction_id);
        $this->createAccountingEntryTransactions($inventory,$transaction);
        $this->generatePDF($inventory->id,'fix');
		return $result;

	}

	public function stockMultiples(Request $request)
	{
        $request->validate([
            'items' => 'required|array'
        ]);

		DB::connection('tenant')->beginTransaction();
		try {
			$items = $request->items;
			foreach ($items as $item) {
				$item_id = $item['item_id'];
				$warehouse_id = $item['warehouse_id'];
				$quantity = $item['quantity'];
				$quantity_real = $item['quantity_real'];
				if ($quantity_real <= 0) {
					throw new Exception("La cantidad del producto {$item['item_description']} a modificar debe ser mayor a 0", 500);
				}

				$type=1;
				$quantity_new=0;
				$quantity_new=$quantity_real-$quantity;
				if ($quantity_real<$quantity) {
					$quantity_new=$quantity-$quantity_real;
					$type=null;
				}

				$inventory = new Inventory();
				$inventory->type = $type;
				$inventory->description = 'STock Real';
				$inventory->item_id = $item_id;
				$inventory->warehouse_id = $warehouse_id;
				$inventory->quantity = $quantity_new;
				if ($quantity_real<$quantity) {
					$inventory->inventory_transaction_id = 28;
				}

				$inventory->real_stock = $item['quantity_real'];
				$inventory->system_stock = $item['quantity'];

				$inventory->save();

			}
			DB::connection('tenant')->commit();

			return response()->json([
				'success' => true,
				'message' => 'Cantidad de stock actualizado con éxito'
			], 200);
		} catch (\Throwable $th) {
            DB::connection('tenant')->rollBack();

			return response()->json([
				'success' => false,
				'message' => $th->getMessage(),
			], 500);
		}
	}

	public function import(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required|numeric|min:1'
        ]);
        if ($request->hasFile('file')) {
            try {
                $import = new StockImport();
                $import->import($request->file('file'), null, Excel::XLSX);
                $data = $import->getData();
                return [
                    'success' => true,
                    'message' =>  __('app.actions.upload.success'),
                    'data' => $data
                ];
            } catch (Exception $e) {
                return [
                    'success' => false,
                    'message' =>  $e->getMessage()
                ];
            }
        }
        return [
            'success' => false,
            'message' =>  __('app.actions.upload.error'),
        ];
    }

    public function generatePDF($id,$type){

		$user = auth()->user();
        $records = Inventory::find($id);
        $company = Company::first();
        $tipo = 'Ingreso';
        if($type == 'output'){
            $tipo = 'Salida';
        }
        if($type == 'fix'){
            $tipo = 'Ajuste';
        }
        $pdf = PDF::loadView('inventory::reports.inventory.report_inventory_pdf',compact('company','records','tipo', 'user'))
            ->setPaper('a4');

        $filename = 'INV-'.$id.'.pdf';

        Storage::disk('tenant')->put('inventory/pdf'.DIRECTORY_SEPARATOR.$filename, $pdf->stream());
    }

    public function print($id,$type){

        $records = Inventory::find($id);
		//Log::info('productionController - '.$records->production);
		$user = Auth()->user();
        $company = Company::first();
        $tipo = 'Ingreso';
        if($type == 'output'){
            $tipo = 'Salida';
        }
        if($type == 'fix'){
            $tipo = 'Ajuste';
        }

        $pdf = PDF::loadView('inventory::reports.inventory.report_inventory_pdf',compact('company','records','tipo', 'user'))
            ->setPaper('a4');

        $filename = $tipo.'_mercaderia_' . date('YmdHis');

        return $pdf->stream($filename . '.pdf');
    }

    public function email(Request $request){

        $email = $request->email;
        $mail = explode(';', str_replace([',', ' '], [';', ''], $email));
        $mails = [];
        if (!empty($mail) && count($mail) > 0) {
            foreach ($mail as $email) {
                $email = trim($email);
                if (!empty($email)) {
                    $mails[] = $email;
                }
            }
            $email= implode(';',$mails);
        }
        $email = explode(';',$email);
        $company = Company::first();
        $document = Inventory::find($request->id);
        $mailable = new InventoryEmail($document, $request->type, $company);
        Configuration::setConfigSmtpMail();

        // Backup your default mailer
        $backup = Mail::getSwiftMailer();
        $transport =  new Swift_SmtpTransport(Config::get('mail.host'), Config::get('mail.port'), Config::get('mail.encryption'));
        $transport->setUsername(Config::get('mail.username'));
        $transport->setPassword(Config::get('mail.password'));
        $mailer = new Swift_Mailer($transport);
        Mail::setSwiftMailer($mailer);
        Mail::to($email)->send($mailable);

        return [
            'success' => true
        ];

    }
}
