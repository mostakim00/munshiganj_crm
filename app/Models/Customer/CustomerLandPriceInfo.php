<?php

namespace App\Models\Customer;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerLandPriceInfo extends Model
{
    use HasFactory;
    protected $guarded=[];

    public static $BOOKING_MONEY = 1;
    public static $DOWNPAYMENT = 2;
    public static $INSTALLMENT = 3;

    public function downpayment()
    {
        return $this->hasMany(CustomerDownpayment::class, 'customer_land_price_infos_id', 'id');
    }

    public function installment()
    {
        return $this->hasMany(CustomerInstallment::class, 'customer_land_price_infos_id', 'id');
    }

    public function landInfo()
    {
        return $this->hasOne(CustomerLandInformation::class, 'id', 'customer_land_info_id');
    }

    public function booking_money_statement()
    {
        return $this->hasMany(PaymentStatement\CustomerPaymentStatementBookingMoney::class, 'customer_land_price_infos_id', 'id');
    }

    public function downpayment_statement()
    {
        return $this->hasMany(PaymentStatement\CustomerPaymentStatementDownpayment::class, 'customer_land_price_infos_id', 'id');
    }

    public function installment_statement()
    {
        return $this->hasMany(PaymentStatement\CustomerPaymentStatementInstallment::class, 'customer_land_price_infos_id', 'id');
    }

}