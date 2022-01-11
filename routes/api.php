<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get("/login", [\App\Http\Controllers\UserController::class, "login"])->name("login");

Route::group(["middleware" => ["auth:sanctum"]], function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::get("/test", function () {
        return ["Test" => "test"];
    });

    Route::get("/item/create", function (Request $request) {
        $item = auth()->user()->items()->make();

        if ($request->has("name") && $request->has("content")) {
            $item->name = $request["name"];
            $item->content = $request["content"];
            try {
                auth()->user()->add($item);
                return ["message" => "Item ajouté"];
            } catch (Exception $e) {
                return ["error_message" => $e->getMessage()];
            }
        }
        return ["error_message" => "Veuillez indiquer un name et un content"];


    });

});
