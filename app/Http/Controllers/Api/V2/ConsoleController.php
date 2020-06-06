<?php

namespace App\Http\Controllers\Api\V2;

use Illuminate\Http\Request;

/**
 * @resource V2 Command Console
 *
 * Api for Console
 */
            

class ConsoleController 
{
    //

     
     /**
     * Command IndexAddress
     *
     * Lệnh index dữ liệu address vào elasticsearch
     * ### Thông tin lệnh:
     * ./artisan elastic:sync index_mapping address
     */
    function IndexAddress(){
        return false;
    }

    /**
     * Command CreateDocument
     *
     * Lệnh tạo document cho api
     * ### Thông tin lệnh:
     * ./artisan api:generate --router="dingo" --routePrefix="v2" --header="isdoc:1"
     */
    function CreateDocument(){
        return false;
    }

    /**
     * Command MapAllLocation
     *
     * Lệnh tạo document cho api
     * ### Thông tin lệnh:
     * ./artisan map:location all
     */
    function mapAllLocation(){
        return false;
    }


    /**
     * Command TestMapLocation
     *
     * Lệnh tạo document cho api
     * ### Thông tin lệnh:
     * ./artisan map:location test loc_id
     */
    function testMapLocation(){
        return false;
    }


    /**
     * Command IndexLocation
     *
     * Lệnh index dữ liệu address vào elasticsearch
     * ### Thông tin lệnh:
     * ./artisan elastic:sync index location
     */
    function IndexLocation(){
        return false;
    }

    /**
     * Command IndexRewrite
     *
     * Lệnh index dữ liệu rewrite vào elasticsearch
     * ### Thông tin lệnh:
     * ./artisan elastic:sync index rewrite
     */
    function IndexRewrite(){
        return false;
    }

    /**
     * Command IndexCategory
     *
     * Lệnh index dữ liệu categories vào elasticsearch
     * ### Thông tin lệnh:
     * ./artisan elastic:sync index categories_multi
     */
    function IndexCategories(){
        return false;
    }

    /**
     * Command IndexProject
     *
     * Lệnh index dữ liệu projects vào elasticsearch
     * ### Thông tin lệnh:
     * ./artisan elastic:sync index projects
     */
    function IndexProjects(){

    }
    /**
     * Command IndexClassifieds
     *
     * Lệnh index dữ liệu classifieds vào elasticsearch
     * ### Thông tin lệnh:
     * ./artisan elastic:sync index bds_classifieds
     */
    function IndexClassifieds(){

    }

    /**
     * Command Update Geolocation
     *
     * Lệnh cập nhật quận huyện tỉnh thành vào bảng geolocation để tìm theo vị trí
     * ### Thông tin lệnh:
     * ./artisan geolocation:update
     */
    function UpdateGeoLocation(){

    }
    /**
     * Command IndexGeoLocation
     *
     * Lệnh index dữ liệu geolocation vào elasticsearch
     * ### Thông tin lệnh:
     * ./artisan elastic:sync  index_mapping geolocation
     */
    function IndexGeoLocation(){

    }
}
