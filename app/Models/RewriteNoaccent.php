<?php

namespace App\Models;

use VatGia\Model\Model;

class RewriteNoaccent extends Model
{
//    protected $connection ='mysql';
    protected $table = 'rewrites_noaccent';
    protected $primaryKey = 'rew_id';
    public $timestamps = false;
    /**
     * @var bool (optional) default false
     */
    protected $mappedOnly = true;


    /**
     * @var array (optional) specifies columns to alias
     */
    var $fillable = [
        'rew_id' => 'id',
        'rew_title' => 'title',
        'rew_rewrite'=>'rewrite',
        'rew_md5' =>'md5',
        'rew_param' => 'param',
        'rew_keyword' => 'keyword',
        'rew_date' => 'date',
        'rew_table' => 'table',
        'rew_id_value' => 'id_value',
        'rew_count_word' =>'count_word',
        'rew_length' => 'length',
        'rew_cat_id'=>'cat_id',
        'rew_dis_id'=>'dis_id',
        'rew_cit_id'=>'cit_id',
        'rew_ward_id'=>'ward_id',
        'rew_street_id'=>'street_id',
        'rew_proj_id'=>'proj_id',
        'rew_total_result'=>'rew_total_result',
        'rew_parent_id'=>'rew_parent_id',
        'rew_what'=>'rew_what',
        'rew_where'=>'rew_where',
        'rew_all_parent'=>'rew_all_parent'
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

    public function createRewrite($title){
        return cleanRewriteNoAccent($title);
    }
    //
}
