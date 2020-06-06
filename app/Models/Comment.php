<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $table = 'comments';
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
        'com_add_id' => 'add_id',
        'com_add_cit_id'=>'add_cit_id',
        'com_add_dis_id'=>'add_dis_id',
        'com_add_ward_id'=>'add_ward_id',
        'com_add_street_id'=>'add_street_id',
        'com_date' => 'date',
        'com_active' => 'active',
        'com_user_id' => 'user_id',
        'com_user_name' => 'user_name',
        'com_cla_id' => 'cla_id'
    ];

    public function getFullNameAttribute()
    {
        return "{$this->add_pre} {$this->add_name}";
    }

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
