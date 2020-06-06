<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassifiedFilter extends Model
{
    protected $table = 'classifieds_filter';
    protected $primaryKey = 'cla_id';
    public $timestamps = false;

    /**
     * @var bool (optional) default false
     */
    protected $mappedOnly = true;


    /**
     * @var array (optional) specifies columns to alias
     */
    var $fillable = [
        'cla_id' => 'id',
        'cla_cat_id'=>'cat_id',
        'cla_cit_id'=>'cit_id',
        'cla_dis_id' => 'dis_id',
        'cla_ward_id'=>'ward_id',
        'cla_street_id'=>'street_id',
        'cla_active'=>'active',
        'cla_proj_id'=>'proj_id',
        'cla_date' => 'date',
        'cla_expire'=>'expire',
        'cla_use_id'=>'use_id',
        'cla_mobile'=>'mobile',
        'cla_price' => 'price',
        'cla_vg_id'=>'vg_id',
        'cla_lat'=>'lat',
        'cla_lng'=>'lng',
        'cla_fields_check'=>'fields_check',
        'cla_type'=>'type',
        'cla_type_cat'=>'type_cat',
        'cla_type_vip' =>'type_vip',
        'cla_has_picture'=>'has_picture',
        'cla_sort'=>'sort',
        'cla_has_video'=>'has_video'
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
