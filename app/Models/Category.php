<?php

namespace App\Models;


use VatGia\Model\Model;

class Category extends Model
{

    protected $table = 'categories_multi';
    protected $primaryKey = 'cat_id';
    public $timestamps = false;
    public $prefix = 'cat_';
    public $defaultFieldsSelect = ['cat_id', 'cat_parent_id', 'cat_has_child'];

    public function childs()
    {
        return $this->hasMany(
            static::class,
            'cat_parent_id',
            'cat_id'
        )->where('cat_active', 1);
    }

    /**
     * @var bool (optional) default false
     */
    protected $mappedOnly = true;


    /**
     * @var array (optional) specifies columns to alias
     */
    var $fillable = [
        'cat_id' => 'id',
        'cat_name' =>'name',
        'cat_name_guest'=>'name_guest',
        'cat_vg_id'=>'vg_id',
        'cat_seo_text'=>'seo_text',
        'cat_picture'=>'picture',
        'cat_icon'=>'icon',
        'cat_description'=>'description',
        'cat_meta_title'=>'meta_title',
        'cat_meta_description'=>'meta_description',
        'cat_meta_keyword'=>'meta_keyword',
        'cat_index_keyword'=>'index_keyword',
        'cat_order'=>'order',
        'cat_type'=>'type',
        'cat_active'=>'active',
        'cat_parent_id'=>'parent_id',
        'cat_has_child'=>'has_child',
        'cat_hot'=>'hot',
        'cat_all_child'=>'all_child',
        'cat_rewrite'=>'rewrite',
        'cat_count_word'=>'count_word',
        'cat_root'=>'root',
        'cat_cla_field_check'=>'cla_field_check',
        'cat_att_id'=>'att_id'
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
