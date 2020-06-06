<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\ConfigurationComment;
use Illuminate\Http\Request;

/**
 * @resource v2 Configuration-comment
 *
 * Api for Configuration-comment
 */
            

class ConfigurationCommentController  extends Controller
{
    //


    /**
     * GET v2/configuration-comment
     *
     * Cho phép trả về danh sách các bản ghi cơ sở dữ liệu.
     *
     * Bạn cần lưu ý rằng hệ thộng mặc định chỉ trả về 30 bản ghi, nếu muốn lấy nhiều hơn bạn có thể truyền thêm các tham số.
     *
     * ### Thông tin cơ bản:
     * Info route | Description
     * --------- | -------
     * `version_route` | Version v2
     * `controller` | ConfigurationCommentController
     * `route_name` | configuration-comment
     *
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Default | Mô tả chi tiết
     * --------- | ------- | -------
     * `@fields` | NULL | Danh sách các trường dữ liệu configuration-comment ví dụ (id,name,v..v.v..), nếu không truyền tham số này thì sẽ trả về 1 số trường mặc định.
     * `@orderby` | NULL | Trường ưu tiên sắp xếp trong configuration-comment ví dụ (date), nếu không truyền tham số này thì sẽ sắp xếp mặc định.
     * `@limit` | NULL | Số lượng bản ghi configuration-comment cần lấy ra, nếu không truyền tham số này thì sẽ mặc định là 30 bản ghi.
     * `@page` | NULL | Số thứ tự trang configuration-comment cần lấy ra, nếu không truyền tham số này thì sẽ mặc định là trang 1.
     *
     * @return \Illuminate\Http\Response
     */
    function index(Request $request){
        $this->validate($request, [
            'fields' => 'string|min:1|max:255',
            'page' => 'numeric|min:1',
            'limit' => 'numeric|min:1',
            'where' => 'string|max:255|min:3',
        ]);
        $config_comment = new ConfigurationComment();
        $config_comment =  $config_comment->select($config_comment->alias($this->fields));
        if ($this->where != null) $config_comment = whereRawQueryBuilder($this->where, $config_comment, 'mysql', 'configuration_comment');
        $config_comment  = $config_comment->get();
        return $this->setResponse(200,$config_comment);
    }
     
     /**
     * GET v2/configuration-comment
     *
     * Mô tả chức năng hàm
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@id` | ID configuration-comment
     * `@fields` | List fields configuration-comment
     * @return \Illuminate\Http\Response
     */
    function show($id){
        $config = ConfigurationComment::find($id);
        return $this->setResponse(200,$config);
    }

    
     /**
     * PUT v2/configuration-comment
     *
     * Mô tả chức năng hàm
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@id` | ID configuration-comment
     * `@fields` | List fields configuration-comment
     * @return \Illuminate\Http\Response
     */
    function update(Request $request,$id){
        $this->validate($request, [
            'start' => 'required|numeric|min:1|max:10',
            'content' => 'required',
            'active' => 'numeric'
        ]);
        $config = ConfigurationComment::find($id);
        $config->com_star = $request->start;
        $config->com_content = json_encode($request->input('content'));
        $config->com_active = $request->active;
        if($config->save()){
            return $this->setResponse(200,'Saved');
        }
        return response('Save error!',500);
    }
        
    
     /**
     * POST v2/configuration-comment
     *
     * Mô tả chức năng hàm
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@id` | ID configuration-comment
     * `@fields` | List fields configuration-comment
     * @return \Illuminate\Http\Response
     */
    function store(Request $request){
        if($request->input('type')=='multi_insert'){
            $data = $request->input('data_config');
            foreach ($data as $key=>$item){
                $config = ConfigurationComment::where('com_star',$key+1)->first();
                if($config){
                    $config->com_content = json_encode($item);
                    $config->save();
                }else{
                    $config =  new ConfigurationComment();
                    $config->com_star = $key+1;
                    $config->com_content = json_encode($item);
                    $config->com_active = 1;
                    $config->save();
                }
            }
            return $this->setResponse(200,'Saved');
        }else{
            $this->validate($request, [
                'start' => 'required|numeric|min:1|max:10',
                'content' => 'required',
                'active' => 'numeric'
            ]);
            $config = new ConfigurationComment();
            $config->com_star = $request->start;
            $config->com_content = json_encode($request->input('content'));
            $config->com_active = $request->active;
            if($config->save()){
                return $this->setResponse(200,'Saved');
            }
            return response('Save error!',500);
        }


    }

        
}
