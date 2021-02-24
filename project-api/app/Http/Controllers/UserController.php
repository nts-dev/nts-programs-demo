<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserFormRequest;
use App\Http\Resources\UserFormResource;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $users = User::all();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<rows>';

        foreach ($users as $user) {
            $xml .= "<row id = '" . $user->id . "'>";
            $xml .= "<cell></cell>";
            $xml .= "<cell><![CDATA[" . $user->name . "]]></cell>";
            $xml .= "<cell><![CDATA[" . $user->email . "]]></cell>";
            $xml .= "</row>";
        }

        $xml .= '</rows>';

        return response()->xml($xml);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = collect(new UserFormResource(User::find($id)));

        $user->toArray();

        return response()->xml($user, $status = 200, [], $xmlRoot = 'data');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(UserFormRequest $request, $id)
    {
        $user = User::whereId($id)->firstOrFail();
        $user->name = $request->get('name');
        $user->email = $request->get('email');
        $user->save();

        $roles = $request->input('roles') ? $request->input('roles') : [];
        $user->syncRoles($roles);

        $response = Response::json([
            'success' => true,
            'message' => 'The user has been updated.',
        ]);

        return $response;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::whereId($id)->firstOrFail();
        $user->delete();
    }

    public function addProjects($id, Request $request)
    {

        $user = User::whereId($id)->firstOrFail();
        $user->projects()->sync($request->get('projects'));

        $message = 'Permission added successfully';

        $response = Response::json([
            'message' => $message,
            'success' => true
        ], 200);

        return $response;

    }

    public function getProjects($id)
    {
        $user = User::whereId($id)->firstOrFail();
        $userProjects = $user->projects->pluck('id')->toArray();

        $response = Response::json([
            'projects' => $userProjects,
            'success' => true
        ], 200);

        return $response;
    }

}
