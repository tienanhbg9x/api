<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassifiedData extends Model
{
    protected $table = 'classifieds_data';
    protected $primaryKey = 'cla_id';
    public $timestamps = false;

    /**
     * @var bool (optional) default false
     */
    protected $mappedOnly = true;
}
