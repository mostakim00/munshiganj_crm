<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;
//use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\Customer\CustomerLandPriceInfo;
use App\Models\Customer\CustomerDownpayment;
use App\Models\Customer\CustomerInstallment;
use App\Models\Customer\PaymentStatement\CustomerPaymentStatementBookingMoney;
use App\Models\Customer\PaymentStatement\CustomerPaymentStatementDownpayment;
use App\Models\Customer\PaymentStatement\CustomerPaymentStatementInstallment;

class PaymentInformationController extends Controller
{
    //Add Customer Payment
    public function store(Request $request)
    {
        //Log::debug($request->all());
        $validator = Validator::make($request->all(),[
            'customer_land_info_id' => 'required',
            'customer_land_price_infos_id' => 'required',
            'amount' => 'required',
            'payment_against' => 'required',
            'payment_date' => 'required',
            'paid_by' => 'required',
            'money_receipt_no' => 'required',

        ]);
        if($validator->fails()){
            return response()->json([
                'status' => 'failed',
                'message' => $validator->messages()->all()
            ]);

        }

        DB::beginTransaction();
        try{
            $paymentInfo = CustomerLandPriceInfo::where('customer_land_info_id', $request->customer_land_info_id)->first();

            //For Booking Money
            if ((int) $request->payment_against === CustomerLandPriceInfo::$BOOKING_MONEY) {

            if (($paymentInfo->booking_money_paid + $request->amount) > $paymentInfo->total_booking_money) {
               return response([
                        'status' => 'failed',
                        'message' => 'Given Amount Can Not Be Greater Than Due Amount. Due Amount is: ' . $paymentInfo->total_booking_money - $paymentInfo->booking_money_paid,
            ]);
            }
            $paymentInfo->booking_money_paid += $request->amount;

            $bookingMoneyPayment = new CustomerPaymentStatementBookingMoney();

            $file =  $request->file('document_image') ?? null;
            $file_path = null;
            if ($file && $file !== 'null') {
                    $file_name = date('Ymd-his') . '.' . $file->getClientOriginalExtension();
                    $destinationPath = 'customer/paymentStatement/bookingMoney/' . $file_name;
                    Image::make($file->getRealPath())->resize(400, 300)->save(public_path($destinationPath));
                    $file_path = $destinationPath;
            }
            $bookingMoneyPayment->customer_land_price_infos_id = $request->customer_land_price_infos_id;
            $bookingMoneyPayment->payment_date = $request->payment_date;
            $bookingMoneyPayment->payment_against = $request->payment_for;
            $bookingMoneyPayment->paid_by = $request->paid_by;
            $bookingMoneyPayment->mobile_banking_account_no = $request->mobile_banking_account_no ?? null;
            $bookingMoneyPayment->bank_name = $request->bank_name ?? null;
            $bookingMoneyPayment->bank_account_no = $request->bank_account_no ?? null;
            $bookingMoneyPayment->bank_cheque_no = $request->bank_cheque_no ?? null;
            $bookingMoneyPayment->money_receipt_no = $request->money_receipt_no ?? null;
            $bookingMoneyPayment->amount = $request->amount;
            $bookingMoneyPayment->document_image = $file_path ?? null;
            $bookingMoneyPayment->save();
            }

            //Downpayment
            else if ((int) $request->payment_against === CustomerLandPriceInfo::$DOWNPAYMENT) {

            $paymentInfo->downpayment_paid += $request->amount;
            $down_Payment = CustomerDownpayment::find($request->downpayment_id);

            if (($down_Payment->paid + $request->amount) > $down_Payment->amount) {
                return response([
                    'status' => 'failed',
                    'message' => 'Given Amount Can Not Be Greater Than Due Amount. Due Amount is:' . $down_Payment->amount - $down_Payment->paid,
                ]);
            }

            $down_Payment->paid += $request->amount;

            $down_Payment->save();


            $downpaymentPayment = new CustomerPaymentStatementDownpayment();
            $file =  $request->file('document_image') ?? null;
            $file_path = null;
            if ($file && $file !== 'null') {
                    $file_name = date('Ymd-his') . '.' . $file->getClientOriginalExtension();
                    $destinationPath = 'customer/paymentStatement/downpayment/' . $file_name;
                    Image::make($file->getRealPath())->resize(400, 300)->save(public_path($destinationPath));
                    $file_path = $destinationPath;
            }

            $downpaymentPayment->customer_land_price_infos_id = $request->customer_land_price_infos_id;
            $downpaymentPayment->downpayment_id = $request->downpayment_id;
            $downpaymentPayment->payment_date = $request->payment_date;
            $downpaymentPayment->payment_against = $request->payment_for;
            $downpaymentPayment->paid_by = $request->paid_by;
            $downpaymentPayment->mobile_banking_account_no = $request->mobile_banking_account_no ?? null;
            $downpaymentPayment->bank_name = $request->bank_name ?? null;
            $downpaymentPayment->bank_account_no = $request->bank_account_no ?? null;
            $downpaymentPayment->bank_cheque_no = $request->bank_cheque_no ?? null;
            $downpaymentPayment->money_receipt_no = $request->money_receipt_no ?? null;
            $downpaymentPayment->amount = $request->amount;
            $downpaymentPayment->document_image = $file_path ?? null;
            $downpaymentPayment->save();

            }
            //installment
            else if ((int) $request->payment_against === CustomerLandPriceInfo::$INSTALLMENT) {
                $paymentInfo->total_installment_amount_paid += $request->amount;
                $install_ment = CustomerInstallment::find($request->installment_id);

                if (($install_ment->paid + $request->amount) > $install_ment->amount) {
                    return response([
                        'status' => 'failed',
                        'message' => 'Given Amount Can Not Be Greater Than Due Amount. Due Amount is:' . $install_ment->amount - $install_ment->paid,
                    ]);
                }
                $install_ment->paid += $request->amount;
                $install_ment->save();


                $installmentPayment = new CustomerPaymentStatementInstallment();

                $file =  $request->file('document_image') ?? null;
                $file_path = null;
                if ($file && $file !== 'null') {
                        $file_name = date('Ymd-his') . '.' . $file->getClientOriginalExtension();
                        $destinationPath = 'customer/paymentStatement/installment/' . $file_name;
                        Image::make($file->getRealPath())->resize(400, 300)->save(public_path($destinationPath));
                        $file_path = $destinationPath;
                }

                $installmentPayment->customer_land_price_infos_id = $request->customer_land_price_infos_id;
                $installmentPayment->installment_id = $request->installment_id;
                $installmentPayment->payment_date = $request->payment_date;
                $installmentPayment->payment_against = $request->payment_for;
                $installmentPayment->paid_by = $request->paid_by;
                $installmentPayment->mobile_banking_account_no = $request->mobile_banking_account_no ?? null;
                $installmentPayment->bank_name = $request->bank_name ?? null;
                $installmentPayment->bank_account_no = $request->bank_account_no ?? null;
                $installmentPayment->bank_cheque_no = $request->bank_cheque_no ?? null;
                $installmentPayment->money_receipt_no = $request->money_receipt_no ?? null;
                $installmentPayment->amount = $request->amount;
                $installmentPayment->document_image = $file_path ?? null;
                $installmentPayment->save();

            }
            $paymentInfo->save();

            DB::commit();
            return response()->json([
                'status' => 'success',
                'messages' => 'Customer payment information added succesfully',
            ],200);

        } catch(\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'failed',
                'message' => "Customer payment information couldn't be loaded"
            ]);
        }
    }

    //View customer payment info
    public function view()
    {
        $data = CustomerLandPriceInfo::with(['downpayment', 'installment', 'booking_money_statement', 'downpayment_statement',
        'installment_statement','landInfo' => function($query){
            $query->with(['customerInfo', 'plotInfo']);
        }])->selectRaw('id,customer_land_info_id,
            total_amount,
            total_booking_money,
            booking_money_paid,
            (total_booking_money - booking_money_paid) as due_booking_amount,
            total_downpayment_amount,
            downpayment_paid,
            (total_downpayment_amount - downpayment_paid) as due_downpayment_amount,
            total_installment_amount,
            total_installment_amount_paid,
            (total_installment_amount - total_installment_amount_paid) as due_installment_amount,
            (booking_money_paid + downpayment_paid + total_installment_amount_paid) as total_payment_amount,
            (total_amount-(booking_money_paid + downpayment_paid + total_installment_amount_paid)) as total_due_amount
            ')->get();

        return response()->json([
            'data' => $data,
        ], 200);
    }

    //Update Customer Payment
    public function update(Request $request)
    {
        //Log::debug($request->all());
        $validator = Validator::make($request->all(),[
            'customer_land_info_id' => 'required',
            'customer_land_price_infos_id' => 'required',
            'amount' => 'required',
            'payment_against' => 'required',
            'payment_date' => 'required',
            'paid_by' => 'required',
            'money_receipt_no' => 'required',

        ]);
        if($validator->fails()){
            return response()->json([
                'status' => 'failed',
                'message' => $validator->messages()->all()
            ]);

        }

        DB::beginTransaction();
       try{
            $paymentInfo = CustomerLandPriceInfo::where('customer_land_info_id', $request->customer_land_info_id)->first();

            //For Booking Money
            if ((int) $request->payment_against === CustomerLandPriceInfo::$BOOKING_MONEY) {

            $bookingMoneyPayment = CustomerPaymentStatementBookingMoney::where('id',
            $request->customer_payment_statement_booking_money_id)->first();

            $file =  $request->file('document_image') ?? null;
            $file_path = null;
            if ($file && $file !== 'null') {
                    $file_name = date('Ymd-his') . '.' . $file->getClientOriginalExtension();
                    $destinationPath = 'customer/paymentStatement/bookingMoney/' . $file_name;
                    Image::make($file->getRealPath())->resize(400, 300)->save(public_path($destinationPath));
                    $file_path = $destinationPath;
            }

            $file = $request->file('document_image') ?? null;
            $file_path = null;
            if ($file && $file !== 'null') {
                $file_name = date('Ymd-his') . '.' . $file->getClientOriginalExtension();
                $destinationPath = 'customer/paymentStatement/bookingMoney/' . $file_name;
                Image::make($file->getRealPath())->resize(400, 300)->save(public_path($destinationPath));
                $file_path = $destinationPath;
                if(file_exists(public_path($bookingMoneyPayment->document_image)) && isset($bookingMoneyPayment->document_image))
            {
                unlink(public_path($bookingMoneyPayment->document_image));
            }
            $bookingMoneyPayment->document_image = $file_path ?? null;
            }

            $bookingMoneyPayment->payment_date = $request->payment_date;
            $bookingMoneyPayment->payment_against = $request->payment_for;
            $bookingMoneyPayment->paid_by = $request->paid_by;
            $bookingMoneyPayment->mobile_banking_account_no = $request->mobile_banking_account_no ?? null;
            $bookingMoneyPayment->bank_name = $request->bank_name ?? null;
            $bookingMoneyPayment->bank_account_no = $request->bank_account_no ?? null;
            $bookingMoneyPayment->bank_cheque_no = $request->bank_cheque_no ?? null;
            $bookingMoneyPayment->money_receipt_no = $request->money_receipt_no ?? null;

            if (($paymentInfo->booking_money_paid + $request->amount) > $paymentInfo->total_booking_money) {
               return response([
                        'status' => 'failed',
                        'message' => 'Given Amount Can Not Be Greater Than Due Amount. Due Amount is: ' . $paymentInfo->total_booking_money - $paymentInfo->booking_money_paid,
            ]);
            }
            $totalBookingMoneyPaid = $paymentInfo->booking_money_paid - $bookingMoneyPayment->amount;
            $paymentInfo->booking_money_paid = $totalBookingMoneyPaid + $request->amount;
            $bookingMoneyPayment->amount = $request->amount;
            $bookingMoneyPayment->save();

            }

            //Downpayment
            else if ((int) $request->payment_against === CustomerLandPriceInfo::$DOWNPAYMENT) {

            $downpaymentPayment = CustomerPaymentStatementDownpayment::where('id',
            $request->customer_payment_statement_downpayments_id)->first();

            $file = $request->file('document_image') ?? null;
            $file_path = null;
            if ($file && $file !== 'null') {
                $file_name = date('Ymd-his') . '.' . $file->getClientOriginalExtension();
                $destinationPath = 'customer/paymentStatement/downpayment/' . $file_name;
                Image::make($file->getRealPath())->resize(400, 300)->save(public_path($destinationPath));
                $file_path = $destinationPath;
                if(file_exists(public_path($downpaymentPayment->document_image)) && isset($downpaymentPayment->document_image))
            {
                unlink(public_path($downpaymentPayment->document_image));
            }
            $downpaymentPayment->document_image = $file_path ?? null;
            }

            $downpaymentPayment->customer_land_price_infos_id = $request->customer_land_price_infos_id;
            $downpaymentPayment->downpayment_id = $request->downpayment_id;
            $downpaymentPayment->payment_date = $request->payment_date;
            $downpaymentPayment->payment_against = $request->payment_for;
            $downpaymentPayment->paid_by = $request->paid_by;
            $downpaymentPayment->mobile_banking_account_no = $request->mobile_banking_account_no ?? null;
            $downpaymentPayment->bank_name = $request->bank_name ?? null;
            $downpaymentPayment->bank_account_no = $request->bank_account_no ?? null;
            $downpaymentPayment->bank_cheque_no = $request->bank_cheque_no ?? null;
            $downpaymentPayment->money_receipt_no = $request->money_receipt_no ?? null;

            $down_Payment = CustomerDownpayment::find($request->downpayment_id);

            if (($down_Payment->paid + $request->amount) > $down_Payment->amount) {
                return response([
                    'status' => 'failed',
                    'message' => 'Given Amount Can Not Be Greater Than Due Amount. Due Amount is:' . $down_Payment->amount - $down_Payment->paid,
                ]);
            }

            $totalSingleDownpaymentPaid  = $down_Payment->paid - $downpaymentPayment->amount;
            $down_Payment->paid = $totalSingleDownpaymentPaid + $request->amount;

            $totalDownpaymentPaid  = $paymentInfo->downpayment_paid -  $downpaymentPayment->amount;
            $paymentInfo->downpayment_paid = $totalDownpaymentPaid  + $request->amount;

            $down_Payment->save();


            $downpaymentPayment->amount = $request->amount;
            $downpaymentPayment->save();

            }
            //installment
            else if ((int) $request->payment_against === CustomerLandPriceInfo::$INSTALLMENT) {

                $installmentPayment = CustomerPaymentStatementInstallment::where('id',
                $request->customer_payment_statement_installments_id)->first();

                $file = $request->file('document_image') ?? null;
                $file_path = null;
                if ($file && $file !== 'null') {
                    $file_name = date('Ymd-his') . '.' . $file->getClientOriginalExtension();
                    $destinationPath = 'customer/paymentStatement/installment/' . $file_name;
                    Image::make($file->getRealPath())->resize(400, 300)->save(public_path($destinationPath));
                    $file_path = $destinationPath;
                    if(file_exists(public_path($installmentPayment->document_image)) && isset($installmentPayment->document_image))
                {
                    unlink(public_path($installmentPayment->document_image));
                }
                $installmentPayment->document_image = $file_path ?? null;
                }

                $installmentPayment->customer_land_price_infos_id = $request->customer_land_price_infos_id;
                $installmentPayment->installment_id = $request->installment_id;
                $installmentPayment->payment_date = $request->payment_date;
                $installmentPayment->payment_against = $request->payment_for;
                $installmentPayment->paid_by = $request->paid_by;
                $installmentPayment->mobile_banking_account_no = $request->mobile_banking_account_no ?? null;
                $installmentPayment->bank_name = $request->bank_name ?? null;
                $installmentPayment->bank_account_no = $request->bank_account_no ?? null;
                $installmentPayment->bank_cheque_no = $request->bank_cheque_no ?? null;
                $installmentPayment->money_receipt_no = $request->money_receipt_no ?? null;

                $install_ment = CustomerInstallment::find($request->installment_id);

                if (($install_ment->paid + $request->amount) > $install_ment->amount) {
                    return response([
                        'status' => 'failed',
                        'message' => 'Given Amount Can Not Be Greater Than Due Amount. Due Amount is:' . $install_ment->amount - $install_ment->paid,
                    ]);
                }


                $totalSingleInstallmentPaid  = $install_ment->paid - $installmentPayment->amount;
                $install_ment->paid = $totalSingleInstallmentPaid + $request->amount;

                $totalInstallmentPaid  = $paymentInfo->total_installment_amount_paid -  $installmentPayment->amount;
                $paymentInfo->total_installment_amount_paid = $totalInstallmentPaid  + $request->amount;

                $install_ment->save();




                $installmentPayment->amount = $request->amount;
                $installmentPayment->save();

            }
            $paymentInfo->save();

            DB::commit();
            return response()->json([
                'status' => 'success',
                'messages' => 'Customer payment information update succesfully',
            ],200);

       } catch(\Exception $e) {
           DB::rollback();
           return response()->json([
               'status' => 'failed',
               'message' => "Customer payment information update failed"
           ]);
       }
    }

    public function statementView($id)
    {
        $data = CustomerLandPriceInfo::with(['downpayment', 'installment', 'booking_money_statement', 'downpayment_statement',
        'installment_statement','landInfo' => function($query){
            $query->with(['customerInfo', 'plotInfo']);
        }])->selectRaw('id,customer_land_info_id,
            total_amount,
            total_booking_money,
            booking_money_paid,
            (total_booking_money - booking_money_paid) as due_booking_amount,
            total_downpayment_amount,
            downpayment_paid,
            (total_downpayment_amount - downpayment_paid) as due_downpayment_amount,
            total_installment_amount,
            total_installment_amount_paid,
            (total_installment_amount - total_installment_amount_paid) as due_installment_amount,
            (booking_money_paid + downpayment_paid + total_installment_amount_paid) as total_payment_amount,
            (total_amount-(booking_money_paid + downpayment_paid + total_installment_amount_paid)) as total_due_amount
            ')->find($id);

        return response()->json([
            'data' => $data,
        ], 200);
    }


}
