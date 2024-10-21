<?php

namespace App\Models\LandMutationBank;

use App\Models\LandInformationBank\SADagAndKhatiyan\SADagInfo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SAMutationInfo extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function saInfo()
    {
        return $this->hasOne(SADagInfo::class, 'id', 'sa_id');
    }
}
