<?php

namespace App\Http\Controllers\LandSeller;

use App\Http\Controllers\Controller;
use App\Http\Traits\UserDataTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Validator;
use App\Models\LandSeller\LandSellerAgreementPaymentStatement;
use App\Models\LandSeller\LandSellerAgreementPriceInformation;
use App\Models\LandSeller\LandSellerAgreement;
use Illuminate\Support\Facades\Auth;

class LandSellerPaymentController extends Controller
{
    use UserDataTraits;

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'amount'=> 'required',
            'pay_by'=> 'required',
            'next_payment_date'=> 'required',
            'payment_receiver_name'=> 'required',
        ]);
        if ($validator->fails()) {
          return response()->json([
              'status' => 'failed',
              'message' =>  $validator->messages()->all(),
          ]);
        }
        DB::beginTransaction();
        try{

            $paymentInfo = new LandSellerAgreementPaymentStatement();
            $paymentInfo->land_seller_agreement_price_information_id = $request->land_seller_agreement_price_information_id;
            $paymentInfo->amount = $request->amount ?? 0;
            $paymentInfo->amount_in_words = $request->amount_in_words ?? null;
            $paymentInfo->pay_by = $request->pay_by;
            $paymentInfo->mobile_banking_account_no = $request->mobile_banking_account_no ?? null;
            $paymentInfo->bank_name = $request->bank_name ?? null;
            $paymentInfo->bank_account_no = $request->bank_account_no ?? null;
            $paymentInfo->bank_cheque_no = $request->bank_cheque_no ?? null;
            $paymentInfo->next_payment_date = $request->next_payment_date;
            $paymentInfo->payment_receiver_name = $request->payment_receiver_name;
            $paymentInfo->description = $request->description ?? null;
            //Add Image
            $file =  $request->file('image') ?? null;
            $file_path = null;
            if ($file && $file !== 'null') {
                $file_name = date('Ymd-his') . '.' . $file->getClientOriginalExtension();
                $destinationPath = 'landSeller/agreement/payment/' . $file_name;
                Image::make($file->getRealPath())->resize(400, 300)->save(public_path($destinationPath));
                $file_path = $destinationPath;
            }

            $paymentInfo->image = $file_path ?? null;

            $paymentInfo->save();

            $paymentTotal  = LandSellerAgreementPriceInformation::where('id', $request->land_seller_agreement_price_information_id)->first();

            if (($paymentTotal->paid_amount + $request->amount) > $paymentTotal->total_price) {
                return response([
                    'status' => 'failed',
                    'message' => 'Given Amount Can Not Be Greater Than Due Amount. Due Amount is:' . $paymentTotal->total_price - $paymentTotal->paid_amount,
                ]);
            }

            $paymentTotal->paid_amount += $request->amount;

            $paymentTotal->save();

            DB::commit();
            return response([
                  'status' => 'success',
                  'message' => 'Payment successfully added',
            ], 200);
        }
        catch (\Exception $e) {
                  DB::rollback();
                  return response()->json([
                      'status' => 'failed',
                      'message' => 'Payment couldn\'t be loaded',
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
    
        $landSellerPaymentInfo=[];
   
      if(!$superAdmin && $admin && isset($userData)){
        foreach($userData as $project_id){
            $datas= LandSellerAgreement::with(['sellerInfo' => function ($query) {
                $query->select('land_seller_lists.id', 'name');
            }, 'dolilVariation' => function ($query) {
                $query->select('dolil_variations.id', 'name');
            }, 'bsInfo' => function ($query) {
                $query->select('b_s_dag_infos.id', 'bs_dag_no', 'bs_khatiyan_no');
            },'priceInfo' => function ($query) {
                $query->select('land_seller_agreement_price_information.id', 'land_seller_agreement_price_information.land_seller_agreements_id', 'total_price', 'paid_amount');
                $query->with(['paymentStatement']);
            }])->whereHas('bsInfo.rsInfo.saInfo.csInfo.mouzaInfo.projectInfo',function ($query) use ($project_id) 
            {
            $query->where('id', $project_id->project_id);
            })->selectRaw('id, agreement_date, purchase_deed_no, sub_deed_no, land_size_katha, dolil_variation_id')->get();

            if ($datas->isNotEmpty()) {
                array_push($landSellerPaymentInfo, $datas);
            }
        }
        return response()->json([
            "data"=> $landSellerPaymentInfo
        ],200);
    }

    else if($superAdmin){
        $data= LandSellerAgreement::with(['sellerInfo' => function ($query) {
            $query->select('land_seller_lists.id', 'name');
        }, 'dolilVariation' => function ($query) {
            $query->select('dolil_variations.id', 'name');
        }, 'bsInfo' => function ($query) {
            $query->select('b_s_dag_infos.id', 'bs_dag_no', 'bs_khatiyan_no');
        },'priceInfo' => function ($query) {
            $query->select('land_seller_agreement_price_information.id', 'land_seller_agreement_price_information.land_seller_agreements_id', 'total_price', 'paid_amount');
            $query->with(['paymentStatement']);
        }])->selectRaw('id, agreement_date, purchase_deed_no, sub_deed_no, land_size_katha, dolil_variation_id')->get();

        return response()->json([
            "data" => $data
        ], 200);
    }


    }

    public function viewPaymentStatement()
    {
        $data= LandSellerAgreement::with(['sellerInfo' => function ($query) {
            $query->select('land_seller_lists.id', 'name');
        }, 'dolilVariation' => function ($query) {
            $query->select('dolil_variations.id', 'name');
        }, 'bsInfo' => function ($query) {
            $query->select('b_s_dag_infos.id', 'bs_dag_no', 'bs_khatiyan_no');
        },'priceInfo' => function ($query) {
            $query->select('land_seller_agreement_price_information.id', 'land_seller_agreement_price_information.land_seller_agreements_id', 'total_price', 'paid_amount');
            $query->with(['paymentStatement']);
        }])->selectRaw('id, agreement_date, land_size_katha, dolil_variation_id')->get();

        return response()->json([
          "data"=> $data
      ],200);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'amount'=> 'required',
            'pay_by'=> 'required',
            'next_payment_date'=> 'required',
            'payment_receiver_name'=> 'required',
        ]);
        if ($validator->fails()) {
          return response()->json([
              'status' => 'failed',
              'message' =>  $validator->messages()->all(),
          ]);
        }
        DB::beginTransaction();
        // try{

            $paymentInfo = LandSellerAgreementPaymentStatement::where('id', $request->land_seller_agreement_payment_statements_id)->first();

            $paymentTotal  = LandSellerAgreementPriceInformation::where('id', $request->land_seller_agreement_price_information_id)->first();

            if (($paymentTotal->paid_amount + $request->amount) > $paymentTotal->total_price) {
                return response([
                    'status' => 'failed',
                    'message' => 'Given Amount Can Not Be Greater Than Due Amount. Due Amount is:' . $paymentTotal->total_price - $paymentTotal->paid_amount,
                ]);
            }
            $data = $paymentTotal->paid_amount - $paymentInfo->amount;
            $paymentTotal->paid_amount = $data + $request->amount;

            $paymentTotal->save();

            $paymentInfo->amount = $request->amount ?? 0;
            $paymentInfo->amount_in_words = $request->amount_in_words ?? null;
            $paymentInfo->pay_by = $request->pay_by;
            $paymentInfo->mobile_banking_account_no = $request->mobile_banking_account_no ?? null;
            $paymentInfo->bank_name = $request->bank_name ?? null;
            $paymentInfo->bank_account_no = $request->bank_account_no ?? null;
            $paymentInfo->bank_cheque_no = $request->bank_cheque_no ?? null;
            $paymentInfo->next_payment_date = $request->next_payment_date;
            $paymentInfo->payment_receiver_name = $request->payment_receiver_name;
            $paymentInfo->description = $request->description ?? null;
            //Add Image
            $file = $request->file('image') ?? null;
            $file_path = null;
            if ($file && $file !== 'null') {
                $file_name = date('Ymd-his') . '.' . $file->getClientOriginalExtension();
                $destinationPath = 'landSeller/agreement/payment/' . $file_name;
                Image::make($file->getRealPath())->resize(400, 300)->save(public_path($destinationPath));
                $file_path = $destinationPath;
                if(file_exists(public_path($paymentInfo->image)) && isset($paymentInfo->image))
            {
                unlink(public_path($paymentInfo->image));
            }
            $paymentInfo->image = $file_path ?? null;
            }

            $paymentInfo->save();



            DB::commit();
            return response([
                  'status' => 'success',
                  'message' => 'Payment successfully Updated',
            ], 200);
        // }
        // catch (\Exception $e) {
        //           DB::rollback();
        //           return response()->json([
        //               'status' => 'failed',
        //               'message' => 'Payment couldn\'t be loaded',
        //         ]);
        // }

    }

    public function singleView($id)
    {
        $data= LandSellerAgreement::with(['sellerInfo' => function ($query) {
            $query->select('land_seller_lists.id', 'name');
        }, 'dolilVariation' => function ($query) {
            $query->select('dolil_variations.id', 'name');
        }, 'bsInfo' => function ($query) {
            $query->select('b_s_dag_infos.id', 'bs_dag_no', 'bs_khatiyan_no');
        },'priceInfo' => function ($query) {
            $query->select('land_seller_agreement_price_information.id', 'land_seller_agreement_price_information.land_seller_agreements_id', 'total_price', 'paid_amount');
            $query->with(['paymentStatement']);
        }])->selectRaw('id, agreement_date, purchase_deed_no, sub_deed_no, land_size_katha, dolil_variation_id')->find($id);
        return response()->json([
          "data"=> $data
      ],200);
    }
}
