<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttributesV2 extends Model
{
    protected $table = 'attributes_v2';
    protected $primaryKey = 'att_id';
    public $timestamps = false;

    /**
     * @var bool (optional) default false
     */
    protected $mappedOnly = true;


    /**
     * @var array (optional) specifies columns to alias
     */
    var $fillable = [
        'att_id' => 'id',
        'att_name' => 'name',
        'att_active' => 'active',
        'att_parent_id' => 'parent_id',
        'att_all_child' => 'all_child',
        'att_sort' => 'sort',

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
