<?php

namespace App\Http\Controllers\LandDisputeBank;

use App\Http\Controllers\Controller;
use App\Http\Traits\UserDataTraits;
use App\Models\LandDisputeBank\CSDispute;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CSDisputeController extends Controller
{
    use UserDataTraits;
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'cs_id'=> 'required',
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
        DB::beginTransaction();
        try {
            $csDisputeInfo = new CSDispute();

            $csDisputeInfo->cs_id = $request->cs_id;
            $csDisputeInfo->dispute_date = $request->dispute_date;
            $csDisputeInfo->dispute_no = $request->dispute_no;
            $csDisputeInfo->dispute_land_size = $request->dispute_land_size;
            $csDisputeInfo->dispute_court_name = $request->dispute_court_name;
            $csDisputeInfo->dispute_judgement_date = $request->dispute_judgement_date;
            $csDisputeInfo->case_badi_name = $request->case_badi_name;
            $csDisputeInfo->case_bibadi_name = $request->case_bibadi_name;
            $csDisputeInfo->judgement_description = $request->judgement_description;
//            return response()->json($csDisputeInfo);
            $csDisputeInfo->save();

            DB::commit();
            return response([
                'status' => 'success',
                'message' => 'CS Dispute Successfully Add',
                'landDespute' => $csDisputeInfo
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
    
        $csDisputeInfoData=[];

        if(!$superAdmin && $admin && isset($userData)){
            foreach($userData as $project_id){
                $datas = CSDispute::with([
                    'csInfo' => function ($query) {
                        $query->with([
                            'mouzaInfo' => function ($query) {
                                $query->with([
                                    'projectInfo']);
                            }]);
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
        $data = CSDispute::with([
            'csInfo' => function ($query) {
                $query->with([
                    'mouzaInfo' => function ($query) {
                        $query->with([
                            'projectInfo']);
                    }]);
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
            'cs_id'=> 'required',
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
            $csDisputeInfo = CSDispute::find($request->id);
            $csDisputeInfo->cs_id = $request->cs_id;
            $csDisputeInfo->dispute_date = $request->dispute_date;
            $csDisputeInfo->dispute_no = $request->dispute_no;
            $csDisputeInfo->dispute_land_size = $request->dispute_land_size;
            $csDisputeInfo->dispute_court_name = $request->dispute_court_name;
            $csDisputeInfo->dispute_judgement_date = $request->dispute_judgement_date;
            $csDisputeInfo->case_badi_name = $request->case_badi_name;
            $csDisputeInfo->case_bibadi_name = $request->case_bibadi_name;
            $csDisputeInfo->judgement_description = $request->judgement_description;

//           return response()->json($csDisputeInfo);
            if ($csDisputeInfo->save()) {
                return response([
                    'status' => 'success',
                    'message' => 'CS Mutation List successfully updated',
                    'CSMutation' => $csDisputeInfo,
                ], 200);
            } else {
                return response([
                    'status' => 'failed',
                    'message' => 'CS Dispute update failed',
                ], 500);
            }
        }

        catch (\Exception $e) {
            return response([
                'status' => 'failed',
                'message' => 'CS Dispute update failed',
                'error' => $e->getMessage(),
            ], 500);
        }

    }
}
