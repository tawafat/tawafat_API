<?php

namespace Database\Seeders;


use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
         User::create([
            'name' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('3214')
        ]);
        User::create([
            'name' => 'manager',
            'email' => 'manager@gmail.com',
            'password' => bcrypt('3214')
        ]);
        User::create([
            'name' => 'employee',
            'email' => 'employee@gmail.com',
            'password' => bcrypt('3214')
        ]);
    }
}
