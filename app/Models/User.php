<?php

namespace App\Models;

use VatGia\Model\Model;

class User extends Model
{
//    protected $connection ='mysql';
    protected $table = 'users';
    protected $primaryKey = 'use_id';
    public $timestamps = false;

    var $fillable = [
        'use_id' => 'id',
        'use_id_vatgia' => 'id_vatgia',
        'use_active'=>'active',
        'use_login'=>'login',
        'use_loginname'=>'loginname',
        'use_first_name'=>'first_name',
        'use_last_name'=>'last_name',
        'use_fullname'=>'fullname',
        'use_birthdays'=>'birthdays',
        'use_yahoo'=>'yahoo',
        'use_gender'=>'gender',
        'use_city'=>'city',
        'use_state'=>'state',
        'use_zip_code'=>'zip_code',
        'use_phone'=>'phone',
        'use_fax'=>'fax',
        'use_email'=>'email',
        'use_address'=>'address',
        'use_date'=>'date',
        'use_web'=>'web',
        'use_group'=>'group',
        'use_name'=>'name',
        'use_mobile'=>'mobile',
        'use_content'=>'content',
        'use_admin'=>"admin",
        'use_staff'=>'staff',
        'use_hits'=>'hits',
        'use_key'=>'key',
        'use_avatar'=>'avatar',
        'use_fbid'=>'fbib',
        'use_rol'=>'rol',
        'use_picture'=>'picture',
        'use_email_payment' =>'email_payment'
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
