<?php

namespace App\Http\Controllers\LandInformationBank;

use App\Http\Controllers\Controller;
use App\Http\Traits\UserDataTraits;
use App\Models\LandBorker\LandBroker;
use Illuminate\Http\Request;
use App\Models\LandInformationBank\ProjectInformation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Auth; 


class ProjectInformationController extends Controller
{
    use UserDataTraits;
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_name'=> 'required',
        ]);
        if ($validator->fails()){
            return response()->json([
                'status' => 'failed',
                'message' =>  $validator->messages()->all()
            ]);
        }
        DB::beginTransaction();
        try {
            $projectInformation = new ProjectInformation();

            $file = $request->file('map_image') ?? null;
            $file_path = null;
            if ($file && $file !== 'null') {
                $file_name = date('Ymd-his') . '.' . $file->getClientOriginalExtension();
                $destinationPath = 'landInformationBank/projectInformation/image/' . $file_name;
                Image::make($file->getRealPath())->resize(400, 300)->save(public_path($destinationPath));
                $file_path = $destinationPath;
            }

            $projectInformation->project_name = $request->project_name;
            $projectInformation->district = $request->district ?? null;
            $projectInformation->thana = $request->thana ?? null;
            $projectInformation->area = $request->area ?? null;
            $projectInformation->number_of_mouza = $request->number_of_mouza ?? 0;
            $projectInformation->map_image = $file_path;

            $projectInformation->save();

            DB::commit();

            return response([
                'status' => 'success',
                'message' => 'Land Information successfully added',
                'landInformation' => $projectInformation
            ], 200);
        }
        catch(\Exception $e)
        {
            DB::rollback();
            return response()->json([
                'status' => 'failed',
                'message' => 'Land information couldn\'t be loaded'
            ]);
        }
    }

    public function update(Request $request)
    {
        try {

            $projectInformationData = ProjectInformation::where('id', $request->id)->first();
            $projectInformationData->project_name = $request->project_name;
            $projectInformationData->district = $request->district;
            $projectInformationData->thana = $request->thana;
            $projectInformationData->area = $request->area;
            $projectInformationData->number_of_mouza = $request->number_of_mouza ?? $projectInformationData->number_of_mouza;

            $file = $request->file('map_image') ?? null;
            $file_path = null;
            if ($file && $file !== 'null') {
                $file_name = date('Ymd-his') . '.' . $file->getClientOriginalExtension();
                $destinationPath = 'landInformationBank/projectInformation/image/' . $file_name;
                Image::make($file->getRealPath())->resize(400, 300)->save(public_path($destinationPath));
                $file_path = $destinationPath;
                if(file_exists(public_path($projectInformationData->map_image)) && isset($projectInformationData->map_image))
            {
                unlink(public_path($projectInformationData->map_image));
            }

            $projectInformationData->map_image = $file_path;
            }

            if ($projectInformationData->save()) {
                return response([
                    'status' => 'success',
                    'message' => 'Project information successfully updated',
                    'projectInfo' => $projectInformationData,
                ], 200);
            } else {
                return response([
                    'status' => 'failed',
                    'message' => 'Project information update failed',
                ], 500);
            }
        } catch (\Exception $e) {
            return response([
                'status' => 'failed',
                'message' => 'Project information update failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function view()

    {   
        $user = Auth::user();
        $data = $this->getUserData($user);
        
        $userData    = $data['userData'];
        $superAdmin  = $data['superAdmin'];
        $admin       = $data['admin'];
        
        $assignProjectInfo=[];

        if(!$superAdmin && $admin && isset($userData)){
            foreach($userData as $project_id){
                $datas=ProjectInformation::where('id',$project_id->project_id)->first();
                
                if($datas){
                    array_push($assignProjectInfo, $datas);
                }
            }
            return response()->json([
                'data' =>  $assignProjectInfo
             ],200);
        }

       else if($superAdmin){
             $projectInformationData = ProjectInformation::all();
             return response()->json([
                'data' =>  $projectInformationData
             ],200);
        }
        
       
        
    }
}
