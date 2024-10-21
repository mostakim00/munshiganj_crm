<?php

namespace App\Http\Controllers\LandMouzaMapBank;

use App\Http\Controllers\Controller;
use App\Http\Traits\UserDataTraits;
use App\Models\LandMouzaMapBank\RSMouzaMap\RSMouzaMapInfo;
use App\Models\LandMouzaMapBank\RSMouzaMap\RSMouzaMapInfoImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RSMouzaMapController extends Controller
{
    use UserDataTraits;
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rs_id'=> 'required',
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
            //Add attributes
            $rsMouzaMap = new RSMouzaMapInfo();
            $rsMouzaMap->rs_id = $request->rs_id;
            $rsMouzaMap->land_size_sotangsho = $request->land_size_sotangsho;
            $rsMouzaMap->land_size_ojutangsho = $request->land_size_ojutangsho;
            $rsMouzaMap->land_size_sq_feet = $request->land_size_sq_feet;
            $rsMouzaMap->land_eastTowest_sq_feet = $request->land_eastTowest_sq_feet;
            $rsMouzaMap->land_northToSouth_sq_feet = $request->land_northToSouth_sq_feet;
            $rsMouzaMap->land_eastAndSouth_angle = $request->land_eastAndSouth_angle;
            $rsMouzaMap->land_eastAndNorth_angle = $request->land_eastAndNorth_angle;
            $rsMouzaMap->land_westAndSouth_angle = $request->land_westAndSouth_angle;
            $rsMouzaMap->land_westAndNorth_angle = $request->land_westAndNorth_angle;
            $rsMouzaMap->eastSouth_to_westNorth_length = $request->eastSouth_to_westNorth_length;
            $rsMouzaMap->southWest_to_northEast_length = $request->southWest_to_northEast_length;
            $rsMouzaMap->description = $request->description;
//            return response()->json($rsMouzaMap);
            $rsMouzaMap->save();

            //Add Multiple Image
            $files = $request->file('map_image');
            if ($files && $files[0] !== null){
                foreach ($files as $file)
                {
                    $file_name = date('Ymd-his') . '.' . $file->getClientOriginalName();
                    $destinationPath = public_path('landMouzaMapBank/rsMouzaMapList/image/');
                    $file->move($destinationPath, $file_name);
                    $file_path = 'landMouzaMapBank/rsMouzaMapList/image/' . $file_name;
                    $file_name = $file->getClientOriginalName();

                    $rsMouzaMapInfoImage = new RSMouzaMapInfoImage();
                    $rsMouzaMapInfoImage->rs_mouza_map_id = $rsMouzaMap->id;
                    $rsMouzaMapInfoImage->file_name = $file_name;
                    $rsMouzaMapInfoImage->file_path = $file_path;
                    $rsMouzaMapInfoImage->save();
                }
            }
            DB::commit();
            return response([
                'status' => 'success',
                'message' => 'RS Mouza Map Information added successfully',
                'agentInfo' => $rsMouzaMap
            ], 200);

        } catch (\Exception $e){
            DB::rollBack();
            return response()->json([
                'status' => 'Faield',
                'message' => 'RS Mouza Map Information couldn\'t be loaded'
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
                $datas = RSMouzaMapInfo::with([ 'map_image',
                'rsInfo' => function ($query) {
                    $query->with([
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
                    ]);
                }
            ])->whereHas('rsInfo.saInfo.csInfo.mouzaInfo.projectInfo',function ($query) use ($project_id) 
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

        $rsDisputeData = RSMouzaMapInfo::with([ 'map_image',
        'rsInfo' => function ($query) {
            $query->with([
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
            ]);
        }
    ])->get();
    return response()->json([
        "data" => $rsDisputeData
    ]);
        
         }


    
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rs_id'=> 'required',
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
            $rsMouzaMapInfo = RSMouzaMapInfo::find($request->id);
            //ADD attributes
            $rsMouzaMapInfo->rs_id = $request->rs_id;
            $rsMouzaMapInfo->land_size_sotangsho = $request->land_size_sotangsho;
            $rsMouzaMapInfo->land_size_ojutangsho = $request->land_size_ojutangsho;
            $rsMouzaMapInfo->land_size_sq_feet = $request->land_size_sq_feet;
            $rsMouzaMapInfo->land_eastTowest_sq_feet = $request->land_eastTowest_sq_feet;
            $rsMouzaMapInfo->land_northToSouth_sq_feet = $request->land_northToSouth_sq_feet;
            $rsMouzaMapInfo->land_eastAndSouth_angle = $request->land_eastAndSouth_angle;
            $rsMouzaMapInfo->land_eastAndNorth_angle = $request->land_eastAndNorth_angle;
            $rsMouzaMapInfo->land_westAndSouth_angle = $request->land_westAndSouth_angle;
            $rsMouzaMapInfo->land_westAndNorth_angle = $request->land_westAndNorth_angle;
            $rsMouzaMapInfo->eastSouth_to_westNorth_length = $request->eastSouth_to_westNorth_length;
            $rsMouzaMapInfo->southWest_to_northEast_length = $request->southWest_to_northEast_length;
            $rsMouzaMapInfo->description = $request->description;

            // Retrieve existing images associated with the SAMouzaMapInfo
            $existingImages = $rsMouzaMapInfo->images;

            if ($existingImages !== null) {
                // Delete existing images from storage
                foreach ($existingImages as $existingImage) {
                    if (file_exists(public_path($existingImage->file_path))) {
                        unlink(public_path($existingImage->file_path));
                    }
                    $existingImage->delete();
                }
            }
            //Add Multiple Image
            $files = $request->file('map_image');
            if ($files && $files[0] !== null)
            {
                foreach ($files as $file) {
                    $file_name = date('Ymd-his') . '.' . $file->getClientOriginalName();
                    $destinationPath = public_path('landMouzaMapBank/rsMouzaMapList/image/');
                    $file->move($destinationPath, $file_name);
                    $file_path = 'landMouzaMapBank/rsMouzaMapList/image/' . $file_name;
                    $file_name = $file->getClientOriginalName();

                    $rsMouzaMapInfoImage = new RSMouzaMapInfoImage();
                    $rsMouzaMapInfoImage->rs_mouza_map_id = $rsMouzaMapInfo->id;
                    $rsMouzaMapInfoImage->file_name = $file_name;
                    $rsMouzaMapInfoImage->file_path = $file_path;
                    $rsMouzaMapInfoImage->save();
                }
            }
            if ($rsMouzaMapInfo->save()) {
                return response([
                    'status' => 'success',
                    'message' => 'RS Mouza Map Information successfully updated',
                    'agentInfo' => $rsMouzaMapInfo,
                ], 200);
            } else {
                return response([
                    'status' => 'error',
                    'message' => 'RS Mouza Map Information update failed',
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
