<?php

namespace App\Http\Controllers;

use App\Jobs\UploadMedia;
use App\Media;
use App\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use stdClass;


class MediaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($pId)
    {

        $files = Media::where('project_id', '=', $pId)
            ->orderByRaw('parent_id = 0', 'desc')
            ->orderBy('id', 'asc')
            ->get();

        $xml = self::get_media_xml($files);

        return response()->xml($xml);
    }

    public static function get_media_xml($files)
    {
        $objects = array();
        $roots = array();

        foreach ($files as $media) {
            if (!isset($objects[$media->id])) {
                $objects[$media->id] = new stdClass;
                $objects[$media->id]->children = array();
            }

            $obj = $objects[$media->id];

            $obj->id = $media->id;
            $obj->name = $media->file_name;
            $obj->parent_id = $media->parent_id;
            $obj->sort_id = $media->sort_id;
            $obj->start_time = $media->start_time;
            $obj->end_time = $media->end_time;
            $obj->path = $media->path;
            $obj->type = $media->type;
            $obj->created_at = $media->created_at;


            if ($media->parent_id == 0) {
                $roots[] = $obj;

            } else {
                if (!isset($objects[$media->parent_id])) {
                    $objects[$media->parent_id] = new stdClass;
                    $objects[$media->parent_id]->children = array();
                }

                $objects[$media->parent_id]->children[$media->id] = $obj;
            }
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<rows>';

        foreach ($roots as $obj) {
            $xml .= self::print_xml($obj, true);
        }

        $xml .= '</rows>';

        return $xml;
    }

    public static function print_xml(stdClass $obj, $isRoot = false)
    {

        $link = 'https://video.nts.nl/uploads/' . $obj->path;

        $xml = '<row id="' . $obj->id . '">';

//        if (!$isRoot && count($obj->children) == 0) {
        $xml .= '<cell>' . $obj->name . '</cell>';
//        } else {
//            $xml .= '<cell image="folder.gif">' . $obj->name . '</cell>';
//        }
        $xml .= "<cell><![CDATA[" . $link . "]]></cell>";
        $xml .= "<cell><![CDATA[" . $obj->start_time . "]]></cell>";
        $xml .= "<cell><![CDATA[" . $obj->end_time . "]]></cell>";
        $xml .= "<cell><![CDATA[" . $obj->created_at . "]]></cell>";
        $xml .= "<cell><![CDATA[" . Str::title($obj->type) . "]]></cell>";

        foreach ($obj->children as $child) {
            $xml .= self::print_xml($child);
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
    public function store(Request $request, $id, $type)
    {
        $this->validate($request, [
            // nullable == optional
//            'file' => 'required|file|mimes:pdf,doc,mp3,mp4'
        ]);

        $project = Project::find($id);
        $project_name = Project::generateProjectId($project->id); //.'_'.strtolower(str_replace(' ','_',$project->title));


        $sort_id = DB::table('media')
            ->select(DB::raw('IF((MAX(sort_id)>0),MAX(sort_id)+1,1) as sort_id'))
            ->where('parent_id', 0)
            ->value('sort_id');

        $file_name = $project_name . '_' . strtolower(str_replace(' ', '_', $project->title)) . '_' . $sort_id;


        // Handle File Upload
        if ($request->hasFile('file')) {
            // Get filename with extension
            $filenameWithExt = $request->file('file')->getClientOriginalName();
            // Get just filename
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            // Get just ext
            $extension = $request->file('file')->getClientOriginalExtension();
            //Filesize
            $filesize = $request->file('file')->getSize();
            //Filename to store
//            $fileNameToStore = $filename . '_' . time() . '.' . $extension;
            $fileNameToStore = $file_name . '.' . $extension;
            $file_path = $project_name . '/' . $type . '/'. $sort_id;
            // Upload Image
            $path = $request->file('file')->storeAs($file_path, $fileNameToStore);
        }

//        dd($realpath);

        $user_id = 1;//Auth::id();


        // create Post
        $media = new Media;
        $media->file_name = $file_name;
        $media->size = $filesize;
        $media->type = $type;
        $media->extension = $extension;
        $media->sort_id = $sort_id;
        $media->path = $path; //$file_path . DIRECTORY_SEPARATOR . $fileNameToStore;
        $media->user_id = $user_id;
        $media->project_id = $id;

        $media->save();

//        $request->path = $file_path;
//        $request->file_name = $fileNameToStore;

//        dd($request->all());

        UploadMedia::dispatch($media);


//        $request->file('file')->storeAs($file_path, $fileNameToStore);

        $message = 'Your file has been added successfully';

        $response = Response::json([
            'state' => true,
            'name' => str_replace("'", "\\'", $filename),
            'extra' => [
                'info' => 'just a way to send some extra data',
                'param' => 'some value here'
            ],
        ]);

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
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
