<?php

use Illuminate\Database\Seeder;

// use RoleSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $this->call('UsersTableSeeder');
        $this->call(RoleSeeder::class);
        // $this->call('RoleSeeder');
    }
}
