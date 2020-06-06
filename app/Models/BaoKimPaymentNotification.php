<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BaoKimPaymentNotification extends Model
{
    protected $table = 'baokim_payment_notification';
    protected $primaryKey = 'bkp_id';
    protected $mappedOnly = true;
    const CREATED_AT = 'bkp_created_at';
    const UPDATED_AT = 'bkp_updated_at';

    var $fillable = [
        'bkp_id' => 'id',
        'bkp_user_id'=>'user_id',
        'bkp_bank_fee'=>"bank_fee",
        'bkp_created_on'=>'created_on',
        'bkp_customer_account_id'=>'customer_account_id',
        'bkp_customer_email'=>'customer_email',
        'bkp_customer_name'=>'customer_name',
        'bkp_customer_phone'=>'customer_phone',
        'bkp_fee_amount'=>'fee_amount',
        'bkp_from_fee'=>'from_fee',
        'bkp_merchant_email'=>'merchant_email',
        'bkp_merchant_id'=>'merchant_id',
        'bkp_merchant_name'=>'merchant_name',
        'bkp_merchant_phone'=>'merchant_phone',
        'bkp_net_amount'=>'net_amount',
        'bkp_order_amount'=>'order_amount',
        'bkp_order_id'=>'order_id',
        'bkp_payment_type'=>'payment_type',
        'bkp_to_fee'=>'to_fee',
        'bkp_total_amount'=>'total_amount',
        'bkp_transaction_id'=>'transaction_id',
        'bkp_transaction_status'=>'transaction_status',
        'bkp_usd_vnd_exchange_rate'=>'usd_vnd_exchange_rate',
        'bkp_created_at' => 'created_at',
        'bkp_updated_at'=>'updated_at',
    ];

    public function alias($fields = null)
    {
        $newFields = [];
        if ($fields == "*" || empty($fields)) {
            foreach ($this->fillable as $field => $alias) {
                $newFields[] = $field . " AS " . $alias;
            }
        }
        $fields = explode(",", $fields);
        foreach ($fields as $alias) {
            $field = array_search($alias, $this->fillable);
            if (!empty($field)) $newFields[] = $field . " AS " . $alias;
        }
        return $newFields;
    }
}
