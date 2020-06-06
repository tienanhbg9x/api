<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfigurationComment extends Model
{
    protected $table = 'configuration_comment';
    protected $primaryKey = 'com_id';
    public $timestamps = false;

    /**
     * @var bool (optional) default false
     */
    protected $mappedOnly = true;


    /**
     * @var array (optional) specifies columns to alias
     */
    var $fillable = [
        'com_id' => 'id',
        'com_star' => 'star',
        'com_content' => 'content',
        'com_active' => 'active'
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
