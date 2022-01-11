<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function login(Request $request) {
        try {
            $user = User::query()->where('email', $request['email'])->where("password", $request["password"])->firstOrFail();
        }catch (\Exception $exception) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $token = $user->createToken('auth_token')->plainTextToken;
        return response()
            ->json(['message' => 'Slt '.$user->name.'!','access_token' => $token, 'token_type' => 'Bearer']);
    }
//    public function logout()
//    {
//        auth()->user()->tokens()->delete();
//
//        return [
//            'message' => 'Déconnecté'
//        ];
//    }
}
