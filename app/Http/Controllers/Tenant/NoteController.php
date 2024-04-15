<?php
namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Document;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\DocumentItem;
use App\Models\Tenant\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Inventory\Models\ItemWarehouse;
use Modules\Item\Models\ItemLot;
use Modules\Item\Models\ItemLotsGroup;

class NoteController extends Controller
{
    public function create($document_id)
    {
        $document_affected = Document::find($document_id);
        $configuration = Configuration::first();

        return view('tenant.documents.note', compact('document_affected', 'configuration'));
    }

    public function record($document_id)
    {
        Log::info('document id - '.$document_id);
        $record = Document::find($document_id);

        return $record;
    }

    public function hasDocuments($document_id)
    {

        $record = Document::wherehas('affected_documents')->find($document_id);

        if($record){

            return [
                'success' => true,
                'data' => $record->affected_documents->transform(function($row, $key) {
                            return [
                                'id' => $row->id,
                                'document_id' => $row->document_id,
                                'document_type_description' => $row->document->document_type->description,
                                'description' => $row->document->number_full,
                            ];
                        })
            ];
            
        }

        return [
            'success' => false,
            'data' => []
        ];

    }

    public function edit($document_id)
    {
        //$document_affected = Document::find($document_id);
        $configuration = Configuration::first();
        $document_affected = Note::where('document_id', $document_id)->first();
        $doc_original = Document::find($document_id);
        $document_affected->doc_original = $doc_original;
        //array_push($document_affected, $doc_original);
        //Log::info('document_affected - '.json_encode($document_affected));
        //Log::info('note - '.json_encode($note));

        return view('tenant.documents.note_edit', compact('document_affected', 'configuration'));
    }

    public function update(Request $request, $id)
    {
        $document = Document::findOrFail($id);
        $note = Note::where('document_id', $id)->first();      

        $old_document_items = $document->items;
        $items = $request->items;
        //Log::info('document - '.json_encode($document));
        //Log::info('request desc - '.json_encode($request->all()));

        /*$purchase_order = $request->purchase_order;
        $description = $request->description;
        $credit_type_id =$request->note_credit_or_debit_type_id;*/
        

        $document->purchase_order = $request->purchase_order;
        $document->save();

        $note->note_description = $request->note_description;
        $note->note_credit_type_id = $request->note_credit_or_debit_type_id;
        $note->save();
        
        if($items != null && $old_document_items != null)
        {
            //Restauraritems del documento ya creado
            if($old_document_items != null || sizeof($old_document_items) > 0)
            {  
                foreach($old_document_items as $item_original)
                {
                    //Log::info('entra al foreach - '.json_encode($item_original));
                    if($item_original->item->unit_type_id == 'ZZ')
                    {
                        //Log::info('Es servicio - '.json_encode($note->affected_document));
                        $document_item = DocumentItem::where('document_id', $note->affected_document->id)->first();                        
                        if ($document_item !== null) {
                            $document_item->delete();
                            //Log::info('Document Item eliminado correctamente.');
                        } else {
                            return [
                                'success' => false,
                                'message' => 'No se encotro el registro del servicio',
                            ];
                        }

                    }else if($item_original->item->IdLoteSelected != null)
                    {
                        //Log::info('Es producto con lotes');

                        $item_warehouse = ItemWarehouse::where('item_id', $item_original->item_id)->get();
                        foreach($item_warehouse as $item)
                        {
                            foreach($item_original->item->IdLoteSelected as $item_lote)
                            {
                                if($item->warehouse_id == $item_lote->warehouse_id)
                                {
                                    $item->stock -= $item_original->quantity;
                                    $item->save();
                                }
                            }
                        }

                        $item_lots_group = ItemLotsGroup::where('item_id', $item_original->item_id)->get();
                        foreach($item_lots_group as $lot_group)
                        {
                            foreach($item_original->item->IdLoteSelected as $lote)
                            {
                                if($lot_group->warehouse_id == $lote->warehouse_id)
                                {
                                    $lot_group->quantity -= $lote->compromise_quantity;
                                    $lot_group->save();
                                }
                            }
                        }

                    }else if($item_original->item->IdLoteSelected == null && empty($item_original->item->lots))
                    {
                        //Log::info('Es producto sin lotes');
                        //Log::info('item original - '.$item_original);

                        $item_warehouse = ItemWarehouse::where('item_id', $item_original->item_id)->get();

                        foreach($item_warehouse as $item)
                        {
                            if($item->warehouse_id == $item_original->warehouse_id)
                            {
                                $item->stock -= $item_original->quantity;
                                $item->save();
                            }
                        }

                    }else if($item_original->item->IdLoteSelected == null && !empty($item_original->item->lots))
                    {
                        //Log::info('Es producto con series - '.json_encode($item_original->item_id));
                        $item_lots = ItemLot::where('item_id', $item_original->item_id)->get();

                        foreach($item_lots as $lots)
                        {
                            foreach($item_original->item->lots as $lot)
                            {
                                if($lots->series == $lot->series)
                                {
                                    $lots->has_sale = true;
                                    $lots->save();                                }
                            }
                        }

                    }
                }
            }
            //Hacer nota de credito con nuevo items
            if($items != null || sizeof($items) > 0)
            {
                foreach($items as $item)
                {
                    //Log::info('Request - '.json_encode($item));
                    if($item['item']['unit_type_id'] == 'ZZ')
                    {
                        //Log::info('Es servicio');
                        $document->item->create($item);
                        //$document->save();

                    }else if($item['item']['IdLoteSelected'] != null)
                    {
                        //Log::info('Es producto con lotes');

                        $item_warehouse = ItemWarehouse::where('item_id', $item['item_id'])->get();
                        foreach($item_warehouse as $item_lote)
                        {
                            foreach($item['item']['IdLoteSelected'] as $lote)
                            {
                                if($item_lote->warehouse_id == $lote['warehouse_id'])
                                {
                                    $item_lote->stock += $lote['compromise_quantity'];
                                    $item_lote->save();
                                }
                            }
                        }
                        
                        $item_lots_group = ItemLotsGroup::where('item_id', $item['item_id'])->get();
                        foreach($item_lots_group as $lot_group)
                        {
                            foreach($item['item']['IdLoteSelected'] as $lote)
                            {
                                if($lot_group->warehouse_id == $lote['warehouse_id'])
                                {
                                    $lot_group->quantity += $lote['compromise_quantity'];
                                    $lot_group->save();
                                }
                            }
                        }

                    }else if($item['item']['IdLoteSelected'] == null && sizeof($item['item']['lots']) == 0)
                    {
                        //Log::info('Es producto sin lotes');
                        $item_warehouse = ItemWarehouse::where('item_id', $item['item_id'])->get();
                        foreach($item_warehouse as $item_sin)
                        {
                            if($item_sin->warehouse_id == $item['warehouse_id'])
                            {
                                $item_sin->stock += $item['quantity'];
                                $item_sin->save();
                            }
                        }

                    }else if($item['item']['IdLoteSelected'] == null && sizeof($item['item']['lots']) > 0)
                    {
                        //Log::info('Es producto con series');
                        $item_lots = ItemLot::where('item_id', $item['item_id'])->get();

                        foreach($item_lots as $lots)
                        {
                            foreach($item['item']['lots'] as $lot)
                            {
                                if($lots->series == $lot['series'])
                                {
                                    $lots->has_sale = false;
                                    $lots->save();   
                                }
                            }
                        }
                    }
                }
            }
        }else{
            return [
                'success' => false,
                'message' => 'Error revisar items',
            ];
        }

        return [
            'success' => true,
            'message' => 'Cambios guardados correctamente',
        ];
    }


}
