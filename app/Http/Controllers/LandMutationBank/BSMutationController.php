<?php

namespace App\Http\Controllers\LandMutationBank;

use App\Http\Controllers\Controller;
use App\Http\Traits\UserDataTraits;
use App\Models\LandMutationBank\BSMutationInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BSMutationController extends Controller
{
    use UserDataTraits;


    public function store(Request $request){
        $validator = Validator::make($request->all(),[
            'bs_id'          =>  'required',
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
            $bsMutationInfo = new BSMutationInfo();
            $createdData = $bsMutationInfo->create([
                'bs_id'                     => $request->bs_id,
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
                'message'           =>  'BS Mutation information added succesfully',
                'created_data'      =>   $createdData
            ],200);
        }
        catch(\Exception $e)
       {
           DB::rollback();
           return response()->json([
               'status'        => 'failed',
               'error_message' => $e->getMessage(),
               'message'       => 'BS Mutation information couldn\'t be loaded',
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
    
            
            $bsMutationInfoData=[];
            if(!$superAdmin && $admin && isset($userData)){
                foreach($userData as $project_id){
                    $datas = BSMutationInfo::with([
                        'bsDagInfo'=>function($query){
                            $query->select('id','b_s_dag_infos.rs_id','bs_dag_no','bs_khatiyan_no');
                            $query->with([
                                'rsInfo'=>function($query){
                                    $query->select('id','r_s_dag_infos.sa_id');
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
                                                                        $query->select('id','mouza_information.project_id','mouza_name');
                                                                        $query->with([
                                                                            'projectInfo'=>function($query){
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
                            ]);
                        }  
                        ])->whereHas('bsDagInfo.rsInfo.saInfo.csInfo.mouzaInfo.projectInfo',function ($query) use ($project_id) 
                     {
                     $query->where('project_id', $project_id->project_id);
                     }
                     )->get(['*']);
                    
                    // array_push($rsMutationInfoData,$datas);
                    if ($datas->isNotEmpty()) {
                        array_push($bsMutationInfoData, $datas);
                    }
                    
                }
                return response()->json([
                    'data' =>$bsMutationInfoData
                 ],200);
            }
        
           else if($superAdmin){
    
            $data = BSMutationInfo::with([
                'bsDagInfo'=>function($query){
                    $query->select('id','b_s_dag_infos.rs_id','bs_dag_no','bs_khatiyan_no');
                    $query->with([
                        'rsInfo'=>function($query){
                            $query->select('id','r_s_dag_infos.sa_id');
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
                                                                $query->select('id','mouza_information.project_id','mouza_name');
                                                                $query->with([
                                                                    'projectInfo'=>function($query){
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
                    ]);
                }  
                ])->get();
        
               return response()->json([
                'data'      => $data,      
                ],200);
        
        }
            
           
        }

    public function update(Request $request){
        $validator = Validator::make($request->all(),[
            'bs_id'          =>  'required',
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
            $bsMutationInfo = BSMutationInfo::find($request->id);
            $bsMutationInfo->bs_id                      = $request->bs_id;
            $bsMutationInfo->mutation_date              = $request->mutation_date;
            $bsMutationInfo->mutation_no                = $request->mutation_no;
            $bsMutationInfo->land_size                  = $request->land_size;
            $bsMutationInfo->owner_name                 = $request->owner_name;
            $bsMutationInfo->ref_deed_no                = $request->ref_deed_no;
            $bsMutationInfo->ref_deed_no_date           = $request->ref_deed_no_date;
            $bsMutationInfo->misscase_no                = $request->misscase_no;
            $bsMutationInfo->misscase_date              = $request->misscase_date;
            $bsMutationInfo->misscase_judgement_date    = $request->misscase_judgement_date;
            $bsMutationInfo->description                = $request->description;
            $bsMutationInfo->save();

            return response()->json([
                'status'         =>'success',
                'updated_data'   => $bsMutationInfo
            ],200);
        }
        catch (\Exception $e) {
            return response([
                'status'    => 'failed',
                'message'   => 'BS Mutation information update failed',
                'error'     => $e->getMessage(),
            ], 500);
        }


    }
}
