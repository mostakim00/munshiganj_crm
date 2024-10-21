<?php

namespace App\Http\Controllers\LandSeller;

use App\Http\Controllers\Controller;
use App\Models\LandSeller\LandSellerDocuments;
use App\Models\LandSeller\LandSellerList;
use App\Models\DolilVariation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

class InformationController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'join_date'=> 'required',
            'name' => 'required',
            'father_name' => 'required',
            'nid' => 'required|unique:land_seller_lists',
            'mobile_no_1' => 'required|unique:land_seller_lists'
        ]);
        if ($validator->fails()){
            return response()->json([
                'status' => 'failed',
                'message' =>  $validator->messages()->all(),
            ]);
        }
        try {
            $landSellerInfo = new LandSellerList();

            //Add Seller image
            $file = $request->file('image') ?? null; 
            $file_path = null;
            if ($file && $file !== 'null') {
                $file_name = date('Ymd-his') . '.' . $file->getClientOriginalExtension();
                $destinationPath = 'landSeller/image/' . $file_name;
                Image::make($file->getRealPath())->resize(400, 300)->save(public_path($destinationPath));
                $file_path = $destinationPath;
            }

            //Add Seller Data
            $landSellerInfo->join_date = $request->join_date ?? null;
            $landSellerInfo->name = $request->name;
            $landSellerInfo->father_name = $request->father_name;
            $landSellerInfo->mother_name = $request->mother_name;
            $landSellerInfo->email = $request->email ?? null;
            $landSellerInfo->nid = $request->nid;
            $landSellerInfo->dob = $request->dob;
            $landSellerInfo->mobile_no_1 = $request->mobile_no_1;
            $landSellerInfo->mobile_no_2 = $request->mobile_no_2 ?? null;
            $landSellerInfo->present_address = $request->present_address;
            $landSellerInfo->permanent_address = $request->permanent_address;
            $landSellerInfo->image = $file_path;
            $landSellerInfo->save();

            //Add multiple files
            $files =  $request->file('document');
            if ($files && $files[0] !== null){
                foreach ($files as $file)
                {
                    $file_name = date('Ymd-his') . '.' . $file->getClientOriginalName();
                    $destinationPath = public_path('landSeller/document/');
                    $file->move($destinationPath, $file_name);
                    $file_path = 'landSeller/document/' . $file_name;
                    $file_name = $file->getClientOriginalName();
                    $landSellerInfoDocument = new LandSellerDocuments();
                    $landSellerInfoDocument->land_seller_id = $landSellerInfo->id;
                    $landSellerInfoDocument->file_name = $file_name;
                    $landSellerInfoDocument->file_path = $file_path;
                    $landSellerInfoDocument->save();
                }
            }
            
            DB::commit();
            return response([
                'status' => 'success',
                'message' => 'Land Seller Info added successfully',
                'sellerInfo' => $landSellerInfo
            ], 200);
        }
        catch(\Exception $e)
        {
            DB::rollback();
            return response()->json([
                'status' => 'failed',
                'message' => 'Land Seller information couldn\'t be loaded',
            ]);
        }
    }

    public function view()
    {
        $landSellerData = LandSellerList::all();

        return response()->json([
            'data' => $landSellerData
        ], 200);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'join_date' => 'required',
            'name' => 'required',
            'father_name' => 'required',
            'nid' => 'required',
            'mobile_no_1' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'failed',
                'message' => $validator->messages()->all(),
            ]);
        }
        try {
            $landSellerData = LandSellerList::find($request->id);
            $landSellerData->join_date = $request->join_date;
            $landSellerData->name = $request->name;
            $landSellerData->father_name = $request->father_name;
            $landSellerData->mother_name = $request->mother_name;
            $landSellerData->email = $request->email;
            $landSellerData->nid = $request->nid;
            $landSellerData->dob = $request->dob;
            $landSellerData->mobile_no_1 = $request->mobile_no_1;
            $landSellerData->mobile_no_2 = $request->mobile_no_2;
            $landSellerData->present_address = $request->present_address;
            $landSellerData->permanent_address = $request->permanent_address;

            //Update Seller image
            $file = $request->file('image') ?? null;
            $file_path = null;
            if ($file && $file !== 'null') {
                $file_name = date('Ymd-his') . '.' . $file->getClientOriginalExtension();
                $destinationPath = 'landSeller/image/' . $file_name;
                Image::make($file->getRealPath())->resize(400, 300)->save(public_path($destinationPath));
                $file_path = $destinationPath;
                if(file_exists(public_path($landSellerData->image)) && isset($landSellerData->image))
            {
                unlink(public_path($landSellerData->image));
            }

            $landSellerData->image = $file_path;
            }


            $files = $request->file('document');
            if ($files && $files[0] !== null) {
                foreach ($files as $file) {
                    $file_name = date('Ymd-his') . '.' . $file->getClientOriginalName();
                    $destinationPath = public_path('landSeller/document/');
                    $file->move($destinationPath, $file_name);
                    $file_path = 'landSeller/document/' . $file_name;
                    $file_name = $file->getClientOriginalName();
                    $landSellerInfoDocument = new LandSellerDocuments();
                    $landSellerInfoDocument->land_seller_id = $landSellerData->id;
                    $landSellerInfoDocument->file_name = $file_name;
                    $landSellerInfoDocument->file_path = $file_path;
                    $landSellerInfoDocument->save();
                }
            }
            if ($landSellerData->update()) {
                $landSellerData->save();
                return response([
                    'status' => 'success',
                    'message' => 'Land Seller successfully updated',
                    'agentInfo' => $landSellerData
                ], 200);
            } else {
                return response([
                    'status' => 'error',
                    'message' => 'Land Seller update failed'
                ], 500);
            }
        } catch (\Exception $e) {
            return response([
                'status' => 'error',
                'message' => 'Land Seller Information update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function dolilVaritation()
    {
        $data = DolilVariation::all();

        return response()->json([
            'data' => $data
        ], 200); 
    }
}
