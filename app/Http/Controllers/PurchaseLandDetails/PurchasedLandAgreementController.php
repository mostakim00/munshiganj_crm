<?php

namespace App\Http\Controllers\PurchaseLandDetails;

use App\Http\Controllers\Controller;
use App\Http\Traits\UserDataTraits;
use Illuminate\Http\Request;
use App\Models\LandSeller\LandSellerAgreement;
use Illuminate\Support\Facades\Auth;

class PurchasedLandAgreementController extends Controller
{
    use UserDataTraits;
    //All list
    public function allList()
    {
        $user = Auth::user();
        $data = $this->getUserData($user);
        
        $userData    = $data['userData'];
        $superAdmin  = $data['superAdmin'];
        $admin       = $data['admin'];
    
        $purchasedLandAgreementInfo=[];

        if(!$superAdmin && $admin && isset($userData)){
            foreach($userData as $project_id){
                $datas= LandSellerAgreement::with(['sellerInfo' => function ($query) {
                    $query->select('land_seller_lists.id', 'name', 'father_name', 'mother_name', 'mobile_no_1', 'mobile_no_2', 'nid', 'image');
                },'landBroker' => function ($query) {
                    $query->select('land_brokers.id', 'name', 'father_name', 'mother_name', 'mobile_number_1', 'mobile_number_2', 'image');
                }, 'landBrokerAgreements', 'dolilVariation' => function ($query) {
                    $query->select('dolil_variations.id', 'name');
                }, 'dolilImage', 'otherImage', 'porcaImage', 'priceInfo' => function ($query) {
                    $query->select('land_seller_agreement_price_information.id', 'land_seller_agreement_price_information.land_seller_agreements_id', 'total_price', 'paid_amount', 'price_per_katha', 'payment_start_date', 'approx_payment_complete_date', 'approx_registry_date');
                }, 'bsInfo' => function ($query) {
                    $query->select('b_s_dag_infos.id', 'rs_id', 'bs_dag_no', 'bs_khatiyan_no');
                    $query->with(['rsInfo' => function ($query) {
                        $query->select('r_s_dag_infos.id', 'sa_id', 'rs_dag_no', 'rs_khatiyan_no');
                        $query->with(['saInfo' => function ($query) {
                            $query->select('s_a_dag_infos.id', 's_a_dag_infos.cs_id', 'sa_dag_no', 'sa_khatiyan_no');
                            $query->with(['csInfo' => function ($query) {
                                $query->select('c_s_dag_infos.id','c_s_dag_infos.mouza_id', 'cs_dag_no', 'cs_khatiyan_no');
                                $query->with(['mouzaInfo' => function ($query) {
                                    $query->select('mouza_information.id', 'mouza_information.project_id', 'mouza_name');
                                    $query->with(['projectInfo' => function ($query) {
                                        $query->select('project_information.id', 'project_name');
                                    }]);
                                }]);
                            }]);
                        }]);
                    }]);
                }])->whereHas('bsInfo.rsInfo.saInfo.csInfo.mouzaInfo.projectInfo',function ($query) use ($project_id) 
                {
                $query->where('id', $project_id->project_id);
                })->get();
                if ($datas->isNotEmpty()) {
                    array_push($purchasedLandAgreementInfo,$datas);
                }
            }
            return response()->json([
                "data"=> $purchasedLandAgreementInfo
            ],200);
        }
        else if($superAdmin){
        $data= LandSellerAgreement::with(['sellerInfo' => function ($query) {
            $query->select('land_seller_lists.id', 'name', 'father_name', 'mother_name', 'mobile_no_1', 'mobile_no_2', 'nid', 'image');
        },'landBroker' => function ($query) {
            $query->select('land_brokers.id', 'name', 'father_name', 'mother_name', 'mobile_number_1', 'mobile_number_2', 'image');
        }, 'landBrokerAgreements', 'dolilVariation' => function ($query) {
            $query->select('dolil_variations.id', 'name');
        }, 'dolilImage', 'otherImage', 'porcaImage', 'priceInfo' => function ($query) {
            $query->select('land_seller_agreement_price_information.id', 'land_seller_agreement_price_information.land_seller_agreements_id', 'total_price', 'paid_amount', 'price_per_katha', 'payment_start_date', 'approx_payment_complete_date', 'approx_registry_date');
        }, 'bsInfo' => function ($query) {
            $query->select('b_s_dag_infos.id', 'rs_id', 'bs_dag_no', 'bs_khatiyan_no');
            $query->with(['rsInfo' => function ($query) {
                $query->select('r_s_dag_infos.id', 'sa_id', 'rs_dag_no', 'rs_khatiyan_no');
                $query->with(['saInfo' => function ($query) {
                    $query->select('s_a_dag_infos.id', 's_a_dag_infos.cs_id', 'sa_dag_no', 'sa_khatiyan_no');
                    $query->with(['csInfo' => function ($query) {
                        $query->select('c_s_dag_infos.id','c_s_dag_infos.mouza_id', 'cs_dag_no', 'cs_khatiyan_no');
                        $query->with(['mouzaInfo' => function ($query) {
                            $query->select('mouza_information.id', 'mouza_information.project_id', 'mouza_name');
                            $query->with(['projectInfo' => function ($query) {
                                $query->select('project_information.id', 'project_name');
                            }]);
                        }]);
                    }]);
                }]);
            }]);
        }])->get();
        return response()->json([
          "data"=> $data
      ],200);
    }
    }

    //View by dolil variation
    public function viewByDolilVariation($id)
    {
        $user = Auth::user();
        $data = $this->getUserData($user);
        
        $userData    = $data['userData'];
        $superAdmin  = $data['superAdmin'];
        $admin       = $data['admin'];
    
        $dolilVariationInfo=[];
   
      if(!$superAdmin && $admin && isset($userData)){
        foreach($userData as $project_id){
            $datas= LandSellerAgreement::with(['sellerInfo' => function ($query) {
                $query->select('land_seller_lists.id', 'name', 'father_name', 'mother_name', 'mobile_no_1', 'mobile_no_2', 'nid', 'image');
            },'landBroker' => function ($query) {
                $query->select('land_brokers.id', 'name', 'father_name', 'mother_name', 'mobile_number_1', 'mobile_number_2', 'image');
            }, 'landBrokerAgreements', 'dolilVariation' => function ($query) {
                $query->select('dolil_variations.id', 'name');
            }, 'dolilImage', 'otherImage', 'porcaImage', 'priceInfo' => function ($query) {
                $query->select('land_seller_agreement_price_information.id', 'land_seller_agreement_price_information.land_seller_agreements_id', 'total_price', 'paid_amount', 'price_per_katha', 'payment_start_date', 'approx_payment_complete_date', 'approx_registry_date');
            }, 'bsInfo' => function ($query) {
                $query->select('b_s_dag_infos.id', 'rs_id', 'bs_dag_no', 'bs_khatiyan_no');
                $query->with(['rsInfo' => function ($query) {
                    $query->select('r_s_dag_infos.id', 'sa_id', 'rs_dag_no', 'rs_khatiyan_no');
                    $query->with(['saInfo' => function ($query) {
                        $query->select('s_a_dag_infos.id', 's_a_dag_infos.cs_id', 'sa_dag_no', 'sa_khatiyan_no');
                        $query->with(['csInfo' => function ($query) {
                            $query->select('c_s_dag_infos.id','c_s_dag_infos.mouza_id', 'cs_dag_no', 'cs_khatiyan_no');
                            $query->with(['mouzaInfo' => function ($query) {
                                $query->select('mouza_information.id', 'mouza_information.project_id','mouza_name');
                                $query->with(['projectInfo' => function ($query) {
                                    $query->select('project_information.id', 'project_name');
                                }]);
                            }]);
                        }]);
                    }]);
                }]);
            }])->whereHas('bsInfo.rsInfo.saInfo.csInfo.mouzaInfo.projectInfo',function ($query) use ($project_id) 
            {
            $query->where('id', $project_id->project_id);
            })->where('dolil_variation_id', $id)->selectRaw('id, agreement_date, purchase_deed_no, land_size_katha, dolil_variation_id, landBrokerID')->get();

            if ($datas->isNotEmpty()) {
                array_push($dolilVariationInfo, $datas);
            }
        }

        return response()->json([
            "data"=>$dolilVariationInfo
        ],200);

      }

      else if($superAdmin){
        $data= LandSellerAgreement::with(['sellerInfo' => function ($query) {
            $query->select('land_seller_lists.id', 'name', 'father_name', 'mother_name', 'mobile_no_1', 'mobile_no_2', 'nid', 'image');
        },'landBroker' => function ($query) {
            $query->select('land_brokers.id', 'name', 'father_name', 'mother_name', 'mobile_number_1', 'mobile_number_2', 'image');
        }, 'landBrokerAgreements', 'dolilVariation' => function ($query) {
            $query->select('dolil_variations.id', 'name');
        }, 'dolilImage', 'otherImage', 'porcaImage', 'priceInfo' => function ($query) {
            $query->select('land_seller_agreement_price_information.id', 'land_seller_agreement_price_information.land_seller_agreements_id', 'total_price', 'paid_amount', 'price_per_katha', 'payment_start_date', 'approx_payment_complete_date', 'approx_registry_date');
        }, 'bsInfo' => function ($query) {
            $query->select('b_s_dag_infos.id', 'rs_id', 'bs_dag_no', 'bs_khatiyan_no');
            $query->with(['rsInfo' => function ($query) {
                $query->select('r_s_dag_infos.id', 'sa_id', 'rs_dag_no', 'rs_khatiyan_no');
                $query->with(['saInfo' => function ($query) {
                    $query->select('s_a_dag_infos.id', 's_a_dag_infos.cs_id', 'sa_dag_no', 'sa_khatiyan_no');
                    $query->with(['csInfo' => function ($query) {
                        $query->select('c_s_dag_infos.id','c_s_dag_infos.mouza_id', 'cs_dag_no', 'cs_khatiyan_no');
                        $query->with(['mouzaInfo' => function ($query) {
                            $query->select('mouza_information.id', 'mouza_information.project_id','mouza_name');
                            $query->with(['projectInfo' => function ($query) {
                                $query->select('project_information.id', 'project_name');
                            }]);
                        }]);
                    }]);
                }]);
            }]);
        }])->where('dolil_variation_id', $id)->selectRaw('id, agreement_date, purchase_deed_no, land_size_katha, dolil_variation_id, landBrokerID')->get();
        
        return response()->json([
          "data"=> $data
      ],200);
      
    }
    }
}
