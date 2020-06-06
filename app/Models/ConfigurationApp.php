<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfigurationApp extends Model
{
//    protected $connection ='mysql';
    protected $table = 'configuration_app';
    protected $primaryKey = 'coa_id';
    public $timestamps = false;

    /**
     * @var bool (optional) default false
     */
    protected $mappedOnly = true;


    /**
     * @var array (optional) specifies columns to alias
     */
    var $fillable = [
        'coa_id' => 'id',
        'coa_type' => 'type',
        'coa_key' => 'key',
        'coa_value' => 'coa_value'
    ];

    public function alias($fields = null,$addField = null)
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
        if(!empty($addField)) $newFields[] = DB::raw($addField);
        return $newFields;
    }
}
