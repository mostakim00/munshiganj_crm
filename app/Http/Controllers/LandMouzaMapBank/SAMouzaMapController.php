<?php

namespace App\Http\Controllers\LandMouzaMapBank;

use App\Http\Controllers\Controller;
use App\Http\Traits\UserDataTraits;
use App\Models\LandMouzaMapBank\SAMouzaMap\SAMouzaMapInfo;
use App\Models\LandMouzaMapBank\SAMouzaMap\SAMouzaMapInfoImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SAMouzaMapController extends Controller
{
    use UserDataTraits;
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sa_id'=> 'required',
            'land_size_sotangsho' => 'required',
            'land_size_ojutangsho' => 'required',
            'land_size_sq_feet' => 'required',
            'land_eastTowest_sq_feet' => 'required',
            'land_northToSouth_sq_feet' => 'required',
            'land_eastAndSouth_angle' => 'required',
            'land_eastAndNorth_angle' => 'required',
            'land_westAndSouth_angle' => 'required',
            'land_westAndNorth_angle' => 'required',
            'eastSouth_to_westNorth_length' => 'required',
            'southWest_to_northEast_length' => 'required'
        ]);
        if ($validator->fails()){
            return response()->json([
                'status' => 'failed',
                'message' =>  $validator->messages()->all(),
            ]);
        }
        try {
            // Add attributes
            $saMouzaMap = new SAMouzaMapInfo();
            $saMouzaMap->sa_id = $request->sa_id;
            $saMouzaMap->land_size_sotangsho = $request->land_size_sotangsho;
            $saMouzaMap->land_size_ojutangsho = $request->land_size_ojutangsho;
            $saMouzaMap->land_size_sq_feet = $request->land_size_sq_feet;
            $saMouzaMap->land_eastTowest_sq_feet = $request->land_eastTowest_sq_feet;
            $saMouzaMap->land_northToSouth_sq_feet = $request->land_northToSouth_sq_feet;
            $saMouzaMap->land_eastAndSouth_angle = $request->land_eastAndSouth_angle;
            $saMouzaMap->land_eastAndNorth_angle = $request->land_eastAndNorth_angle;
            $saMouzaMap->land_westAndSouth_angle = $request->land_westAndSouth_angle;
            $saMouzaMap->land_westAndNorth_angle = $request->land_westAndNorth_angle;
            $saMouzaMap->eastSouth_to_westNorth_length = $request->eastSouth_to_westNorth_length;
            $saMouzaMap->southWest_to_northEast_length = $request->southWest_to_northEast_length;
            $saMouzaMap->description = $request->description;
//            return response()->json($saMouzaMap);
            $saMouzaMap->save();

            //Add Multiple Image
            $files = $request->file('map_image');
            if ($files && $files[0] !== null){
                foreach ($files as $file)
                {
                    $file_name = date('Ymd-his') . '.' . $file->getClientOriginalName();
                    $destinationPath = public_path('landMouzaMapBank/saMouzaMapList/image/');
                    $file->move($destinationPath, $file_name);
                    $file_path = 'landMouzaMapBank/saMouzaMapList/image/' . $file_name;
                    $file_name = $file->getClientOriginalName();

                    $saMouzaMapInfoImage = new SAMouzaMapInfoImage();
                    $saMouzaMapInfoImage->sa_mouza_map_id = $saMouzaMap->id;
                    $saMouzaMapInfoImage->file_name = $file_name;
                    $saMouzaMapInfoImage->file_path = $file_path;
                    $saMouzaMapInfoImage->save();
                }
            }
            DB::commit();
            return response([
                'status' => 'success',
                'message' => 'SA Mouza Map Information added successfully',
                'agentInfo' => $saMouzaMap
            ], 200);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'failed',
                'message' => 'SA Mouza Map Information couldn\'t be loaded'
            ]);
        }
    }


    public function view()
    {
        $user = Auth::user();
        $data = $this->getUserData($user);
        
        $userData    = $data['userData'];
        $superAdmin  = $data['superAdmin'];
        $admin       = $data['admin'];
    
        $csDisputeInfoData=[];

        if(!$superAdmin && $admin && isset($userData)){
            foreach($userData as $project_id){
            $datas = SAMouzaMapInfo::with([ 'map_image',
            'saInfo' => function ($query) {
                $query->with([
                    'csInfo' => function ($query) {
                        $query->with([
                            'mouzaInfo' => function ($query) {
                                $query->with([
                                    'projectInfo'
                                ]);
                            }
                        ]);
                    }
                ]);
            }
            ])->whereHas('saInfo.csInfo.mouzaInfo.projectInfo',function ($query) use ($project_id) 
                {
                $query->where('id', $project_id->project_id);
                }
                )->get();
                
                if ($datas->isNotEmpty()) {
                    array_push($csDisputeInfoData, $datas);
                }
                
            }
            return response()->json([
                'data' => $csDisputeInfoData
             ],200);
        }
    
       else if($superAdmin){

         $saMouzaMapInfoData = SAMouzaMapInfo::with([ 
         'map_image',
         'saInfo' => function ($query) {
             $query->with([
                 'csInfo' => function ($query) {
                     $query->with([
                         'mouzaInfo' => function ($query) {
                             $query->with([
                                 'projectInfo'
                             ]);
                         }
                     ]);
                 }
             ]);
         }
        ])->get();
        
         return response()->json([
             "data" => $saMouzaMapInfoData
         ]);
        
         }


    
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sa_id'=> 'required',
            'land_size_sotangsho' => 'required',
            'land_size_ojutangsho' => 'required',
            'land_size_sq_feet' => 'required',
            'land_eastTowest_sq_feet' => 'required',
            'land_northToSouth_sq_feet' => 'required',
            'land_eastAndSouth_angle' => 'required',
            'land_eastAndNorth_angle' => 'required',
            'land_westAndSouth_angle' => 'required',
            'land_westAndNorth_angle' => 'required',
            'eastSouth_to_westNorth_length' => 'required',
            'southWest_to_northEast_length' => 'required'
        ]);
        if ($validator->fails()){
            return response()->json([
                'status' => 'failed',
                'message' =>  $validator->messages()->all()
            ]);
        }
        try {
            $saMouzaMapInfo = SAMouzaMapInfo::find($request->id);
            //Update Attributes
            $saMouzaMapInfo->sa_id = $request->sa_id;
            $saMouzaMapInfo->land_size_sotangsho = $request->land_size_sotangsho;
            $saMouzaMapInfo->land_size_ojutangsho = $request->land_size_ojutangsho;
            $saMouzaMapInfo->land_size_sq_feet = $request->land_size_sq_feet;
            $saMouzaMapInfo->land_eastTowest_sq_feet = $request->land_eastTowest_sq_feet;
            $saMouzaMapInfo->land_northToSouth_sq_feet = $request->land_northToSouth_sq_feet;
            $saMouzaMapInfo->land_eastAndSouth_angle = $request->land_eastAndSouth_angle;
            $saMouzaMapInfo->land_eastAndNorth_angle = $request->land_eastAndNorth_angle;
            $saMouzaMapInfo->land_westAndSouth_angle = $request->land_westAndSouth_angle;
            $saMouzaMapInfo->land_westAndNorth_angle = $request->land_westAndNorth_angle;
            $saMouzaMapInfo->eastSouth_to_westNorth_length = $request->eastSouth_to_westNorth_length;
            $saMouzaMapInfo->southWest_to_northEast_length = $request->southWest_to_northEast_length;
            $saMouzaMapInfo->description = $request->description;

            // Retrieve existing images associated with the SAMouzaMapInfo
            $existingImages = $saMouzaMapInfo->images;

            if ($existingImages !== null){
                // Delete existing images from storage
                foreach ($existingImages as $existingImage){
                    if (file_exists(public_path($existingImage->file_path))){
                        unlink(public_path($existingImage->file_path));
                    }
                    $existingImage->delete();
                }
            }
            // Store new images
            $files = $request->file('map_image');
            if ($files && $files[0] !== null){
                foreach ($files as $file)
                {
                    $file_name = date('Ymd-his') . '.' . $file->getClientOriginalName();
                    $destinationPath = public_path('landMouzaMapBank/saMouzaMapList/image/');
                    $file->move($destinationPath, $file_name);
                    $file_path = 'landMouzaMapBank/saMouzaMapList/image/' . $file_name;
                    $file_name = $file->getClientOriginalName();

                    $saMouzaMapInfoImage = new SAMouzaMapInfoImage();
                    $saMouzaMapInfoImage->sa_mouza_map_id = $saMouzaMapInfo->id;
                    $saMouzaMapInfoImage->file_name = $file_name;
                    $saMouzaMapInfoImage->file_path = $file_path;
                    $saMouzaMapInfoImage->save();
                }
            }
            if ($saMouzaMapInfo->save()) {
                return response([
                    'status' => 'success',
                    'message' => 'SA Mouza Map Information successfully updated',
                    'agentInfo' => $saMouzaMapInfo,
                ], 200);
            } else {
                return response([
                    'status' => 'error',
                    'message' => 'SA Mouza Map Information update failed',
                ], 500);
            }

        } catch (\Exception $e) {
            return response([
                'status' => 'error',
                'message' => 'SA Mouza Map Information update failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
