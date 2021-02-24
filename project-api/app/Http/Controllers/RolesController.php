<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleFormRequest;
use App\Http\Resources\RoleCollection;
use App\Http\Resources\RoleResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Spatie\Permission\Models\Role;

class RolesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $roles = Role::all();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<rows>';

        foreach ($roles as $role) {
            $xml .= "<row id = '" . $role->id . "'>";
            $xml .= "<cell><![CDATA[" . $role->name . "]]></cell>";
            $xml .= "</row>";
        }

        $xml .= '</rows>';

        return response()->xml($xml);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(RoleFormRequest $request)
    {
        Role::create(['name' => $request->get('name')]);


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
    public function update(RoleFormRequest $request, $id)
    {
        $role = Role::find($id);
        $role->name = $request->name;
        $role->save();

        $response = Response::json([
            'success' => true,
            'message' => 'Successfully Updated.'
        ], 200);

        return $response;
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

    public function getRolesList(){

        $roles = new RoleCollection(Role::all());

        $response = [
            'roles' => $roles
        ];

        return $response;

    }
}
