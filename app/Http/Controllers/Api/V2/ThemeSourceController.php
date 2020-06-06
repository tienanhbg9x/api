<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\ThemeSource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * @resource v2 Theme-sources
 *
 * Api for Theme-sources
 */
            

class ThemeSourceController extends Controller
{
    //

    protected $arr_type = ['js', 'css', 'jpeg', 'jpg', 'png', 'woff', 'ttf', 'woff2', 'otf', 'gif', 'svg', 'webp', 'tiff','eot'];
     /**
     * GET v2/theme-sources
     *
     * Cho phép trả về danh sách các bản ghi cơ sở dữ liệu.
     *
     * Bạn cần lưu ý rằng hệ thộng mặc định chỉ trả về 30 bản ghi, nếu muốn lấy nhiều hơn bạn có thể truyền thêm các tham số.
     *
     * ### Thông tin cơ bản:
     * Info route | Description
     * --------- | -------
     * `version_route` | Version v2
     * `controller` | ThemeSourceController
     * `route_name` | theme-sources
     *
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Default | Mô tả chi tiết
     * --------- | ------- | -------
     * `@fields` | NULL | Danh sách các trường dữ liệu theme-sources ví dụ (id,name,v..v.v..), nếu không truyền tham số này thì sẽ trả về 1 số trường mặc định.
     * `@orderby` | NULL | Trường ưu tiên sắp xếp trong theme-sources ví dụ (date), nếu không truyền tham số này thì sẽ sắp xếp mặc định.
     * `@limit` | NULL | Số lượng bản ghi theme-sources cần lấy ra, nếu không truyền tham số này thì sẽ mặc định là 30 bản ghi.
     * `@page` | NULL | Số thứ tự trang theme-sources cần lấy ra, nếu không truyền tham số này thì sẽ mặc định là trang 1.
     *
     * @return \Illuminate\Http\Response
     */
    function index(Request $request){
        $this->validate($request, [
            'fiedls' => 'string|min:1|max:255',
            'page' => 'numeric|min:1',
            'limit' => 'numeric|min:1',
            'where' => 'string|max:255|min:3'
        ]);
        $offset = $this->page * $this->limit - $this->limit;
        $source = new ThemeSource();
        $source = $source->select($source->alias($this->fields));
        if ($this->where != null) $source = whereRawQueryBuilder($this->where, $source, 'mysql', 'theme_source');
        $source = $source->orderBy('ths_id', 'desc')->offset($offset)->limit($this->limit)->get();
        if(strpos($this->fields,'url')!==false){
            foreach ($source as $key=>$data){
                $source[$key]->url = config('app.source_path').'/'.$data->path;
            }
        }
        $data = [
            'total_record' => 1000,
            'current_page' => $this->page,
            'per_page' => $this->limit,
            'sources' => $source,

        ];
        return $this->setResponse(200, $data);
    }
        
     
     /**
     * GET v2/theme-sources/{id}
     *
     * Mô tả chức năng hàm
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@id` | ID theme-sources
     * `@fields` | List fields theme-sources
     * @return \Illuminate\Http\Response
     */
    function show($id){
    
    }
        
    
     /**
     * POST v2/theme-sources
     *
     * Thêm dữ liệu vào csdl
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@id` | ID theme-sources
     * `@fields` | List fields theme-sources
     * @return \Illuminate\Http\Response
     */
    function store(Request $request){
        $this->validate($request, [
            'file' => 'required',
            'version'=>'string|min:1|max:10'
        ]);
        try{
            $uploadedFile = $request->file('file');
            $name = $uploadedFile->getClientOriginalName();
            $exp_name = explode('.',$name);
            $type = isset($exp_name[count($exp_name)-1])?$exp_name[count($exp_name)-1]:null;
            if($type==null){
                return response($this->setResponse(500,null,'Error type file!'),500);
            }else if(!in_array($type,$this->arr_type)){
                return response($this->setResponse(500,null,'Error type file!'),500);
            }
            $folder_save = 'source/'.$type;
            if(Storage::has($folder_save.'/'.$name)){
                return response($this->setResponse(500,null,'File existed!'),500);
            }
            Storage::disk('local')->putFileAs(
                $folder_save,
                $uploadedFile,
                $name
            );
            $source = new ThemeSource();
            $source->ths_name = $name;
            $source->ths_path = $folder_save.'/'.$name;
            $source->ths_version = $request->input('version')&&$request->input('version')!="null"?$request->input('version'):'1.0.0';
            $source->ths_type = $type;
            $source->save();
            Cache::forget('theme_source');
            return $this->setResponse(200,$source);
        }catch (\Exception $error){
            return response($this->setResponse(500,null,$error->getMessage()),500);
        }


    }
        
     
     /**
     * DELETE v2/theme-sources/{id}
     *
     * Xóa dữ liệu
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@id` | ID theme-sources
     * `@fields` | List fields theme-sources
     * @return \Illuminate\Http\Response
     */
    function destroy($id){
        $source = ThemeSource::find($id);
        if($source){
            if(Storage::has($source->ths_path)){
                    Storage::delete($source->ths_path);
                    $source->delete();
                    return $this->setResponse(200,'Delete success');
                }
                return response($this->setResponse(500,null,'Delete error! Not found file in system'),500);
        }else{
               return response($this->setResponse(500,null,'Not found id'),500);
        }
    }
        
    
     /**
     * PUT v2/theme-sources
     *
     * Mô tả chức năng hàm
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@id` | ID theme-sources
     * `@fields` | List fields theme-sources
     * @return \Illuminate\Http\Response
     */
    function update(Request $request,$id){
    
    }
        
}
