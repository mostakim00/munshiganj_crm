<?php

namespace App\Http\Controllers\LandInformationBank;

use App\Http\Controllers\Controller;
use App\Http\Traits\UserDataTraits;
use Illuminate\Http\Request;
use App\Models\LandInformationBank\MouzaInformation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Auth; 


class MouzaInformationController extends Controller
{
    use UserDataTraits;
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_id'=> 'required',
        ]);
        if ($validator->fails()){
            return response()->json([
                'status' => 'failed',
                'message' =>  $validator->messages()->all()
            ]);
        }
        DB::beginTransaction();
        try {
            $mouzaInformation = new MouzaInformation();

            $file = $request->file('map_image') ?? null;
            $file_path = null;
            if ($file && $file !== 'null') {
                $file_name = date('Ymd-his') . '.' . $file->getClientOriginalExtension();
                $destinationPath = 'landInformationBank/mouzaInformation/image/' . $file_name;
                Image::make($file->getRealPath())->resize(400, 300)->save(public_path($destinationPath));
                $file_path = $destinationPath;
            }

            $mouzaInformation->project_id = $request->project_id;
            $mouzaInformation->mouza_name = $request->mouza_name;
            $mouzaInformation->map_image = $file_path;
            $mouzaInformation->save();

            DB::commit();

            return response([
                'status' => 'success',
                'message' => 'Mouza Information successfully added',
                'mouzaInformation' => $mouzaInformation
            ], 200);
        }
        catch(\Exception $e)
        {
            DB::rollback();
            return response()->json([
                'status' => 'failed',
                'message' => 'Mouza Information couldn\'t be loaded'
            ]);
        }
    }

    public function update(Request $request)
    {
        try {
            $mouzaInformationData = MouzaInformation::where('id', $request->id)->first();

            $mouzaInformationData->project_id = $request->project_id;
            $mouzaInformationData->mouza_name = $request->mouza_name;

            $file = $request->file('map_image') ?? null;
            $file_path = null;
            if ($file && $file !== 'null') {
                $file_name = date('Ymd-his') . '.' . $file->getClientOriginalExtension();
                $destinationPath = 'landInformationBank/mouzaInformation/image/' . $file_name;
                Image::make($file->getRealPath())->resize(400, 300)->save(public_path($destinationPath));
                $file_path = $destinationPath;
                if(file_exists(public_path($mouzaInformationData->map_image)) && isset($mouzaInformationData->map_image))
                {
                    unlink(public_path($mouzaInformationData->map_image));
                }

                $mouzaInformationData->map_image = $file_path;
            }

            if ($mouzaInformationData->save()) {
                return response([
                    'status' => 'success',
                    'message' => 'Mouza information successfully updated',
                    'projectInfo' => $mouzaInformationData,
                ], 200);
            } else {
                return response([
                    'status' => 'error',
                    'message' => 'Mouza information update failed',
                ], 500);
            }
        } catch (\Exception $e) {
            return response([
                'status' => 'error',
                'message' => 'Mouza information update failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
  
    public function view()
    {
        // $userData      = Auth::user()->projects;
        // $superAdmin    = Auth::user()->hasAnyRole('Super Admin');
        // $admin         = Auth::user()->hasAnyRole('Admin');

        $user = Auth::user();
        $data = $this->getUserData($user);
        
        $userData    = $data['userData'];
        $superAdmin  = $data['superAdmin'];
        $admin       = $data['admin'];

        $mouzaInformationData=[];

        if(!$superAdmin && $admin && isset($userData)){
            foreach($userData as $project_id){
                $datas=MouzaInformation::with(['projectInfo'])->where('project_id',$project_id->project_id)->get();
                array_push($mouzaInformationData, $datas);
            }
            return response()->json([
                'data' =>  $mouzaInformationData
             ],200);
        }

       else if($superAdmin){
        $mouzaInformationData = MouzaInformation::with(['projectInfo'])->get();
             return response()->json([
                'data' =>   $mouzaInformationData
             ],200);
        }
    }
}
