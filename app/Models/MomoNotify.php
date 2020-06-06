<?php
/**
 * Created by PhpStorm.
 * User: minhngoc
 * Date: 25/10/2019
 * Time: 10:05
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MomoNotify extends Model
{
    protected $table = 'momo_notify';
    protected $primaryKey = 'id';
    public $timestamps = false;

}