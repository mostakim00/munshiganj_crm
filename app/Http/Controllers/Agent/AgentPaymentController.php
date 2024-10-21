<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Agent\AgentPaymentInformation;
use App\Models\Agent\AgentPaymentStatement;
use App\Models\Customer\CustomerLandInformation;


class AgentPaymentController extends Controller
{

    
    //Create Agent Payment
    public function createPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_land_info_id' => 'required'
        ]);
        if ($validator->fails()){
            return response()->json([
                'status' => 'failed',
               'message' =>  $validator->messages()->all()
            ]);
        }


        DB::beginTransaction();

        try
        {
            $create_payment = AgentPaymentInformation::where('customer_land_info_id', $request->customer_land_info_id)->first();
            if ($create_payment) {
                if ($request->payment_purpose === AgentPaymentInformation::$EXTRA_MONEY) {
                    $create_payment->extra_money = $create_payment->extra_money+$request->amount;
                    $create_payment->extra_money_note = $request->note ?? null;
                } else if ($request->payment_purpose === AgentPaymentInformation::$AGENT_CONVEYANCE) {
                    $create_payment->agent_conveyance = $create_payment->agent_conveyance + $request->amount;
                    $create_payment->agent_conveyance_note = $request->note ?? null;
                } else if ($request->payment_purpose === AgentPaymentInformation::$MOBILE_BILL) {
                    $create_payment->mobile_bill = $create_payment->mobile_bill + $request->amount;
                    $create_payment->mobile_bill_note = $request->note ?? null;
                } else if ($request->payment_purpose === AgentPaymentInformation::$ENTERTAINMENT) {
                    $create_payment->entertainment = $create_payment->entertainment + $request->amount;
                    $create_payment->entertainment_note = $request->note ?? null;
                }

                $create_payment->save();
            }

            DB::commit();
            return response([
                'status' => 'success',
                'message' => 'Agent Payment Information added succesfully',
            ], 200);

        }
        catch(\Exception $e)
        {
            DB::rollback();
            return response()->json([
                'status' => 'failed',
                'message' => 'Agent Payment Information couldn\'t be loaded',
            ]);
        }
    }

    //Add Agent Payment

    public function addPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'agent_payment_information_id' => 'required'
        ]);
        if ($validator->fails()){
            return response()->json([
                'status' => 'failed',
                'message' =>  $validator->messages()->all()
            ]);
        }

        try
        {
            $pay_payment = AgentPaymentInformation::where('customer_land_info_id', $request->customer_land_info_id)->first();
            if($pay_payment == !null)
            {
                if ($request->payment_for === AgentPaymentInformation::$AGENT_MONEY){
                    if(($pay_payment->agent_money_paid + $request->amount) > $pay_payment->agent_money){
                        return response([
                            'status' => 'failed',
                            'message' => 'Given Amount Can Not Be Greater Than Due Amount. Due Amount is:' . $pay_payment->agent_money - $pay_payment->agent_money_paid,
                        ]);
                    }
                    $pay_payment->agent_money_paid = $pay_payment->agent_money_paid+$request->amount;
                }
                if ($request->payment_for === AgentPaymentInformation::$EXTRA_MONEY){
                    if(($pay_payment->extra_money_paid + $request->amount) > $pay_payment->extra_money){
                        return response([
                            'status' => 'failed',
                            'message' => 'Given Amount Can Not Be Greater Than Due Amount. Due Amount is:' . $pay_payment->extra_money - $pay_payment->extra_money_paid,
                        ]);
                    }
                    $pay_payment->extra_money_paid = $pay_payment->extra_money_paid+$request->amount;
                }
                if ($request->payment_for === AgentPaymentInformation::$AGENT_CONVEYANCE){
                    if(($pay_payment->agent_conveyance_paid + $request->amount) > $pay_payment->agent_conveyance){
                        return response([
                            'status' => 'failed',
                            'message' => 'Given Amount Can Not Be Greater Than Due Amount. Due Amount is:' . $pay_payment->agent_conveyance - $pay_payment->agent_conveyance_paid,
                        ]);
                    }
                    $pay_payment->agent_conveyance_paid = $pay_payment->agent_conveyance_paid+$request->amount;
                }
                if ($request->payment_for === AgentPaymentInformation::$MOBILE_BILL){
                    if(($pay_payment->mobile_bill_paid + $request->amount) > $pay_payment->mobile_bill){
                        return response([
                            'status' => 'failed',
                            'message' => 'Given Amount Can Not Be Greater Than Due Amount. Due Amount is:' . $pay_payment->mobile_bill - $pay_payment->mobile_bill_paid,
                        ]);
                    }
                    $pay_payment->mobile_bill_paid = $pay_payment->mobile_bill_paid+$request->amount;
                }
                if ($request->payment_for === AgentPaymentInformation::$ENTERTAINMENT){
                    if(($pay_payment->entertainment_paid + $request->amount) > $pay_payment->entertainment){
                        return response([
                            'status' => 'failed',
                            'message' => 'Given Amount Can Not Be Greater Than Due Amount. Due Amount is:' . $pay_payment->entertainment - $pay_payment->entertainment_paid,
                        ]);
                    }
                    $pay_payment->entertainment_paid = $pay_payment->entertainment_paid+$request->amount;
                }

                $pay_payment->save();
            }

            //Add payment statement
            $add_Payment = new AgentPaymentStatement();
            $add_Payment->agent_payment_information_id = $request->agent_payment_information_id;
            $add_Payment->payment_purpose = $request->payment_purpose;
            $add_Payment->amount = $request->amount ?? 0;
            $add_Payment->payment_by = $request->payment_by;
            $add_Payment->mobile_account_no = $request->mobile_account_no ?? null;
            $add_Payment->bank_name = $request->bank_name ?? null;
            $add_Payment->bank_account_no = $request->bank_account_no ?? null;
            $add_Payment->bank_cheque_no = $request->bank_cheque_no ?? null;
            $add_Payment->invoice_no = $request->invoice_no ?? null;
            $add_Payment->note = $request->note ?? null;
            $add_Payment->save();

            DB::commit();

            return response([
                'status' => 'success',
                'message' => 'Payment to agent succeeded',
            ], 200);

        }
        catch(\Exception $e)
        {
            DB::rollback();
            return response()->json([
                'status' => 'failed',
                'message' => 'Payment to agent failed',
            ]);
        }
    }

    //Update Agent Payment

    public function updatePayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'agent_payment_information_id' => 'required'
        ]);
        if ($validator->fails()){
            return response()->json([
                'status' => 'failed',
                'message' =>  $validator->messages()->all()
            ]);
        }

        try
        {

            //Upadte Agent payment statement
            $update_Payment = AgentPaymentStatement::where('id', $request->agent_payment_statements_id)->first();
            $update_Payment->payment_by = $request->payment_by;
            $update_Payment->mobile_account_no = $request->mobile_account_no ?? null;
            $update_Payment->bank_name = $request->bank_name ?? null;
            $update_Payment->bank_account_no = $request->bank_account_no ?? null;
            $update_Payment->bank_cheque_no = $request->bank_cheque_no ?? null;
            $update_Payment->invoice_no = $request->invoice_no ?? null;
            $update_Payment->note = $request->note ?? null;


            $pay_payment = AgentPaymentInformation::where('id', $request->agent_payment_information_id)->first();
            if($pay_payment == !null)
            {
                if ($request->payment_for === AgentPaymentInformation::$AGENT_MONEY){
                    if(($pay_payment->agent_money_paid + $request->amount) > $pay_payment->agent_money){
                        return response([
                            'status' => 'failed',
                            'message' => 'Given Amount Can Not Be Greater Than Due Amount. Due Amount is:' . $pay_payment->agent_money - $pay_payment->agent_money_paid,
                        ]);
                    }
                    $agent_money_paid = $pay_payment->agent_money_paid - $update_Payment->amount;
                    $pay_payment->agent_money_paid = $agent_money_paid + $request->amount;
                }

                if ($request->payment_for === AgentPaymentInformation::$EXTRA_MONEY){
                    if(($pay_payment->extra_money_paid + $request->amount) > $pay_payment->extra_money){
                        return response([
                            'status' => 'failed',
                            'message' => 'Given Amount Can Not Be Greater Than Due Amount. Due Amount is:' . $pay_payment->extra_money - $pay_payment->extra_money_paid,
                        ]);
                    }
                    $extra_money_paid = $pay_payment->extra_money_paid - $update_Payment->amount;
                    $pay_payment->extra_money_paid = $extra_money_paid + $request->amount;
                }
                if ($request->payment_for === AgentPaymentInformation::$AGENT_CONVEYANCE){
                    if(($pay_payment->agent_conveyance_paid + $request->amount) > $pay_payment->agent_conveyance){
                        return response([
                            'status' => 'failed',
                            'message' => 'Given Amount Can Not Be Greater Than Due Amount. Due Amount is:' . $pay_payment->agent_conveyance - $pay_payment->agent_conveyance_paid,
                        ]);
                    }
                    $agent_conveyance_paid = $pay_payment->agent_conveyance_paid - $update_Payment->amount;
                    $pay_payment->agent_conveyance_paid = $agent_conveyance_paid + $request->amount;
                }
                if ($request->payment_for === AgentPaymentInformation::$MOBILE_BILL){
                    if(($pay_payment->mobile_bill_paid + $request->amount) > $pay_payment->mobile_bill){
                        return response([
                            'status' => 'failed',
                            'message' => 'Given Amount Can Not Be Greater Than Due Amount. Due Amount is:' . $pay_payment->mobile_bill - $pay_payment->mobile_bill_paid,
                        ]);
                    }
                    $mobile_bill_paid = $pay_payment->mobile_bill_paid - $update_Payment->amount;
                    $pay_payment->mobile_bill_paid = $mobile_bill_paid + $request->amount;
                }
                if ($request->payment_for === AgentPaymentInformation::$ENTERTAINMENT){
                    if(($pay_payment->entertainment_paid + $request->amount) > $pay_payment->entertainment){
                        return response([
                            'status' => 'failed',
                            'message' => 'Given Amount Can Not Be Greater Than Due Amount. Due Amount is:' . $pay_payment->entertainment - $pay_payment->entertainment_paid,
                        ]);
                    }
                    $entertainment_paid = $pay_payment->entertainment_paid - $update_Payment->amount;
                    $pay_payment->entertainment_paid = $entertainment_paid + $request->amount;
                }

                $update_Payment->amount = $request->amount;
                $update_Payment->save();
                $pay_payment->save();
            }



            DB::commit();

            return response([
                'status' => 'success',
                'message' => 'Payment to agent updated successfully',
            ], 200);

        }
        catch(\Exception $e)
        {
            DB::rollback();
            return response()->json([
                'status' => 'failed',
                'message' => 'Payment to agent update failed',
            ]);
        }
    }

    //View Payment Statement
    public function paymentStatement($id)
    {
        $data = CustomerLandInformation::with([
            'customerInfo',
            'plotInfo',
            'customer_land_price',
            'agent',
            'agentPrice.agentPaymentStatement',
        ])->find($id);

        return response()->json([
            'data'=> $data
        ],200);
    }

}
