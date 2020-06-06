<?php

namespace App\Models;

use VatGia\Model\Model;

class ProjectDescription extends Model
{
//    protected $connection = 'mysql';
    protected $table = 'projects_description';
    protected $primaryKey = 'proj_proj_id';
    public $timestamps = false;
    /**
     * @var bool (optional) default false
     */
    protected $mappedOnly = true;


    /**
     * @var array (optional) specifies columns to alias
     */
    var $fillable = [
        'proj_proj_id' => 'id',
        'proj_intro' => 'intro',
        'proj_long_keyword' => 'long_keyword',
        'proj_position' => 'position',
        'proj_infrastructure' => 'infrastructure',
        'proj_template' => 'template',
        'proj_template_picture' => 'template_picture',
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
