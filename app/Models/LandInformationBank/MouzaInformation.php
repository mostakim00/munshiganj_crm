<?php

namespace App\Models\LandInformationBank;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MouzaInformation extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function projectInfo()
    {
        return $this->hasOne(ProjectInformation::class, 'id','project_id' );
    }
}
