<?php

namespace App\Http\Controllers\Api\V2;

use Firebase\JWT\JWT;
use App\Models\Configuration;
use Illuminate\Http\Request;

/**
 * @resource v2 Configuration
 *
 * Api for Configuration
 */
            

class ConfigurationController extends Controller
{
    //

    
     /**
     * GET v2/configuration
     *
     * Cho phép trả về danh sách các bản ghi cơ sở dữ liệu.
     *
     * Bạn cần lưu ý rằng hệ thộng mặc định chỉ trả về 30 bản ghi, nếu muốn lấy nhiều hơn bạn có thể truyền thêm các tham số.
     *
     * ### Thông tin cơ bản:
     * Info route | Description
     * --------- | -------
     * `version_route` | Version v2
     * `controller` | ConfigurationController
     * `route_name` | configuration
     *
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Default | Mô tả chi tiết
     * --------- | ------- | -------
     * `@fields` | NULL | Danh sách các trường dữ liệu configuration ví dụ (id,name,v..v.v..), nếu không truyền tham số này thì sẽ trả về 1 số trường mặc định.
     * `@orderby` | NULL | Trường ưu tiên sắp xếp trong configuration ví dụ (date), nếu không truyền tham số này thì sẽ sắp xếp mặc định.
     * `@limit` | NULL | Số lượng bản ghi configuration cần lấy ra, nếu không truyền tham số này thì sẽ mặc định là 30 bản ghi.
     * `@page` | NULL | Số thứ tự trang configuration cần lấy ra, nếu không truyền tham số này thì sẽ mặc định là trang 1.
     *
     * @return \Illuminate\Http\Response
     */
    function index(){
        $config = config('configuration');

        if(strpos($this->fields,'client_id')!==false){

            $config['client_id']  = (int)substr(number_format(time() * rand(),0,'',''),0,10);
            $token = [
                "data" => [
                    "id" => $config['client_id'],
                    "active" => 1
                ],
            ];
            $config['client_token'] = JWT::encode($token, env('SECRET_FORM_TEMPLATE'));
        }
        return $this->setResponse(200,$config);
    }
        
}
