<?php

namespace App\Http\Controllers;

use App\Http\Resources\ServerResource;
use App\Server;
use Illuminate\Http\Request;

class ServerController extends Controller
{
    public function fetchServers()
    {
        $servers = Server::all();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<rows>';

        foreach ($servers as $server) {

            $xml .= "<row id = '" . $server->id . "'>";
            $xml .= "<cell></cell>";
            $xml .= "<cell><![CDATA[" . $server->name . "]]></cell>";
            $xml .= "<cell><![CDATA[" . $server->domain . "]]></cell>";
            $xml .= "<cell><![CDATA[" . $server->token . "]]></cell>";
            $xml .= "<cell><![CDATA[" . $server->path . "]]></cell>";
            $xml .= "<cell>" . $server->is_moodle . "</cell>";
            $xml .= "</row>";
        }

        $xml .= "</rows>";

        return response()->xml($xml);
    }

    public function createServer(Request $request)
    {

        $server = new Server([
            'name' => 'New Server'
        ]);

        $server->save();

        if ($server->id) {
            $result = array('success' => true, 'text' => 'Successfully Added', 'row_id' => $server->id);
        } else {
            $result = array('success' => false, 'text' => 'An Error Occured While Saving');
        }
        return $result;
    }


    public function deleteServer($id)
    {
        $server = Server::find($id);

        if ($server->delete()) {
            $result = array('success' => true, 'text' => 'Successfully Deleted');
        } else {
            $result = array('success' => false, 'text' => 'An Error Occured While Deleting');
        }

        return $result;
    }

    public function showServer($id)
    {
        $server = collect(new ServerResource(Server::find($id)));

        $server->toArray();

        return response()->xml($server, $status = 200, [], $xmlRoot = 'data');
    }

    public function updateServer(Request $request, $id)
    {
        $server = Server::find($id);
        $server->name = $request->name;
        $server->domain = $request->domain;
        $server->token = $request->token;
        $server->path = $request->path;
//        $server->location = $request->location;
        $server->is_moodle = $request->is_moodle;
        $server->save();

        return array('success' => true, 'text' => 'Successfully Updated');

    }


    public function editServerCell(Request $request)
    {

        $id = $request->get('id');
        $field_value = $request->get('nvalue');
        $field = $request->get('colId');

        $server = Server::find($id);
        $server->{$field} = $field_value;

        if ($server->save()) {

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
