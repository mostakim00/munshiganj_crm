<?php

namespace App\Http\Controllers\LandInformationBank;

use App\Http\Controllers\Controller;
use App\Http\Traits\imageTraits;
use App\Http\Traits\UserDataTraits;
use App\Models\LandInformationBank\RSDagAndKhatiyan\RSDagInfo;
use App\Models\LandInformationBank\RSDagAndKhatiyan\RSDagRecordedPerson;
use App\Models\LandInformationBank\RSDagAndKhatiyan\RSDagRecordedPersonRelation;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class RSDagKhatiyanController extends Controller
{
   use imageTraits;
   use UserDataTraits;
   public function store(Request $request){

    $validator = Validator::make($request->all(),[
        'recorded_person.*rs_recorded_person'=>'required',
        'recorded_person.*father_name'=>'required',
        'recorded_person.*ownership_size'=>'required',
        'sa_id'=>'required',
        'rs_dag_no'=>'required',
        'rs_khatiyan_no'=>'required'
    ]);
    if($validator->fails()){
        return response()->json([
            "status"=>"failed",
            "message"=> $validator->messages()->all(),
        ]);
    }
    DB::beginTransaction();
    // try{
        $rsDagInfo=New RSDagInfo();
        $rsDagInfo->sa_id=$request->sa_id;
        $rsDagInfo->rs_dag_no=$request->rs_dag_no;
        $rsDagInfo->rs_khatiyan_no=$request->rs_khatiyan_no;
        $rsDagInfo->total_rs_area=$request->total_rs_area;
        $rsDagInfo->total_rs_use_area=$request->total_rs_use_area;
        $rsDagInfo->rs_porca_scan_copy=$this->getImageUrl($request->file('rs_porca_scan_copy') ?? null,'landInformationBank/rsdag&khatiyan/image/porcaScan/');
        $rsDagInfo->rs_dag_map_scan_copy=$this->getImageUrl($request->file('rs_dag_map_scan_copy') ?? null,'landInformationBank/rsdag&khatiyan/image/dagMapScan/');
        $rsDagInfo->save();

        $recordedPersaonInfo=array();
        foreach($request->recorded_person as $personData){
            $person=array(
                'rs_recorded_person'=>$personData['rs_recorded_person'],
                'rs_recorded_person_fathers_name'=>$personData['rs_recorded_person_fathers_name'],
                'rs_recorded_person_ownership_size'=>$personData['rs_recorded_person_ownership_size'],
            );
            $rsPersonInfo=RSDagRecordedPerson::create($person);
            array_push($recordedPersaonInfo,$rsPersonInfo);
        }

        //set relation between rs dag information rs recorded people
        foreach($recordedPersaonInfo as $person){
            $rsRecordedPersonRelation= new RSDagRecordedPersonRelation();
            $rsRecordedPersonRelation->RSDagInfoId= $rsDagInfo->id;
            $rsRecordedPersonRelation->RSDagRecordedPeopleId=$person->id;
            $rsRecordedPersonRelation->save();
        }

        DB::commit();
            return response()->json([
                'status'=>'success',
                'message' => 'RS DAG INFO information added succesfully',
                'rs_info_data'=>$rsDagInfo,
                'recorded_person_info_data'=>$recordedPersaonInfo,
            ],200);
    // }
    // catch(\Exception $e)
    // {
    //     DB::rollback();
    //     return response()->json([
    //         'status' => 'failed',
    //         'message' => 'RS DAG INFO information couldn\'t be loaded',
    //         'msg'=>$e->getMessage()
    //     ],500);
    // }


   }

public function view(){
    $user = Auth::user();
    $data = $this->getUserData($user);
    
    $userData    = $data['userData'];
    $superAdmin  = $data['superAdmin'];
    $admin       = $data['admin'];

  $saDagInfoData=[];

    if(!$superAdmin && $admin && isset($userData)){
        foreach($userData as $project_id){
        $datas = RSDagInfo::with([
        'rsRecordedPerson',
        'saInfo'=>function($query){
            $query->with([
                'saRecordedPerson',
                'csInfo'=>function($query){
                    $query->with([
                        'csRecordedPerson',
                        'mouzaInfo'=>function($query){
                            $query->with('projectInfo');
                        }
                    ]);
                }
            ]);
        }

    ])->whereHas('saInfo.csInfo.mouzaInfo.projectInfo',function ($query) use ($project_id) 
             {
             $query->where('id', $project_id->project_id);
             }
             )->get();
             if ($datas->isNotEmpty()) {
            array_push($saDagInfoData,$datas);
             }
        }
        return response()->json([
            'data' =>$saDagInfoData
         ],200);
    }

   else if($superAdmin){
    $saDagInfoData = RSDagInfo::with([
        'rsRecordedPerson',
        'saInfo'=>function($query){
            $query->with([
                'saRecordedPerson',
                'csInfo'=>function($query){
                    $query->with([
                        'csRecordedPerson',
                        'mouzaInfo'=>function($query){
                            $query->with('projectInfo');
                        }
                    ]);
                }
            ]);
        }

    ])->get();
         return response()->json([
            'data' => $saDagInfoData
         ],200);
    }
    
}
   public function update(Request $request){
    try{
        $data = RSDagInfo::with([
            'rsRecordedPerson',
            'saInfo'=>function($query){
                $query->with([
                    'saRecordedPerson',
                    'csInfo'=>function($query){
                        $query->with([
                            'csRecordedPerson',
                            'mouzaInfo'=>function($query){
                                $query->with('projectInfo');
                            }
                        ]);
                    }
                ]);
            }

            ])->findOrFail($request->id);
            
    $rsRecordedPersonData=$data->rsRecordedPerson;

    $data->sa_id=$request->sa_id;
    $data->rs_dag_no=$request->rs_dag_no;
    $data->rs_khatiyan_no=$request->rs_khatiyan_no;
    $data->total_rs_area=$request->total_rs_area;
    $data->total_rs_area=$request->total_rs_area;

    if ($request->hasFile('rs_porca_scan_copy')) {

        if(file_exists(public_path($data->rs_porca_scan_copy)) && isset($data->rs_porca_scan_copy)){
            File::delete(public_path( $data->rs_porca_scan_copy));

        }
      
            $data->rs_porca_scan_copy=$this->getImageUrl($request->file('rs_porca_scan_copy') ?? $data->rs_porca_scan_copy,'landInformationBank/rsdag&khatiyan/image/porcaScan/');
        

    }

    if ($request->hasFile('rs_dag_map_scan_copy')) {
        if(file_exists(public_path($data->rs_dag_map_scan_copy)) && isset($data->rs_dag_map_scan_copy)){
            File::delete(public_path( $data->rs_dag_map_scan_copy));

        }
       
            $data->rs_dag_map_scan_copy=$this->getImageUrl($request->file('rs_dag_map_scan_copy') ?? $data->rs_dag_map_scan_copy,'landInformationBank/rsdag&khatiyan/image/dagMapScan/');
       

    }
    $data->save();

    $createRsRecordedPersonInfo = [];
    $updaterRsRecordedPersonInfo = [];

    foreach($request->recorded_person as $person){

        if(isset($person['id'])){
            $foundPerson= $rsRecordedPersonData->find($person['id']);
            $foundPerson->update([
                'rs_recorded_person'=> $person['rs_recorded_person'],
                'rs_recorded_person_fathers_name'=> $person['rs_recorded_person_fathers_name'],
                'rs_recorded_person_ownership_size'=> $person['rs_recorded_person_ownership_size'],
            ]);
            $updaterRsRecordedPersonInfo []=$foundPerson;
        }

        else{
            //create new rs_person
            $personsData = [
                'rs_recorded_person' => $person['rs_recorded_person'],
                'rs_recorded_person_fathers_name' => $person['rs_recorded_person_fathers_name'],
                'rs_recorded_person_ownership_size' => $person['rs_recorded_person_ownership_size']
            ];
            $personInfo=RSDagRecordedPerson::create($personsData);
            if($personInfo){
                $createRsRecordedPersonInfo[]=$personInfo;
            }
            $RSDagRecordedPersonRelation= new RSDagRecordedPersonRelation();
            foreach($createRsRecordedPersonInfo as $person){
                $RSDagRecordedPersonRelation->RSDagRecordedPeopleId = $person->id;
                $RSDagRecordedPersonRelation->RSDagInfoId =  $request->id;
                $RSDagRecordedPersonRelation->save();
            }
        }

    }
    return response()->json([
        "message"=>"success",
        "create"=>  $createRsRecordedPersonInfo,
        "update"=>  $updaterRsRecordedPersonInfo,
        "data" =>$data
    ],200);


   }
    catch (\Exception $e) {
        return response([
            'status' => 'failed',
            'message' => 'RS information update failed',
            'error' => $e->getMessage(),
        ], 500);
    }
}


}
