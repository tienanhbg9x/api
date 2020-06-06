<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Theme extends Model
{
    protected $table = 'themes';
    protected $primaryKey = 'thm_id';
    public $timestamps = false;
    /**
     * @var bool (optional) default false
     */
    protected $mappedOnly = true;
    /**
     * @var array (optional) specifies columns to alias
     */
    var $fillable = [
        'thm_id' => 'id',
        'thm_name'=>'name',
        'thm_html_path'=>'html_path',
        'thm_project_id'=>'project_id',
        'thm_source'=>'source',
        'thm_active'=>'active',
        'thm_js_extend'=>'js_extend',
        'thm_css_extend'=>'css_extend',
        'thm_cat_id'=>'cat_id',
        'thm_price' => 'price',
        'thm_thumbnail'=>'thumbnail',
        'thm_url'=>'url',
        'thm_created_at'=>'created_at'
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
