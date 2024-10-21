<?php

namespace App\Http\Controllers\LandDisputeBank;

use App\Http\Controllers\Controller;
use App\Http\Traits\UserDataTraits;
use App\Models\LandDisputeBank\RSDispute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RSDisputeController extends Controller
{
    use UserDataTraits;
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'rs_id'=> 'required',
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
            $rsDisputeInfo = new RSDispute();

            $rsDisputeInfo->rs_id = $request->rs_id;
            $rsDisputeInfo->dispute_date = $request->dispute_date;
            $rsDisputeInfo->dispute_no = $request->dispute_no;
            $rsDisputeInfo->dispute_land_size = $request->dispute_land_size;
            $rsDisputeInfo->dispute_court_name = $request->dispute_court_name;
            $rsDisputeInfo->dispute_judgement_date = $request->dispute_judgement_date;
            $rsDisputeInfo->case_badi_name = $request->case_badi_name;
            $rsDisputeInfo->case_bibadi_name = $request->case_bibadi_name;
            $rsDisputeInfo->judgement_description = $request->judgement_description;
//            return response()->json($rsDisputeInfo);
            $rsDisputeInfo->save();

            DB::commit();
            return response([
                'status' => 'success',
                'message' => 'CS Dispute Successfully Add',
                'landDespute' => $rsDisputeInfo
            ],200);
        }
        catch (\Exception $e) {
            return response([
                'status' => 'failed',
                'message' => 'RS Dispute update failed',
                'error' => $e->getMessage()
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
    
        $saDisputeInfoData=[];

        if(!$superAdmin && $admin && isset($userData)){
            foreach($userData as $project_id){
                $datas = RSDispute::with([
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
                    array_push($saDisputeInfoData, $datas);
                }
                
            }
            return response()->json([
                'data' => $saDisputeInfoData
             ],200);
        }
    
       else if($superAdmin){
        $data = RSDispute::with([
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
            "data" => $data
        ], 200);
        }

    
    }



    public function update(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'rs_id'=> 'required',
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
            $rsDisputeInfo = RSDispute::find($request->id);
            $rsDisputeInfo->rs_id = $request->rs_id;
            $rsDisputeInfo->dispute_date = $request->dispute_date;
            $rsDisputeInfo->dispute_no = $request->dispute_no;
            $rsDisputeInfo->dispute_land_size = $request->dispute_land_size;
            $rsDisputeInfo->dispute_court_name = $request->dispute_court_name;
            $rsDisputeInfo->dispute_judgement_date = $request->dispute_judgement_date;
            $rsDisputeInfo->case_badi_name = $request->case_badi_name;
            $rsDisputeInfo->case_bibadi_name = $request->case_bibadi_name;
            $rsDisputeInfo->judgement_description = $request->judgement_description;

            if ($rsDisputeInfo->save()) {
                return response([
                    'status' => 'success',
                    'message' => 'RS Mutation List successfully updated',
                    'CSMutation' => $rsDisputeInfo
                ], 200);
            } else {
                return response([
                    'status' => 'failed',
                    'message' => 'RS Dispute update failed'
                ], 500);
            }
        }
        catch (\Exception $e) {
            return response([
                'status' => 'failed',
                'message' => 'RS Dispute update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
