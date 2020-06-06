<?php

namespace App\Models;

use VatGia\Model\Model;

class ProjectCompare extends Model
{
//    protected $connection = 'mysql';
    protected $table = 'projects_compare';
    protected $primaryKey = 'prc_id';
    public $timestamps = false;

    /**
     * @var bool (optional) default false
     */
    protected $mappedOnly = true;


    /**
     * @var array (optional) specifies columns to alias
     */
    var $fillable = [
        'prc_id' => 'id',
        'prc_min_id' => 'min_id',
        'prc_max_id' => 'max_id',
        'prc_meta_title' => 'meta_title',
        'prc_meta_description' => 'meta_description',
        'prc_keyword' => 'prc_keyword',
        'prc_title' => 'title',
        'prc_active'=>"active",
        'prc_cit_id' => 'cit_id',
        'prc_dis_id' => 'dis_id',
        'prc_ward_id' =>'ward_id',
        'prc_street_id'=>'street_id',
        'prc_picture'=>'picture',
        'prc_rewrite' =>'rewrite'
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
