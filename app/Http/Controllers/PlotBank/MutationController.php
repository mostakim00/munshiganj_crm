<?php

namespace App\Http\Controllers\PlotBank;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\PlotBank\MutationComplete;

class MutationController extends Controller
{

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_land_agreement_id'=> 'required',
            'mutation_no'=> 'required',
            'mutation_date'=> 'required',
        ]);
        if ($validator->fails()){
            return response()->json([
                'status' => 'failed',
                'message' =>  $validator->messages()->all(),
            ]);
        }


        DB::beginTransaction();
        try{
            $data = new MutationComplete();
            
            $data->customer_land_agreement_id = $request->customer_land_agreement_id;
            $data->mutation_no = $request->mutation_no;
            $data->mutation_date = $request->mutation_date;
            $data->description = $request->description ?? null;

            $data->save();

            DB::commit();

            return response([
                'status' => 'success',
                'message' => 'Mutation data added successfully',
                'data' => $data,
            ], 200);
       }
       catch(\Exception $e)
       {
           DB::rollback();
           return response()->json([
               'status' => 'failed',
               'message' => 'Mutation data couldn\'t be loaded',
           ]);
       }
    }

    public function view()
    {
        $data = MutationComplete::with(['customer_land_agreement_info' => function ($query) {
            $query->with(['landSellerAgreement' => function ($query) {
                $query->with(['bsInfo']);
            }, 'customer_land_info' => function ($query) {
                $query->with(['plotInfo','customerInfo']);
            }]);
        }])->get();

        return response()->json([
            'data'      => $data,   
        ], 200);
    }
}
