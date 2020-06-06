<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Money extends Model
{
    protected $table = 'money';
    protected $primaryKey = 'mon_id';
    const CREATED_AT = 'mon_created_at';
    const UPDATED_AT = 'mon_updated_at';

    var $fillable = [
        'mon_id' => 'id',
        'mon_user_id'=>'user_id',
        'mon_count'=>"count",
        'mon_created_at' => 'created_at',
        'mon_updated_at'=>'updated_at',
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
