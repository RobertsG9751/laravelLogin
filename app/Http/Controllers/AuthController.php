<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use PDO;

class AuthController extends Controller
{
    // Login API route, lietotaja autentificēšanai
    use HasApiTokens;
    public function login(Request $request){
        $fields = $request->validate([
            "email"=>'required|email',
            "password"=>'required|string|unique:users',
        ]);
        $user = User::where('email', $fields['email'])->first();
        if(!$user || !Hash::check($fields['password'], $user->password)){
            return response()->json([
                "message"=> "bad login"
            ], 401);
        }else{
            $token = $user->createToken('myapptoken')->plainTextToken;
            $response = [
                "user"=>$user,
                "token"=>$token
            ];
            return response()->json($response, 201);
        }

    }
    // Register API route, lietotaja registrešanai
    public function register(Request $request){
        $fields = $request->validate([
            "email"=>'required|email|unique:users',
            "password"=>'required|confirmed|string',
            "name"=>'required|string',
            "surname"=>'required|string'
        ]);

        $user = User::create([
            "name" => $fields['name'],
            "surname"=>$fields["surname"],
            "email"=> $fields['email'],
            'password'=> bcrypt($fields['password'])
        ]);

        $token = $user->createToken('myapptoken')->plainTextToken;

        $response = [
            "user"=>$user,
            "token"=>$token
        ];

        return response()->json($response, 201);
    }

    // Logout API route lietotaja izlogošanai no konta
    public function logout(Request $request){
        if ($request->user()) { 
            $request->user()->tokens()->delete();
        }
        return response()->json([
            "message"=> "Logged out!"
        ], 200);
    }
}
