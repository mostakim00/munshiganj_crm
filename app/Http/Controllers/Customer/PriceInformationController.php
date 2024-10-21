<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Customer\CustomerLandPriceInfo;
use App\Models\Customer\CustomerDownpayment;
use App\Models\Customer\CustomerInstallment;
use App\Models\Customer\CustomerLandInformation;

class PriceInformationController extends Controller
{
    //Add customer price information
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [

            'customer_land_info_id' => 'required',
            'booking_money_date' => 'required'
        ]);
        if ($validator->fails()){
            return response()->json([
                'status' => 'failed',
                'message' =>  $validator->messages()->all(),
            ]);
        }

        try{

            $priceInfo = CustomerLandPriceInfo::where('customer_land_info_id', $request->customer_land_info_id)->first();
            $priceInfo->total_booking_money = $request->booking_money ?? $priceInfo->total_booking_money;
            $priceInfo->booking_money_date = $request->booking_money_date;

            $priceInfo->save();
  
            $downpaymentArray = array();
            $installmentArray = array();
            foreach ($request->downpayment as $key => $downpayment) {

                $downpaymentAmount = array(
                    'customer_land_price_infos_id' => $priceInfo->id,
                    'amount' => $downpayment['amount'],
                    'paid' => 0,
                    'downpayment_no' => $downpayment['downpayment_no'],
                    'start_date' => $downpayment['start_date'] ?? null
                );

                $downpayments = CustomerDownpayment::create($downpaymentAmount);

                array_push($downpaymentArray, $downpayments);

            }

            foreach ($request->installment as $key => $installment) {

                $installmentAmount = array(
                    'customer_land_price_infos_id' =>  $priceInfo->id,
                    'amount' => $installment['amount'] ?? null,
                    'paid' => 0,
                    'installment_no' => $installment['installment_no'] ?? null,
                    'start_date' => $installment['start_date'] ?? null
                );

                $installments = CustomerInstallment::create($installmentAmount);

                array_push($installmentArray, $installments);

            }

            DB::commit();
            return response([
                'status' => 'success',
                'message' => 'Customer Price Information successfully added',
                'priceInfo' => $priceInfo,
                'downpayment' => $downpaymentArray,
                'installment' => $installmentArray    
            ], 200);
        }
        catch(\Exception $e){
            DB::rollback();
            return response()->json([
               'status' => 'failed',
               'message' => 'Customer Price Information couldn\'t be loaded',
            ]);
        }
    }


    //Update customer price information
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'booking_money' => 'required',
            'booking_money_date' => 'required'
        ]);
        if ($validator->fails()){
            return response()->json([
                'status' => 'failed',
                'message' =>  $validator->messages()->all(),
            ]);
        }
        try{
            $priceInfo = CustomerLandPriceInfo::where('customer_land_info_id', $request->customer_land_info_id)->first();
            if($priceInfo != null){
            $priceInfo->total_booking_money = $request->booking_money ?? $priceInfo->total_booking_money;
            $priceInfo->booking_money_date = $request->booking_money_date;
            $priceInfo->save();
            }
            foreach ($request->downpayment as $key => $downpayment) {
                if(isset($downpayment['id']))
                {
                    $item = CustomerDownpayment::find($downpayment['id']);
                    $item->amount = $downpayment['amount'];
                    $item->downpayment_no = $downpayment['downpayment_no'];
                    $item->start_date = $downpayment['start_date'];

                    $item->save();

                }
                else
                {
                    $downpaymentAmount = array(
                        'customer_land_price_infos_id' => $priceInfo->id,
                        'amount' => $downpayment['amount'] ?? null,
                        'paid' => 0,
                        'downpayment_no' => $downpayment['downpayment_no'] ?? null,
                        'start_date' => $downpayment['start_date'] ?? null
                    );

                    $downpayments = CustomerDownpayment::create($downpaymentAmount);
                }
            }

            foreach ($request->installment as $key => $installment) {

                if(isset($installment['id']))
                {
                    $item = CustomerInstallment::find($installment['id']);
                    $item->installment_no = $installment['installment_no'];
                    $item->amount = $installment['amount'];
                    $item->start_date = $installment['start_date'];
                    $item->save();

                }
                else{
                    $installmentAmount = array(
                        'customer_land_price_infos_id' => $priceInfo->id,
                        'amount' => $installment['amount'] ?? null,
                        'paid' => 0,
                        'installment_no' => $installment['installment_no'] ?? null,
                        'start_date' => $installment['start_date'] ?? null
                    );

                    $installments = CustomerInstallment::create($installmentAmount);
                }

            }
            DB::commit();
            return response([
                'status' => 'success',
                'message' => 'Customer Price Information successfully updated',  
            ], 200);
        }
        catch(\Exception $e){
            DB::rollback();
            return response()->json([
               'status' => 'failed',
               'message' => 'Customer Price Information couldn\'t be updated',
            ]);
        }
    }

    //View Customer Price
    public function view ()
    {
        $data = CustomerLandInformation::with(['customerInfo', 'agent', 'agentPrice', 'plotInfo', 'customer_land_price' => function($query) {
            $query->with(['downpayment', 'installment']);
        } ])->get();

        return response()->json([
            'data' => $data
        ], 200);
    }
}
