<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserNotify extends Model
{
    protected $table = 'user_notify';
    protected $primaryKey = 'usn_id';
    public $timestamps = false;

    /**
     * @var bool (optional) default false
     */
    protected $mappedOnly = true;
    /**
     * @var array (optional) specifies columns to alias
     */
    public  $fillable = [
        'usn_id' => 'id',
        'usn_use_id' => 'use_id',
        'usn_create_use_id' => 'create_use_id',
        'usn_name' => 'name',
        'usn_email_cc' => 'email_cc',
        'usn_email' => 'email',
        'usn_content' => 'content',
        'usn_subject' => 'subject',
        'usn_status'=>'status',
        'usn_created_at'=>'created_at',
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
