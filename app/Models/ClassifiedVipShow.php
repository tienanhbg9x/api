<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassifiedVipShow extends Model
{
    protected $table = 'classified_vip_show';
    protected $primaryKey = 'cvs_id';
    public $timestamps = false;

    /**
     * @var bool (optional) default false
     */
    protected $mappedOnly = true;


    /**
     * @var array (optional) specifies columns to alias
     */
    public $fillable = [
        'cvs_id' => 'id',
        'cvs_date'=>'date',
        'cvs_cla_id'=>'cla_id',
        'cvs_cvc_id'=>'cvc_id',
        'cvs_count'=>'count'
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
