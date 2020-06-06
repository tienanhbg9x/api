<?php
/**
 * Created by Lê Đình Toản.
 * User: dinhtoan1905@gmail.com
 * Date: 4/6/2019
 * Time: 3:07 PM
 */
namespace App\Models;

use VatGia\Model\Model;


class GeoLocation extends Model
{
    protected $table = 'geolocation';
    protected $primaryKey = 'geo_id';
    public $timestamps = false;
    /**
     * @var bool (optional) default false
     */
    protected $mappedOnly = true;


    /**
     * @var array (optional) specifies columns to alias
     */
    var $fillable = [
        'geo_id' => 'id',
        'geo_title' => 'title',
        'geo_brand' => 'brand',
        'geo_address' => 'address',
        'geo_category' => 'category',
        'geo_rating' => 'rating',
        'geo_last_update' => 'last_update',
        'geo_lat' => 'lat',
        'geo_lng' => 'lng',
        'geo_cit_id' => 'cit_id',
        'geo_dis_id' => 'dis_id',
        'geo_ward_id' => 'ward_id',
        'geo_street_id' => 'street_id',
        'geo_citid' => 'citid',
        'geo_disid' => 'disid',
        'geo_wardid' => 'wardid',
        'geo_streetid' => 'streetid',
        'geo_proj_id' => 'proj_id',
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

    /**
     * Created by Lê Đình Toản
     * Hàm formart lại định dạng của địa chỉ coccoc
     * User: dinhtoan1905@gmail.com
     * Date: 4/6/2019
     * Time: 3:30 PM
     * @param $address
     */
    public function formatCoccocAddress($address){
        $arrayReplace = [
            "Q." => "Quận",
            "H." => "Huyện",
            "Tp." => "Thành phố",
            "P." => "Phường ",
            "T." => "Tỉnh ",
            "Tx." => "Thị xã",
            "Tt." => "Thị trấn",
            "Đ." => "Đường",
        ];
        foreach ($arrayReplace as $key => $value){
            $address = str_replace($key,$value,$address);
        }
        return $address;
    }


}