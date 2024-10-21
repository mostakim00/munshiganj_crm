<?php

namespace App\Http\Controllers\LandMutationBank;

use App\Http\Controllers\Controller;
use App\Http\Traits\UserDataTraits;
use App\Models\LandMutationBank\CSMutationInfo;
use App\Models\LandMutationBank\SAMutationInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SAMutationController extends Controller
{
    use UserDataTraits;
 
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sa_id'=> 'required',
            "mutation_no" => 'required',
            "land_size" => 'required',
            "misscase_no" => 'required',
        ]);
        if ($validator->fails()){
            return response()->json([
                'status' => 'failed',
                'message' =>  $validator->messages()->all()
            ]);
        }
        DB::beginTransaction();
        try {
            $saMutationInfo = new SAMutationInfo();

            $saMutationInfo->sa_id = $request->sa_id;
            $saMutationInfo->mutation_date = $request->mutation_date;
            $saMutationInfo->mutation_no = $request->mutation_no;
            $saMutationInfo->land_size = $request->land_size;
            $saMutationInfo->owner_name = $request->owner_name;
            $saMutationInfo->ref_deed_no = $request->ref_deed_no;
            $saMutationInfo->ref_deed_no_date = $request->ref_deed_no_date;
            $saMutationInfo->misscase_no = $request->misscase_no;
            $saMutationInfo->misscase_date = $request->misscase_date;
            $saMutationInfo->misscase_judgement_date = $request->misscase_judgement_date;
            $saMutationInfo->description = $request->description;
            $saMutationInfo->save();

            DB::commit();
            return response([
                'status' => 'success',
                'message' => 'SA Mutation Info successfully added',
                'landInformation' => $saMutationInfo
            ], 200);
        }
        catch(\Exception $e)
        {
            DB::rollback();
            return response()->json([
                'status' => 'failed',
                'message' => 'SA Mutation Info couldn\'t be loaded'
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

        
        $saMutationInfoData=[];
        if(!$superAdmin && $admin && isset($userData)){
            foreach($userData as $project_id){
                $datas =SAMutationInfo::with([
                'saInfo'=> function ($query) {
                        $query->with(['csInfo' => function ($query) {
                            $query->with(['mouzaInfo' => function($query){
                                $query->with(['projectInfo']);
                            }]);
                        }]);
                    }
                ])->whereHas('saInfo.csInfo.mouzaInfo.projectInfo',function ($query) use ($project_id) 
                 {
                 $query->where('project_id', $project_id->project_id);
                 }
                 )->get();
                
                 if ($datas->isNotEmpty()) {
                    array_push($saMutationInfoData, $datas);
                }
            }
            return response()->json([
                'data' =>$saMutationInfoData
             ],200);
        }
    
       else if($superAdmin){

        $saMutationInfoData = SAMutationInfo::with([
        'saInfo'=> function ($query) {
                $query->with(['csInfo' => function ($query) {
                    $query->with(['mouzaInfo' => function($query){
                        $query->with(['projectInfo']);
                    }]);
                }]);
            }
        ])->get();
        return response()->json([
            "data" => $saMutationInfoData
        ],200);
    
    }
        
       
    }
    
    public function update(Request $request)
    {
        try {

            $saMutationInfoData = SAMutationInfo::find($request->id);
            $saMutationInfoData->sa_id = $request->sa_id;
            $saMutationInfoData->mutation_date = $request->mutation_date;
            $saMutationInfoData->mutation_no = $request->mutation_no;
            $saMutationInfoData->land_size = $request->land_size;
            $saMutationInfoData->owner_name = $request->owner_name;
            $saMutationInfoData->ref_deed_no = $request->ref_deed_no;
            $saMutationInfoData->ref_deed_no_date = $request->ref_deed_no_date;
            $saMutationInfoData->misscase_no = $request->misscase_no;
            $saMutationInfoData->misscase_date = $request->misscase_date;
            $saMutationInfoData->misscase_judgement_date = $request->misscase_judgement_date;
            $saMutationInfoData->description = $request->description;
            if ($saMutationInfoData->save()) {
                return response([
                    'status' => 'success',
                    'message' => 'SA Mutation List successfully updated',
                    'CSMutation' => $saMutationInfoData,
                ], 200);
            } else {
                return response([
                    'status' => 'failed',
                    'message' => 'SA Mutation List update failed',
                ], 500);
            }
        } catch (\Exception $e) {
            return response([
                'status' => 'failed',
                'message' => 'SA Mutation List update failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
