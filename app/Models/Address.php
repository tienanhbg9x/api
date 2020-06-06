<?php

namespace App\Models;

use VatGia\Model\Model;
use DB;

class Address extends Model
{
//    protected $connection ='mysql';
    protected $table = 'address';
    protected $primaryKey = 'add_id';
    public $timestamps = false;

    /**
     * @var bool (optional) default false
     */
    protected $mappedOnly = true;


    /**
     * @var array (optional) specifies columns to alias
     */
    var $fillable = [
        'add_id' => 'id',
        'add_name' => 'name',
        'add_name_en' => 'name_en',
        'add_short_address'=>'short_address',
        'add_address'=>'address',
        'add_cit_name'=>'cit_name',
        'add_dis_name'=>'dis_name',
        'add_dis_pre'=>'dis_pre',
        'add_pre'=>'pre',
        'add_ward_name'=>'ward_name',
        'add_street_name'=>'street_name',
        'add_proj_name'=>'proj_name',
        'add_rewrite'=>'rewrite',
        'add_code'=>'code',
        'add_keyword'=>'keyword',
        'add_type'=>'type',
        'add_order' =>'order',
        'add_citid'=>'citid',
        'add_disid'=>'disid',
        'add_projid'=>'projid',
        'add_wardid'=>'wardid',
        'add_streetid'=>'streetid',
        'add_parent_id'=>'parent_id',
        'add_lat'=>'lat',
        'add_lng'=>'lng',
        'add_update'=>'update',
        'add_picture'=>'picture',
        'add_all_child'=>'all_child',
        'add_all_parent'=>'all_parent',
        'add_loc_id'=>'loc_id',
        'add_geometry' =>'geometry'
    ];

    public function getFullNameAttribute()
    {
        return "{$this->add_pre} {$this->add_name}";
    }

    public function alias($fields = null,$addField = null)
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
        if(!empty($addField)) $newFields[] = DB::raw($addField);
        return $newFields;
    }
    //
}
