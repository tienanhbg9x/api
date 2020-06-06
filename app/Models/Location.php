<?php

namespace App\Models;

use VatGia\Model\Model;

class Location extends Model
{
//    protected $connection ='mysql';
    protected $table = 'location';
    protected $primaryKey = 'loc_id';
    public $timestamps = false;

    /**
     * @var bool (optional) default false
     */
    protected $mappedOnly = true;


    /**
     * @var array (optional) specifies columns to alias
     */
    var $fillable = [
        'loc_id' => 'id',
        'loc_name' => 'name',
        'loc_address'=>'address',
        'loc_cit_name'=>'cit_name',
        'loc_dis_name'=>'dis_name',
        'loc_pre'=>'pre',
        'loc_ward_name'=>'ward_name',
        'loc_street_name'=>'street_name',
        'loc_rewrite'=>'rewrite',
        'loc_code'=>'code',
        'loc_keyword'=>'keyword',
        'loc_type'=>'type',
        'loc_order' =>'order',
        'loc_citid'=>'citid',
        'loc_disid'=>'disid',
        'loc_wardid'=>'wardid',
        'loc_streetid'=>'streetid',
        'loc_cit_id'=>'cit_id',
        'loc_dis_id'=>'dis_id',
        'loc_ward_id'=>'ward_id',
        'loc_street_id'=>'street_id',
        'loc_add_id'=>'add_id',
        'loc_lat'=>'lat',
        'loc_lng'=>'lng',
        'loc_update'=>'update',
        'loc_picture'=>'picture',
        'loc_rsync_address'=>'rsync_address'
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
