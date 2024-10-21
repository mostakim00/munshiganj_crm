<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
    
        //I will create a super admin
        User::create([
            'name'=>'Super Admin',
            'email'=>'superadmin@gmail.com',
            'password'=>bcrypt('123456'),
            'username'=>'Super Admin'
        ]);
    }
}
