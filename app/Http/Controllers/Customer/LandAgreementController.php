<?php

namespace App\Http\Controllers\customer;

use App\Http\Controllers\Controller;
use App\Http\Traits\UserDataTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Customer\CustomerLandAgreement;
use App\Models\Customer\CustomerLandAgreementDocuments;
use App\Models\Customer\CustomerLandInformation;
use Illuminate\Support\Facades\Auth;

class LandAgreementController extends Controller
{   
    use UserDataTraits;
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
                'agreement_date'=> 'required',
                'land_size_katha'=> 'required',
                'land_size_ojutangsho'=> 'required',
                'deed_no'=> 'required',
                'registry_complete_date'=> 'required',
                'registry_sub_deed_no'=> 'required',
                'dolil_variation_id'=> 'required',
                'namejari_no'=> 'required',
                'namejari_date'=> 'required',

        ]);
        if ($validator->fails()){
                return response()->json([
                'status' => 'failed',
                'message' =>  $validator->messages()->all(),
        ]);
        }

        DB::beginTransaction();
       try{
            $data = new CustomerLandAgreement();

            $data->customer_land_information_id = $request->customer_land_information_id;
            $data->land_seller_agreement_id = $request->land_seller_agreement_id;
            $data->agreement_date = $request->agreement_date;
            $data->land_size_katha = $request->land_size_katha;
            $data->land_size_ojutangsho = $request->land_size_ojutangsho;
            $data->deed_no = $request->deed_no;
            $data->registry_complete_date = $request->registry_complete_date;
            $data->registry_sub_deed_no = $request->registry_sub_deed_no;
            $data->registry_office = $request->registry_office ?? null;
            $data->dolil_variation_id = $request->dolil_variation_id;
            $data->namejari_no = $request->namejari_no;
            $data->namejari_date = $request->namejari_date;
            $data->description = $request->description ?? null;

            $data->save();

            //Add supported Document
            $files = $request->file('documents');
             if ($files && $files[0] !== null) {
             foreach ($files as $file)
                 {
                     $file_name = date('Ymd-his') . '.' . $file->getClientOriginalExtension();
                     $destinationPath = public_path('customer/landAgreementDocuments/');
                     $file->move($destinationPath, $file_name);
                     $file_path = 'customer/landAgreementDocuments/' . $file_name;
                     $file_name = $file->getClientOriginalName();
                     $landAgreement = new CustomerLandAgreementDocuments();
                     $landAgreement->customer_land_agreements_id = $data->id;
                     $landAgreement->file_path = $file_path;
                     $landAgreement->file_name = $file_name;
                     $landAgreement->save();
                 }
             }

                DB::commit();

                return response([
                    'status' => 'success',
                    'message' => 'Customer Land Agreement added successfully',
                    'data' => $data,
                ], 200);
        }
          catch(\Exception $e)
          {
              DB::rollback();
              return response()->json([
                  'status' => 'failed',
                  'message' => 'Customer Land Agreement information couldn\'t be loaded',
              ]);
          }
   }

    public function view()
    {
        $data = CustomerLandInformation::with([
            'customerInfo', 
            'plotInfo', 
            'landAgreement' => function ($query) {
             $query->with([
                'dolilVariation', 
                'landSellerAgreement' => function ($query) {
                $query->with([
                    'sellerInfo', 
                    'bsInfo'=>function($query){
                     $query->with([
                        'rsInfo'=>function($query){
                            $query->with([
                                'saInfo'=>function($query){
                                    $query->with([
                                        'csInfo'=>function($query){
                                            $query->with([
                                                'mouzaInfo'=>function($query){
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
                    },
                    'dolilVariation'
                ]);
            }]);
        }])
        ->get();
        return response()->json([
            'data' => $data
        ], 200);
    }

    // public function view()
    // {
    //     $user = Auth::user();
    //     $data = $this->getUserData($user);

    //     $userData    = $data['userData'];
    //     $superAdmin  = $data['superAdmin'];
    //     $admin       = $data['admin'];

        
    //     $clientLandSellInfoData=[];
    //     if(!$superAdmin && $admin && isset($userData)){
    //         foreach($userData as $project_id){
    //             $datas = CustomerLandInformation::with([
    //                 'customerInfo', 
    //                 'plotInfo', 
    //                 'landAgreement' => function ($query) {
    //                  $query->with([
    //                     'dolilVariation', 
    //                     'landSellerAgreement' => function ($query) {
    //                     $query->with([
    //                         'sellerInfo', 
    //                         'bsInfo'=>function($query){
    //                          $query->with([
    //                             'rsInfo'=>function($query){
    //                                 $query->select('id','r_s_dag_infos.sa_id');
    //                                 $query->with([
    //                                     'saInfo'=>function($query){
    //                                         $query->select('id','s_a_dag_infos.cs_id');
    //                                         $query->with([
    //                                             'csInfo'=>function($query){
    //                                                 $query->select('id','c_s_dag_infos.mouza_id');
    //                                                 $query->with([
    //                                                     'mouzaInfo'=>function($query){
    //                                                         $query->with([
    //                                                           'projectInfo'=>function($query){
    //                                                             $query->select('id', 'project_name');
    //                                                           }
    //                                                         ]);
    //                                                     }
    //                                                 ]);
    //                                             }
    //                                         ]);
    //                                     }
    //                                 ]);
    //                             }
    //                          ]);
    //                         },
    //                         'dolilVariation'
    //                     ]);
    //                 }]);
    //             }])->whereHas('landAgreement.landSellerAgreement.bsInfo.rsInfo.saInfo.csInfo.mouzaInfo.projectInfo',function ($query) use ($project_id) 
    //                  {
    //                  $query->where('id', $project_id->project_id);
    //                  }
    //                 )->get();
            
    //             if ($datas->isNotEmpty()) {
    //                 array_push( $clientLandSellInfoData, $datas);
    //             }
                
    //         }
    //         return response()->json([
    //             'data' => $clientLandSellInfoData
    //          ],200);
    //     }
    
    //    else if($superAdmin){

    //     $data = CustomerLandInformation::with([
    //         'customerInfo', 
    //         'plotInfo', 
    //         'landAgreement' => function ($query) {
    //          $query->with([
    //             'dolilVariation', 
    //             'landSellerAgreement' => function ($query) {
    //             $query->with([
    //                 'sellerInfo', 
    //                 'bsInfo', 
    //                 'dolilVariation'
    //             ]);
    //         }]);
    //     }])
    //     ->get();
    //     return response()->json([
    //         'data' => $data
    //     ], 200);
    
    // }
        
       
    // }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'agreement_date'=> 'required',
            'land_size_katha'=> 'required',
            'land_size_ojutangsho'=> 'required',
            'deed_no'=> 'required',
            'registry_complete_date'=> 'required',
            'registry_sub_deed_no'=> 'required',
            'dolil_variation_id'=> 'required',
            'namejari_no'=> 'required',
            'namejari_date'=> 'required',

    ]);
    if ($validator->fails()){
            return response()->json([
            'status' => 'failed',
            'message' =>  $validator->messages()->all(),
    ]);
    }

    DB::beginTransaction();
    try{
        $data = CustomerLandAgreement::find(intval($request->id));

        $data->customer_land_information_id = intval($request->customer_land_information_id);
        $data->land_seller_agreement_id = $request->land_seller_agreement_id;
        $data->agreement_date = $request->agreement_date;
        $data->land_size_katha = $request->land_size_katha;
        $data->land_size_ojutangsho = $request->land_size_ojutangsho;
        $data->deed_no = $request->deed_no;
        $data->registry_complete_date = $request->registry_complete_date;
        $data->registry_sub_deed_no = $request->registry_sub_deed_no;
        $data->registry_office = $request->registry_office ?? null;
        $data->dolil_variation_id = $request->dolil_variation_id;
        $data->namejari_no = $request->namejari_no;
        $data->namejari_date = $request->namejari_date;
        $data->description = $request->description ?? null;

        $data->save();
        //Add supported Document
        $files = $request->file('documents');
         if ($files !== null) {
         foreach ($files as $file)
             {
                 $file_name = date('Ymd-his') . '.' . $file->getClientOriginalExtension();
                 $destinationPath = public_path('customer/landAgreementDocuments/');
                 $file->move($destinationPath, $file_name);
                 $file_path = 'customer/landAgreementDocuments/' . $file_name;
                 $file_name = $file->getClientOriginalName();
                 $landAgreement = new CustomerLandAgreementDocuments();
                 $landAgreement->customer_land_agreements_id = $data->id;
                 $landAgreement->file_path = $file_path;
                 $landAgreement->file_name = $file_name;
                 $landAgreement->save();
             }
         }

            DB::commit();

            return response([
                'status' => 'success',
                'message' => 'Customer Land Agreement Updated successfully',
                'data' => $data,
            ], 200);
    }
       catch(\Exception $e)
       {
           DB::rollback();
           return response()->json([
               'status' => 'failed',
               'message' => 'Customer Land Agreement information couldn\'t be loaded',
           ]);
       }
    }


    //View by Dolil Variation
    public function viewByDolilVariation($id)
    {
        $user = Auth::user();
        $data = $this->getUserData($user);

        $userData    = $data['userData'];
        $superAdmin  = $data['superAdmin'];
        $admin       = $data['admin'];

        
        $dolilVariationData=[];

        if(!$superAdmin && $admin && isset($userData)){
            foreach($userData as $project_id){
                $datas = CustomerLandAgreement::with([
                    'customer_land_info' => function ($query) {
                    $query->with(['customerInfo', 'plotInfo']);
                }, 
                'landSellerAgreement' => function ($query) {
                    $query->with(['sellerInfo', 'dolilVariation', 'bsInfo' => function ($query) {
                        $query->with(['rsInfo' => function ($query) {
                            $query->with(['saInfo' => function ($query) {
                                $query->with(['csInfo' => function ($query) {
                                    $query->with(['mouzaInfo' => function ($query) {
                                        $query->with(['projectInfo']);
                                    }]);
                                }]);
                            }]);
                        }]);
                    }]);
                }])->whereHas('landSellerAgreement.bsInfo.rsInfo.saInfo.csInfo.mouzaInfo.projectInfo',function ($query) use ($project_id) 
                {
                $query->where('id', $project_id->project_id);
                }
               )->where('dolil_variation_id', $id)->get();

               if ($datas->isNotEmpty()) {
                array_push($dolilVariationData, $datas);
              }
 
            }
            return response()->json([
                'data' => $dolilVariationData
             ],200);
        }
        else{
        $data = CustomerLandAgreement::with(['customer_land_info' => function ($query) {
            $query->with(['customerInfo', 'plotInfo']);
        }, 'landSellerAgreement' => function ($query) {
            $query->with(['sellerInfo', 'dolilVariation', 'bsInfo' => function ($query) {
                $query->with(['rsInfo' => function ($query) {
                    $query->with(['saInfo' => function ($query) {
                        $query->with(['csInfo' => function ($query) {
                            $query->with(['mouzaInfo' => function ($query) {
                                $query->with(['projectInfo']);
                            }]);
                        }]);
                    }]);
                }]);
            }]);
        }])->where('dolil_variation_id', $id)->get();

        return response()->json([
            'data' => $data
        ], 200);
        }

    
    }

}
