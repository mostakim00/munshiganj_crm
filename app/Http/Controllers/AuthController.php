<?php

namespace App\Http\Controllers;

use App\Http\Traits\imageTraits;
use App\Models\AdminProject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    use imageTraits;

   public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'      => 'required|string',
            'email'     => 'nullable|email:rfc,dns|unique:users,email',
            'role_id'   => 'required',
            'mobile_no' => 'required',
            'username'  => 'required|unique:users,username|min:5',
            'password'  => 'required|min:6',
        ]);
    
    
        if ($validator->fails()) {
            return response()->json([
                'status'   => 'failed',
                'message'  => $validator->messages()->all(),
            ]);
        }
        DB::beginTransaction();
        try{
      
            $user = new User();
            $user->name          = $request->name;
            $user->username      = $request->username;
            $user->email         = $request->email;
            $user->designation   = $request->designation;
            $user->mobile_no     = $request->mobile_no;
            $user->nid           = $request->nid;
            $user->image         = $this->getImageUrl($request->file('image') ?? null,'user/image/');
            $user->remarks       = $request->remarks;
            $user->password      = bcrypt($request->password);

            $file = $request->file('document');

            if ($file !== null) {
                $file_name       = date('Ymd-his') . '.' . $file->getClientOriginalExtension();
                $destinationPath = public_path('user/document/');
                $file->move($destinationPath, $file_name);

                $file_path       = 'user/document/' . $file_name;
                $file_name       = $file->getClientOriginalName();
                $user->file_path = $file_path;
                $user->file_name = $file_name;
                
            }
            
            $user->save();

            if($request->role_id){
                $user->syncRoles($request->role_id);
            }
           
            if(isset($request->project_id)){
               
                foreach($request->project_id as $project_id){
                        
                $assignProject              =  new AdminProject();
                $assignProject->user_id     =  intval($user->id);
                $assignProject->project_id  =  intval($project_id);
                $assignProject->save();
                       
                }
             
            }

            // $permissions = $user->getPermissionsViaRoles();
            $response = [
                'user'        => $user,
                // 'roles'       => $user->roles,
                // 'permissions' => $user->permissions,
                // 'permissions' =>  $permissions ,
               

            ];
            DB::commit();
            return response()->json([
                'status'  => 'success',
                'message' => 'Registration Successful',
                'data'    => $response
            ],200);
        }catch (\Exception $e){
            return response()->json([
                'status'   => 'failed',
                'message'  => "Couldn't be registered. Please try again",
                'errorMsg' => $e->getMessage()
                
            ]);
        }

    }

    public function login(Request $request)
    {


        $this->validate($request,[
            'username' => 'required|min:5',
            'password'=>'required|min:6',
        ]);

        try {
           
            $user = User::with(
                [   'projects'=>function($query){
                        $query->select('id', 'admin_projects.user_id','project_id');
                    },
                    'roles'=>function($query){
                        $query->with(['permissions']);
                    }

                ]
                )->where('username', $request->username)->first();

            if (!$user){
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Incorrect username',
                ]);
            }

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Incorrect password',
                ]);
            }

            else {
                $token = $user->createToken('Munshiganj_Land_Project')->plainTextToken;

                if(!$token){
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'Failed to login',
                    ]);
                }
                DB::commit();
                return response()->json([
                    'status' => 'success',
                    'message' => 'You have logged in successfully',
                    'token' => $token,
                    'user_info'=>$user
                ]);
            }
        }
        catch (\Exception $e){
            return response()->json([
                'status' => 'failed',
                'message' => "Couldn't be logged in. Please try again",
                'data' => []
            ]);
        }
    }

    public function AllUsers(){
        $allUsers = User::with([
            'roles'=>function($query){
                $query->with(['permissions']);
            },
            'projects'=>function($query){
                $query->with(['singleProjectInfo']);
            }
        ])->get();

        return response()->json([
            'allusers'=>  $allUsers
        ]);
    }


    public function updateUser(Request $request){
        DB::beginTransaction();
        try{
       $user_id  = $request->user_id;
       $userData = User::find($user_id);
       $message  = '';
       $updatedProjectInfo = [];
       $createdProjectInfo = [];
       $userData->name        = $request->name;
       $userData->username    = $request->username;
       $userData->email       = $request->email;
       $userData->designation = $request->designation;
       $userData->mobile_no   = $request->mobile_no;
       $userData->address     = $request->address;
       $userData->nid         = $request->nid;
       $userData->remarks     = $request->remarks;
       $userData->password    = bcrypt($request->password);
       if ($request->hasFile('image')) {
        if(file_exists(public_path($userData->image)) && isset($userData->image)){
            File::delete(public_path($userData->image));
        }
        $userData->image=$this->getImageUrl($request->file('image') ?? $userData->image,'user/image/');
      }
        $file = $request->file('document');
         if ($file !== null) {
            // Delete the old file if it exists
             if (!empty($userData->file_path) && file_exists(public_path($userData->file_path))) {
                 unlink(public_path($userData->file_path));
             }
             $file_name       = date('Ymd-his') . '.' . $file->getClientOriginalExtension();
             $destinationPath = public_path('user/document/');
             $file->move($destinationPath, $file_name);
             $file_path           = 'user/document/' . $file_name;
             $file_name           = $file->getClientOriginalName();
             $userData->file_path = $file_path;
             $userData->file_name = $file_name;
         }
        $userData->save();
        if($request->role_id){
            $role = Role::find($request->role_id);
            if (!$userData->hasRole($role->name)) {
                $userData->syncRoles($role);
                $message .='Role updated. '  ;
            } else {
                $message .= 'The Role is already assigned to the user. '  ;
            }
        }
        if(is_array($request->project_info)){
        $projectInfoArr      = $request->project_info;
        $projectInfoLength   = count($projectInfoArr);
        $singleProjectData   = AdminProject::where('user_id', $user_id)->get();
        $delete= $singleProjectData->each->delete();
        for ($i = 0; $i < $projectInfoLength; $i++)
        {
            $projectId = $projectInfoArr[$i]['project_id'];
                $assignProject = new AdminProject();
                $assignProject->user_id    = $user_id;
                $assignProject->project_id = $projectId;
                $assignProject->save();
                $createdProjectInfo[]      = $assignProject;
        }
        }
        DB::commit();
        return response()->json([
            'status'          => "success",
            'message'         => "user update successfully",
            'user'            => $userData,
            'project_info'    => $createdProjectInfo,
        ]);
    }
     catch (\Exception $e){
        DB::rollback();
        return response()->json([
            'status'   => 'failed',
            'message'  => "Couldn't be update. Please try again",
            'errorMsg' => $e->getMessage()
        ]);
    }
    }


    public function revoke(User $user,$tokenId)
    {
        $user->tokens()->where('id', $tokenId)->delete();
        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }
}
