<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Agent\Agent;
use App\Models\Agent\AgentDocuments;
use App\Models\Customer\CustomerLandInformation;
use Intervention\Image\Facades\Image;

class InformationController extends Controller
{
    //Add a new Agent
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'=> 'required',
            'email' => 'email:rfc,dns|unique:agents',
            'nid' => 'required|unique:agents',
            'mobile_number_1' => 'required|unique:agents',
        ]);
        if ($validator->fails()){
            return response()->json([
                'status' => 'failed',
                'message' =>  $validator->messages()->all(),
            ]);
        }


        DB::beginTransaction();
       try{
            //Add agent data
            $agentInfo = new Agent();

            $file =  $request->file('image') ?? null;
            $file_path = null;
            if ($file && $file !== 'null') {
                    $file_name = date('Ymd-his') . '.' . $file->getClientOriginalExtension();
                    $destinationPath = 'agent/image/' . $file_name;
                    Image::make($file->getRealPath())->resize(400, 300)->save(public_path($destinationPath));
                    $file_path = $destinationPath;
            }

            $agentInfo->join_date = $request->join_date ?? null;
            $agentInfo->name = $request->name;
            $agentInfo->father_name = $request->father_name ?? null;
            $agentInfo->mother_name = $request->mother_name ?? null;
            $agentInfo->email = $request->email ?? null;
            $agentInfo->nid = $request->nid;
            $agentInfo->dob = $request->dob ?? null;
            $agentInfo->mobile_number_1 = $request->mobile_number_1;
            $agentInfo->mobile_number_2 = $request->mobile_number_2 ?? null;
            $agentInfo->educational_background = $request->educational_background ?? null;
            $agentInfo->present_address = $request->present_address ?? null;
            $agentInfo->permanent_address = $request->permanent_address ?? null;
            $agentInfo->image = $file_path;

            $agentInfo->save();

            //Add multiple files

            $files = $request->file('other_file');
            if ($files && $files[0] !== null) {
            foreach ($files as $file)
                {
                    $file_name = date('Ymd-his') . '.' . $file->getClientOriginalExtension();
                    $destinationPath = public_path('agent/document/');
                    $file->move($destinationPath, $file_name);
                    $file_path = 'agent/document/' . $file_name;
                    $file_name = $file->getClientOriginalName();
                    $agentDocument = new AgentDocuments();
                    $agentDocument->agent_id = $agentInfo->id;
                    $agentDocument->file_path = $file_path;
                    $agentDocument->file_name = $file_name;
                    $agentDocument->save();
                }
            }

            DB::commit();

            return response([
                'status' => 'success',
                'message' => 'Agent added successfully',
                'agentInfo' => $agentInfo,
            ], 200);
       }
       catch(\Exception $e)
       {
           DB::rollback();
           return response()->json([
               'status' => 'failed',
               'message' => 'Agent information couldn\'t be loaded',
           ]);
       }
    }

    //View agent list
    public function view()
    {
        $agentData = Agent::all();

        return response()->json([
            'data'=> $agentData
        ],200);
    }

    //Update Agent
    public function update(Request $request)
    {
        try {
            $agentData = Agent::find($request->id);
            $agentData->join_date = $request->join_date;
            $agentData->name = $request->name;
            $agentData->father_name = $request->father_name;
            $agentData->mother_name = $request->mother_name;
            $agentData->email = $request->email;
            $agentData->nid = $request->nid;
            $agentData->dob = $request->dob;
            $agentData->mobile_number_1 = $request->mobile_number_1;
            $agentData->mobile_number_2 = $request->mobile_number_2;
            $agentData->educational_background = $request->educational_background;
            $agentData->present_address = $request->present_address;
            $agentData->permanent_address = $request->permanent_address;

            $file = $request->file('image') ?? null;
            $file_path = null;
            if ($file && $file !== 'null') {
                $file_name = date('Ymd-his') . '.' . $file->getClientOriginalExtension();
                $destinationPath = 'agent/image/' . $file_name;
                Image::make($file->getRealPath())->resize(400, 300)->save(public_path($destinationPath));
                $file_path = $destinationPath;
                if(file_exists(public_path($agentData->image)) && isset($agentData->image))
            {
                unlink(public_path($agentData->image));
            }

            $agentData->image = $file_path;
            }

            $files = $request->file('other_file');
            if ($files && $files[0] !== null) {
            foreach ($files as $file)
                {
                    $file_name = date('Ymd-his') . '.' . $file->getClientOriginalExtension();
                    $destinationPath = public_path('agent/document/');
                    $file->move($destinationPath, $file_name);
                    $file_path = 'agent/document/' . $file_name;
                    $file_name = $file->getClientOriginalName();
                    $agentDocument = new AgentDocuments();
                    $agentDocument->agent_id = $agentData->id;
                    $agentDocument->file_path = $file_path;
                    $agentDocument->file_name = $file_name;
                    $agentDocument->save();
                }
            }

            if ($agentData->update()) {
                $agentData->save();
                return response([
                    'status' => 'success',
                    'message' => 'Agent successfully updated',
                    'agentInfo' => $agentData,
                ], 200);
            } else {
                return response([
                    'status' => 'error',
                    'message' => 'Agent update failed',
                ], 500);
            }
        } catch (\Exception $e) {
            return response([
                'status' => 'error',
                'message' => 'Agent update failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function workList()
    {
        $data = CustomerLandInformation::with(['customerInfo', 'plotInfo', 'customer_land_price', 'agent', 'agentPrice'])->whereNotNull("agentID") ->get();
        return response()->json([
            'data' => $data
        ], 200);
    }
    

}
