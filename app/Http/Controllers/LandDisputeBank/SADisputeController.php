<?php

namespace App\Http\Controllers\LandDisputeBank;

use App\Http\Controllers\Controller;
use App\Http\Traits\UserDataTraits;
use App\Models\LandDisputeBank\SADispute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SADisputeController extends Controller
{
    use UserDataTraits;
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'sa_id'=> 'required',
            'dispute_no' => 'required',
            'dispute_land_size' => 'required',
            'dispute_court_name' => 'required',
            'case_badi_name' => 'required',
            'case_bibadi_name' => 'required'
        ]);
        if ($validator->fails()){
            return response()->json([
                'status' => 'faield',
                'message' => $validator->messages()->all()
            ]);
        }
        try {
            $saDisputeInfo = new SADispute();

            $saDisputeInfo->sa_id = $request->sa_id;
            $saDisputeInfo->dispute_date = $request->dispute_date;
            $saDisputeInfo->dispute_no = $request->dispute_no;
            $saDisputeInfo->dispute_land_size = $request->dispute_land_size;
            $saDisputeInfo->dispute_court_name = $request->dispute_court_name;
            $saDisputeInfo->dispute_judgement_date = $request->dispute_judgement_date;
            $saDisputeInfo->case_badi_name = $request->case_badi_name;
            $saDisputeInfo->case_bibadi_name = $request->case_bibadi_name;
            $saDisputeInfo->judgement_description = $request->judgement_description;
//            return response()->json($saDisputeInfo);
            $saDisputeInfo->save();

            DB::commit();
            return response([
                'status' => 'success',
                'message' => 'CS Dispute Successfully Add',
                'landDespute' => $saDisputeInfo
            ],200);
        }
        catch(\Exception $e)
        {
            DB::rollback();
            return response()->json([
                'status' => 'failed',
                'message' => 'CS Dispute Info couldn\'t be loaded'
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
    
        $saDisputeInfoData=[];

        if(!$superAdmin && $admin && isset($userData)){
            foreach($userData as $project_id){
                $datas = SADispute::with([
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
                    array_push($saDisputeInfoData, $datas);
                }
                
            }
            return response()->json([
                'data' => $saDisputeInfoData
             ],200);
        }
    
       else if($superAdmin){
        $data = SADispute::with([
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
            "data" => $data
        ], 200);
        }
    
    }


    
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'sa_id'=> 'required',
            'dispute_no' => 'required',
            'dispute_land_size' => 'required',
            'dispute_court_name' => 'required',
            'case_badi_name' => 'required',
            'case_bibadi_name' => 'required'
        ]);
        if ($validator->fails()){
            return response()->json([
                'status' => 'faield',
                'message' => $validator->messages()->all()
            ]);
        }
        try {
            $saDisputeInfo = SADispute::find($request->id);
            $saDisputeInfo->sa_id = $request->sa_id;
            $saDisputeInfo->dispute_date = $request->dispute_date;
            $saDisputeInfo->dispute_no = $request->dispute_no;
            $saDisputeInfo->dispute_land_size = $request->dispute_land_size;
            $saDisputeInfo->dispute_court_name = $request->dispute_court_name;
            $saDisputeInfo->dispute_judgement_date = $request->dispute_judgement_date;
            $saDisputeInfo->case_badi_name = $request->case_badi_name;
            $saDisputeInfo->case_bibadi_name = $request->case_bibadi_name;
            $saDisputeInfo->judgement_description = $request->judgement_description;

            if ($saDisputeInfo->save()) {
                return response([
                    'status' => 'success',
                    'message' => 'SA Mutation List successfully updated',
                    'CSMutation' => $saDisputeInfo
                ], 200);
            } else {
                return response([
                    'status' => 'failed',
                    'message' => 'SA Dispute update failed'
                ], 500);
            }
        }
        catch (\Exception $e) {
            return response([
                'status' => 'failed',
                'message' => 'SA Dispute update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
