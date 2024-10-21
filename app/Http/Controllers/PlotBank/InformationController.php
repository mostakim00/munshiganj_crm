<?php

namespace App\Http\Controllers\PlotBank;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\PlotBank\PlotInformation;
use App\Models\Customer\CustomerLandInformation;
use App\Models\Customer\CustomerLandPriceInfo;

class InformationController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_start_date'=> 'required',
            'project_name' => 'required',
            'road_width' => 'required',
            'file_no' => 'required|unique:plot_information',
            'road_number_name' => 'required',
            'plot_facing' => 'required',
            'plot_size_katha' => 'required',
            'plot_size_ojutangsho' => 'required',
            'plot_dimension' => 'required',

        ]);
        if ($validator->fails()){
            return response()->json([
                'status' => 'failed',
                'message' =>  $validator->messages()->all(),
            ]);
        }


        DB::beginTransaction();
        try{
            $data = new PlotInformation();

            $data->project_start_date = $request->project_start_date;
            $data->project_name = $request->project_name;
            $data->no_of_plot = $request->no_of_plot ?? 0;
            $data->road_width = $request->road_width;
            $data->file_no = $request->file_no;
            $data->road_number_name = $request->road_number_name;
            $data->plot_facing = $request->plot_facing;
            $data->sector = $request->sector ?? null;
            $data->plot_size_katha = $request->plot_size_katha;
            $data->plot_size_ojutangsho = $request->plot_size_ojutangsho;
            $data->plot_dimension = $request->plot_dimension;
            $data->description = $request->description ?? null;
            $data->save();

            DB::commit();

            return response([
                'status' => 'success',
                'message' => 'Plot added successfully',
                'plotInfo' => $data,
            ], 200);
       }
       catch(\Exception $e)
       {
           DB::rollback();
           return response()->json([
               'status' => 'failed',
               'message' => 'Plot information couldn\'t be loaded',
           ]);
       }
    }

    public function view ()
    {
        $data = PlotInformation::all();

        return response()->json([
            'data'      => $data,      
            ],200);
    }

    public function update(Request $request)
    {
        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'project_start_date'=> 'required',
            'project_name' => 'required',
            'road_width' => 'required',
            'file_no' => 'required|unique:plot_information,file_no,'.$request->id,
            'road_number_name' => 'required',
            'plot_facing' => 'required',
            'plot_size_katha' => 'required',
            'plot_size_ojutangsho' => 'required',
            'plot_dimension' => 'required',

        ]);
        if ($validator->fails()){
            return response()->json([
                'status' => 'failed',
                'message' =>  $validator->messages()->all(),
            ]);
        }


        DB::beginTransaction();
        try{
            
            $data = PlotInformation::where('id', $request->id)->first();

            $data->project_start_date = $request->project_start_date;
            $data->project_name = $request->project_name;
            $data->no_of_plot = $request->no_of_plot ?? 0;
            $data->road_width = $request->road_width;
            $data->file_no = $request->file_no;
            $data->road_number_name = $request->road_number_name;
            $data->plot_facing = $request->plot_facing;
            $data->sector = $request->sector ?? null;
            $data->plot_size_katha = $request->plot_size_katha;
            $data->plot_size_ojutangsho = $request->plot_size_ojutangsho;
            $data->plot_dimension = $request->plot_dimension;
            $data->description = $request->description ?? null;
            $data->update();

            DB::commit();

            return response([
                'status' => 'success',
                'message' => 'Plot Information updated successfully',
                'plotInfo' => $data,
            ], 200);
       }
       catch(\Exception $e)
       {
           DB::rollback();
           return response()->json([
               'status' => 'failed',
               'message' => 'Plot information couldn\'t be updated',
           ]);
       }
    }

    //Show remaining or unsold plots
    public function remainingPlots()
    {
        $data = PlotInformation::whereNotIn('id', function ($query) {
            $query->select('plot_id')
                ->from('customer_land_information');
        })->get();

        return response()->json([
            'data'      => $data,      
            ],200);
    }

    //Show Plot Alot List
    public function bookedPlots()
    {
        $data = CustomerLandInformation::with(['plotInfo', 'customerInfo', 'landAgreement'])->get();

        return response()->json([
            'data'      => $data,      
            ],200);
    }


    //Registry Complete Plot List
    public function registryCompletePlots()
    {
        $data = CustomerLandInformation::with(['plotInfo', 'customerInfo', 'landAgreement' => function ($query) {
            $query->with(['landSellerAgreement' => function ($query) {
                $query->with(['bsInfo']);
            }]);
        }])->get();
        
        return response()->json([
            'data'      => $data,
            ],200);
    }

    //Show Plot Complete Payment List
    public function paymentCompletePlots()
    {
        $data = CustomerLandPriceInfo::with(['landInfo' => function ($query){
            $query->with(['plotInfo', 'customerInfo']);
        }])
        ->where('total_booking_money', '=', DB::raw('booking_money_paid'))
        ->where('total_installment_amount', '=', DB::raw('total_installment_amount_paid'))
        ->where('total_downpayment_amount', '=', DB::raw('downpayment_paid'))
        ->get();

        return response()->json([
            'data'      => $data,      
            ],200);
    }

}
