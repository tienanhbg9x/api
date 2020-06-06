<?php

namespace App\Models;

use VatGia\Model\Model;

class Investor extends Model
{
//    protected $connection = 'mysql';
    protected $table = 'investors';
    protected $primaryKey = 'inv_id';
    public $timestamps = false;

    /**
     * @var bool (optional) default false
     */
    protected $mappedOnly = true;


    /**
     * @var array (optional) specifies columns to alias
     */
    var $fillable = [
        'inv_id' => 'id',
        'inv_name' => 'name',
        'inv_rewrite' => 'rewrite',
        'inv_picture' => 'picture',
        'inv_has_picture' => 'has_picture',
        'inv_address' => 'address',
        'inv_phone' => 'phone',
        'inv_email' => 'email',
        'inv_website' => 'website',
        'inv_teaser' => 'teaser',
        'inv_description' => 'description',
        'inv_city_id'=>'city_id',
        'inv_dis_id'=>'dis_id',
        'inv_update'=>'update'
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
