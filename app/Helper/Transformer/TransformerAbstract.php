<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 10/2/18
 * Time: 09:02
 */

namespace App\Helper\Transformer;


abstract class TransformerAbstract extends \League\Fractal\TransformerAbstract
{

    protected $fields = [];

    /**
     * TransformerAbstract constructor.
     * @param mixed $fields
     */
    public function __construct($fields = '')
    {
        if ($fields) {
            if (is_string($fields)) {
                $fields = explode(',', $fields);
            }

            if (is_array($fields)) {
                $this->fields = array_map('trim', $fields);
            }
        }
    }

    public function filterFromFields($data)
    {
        $fields = $this->fields;
        if (empty($fields)) {
            return $data;
        }

        return array_filter($data, function ($value, $key) use ($fields) {
            return (bool)in_array($key, $fields);
        }, ARRAY_FILTER_USE_BOTH);
    }

}