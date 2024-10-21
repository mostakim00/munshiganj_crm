<?php

namespace App\Http\Controllers\LandInformationBank;

use App\Http\Controllers\Controller;
use App\Http\Traits\imageTraits as TraitsImageTraits;
use App\Http\Traits\UserDataTraits;
use App\Models\LandInformationBank\CSDagAndKhatiyan\CSDagInfo;
use App\Models\LandInformationBank\CSDagAndKhatiyan\CSDagRecordedPerson;
use App\Models\LandInformationBank\CSDagAndKhatiyan\CSDagRecordedPersonRelation;
use Intervention\Image\Exception\NotWritableException;
use App\Traits\imageTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth; 

class CSDagKhatiyanController extends Controller
{
    use TraitsImageTraits;
    use UserDataTraits;
    public function store(Request $request){

        $validator = Validator::make($request->all(), [
            'recorded_person.*cs_recorded_person'=> 'required',
            'recorded_person.*father_name'=> 'required',
            'recorded_person.*ownership_size'=> 'required',
            'mouza_id'=> 'required',
            'cs_dag_no'=> 'required',
            'cs_khatiyan_no'=> 'required',
        ]);

        if ($validator->fails()){
            return response()->json([
                'status' => 'failed',
                'message' =>  $validator->messages()->all(),
            ]);
        }

        DB::beginTransaction();
        try{
            // Add CS Dag Info
            $csDagInfo = New CSDagInfo();
            $csDagInfo->mouza_id=$request->mouza_id;
            $csDagInfo->cs_dag_no=$request->cs_dag_no;
            $csDagInfo->cs_khatiyan_no=$request->cs_khatiyan_no;
            $csDagInfo->total_cs_area=$request->total_cs_area;
            $csDagInfo->total_cs_use_area=$request->total_cs_use_area;
            $csDagInfo->cs_porca_scan_copy=$this->getImageUrl($request->file('cs_porca_scan_copy') ?? null,'landInformationBank/csdag&khatiyan/image/porcaScan/');
            $csDagInfo->cs_dag_map_scan_copy=$this->getImageUrl($request->file('cs_dag_map_scan_copy') ?? null,'landInformationBank/csdag&khatiyan/image/dagMapScan/');
            $csDagInfo->save();

            $recordedPersonInfo=array();
            foreach($request->recorded_person as $personData){

                $person = array(
                    'cs_recorded_person'=>$personData['cs_recorded_person'],
                    'cs_recorded_person_fathers_name'=>$personData['father_name'],
                    'cs_recorded_person_ownership_size'=>$personData['ownership_size'],
                );

                $personInfo=CSDagRecordedPerson::create($person);
                array_push($recordedPersonInfo,$personInfo);
            }

           // set relation between cs dag information and cs recorded people
            foreach($recordedPersonInfo as $person){
                $CSDagRecordedPersonRelation= new CSDagRecordedPersonRelation();
                $CSDagRecordedPersonRelation->CSDagRecordedPeopleId = $person->id;
                $CSDagRecordedPersonRelation->CSDagInfoId =  $csDagInfo->id;
                $CSDagRecordedPersonRelation->save();
            }

            DB::commit();
            return response()->json([
                'status'=>'success',
                'message' => 'CS DAG INFO information added succesfully',
                'cs_info_data'=>$csDagInfo,
                'recorded_person_info_data'=>$recordedPersonInfo,
            ],200);
        }
        catch(\Exception $e)
        {
            DB::rollback();
            return response()->json([
                'status' => 'failed',
                'message' => 'CS DAG INFO information couldn\'t be loaded',
                'msg'=>$e->getMessage()
            ],500);
        }



    }
    //view all data in csDagInfo 
    // public function view(){
    //     $csDagInfoData = CSDagInfo::with(['csRecordedPerson','mouzaInfo'
    //     => function ($query) {
    //         $query->with(['projectInfo']);
    //     }
    //     ])->get();

    //     return response()->json([
    //         "data"=> $csDagInfoData
    //     ],200);
        
    // }
    public function view(){
        $user = Auth::user();
        $data = $this->getUserData($user);
        
        $userData    = $data['userData'];
        $superAdmin  = $data['superAdmin'];
        $admin       = $data['admin'];

       $csDagInfoData=[];

        if(!$superAdmin && $admin && isset($userData)){
            foreach($userData as $project_id){
                $datas = CSDagInfo::with([
                    'csRecordedPerson', 
                    'mouzaInfo' => function ($query)
                      {
                      $query->with(['projectInfo']);
                      }
                 ])->whereHas('mouzaInfo.projectInfo',function ($query) use ($project_id) 
                 {
                 $query->where('id', $project_id->project_id);
                 }
                 )->get();
                 if ($datas->isNotEmpty()) {
                array_push($csDagInfoData,$datas);
                 }
            }
            return response()->json([
                'data' => $csDagInfoData
             ],200);
        }

       else if($superAdmin){
        $csDagInfoData = CSDagInfo::with(

        ['csRecordedPerson',
        'mouzaInfo' => function ($query) {
            $query->with(['projectInfo']);
        }
        ]
        )->get();
             return response()->json([
                'data' =>  $csDagInfoData
             ],200);
        }
        
    }

    public function update(Request $request){
        $validator = Validator::make($request->all(), [
            'mouza_id'=> 'required',
            'cs_dag_no'=> 'required',
            'cs_khatiyan_no'=> 'required',
        ]);

        if ($validator->fails()){
            return response()->json([
                'status' => 'failed',
                'message' =>  $validator->messages()->all(),
            ]);
        }
        DB::beginTransaction();
        try{
            $data = CSDagInfo::with(['csRecordedPerson','mouzaInfo'
            => function ($query) {
                $query->with(['projectInfo']);
            }
            ])->find($request->id);

            $csRecordedPersonData = $data->csRecordedPerson;
            $data->mouza_id = $request->mouza_id;
            $data->cs_dag_no = $request->cs_dag_no;
            $data->cs_khatiyan_no = $request->cs_khatiyan_no;
            $data->total_cs_area = $request->total_cs_area;
            $data->total_cs_use_area = $request->total_cs_use_area;

            if ($request->hasFile('cs_porca_scan_copy')) {

                if(file_exists(public_path($data->cs_porca_scan_copy)) && isset($data->cs_porca_scan_copy)){
                    File::delete(public_path( $data->cs_porca_scan_copy));
                }
                
                $data->cs_porca_scan_copy=$this->getImageUrl($request->file('cs_porca_scan_copy') ?? $data->cs_porca_scan_copy,'landInformationBank/csdag&khatiyan/image/porcaScan/');        

            }

            if ($request->hasFile('cs_dag_map_scan_copy')) {
                if(file_exists(public_path($data->cs_dag_map_scan_copy)) && isset($data->cs_dag_map_scan_copy)){
                    File::delete(public_path( $data->cs_dag_map_scan_copy));
                }
                
                $data->cs_dag_map_scan_copy=$this->getImageUrl($request->file('cs_dag_map_scan_copy') ?? $data->cs_porca_scan_copy,'landInformationBank/csdag&khatiyan/image/dagMapScan/');
        
            }

            $data->save();


            $createCsRecordedPersonInfo = []; //array to store create cs recorded person info
            $updateCsRecordedPersonInfo = []; //array to store update cs recorded person info
            foreach($request->recorded_person as $person){

                if(isset($person['id'])){

                    $person_id = $person['id'];
                    $foundPerson =  $csRecordedPersonData->find($person['id']);

                        $foundPerson->update([
                            'cs_recorded_person'=> $person['cs_recorded_person'],
                            'cs_recorded_person_fathers_name'=> $person['father_name'],
                            'cs_recorded_person_ownership_size'=> $person['ownership_size'],
                        ]);

                        $updateCsRecordedPersonInfo[] = $foundPerson;

                }

                else{
                    //create new cs_person
                    $personsData = [
                        'cs_recorded_person' => $person['cs_recorded_person'],
                        'cs_recorded_person_fathers_name' => $person['father_name'],
                        'cs_recorded_person_ownership_size' => $person['ownership_size']
                    ];
                    $personInfo=CSDagRecordedPerson::create($personsData);
                    if($personInfo){
                        $createCsRecordedPersonInfo[]=$personInfo;
                    }
                    $CSDagRecordedPersonRelation= new CSDagRecordedPersonRelation();
                    foreach($createCsRecordedPersonInfo as $person){
                        $CSDagRecordedPersonRelation->CSDagRecordedPeopleId = $person->id;
                        $CSDagRecordedPersonRelation->CSDagInfoId =  $request->id;
                        $CSDagRecordedPersonRelation->save();
                    }

                }
            }
        DB::commit();
        return response()->json([
            "message"=>"success",
            "update"=>  $updateCsRecordedPersonInfo,
            "create"=>  $createCsRecordedPersonInfo,
            "data"=> $data,
        ],200);

        }
        catch (\Exception $e) {
        DB::rollback();
            return response([
                'status' => 'failed',
                'message' => 'CS information update failed',
                'error' => $e->getMessage(),
            ], 500);
        }


    }


}
