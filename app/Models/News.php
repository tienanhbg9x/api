<?php

namespace App\Models;
use VatGia\Model\Model;

class News extends Model
{
//    protected $connection ='mysql';
    protected $table = 'news';
    protected $primaryKey = 'new_id';
    public $timestamps = false;

    /**
     * @var bool (optional) default false
     */
    protected $mappedOnly = true;
    /**
     * @var array (optional) specifies columns to alias
     */
    public  $fillable = [
        'new_id' => 'id',
        'new_cat_id' => 'cat_id',
        'new_cat_root_id'=>'cat_root_id',
        'new_use_id'=>'use_id',
        'new_title'=>'title',
        'new_rewrite'=>'rewrite',
        'new_meta_title'=>'meta_title',
        'new_meta_keyword'=>'meta_keyword',
        'new_meta_description'=>'meta_description',
        'new_tags'=>'tags',
        'new_picture'=>'picture',
        'new_teaser'=>'teaser',
        'new_description'=>'description',
        'new_time_create'=>'time_create',
        'new_time_update'=>'time_update',
        'new_status'=>'status',
        'new_citid'=>'citid',
        'new_disid'=>'disid',
        'new_wardid'=>'wardid',
        'new_streetid'=>'streetid'
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
