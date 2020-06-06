<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\AttributesV2;
use Illuminate\Http\Request;

/**
 * @resource v2 Attributes_v2
 *
 * Api for Attributes_v2
 */
class AttributesV2Controller extends Controller
{

    public $request = null;
    //


    /**
     * GET v2/attributes_v2
     *
     * Cho phép trả về danh sách các bản ghi cơ sở dữ liệu.
     *
     * Bạn cần lưu ý rằng hệ thộng mặc định chỉ trả về 30 bản ghi, nếu muốn lấy nhiều hơn bạn có thể truyền thêm các tham số.
     *
     * ### Thông tin cơ bản:
     * Info route | Description
     * --------- | -------
     * `version_route` | Version v2
     * `controller` | AttributesV2Controller
     * `route_name` | attributes_v2
     *
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Default | Mô tả chi tiết
     * --------- | ------- | -------
     * `@fields` | NULL | Danh sách các trường dữ liệu attributes_v2 ví dụ (id,name,v..v.v..), nếu không truyền tham số này thì sẽ trả về 1 số trường mặc định.
     * `@limit` | NULL | Số lượng bản ghi attributes_v2 cần lấy ra, nếu không truyền tham số này thì sẽ mặc định là 30 bản ghi.
     * `@page` | NULL | Số thứ tự trang attributes_v2 cần lấy ra, nếu không truyền tham số này thì sẽ mặc định là trang 1.
     * `@type` | null | Tùy chọn điều kiện lấy dữ liệu
     *
     * ### Tùy chọn với tham số `type`:
     *  Giá trị (Value) | Điều kiện | Mô tả chi tiết
     * --------- | ------- | -------
     * `group_attribute` |  null | Nhóm thuộc tính thành một nhóm có đặc điểm chung với nhau
     *
     * @return \Illuminate\Http\Response
     */
    function index(Request $request)
    {
        $this->validate($request, [
            'type' => 'string',
            'fiedls' => 'string|min:1|max:255',
            'page' => 'numeric|min:1',
            'limit' => 'numeric|min:1',
            'where' => 'string|max:255|min:3'
        ]);
        $this->request = $request;
        if($request->input('type')=='group_attribute'){
            return $this->groupAttribute();
        }
        $offset = $this->page * $this->limit - $this->limit;
        $attributes = new AttributesV2();
        $attributes = $attributes->select($attributes->alias($this->fields));
        if ($this->where != null) $attributes = whereRawQueryBuilder($this->where, $attributes, 'mysql', 'attributes_v2');
        $attributes = $attributes->orderBy('att_id', 'desc')->offset($offset)->limit($this->limit)->get();
        return $this->setResponse(200, $attributes);

    }


    function groupAttribute(){
        $attributes = new AttributesV2();
        $attributes = $attributes->select($attributes->alias($this->fields))->where('att_active',1)->get();
        $attributes = $attributes->groupBy('parent_id');
        $data_response = [];
        foreach ($attributes[0] as $parent_attribute){
        if(isset($attributes[$parent_attribute->id])){
            $parent_attribute['attributes_child'] = $attributes[$parent_attribute->id];
        }else{
            $parent_attribute['attributes_child'] = [];
        }
       array_push($data_response,$parent_attribute);
        }
        return $this->setResponse(200,$data_response);
    }


    /**
     * GET v2/attributes_v2
     *
     * Mô tả chức năng hàm
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@id` | ID attributes_v2
     * `@fields` | List fields attributes_v2
     * @return \Illuminate\Http\Response
     */
    function show($id)
    {

    }

}
