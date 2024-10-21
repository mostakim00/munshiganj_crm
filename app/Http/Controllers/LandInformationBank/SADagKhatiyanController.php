<?php

namespace App\Http\Controllers\LandInformationBank;

use App\Http\Controllers\Controller;
use App\Http\Traits\imageTraits as TraitsImageTraits;
use App\Http\Traits\UserDataTraits;

use App\Models\LandInformationBank\SADagAndKhatiyan\SADagInfo;
use App\Models\LandInformationBank\SADagAndKhatiyan\SADagRecordedPersonRelation;
use App\Models\LandInformationBank\SADagAndKhatiyan\SADagRecordedPerson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\File;

class SADagKhatiyanController extends Controller
{
  
    use TraitsImageTraits;
    use UserDataTraits;

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cs_id'=> 'required',
            'sa_dag_no'=> 'required',
            'sa_khatiyan_no'=> 'required',
        ]);

        if ($validator->fails()){
            return response()->json([
                'status' => 'failed',
                'message' =>  $validator->messages()->all(),
            ]);
        }
        DB::beginTransaction();
        try{
            $saDagInfo = New SADagInfo();
            $saDagInfo->cs_id=$request->cs_id;
            $saDagInfo->sa_dag_no=$request->sa_dag_no;
            $saDagInfo->sa_khatiyan_no=$request->sa_khatiyan_no;
            $saDagInfo->total_sa_area=$request->total_sa_area;
            $saDagInfo->total_sa_use_area=$request->total_sa_use_area;
            $saDagInfo->sa_porca_scan_copy   = $this->getImageUrl($request->file('sa_porca_scan_copy')   ?? null,'landInformationBank/sadag&khatiyan/image/porcaScan/');
            $saDagInfo->sa_dag_map_scan_copy = $this->getImageUrl($request->file('sa_dag_map_scan_copy') ?? null,'landInformationBank/sadag&khatiyan/image/dagMapScan/');
            $saDagInfo->save();

            $recordedPersonInfo=array();
            $recorded_person=$request->recorded_person;
            if(is_array($recorded_person)){
                foreach($recorded_person as $personData){
                 
                    $person = array(
                        'sa_recorded_person'=>$personData['sa_recorded_person'],
                        'sa_recorded_person_fathers_name'=>$personData['sa_recorded_person_fathers_name'],
                        'sa_recorded_person_ownership_size'=>$personData['sa_recorded_person_ownership_size'],
                    );
    
                    $personInfo=SADagRecordedPerson::create($person);
                    array_push($recordedPersonInfo,$personInfo);
                }
            }
            
            // set relation between sa dag information and sa recorded people
            foreach($recordedPersonInfo as $person){
                $SADagRecordedPersonRelation= new SADagRecordedPersonRelation();
                $SADagRecordedPersonRelation->SADagInfoId =  $saDagInfo->id;
                $SADagRecordedPersonRelation->SADagRecordedPeopleId = $person->id;
                $SADagRecordedPersonRelation->save();
            }
            DB::commit();
            return response()->json([
                'status'=>'success',
                'message' => 'SA DAG INFO information added succesfully',
                'sa_info_data'=>$saDagInfo,
            ]);
        }
        catch(\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'failed',
                'message' => 'SA DAG INFO information couldn\'t be loaded',
                'msg' => $e->getMessage()
            ]);
        }
    }

    // public function view()
    // {
    //     $saDagInfoData = SADagInfo::with([
    //         'saRecordedPerson',
    //         'csInfo' => function ($query) {
    //             $query->with([
    //                 'csRecordedPerson',
    //                 'mouzaInfo' => function ($query) {
    //                     $query->with(['projectInfo']);
    //                 }
    //             ]);
    //         }
    //     ])->get();

    //     return response()->json([
    //         "data" => $saDagInfoData
    //     ], 200);
    // }
    public function view(){
        $user = Auth::user();
        $data = $this->getUserData($user);
        
        $userData    = $data['userData'];
        $superAdmin  = $data['superAdmin'];
        $admin       = $data['admin'];

      $saDagInfoData=[];

        if(!$superAdmin && $admin && isset($userData)){
            foreach($userData as $project_id){
                $datas = SADagInfo::with([
                            'saRecordedPerson',
                            'csInfo' => function ($query) {
                                $query->with([
                                    'csRecordedPerson',
                                    'mouzaInfo' => function ($query) {
                                        $query->with(['projectInfo']);
                                    }
                                ]);
                            }
                        ])->whereHas('csInfo.mouzaInfo.projectInfo',function ($query) use ($project_id) 
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
        $saDagInfoData = SADagInfo::with([
            'saRecordedPerson',
            'csInfo' => function ($query) {
                $query->with([
                    'csRecordedPerson',
                    'mouzaInfo' => function ($query) {
                        $query->with(['projectInfo']);
                    }
                ]);
            }
        ])->get();

        return response()->json([
            "data" => $saDagInfoData
        ], 200);;
        }
        
    }

    public function update(Request $request)
    {
        try {
            $data = SADagInfo::with([
                'saRecordedPerson',
                'csInfo' => function ($query) {
                $query->with([
                    'csRecordedPerson',
                    'mouzaInfo'=>function($query){
                    $query->with(['projectInfo']);
                    }]);
            }
            ])->findOrFail($request->id);
            $saRecordedPersonData = $data->saRecordedPerson;

            $data->cs_id = $request->cs_id;
            $data->sa_dag_no = $request->sa_dag_no;
            $data->sa_khatiyan_no = $request->sa_khatiyan_no;
            $data->total_sa_area = $request->total_sa_area;
            $data->total_sa_use_area = $request->total_sa_use_area;
            if ($request->hasFile('sa_porca_scan_copy')) {

                if(file_exists(public_path($data->sa_porca_scan_copy)) && isset($data->sa_porca_scan_copy)){
                    File::delete(public_path( $data->sa_porca_scan_copy));
                }
                
                $data->sa_porca_scan_copy=$this->getImageUrl($request->file('sa_porca_scan_copy') ?? $data->sa_porca_scan_copy,'landInformationBank/sadag&khatiyan/image/porcaScan/');        

            }

            if ($request->hasFile('sa_dag_map_scan_copy')) {
                if(file_exists(public_path($data->sa_dag_map_scan_copy)) && isset($data->sa_dag_map_scan_copy)){
                    File::delete(public_path( $data->sa_dag_map_scan_copy));
                }
                
                $data->sa_dag_map_scan_copy=$this->getImageUrl($request->file('sa_dag_map_scan_copy') ?? $data->sa_dag_map_scan_copy,'landInformationBank/sadag&khatiyan/image/dagMapScan/');
        
            }
    
            $data->save();

            $createSARecordedPersonInfo = []; //array to store create SA recorded person info
            $updateSARecordedPersonInfo = []; //array to store update SA recorded person info

            $recorded_person = $request->recorded_person;
            if(is_array($recorded_person)) 
            {
                foreach($recorded_person  as $person){
                    if(isset($person['id'])){
                        $person_id = $person['id'];
                        $foundPerson =  $saRecordedPersonData->find($person['id']);
                        $foundPerson->update([
                            'sa_recorded_person'=> $person['sa_recorded_person'],
                            'sa_recorded_person_fathers_name'=> $person['sa_recorded_person_fathers_name'],
                            'sa_recorded_person_ownership_size'=> $person['sa_recorded_person_ownership_size'],
                        ]);
    
                        $updateSARecordedPersonInfo[] = $foundPerson;
    
                    }else {
                        //create new sa_person
                        $personsData = [
                            'sa_recorded_person' => $person['sa_recorded_person'],
                            'sa_recorded_person_fathers_name' => $person['sa_recorded_person_fathers_name'],
                            'sa_recorded_person_ownership_size' => $person['sa_recorded_person_ownership_size']
                        ];
                        $personInfo = SADagRecordedPerson::create($personsData);
                        if ($personInfo) {
                            $createSARecordedPersonInfo[] = $personInfo;
                        }
                        $SADagRecordedPersonRelation= new SADagRecordedPersonRelation();
                        foreach($createSARecordedPersonInfo as $person){
                            $SADagRecordedPersonRelation->SADagRecordedPeopleId = $person->id;
                            $SADagRecordedPersonRelation->SADagInfoId =  $request->id;
                            $SADagRecordedPersonRelation->save();
                        }
                    }
                }
            }
         

            return response()->json([
                "message" => "success",
                "update" => $updateSARecordedPersonInfo,
                "create" => $createSARecordedPersonInfo,
                "data" => $data
            ], 200);

        } catch (\Exception $e) {
            return response([
                'status' => 'error',
                'message' => 'SA Dag and Khatiyan information update failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
