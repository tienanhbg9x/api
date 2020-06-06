<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 10/1/18
 * Time: 12:36
 */

namespace App\Helper\Transformer;


class DataArraySerializer extends \League\Fractal\Serializer\DataArraySerializer
{
    /**
     * Serialize a collection.
     *
     * @param string $resourceKey
     * @param array $data
     *
     * @return array
     */
    public function collection($resourceKey, array $data)
    {

        return $data;

//        $data = array_filter_recursive($data, function ($value) {
//            return $value !== null;
//        });
//
//        return $data;
    }

    /**
     * Serialize an item.
     *
     * @param string $resourceKey
     * @param array $data
     *
     * @return array
     */
    public function item($resourceKey, array $data)
    {
        
        return $data;

//        return array_filter_recursive($data, function ($value) {
//            return $value !== null;
//        });
    }

    /**
     * Serialize null resource.
     *
     * @return array
     */
    public function null()
    {
        return [];
    }
}