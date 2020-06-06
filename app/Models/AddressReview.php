<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AddressReview extends Model
{
    protected $table = 'address_review';
    protected $primaryKey = 'adr_id';
    public $timestamps = false;

    /**
     * @var bool (optional) default false
     */
    protected $mappedOnly = true;


    /**
     * @var array (optional) specifies columns to alias
     */
    var $fillable = [
        'adr_id' => 'id',
        'adr_add_id' => 'add_id',
        'adr_total_star' => 'total_star',
        'adr_total_review' => 'total_review',
        'adr_star_first' => 'star_first',
        'adr_star_second' => 'star_second',
        'adr_star_third' => 'star_third',
        'adr_star_fourth' => 'star_fourth',
        'adr_star_fifth' => 'star_fifth'
    ];

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
