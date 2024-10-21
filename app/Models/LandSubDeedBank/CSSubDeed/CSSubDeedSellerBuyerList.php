<?php

namespace App\Models\LandSubDeedBank\CSSubDeed;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CSSubDeedSellerBuyerList extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function csSellerBuyerRelation()
    {
        return $this->hasOne(CSSubDeedSellerBuyerList::class, 'cs_seller_buyer_id', 'id');
    }
}
