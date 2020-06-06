<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserFavorite extends Model
{
    //
    protected $table = 'user_favorite';
    protected $primaryKey = 'usf_id';
    public $timestamps = false;

    var $fillable = [
        'usf_id' => 'id',
        'usf_use_id' => 'use_id',
        'usf_cla_id'=>'cla_id',
        'usf_date'=>'date'
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
