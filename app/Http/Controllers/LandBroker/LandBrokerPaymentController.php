<?php

namespace App\Http\Controllers\LandBroker;

use App\Http\Controllers\Controller;
use App\Models\LandBroker\LandBrokerPaymentInformation;
use App\Models\LandBroker\LandBrokerPaymentStatement;
use App\Models\LandSeller\LandSellerAgreement;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LandBrokerPaymentController extends Controller
{
    public function createPayment(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'land_seller_agreements_id' => 'required'
        ]);
        if ($validator->fails()){
            return response()->json([
                'status' => 'failed',
               'message' =>  $validator->messages()->all()
            ]);
        }

        try{
            $id=$request->land_seller_agreements_id;
            $payment_purpose=$request->payment_purpose;

            $create_payment=LandBrokerPaymentInformation::where('land_seller_agreements_id',$id)->first();
            if($create_payment){

                if($payment_purpose === LandBrokerPaymentInformation::$EXTRA_MONEY){
                    $create_payment->extra_money = $create_payment->extra_money+$request->amount;
                    $create_payment->extra_money_note = $request->note ?? null;
                }

                else if($payment_purpose === LandBrokerPaymentInformation::$BROKER_CONVEYANCE){
                    $create_payment->conveyance_money = $create_payment->conveyance_money+$request->amount;
                    $create_payment->conveyance_money_note = $request->note ?? null;
                }
                else if($payment_purpose === LandBrokerPaymentInformation::$MOBILE_BILL){
                    $create_payment->mobile_bill = $create_payment->mobile_bill+$request->amount;
                    $create_payment->mobile_bill_note = $request->note ?? null;
                }
                else if($payment_purpose === LandBrokerPaymentInformation::$ENTERTAINMENT){
                    $create_payment->entertainment = $create_payment->entertainment+$request->amount;
                    $create_payment->entertainment_bill_note = $request->note ?? null;
                }
                $create_payment->save();

            }
            DB::commit();
            return response()->json([
                'status' => "success",
                'messages' => 'Land Broker Payment Information Added Successfully',
                'data'=>$create_payment

            ], 200);

        }
        catch(\Exception $e)
        {
            DB::rollback();
            return response()->json([
                'status' => 'failed',
                'message' => 'Land Broker Payment Information couldn\'t be loaded',
            ]);
        }


    }

    //Add Land Broker Payment
    public function addPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'broker_payment_information_id' => 'required'
        ]);
        if ($validator->fails()){
            return response()->json([
                'status' => 'failed',
                'message' =>  $validator->messages()->all()
            ]);
        }

            try{
                $id=$request->land_seller_agreements_id;
                $payment_purpose=$request->payment_for;
                $pay_payment = LandBrokerPaymentInformation::where('land_seller_agreements_id',$id)->first();
                if($pay_payment){
                     if($payment_purpose === LandBrokerPaymentInformation::$BROKER_MONEY){
                        if(($pay_payment->broker_money_paid+$request->amount) > $pay_payment->broker_money){
                            return response([
                                'status' => 'failed',
                                'message' => 'Given Amount Can Not Be Greater Than Due Amount. Due Amount is:' . $pay_payment->broker_money - $pay_payment->broker_money_paid,
                            ]);
                        }

                        $pay_payment->broker_money_paid = $pay_payment->broker_money_paid+$request->amount;

                     }
                     if($payment_purpose === LandBrokerPaymentInformation::$EXTRA_MONEY){
                        if(($pay_payment->extra_money_paid+$request->amount) > $pay_payment->extra_money){
                            return response([
                                'status' => 'failed',
                                'message' => 'Given Amount Can Not Be Greater Than Due Amount. Due Amount is:' . $pay_payment->extra_money - $pay_payment->extra_money_paid,
                            ]);
                        }
                        $pay_payment->extra_money_paid = $pay_payment->extra_money_paid+$request->amount;

                     }
                     if($payment_purpose === LandBrokerPaymentInformation::$BROKER_CONVEYANCE){
                        if(($pay_payment->conveyance_money_paid+$request->amount) > $pay_payment->conveyance_money){
                            return response([
                                'status' => 'failed',
                                'message' => 'Given Amount Can Not Be Greater Than Due Amount. Due Amount is:' . $pay_payment->conveyance_money - $pay_payment->conveyance_money_paid,
                            ]);
                        }

                        $pay_payment->conveyance_money_paid = $pay_payment->conveyance_money_paid+$request->amount;

                     }
                     if($payment_purpose === LandBrokerPaymentInformation::$MOBILE_BILL){
                        if(($pay_payment->mobile_bill_paid+$request->amount) > $pay_payment->mobile_bill){
                            return response([
                                'status' => 'failed',
                                'message' => 'Given Amount Can Not Be Greater Than Due Amount. Due Amount is:' . $pay_payment->mobile_money - $pay_payment->mobile_bill_paid,
                            ]);
                        }
                        $pay_payment->mobile_bill_paid = $pay_payment->mobile_bill_paid+$request->amount;
                     }

                     if($payment_purpose === LandBrokerPaymentInformation::$ENTERTAINMENT){
                        if(($pay_payment->entertainment_paid+$request->amount) > $pay_payment->entertainment_money){
                            return response([
                                'status' => 'failed',
                                'message' => 'Given Amount Can Not Be Greater Than Due Amount. Due Amount is:' . $pay_payment->entertainment_money - $pay_payment->entertainment_money_paid,
                            ]);
                        }
                        $pay_payment->entertainment_money_paid = $pay_payment->entertainment_paid+$request->amount;
                     }
                     $pay_payment->save();
                }

                    $add_payment = new LandBrokerPaymentStatement();
                    $add_payment->land_broker_payment_information_id = $request->broker_payment_information_id;
                    $add_payment->payment_purpose = $request->payment_purpose;
                    $add_payment->amount = $request->amount ?? 0;
                    $add_payment->payment_by = $request->payment_by;
                    $add_payment->mobile_account_no = $request->mobile_account_no ?? null;
                    $add_payment->bank_name = $request->bank_name ?? null;
                    $add_payment->bank_account_no = $request->bank_account_no ?? null;
                    $add_payment->bank_cheque_no = $request->bank_cheque_no ?? null;
                    $add_payment->invoice_no = $request->invoice_no ?? null;
                    $add_payment->note = $request->note ?? null;
                    $add_payment->save();

                    DB::commit();
                return response([
                    'status' => 'success',
                    'message' => 'Land Broker Payment successed',
                ]);
            }
            catch(\Exception $e)
            {

                DB::rollback();
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Land Broker Payment Information couldn\'t be loaded',
                ]);
            }


    }

    //Update Land Broker Payment
    public function updatePayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'land_broker_payment_information_id' => 'required'
        ]);
        if ($validator->fails()){
            return response()->json([
                'status' => 'failed',
                'message' =>  $validator->messages()->all()
            ]);
        }

            try{

                $update_payment = LandBrokerPaymentStatement::where('id', $request->land_broker_payment_statements_id)->first();
                $update_payment->payment_by = $request->payment_by;
                $update_payment->mobile_account_no = $request->mobile_account_no ?? null;
                $update_payment->bank_name = $request->bank_name ?? null;
                $update_payment->bank_account_no = $request->bank_account_no ?? null;
                $update_payment->bank_cheque_no = $request->bank_cheque_no ?? null;
                $update_payment->invoice_no = $request->invoice_no ?? null;
                $update_payment->note = $request->note ?? null;

                $payment_purpose=$request->payment_for;
                $pay_payment = LandBrokerPaymentInformation::where('id', $request->land_broker_payment_information_id)->first();
                if($pay_payment == !null)
                {
                     if($payment_purpose === LandBrokerPaymentInformation::$BROKER_MONEY){
                        if(($pay_payment->broker_money_paid+$request->amount) > $pay_payment->broker_money){
                            return response([
                                'status' => 'failed',
                                'message' => 'Given Amount Can Not Be Greater Than Due Amount. Due Amount is:' . $pay_payment->broker_money - $pay_payment->broker_money_paid,
                            ]);
                        }

                        $broker_money_paid = $pay_payment->broker_money_paid - $update_payment->amount;
                        $pay_payment->broker_money_paid = $broker_money_paid + $request->amount;

                     }
                     if($payment_purpose === LandBrokerPaymentInformation::$EXTRA_MONEY){
                        if(($pay_payment->extra_money_paid+$request->amount) > $pay_payment->extra_money){
                            return response([
                                'status' => 'failed',
                                'message' => 'Given Amount Can Not Be Greater Than Due Amount. Due Amount is:' . $pay_payment->extra_money - $pay_payment->extra_money_paid,
                            ]);
                        }

                        $extra_money_paid = $pay_payment->extra_money_paid - $update_payment->amount;
                        $pay_payment->extra_money_paid = $extra_money_paid + $request->amount;

                     }
                     if($payment_purpose === LandBrokerPaymentInformation::$BROKER_CONVEYANCE){
                        if(($pay_payment->conveyance_money_paid+$request->amount) > $pay_payment->conveyance_money){
                            return response([
                                'status' => 'failed',
                                'message' => 'Given Amount Can Not Be Greater Than Due Amount. Due Amount is:' . $pay_payment->conveyance_money - $pay_payment->conveyance_money_paid,
                            ]);
                        }

                        $conveyance_money_paid = $pay_payment->conveyance_money_paid - $update_payment->amount;
                        $pay_payment->conveyance_money_paid = $conveyance_money_paid + $request->amount;

                     }
                     if($payment_purpose === LandBrokerPaymentInformation::$MOBILE_BILL){
                        if(($pay_payment->mobile_bill_paid+$request->amount) > $pay_payment->mobile_bill){
                            return response([
                                'status' => 'failed',
                                'message' => 'Given Amount Can Not Be Greater Than Due Amount. Due Amount is:' . $pay_payment->mobile_money - $pay_payment->mobile_bill_paid,
                            ]);
                        }

                        $mobile_bill_paid = $pay_payment->mobile_bill_paid - $update_payment->amount;
                        $pay_payment->mobile_bill_paid = $mobile_bill_paid + $request->amount;
                     }

                     if($payment_purpose === LandBrokerPaymentInformation::$ENTERTAINMENT){
                        if(($pay_payment->entertainment_paid+$request->amount) > $pay_payment->entertainment_money){
                            return response([
                                'status' => 'failed',
                                'message' => 'Given Amount Can Not Be Greater Than Due Amount. Due Amount is:' . $pay_payment->entertainment_money - $pay_payment->entertainment_money_paid,
                            ]);
                        }

                        $entertainment_paid = $pay_payment->entertainment_paid - $update_payment->amount;
                        $pay_payment->entertainment_paid = $entertainment_paid + $request->amount;

                     }
                     $update_payment->amount = $request->amount ?? 0;
                    $pay_payment->save();
                    $update_payment->save();
                }



                DB::commit();
                return response([
                    'status' => 'success',
                    'message' => 'Land Broker Payment updated successfully',
                ]);
            }
            catch(\Exception $e)
            {

                DB::rollback();
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Land Broker Payment Information couldn\'t be updated',
                ]);
            }


    }

    //View Payment Statement
    public function paymentStatement($id)
    {
        $data = LandSellerAgreement::with([
            'landBroker',
            'sellerInfo',
            'brokerPriceInfo'=>function($query){
                $query->with([
                    'brokerPaymentStatement'
                ]);
            },
            'priceInfo',
            'bsInfo' => function($query) {
                $query->with([
                    'rsInfo'=>function($query){
                        $query->with([
                            'saInfo'=>function($query){
                                $query->with([
                                    'csInfo'=>function($query){
                                        $query->with([
                                            'mouzaInfo'=>function($query){
                                                $query->with([
                                                    'projectInfo'=>function($query){
                                                        $query->select('id', 'project_name');
                                                    }
                                                ]);
                                            }
                                        ]);
                                    }
                                ]);
                            }
                        ]);
                    }
                ]);
            }
        ])->find($id);

        return response()->json([
            'data'=> $data
        ],200);
    }



}
