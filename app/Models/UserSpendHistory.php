<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSpendHistory extends Model
{
    protected $table = 'user_spend_history';
    protected $primaryKey = 'ush_id';
    const CREATED_AT = 'ush_created_at';
    const UPDATED_AT = 'ush_updated_at';

    var $fillable = [
        'ush_id' => 'id',
        'ush_order_id'=>'order_id',
        'ush_user_id'=>'user_id',
        'ush_count'=>'count',
        'ush_message'=>'message',
        'ush_ip'=>'ip',
        'ush_status'=>'status',
        'ush_type' =>'type',
        'ush_user_agent'=>'user_agent',
        'ush_created_at' => 'created_at',
        'ush_updated_at'=>'updated_at',
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
