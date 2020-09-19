<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Roles;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // DB::table('roles')->insert(
        $roles = [
            [
                'name' => 'admin',
                'description' => 'Admin role',
                'permissions' => ([
                    'user-list', 'user-add', 'user-delete', 'task-list'
                ])
            ],
            [
                'name' => 'user',
                'description' => 'Normal user role',
                'permissions' => ([])
            ]
        ];
        Roles::create($roles[0]);
        Roles::create($roles[1]);
    }
}
