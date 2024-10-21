<?php

namespace App\Http\Controllers\LandSubDeedBank;

use App\Http\Controllers\Controller;
use App\Http\Traits\UserDataTraits;
use App\Models\LandSubDeedBank\BSSubDeed\BSSubDeedInfo;
use App\Models\LandSubDeedBank\BSSubDeed\BSSubDeedSellerBuyerList;
use App\Models\LandSubDeedBank\BSSubDeed\BSSubDeedSellerBuyerRelation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BSSubDeedController extends Controller
{
    use UserDataTraits;
    public function store(Request $request){
        $validator = Validator::make($request->all(),[
         'seller_buyer_info.*seller_name'               => 'required',
         'seller_buyer_info.*seller_father_name'        => 'required',
         'seller_buyer_info.*buyer_name'                => 'required',
         'seller_buyer_info.*buyer_father_name'         => 'required',
         'bs_id'                                        => 'required',
         'sub_deed_date'                                => 'required',
         'sub_deed_no'                                  => 'required',
         'sub_deed_land_size'                           => 'required',
         'sub_deed_registry_office'                     => 'required',
  
        ]);
 
        if($validator->fails()){
         return response()->json([
             "status"=>"failed",
             "message"=> $validator->messages()->all(),
         ]);
        }
        DB::beginTransaction();
        try{
            $bsSubDeedInfo =new BSSubDeedInfo();
            $bsSubDeedInfo->bs_id                         = $request->bs_id;
            $bsSubDeedInfo->sub_deed_date                 = $request->sub_deed_date;
            $bsSubDeedInfo->sub_deed_no                   = $request->sub_deed_no;
            $bsSubDeedInfo->sub_deed_land_size            = $request->sub_deed_land_size;
            $bsSubDeedInfo->sub_deed_registry_office      = $request->sub_deed_registry_office;
            $bsSubDeedInfo->description                   = $request->description;
            $bsSubDeedInfo->save();
            
            $sellerBuyerInfos= array();
            foreach($request->seller_buyer_info as $info){
             $infos=array(
                 'seller_name'           => $info['seller_name'],
                 'seller_father_name'    => $info['seller_father_name'],
                 'buyer_name'            => $info['buyer_name'],
                 'buyer_father_name'     => $info['buyer_father_name'],
             );
             $rsSellerBuyerInfo = BSSubDeedSellerBuyerList::create($infos);
             array_push($sellerBuyerInfos,$rsSellerBuyerInfo);
            }
 
            //set relation between bs sub deed info && b_s_sub_deed_seller_buyer_lists
            foreach ($sellerBuyerInfos as $info) {
             $sellerBuyerRelation = new BSSubDeedSellerBuyerRelation();
             $sellerBuyerRelation->bs_sub_deed_id = $bsSubDeedInfo->id;
             $sellerBuyerRelation->bs_seller_buyer_id = $info->id; 
             $sellerBuyerRelation->save();
           
         }
         
            DB::commit();
            return response()->json([
                'status'                         => 'success',
                'message'                        => 'BS SUB DEED information added succesfully',
                'bs_sub_deed_info_data '         => $bsSubDeedInfo,
                'bs_seller_buyer_info_data'      => $sellerBuyerInfos,
            ],200);
        }
        catch(\Exception $e)
        {
            DB::rollback();
            return response()->json([
                'status'                         => 'failed',
                'message'                        => 'BS SUB DEED information couldn\'t be loaded',
                'error_msg'                      =>  $e->getMessage()
            ],500);
        }
 
    }

   

    public function view()
    {
        $user = Auth::user();
        $data = $this->getUserData($user);
        
        $userData    = $data['userData'];
        $superAdmin  = $data['superAdmin'];
        $admin       = $data['admin'];
    
        $bsSubDeedInfoData=[];

        if(!$superAdmin && $admin && isset($userData)){
            foreach($userData as $project_id){
                $datas = BSSubDeedInfo::with(
                    [
                        'bsSellerBuyerList'=>function($query){
                            $query->select('*');
                        },
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
                )->get();
                
                if ($datas->isNotEmpty()) {
                    array_push($bsSubDeedInfoData, $datas);
                }
                
            }
            return response()->json([
                'data' =>    $bsSubDeedInfoData
             ],200);
        }
    
       else if($superAdmin){
        $data = BSSubDeedInfo::with(
            [
                'bsSellerBuyerList'=>function($query){
                    $query->select('*');
                },
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
            "data" => $data
        ], 200);
        }

      
    
    }
    
    public function update(Request $request){
        try{
        $validator = Validator::make($request->all(),[
            'seller_buyer_info.*seller_name'               => 'required',
            'seller_buyer_info.*seller_father_name'        => 'required',
            'seller_buyer_info.*buyer_name'                => 'required',
            'seller_buyer_info.*buyer_father_name'         => 'required',
            'bs_id'                                        => 'required',
            'sub_deed_date'                                => 'required',
            'sub_deed_no'                                  => 'required',
            'sub_deed_land_size'                           => 'required',
            'sub_deed_registry_office'                     => 'required',
        
           ]);
    
           if($validator->fails()){
            return response()->json([
                "status"=>"failed",
                "message"=> $validator->messages()->all(),
            ]);
           }
        $id                                 = $request->id;
        $data                               = BSSubDeedInfo::with(['bsSellerBuyerList'])->find($id);
        $sellerBuyerData                    = $data->bsSellerBuyerList;   

        $data->bs_id                        = $request->bs_id;
        $data->sub_deed_date                = $request->sub_deed_date;
        $data->sub_deed_no                  = $request->sub_deed_no;
        $data->sub_deed_land_size           = $request->sub_deed_land_size;
        $data->sub_deed_registry_office     = $request->sub_deed_registry_office;
        $data->description                  = $request->description;
        $data->save();

        $createNewSellerBuyer = [];
        $updateSellerBuyer = [];
        foreach($request->seller_buyer_info as $sellerBuyer){
            if(isset($sellerBuyer['id'])){
                $foundSellerBuyer =  $sellerBuyerData->find($sellerBuyer['id']);
                $foundSellerBuyer->update([
                    'seller_name'           => $sellerBuyer['seller_name'],
                    'seller_father_name'    => $sellerBuyer['seller_father_name'],
                    'buyer_name'            => $sellerBuyer['buyer_name'],
                    'buyer_father_name'     => $sellerBuyer['buyer_father_name']
                ]);
                $updateSellerBuyer[]=$foundSellerBuyer;
            }
            else{
                //create new seller_buyer
                $newSellerBuyer = [
                    'seller_name'           => $sellerBuyer['seller_name'],
                    'seller_father_name'    => $sellerBuyer['seller_father_name'],
                    'buyer_name'            => $sellerBuyer['buyer_name'],
                    'buyer_father_name'     => $sellerBuyer['buyer_father_name']
                ];
                
                $sellerBuyerData = BSSubDeedSellerBuyerList::create($newSellerBuyer);
                if($sellerBuyerData){
                    $createNewSellerBuyer[] = $sellerBuyerData;
                }
            //set relation between bs sub deed info && b_s_sub_deed_seller_buyer_lists
            $sellerBuyerRelation = new BSSubDeedSellerBuyerRelation();
            foreach ($createNewSellerBuyer as $info) {          
                $sellerBuyerRelation->bs_sub_deed_id = $id;
                $sellerBuyerRelation->bs_seller_buyer_id = $info->id; 
                $sellerBuyerRelation->save();
              
            }
            
                
            }
        }

        return response()->json([
            'status'                        => 'success',
            "all_data"                      => $data,
            "update_seller_buyer_data"      => empty($updateSellerBuyer)    ? "No New update seller buyer data"   : $updateSellerBuyer,  
            "create_new_seller_buyer_data"  => empty($createNewSellerBuyer) ? "No New Seller Buyer Create"        : $createNewSellerBuyer  
        ],200);
        }
        catch (\Exception $e) {
            return response([
                'status'                    => 'failed',
                'message'                   => 'BS SUB DEED information update failed',
                'error_message'             => $e->getMessage(),
            ], 500);
        }
        
    }
}

