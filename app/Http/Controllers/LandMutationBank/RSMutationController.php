<?php

namespace App\Http\Controllers\LandMutationBank;

use App\Http\Controllers\Controller;
use App\Http\Traits\UserDataTraits;
use App\Models\LandMutationBank\RSMutationInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RSMutationController extends Controller
{
    use UserDataTraits;
    public function store(Request $request){
        $validator = Validator::make($request->all(),[
            'rs_id'          =>  'required',
            'mutation_date'  =>  'required',
            'mutation_no'    =>  'required',
            'owner_name'     =>  'required',
           
        ]);
        if ($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   =>  $validator->messages()->all()
            ]);
        }

        DB::beginTransaction();
        try{
            $rsMutationInfo = new RSMutationInfo();
            $createdData = $rsMutationInfo->create([
                'rs_id'                     => $request->rs_id,
                'mutation_date'             => $request->mutation_date,
                'mutation_no'               => $request->mutation_no,
                'land_size'                 => $request->land_size,
                'owner_name'                => $request->owner_name,
                'ref_deed_no'               => $request->ref_deed_no,
                'ref_deed_no_date'          => $request->ref_deed_no_date,
                'misscase_no'               => $request->misscase_no,
                'misscase_date'             => $request->misscase_date,
                'misscase_judgement_date'   => $request->misscase_judgement_date,
                'description'               => $request->description,
            ]);
            DB::commit();
            return response()->json([
                'status'            =>  'success',
                'message'           =>  'RS Mutation information added succesfully',
                'created_data'      =>   $createdData
            ],200);
        }
        catch(\Exception $e)
       {
           DB::rollback();
           return response()->json([
               'status'        => 'failed',
               'error_message' => $e->getMessage(),
               'message'       => 'RS Mutation information couldn\'t be loaded',
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

        
        $rsMutationInfoData=[];
        if(!$superAdmin && $admin && isset($userData)){
            foreach($userData as $project_id){
                $datas =RSMutationInfo::with([
                    'rsDagInfo'=>function($query){
                        $query->select('id','r_s_dag_infos.sa_id','rs_dag_no','rs_khatiyan_no');
                        $query->with(
                            [
                                'saInfo'=>function($query){
                                    $query->select('id','s_a_dag_infos.cs_id');
                                    $query->with(
                                        [
                                            'csInfo'=>function($query){
                                                $query->select('id','c_s_dag_infos.mouza_id');
                                                $query->with(
                                                    [
                                                        'mouzaInfo'=>function($query){
                                                            $query->select('mouza_information.id','mouza_information.project_id', 'mouza_name');
                                                            $query->with([
                                                                'projectInfo' => function($query){
                                                                    $query->select('id', 'project_name');
                                                                }
                                                            ]);
                                                        }
                                                    ]
                                                );
                                            }
                                        ]
                                        );
                                }
                            ]
                        );
                      
                    }        
                    ])->whereHas('rsDagInfo.saInfo.csInfo.mouzaInfo.projectInfo',function ($query) use ($project_id) 
                 {
                 $query->where('project_id', $project_id->project_id);
                 }
                 )->get();
                
                // array_push($rsMutationInfoData,$datas);
                if ($datas->isNotEmpty()) {
                    array_push($rsMutationInfoData, $datas);
                }
                
            }
            return response()->json([
                'data' =>$rsMutationInfoData
             ],200);
        }
    
       else if($superAdmin){

        $data = RSMutationInfo::with([
            'rsDagInfo'=>function($query){
                $query->select('id','r_s_dag_infos.sa_id','rs_dag_no','rs_khatiyan_no');
                $query->with(
                    [
                        'saInfo'=>function($query){
                            $query->select('id','s_a_dag_infos.cs_id');
                            $query->with(
                                [
                                    'csInfo'=>function($query){
                                        $query->select('id','c_s_dag_infos.mouza_id');
                                        $query->with(
                                            [
                                                'mouzaInfo'=>function($query){
                                                    $query->select('mouza_information.id','mouza_information.project_id', 'mouza_name');
                                                    $query->with([
                                                        'projectInfo' => function($query){
                                                            $query->select('id', 'project_name');
                                                        }
                                                    ]);
                                                }
                                            ]
                                        );
                                    }
                                ]
                                );
                        }
                    ]
                );
              
            }        
            ])->get();
    
           return response()->json([
            'data'      => $data,      
            ],200);
    
    }
        
       
    }

    public function update(Request $request){
        $validator = Validator::make($request->all(),[
            'rs_id'          =>  'required',
            'mutation_date'  =>  'required',
            'mutation_no'    =>  'required',
            'owner_name'     =>  'required',
           
        ]);
        if ($validator->fails()){
            return response()->json([
            'status'    => 'failed',
            'message'   =>  $validator->messages()->all()
            ]);
        }
     
        try{
            $rsMutationInfo = RSMutationInfo::find($request->id);
            $rsMutationInfo->rs_id                      = $request->rs_id;
            $rsMutationInfo->mutation_date              = $request->mutation_date;
            $rsMutationInfo->mutation_no                = $request->mutation_no;
            $rsMutationInfo->land_size                  = $request->land_size;
            $rsMutationInfo->owner_name                 = $request->owner_name;
            $rsMutationInfo->ref_deed_no                = $request->ref_deed_no;
            $rsMutationInfo->ref_deed_no_date           = $request->ref_deed_no_date;
            $rsMutationInfo->misscase_no                = $request->misscase_no;
            $rsMutationInfo->misscase_date              = $request->misscase_date;
            $rsMutationInfo->misscase_judgement_date    = $request->misscase_judgement_date;
            $rsMutationInfo->description                = $request->description;
            $rsMutationInfo->save();

            return response()->json([
                'status'         =>'success',
                'updated_data'   => $rsMutationInfo
            ],200);
        }
        catch (\Exception $e) {
            return response([
                'status'    => 'failed',
                'message'   => 'RS Mutation information update failed',
                'error'     => $e->getMessage(),
            ], 500);
        }
    }
} 

