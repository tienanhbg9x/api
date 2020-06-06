<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QueuePayment extends Model
{
    protected $table = 'queue_payment';
    protected $primaryKey = 'qpm_id';
    public $timestamps = false;

    /**
     * @var bool (optional) default false
     */
    protected $mappedOnly = true;
    /**
     * @var array (optional) specifies columns to alias
     */
    public  $fillable = [
        'qpm_id' => 'id',
        'qpm_name' => 'name',
        'qpm_user_id' => 'user_id',
        'qpm_url' => 'url',
        'qpm_md5_url'=>'md5_url',
        'qpm_type' => 'type',
        'qpm_data'=>'data',
        'qpm_created_at' => 'created_at'
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
