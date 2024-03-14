<?php
namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Document;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Note;
use Illuminate\Support\Facades\Log;

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
        //Log::info('document_affected - '.json_encode($document_affected));
        //Log::info('note - '.json_encode($note));

        return view('tenant.documents.note_edit', compact('document_affected', 'configuration'));
    }

    public function update()
    {

    }


}
