<?php

namespace App\Http\Controllers;

use App\Chapter;
use App\Document;
use App\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use stdClass;

class DocumentController extends Controller
{

    public static $toc = '<h4>Table of Content</h4>';
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($pId)
    {
        $documents = Document::whereHas('projects', function ($query) use ($pId) {
            $query->where("document_project.project_id", "=", $pId);
        })->with('author')->get();

        $chapters = Chapter::with('author')->get();

        $objects = array();
        $roots = array();
        foreach ($chapters as $chapter) {
            if (!isset($objects[$chapter->id])) {
                $objects[$chapter->id] = new stdClass;
                $objects[$chapter->id]->children = array();
            }

            $obj = $objects[$chapter->id];
            $obj->title = $chapter->title;
            $obj->id = $chapter->id;
            $obj->sort = $chapter->sort_id;
            $obj->document_id = $chapter->document_id;
            $obj->author = $chapter->author->name;
            $obj->created_at = $chapter->created_at;

            if ($chapter->parent_id == 0) {
                $roots[$chapter->document_id][] = $obj;
            } else {
                if (!isset($objects[$chapter->parent_id])) {
                    $objects[$chapter->parent_id] = new stdClass;
                    $objects[$chapter->parent_id]->children = array();
                }

                $objects[$chapter->parent_id]->children[$chapter->id] = $obj;
            }
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<rows>';

        foreach ($documents as $document) {
            $xml .= '<row id="doc_' . $document->id . '">';
            $xml .= '<cell>' . $document->id . '</cell>';
            $xml .= "<cell image=\"folder.gif\"><![CDATA[" . $document->title . "]]></cell>";
            $xml .= "<cell><![CDATA[" . $document->author->name . "]]></cell>";
            $xml .= "<cell><![CDATA[" . $document->created_at . "]]></cell>";
            $xml .= "<cell><![CDATA[" . $document->is_published . "]]></cell>";

            if (isset($roots[$document->id])) {
                foreach ($roots[$document->id] as $key => $obj) {
                    $xml .= $this->printElementsTreeGridXML($obj, '', true);
                }
            }

            $xml .= '</row>';
        }
        $xml .= '</rows>';

        return response()->xml($xml);
    }

    function printElementsTreeGridXML(stdClass $obj, $chapter, $isRoot = false)
    {

        if ($isRoot) {
            $chapter = $obj->sort;
        } else {
            $chapter .= '.' . $obj->sort;
        }

        $xml = '<row id="' . $obj->id . '">';
        $xml .= '<cell>' . $chapter . '</cell>';
        if (count($obj->children) == 0) {
            $xml .= "<cell><![CDATA[" . $obj->title . "]]></cell>";
        } else {
            $xml .= "<cell><![CDATA[" . $obj->title . "]]></cell>";
        }
        $xml .= "<cell><![CDATA[" . $obj->author . "]]></cell>";
        $xml .= "<cell><![CDATA[" . $obj->created_at . "]]></cell>";

        foreach ($obj->children as $child) {
            $xml .= $this->printElementsTreeGridXML($child, $chapter);
        }
        $xml .= '</row>';

        return $xml;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => 'required',
            'id' => 'required'
        ]);

        $user_id = 1;//Auth::id();

        $document = new Document(array(
            'title' => $request->get('title'),
            'category' => $request->get('category'),
            'user_id' => $user_id
        ));

        if ($document->save()) {
            $document->projects()->attach($request->get('id'));

            $response = Response::json([
                'success' => true,
                'message' => 'Document created succesfully',
                'id' => 'doc_' . $document->id
            ]);
        } else {

            $response = Response::json([
                'message' => 'An error occurred while saving',
                'success' => false
            ]);
        }

        return $response;
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $document = Document::find($id);

        $document->toArray();

        return response()->xml($document, $status = 200, [], $xmlRoot = 'data');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!$request->title) {

            $response = Response::json([
                'error' => [
                    'message' => 'Please enter all required fields'
                ]
            ], 422);
            return $response;
        }

        $document = Document::find($id);
        $document->title = $request->title;
        $document->category = $request->category;
        $document->is_published = $request->is_published;
        $document->save();

        $response = Response::json([
            'success' => true,
            'message' => 'The document has been updated.',
            'data' => $document,
        ]);

        return $response;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $document = Document::find($id);

        if (!$document) {
            $response = Response::json([
                'error' => [
                    'message' => 'The document cannot be found.'
                ]
            ], 404);

            return $response;
        }

        $document->projects()->detach($request->get('pId'));

        $response = Response::json([
            'success' => true,
            'message' => 'The document has been deleted.'

        ]);

        return $response;
    }

    public function  showContent($id){

        $doc_str = '';

        $doc_str = self::generate_document($id, $doc_str);

        $response = Response::json([
            'success' => true,
            'content' => $doc_str
        ]);

        return $response;

    }


    public static function generate_document($id, $doc_str) {

        $chapters = Chapter::where('document_id', '=', $id)
            ->orderByRaw('parent_id = 0', 'desc')
            ->orderBy('sort_id', 'asc')
            ->get();

        $objects = array();
        $roots = array();
        foreach ($chapters as $chapter) {

            if (!isset($objects[$chapter->id])) {
                $objects[$chapter->id] = new stdClass;
                $objects[$chapter->id]->children = array();
            }

            $obj = $objects[$chapter->id];
            $obj->id = $chapter->id;
            $obj->title = $chapter->title;
            $obj->sort = $chapter->sort_id;
            $obj->content = $chapter->content;

            if ($chapter->parent_id == 0) {
                $roots[] = $obj;
            } else {
                if (!isset($objects[$chapter->parent_id])) {
                    $objects[$chapter->parent_id] = new stdClass;
                    $objects[$chapter->parent_id]->children = array();
                }

                $objects[$chapter->parent_id]->children[$chapter->id] = $obj;
            }
        }

        foreach ($roots as $obj) {
            $doc_str = self::print_document($obj, $doc_str, '', true);
        }

//    $doc_str .= '<br/>';
//    $doc_str .= '<a href="#">Move to top</a>';
//    $doc_str .= '<br/>';

        return self::$toc . $doc_str;
    }

    public static function print_document(stdClass $obj, $doc_str, $chapter, $isRoot = false) {

//        global $toc;
        if ($isRoot) {
            $link = $obj->sort;
            $chapter = $obj->sort . '.';
            self::$toc .= '<a href="#C' . $link . '">' . $chapter . ' ' . $obj->title . '</a><br />';
            $doc_str .= '<h4 id="C' . $link . '">' . $chapter . ' ' . $obj->title . '</h4>';
        } else {
            $link = '_' . $obj->sort;
            $chapter .= $obj->sort . '.';
            self::$toc .= '<a href="#C' . $link . '">' . $chapter . ' ' . $obj->title . '</a><br />';
            $doc_str .= '<h5 id="C' . $link . '">' . $chapter . ' ' . $obj->title . '</h5>';
        }

        $doc_str .= $obj->content;
        $doc_str .= '<br />';

        foreach ($obj->children as $child) {
            $doc_str = self::print_document($child, $doc_str, $chapter);
        }

        return $doc_str;
    }

    public function editCell(Request $request)
    {

        $id = $request->get('id');
        $field_value = $request->get('nvalue');
        $field = $request->get('colId');

        $document = Document::find($id);
        $document->{$field} = $field_value;

        if ($document->save()) {

            $message = 'The document has been updated';

            $response = Response::json([
                'message' => $message,
                'success' => true
            ]);
        } else {
            $message = 'An error occurred while saving';

            $response = Response::json([
                'message' => $message,
                'success' => false
            ]);
        }

        return $response;

    }

    public function addMedia(Request $request)
    {

        $document = Document::find($request->document_id);
        $ids = explode(",", $request->get('ids'));

        if ($request->get('n_value') == '1') {
            $document->media()->attach($ids);

            //Get the IDs that are already attached
            $attachedIds = $document->media()->whereIn('id', $ids)->pluck('id');
            //Remove the attached IDs from the request array
            $newIds = array_diff($ids, $attachedIds);
            //Attach the new IDs
            $document->media()->attach($newIds);
        }

        if ($request->get('n_value') == '0') {
            $document->media()->detach($ids);
        }


        $response = Response::json([
            'success' => true,
            'message' => 'Media added successfully.'
        ], 200);

        return $response;

    }

    public function getMedia($id)
    {

        $document = Document::with('media')->whereId($id)->firstOrFail();
        $xml = MediaController::get_media_xml($document->media);

        return response()->xml($xml);

    }

}
