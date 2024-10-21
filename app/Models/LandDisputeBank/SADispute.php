<?php

namespace App\Models\LandDisputeBank;

use App\Models\LandInformationBank\SADagAndKhatiyan\SADagInfo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SADispute extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function saInfo()
    {
        return $this->hasOne(SADagInfo::class, 'id', 'sa_id');
    }
}
