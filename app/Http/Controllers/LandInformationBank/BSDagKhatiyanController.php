<?php

namespace App\Http\Controllers\LandInformationBank;

use App\Http\Controllers\Controller;
use App\Http\Traits\imageTraits;
use App\Http\Traits\UserDataTraits;
use App\Models\LandInformationBank\BSDagAndKhatiyan\BSDagInfo;
use App\Models\LandInformationBank\BSDagAndKhatiyan\BSDagRecordedPerson;
use App\Models\LandInformationBank\BSDagAndKhatiyan\BSDagRecordedPersonRelation;
use App\Models\LandInformationBank\RSDagAndKhatiyan\RSDagInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BSDagKhatiyanController extends Controller
{
    use imageTraits;
    use UserDataTraits;
    
    public function store(Request $request){
        $validator = Validator::make($request->all(),[
            'recorded_person.*bs_recorded_person'=>'required',
            'recorded_person.*father_name'=>'required',
            'recorded_person.*ownership_size'=>'required',
            'rs_id'=>'required',
            'bs_dag_no'=>'required',
            'bs_khatiyan_no'=>'required'
        ]);


        if($validator->fails()){
            return response()->json([
                "status"=>"failed",
                "message"=> $validator->messages()->all(),
            ]);
        }
        DB::beginTransaction();
        // try{
            $bsDagInfo=New BSDagInfo();
            $bsDagInfo->rs_id=$request->rs_id;
            $bsDagInfo->bs_dag_no=$request->bs_dag_no;
            $bsDagInfo->bs_khatiyan_no=$request->bs_khatiyan_no;
            $bsDagInfo->total_bs_area=$request->total_bs_area;
            $bsDagInfo->total_bs_use_area=$request->total_bs_use_area;
            $bsDagInfo->bs_porca_scan_copy=$this->getImageUrl($request->file('bs_porca_scan_copy') ?? null,'landInformationBank/bsdag&khatiyan/image/porcaScan/');
            $bsDagInfo->bs_dag_map_scan_copy=$this->getImageUrl($request->file('bs_dag_map_scan_copy') ?? null,'landInformationBank/bsdag&khatiyan/image/dagMapScan/');
            $bsDagInfo->description=$request->description ?? null;
            $bsDagInfo->save();

            $recordedPersaonInfo=array();
            foreach($request->recorded_person as $personData){
                $person=array(
                    'bs_recorded_person'=>$personData['bs_recorded_person'],
                    'bs_recorded_person_fathers_name'=>$personData['bs_recorded_person_fathers_name'],
                    'bs_recorded_person_ownership_size'=>$personData['bs_recorded_person_ownership_size'],
                );
                $bsPersonInfo=BSDagRecordedPerson::create($person);
                array_push($recordedPersaonInfo,$bsPersonInfo);
        }

         //set relation between bs dag information bs recorded people
         foreach($recordedPersaonInfo as $person){
            $bsRecordedPersonRelation= new BSDagRecordedPersonRelation();
            $bsRecordedPersonRelation->BSDagInfoId= $bsDagInfo->id;
            $bsRecordedPersonRelation->BSDagRecordedPeopleId=$person->id;
            $bsRecordedPersonRelation->save();
        }
        DB::commit();
        return response()->json([
            'status'=>'success',
            'message' => 'BS DAG INFO information added succesfully',
            'bs_info_data'=>$bsDagInfo,
            'recorded_person_info_data'=>$recordedPersaonInfo,
        ],200);

        // }
        // catch(\Exception $e)
        // {
        //     DB::rollback();
        //     return response()->json([
        //         'status' => 'failed',
        //         'message' => 'BS DAG INFO information couldn\'t be loaded',
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
    
      $bsDagInfoData=[];
    
        if(!$superAdmin && $admin && isset($userData)){
            foreach($userData as $project_id){
                $datas = BSDagInfo::with([
                    'bsRecordedPerson',
                    'rsInfo'=>function($query){
                        $query->with([
                            'rsRecordedPerson',
                            'saInfo'=>function($query){
                                $query->with([
                                    'saRecordedPerson',
                                    'csInfo'=>function($query){
                                        $query->with([
                                            'csRecordedPerson',
                                            'mouzaInfo'=>function($query){
                                                $query->with(['projectInfo']);
                                            }
        
        
                                        ]);
                                    }
                                ]);
                            }
                        ]);
                    }
        
                ])->whereHas('rsInfo.saInfo.csInfo.mouzaInfo.projectInfo',function ($query) use ($project_id) 
                 {
                 $query->where('id', $project_id->project_id);
                 }
                 )->get();
                 if ($datas->isNotEmpty()) {
                array_push($bsDagInfoData,$datas);
                 }
            }
            return response()->json([
                'data' =>$bsDagInfoData
             ],200);
        }
    
       else if($superAdmin){
        $bsDagInfoData = BSDagInfo::with([
            'bsRecordedPerson',
            'rsInfo'=>function($query){
                $query->with([
                    'rsRecordedPerson',
                    'saInfo'=>function($query){
                        $query->with([
                            'saRecordedPerson',
                            'csInfo'=>function($query){
                                $query->with([
                                    'csRecordedPerson',
                                    'mouzaInfo'=>function($query){
                                        $query->with(['projectInfo']);
                                    }


                                ]);
                            }
                        ]);
                    }
                ]);
            }

        ])->get();
             return response()->json([
                'data' => $bsDagInfoData
             ],200);
        }
        
    }

    

       public function update(Request $request){
        try{
            $data = BSDagInfo::with(['bsRecordedPerson'])->findOrFail($request->id);

            $bsRecordedPersonData=$data->bsRecordedPerson;

            $data->rs_id=$request->rs_id;
            $data->bs_dag_no=$request->bs_dag_no;
            $data->bs_khatiyan_no=$request->bs_khatiyan_no;
            $data->total_bs_area=$request->total_bs_area;
            $data->total_bs_area=$request->total_bs_area;
            $data->description=$request->description ?? null;

            if ($request->hasFile('bs_porca_scan_copy')) {

                if(file_exists(public_path($data->bs_porca_scan_copy)) && isset($data->bs_porca_scan_copy)){
                    File::delete(public_path( $data->bs_porca_scan_copy));

                }
                
                    $data->bs_porca_scan_copy=$this->getImageUrl($request->file('bs_porca_scan_copy') ??  $data->bs_porca_scan_copy,'landInformationBank/bsdag&khatiyan/image/porcaScan/');
                

            }

            if ($request->hasFile('bs_dag_map_scan_copy')) {
                if(file_exists(public_path($data->bs_dag_map_scan_copy)) && isset($data->bs_dag_map_scan_copy)){
                    File::delete(public_path( $data->bs_dag_map_scan_copy));

                }
              
                    $data->bs_dag_map_scan_copy=$this->getImageUrl($request->file('bs_dag_map_scan_copy') ??  $data->bs_dag_map_scan_copy,'landInformationBank/bsdag&khatiyan/image/dagMapScan/');
               

            }
            $data->save();

            $createBsRecordedPersonInfo = [];
            $updaterBsRecordedPersonInfo = [];

            foreach($request->recorded_person as $person){

                if(isset($person['id'])){
                    $foundPerson= $bsRecordedPersonData->find($person['id']);
                    $foundPerson->update([
                        'bs_recorded_person'=> $person['bs_recorded_person'],
                        'bs_recorded_person_fathers_name'=> $person['bs_recorded_person_fathers_name'],
                        'bs_recorded_person_ownership_size'=> $person['bs_recorded_person_ownership_size'],
                    ]);
                    $updaterBsRecordedPersonInfo []=$foundPerson;
                }

                else{
                    //create new rs_person
                    $personsData = [
                        'bs_recorded_person' => $person['bs_recorded_person'],
                        'bs_recorded_person_fathers_name' => $person['bs_recorded_person_fathers_name'],
                        'bs_recorded_person_ownership_size' => $person['bs_recorded_person_ownership_size']
                    ];
                    $personInfo=BSDagRecordedPerson::create($personsData);
                    if($personInfo){
                        $createBsRecordedPersonInfo[]=$personInfo;
                    }

                $BSDagRecordedPersonRelation= new BSDagRecordedPersonRelation();
                foreach($createBsRecordedPersonInfo as $person){
                $BSDagRecordedPersonRelation->BSDagRecordedPeopleId = $person->id;
                $BSDagRecordedPersonRelation->BSDagInfoId =  $request->id;
                $BSDagRecordedPersonRelation->save();
            }
                }

            }
            return response()->json([
                "message"=>"success",
                "create"=> $createBsRecordedPersonInfo,
                "update"=> $updaterBsRecordedPersonInfo ,
                "data"=> $data,
            ],200);

        }
        catch (\Exception $e) {
            return response([
                'status' => 'failed',
                'message' => 'BS information update failed',
                'error' => $e->getMessage(),
            ], 500);
        }
       }

}
