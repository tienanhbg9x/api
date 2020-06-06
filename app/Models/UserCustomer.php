<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCustomer extends Model
{
    protected $table = 'user_customer';
    protected $primaryKey = 'ucm_id';
    public $timestamps = false;

    /**
     * @var bool (optional) default false
     */
    protected $mappedOnly = true;
    /**
     * @var array (optional) specifies columns to alias
     */
    public  $fillable = [
        'ucm_id' => 'id',
        'ucm_use_id' => 'use_id',
        'ucm_name' => 'name',
        'ucm_address' => 'address',
        'ucm_email' => 'email',
        'ucm_phone' => 'phone',
        'ucm_web' => 'web',
        'ucm_feature'=>'feature',
        'ucm_uth_id'=>'uth_id',
        'ucm_info'=>'info',
        'ucm_type'=>'type',
        'ucm_cla_id'=>'cla_id',
        'ucm_created_at' => 'created_at',
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
