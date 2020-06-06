<?php

namespace App\Models;

use VatGia\Model\Model;

class Project extends Model
{
//    protected $connection = 'mysql';
    protected $table = 'projects';
    protected $primaryKey = 'proj_id';
    public $timestamps = false;
    /**
     * @var bool (optional) default false
     */
    protected $mappedOnly = true;


    /**
     * @var array (optional) specifies columns to alias
     */
    var $fillable = [
        'proj_id' => 'id',
        'proj_name' => 'name',
        'proj_inv_name'=>'inv_name',
        'proj_inv_id' =>'inv_id',
        'proj_title' => 'title',
        'proj_rewrite' => 'rewrite',
        'proj_address' => 'address',
        'proj_picture' => 'picture',
        'proj_video'=>'video',
        'proj_distribution'=>'distribution',
        'proj_construction'=>'construction',
        'proj_price_from'=>'price_from',
        'proj_lat'=>'lat',
        'proj_lng'=>'lng',
        'proj_cats'=>'cats',
        'proj_active'=>'active',
        'proj_cron'=>'cron',
        'proj_size'=>'size',
        'proj_teaser' => 'teaser',
        'proj_cat_name' =>'cat_name',
        'proj_overview' => 'overview',
        'proj_cat_id'=>'cat_id',
        'proj_dis_id'=>'dis_id',
        'proj_cit_id'=>'cit_id',
        'proj_ward_id'=>'ward_id',
        'proj_street_id'=>'street_id',
        'proj_update'=>'update'
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
        $count_field = 0;
        foreach ($fields as $alias) {
            $field = array_search($alias, $this->fillable);
            if (!empty($field)){
                $newFields[] = $field . " AS " . $alias;
                $count_field++;
            }
        }
        if($count_field==0){
                foreach ($this->fillable as $field => $alias) {
                    $newFields[] = $field . " AS " . $alias;
                }
        }
        return $newFields;
    }
}
