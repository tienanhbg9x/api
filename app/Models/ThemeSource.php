<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThemeSource extends Model
{
    protected $table = 'theme_source';
    protected $primaryKey = 'ths_id';
    public $timestamps = false;
    /**
     * @var bool (optional) default false
     */
    protected $mappedOnly = true;
    /**
     * @var array (optional) specifies columns to alias
     */
    var $fillable = [
        'ths_id' => 'id',
        'ths_name'=>'name',
        'ths_path'=>'path',
        'ths_version'=>'version',
        'ths_type'=>'type',
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
    //
}
