<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;
use App\Models\Customer\CustomerInfo;
use App\Models\Customer\CustomerLandInformation;
use App\Models\Customer\CustomerLandPriceInfo;
use App\Models\Customer\CustomerLandRelation;
use App\Models\Agent\AgentPaymentInformation;
use App\Models\Customer\CustomerAgentAgreement;


class InformationController extends Controller
{

    private static $imageUrl;


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer.*.name' => 'required',
            'customer.*.father_name' => 'required',
            'customer.*.mother_name' => 'required',
            'customer.*.nid' => 'required|unique:customer_infos',
            'customer.*.dob' => 'required',
            'customer.*.mobile_number_1' => 'required|unique:customer_infos',
            'customer.*.email' => 'required|email:rfc,dns|unique:customer_infos',
            'customer.*.permanent_address' => 'required',
            'customer.*.image' => 'image|mimes:jpg,png,jpeg,svg',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'failed',
                'message' =>  $validator->messages()->all(),

            ]);
        }

        DB::beginTransaction();
        // try {
            //Store Customer Info----
            $customerInfoArray = array();
            if(is_array($request->customer))
            {
                foreach ($request->customer  as $customerData) {
                    $file = $customerData['image'] ?? null;
                    $file_path = null;
                    if ($file && $file !== 'null') {
                        $file_name = date('Ymd-his') . '.' . $file->getClientOriginalName();
                        $destinationPath = 'customer/image/' . $file_name;
                        Image::make($file->getRealPath())->resize(400, 300)->save(public_path($destinationPath));
                        $file_path = $destinationPath;
                    }
    
                    $customer = array(
                        'name' => $customerData['name'] ?? null,
                        'father_name' => $customerData['father_name'],
                        'mother_name' => $customerData['mother_name'],
                        'spouse_name' => $customerData['spouse_name'] ?? null,
                        'spouse_type' => $customerData['spouse_type'] ?? null,
                        'nid' => $customerData['nid'],
                        'dob' => $customerData['dob'],
                        'mobile_number_1' => $customerData['mobile_number_1'],
                        'mobile_number_2' => $customerData['mobile_number_2'] ?? null,
                        'other_file_no' => $customerData['other_file_no'] ?? null,
                        'profession' => $customerData['profession'] ?? null,
                        'designation' => $customerData['designation'] ?? null,
                        'email' => $customerData['email'] ?? null,
                        'mailing_address' => $customerData['mailing_address'] ?? null,
                        'permanent_address' => $customerData['permanent_address'],
                        'office_address' => $customerData['office_address'] ?? null,
                        'image' => $file_path,
                    );
    
                    $customerInfo = CustomerInfo::create($customer);
    
                    array_push($customerInfoArray, $customerInfo);
                }
            }
            
            //Store Customer Land Data----
            $customerLandInfo = new CustomerLandInformation();

            $customerLandInfo->plot_id = $request->plot_id;
            $customerLandInfo->booking_date = $request->booking_date ?? null;
            $customerLandInfo->expected_reg_date = $request->expected_reg_date ?? null;
            $customerLandInfo->plot_address_description = $request->plot_address_description ?? null;
            $customerLandInfo->agentID = $request->agentID;
            $customerLandInfo->save();

            //Store Customer Land Price Info----
            $customerLandPriceInfo = new CustomerLandPriceInfo();

            $customerLandPriceInfo->customer_land_info_id = $customerLandInfo->id;
            $customerLandPriceInfo->total_amount = $request->total_amount ?? 0;
            $customerLandPriceInfo->land_price_per_decimal = $request->land_price_per_decimal ?? 0;
            $customerLandPriceInfo->total_booking_money = $request->total_booking_money ?? 0;
            $customerLandPriceInfo->total_downpayment_amount = $request->total_downpayment_amount ?? 0;
            $customerLandPriceInfo->total_installment_amount = $request->total_installment_amount ?? 0;
            $customerLandPriceInfo->no_of_installment = $request->no_of_installment ?? 0;
            $customerLandPriceInfo->per_month_installment = $request->per_month_installment ?? 0;

            $customerLandPriceInfo->save();


            //Store Agent Price Info

            $agentPriceInfo = new AgentPaymentInformation();

            $agentPriceInfo->customer_land_info_id  = $customerLandInfo->id;
            $agentPriceInfo->agent_money = $request->agent_money ?? 0;

            $agentPriceInfo->save();

            //Set relation between customer and land-----
            foreach ($customerInfoArray as $customerInfo) {
                $customerLandRelation = new CustomerLandRelation();
                $customerLandRelation->customerID = $customerInfo->id;
                $customerLandRelation->landID = $customerLandInfo->id;
                $customerLandRelation->save();
            }

             //Add agent agreement

             $files = $request->file('agreements');
             if ($files && $files[0] !== null) {
             foreach ($files as $file) 
                 {
                     $file_name = date('Ymd-his') . '.' . $file->getClientOriginalExtension();
                     $destinationPath = public_path('customer/agentAgreement/');
                     $file->move($destinationPath, $file_name);
                     $file_path = 'customer/agentAgreement/' . $file_name;
                     $file_name = $file->getClientOriginalName();
                     $agentAgreement = new CustomerAgentAgreement();
                     $agentAgreement->customer_land_info_id = $customerLandInfo->id;
                     $agentAgreement->file_path = $file_path;
                     $agentAgreement->file_name = $file_name;
                     $agentAgreement->save();
                 }
             }

            DB::commit();

            return response([
                'status' => 'success',
                'message' => 'Customer successfully added',
                'customersInfo' => $customerInfoArray,
                'customerLandInfo' => $customerLandInfo,
                'customerLandPriceInfo' => $customerLandPriceInfo,
                'agentPriceInfo' => $agentPriceInfo
            ], 200);
        // } catch (\Exception $e) {
        //     DB::rollback();
        //     return response()->json([
        //         'status' => 'failed',
        //         'message' => 'Customer information couldn\'t be loaded',
        //     ]);
        // }
    }


    public function view()
    {
          
        $data = CustomerLandInformation::with(['customerInfo', 'plotInfo', 'customer_land_price', 'agent', 'agentPrice'])->get();

        return response()->json([
            'data' => $data
        ], 200);
    }

    public function getImageUrl($customerData)
    {
        $file = $customerData['image'] ?? null;
        $file_path = null;
        if ($file && $file !== 'null') {
            $file_name = date('Ymd-his') . '.' . $file->getClientOriginalExtension();
            $destinationPath = 'customer/image/' . $file_name;
            Image::make($file->getRealPath())->resize(400, 300)->save(public_path($destinationPath));
            $file_path = $destinationPath;
            return  $file_path;
        }
    }



    public function update(Request $request)
    {
        try {
            $data = CustomerLandInformation::with([
                'customerInfo',
                'customer_land_price',
                'agent',
                'agentPrice',
            ])->where('id', $request->id)->firstOrFail();
            $relatedCustomers = $data->customerInfo;
            $updatedCustomers = []; // Array to store updated customers
            $createNewCustomers=[]; // Array to store created new customers

            foreach ($request->customer as $customerData) {
                if(isset($customerData['customer_id'])){
                    $customer_id = $customerData['customer_id'];
                    $foundCustomer = $relatedCustomers->where('id', $customer_id)->first();
                    $foundCustomer->update([
                        'name' => $customerData['name'],
                        'father_name' => $customerData['father_name'],
                        'mother_name' => $customerData['mother_name'],
                        'spouse_name' => $customerData['spouse_name'],
                        'spouse_type' => $customerData['spouse_type'] ?? null,
                        'nid' => $customerData['nid'],
                        'dob' => $customerData['dob'],
                        'mobile_number_1' => $customerData['mobile_number_1'],
                        'other_file_no' => $customerData['other_file_no'],
                        'profession' => $customerData['profession'],
                        'designation' => $customerData['designation'],
                        'email' => $customerData['email'],
                        'mailing_address' => $customerData['mailing_address'],
                        'permanent_address' => $customerData['permanent_address'],
                        'office_address' => $customerData['office_address'],
                        //'image'=>self::$imageUrl,
                    ]);
                    $updatedCustomers[] = $foundCustomer;

                }



                else {
                    // Create a new customer
                    $customer = [
                        'name' => $customerData['name'] ?? null,
                        'father_name' => $customerData['father_name'],
                        'mother_name' => $customerData['mother_name'],
                        'spouse_name' => $customerData['spouse_name'] ?? null,
                        'spouse_type' => $customerData['spouse_type'] ?? null,
                        'nid' => $customerData['nid'],
                        'dob' => $customerData['dob'],
                        'mobile_number_1' => $customerData['mobile_number_1'],
                        'mobile_number_2' => $customerData['mobile_number_2'] ?? null,
                        'other_file_no' => $customerData['other_file_no'] ?? null,
                        'profession' => $customerData['profession'] ?? null,
                        'designation' => $customerData['designation'] ?? null,
                        'email' => $customerData['email'] ?? null,
                        'mailing_address' => $customerData['mailing_address'] ?? null,
                        'permanent_address' => $customerData['permanent_address'],
                        'office_address' => $customerData['office_address'] ?? null,
                        // 'image'=>self::$imageUrl ?? null,
                    ];

                    $customerInfo = CustomerInfo::create($customer);
                    if ($customerInfo) {
                        // Store new customer
                        $createNewCustomers[] = $customerInfo;
                    }
                }
            }

             //Set relation between customer and land-----
            if (!empty($createNewCustomers)) {
                foreach ($createNewCustomers as $customerInfo) {
                    $customerLandRelation = new CustomerLandRelation();
                    $customerLandRelation->customerID = $customerInfo->id;
                    $customerLandRelation->landID = $data->customer_land_price->customer_land_info_id;
                    $customerLandRelation->save();
                }
            }


            //update land price info
            $data->customer_land_price->total_amount = $request->total_amount;
            $data->customer_land_price->land_price_per_decimal = $request->land_price_per_decimal;
            $data->customer_land_price->booking_money_date = $request->booking_date;
            $data->customer_land_price->total_booking_money = $request->total_booking_money;
            $data->customer_land_price->total_downpayment_amount = $request->total_downpayment_amount;
            $data->customer_land_price->total_installment_amount = $request->total_installment_amount;
            $data->customer_land_price->no_of_installment = $request->no_of_installment;
            $data->customer_land_price->per_month_installment = $request->per_month_installment;
            $data->customer_land_price->save();

            //update agentPrice table

            $data->agentPrice->agent_money = $request->agent_money;
            $data->agentPrice->save();

            //update customer_land_information info table
            $data->plot_id = $request->plot_id;
            $data->booking_date = $request->booking_date ?? null;
            $data->expected_reg_date = $request->expected_reg_date ?? null;
            $data->plot_address_description = $request->plot_address_description ?? null;
            $data->agentID = $request->agentID;
            $data->save();

             //Update agent agreement

             $files = $request->file('agreements');
             if ($files && $files[0] !== null) {
             foreach ($files as $file) 
                 {
                     $file_name = date('Ymd-his') . '.' . $file->getClientOriginalExtension();
                     $destinationPath = public_path('customer/agentAgreement/');
                     $file->move($destinationPath, $file_name);
                     $file_path = 'customer/agentAgreement/' . $file_name;
                     $file_name = $file->getClientOriginalName();
                     $agentAgreement = new CustomerAgentAgreement();
                     $agentAgreement->customer_land_info_id = $request->id;
                     $agentAgreement->file_path = $file_path;
                     $agentAgreement->file_name = $file_name;
                     $agentAgreement->save();
                 }
             }



            return response()->json([
                'status' => "success",
                'messages' => 'Customer Data successfully Updated',
                'data' =>  $data,

            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Data couldn\'t be updated',
                'error_message' => $e->getMessage(),
            ], 500);
        }
    }

    public function allAgentView()
    {
        $data = DB::table('agents')->select('id', 'name', 'mobile_number_1')->get();

        return response()->json([
            'data' => $data
        ], 200);
    }
}
