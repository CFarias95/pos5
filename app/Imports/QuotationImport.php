<?php

namespace App\Imports;

use App\Models\Tenant\Catalogs\AffectationIgvType;
use App\Models\Tenant\Company;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\Item;
use App\Models\Tenant\ItemSupplyLot;
use App\Models\Tenant\Person;
use App\Models\Tenant\PersonType;
use App\Models\Tenant\ProductionSupply;
use App\Models\Tenant\Quotation;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Modules\Production\Http\Controllers\ProductionController;
use Modules\Production\Models\Machine;
use Modules\Production\Models\Production;
use Illuminate\Support\Str;

class QuotationImport implements ToCollection
{
    use Importable;
    protected $result;

    public function collection(Collection $rows)
    {
        $total = count($rows);
        $registered = 0;
        $noRegistered = 0;
        $surplus = 0;
        unset($rows[0]);

        $config = Company::first();
        $quotation = null;
        $number = null;
        $quotation = new Quotation();
        foreach ($rows as $row) {

            if($row[0] == null){
                break;
            }

            try {

                if($number ==  null || $number != $row[0]){

                    $number = $row[0];
                    $quotation = new Quotation();
                    $item = Item::where('internal_id', $row[6])->orWhere('id', $row[6])->orWhere('barcode', $row[6])->first();
                    $client = Person::where('number',$row[1])->first();
                    $establishmnet = Establishment::find(Auth()->user()->establishment_id);
                    $config = Configuration::first();
                    $company = Company::active();

                    $quotation->currency_type_id = $config->currency_type_id;
                    $quotation->soap_type_id = $company->soap_type_id;
                    $quotation->customer_id = $client->id;
                    $quotation->customer = $client;
                    $quotation->date_of_due = $row[3];
                    $quotation->date_of_issue = $row[2];
                    $quotation->delivery_date = $row[4];
                    $quotation->user_id = auth()->user()->id;
                    $quotation->seller_id = auth()->user()->id;
                    $quotation->establishment_id =$establishmnet->id;
                    $quotation->establishment = $establishmnet;
                    $quotation->exchange_rate_sale = 1;
                    $quotation->payment_method_type_id = "01";
                    $quotation->prefix = "COT";
                    $quotation->shipping_address = $row[5];
                    $quotation->subtotal = 0;
                    $quotation->time_of_issue = date("h:i:s");
                    $quotation->total = 0;
                    $quotation->total_base_isc = 0;
                    $quotation->total_base_other_taxes = 0;
                    $quotation->total_charge = 0;
                    $quotation->total_discount = 0;
                    $quotation->total_exonerated = 0;
                    $quotation->total_exportation = 0 ;
                    $quotation->total_free = 0;
                    $quotation->total_igv = 0;
                    $quotation->total_isc = 0;
                    $quotation->total_igv_free = 0;
                    $quotation->total_other_taxes = 0;
                    $quotation->total_prepayment = 0;
                    $quotation->total_taxed = 0;
                    $quotation->total_unaffected = 0;
                    $quotation->total_value = 0;
                    $quotation->external_id = Str::uuid()->toString();
                    $quotation->state_type_id = '01';
                    $quotation->save();
                    $name = [$quotation->prefix, $quotation->id, date('Ymd')];
                    $quotation->filename = join('-', $name);
                    $registered += 1;

                    $dataItem = null;
                    $unit_price = $item->sale_unit_price;
                    $has_igv = $item->has_igv;

                    $affectation_igv = AffectationIgvType::find($item->sale_affectation_igv_type_id);
                    $percentage_igv = intval(filter_var(str_replace('-','',$affectation_igv->description), FILTER_SANITIZE_NUMBER_INT));

                    if($has_igv ==  true){
                        $unit_value = round($unit_price / ((100+$percentage_igv)/100),2);
                    }else{
                        $unit_value = $unit_price;
                        $unit_price = round($unit_price * ((100+$percentage_igv)/100),2);
                    }

                    $dataItem['item_id'] = $item->id;
                    $dataItem['quantity'] = $row[7];
                    $dataItem['item'] = $item;
                    $dataItem['unit_value'] = $unit_value;
                    $dataItem['affectation_igv_type_id'] = $affectation_igv->id;
                    $dataItem['total_base_igv'] = round($row[7] * $unit_value,2);
                    $dataItem['percentage_igv'] = $percentage_igv;
                    $dataItem['total_igv'] = round(($unit_price-$unit_value)*$row[7],2);
                    $dataItem['total_taxes'] = round(($unit_price-$unit_value)*$row[7],2);
                    $dataItem['price_type_id'] = '01';
                    $dataItem['unit_price'] = $unit_price;
                    $dataItem['total_value'] = round($row[7] * $unit_value,2);
                    $dataItem['total'] = round($row[7] * $unit_price,2);
                    $dataItem['name_product_pdf'] = $row[8];

                    $quotation->items()->create($dataItem);
                    $quotation->total = $dataItem['total'];
                    $quotation->total_taxes = $dataItem['total_taxes'];
                    $quotation->subtotal = $dataItem['total'];
                    $quotation->total_value = $dataItem['total_value'];
                    $quotation->total_igv = $dataItem['total_igv'];
                    $quotation->total_taxed = $dataItem['total_base_igv'];
                    $quotation->total_unaffected = ($affectation_igv->free > 0)?$dataItem['total_value']:0;
                    $quotation->save();
                }
                elseif($number != null && $number == $row[0]){

                    $item = Item::where('internal_id', $row[6])->orWhere('id', $row[6])->orWhere('barcode', $row[6])->first();
                    $dataItem = null;
                    $unit_price = $item->sale_unit_price;
                    $has_igv = $item->has_igv;

                    $affectation_igv = AffectationIgvType::find($item->sale_affectation_igv_type_id);
                    $percentage_igv = intval(filter_var(str_replace('-','',$affectation_igv->description), FILTER_SANITIZE_NUMBER_INT));

                    if($has_igv ==  true){
                        $unit_value = round($unit_price / ((100+$percentage_igv)/100),2);
                    }else{
                        $unit_value = $unit_price;
                        $unit_price = round($unit_price * ((100+$percentage_igv)/100),2);
                    }

                    $dataItem['item_id'] = $item->id;
                    $dataItem['quantity'] = $row[7];
                    $dataItem['item'] = $item;
                    $dataItem['unit_value'] = $unit_value;
                    $dataItem['affectation_igv_type_id'] = $affectation_igv->id;
                    $dataItem['total_base_igv'] = round($row[7] * $unit_value,2);
                    $dataItem['percentage_igv'] = $percentage_igv;
                    $dataItem['total_igv'] = round(($unit_price-$unit_value)*$row[7],2);
                    $dataItem['total_taxes'] = round(($unit_price-$unit_value)*$row[7],2);
                    $dataItem['price_type_id'] = '01';
                    $dataItem['unit_price'] = $unit_price;
                    $dataItem['total_value'] = round($row[7] * $unit_value,2);
                    $dataItem['total'] = round($row[7] * $unit_price,2);
                    $dataItem['name_product_pdf'] = $row[8];

                    $quotation->items()->create($dataItem);
                    $quotation->total += $dataItem['total'];
                    $quotation->total_taxes += $dataItem['total_taxes'];
                    $quotation->subtotal += $dataItem['total'];
                    $quotation->total_value += $dataItem['total_value'];
                    $quotation->total_igv += $dataItem['total_igv'];
                    $quotation->total_taxed += $dataItem['total_base_igv'];
                    $quotation->total_unaffected += ($affectation_igv->free > 0)?$dataItem['total_value']:0;
                    $quotation->save();
                }

            } catch (Exception $ex) {

                Log::error("No se pudo procesar la orden de produccion: " . $row[4]);
                Log::error($ex->getMessage());
                $noRegistered += 1;
            }
        }
        $this->result = compact('registered', 'noRegistered', 'surplus');
    }

    public function getData()
    {
        return $this->result;
    }
}
