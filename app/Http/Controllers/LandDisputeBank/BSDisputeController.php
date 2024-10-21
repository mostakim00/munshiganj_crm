<?php

namespace App\Http\Controllers\LandDisputeBank;

use App\Http\Controllers\Controller;
use App\Http\Traits\UserDataTraits;
use App\Models\LandDisputeBank\BSDispute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BSDisputeController extends Controller
{
    use UserDataTraits;
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'bs_id'=> 'required',
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
            $bsDisputeInfo = new BSDispute();
            $bsDisputeInfo->bs_id = $request->bs_id;
            $bsDisputeInfo->dispute_date = $request->dispute_date;
            $bsDisputeInfo->dispute_no = $request->dispute_no;
            $bsDisputeInfo->dispute_land_size = $request->dispute_land_size;
            $bsDisputeInfo->dispute_court_name = $request->dispute_court_name;
            $bsDisputeInfo->dispute_judgement_date = $request->dispute_judgement_date;
            $bsDisputeInfo->case_badi_name = $request->case_badi_name;
            $bsDisputeInfo->case_bibadi_name = $request->case_bibadi_name;
            $bsDisputeInfo->judgement_description = $request->judgement_description;
//            return response()->json($bsDisputeInfo);
            $bsDisputeInfo->save();
            DB::commit();
            return response([
                'status' => 'success',
                'message' => 'BS Dispute Successfully Add',
                'landDispute' => $bsDisputeInfo
            ],200);

        }
        catch (\Exception $e) {
            return response([
                'status' => 'failed',
                'message' => 'BS Dispute Added failed',
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
                $datas = BSDispute::with([
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
                ])->whereHas('bsDagInfo.rsInfo.saInfo.csInfo.mouzaInfo.projectInfo',function ($query) use ($project_id) 
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
        $data = BSDispute::with([
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
            "data" => $data
        ], 200);
        }

    
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'bs_id'=> 'required',
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
            $bsDisputeInfo = BSDispute::find($request->id);
            $bsDisputeInfo->bs_id = $request->bs_id;
            $bsDisputeInfo->dispute_date = $request->dispute_date;
            $bsDisputeInfo->dispute_no = $request->dispute_no;
            $bsDisputeInfo->dispute_land_size = $request->dispute_land_size;
            $bsDisputeInfo->dispute_court_name = $request->dispute_court_name;
            $bsDisputeInfo->dispute_judgement_date = $request->dispute_judgement_date;
            $bsDisputeInfo->case_badi_name = $request->case_badi_name;
            $bsDisputeInfo->case_bibadi_name = $request->case_bibadi_name;
            $bsDisputeInfo->judgement_description = $request->judgement_description;

            if ($bsDisputeInfo->save()) {
                return response([
                    'status' => 'success',
                    'message' => 'BS Mutation List successfully updated',
                    'CSMutation' => $bsDisputeInfo
                ], 200);
            } else {
                return response([
                    'status' => 'failed',
                    'message' => 'BS Dispute update failed'
                ], 500);
            }
        }
        catch (\Exception $e) {
            return response([
                'status' => 'failed',
                'message' => 'BS Dispute update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
