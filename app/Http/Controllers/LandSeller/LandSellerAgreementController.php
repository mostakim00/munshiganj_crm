<?php

namespace App\Http\Controllers\LandSeller;

use App\Http\Controllers\Controller;
use App\Http\Traits\UserDataTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\LandSeller\LandSellerAgreement;
use App\Models\LandSeller\LandSellerAgreementPriceInformation;
use App\Models\LandSeller\LandSellerAgreementRelation;
use App\Models\LandSeller\LandSellerAgreementBSRelation;
use App\Models\LandSeller\LandSellerAgreementDolilImage;
use App\Models\LandSeller\LandSellerAgreementPorcaImage;
use App\Models\LandSeller\LandSellerAgreementOtherImage;
use App\Models\LandBroker\LandBrokerPaymentInformation;
use App\Models\LandBroker\LandBrokerAgreement;
use Illuminate\Support\Facades\Auth;
use Monolog\Handler\IFTTTHandler;

class LandSellerAgreementController extends Controller
{
  use UserDataTraits;
  public function store(Request $request)
  {
    $validator = Validator::make($request->all(),[
        'agreement_date'=> 'required',
        'land_size_katha'=> 'required',
        'purchase_deed_no'=> 'required|unique:land_seller_agreements',
        'dolil_variation_id'=> 'required',
        'sub_deed_date'=> 'required',
        'sub_deed_no'=> 'required'
    ]);
    if ($validator->fails()) {
      return response()->json([
          'status' => 'failed',
          'message' =>  $validator->messages()->all(),
      ]);
    }
    DB::beginTransaction();
    try{
      //Land Seller Agreement
      $landSellerAgreement = new LandSellerAgreement;

      $landSellerAgreement->agreement_date = $request->agreement_date;
      $landSellerAgreement->land_size_katha = $request->land_size_katha;
      $landSellerAgreement->purchase_deed_no	 = $request->purchase_deed_no;
      $landSellerAgreement->dolil_variation_id = $request->dolil_variation_id;
      $landSellerAgreement->sub_deed_date = $request->sub_deed_date;
      $landSellerAgreement->sub_deed_no = $request->sub_deed_no;
      $landSellerAgreement->landBrokerID = $request->landBrokerID ?? null;

      $landSellerAgreement->save();

      //Land Seller Agreement Price Information
      $landSellerAgreementPriceInfo = new LandSellerAgreementPriceInformation;

      $landSellerAgreementPriceInfo->land_seller_agreements_id = $landSellerAgreement->id;
      $landSellerAgreementPriceInfo->price_per_katha = $request->price_per_katha ?? 0;
      $landSellerAgreementPriceInfo->total_price = $request->total_price ?? 0;
      $landSellerAgreementPriceInfo->payment_start_date = $request->payment_start_date;
      $landSellerAgreementPriceInfo->approx_payment_complete_date = $request->approx_payment_complete_date;
      $landSellerAgreementPriceInfo->approx_registry_date = $request->approx_registry_date;

      $landSellerAgreementPriceInfo->save();

      //LandBroker Price Informartion
      $landBrokerPriceInfo = new LandBrokerPaymentInformation();
      $landBrokerPriceInfo->land_seller_agreements_id = $landSellerAgreement->id;
      $landBrokerPriceInfo->broker_money = $request->broker_money;
      $landBrokerPriceInfo->save();

      //Set relation between Land Seller and Agreement

      $sellerInfoArray = $request->sellerInfo;
      foreach ($sellerInfoArray as $sellerInfo) {
        $landSellerAgreementRelation = new LandSellerAgreementRelation();
        $landSellerAgreementRelation->land_seller_agreements_id = $landSellerAgreement->id;
        $landSellerAgreementRelation->land_seller_id = $sellerInfo;
        $landSellerAgreementRelation->save();
      }

    //Set relation between Land Seller Agreement & BS DAg

    $bsInfoArray = $request->bsInfo;
    foreach ($bsInfoArray as $bsInfo) {
        $landSellerAgreementBSRelation = new LandSellerAgreementBSRelation();
        $landSellerAgreementBSRelation->land_seller_agreements_id = $landSellerAgreement->id;
        $landSellerAgreementBSRelation->bs_id = $bsInfo;
        $landSellerAgreementBSRelation->save();
    }

        //Store Dolil Image
        $files = $request->file('dolil_image');
        if ($files && $files[0] !== null) {
        foreach ($files as $file)
            {
                $file_name = date('Ymd-his') . '_' . $file->getClientOriginalName();
                $destinationPath = public_path('landSeller/agreement/dolilImage');
                $file->move($destinationPath, $file_name);
                $file_path = 'landSeller/agreement/dolilImage/' . $file_name;
                $file_name = $file->getClientOriginalName();
                $dolilImage = new LandSellerAgreementDolilImage();
                $dolilImage->land_seller_agreements_id = $landSellerAgreement->id;
                $dolilImage->file_path = $file_path;
                $dolilImage->file_name = $file_name;
                $dolilImage->save();
            }
        }

        //Store Porca Image
        $files = $request->file('porca_image');
        if ($files && $files[0] !== null) {
        foreach ($files as $file)
            {
                $file_name = date('Ymd-his') . '_' . $file->getClientOriginalName();
                $destinationPath = public_path('landSeller/agreement/porcaImage');
                $file->move($destinationPath, $file_name);
                $file_path = 'landSeller/agreement/porcaImage/' . $file_name;
                $file_name = $file->getClientOriginalName();
                $porcaImage = new LandSellerAgreementPorcaImage();
                $porcaImage->land_seller_agreements_id = $landSellerAgreement->id;
                $porcaImage->file_path = $file_path;
                $porcaImage->file_name = $file_name;
                $porcaImage->save();
            }
        }
        //Store Other Image
        $files = $request->file('other_image');
        if ($files && $files[0] !== null) {
        foreach ($files as $file)
            {
                $file_name = date('Ymd-his') . '_' . $file->getClientOriginalName();
                $destinationPath = public_path('landSeller/agreement/otherImage');
                $file->move($destinationPath, $file_name);
                $file_path = 'landSeller/agreement/otherImage/' . $file_name;
                $file_name = $file->getClientOriginalName();
                $otherImage = new LandSellerAgreementOtherImage();
                $otherImage->land_seller_agreements_id = $landSellerAgreement->id;
                $otherImage->file_path = $file_path;
                $otherImage->file_name = $file_name;
                $otherImage->save();
            }
        }

        //Landbroker Agreement
        $files = $request->file('broker_agreement');
        if ($files && $files[0] !== null) {
        foreach ($files as $file)
            {
                $file_name = date('Ymd-his') . '_' . $file->getClientOriginalName();
                $destinationPath = public_path('landBroker/agreement');
                $file->move($destinationPath, $file_name);
                $file_path = 'landBroker/agreement/' . $file_name;
                $file_name = $file->getClientOriginalName();
                $brokerAgreement = new LandBrokerAgreement();
                $brokerAgreement->land_seller_agreements_id = $landSellerAgreement->id;
                $brokerAgreement->file_path = $file_path;
                $brokerAgreement->file_name = $file_name;
                $brokerAgreement->save();
            }
        }

        DB::commit();
        return response([
            'status' => 'success',
            'message' => 'Data successfully added',
        ], 200);
    }
    catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'failed',
                'message' => 'Data couldn\'t be loaded',
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
  
      $saDisputeInfoData=[];

      if(!$superAdmin && $admin && isset($userData)){
          foreach($userData as $project_id){
            $datas=LandSellerAgreement::with([
                'sellerInfo', 
                'priceInfo', 
                'dolilImage',
                'otherImage', 
                'porcaImage',
                'landBrokerAgreements', 
                'landBroker', 
                'brokerPriceInfo', 
                'dolilVariation', 
                'bsInfo' => function($query) {
                $query->select('b_s_dag_infos.id', 'rs_id', 'bs_dag_no', 'bs_khatiyan_no', 'total_bs_area');
                $query->with(['rsInfo' => function ($query) {
                    $query->select('r_s_dag_infos.id', 'sa_id', 'rs_dag_no', 'rs_khatiyan_no');
                    $query->with(['saInfo' => function ($query) {
                        $query->select('s_a_dag_infos.id', 's_a_dag_infos.cs_id', 'sa_dag_no', 'sa_khatiyan_no');
                        $query->with(['csInfo' => function ($query) {
                            $query->select('c_s_dag_infos.id','c_s_dag_infos.mouza_id', 'cs_dag_no', 'cs_khatiyan_no');
                            $query->with(['mouzaInfo' => function ($query) {
                                $query->select('mouza_information.id', 'mouza_information.project_id');
                                $query->with(['projectInfo' => function ($query) {
                                    $query->select('project_information.id', 'project_name');
                                }]);
                            }]);
                        }]);
                    }]);
                }]);
            } ])->whereHas('bsInfo.rsInfo.saInfo.csInfo.mouzaInfo.projectInfo',function ($query) use ($project_id) 
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
        $data=LandSellerAgreement::with([
            'sellerInfo', 
            'priceInfo', 
            'dolilImage',
            'otherImage', 
            'porcaImage',
            'landBrokerAgreements', 
            'landBroker', 
            'brokerPriceInfo', 
            'dolilVariation', 
            'bsInfo' => function($query) {
            $query->select('b_s_dag_infos.id', 'rs_id', 'bs_dag_no', 'bs_khatiyan_no', 'total_bs_area');
            $query->with(['rsInfo' => function ($query) {
                $query->select('r_s_dag_infos.id', 'sa_id', 'rs_dag_no', 'rs_khatiyan_no');
                $query->with(['saInfo' => function ($query) {
                    $query->select('s_a_dag_infos.id', 's_a_dag_infos.cs_id', 'sa_dag_no', 'sa_khatiyan_no');
                    $query->with(['csInfo' => function ($query) {
                        $query->select('c_s_dag_infos.id','c_s_dag_infos.mouza_id', 'cs_dag_no', 'cs_khatiyan_no');
                        $query->with(['mouzaInfo' => function ($query) {
                            $query->select('mouza_information.id', 'mouza_information.project_id');
                            $query->with(['projectInfo' => function ($query) {
                                $query->select('project_information.id', 'project_name');
                            }]);
                        }]);
                    }]);
                }]);
            }]);
        } ])->get();

      return response()->json([
          "data" => $data
      ], 200);
      }
  
  }


  public function update(Request $request)
  {
     $validator = Validator::make($request->all(),[
         'agreement_date'=> 'required',
         'land_size_katha'=> 'required',
         'purchase_deed_no'=> 'required',
         'dolil_variation_id'=> 'required',
         'sub_deed_date'=> 'required',
         'sub_deed_no'=> 'required'
     ]);
     if ($validator->fails()) {
         return response()->json([
             'status' => 'failed',
             'message' =>  $validator->messages()->all(),
         ]);
     }
     try {
          $landSellerAgreement = LandSellerAgreement::with([
              'sellerInfo',
              'bsInfo',
              'priceInfo',
              'priceInfo',
              'landBroker'
          ])->where('id', $request->land_seller_agreements_id)->firstOrFail();
          if (!$landSellerAgreement){
              return response()->json([
                  'status' => 'error',
                  'message' => 'Land Seller Agreement list not found'
              ],404);
          }

        //  Land Seller Agreement

         $landSellerAgreement->agreement_date = $request->agreement_date;
         $landSellerAgreement->land_size_katha = $request->land_size_katha;
         $landSellerAgreement->purchase_deed_no	 = $request->purchase_deed_no;
         $landSellerAgreement->dolil_variation_id = $request->dolil_variation_id;
         $landSellerAgreement->sub_deed_date = $request->sub_deed_date;
         $landSellerAgreement->sub_deed_no = $request->sub_deed_no;
         $landSellerAgreement->landBrokerID = $request->landBrokerID ?? null;
         $landSellerAgreement->save();

        //  Land Seller Agreement Price Information

         $landSellerAgreementPriceInfo = LandSellerAgreementPriceInformation::find($request->land_seller_agreements_id);

         $landSellerAgreementPriceInfo->land_seller_agreements_id = $landSellerAgreement->id;
         $landSellerAgreementPriceInfo->price_per_katha = $request->price_per_katha ?? 0;
         $landSellerAgreementPriceInfo->total_price = $request->total_price ?? 0;
         $landSellerAgreementPriceInfo->payment_start_date = $request->payment_start_date;
         $landSellerAgreementPriceInfo->approx_payment_complete_date = $request->approx_payment_complete_date;
         $landSellerAgreementPriceInfo->approx_registry_date = $request->approx_registry_date;
         $landSellerAgreementPriceInfo->save();

          //LandBroker Price Information

         $LandBrokerPriceInfo = LandBrokerPaymentInformation::find($request->land_seller_agreements_id);
         if ($LandBrokerPriceInfo == !null) {
             $LandBrokerPriceInfo->land_seller_agreements_id = $landSellerAgreement->id;
             $LandBrokerPriceInfo->broker_money = $request->broker_money;
             $LandBrokerPriceInfo->save();
         }

            //Set relation between Land Seller and Agreement
          $datas = LandSellerAgreementRelation::where('land_seller_agreements_id', $request->land_seller_agreements_id)->get();
//              $data = LandSellerAgreement::find($request->land_seller_agreements_id);
          $sellerInfoArray = $request->sellerInfo;
          $count = count($sellerInfoArray);
          for ($i = 0; $i < $count; $i++) {

              if (isset($datas[$i]) && ($datas[$i]->land_seller_id !== $sellerInfoArray[$i]) && ($datas[$i]->land_seller_id != null) && ($request->land_seller_agreements_id != null)) {
                  $datas[$i]->land_seller_id = $sellerInfoArray[$i];
                  $datas[$i]->save();
              } else if (!isset($datas[$i])) {
//                      dd($request->land_seller_agreements_id);
                  LandSellerAgreementRelation::create([
                      'land_seller_id' => $sellerInfoArray[$i],
                      'land_seller_agreements_id' => $request->land_seller_agreements_id
                  ]);
              }
          }

          //Set relation between Land Seller Agreement & BS Dag

          $datas = LandSellerAgreementBSRelation::where('land_seller_agreements_id', $request->land_seller_agreements_id)->get();
          $bsInfoArray = $request->bsInfo;
          $count = count($bsInfoArray);

          for ($i = 0; $i < $count; $i++){
              if (isset($datas[$i]) && ($datas[$i]->bs_id !== $bsInfoArray[$i] && ($datas[$i])->bs_id !== null) && ($request->land_seller_agreements_id !== null)){
                  $datas[$i]->bs_id = $bsInfoArray[$i];
                  $datas[$i]->save();
               } else if (!isset($datas[$i])){
                  LandSellerAgreementBSRelation::create([
                     'bs_id' => $bsInfoArray[$i],
                      'land_seller_agreements_id' => $request->land_seller_agreements_id
                  ]);
              }
          }
          //Store Dolil Image
         $files = $request->file('dolil_image');
         if ($files && $files[0] !== null) {
             foreach ($files as $file)
             {
                 $file_name = date('Ymd-his') . '_' . $file->getClientOriginalName();
                 $destinationPath = public_path('landSeller/agreement/dolilImage');
                 $file->move($destinationPath, $file_name);
                 $file_path = 'landSeller/agreement/dolilImage/' . $file_name;
                 $file_name = $file->getClientOriginalName();
                 $dolilImage = new LandSellerAgreementDolilImage();
                 $dolilImage->land_seller_agreements_id = $landSellerAgreement->id;
                 $dolilImage->file_path = $file_path;
                 $dolilImage->file_name = $file_name;
                 $dolilImage->save();
             }
         }

        //Store Porca Image
        $files = $request->file('porca_image');
        if ($files && $files[0] !== null) {
            foreach ($files as $file)
            {
                $file_name = date('Ymd-his') . '_' . $file->getClientOriginalName();
                $destinationPath = public_path('landSeller/agreement/porcaImage');
                $file->move($destinationPath, $file_name);
                $file_path = 'landSeller/agreement/porcaImage/' . $file_name;
                $file_name = $file->getClientOriginalName();
                $porcaImage = new LandSellerAgreementPorcaImage();
                $porcaImage->land_seller_agreements_id = $landSellerAgreement->id;
                $porcaImage->file_path = $file_path;
                $porcaImage->file_name = $file_name;
                $porcaImage->save();
            }
        }

        //Store Other Image

        $files = $request->file('other_image');
        if ($files && $files[0] !== null) {
             foreach ($files as $file)
             {
                 $file_name = date('Ymd-his') . '_' . $file->getClientOriginalName();
                 $destinationPath = public_path('landSeller/agreement/otherImage');
                 $file->move($destinationPath, $file_name);
                 $file_path = 'landSeller/agreement/otherImage/' . $file_name;
                 $file_name = $file->getClientOriginalName();
                 $otherImage = new LandSellerAgreementOtherImage();
                 $otherImage->land_seller_agreements_id = $landSellerAgreement->id;
                 $otherImage->file_path = $file_path;
                 $otherImage->file_name = $file_name;
                 $otherImage->save();
             }
         }

      return response()->json([
          "status" => "success",
          "message" => "Land Seller Agreement updated successfully"
      ], 200);

     } catch (\Exception $e) {
         return response([
             'status' => 'error',
             'message' => 'Land Seller Agreement list update failed',
             'error' => $e->getMessage()
         ], 500);
     }
  }
}
