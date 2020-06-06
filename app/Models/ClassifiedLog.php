<?php

namespace App\Models;

use VatGia\Model\Model;

class ClassifiedLog extends Model
{
    //    protected $connection ='mysql';
    protected $table = 'classifieds_log';
    protected $primaryKey = 'id';
    public $timestamps = false;

    var $fillable = ['id','cla_id','cat_id','cit_id','dis_id','ward_id','street_id','proj_id','lat','lng','citid','disid','wardid','streetid','use_id','money_count','type_vip','created_at'];


    public function alias($fields = null)
    {
        $newFields = [];
        if ($fields == "*" || empty($fields)) {
            foreach ($this->fillable as $field ) {
                $newFields[] = $field;
            }
        }
        $fields = explode(",", $fields);
        foreach ($fields as $alias) {

            if (in_array($alias, $this->fillable)) $newFields[] = $alias;
        }
        return \DB::raw(implode(',',$newFields));
    }
}
