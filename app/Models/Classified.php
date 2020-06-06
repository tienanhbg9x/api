<?php

namespace App\Models;

use VatGia\Model\Model;

class Classified extends Model
{
//    protected $connection = 'mysql';
    protected $table = 'classifieds';
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
        'cla_title' => 'title',
        'cla_rewrite' => 'rewrite',
        'cla_cat_id'=>'cat_id',
        'cla_cit_id'=>'cit_id',
        'cla_dis_id' => 'dis_id',
        'cla_ward_id'=>'ward_id',
        'cla_street_id'=>'street_id',
        'cla_active'=>'active',
        'cla_proj_id'=>'proj_id',
        'cla_date' => 'date',
        'cla_expire'=>'cla_expire',
        'cla_use_id'=>'use_id',
        'cla_mobile'=>'mobile',
        'cla_description' => 'description',
        'cla_teaser'=>"teaser",
        'cla_price' => 'price',
        'cla_picture'=>'picture',
        'cla_list_acreage' => 'list_acreage',
        'cla_list_price'=>'list_price',
        'cla_list_badroom'=>'list_badroom',
        'cla_list_toilet'=>'list_toilet',
        'cla_vg_id'=>'vg_id',
        'cla_lat'=>'lat',
        'cla_lng'=>'lng',
        'cla_fields_check'=>'fields_check',
        'cla_search'=>'search',
        'cla_type'=>'type',
        'cla_has_picture'=>'has_picture',
        'cla_feature'=>'feature',
        'cla_type_cat'=>'type_cat',
        'cla_type_vip' =>'type_vip',
        'cla_address'=>'address',
        'cla_phone'=>'phone',
        'cla_email'=>'email',
        'cla_contact_name'=>'contact_name',
        'cla_rew_id'=>'rew_id',
        'cla_citid'=>'citid',
        'cla_disid'=>'disid',
        'cla_wardid'=>'wardid',
        'cla_streetid'=>'streetid',
        'cla_cat_prentid'=>'cat_prentid',
        'cla_has_video'=>'has_video',
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
