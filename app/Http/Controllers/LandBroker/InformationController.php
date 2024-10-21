<?php

namespace App\Http\Controllers\LandBroker;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\LandBroker\LandBroker;
use App\Models\LandBroker\LandBrokerDocuments;
use App\Models\LandSeller\LandSellerAgreement;
use Intervention\Image\Facades\Image;


class InformationController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'=> 'required',
            'email' => 'email:rfc,dns|unique:agents',
            'nid' => 'required|unique:agents',
            'mobile_number_1' => 'required|unique:agents'
        ]);
        if ($validator->fails()){
            return response()->json([
                'status' => 'failed',
                'message' =>  $validator->messages()->all()
            ]);
        }
        DB::beginTransaction();
       try {
           //Add LandBroker data
           $landBrokerInfo = new LandBroker();

           $file =  $request->file('image') ?? null;
           $file_path = null;
           if ($file && $file !== 'null') {
               $file_name = date('Ymd-his') . '.' . $file->getClientOriginalExtension();
               $destinationPath = 'landBroker/image/' . $file_name;
               Image::make($file->getRealPath())->resize(400, 300)->save(public_path($destinationPath));
               $file_path = $destinationPath;
           }

           $landBrokerInfo->joining_date = $request->join_date ?? null;
           $landBrokerInfo->name = $request->name;
           $landBrokerInfo->father_name = $request->father_name ?? null;
           $landBrokerInfo->mother_name = $request->mother_name ?? null;
           $landBrokerInfo->nid = $request->nid;
           $landBrokerInfo->dob = $request->dob ?? null;
           $landBrokerInfo->educational_background = $request->educational_background ?? null;
           $landBrokerInfo->mobile_number_1 = $request->mobile_number_1;
           $landBrokerInfo->mobile_number_2 = $request->mobile_number_2 ?? null;
           $landBrokerInfo->email = $request->email ?? null;
           $landBrokerInfo->present_address = $request->present_address ?? null;
           $landBrokerInfo->permanent_address = $request->permanent_address ?? null;
           $landBrokerInfo->image = $file_path;

           $landBrokerInfo->save();

           $landBrokerDocument  =new LandBrokerDocuments();
           $landBrokerDocument->landBroker_id = $landBrokerInfo->id;
           $file = $request->file('other_file') ?? null;
           if ($file && $file !== 'null') {
               $file_name = date('Ymd-his') . '.' . $file->getClientOriginalExtension();
               $destinationPath = 'landBroker/document/' . $file_name;
               $file->move(public_path('landBroker/document/'),$destinationPath);
               $file_path = $destinationPath;
               $file_name = $file->getClientOriginalName();
               $landBrokerDocument->file_path = $file_path;
               $landBrokerDocument->file_name = $file_name;
           }
           $landBrokerDocument->save();

           DB::commit();

           return response([
               'status' => 'success',
               'message' => 'Land Broker successfully added',
               'landBrokerInfo' => $landBrokerInfo,
               'landBrokerDocument' => $landBrokerDocument
           ], 200);
       }
       catch(\Exception $e)
       {
           DB::rollback();
           return response()->json([
               'status' => 'failed',
               'message' => 'Land Broker information couldn\'t be loaded',
           ]);
       }
    }

    public function view()
    {
        $landBrokerData = LandBroker::all();

        return response()->json([
            'data'=> $landBrokerData
        ],200);
    }

    public function update(Request $request)
    {
        try {
            $landBrokerData = LandBroker::find($request->id);
            $landBrokerData->joining_date = $request->joining_date;
            $landBrokerData->name = $request->name;
            $landBrokerData->father_name = $request->father_name;
            $landBrokerData->mother_name = $request->mother_name;
            $landBrokerData->nid = $request->nid;
            $landBrokerData->dob = $request->dob;
            $landBrokerData->educational_background = $request->educational_background;
            $landBrokerData->mobile_number_1 = $request->mobile_number_1;
            $landBrokerData->mobile_number_2 = $request->mobile_number_2;
            $landBrokerData->email = $request->email;
            $landBrokerData->present_address = $request->present_address;
            $landBrokerData->permanent_address = $request->permanent_address;
            $file = $request->file('image') ?? null;
            $file_path = null;
            if ($file && $file !== 'null') {
                $file_name = date('Ymd-his') . '.' . $file->getClientOriginalExtension();
                $destinationPath = 'landBroker/image/' . $file_name;
                Image::make($file->getRealPath())->resize(400, 300)->save(public_path($destinationPath));
                $file_path = $destinationPath;
                if(file_exists(public_path($landBrokerData->image)) && isset($landBrokerData->image))
            {
                unlink(public_path($landBrokerData->image));
            }

            $landBrokerData->image = $file_path;
            }

            if ($landBrokerData->update()) {
                $landBrokerData->save();
                return response([
                    'status' => 'success',
                    'message' => 'Land Broker successfully updated',
                    'agentInfo' => $landBrokerData,
                ], 200);
            } else {
                return response([
                    'status' => 'error',
                    'message' => 'Land Broker did not updated',
                ], 500);
            }
        } catch (\Exception $e) {
            return response([
                'status' => 'error',
                'message' => 'Land Broker updated failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function workList()
    {
        $data = LandSellerAgreement::with(['priceInfo', 'landBroker', 'brokerPriceInfo', 'sellerInfo',
            'bsInfo' => function ($query) {
                $query->with([
                    'rsInfo' => function ($query) {
                        $query->with([
                            'saInfo' => function ($query) {
                                $query->with([
                                    'csInfo' => function ($query) {
                                        $query->with([
                                            'mouzaInfo' => function ($Query) {
                                                $Query->with([
                                                    'projectInfo'
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
        ])->whereNotNull("landBrokerID")->get();
        return response()->json([
            'data' => $data
        ], 200);
    }
}
