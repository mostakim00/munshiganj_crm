<?php

namespace App\Models\LandSubDeedBank\SASubDeed;

use App\Models\LandInformationBank\SADagAndKhatiyan\SADagInfo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SASubDeedInfo extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function saInfo()
    {
        return $this->hasOne(SADagInfo::class, 'id', 'sa_id');
    }
    public function saSallerBuyer()
    {
        return $this->hasManyThrough(SASubDeedSellerBuyerList::class, SASubDeedSellerBuyerRelation::class, 'sa_sub_deed_id', 'id', 'id', 'sa_seller_buyer_id');
    }
}
