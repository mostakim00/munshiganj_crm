<?php

namespace App\Http\Controllers\RolePermission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionController extends Controller
{

    public function createRoleWithPermission(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:roles,name',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status' => 'failed',
                'message' => $validator->errors()->all(),
            ]);
        }
    
        DB::beginTransaction();
    
        try {
            $role = Role::create(['name' => $request->name, 'guard_name' => 'web']);
            $role->syncPermissions($request->permissions);
    
            DB::commit(); 
    
            return response()->json([
                'message' => 'User Role With Permission Added Successfully',
                'status' => 'success',
                'role' => $role
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'message' => 'Customer information couldn\'t be loaded',
            ]);
        }
    }
    
     
    public function updateRoleWithPermission(Request $request){
    
        // $validator = Validator::make($request->all(), [
        //     'name' => 'required|unique:roles,name',
        // ]);
    
        // if ($validator->fails()) {
        //     return response()->json([
        //         'status' => 'failed',
        //         'message' => $validator->errors()->all(),
        //     ]);
        // }
    
        // DB::beginTransaction();
    
        // try {
        $role = Role::find($request->id);

        $role->update(['name'=>$request->name]);
        $role->syncPermissions($request->permissions);

        // DB::commit();

        return response()->json([
            'message'   =>'User Role With  Permission Update Successfully',
            'status'    =>'success',
            'role'      =>$role 
        ],200);
    //   } catch (\Exception $e) {
    //     DB::rollBack();
    //     return response()->json([
    //         'status'    => 'failed',
    //         'message'   => 'Role information couldn\'t be loaded',
    //     ],500);
    //   }
    }
    
    public function allRole(){
        try {
        $allRole = Role::with(['permissions'])->get();
        $allRole = Role::all();

        return response()->json([
            'allRole' => $allRole
        ]);
    } catch (Exception $e) {
        return response()->json([
            'error' => 'An error occurred while fetching role.'
        ], 500); 
    }
    }

    public function allPermission(){
        try {
            $allPermission = Permission::all();
    
            return response()->json([
                'allPermission' => $allPermission
            ]);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'An error occurred while fetching permissions.'
            ], 500); // 500 indicates a server error, you can change this based on your needs
        }
    }
    
    


}
