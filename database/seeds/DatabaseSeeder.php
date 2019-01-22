<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('users')->insert([
           [
               'name' => 'Narayan',
               'email' => 'sharmanarayan1991'.rand().'@gmail.com',
               'password' => '1234567896',
           ]
        ]);
    }
}
