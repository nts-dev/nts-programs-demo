<?php

namespace App\Http\Controllers;

use App\Choice;
use App\Classes\curl;
use App\Enums\QuestionType;
use App\Http\Resources\QuestionResource;
use App\Question;
use App\Server;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuestionController extends Controller
{
    public function fetchQuestions($courseId, $pageId = null)
    {
        if ($pageId) {

            $questions = Question::where('course_id', $courseId)
                ->whereRaw('id NOT IN (SELECT question_id FROM page_question WHERE page_id =' . $pageId . ')')
                ->get();
        } else {
            $questions = Question::where('course_id', $courseId)->get();
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<rows>';

        foreach ($questions as $question) {

            $type = $pageId ? QuestionType::getDescription($question->type) : $question->type;

            $xml .= "<row id = '" . $question->id . "'>";
            if ($pageId)
                $xml .= "<cell></cell>";
            $xml .= "<cell>" . $question->id . "</cell>";
            $xml .= "<cell><![CDATA[" . $question->title . "]]></cell>";
            $xml .= "<cell><![CDATA[" . $question->text . "]]></cell>";
            $xml .= "<cell><![CDATA[" . $type . "]]></cell>";
            $xml .= "</row>";
        }

        $xml .= "</rows>";

        return response()->xml($xml);
    }

    public function createQuestion(Request $request)
    {

        $courseId = $request->get('course_id');
        $type = $request->get('type');
        $pageId = $request->get('page_id');

        $question = new Question(array(
            'course_id' => $courseId,
            'type' => $type
        ));
        $question->save();

        $question_id = $question->id;

        $answers = array();

        switch ($type) {

            case QuestionType::Essay:

                $answers[] = [
                    'question_id' => $question_id,
                    'text' => null,
                    'jumpto' => -1,
                    'score' => 1,
                    'response' => null
                ];
                break;

            case QuestionType::ShortAnswer:
            case QuestionType::Numerical:

                $answers[] = [
                    'question_id' => $question_id,
                    'text' => null,
                    'jumpto' => -1,
                    'score' => 1,
                    'response' => 'correct'
                ];

                $answers[] = [
                    'question_id' => $question_id,
                    'text' => '@#wronganswer#@',
                    'jumpto' => -1,
                    'score' => 0,
                    'response' => 'wrong'
                ];
                break;

            case QuestionType::TrueFalse:

                $answers[] = [
                    'question_id' => $question_id,
                    'text' => 'Yes',
                    'jumpto' => -1,
                    'score' => 1,
                    'response' => 'correct'
                ];

                $answers[] = [
                    'question_id' => $question_id,
                    'text' => 'No',
                    'jumpto' => -1,
                    'score' => 0,
                    'response' => 'wrong'
                ];
                break;

            case QuestionType::TrueFalse:

                $answers[] = [
                    'question_id' => $question_id,
                    'text' => null,
                    'jumpto' => -1,
                    'score' => 1,
                    'response' => 'correct'
                ];

                break;

            case QuestionType::Matching:
                break;
        }

        if (count($answers) > 0) {
            Choice::insert($answers);
        }

        if ($pageId) {

            $newId = $this->insertQuestion($pageId, $question_id);

            if ($newId) {

                $response = array('success' => true, 'text' => 'Successfully Added', 'row_id' => $newId);
            } else {
                $response = array('success' => false, 'text' => 'An Error Occured While Saving');
            }
        } else {
            $response = array('success' => true, 'text' => 'Successfully Added', 'row_id' => $question_id);
        }

        return $response;
    }

    public function importQuestions()
    {

    }

    public function fetchPageQuestions($pageId)
    {

        $questions = DB::table('page_question')
            ->join('questions', 'page_question.question_id', '=', 'questions.id')
            ->select('page_question.id as id', 'page_question.question_id', 'questions.title', 'questions.text', 'questions.type')
            ->where('page_question.page_id', $pageId)
            ->get();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<rows>';
        foreach ($questions as $question) {
            $xml .= "<row id = '" . $question->id . "'>";
            $xml .= "<cell>" . $question->question_id . "</cell>";
            $xml .= "<cell><![CDATA[" . $question->title . "]]></cell>";
            $xml .= "<cell><![CDATA[" . $question->text . "]]></cell>";
            $xml .= "<cell><![CDATA[" . QuestionType::getDescription($question->type) . "]]></cell>";
            $xml .= "</row>";
        }
        $xml .= "</rows>";

        return response()->xml($xml);
    }

    public function deleteQuestion($id)
    {

        $question = Question::find($id);

        if ($question->delete()) {
            $result = array('success' => true, 'text' => 'Successfully Deleted');
        } else {
            $result = array('success' => false, 'text' => 'An Error Occured While Deleting');
        }

        return $result;

    }

    public function deletePageQuestion($id)
    {
        $question = DB::table('page_question')
            ->select(['page_id', 'sort_id'])
            ->where('id', $id)
            ->first();

        $sort_order = $question->sort_id;
        $page_id = $question->page_id;

        $delete = DB::table('page_question')->where('id', $id)->delete();

        if ($delete) {

            // Update remaining records.
            DB::table('page_question')
                ->where([['page_id', '=', $page_id], ['sort_id', '>', $sort_order]])
                ->update(['sort_id' => DB::raw('sort_id-1')]);

            $result = array('success' => true, 'text' => 'Successfully Deleted');
        } else {
            $result = array('success' => false, 'text' => 'An Error Occured While Deleting');
        }

        return $result;

    }


    public function editQuestionCell(Request $request)
    {

        $id = $request->get('id');
        $field_value = $request->get('nvalue');
        $field = $request->get('colId');

        $question = Question::find($id);
        $question->{$field} = $field_value;

        if ($question->save()) {

            $message = 'The question has been updated';
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


    public function exportQuestions(Request $request)
    {

        $lessonId = $request->lesson_id;
        $pageId = $request->page_id;
        $serverId = $request->server_id;

        $questions = DB::table('page_question')
            ->join('questions', 'page_question.question_id', '=', 'questions.id')
            ->leftJoin('choices', 'choices.question_id', '=', 'question.id')
            ->where('page_question.id', $pageId)
            ->select([
                'question.id',
                'question.title',
                'question.text',
                'question.type',
                'question.qoption',
                'choices.id choice_id',
                'choices.text choice_text',
                'choices.score',
                'choices.response',
                'choices.responseformat',
                'choices.jumpto'
            ])
            ->get();

        $objects = [];
        $prevpageid = $pageId;

        foreach ($questions as $row) {

            if (!isset($objects[$row->id])) {
                $objects[$row->id] = new stdClass;
                $objects[$row->id]->choices = [];
            }

            $question = $objects[$row->id];
            $question->id = $row->id;
            $question->qtype = $row->type;
            $question->title = $row->title;
            $question->contents = $row->text;
            $question->qoption = $row->qoption;


            if ($row->choice_id) {

                $choice = new stdClass;
                $choice->id = $row->choice_id;
                $choice->score = $row->score;
                $choice->answer = $row->choice_text;
                $choice->response = $row->response;
                $choice->responseformat = $row->responseformat;
                $choice->jumpto = $row->jumpto;

                $question->choices[$row->choice_id] = $choice;
            }
        }

        $server = Server::find($serverId);
        $domainname = $server->path;

        $serverurl = $domainname . "/data_content.php";

        $params = array(
            'lessonid' => $lessonId,
            'prevpageid' => $prevpageid,
            'question' => serialize($objects)
        );

        $curl = new curl;
        $resp = $curl->post($serverurl . "?action=13", $params);
        $response = json_decode($resp);


        if ($response->success) {

            foreach ($response->page_ids as $pid => $mid) {

                DB::table('page_question')
                    ->where([['page_id', '=', $pageId], ['question_id', '=', $pid]])
                    ->update(['moodle_id' => $mid, 'is_updated' => 0]);
            }

            foreach ($response->choice_ids as $pid => $mid) {

                $choice = Choice::find($pid);
                $choice->moodle_id = $mid;
                $choice->is_updated = 0;
                $choice->save();
            }

            $result = array('success' => true, 'text' => 'Successfully Updated');
        } else {
            $result = array('success' => false, 'text' => 'An Error Occured While Saving');
        }

        return $result;
    }

    public function linkQuestionToPage(Request $request)
    {
        $pageId = $request->page_id;
        $ids = $request->ids;

        $rowIds = explode(",", $ids);

        foreach ($rowIds as $question_id) {

            $this->insertQuestion($pageId, $question_id);
        }

        return array('success' => true, 'text' => 'Successfully Added');
    }

    public function showQuestion($id)
    {
        $question = collect(new QuestionResource(Question::find($id)));

        $question->toArray();

        return response()->xml($question, $status = 200, [], $xmlRoot = 'data');
    }

    public function updateQuestion(Request $request, $id)
    {
        $question = Question::find($id);
        $question->title = $request->title;
        $question->text = $request->text;
        $question->type = $request->type;
        $question->qoption = $request->qoption;
        $question->save();

        return array('success' => true, 'text' => 'Successfully Updated');

    }

    private function insertQuestion($pageId, $questionId)
    {
        $sortId = DB::table('page_question')
            ->select(DB::raw('IF((MAX(sort_id)>0),MAX(sort_id)+1,1) as sort_id'))
            ->where('page_id', $pageId)
            ->value('sort_id');

        $newId = DB::table('page_question')->insertGetId(
            [
                'question_id' => $questionId,
                'page_id' => $pageId,
                'sort_id' => $sortId
            ]
        );

        return $newId;
    }

}
