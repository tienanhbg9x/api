<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 10/5/18
 * Time: 14:25
 */

namespace App\Transformers;


class CategoryWithChildsTransformer extends CategoryTransformer
{

    public $defaultIncludes = [
        'childs'
    ];
}