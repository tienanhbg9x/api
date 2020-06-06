<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassifiedVipConfig extends Model
{
    protected $table = 'classified_vip_configuration';
    protected $primaryKey = 'cvc_id';
    public $timestamps = false;

    /**
     * @var bool (optional) default false
     */
    protected $mappedOnly = true;


    /**
     * @var array (optional) specifies columns to alias
     */
    public $fillable = [
        'cvc_id' => 'id',
        'cvc_price'=>'price',
        'cvc_time_start'=>'time_start',
        'cvc_time_end'=>'time_end',
        'cvc_classified_limit'=>'classified_limit',
        'cvc_type'=>'type',
        'cvc_description'=>'description',
        'cvc_picture'=>'picture',
        'cvc_active' =>'active'
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
