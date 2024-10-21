<?php

namespace App\Http\Controllers\LandMutationBank;

use App\Http\Controllers\Controller;
use App\Http\Traits\UserDataTraits;
use App\Models\LandMutationBank\CSMutationInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CSMutationController extends Controller
{    
    use UserDataTraits;
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'cs_id'=> 'required',
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
            $csMutationInfo = new CSMutationInfo();

            $csMutationInfo->cs_id = $request->cs_id;
            $csMutationInfo->mutation_date = $request->mutation_date;
            $csMutationInfo->mutation_no = $request->mutation_no;
            $csMutationInfo->land_size = $request->land_size;
            $csMutationInfo->owner_name = $request->owner_name;
            $csMutationInfo->ref_deed_no = $request->ref_deed_no;
            $csMutationInfo->ref_deed_no_date = $request->ref_deed_no_date;
            $csMutationInfo->misscase_no = $request->misscase_no;
            $csMutationInfo->misscase_date = $request->misscase_date;
            $csMutationInfo->misscase_judgement_date = $request->misscase_judgement_date;
            $csMutationInfo->description = $request->description;
            $csMutationInfo->save();

            DB::commit();
            return response([
                'status' => 'success',
                'message' => 'CS Mutation Info successfully added',
                'landInformation' => $csMutationInfo
            ], 200);
         }
         catch(\Exception $e)
         {
             DB::rollback();
             return response()->json([
                 'status' => 'failed',
                 'message' => 'CS Mutation Info couldn\'t be loaded'
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
    
        $csMutationInfoData=[];

        if(!$superAdmin && $admin && isset($userData)){
            foreach($userData as $project_id){
                $datas = CSMutationInfo::with([
                    'csInfo' => function ($query) {
                           $query->with(['mouzaInfo' => function ($query) {
                               $query->with(['projectInfo']);
                           }]);
                       }
                   ])->whereHas('csInfo.mouzaInfo.projectInfo',function ($query) use ($project_id) 
                 {
                 $query->where('project_id', $project_id->project_id);
                 }
                 )->get();
                
                 if ($datas->isNotEmpty()) {
                    array_push($csMutationInfoData, $datas);
                }
            }
            return response()->json([
                'data' =>$csMutationInfoData
             ],200);
        }
    
       else if($superAdmin){
        $csMutationInfoData = CSMutationInfo::with([
            'csInfo' => function ($query) {
                   $query->with(['mouzaInfo' => function ($query) {
                       $query->with(['projectInfo']);
                   }]);
               }
           ])->get();
             return response()->json([
                'data' =>  $csMutationInfoData
             ],200);
        }

    }
    public function update(Request $request)
    {
        try {

            $csMutationInfoData = CSMutationInfo::find($request->id);
            $csMutationInfoData->cs_id = $request->cs_id;
            $csMutationInfoData->mutation_date = $request->mutation_date;
            $csMutationInfoData->mutation_no = $request->mutation_no;
            $csMutationInfoData->land_size = $request->land_size;
            $csMutationInfoData->owner_name = $request->owner_name;
            $csMutationInfoData->ref_deed_no = $request->ref_deed_no;
            $csMutationInfoData->ref_deed_no_date = $request->ref_deed_no_date;
            $csMutationInfoData->misscase_no = $request->misscase_no;
            $csMutationInfoData->misscase_date = $request->misscase_date;
            $csMutationInfoData->misscase_judgement_date = $request->misscase_judgement_date;
            $csMutationInfoData->description = $request->description;
            if ($csMutationInfoData->save()) {
                return response([
                    'status' => 'success',
                    'message' => 'CS Mutation List successfully updated',
                    'CSMutation' => $csMutationInfoData,
                ], 200);
            } else {
                return response([
                    'status' => 'failed',
                    'message' => 'CS Mutation List update failed',
                ], 500);
            }
        } catch (\Exception $e) {
            return response([
                'status' => 'failed',
                'message' => 'CS Mutation List update failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
