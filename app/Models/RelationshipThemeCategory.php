<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RelationshipThemeCategory extends Model
{
    //

    protected $table = 'relationship_theme_category';
    protected $primaryKey = 'rtc_id';
    public $timestamps = false;
    /**
     * @var bool (optional) default false
     */
    protected $mappedOnly = true;
    /**
     * @var array (optional) specifies columns to alias
     */
    var $fillable = [
        'rtc_id' => 'id',
        'rtc_cat_id' => 'cat_id',
        'rtc_theme_id' => 'theme_id',
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
