<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        User::factory([
            "firstname" => "John",
            "lastname" => "Doe",
            "birthday" => Carbon::now()->subYears(20),
            "email" => "admin@example.com",
            "password" => "passwordpassword"
        ])->create();
        User::factory(2)->has(Item::factory()->count(3))->create();
        // \App\Models\User::factory(10)->create();
    }
}
