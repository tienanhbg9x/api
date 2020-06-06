<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserTheme extends Model
{
    protected $table = 'user_themes';
    protected $primaryKey = 'uth_id';
    public $timestamps = false;
    /**
     * @var bool (optional) default false
     */
    protected $mappedOnly = true;
    /**
     * @var array (optional) specifies columns to alias
     */
    var $fillable = [
        'uth_id' => 'id',
        'uth_name' => 'name',
        'uth_rewrite' => 'rewrite',
        'uth_html_path' => 'html_path',
        'uth_thumbnail'=>'thumbnail',
        'uth_seo_content' => 'seo_content',
        'uth_active' => 'active',
        'uth_user_id' => 'user_id',
        'uth_rew_md5'=>'rew_md5',
        'uth_created_at'=>'created_at',
        'uth_deleted_at'=>'deleted_at'
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
