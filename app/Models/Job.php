<?php
/**
 * Created by PhpStorm.
 * User: minhngoc
 * Date: 24/06/2019
 * Time: 11:34
 */

namespace App\Models;
use VatGia\Model\Model;


class Job extends Model
{
    protected $table = 'jobs';
    protected $primaryKey = 'id';
    public $timestamps = false;
}