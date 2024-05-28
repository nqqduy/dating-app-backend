<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            ['name' => 'Admin', 'username' => 'admin', 'password' => Hash::make('admin'), 'role' => 'ADMIN', 'avatar' => 'https://png.pngtree.com/png-clipart/20230409/original/pngtree-admin-and-customer-service-job-vacancies-png-image_9041264.png'],
        ];
 
        foreach($users as $user) {
            DB::table('users')->insert($user);
        }
    }
}