<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserContact extends Model
{
    protected $table = 'user_contacts';
    protected $primaryKey = 'usc_id';
    public $timestamps = false;

    var $fillable = [
        'usc_id' => 'id',
        'usc_user_id' => 'user_id',
        'usc_guest_id'=>'guest_id',
        'usc_rew_id'=>'rew_id',
        'usc_cat_id'=>'cat_id',
        'usc_cit_id'=>'cit_id',
        'usc_dis_id'=>'dis_id',
        'usc_ward_id'=>'ward_id',
        'usc_proj_id'=>'proj_id',
        'usc_user_phone'=>'user_phone',
        'usc_status'=>'status',
        'usc_content'=>'content',
        'usc_date'=>'date',
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
