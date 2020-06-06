<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\AccessToken;
use Illuminate\Http\Request;

/**
 * @resource v2 AccessToken
 *
 * Api for AccessToken
 */
            

class AccessTokenController 
{
    //

     
     /**
     * GET v2/accessToken
     *
     * Mô tả chức năng hàm
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@id` | ID accessToken
     * `@fields` | List fields accessToken
     * @return \Illuminate\Http\Response
     */
    function show($id){
    
    }
        
    
     /**
     * POST v2/accessToken
     *
     * Mô tả chức năng hàm
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@id` | ID accessToken
     * `@fields` | List fields accessToken
     * @return \Illuminate\Http\Response
     */
    function store(Request $request){
        return (new AccessToken())->create();
    }
        
}
