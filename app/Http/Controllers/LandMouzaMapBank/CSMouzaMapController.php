<?php

namespace App\Http\Controllers\LandMouzaMapBank;

use App\Http\Controllers\Controller;
use App\Http\Traits\UserDataTraits;
use App\Models\LandMouzaMapBank\CSMouzaMap\CSMouzaMapInfo;
use App\Models\LandMouzaMapBank\CSMouzaMap\CSMouzaMapInfoImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CSMouzaMapController extends Controller
{
    use UserDataTraits;
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cs_id'=> 'required',
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

        DB::beginTransaction();
        try {
            // Add attributes
            $csMouzaMap = new CSMouzaMapInfo();
            $csMouzaMap->cs_id = $request->cs_id;
            $csMouzaMap->land_size_sotangsho = $request->land_size_sotangsho;
            $csMouzaMap->land_size_ojutangsho = $request->land_size_ojutangsho;
            $csMouzaMap->land_size_sq_feet = $request->land_size_sq_feet;
            $csMouzaMap->land_eastTowest_sq_feet = $request->land_eastTowest_sq_feet;
            $csMouzaMap->land_northToSouth_sq_feet = $request->land_northToSouth_sq_feet;
            $csMouzaMap->land_eastAndSouth_angle = $request->land_eastAndSouth_angle;
            $csMouzaMap->land_eastAndNorth_angle = $request->land_eastAndNorth_angle;
            $csMouzaMap->land_westAndSouth_angle = $request->land_westAndSouth_angle;
            $csMouzaMap->land_westAndNorth_angle = $request->land_westAndNorth_angle;
            $csMouzaMap->eastSouth_to_westNorth_length = $request->eastSouth_to_westNorth_length;
            $csMouzaMap->southWest_to_northEast_length = $request->southWest_to_northEast_length;
            $csMouzaMap->description = $request->description;
//            return response()->json($csMouzaMap);
            $csMouzaMap->save();

            // Add multiple image
            $files =  $request->file('map_image');
            if ($files && $files[0] !== null){
                foreach ($files as $file)
                {
                    $file_name = date('Ymd-his') . '.' . $file->getClientOriginalName();
                    $destinationPath = public_path('landMouzaMapBank/csMouzaMapList/image/');
                    $file->move($destinationPath, $file_name);
                    $file_path = 'landMouzaMapBank/csMouzaMapList/image/' . $file_name;
                    $file_name = $file->getClientOriginalName();
                    $csMouzaMapInfoImage = new CSMouzaMapInfoImage();
                    $csMouzaMapInfoImage->cs_mouza_map_id = $csMouzaMap->id;
                    $csMouzaMapInfoImage->file_path = $file_path;
                    $csMouzaMapInfoImage->file_name = $file_name;
                    $csMouzaMapInfoImage->save();
                }
            }
            DB::commit();
            return response([
                'status' => 'success',
                'message' => 'CS Mouza Map Information added successfully',
                'agentInfo' => $csMouzaMap
            ], 200);
        }
        catch(\Exception $e)
        {
            DB::rollback();
            return response()->json([
                'status' => 'failed',
                'message' => 'CS Mouza Map Information couldn\'t be loaded'
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
                $datas = CSMouzaMapInfo::with([ 'map_image',
                'csInfo' => function ($query) {
                    $query->with([
                        'mouzaInfo' => function ($query) {
                            $query->with(['projectInfo']);
                        }
                    ]);
                }
            ])->whereHas('csInfo.mouzaInfo.projectInfo',function ($query) use ($project_id) 
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

        $csMouzaMapInfo = CSMouzaMapInfo::with([
        'map_image',
        'csInfo' => function ($query) {
            $query->with([
                'mouzaInfo' => function ($query) {
                    $query->with(['projectInfo']);
                }
            ]);
        }
        ])->get();
        return response()->json([
            "data" => $csMouzaMapInfo
        ], 200);

        }


    
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cs_id'=> 'required',
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

        if ($validator->fails()) {
            return response()->json([
                'status' => 'failed',
                'message' => $validator->messages()->all()
            ]);
        }

        try {
            $csMouzaMapInfo = CSMouzaMapInfo::find($request->id);

            // Update attributes
            $csMouzaMapInfo->cs_id = $request->cs_id;
            $csMouzaMapInfo->land_size_sotangsho = $request->land_size_sotangsho;
            $csMouzaMapInfo->land_size_ojutangsho = $request->land_size_ojutangsho;
            $csMouzaMapInfo->land_size_sq_feet = $request->land_size_sq_feet;
            $csMouzaMapInfo->land_eastTowest_sq_feet = $request->land_eastTowest_sq_feet;
            $csMouzaMapInfo->land_northToSouth_sq_feet = $request->land_northToSouth_sq_feet;
            $csMouzaMapInfo->land_eastAndSouth_angle = $request->land_eastAndSouth_angle;
            $csMouzaMapInfo->land_eastAndNorth_angle = $request->land_eastAndNorth_angle;
            $csMouzaMapInfo->land_westAndSouth_angle = $request->land_westAndSouth_angle;
            $csMouzaMapInfo->land_westAndNorth_angle = $request->land_westAndNorth_angle;
            $csMouzaMapInfo->eastSouth_to_westNorth_length = $request->eastSouth_to_westNorth_length;
            $csMouzaMapInfo->southWest_to_northEast_length = $request->southWest_to_northEast_length;
            $csMouzaMapInfo->description = $request->description;

            // Retrieve existing images associated with the CSMouzaMapInfo
            $existingImages = $csMouzaMapInfo->images;

            if ($existingImages !== null) {
                // Delete existing images from storage
                foreach ($existingImages as $existingImage) {
                    if (file_exists(public_path($existingImage->file_path))) {
                        unlink(public_path($existingImage->file_path));
                    }
                    $existingImage->delete();
                }
            }

            // Store new images
            $files = $request->file('map_image');
            if ($files && $files[0] !== null) {
                foreach ($files as $file) {
                    $file_name = date('Ymd-his') . '.' . $file->getClientOriginalName();
                    $destinationPath = public_path('landMouzaMapBank/csMouzaMapList/image/');
                    $file->move($destinationPath, $file_name);
                    $file_path = 'landMouzaMapBank/csMouzaMapList/image/' . $file_name;
                    $file_name = $file->getClientOriginalName();

                    $csMouzaMapInfoImage = new CSMouzaMapInfoImage();
                    $csMouzaMapInfoImage->cs_mouza_map_id = $csMouzaMapInfo->id;
                    $csMouzaMapInfoImage->file_path = $file_path;
                    $csMouzaMapInfoImage->file_name = $file_name;
                    $csMouzaMapInfoImage->save();
                }
            }

            if ($csMouzaMapInfo->save()) {
                return response([
                    'status' => 'success',
                    'message' => 'CS Mouza Map Information successfully updated',
                    'agentInfo' => $csMouzaMapInfo,
                ], 200);
            } else {
                return response([
                    'status' => 'error',
                    'message' => 'CS Mouza Map Information update failed'
                ], 500);
            }

        } catch (\Exception $e) {
            return response([
                'status' => 'error',
                'message' => 'CS Mouza Map Information update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }



}
