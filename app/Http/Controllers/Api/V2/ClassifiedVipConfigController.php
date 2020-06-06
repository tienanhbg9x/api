<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\ClassifiedVipConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * @resource v2 Classified-vip-config
 *
 * Api for Classified-vip-config
 */
class ClassifiedVipConfigController extends Controller
{
    //


    /**
     * GET v2/classified-vip-config
     *
     * Cho phép trả về danh sách các bản ghi cơ sở dữ liệu.
     *
     * Bạn cần lưu ý rằng hệ thộng mặc định chỉ trả về 30 bản ghi, nếu muốn lấy nhiều hơn bạn có thể truyền thêm các tham số.
     *
     * ### Thông tin cơ bản:
     * Info route | Description
     * --------- | -------
     * `version_route` | Version v2
     * `controller` | ClassifiedVipConfigController
     * `route_name` | classified-vip-config
     *
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Default | Mô tả chi tiết
     * --------- | ------- | -------
     * `@fields` | NULL | Danh sách các trường dữ liệu classified-vip-config ví dụ (id,name,v..v.v..), nếu không truyền tham số này thì sẽ trả về 1 số trường mặc định.
     * `@orderby` | NULL | Trường ưu tiên sắp xếp trong classified-vip-config ví dụ (date), nếu không truyền tham số này thì sẽ sắp xếp mặc định.
     * `@limit` | NULL | Số lượng bản ghi classified-vip-config cần lấy ra, nếu không truyền tham số này thì sẽ mặc định là 30 bản ghi.
     * `@page` | NULL | Số thứ tự trang classified-vip-config cần lấy ra, nếu không truyền tham số này thì sẽ mặc định là trang 1.
     *
     * @return \Illuminate\Http\Response
     */
    function index(Request $request)
    {
        $this->validate($request, [
            'fields' => 'string|min:1|max:255',
            'where' => 'string|max:255|min:3',
//            'ssr' => 'boolean'
        ]);
        $classified_vip_config = new ClassifiedVipConfig();
        $classified_vip_config = $classified_vip_config->select($classified_vip_config->alias($this->fields));
        if ($request->input('permissions') == 'manage_data') {
            if (!checkAuth($request->input('access_token'))) {
                return $this->setResponse(401, null, 'Không có quyền thao tác!');
            }
        } else {
            $classified_vip_config = $classified_vip_config->where('cvc_active', 1);
        }
        if ($this->where != null) $classified_vip_config = whereRawQueryBuilder($this->where, $classified_vip_config, 'mysql', 'classified_vip_configuration');
        $classified_vip_config = $classified_vip_config->orderBy('cvc_type', 'desc')->get();
        return $this->setResponse(200, $classified_vip_config);
    }


    /**
     * GET v2/classified-vip-config
     *
     * Mô tả chức năng hàm
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@id` | ID classified-vip-config
     * `@fields` | List fields classified-vip-config
     * @return \Illuminate\Http\Response
     */
    function show(Request $request,$id)
    {
        $classified_vip_config = new ClassifiedVipConfig();
        $classified_vip_config = $classified_vip_config->select($classified_vip_config->alias($this->fields))->where('cvc_id',$id);
        if ($this->where != null) $classified_vip_config = whereRawQueryBuilder($this->where, $classified_vip_config, 'mysql', 'classified_vip_configuration');
        $classified_vip_config = $classified_vip_config->first();
        if($classified_vip_config){
            return $this->setResponse(200,$classified_vip_config);
        }
        return response($this->setResponse(404,null,'Not found id'),404);
    }


    /**
     * DELETE v2/classified-vip-config
     *
     * Mô tả chức năng hàm
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@id` | ID classified-vip-config
     * `@fields` | List fields classified-vip-config
     * @return \Illuminate\Http\Response
     */
    function destroy($id)
    {
        $classified_config = ClassifiedVipConfig::find($id);
        if($classified_config){
            $classified_config->delete();
            Cache::forget('cla_vip_config');
            return $this->setResponse(200,'Deleted !');
        }
          return response($this->setResponse(404,null,'Not found id'),404);
    }


    /**
     * PUT v2/classified-vip-config
     *
     * Mô tả chức năng hàm
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@id` | ID classified-vip-config
     * `@fields` | List fields classified-vip-config
     * @return \Illuminate\Http\Response
     */
    function update(Request $request, $id)
    {
        $classified_config = ClassifiedVipConfig::find($id);
        if($classified_config){
            if($request->input('time_start')&&$request->input('time_end')){
                $time_start = (int) $request->input('time_start');
                $time_end =  (int) $request->input('time_end');
                if($time_end<= $time_start){
                    return response($this->setResponse(500,null,'Time error'),500);
                }
                $classified_config->cvc_time_end =   $time_end;
                $classified_config->cvc_time_start =   $time_start;
            }
            if($request->input('price')){
                $classified_config->cvc_price =(int) $request->input('price');
            }
            if($request->input('active')!==null){
                $classified_config->cvc_active =(int) $request->input('active');
            }
            if($request->input('classified_limit')){
                $classified_config->cvc_classified_limit =(int) $request->input('classified_limit');
            }
            if($request->input('type')){
                $classified_config->cvc_type = trim($request->input('type'));
            }
            if($request->input('description')){
                $classified_config->cvc_description = trim($request->input('description'));
            }
            if($request->input('picture')){
                $classified_config->cvc_picture = $request->input('picture');
            }
            if($classified_config->save()){
                Cache::forget('cla_vip_config');
                return $this->setResponse(200,"Update");
            }
        }
        return response($this->setResponse(404,null,'Not found id'),404);
    }

    /**
     * POST v2/configuration_app
     *
     * Thêm dữ liệu vào database
     * @return \Illuminate\Http\Response
     */
    function store(Request $request)
    {
        $this->validate($request, [
            'classified_limit' => 'required|numeric',
            'price' => 'numeric|min:1',
            'time_end' => 'required|numeric|min:1',
            'time_start' => 'required|numeric',
            'type' => 'required|string|max:255|min:3',
            'description' => 'required|string|max:255|min:3',
            'picture'=> 'required|string|max:255|min:3'
        ]);
        $time_start = (int) $request->input('time_start');
        $time_end =  (int) $request->input('time_end');
        if($time_end<= $time_start){
          return response($this->setResponse(500,null,'Time error'),500);
        }
        $classified_config = new ClassifiedVipConfig();
        $classified_config->cvc_price = $request->input('price');
        $classified_config->cvc_time_start =  $time_start;
        $classified_config->cvc_time_end =   $time_end;
        $classified_config->cvc_classified_limit = $request->input('classified_limit');
        $classified_config->cvc_type = trim($request->input('type'));
        $classified_config->cvc_description = trim($request->input('description'));
        $classified_config->cvc_picture = $request->input('picture');
        if($classified_config->save()){
            Cache::forget('cla_vip_config');
            return $this->setResponse(200,"Saved");
        }
        return response($this->setResponse(500,null,'Save error'),500);

    }
}
