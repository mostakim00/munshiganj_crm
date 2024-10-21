<?php

namespace App\Http\Controllers\LandMouzaMapBank;

use App\Http\Controllers\Controller;
use App\Models\LandMouzaMapBank\BSMouzaMap\BSMouzaMapInfo;
use App\Models\LandMouzaMapBank\BSMouzaMap\BSMouzaMapInfoImage;
use http\Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BSMouzaMapController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bs_id'=> 'required',
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
            $bsMouzaMap = new BSMouzaMapInfo();
            //ADD Attributes
            $bsMouzaMap->bs_id = $request->bs_id;
            $bsMouzaMap->land_size_sotangsho = $request->land_size_sotangsho;
            $bsMouzaMap->land_size_ojutangsho = $request->land_size_ojutangsho;
            $bsMouzaMap->land_size_sq_feet = $request->land_size_sq_feet;
            $bsMouzaMap->land_eastTowest_sq_feet = $request->land_eastTowest_sq_feet;
            $bsMouzaMap->land_northToSouth_sq_feet = $request->land_northToSouth_sq_feet;
            $bsMouzaMap->land_eastAndSouth_angle = $request->land_eastAndSouth_angle;
            $bsMouzaMap->land_eastAndNorth_angle = $request->land_eastAndNorth_angle;
            $bsMouzaMap->land_westAndSouth_angle = $request->land_westAndSouth_angle;
            $bsMouzaMap->land_westAndNorth_angle = $request->land_westAndNorth_angle;
            $bsMouzaMap->eastSouth_to_westNorth_length = $request->eastSouth_to_westNorth_length;
            $bsMouzaMap->southWest_to_northEast_length = $request->southWest_to_northEast_length;
            $bsMouzaMap->description = $request->description;
//            return response()->json($bsMouzaMap);
            $bsMouzaMap->save();

            //ADD Multiple Image
            $files = $request->file('map_image');
            if ($files && $files[0] !== null){
                foreach ($files as $file)
                {
                    $file_name = date('Ymd-his') . '.' . $file->getClientOriginalName();
                    $destinationPath = public_path('landMouzaMapBank/bsMouzaMapList/image/');
                    $file->move($destinationPath, $file_name);
                    $file_path = 'landMouzaMapBank/bsMouzaMapList/image/' . $file_name;
                    $file_name = $file->getClientOriginalName();

                    $bsMouzaMapInfoImage = new BSMouzaMapInfoImage();
                    $bsMouzaMapInfoImage->bs_mouza_map_id = $bsMouzaMap->id;
                    $bsMouzaMapInfoImage->file_name = $file_name;
                    $bsMouzaMapInfoImage->file_path = $file_path;
                    $bsMouzaMapInfoImage->save();
                }
            }
            DB::commit();
            return response([
                'status' => 'success',
                'message' => 'BS Mouza Map Information added successfully',
                'agentInfo' => $bsMouzaMap
            ]);

        } catch (\Exception $e){
            DB::rollback();
            return response()->json([
                'ststus' => 'Faield',
                'message' => 'RS Mouza Map Information couldn\'t be loaded'
            ]);
        }
    }

    public function view()
    {
        $bsInfoData = BSMouzaMapInfo::with([ 'map_image',
            'bsDagInfo' => function ($query) {
                $query->with([
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
                ]);
            }
        ])->get();
        return response()->json([
            "data" => $bsInfoData
        ]);
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
            $datas = BSMouzaMapInfo::with([ 'map_image',
            'bsDagInfo' => function ($query) {
                $query->with([
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

        $bsInfoData = BSMouzaMapInfo::with([ 'map_image',
            'bsDagInfo' => function ($query) {
                $query->with([
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
                ]);
            }
        ])->get();
        return response()->json([
            "data" => $bsInfoData
        ]);
        
         }


    
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bs_id'=> 'required',
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
            $bsMouzaMapInfo = BSMouzaMapInfo::find($request->id);
            //Update Attributes
            $bsMouzaMapInfo->bs_id = $request->bs_id;
            $bsMouzaMapInfo->land_size_sotangsho = $request->land_size_sotangsho;
            $bsMouzaMapInfo->land_size_ojutangsho = $request->land_size_ojutangsho;
            $bsMouzaMapInfo->land_size_sq_feet = $request->land_size_sq_feet;
            $bsMouzaMapInfo->land_eastTowest_sq_feet = $request->land_eastTowest_sq_feet;
            $bsMouzaMapInfo->land_northToSouth_sq_feet = $request->land_northToSouth_sq_feet;
            $bsMouzaMapInfo->land_eastAndSouth_angle = $request->land_eastAndSouth_angle;
            $bsMouzaMapInfo->land_eastAndNorth_angle = $request->land_eastAndNorth_angle;
            $bsMouzaMapInfo->land_westAndSouth_angle = $request->land_westAndSouth_angle;
            $bsMouzaMapInfo->land_westAndNorth_angle = $request->land_westAndNorth_angle;
            $bsMouzaMapInfo->eastSouth_to_westNorth_length = $request->eastSouth_to_westNorth_length;
            $bsMouzaMapInfo->southWest_to_northEast_length = $request->southWest_to_northEast_length;
            $bsMouzaMapInfo->description = $request->description;

            // Retrieve existing images associated with the SAMouzaMapInfo
            $existingImages = $bsMouzaMapInfo->images;

            // Delete existing images from storage
            if ($existingImages !== null){
                foreach ($existingImages as $existingImage){
                    if (file_exists(public_path($existingImage->file_path))){
                        unlink(public_path($existingImage->file_path));
                    }
                    $existingImage->delete();
                }
            }
            //Add multiple image
            $files = $request->file('map_image');
            if ($files && $files[0] !== null){
                foreach ($files as $file)
                {
                    $file_name = date('Ymd-his') . '.' . $file->getClientOriginalName();
                    $destinationPath = public_path('landMouzaMapBank/bsMouzaMapList/image/');
                    $file->move($destinationPath, $file_name);
                    $file_path = 'landMouzaMapBank/bsMouzaMapList/image/' . $file_name;
                    $file_name = $file->getClientOriginalName();

                    $bsMouzaMapInfoImage = new BSMouzaMapInfoImage();
                    $bsMouzaMapInfoImage->bs_mouza_map_id = $bsMouzaMapInfo->id;
                    $bsMouzaMapInfoImage->file_name = $file_name;
                    $bsMouzaMapInfoImage->file_path = $file_path;
                    $bsMouzaMapInfoImage->save();
                }
            }
            if ($bsMouzaMapInfo->save()) {
                return response([
                    'status' => 'success',
                    'message' => 'BS Mouza Map Information successfully updated',
                    'agentInfo' => $bsMouzaMapInfo,
                ], 200);
            } else {
                return response([
                    'status' => 'error',
                    'message' => 'BS Mouza Map Information update failed',
                ], 500);
            }

        } catch (\Exception $e){
            DB::rollBack();
            return response()->json([
                'status' => 'Faield',
                'message' => 'BS Mouza Map Information couldn\'t be loaded'
            ]);
        }
    }
}
