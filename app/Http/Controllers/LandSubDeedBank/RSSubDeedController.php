<?php

namespace App\Http\Controllers\LandSubDeedBank;

use App\Http\Controllers\Controller;
use App\Http\Traits\UserDataTraits;
use App\Models\LandSubDeedBank\RSSubDeed\RSSubDeedInfo;
use App\Models\LandSubDeedBank\RSSubDeed\RSSubDeedSellerBuyerList;
use App\Models\LandSubDeedBank\RSSubDeed\RSSubDeedSellerBuyerRelation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RSSubDeedController extends Controller
{
    use UserDataTraits;
    public function store(Request $request){
       $validator = Validator::make($request->all(),[
        'seller_buyer_info.*seller_name'               => 'required',
        'seller_buyer_info.*seller_father_name'        => 'required',
        'seller_buyer_info.*buyer_name'                => 'required',
        'seller_buyer_info.*buyer_father_name'         => 'required',
        'rs_id'                                        => 'required',
        'sub_deed_date'                                => 'required',
        'sub_deed_no'                                  => 'required',
        'sub_deed_land_size'                           => 'required',
        'sub_deed_registry_office'                     => 'required',
 
       ]);

       if($validator->fails()){
        return response()->json([
            "status"  => "failed",
            "message" => $validator->messages()->all(),
        ]);
       }
       DB::beginTransaction();
       try{
           $rsSubDeedInfo =new RSSubDeedInfo();
           $rsSubDeedInfo->rs_id                         = $request->rs_id;
           $rsSubDeedInfo->sub_deed_date                 = $request->sub_deed_date;
           $rsSubDeedInfo->sub_deed_no                   = $request->sub_deed_no;
           $rsSubDeedInfo->sub_deed_land_size            = $request->sub_deed_land_size;
           $rsSubDeedInfo->sub_deed_registry_office      = $request->sub_deed_registry_office;
           $rsSubDeedInfo->description                   = $request->description;
           $rsSubDeedInfo->save();
           
           $sellerBuyerInfos= array();
           foreach($request->seller_buyer_info as $info){
            $infos=array(
                'seller_name'           => $info['seller_name'],
                'seller_father_name'    => $info['seller_father_name'],
                'buyer_name'            => $info['buyer_name'],
                'buyer_father_name'     => $info['buyer_father_name'],
            );
            $rsSellerBuyerInfo = RSSubDeedSellerBuyerList::create($infos);
            array_push($sellerBuyerInfos,$rsSellerBuyerInfo);
           }

           //set relation between rs sub deed info && r_s_sub_deed_seller_buyer_lists
           foreach ($sellerBuyerInfos as $info) {
            $sellerBuyerRelation = new RSSubDeedSellerBuyerRelation();
            $sellerBuyerRelation->rs_sub_deed_id = $rsSubDeedInfo->id;
            $sellerBuyerRelation->rs_seller_buyer_id = $info->id; 
            $sellerBuyerRelation->save();
          
        }
        
           DB::commit();
           return response()->json([
               'status'                         => 'success',
               'message'                        => 'RS SUB DEED information added succesfully',
               'rs_sub_deed_info_data '         => $rsSubDeedInfo,
               'rs_seller_buyer_info_data'      => $sellerBuyerInfos,
           ],200);
       }
       catch(\Exception $e)
       {
           DB::rollback();
           return response()->json([
               'status'                         => 'failed',
               'message'                        => 'RS SUB DEED information couldn\'t be loaded',
               'msg'                            =>$e->getMessage()
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
    
        $rsSubDeedInfoData=[];

        if(!$superAdmin && $admin && isset($userData)){
            foreach($userData as $project_id){
                $datas = RSSubDeedInfo::with(
                    [
                        'rsSellerBuyerList'=>function($query){
                            $query->select('*');
                        },
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
                                                                $query->select('id','mouza_information.project_id','mouza_name');
                                                                $query->with(
                                                                    [
                                                                        'projectInfo'=>function($query){
                                                                            $query->select('id','project_name');
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
                                ]
                            );
                        }
                    ])->whereHas('rsDagInfo.saInfo.csInfo.mouzaInfo.projectInfo',function ($query) use ($project_id) 
                {
                $query->where('project_id', $project_id->project_id);
                }
                )->get();
                
                if ($datas->isNotEmpty()) {
                    array_push($rsSubDeedInfoData, $datas);
                }
                
            }
            return response()->json([
                'data' =>    $rsSubDeedInfoData
             ],200);
        }
    
       else if($superAdmin){
        $data = RSSubDeedInfo::with(
            [
                'rsSellerBuyerList'=>function($query){
                    $query->select('*');
                },
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
                                                        $query->select('id','mouza_information.project_id','mouza_name');
                                                        $query->with(
                                                            [
                                                                'projectInfo'=>function($query){
                                                                    $query->select('id','project_name');
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
                        ]
                    );
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
            'rs_id'                                        => 'required',
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
        $data                               = RSSubDeedInfo::with(['rsSellerBuyerList'])->find($id);
        $sellerBuyerData                    = $data->rsSellerBuyerList;   
        
        $data->rs_id                        = $request->rs_id;
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
                
                $sellerBuyerData = RSSubDeedSellerBuyerList::create($newSellerBuyer);
                if($sellerBuyerData){
                    $createNewSellerBuyer[] =  $sellerBuyerData;
                }
            //set relation between bs sub deed info && b_s_sub_deed_seller_buyer_lists
            $sellerBuyerRelation = new RSSubDeedSellerBuyerRelation();
            foreach ($createNewSellerBuyer as $info) {              
                $sellerBuyerRelation->rs_sub_deed_id = $id;
                $sellerBuyerRelation->rs_seller_buyer_id = $info->id; 
                $sellerBuyerRelation->save();
              
            }
           
            }
        }

        return response()->json([
            'status'                        => 'success',
            "all_data"                      => $data,
            "update_seller_buyer_data"      => empty($updateSellerBuyer)    ? "No  New update seller buyer data"   : $updateSellerBuyer, 
            "create_new_seller_buyer_data"  => empty($createNewSellerBuyer) ? "No New Seller Buyer Create"         : $createNewSellerBuyer   
        ],200);
        }
        catch (\Exception $e) {
            return response([
                'status'                    => 'failed',
                'message'                   => 'RS SUB DEED information update failed',
                'error_message'             =>  $e->getMessage(),
            ], 500);
        }
        
    }

    }
