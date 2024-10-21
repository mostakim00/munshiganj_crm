<?php

namespace App\Http\Controllers\LandSubDeedBank;

use App\Http\Controllers\Controller;
use App\Http\Traits\UserDataTraits;
use App\Models\LandSubDeedBank\CSSubDeed\CSSubDeedInfo;
use App\Models\LandSubDeedBank\CSSubDeed\CSSubDeedSellerBuyerList;
use App\Models\LandSubDeedBank\CSSubDeed\CSSubDeedSellerBuyerRelation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CSSubDeedController extends Controller
{
    use UserDataTraits;
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'seller_buyer_info.*seller_name' => 'required',
            'seller_buyer_info.*seller_father_name' => 'required',
            'seller_buyer_info.*buyer_name' => 'required',
            'seller_buyer_info.*buyer_father_name' => 'required',
            'cs_id' => 'required',
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
        try{
            $csSubDeedInfo = new CSSubDeedInfo();
            $csSubDeedInfo->cs_id = $request->cs_id;
            $csSubDeedInfo->sub_deed_date = $request->sub_deed_date;
            $csSubDeedInfo->sub_deed_no = $request->sub_deed_no;
            $csSubDeedInfo->sub_deed_land_size = $request->sub_deed_land_size;
            $csSubDeedInfo->sub_deed_registry_office = $request->sub_deed_registry_office;
            $csSubDeedInfo->description = $request->description;
//            return response()->json($csSubDeedInfo);
            $csSubDeedInfo->save();

            $salerBuyerInfo =[];
//
            foreach ($request->seller_buyer_info as $personData){
                $person = array(
                    'seller_name' => $personData['seller_name'],
                    'seller_father_name' => $personData['seller_father_name'],
                    'buyer_name' => $personData['buyer_name'],
                    'buyer_father_name' => $personData['buyer_father_name']
                );
                $personInfo = CSSubDeedSellerBuyerList::create($person);
                array_push($salerBuyerInfo,$personInfo);
            }

            // set relation between cs sub-deed information and seller buyer relationship table
            foreach ($salerBuyerInfo as $person){
                $CSSubDeedSellerBuyerRelation = new CSSubDeedSellerBuyerRelation();
                $CSSubDeedSellerBuyerRelation->cs_seller_buyer_id = $person->id;
                $CSSubDeedSellerBuyerRelation->cs_sub_deed_id =  $csSubDeedInfo->id;
                $CSSubDeedSellerBuyerRelation->save();
            }
            DB::commit();
            return response()->json([
                'status'=>'success',
                'message' => 'CS Sub-Deed INFO information added succesfully',
                'cs_info_data'=>$csSubDeedInfo,
                'recorded_person_info_data'=>$salerBuyerInfo,
            ],200);
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


    public function view()
    {
        $user = Auth::user();
        $data = $this->getUserData($user);
        
        $userData    = $data['userData'];
        $superAdmin  = $data['superAdmin'];
        $admin       = $data['admin'];
    
        $csSubDeedInfoData=[];

        if(!$superAdmin && $admin && isset($userData)){
            foreach($userData as $project_id){
                $datas = CSSubDeedInfo::with([
                    'csSalerBuyer',
                    'csInfo' => function ($query) {
                        $query->with([
                            'mouzaInfo' => function ($query) {
                                $query->with(['projectInfo']);
                            }
                        ]);
                    }
                ])->whereHas('csInfo.mouzaInfo.projectInfo',function ($query) use ($project_id) 
                {
                $query->where('project_id', $project_id->project_id);
                }
                )->get();
                
                if ($datas->isNotEmpty()) {
                    array_push($csSubDeedInfoData, $datas);
                }
                
            }
            return response()->json([
                'data' => $csSubDeedInfoData
             ],200);
        }
    
       else if($superAdmin){
        $data = CSSubDeedInfo::with([
            'csSalerBuyer',
            'csInfo' => function ($query) {
                $query->with([
                    'mouzaInfo' => function ($query) {
                        $query->with(['projectInfo']);
                    }
                ]);
            }
        ])->get();

        return response()->json([
            "data" => $data
        ], 200);
        }

        return response()->json([
            "data" => $data
        ], 200);

    
    }


    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'seller_buyer_info.*seller_name' => 'required',
            'seller_buyer_info.*seller_father_name' => 'required',
            'seller_buyer_info.*buyer_name' => 'required',
            'seller_buyer_info.*buyer_father_name' => 'required',
            'cs_id' => 'required',
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
            $data = CSSubDeedInfo::with(['csSalerBuyer'])->find($request->id);

            if (!$data) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'CS Sub-Deed Info not found'
                ], 404);
            }

            $data->cs_id = $request->cs_id;
            $data->sub_deed_date = $request->sub_deed_date;
            $data->sub_deed_no = $request->sub_deed_no;
            $data->sub_deed_land_size = $request->sub_deed_land_size;
            $data->sub_deed_registry_office = $request->sub_deed_registry_office;
            $data->description = $request->description;
            $data->save();

            $createCSSellerBuyer = [];
            $updateCSSellerBuyer = [];

            foreach ($request->seller_buyer_info as $sellerBuyer) {
                if (isset($sellerBuyer['id'])) {
                    $foundSellerBuyer = CSSubDeedSellerBuyerList::find($sellerBuyer['id']);
                    if ($foundSellerBuyer) {
                        $foundSellerBuyer->update([
                            'seller_name' => $sellerBuyer['seller_name'],
                            'seller_father_name' => $sellerBuyer['seller_father_name'],
                            'buyer_name' => $sellerBuyer['buyer_name'],
                            'buyer_father_name' => $sellerBuyer['buyer_father_name']
                        ]);
                        $updateCSSellerBuyer[] = $foundSellerBuyer;
                    }
                } else {
                    $newSellerBuyer = [
                        'seller_name' => $sellerBuyer['seller_name'],
                        'seller_father_name' => $sellerBuyer['seller_father_name'],
                        'buyer_name' => $sellerBuyer['buyer_name'],
                        'buyer_father_name' => $sellerBuyer['buyer_father_name']
                    ];
                    $csSalerBuyerData = CSSubDeedSellerBuyerList::create($newSellerBuyer);
                    if ($csSalerBuyerData) {
                        $createCSSellerBuyer[] = $csSalerBuyerData;
                    }

                    //set relation between bs sub deed info && b_s_sub_deed_seller_buyer_lists
                    $sellerBuyerRelation = new CSSubDeedSellerBuyerRelation();
                    foreach ($createCSSellerBuyer as $info) {
                        $sellerBuyerRelation->cs_sub_deed_id = $id;
                        $sellerBuyerRelation->cs_seller_buyer_id = $info->id;
                        $sellerBuyerRelation->save();

                    }
                }
            }

            return response()->json([
                "status" => "success",
                "message" => "CS Sub-Deed Info updated successfully",
                "updated_data" => $data,
                "updated_seller_buyer_data" => $updateCSSellerBuyer,
                "created_seller_buyer_data" => $createCSSellerBuyer
            ], 200);

        } catch (\Exception $e) {
            return response([
                'status' => 'error',
                'message' => 'CS Sub-Deed Info update failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
