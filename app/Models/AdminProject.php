<?php

namespace App\Models;

use App\Models\LandInformationBank\ProjectInformation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminProject extends Model
{
    use HasFactory;
    protected $guarded=[];

    public function singleProjectInfo(){
        return $this->belongsTo(ProjectInformation::class,'project_id','id');
    }
}
