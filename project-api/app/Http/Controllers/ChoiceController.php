<?php

namespace App\Http\Controllers;

use App\Choice;
use App\Http\Resources\ChoiceResource;
use Illuminate\Http\Request;

class ChoiceController extends Controller
{
    public function fetchChoices($questionId)
    {
        $choices = Choice::where('question_id', $questionId)->get();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<rows>';

        foreach ($choices as $choice) {

            $xml .= "<row id = '" . $choice->id . "'>";
            $xml .= "<cell></cell>";
            $xml .= "<cell><![CDATA[" . $choice->text . "]]></cell>";
            $xml .= "<cell><![CDATA[" . $choice->response . "]]></cell>";
            $xml .= "<cell><![CDATA[" . $choice->score . "]]></cell>";
            $xml .= "<cell><![CDATA[" . $choice->jumpto . "]]></cell>";
            $xml .= "</row>";
        }

        $xml .= "</rows>";

        return response()->xml($xml);
    }

    public function createChoice(Request $request)
    {

        $questionId = $request->question_id;

        $choice = new Choice([
            'question_id' => $questionId
        ]);

        $choice->save();

        if ($choice->id) {
            $result = array('success' => true, 'text' => 'Successfully Added', 'row_id' => $choice->id);
        } else {
            $result = array('success' => false, 'text' => 'An Error Occured While Saving');
        }
        return $result;
    }


    public function deleteChoice($id)
    {
        $choice = Choice::find($id);

        if ($choice->delete()) {
            $result = array('success' => true, 'text' => 'Successfully Deleted');
        } else {
            $result = array('success' => false, 'text' => 'An Error Occured While Deleting');
        }

        return $result;
    }

    public function showChoice($id)
    {
        $choice = collect(new ChoiceResource(Choice::find($id)));

        $choice->toArray();

        return response()->xml($choice, $status = 200, [], $xmlRoot = 'data');
    }

    public function updateChoice(Request $request, $id)
    {
        $choice = Choice::find($id);
        $choice->response = $request->response;
        $choice->text = $request->text;
        $choice->score = $request->score;
        $choice->jumpto = $request->jumpto;
        $choice->save();

        return array('success' => true, 'text' => 'Successfully Updated');

    }


    public function editChoiceCell(Request $request)
    {

        $id = $request->get('id');
        $field_value = $request->get('nvalue');
        $field = $request->get('colId');

        $question = Choice::find($id);
        $question->{$field} = $field_value;

        if ($question->save()) {

            $message = 'Successfully updated';
            $success = true;

        } else {
            $message = 'An error occurred while saving';
            $success = false;
        }

        return [
            'text' => $message,
            'success' => $success
        ];
    }
}
