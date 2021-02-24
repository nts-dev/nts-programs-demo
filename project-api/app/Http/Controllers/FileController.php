<?php

namespace App\Http\Controllers;

use App\Chapter;
use App\File;
use App\Http\Resources\FileCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class FileController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($type, $id)
    {
        $colId = 'document_id';

        if ($type == 'chapter') {
            $colId = 'chapter_id';
        }

        $files = File::with(['uploader'])->where([["{$colId}", '=', $id]])->get();

        $files = new FileCollection($files);
        $response = Response::json($files, 200);
        return $response;
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $id, $type)
    {
        $this->validate($request, [
            // nullable == optional
            // apache max upload 2mb
            'file' => 'nullable|max:1999'
        ]);
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
            // Upload Image
            $path = $request->file('file')->store('files', 'public');
        }

        if ($type == 'chapter') {
            $chapter_id = $id;
            $document_id = Chapter::where('id', $id)->first()->document_id;
        }

        if ($type == 'document') {
            $document_id = $id;
            $chapter_id = 0;
        }

        $user_id = 1;//Auth::id();

        // create Post
        $file = new File;
        $file->title = $filenameWithExt;
        $file->size = $filesize;
        $file->type = $extension;
        $file->path = $path;
        $file->user_id = $user_id;
        $file->chapter_id = $chapter_id;
        $file->document_id = $document_id;
        $file->save();

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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
