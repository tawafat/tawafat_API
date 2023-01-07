<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Mailjet\LaravelMailjet\Facades\Mailjet;
use Mailjet\Resources;

class UserController extends Controller
{

    public function index()
    {
        return User::all();
    }


    public function show($id)
    {
        return User::with('role')->find($id);
    }


    public function store(Request $request)
    {
        $fields = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|confirmed',
        ]);
        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'password' => bcrypt($fields['password'])
        ]);
        $token = $user->createToken('myAppToken')->plainTextToken;

        $response = [
            'user' => $user,
            'token' => $token
        ];



        $body = [
            'FromEmail' => "hazem.xmotion@gmail.com",
            'FromName' => "Hazem Tawafat",
            'Subject' => "Welcome ".$fields['name'] .",
             An account is been Created on Tawafat,
             your Credential is:
             user: ".$fields['name'] ." , Password: ".$fields['password']."
             ",

            'Recipients' => [['Email' =>  $fields['email']]]
        ];
         Mailjet::post(Resources::$Email, ['body' => $body]);

        return response($response, 201);
    }


    public function update(Request $request, $id)
    {
        $fields = $request->validate([
            'name' => 'nullable|string',
            'email' => 'email|unique:users,email',
            'password' => 'nullable|string|confirmed',
        ]);
        $user = User::find($id);
        if ($user) {
            $user->update($fields);
            return $user;
        } else {
            return response(['message' => 'Not Found'], 404);
        }
    }


    public function destroy($id)
    {

        $response = User::destroy($id);
        if ($response) {
            return response(['message' => 'Deleted Successfully'], 200);
        } else {
            return response(['message' => 'Not Found'], 404);
        }
    }
}
