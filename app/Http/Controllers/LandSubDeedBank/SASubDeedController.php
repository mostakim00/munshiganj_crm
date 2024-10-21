<?php

namespace App\Http\Controllers\LandSubDeedBank;

use App\Http\Controllers\Controller;
use App\Http\Traits\UserDataTraits;
use App\Models\LandSubDeedBank\SASubDeed\SASubDeedInfo;
use App\Models\LandSubDeedBank\SASubDeed\SASubDeedSellerBuyerList;
use App\Models\LandSubDeedBank\SASubDeed\SASubDeedSellerBuyerRelation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use MongoDB\Driver\Query;

class SASubDeedController extends Controller
{
    use UserDataTraits;
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'seller_buyer_info.*seller_name' => 'required',
            'seller_buyer_info.*seller_father_name' => 'required',
            'seller_buyer_info.*buyer_name' => 'required',
            'seller_buyer_info.*buyer_father_name' => 'required',
            'sa_id' => 'required',
            'sub_deed_date' => 'required',
            'sub_deed_no' => 'required',
            'sub_deed_land_size' => 'required',
            'sub_deed_registry_office' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'failed',
                'message' => $validator->messages()->all()
            ]);
        }
        DB::beginTransaction();
        try {
            $saSubDeedInfo = new SASubDeedInfo();
            $saSubDeedInfo->sa_id = $request->sa_id;
            $saSubDeedInfo->sub_deed_date = $request->sub_deed_date;
            $saSubDeedInfo->sub_deed_no = $request->sub_deed_no;
            $saSubDeedInfo->sub_deed_land_size = $request->sub_deed_land_size;
            $saSubDeedInfo->sub_deed_registry_office = $request->sub_deed_registry_office;
            $saSubDeedInfo->description = $request->description;
//            return response()->json($saSubDeedInfo);
            $saSubDeedInfo->save();

            $salerBuyerInfo =[];

            foreach ($request->seller_buyer_info as $personData){
                $person = array(
                    'seller_name' => $personData['seller_name'],
                    'seller_father_name' => $personData['seller_father_name'],
                    'buyer_name' => $personData['buyer_name'],
                    'buyer_father_name' => $personData['buyer_father_name']
                );
                $personData = SASubDeedSellerBuyerList::create($person);
                array_push($salerBuyerInfo,$personData);
            }
            // set relation between cs sub-deed information and seller buyer relationship table
            foreach ($salerBuyerInfo as $person){
                $saSubDeedSellerBuyerRelation = new SASubDeedSellerBuyerRelation();
                $saSubDeedSellerBuyerRelation->sa_sub_deed_id= $saSubDeedInfo->id;
                $saSubDeedSellerBuyerRelation->sa_seller_buyer_id = $person->id;
                $saSubDeedSellerBuyerRelation->save();
            }
            DB::commit();
            return response()->json([
               'status' => 'Success',
               'message' => 'SA Sub-Deed INFO information added succesfully',
                'sa_info_data' => $saSubDeedInfo,
                'recorded_person_info_data' => $salerBuyerInfo
            ]);
        }
        catch(\Exception $e)
        {
            DB::rollback();
            return response()->json([
                'status' => 'failed',
                'message' => 'CS Sub-Deed information couldn\'t be loaded',
                'msg'=>$e->getMessage()
            ],500);
        }
    }

    // public function view()
    // {
    //     $data = SASubDeedInfo::with([
    //         'saSallerBuyer',
    //         'saInfo' => function ($query) {
    //             $query->with([
    //                 'csInfo' => function ($query) {
    //                     $query->with([
    //                         'mouzaInfo' => function ($query) {
    //                             $query->with([
    //                                 'projectInfo'
    //                             ]);
    //                         }
    //                     ]);
    //                 }
    //             ]);
    //         }
    //     ])->get();
    //     return response()->json([
    //         'message' => 'success',
    //         "data" => $data
    //     ], 200);
    // }

    
    public function view()
    {
        $user = Auth::user();
        $data = $this->getUserData($user);
        
        $userData    = $data['userData'];
        $superAdmin  = $data['superAdmin'];
        $admin       = $data['admin'];
    
        $saSubDeedInfoData=[];

        if(!$superAdmin && $admin && isset($userData)){
            foreach($userData as $project_id){
                $datas = SASubDeedInfo::with([
                    'saSallerBuyer',
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
                $query->where('project_id', $project_id->project_id);
                }
                )->get();
                
                if ($datas->isNotEmpty()) {
                    array_push(   $saSubDeedInfoData, $datas);
                }
                
            }
            return response()->json([
                'data' =>    $saSubDeedInfoData
             ],200);
        }
    
       else if($superAdmin){
        $data = $data = SASubDeedInfo::with([
            'saSallerBuyer',
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

    public function update(Request $request){
        $validator = Validator::make($request->all(), [
            'seller_buyer_info.*seller_name' => 'required',
            'seller_buyer_info.*seller_father_name' => 'required',
            'seller_buyer_info.*buyer_name' => 'required',
            'seller_buyer_info.*buyer_father_name' => 'required',
            'sa_id' => 'required',
            'sub_deed_date' => 'required',
            'sub_deed_no' => 'required',
            'sub_deed_land_size' => 'required',
            'sub_deed_registry_office' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'failed',
                'message' => $validator->messages()->all()
            ]);
        }
        try {
            $id = $request->id;
            $data = SASubDeedInfo::with(['saSallerBuyer'])->findOrFail($request->id);
            if (!$data){
                return response()->json([
                'status' => 'error',
                'message' => 'SA Sub-Deed Info not found'
                ],404);
            }
            $data->sa_id = $request->sa_id;
            $data->sub_deed_date = $request->sub_deed_date;
            $data->sub_deed_no = $request->sub_deed_no;
            $data->sub_deed_land_size = $request->sub_deed_land_size;
            $data->sub_deed_registry_office = $request->sub_deed_registry_office;
            $data->description = $request->description;
            $data->save();

            $createSASallerBuyer = [];
            $updateSASallerBuyer = [];

            foreach ($request->seller_buyer_info as $sellerBuyer){
                if (isset($sellerBuyer['id'])){
                    $foundSallerBuyer = SASubDeedSellerBuyerList::find($sellerBuyer['id']);
                    if($foundSallerBuyer){
                        $foundSallerBuyer->update([
                            'seller_name' => $sellerBuyer['seller_name'],
                            'seller_father_name' => $sellerBuyer['seller_father_name'],
                            'buyer_name' => $sellerBuyer['buyer_name'],
                            'buyer_father_name' => $sellerBuyer['buyer_father_name']
                         ]);
                         $updateSASallerBuyer[] = $foundSallerBuyer;
                     }
                 }else{
                     $newSallerBuyer = [
                         'seller_name' => $sellerBuyer['seller_name'],
                         'seller_father_name' => $sellerBuyer['seller_father_name'],
                         'buyer_name' => $sellerBuyer['buyer_name'],
                         'buyer_father_name' => $sellerBuyer['buyer_father_name']
                     ];
                     $saSallerBuyerData = SASubDeedSellerBuyerList::create($newSallerBuyer);
                     if ($saSallerBuyerData){
                         $createSASallerBuyer[] = $saSallerBuyerData;
                     }
                     //set relation between bs sub deed info && b_s_sub_deed_seller_buyer_lists
                     $sellerBuyerRelation = new SASubDeedSellerBuyerRelation();
                     foreach ($createSASallerBuyer as $info) {
                         $sellerBuyerRelation->sa_sub_deed_id = $id;
                         $sellerBuyerRelation->sa_seller_buyer_id = $info->id;
                         $sellerBuyerRelation->save();
                     }
                 }
            }
            return response()->json([
                "status" => "success",
                "message" => "SA Sub-Deed Info updated successfully",
                "updated_data" => $data,
                "updated_seller_buyer_data" => $updateSASallerBuyer,
                "created_seller_buyer_data" => $createSASallerBuyer
            ], 200);


        }catch (\Exception $e) {
            return response([
                'status' => 'error',
                'message' => 'SA Sub-Deed Info update failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
