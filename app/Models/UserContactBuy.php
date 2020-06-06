<?php
/**
 * Created by PhpStorm.
 * User: minhngoc
 * Date: 23/08/2019
 * Time: 14:39
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserContactBuy extends Model
{
    protected $table = 'user_contacts_buy';
    protected $primaryKey = 'ucb_id';
    public $timestamps = false;


    /**
     * @var bool (optional) default false
     */
    protected $mappedOnly = true;
    /**
     * @var array (optional) specifies columns to alias
     */
    public  $fillable = [
        'ucb_id' => 'id',
        'ucb_usc_id' => 'usc_id',
        'ucb_use_id' => 'use_id'
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