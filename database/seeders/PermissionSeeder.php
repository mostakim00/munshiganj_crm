<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //create a role super admin
        // $role = Role::create(['name'=>'Admin']);
        $role = Role::create(['name'=>'Super Admin']);

        //create some default permission
        // $permissions =[
        //     ['name'=>'customer.store'],
        //     ['name'=>'customer.view'],
        //     ['name'=>'customer.update'],
        //     ['name'=>'customer.allAgent.view'],
        //     ['name'=>'customer.price.store'],
        //     ['name'=>'customer.price.view'],
        //     ['name'=>'customer.price.update'],
        //     ['name'=>'customer.payment.store'],
        //     ['name'=>'customer.payment.view'],
        //     ['name'=>'customer.payment.update'],
        //     ['name'=>'customer.paymentStatement.store'],
        //     ['name'=>'customer.landAgreement.store'],
        //     ['name'=>'customer.landAgreement.view'],
        //     ['name'=>'customer.landAgreement.update'],
        //     ['name'=>'purchasedLandDetails.view'],
        //     ['name'=>'purchasedLandDetails.DolilVariation.view'],
        //     ['name'=>'landSeller.store'],
        //     ['name'=>'landSeller.update'],
        //     ['name'=>'landSeller.view'],
        //     ['name'=>'sellerAgreement.store'],
        //     ['name'=>'sellerPayment.update'],
        //     ['name'=>'sellerPayment.view'],
        //     ['name'=>'sellerPayment.paymentStatement.view'],
        //     ['name'=>'projectInformation.store'],
        //     ['name'=>'projectInformation.view'],
        //     ['name'=>'projectInformation.update'],
        //     ['name'=>'mouzaInformation.store'],
        //     ['name'=>'mouzaInformation.view'],
        //     ['name'=>'mouzaInformation.update'],
        //     ['name'=>'csDagKhatiyan.store'],
        //     ['name'=>'csDagKhatiyan.update'],
        //     ['name'=>'csDagKhatiyan.view'],
        //     ['name'=>'rsDagKhatiyan.store'],
        //     ['name'=>'rsDagKhatiyan.update'],
        //     ['name'=>'rsDagKhatiyan.view'],
        //     ['name'=>'bsDagKhatiyan.store'],
        //     ['name'=>'bsDagKhatiyan.update'],
        //     ['name'=>'bsDagKhatiyan.view'],

        // ];

        $permissions = [
            ['name' => 'customer.store'],
            ['name' => 'customer.view'],
            ['name' => 'customer.update'],
            ['name' => 'customerprice.store'],
            ['name' => 'customerprice.view'],
            ['name' => 'customerprice.update'],
            ['name' => 'customerpayment.store'],
            ['name' => 'customerpayment.view'],
            ['name' => 'customerpayment.update'],
            ['name' => 'customerlandagreement.store'],
            ['name' => 'customerlandagreement.view'],
            ['name' => 'customerlandagreement.update'],
            ['name' => 'landseller.store'],
            ['name' => 'landseller.view'],
            ['name' => 'landseller.update'],
            ['name' => 'selleragreement.store'],
            ['name' => 'selleragreement.view'],
            ['name' => 'selleragreement.update'],
            ['name' => 'sellerpayment.store'],
            ['name' => 'sellerpayment.view'],
            ['name' => 'sellerpayment.update'],
            ['name' => 'agent.store'],
            ['name' => 'agent.view'],
            ['name' => 'agent.update'],
            ['name' => 'broker.store'],
            ['name' => 'broker.view'],
            ['name' => 'broker.update'],
            ['name' => 'landInfobank.store'],
            ['name' => 'landInfobank.view'],
            ['name' => 'landInfobank.update'],
            ['name' => 'landsubdeedbank.store'],
            ['name' => 'landsubdeedbank.view'],
            ['name' => 'landsubdeedbank.update'],
            ['name' => 'landdisputebank.store'],
            ['name' => 'landdisputebank.view'],
            ['name' => 'landdisputebank.update'],
            ['name' => 'landmouzamapbank.store'],
            ['name' => 'landmouzamapbank.view'],
            ['name' => 'landmouzamapbank.update'],
            ['name' => 'plotbank.store'],
            ['name' => 'plotbank.view'],
            ['name' => 'plotbank.update'],
        ];
        


        foreach($permissions as $permission){
            Permission::create($permission);
        }

        $user=User::where('name','Super Admin')->first();

        $user->assignRole($role);
        $role->syncPermissions(Permission::pluck('id')->toArray());


    }
}
