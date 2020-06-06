<?php

namespace App\Models;

use VatGia\Model\Model;

class ClassifiedVip extends Model
{
//    protected $connection = 'mysql';
    protected $table = 'classifieds_vip';
    protected $primaryKey = 'clv_id';
    public $timestamps = false;

    /**
     * @var bool (optional) default false
     */
    protected $mappedOnly = true;


    /**
     * @var array (optional) specifies columns to alias
     */
    public $fillable = [
        'clv_id' => 'id',
        'clv_cla_id'=>'cla_id',
        'clv_date'=>'date',
        'clv_type_vip'=>'type_vip'
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
