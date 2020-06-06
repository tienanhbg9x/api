<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Classified;
use App\Models\UserFavorite;
use Illuminate\Http\Request;

/**
 * @resource v2 Users-favorite
 *
 * Api for Users-favorite
 */
class UserFavoriteController extends Controller
{
    //


    /**
     * GET v2/users-favorite
     *
     * Cho phép trả về danh sách các bản ghi cơ sở dữ liệu.
     *
     * Bạn cần lưu ý rằng hệ thộng mặc định chỉ trả về 30 bản ghi, nếu muốn lấy nhiều hơn bạn có thể truyền thêm các tham số.
     *
     * ### Thông tin cơ bản:
     * Info route | Description
     * --------- | -------
     * `version_route` | Version v2
     * `controller` | UserFavoriteController
     * `route_name` | users-favorite
     *
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Default | Mô tả chi tiết
     * --------- | ------- | -------
     * `@fields` | NULL | Danh sách các trường dữ liệu users-favorite ví dụ (id,name,v..v.v..), nếu không truyền tham số này thì sẽ trả về 1 số trường mặc định.
     * `@orderby` | NULL | Trường ưu tiên sắp xếp trong users-favorite ví dụ (date), nếu không truyền tham số này thì sẽ sắp xếp mặc định.
     * `@limit` | NULL | Số lượng bản ghi users-favorite cần lấy ra, nếu không truyền tham số này thì sẽ mặc định là 30 bản ghi.
     * `@page` | NULL | Số thứ tự trang users-favorite cần lấy ra, nếu không truyền tham số này thì sẽ mặc định là trang 1.
     *
     * @return \Illuminate\Http\Response
     */
    function index(Request $request)
    {
        $user_id = getUserId($this->access_token);
        $offset = $this->page*$this->limit - $this->limit;
        $user_favorites  =  new UserFavorite();
        $user_favorites = $user_favorites->select($user_favorites->alias($this->fields))->where('usf_use_id',$user_id);
        if ($this->where != null) $user_favorites = whereRawQueryBuilder($this->where, $user_favorites, 'mysql', 'user_favorite');
        $user_favorites = $user_favorites->orderBy('usf_id','desc')->offset($offset)->limit($this->limit)->get();
        if(isset($user_favorites[0])&&!empty($user_favorites[0]->cla_id)){
            $arr_cla_id = $user_favorites->map(function($item){
                return $item->cla_id;
            })->toArray();
            $classified = new Classified();
            $classified  = $classified->select($classified->alias('id,title,address,picture,price,list_acreage,rewrite,contact_name,phone,use_id'))->whereIn('cla_id',$arr_cla_id)->where('cla_active',1)->get();
            $classified = $this->filterClassifieds($classified);
            $user_favorites = $user_favorites->map(function($item) use($classified){
               $item->classified_info = $classified->where('id',$item->cla_id)->first();
                return $item;
            });
        }
        return $this->setResponse(200,$user_favorites);

    }


    function filterClassifieds($classifieds)
    {

        $classifieds = $classifieds->map(function ($item) use (&$location_arr) {
            return $this->filterClassified($item);
        });
        return $classifieds;
    }


    function filterClassified($classified, $type = null)
    {
        if (isset($classified->address)) {
            $classified->address = showAddresFromList($classified->address);
        }
        if (isset($classified->date)) {
//            $classified->date = date('d/m/Y h:i:s',$classified->date);
            $classified->date = today_yesterday_v2($classified->date);
        }
        if (isset($classified->price)) {
            $classified->price = showPriceFromList($classified->price);
        }
//        if (isset($classified->list_acreage)) {
//            $classified->list_acreage = $classified->list_acreage != "" || $classified->list_acreage != null ? explode(',', $classified->list_acreage) : null;
//        }
//        if (isset($classified->cat_id)) {
//            $category = Category::select('cat_name')->where('cat_id', $classified->cat_id)->first();
//            $classified->type_classified = $category->cat_name;
//        }
        if (isset($classified->picture)) {
            if ($type == 'detail') {
                $pictures = bdsDecode($classified->picture);
                if ($pictures != "") {
                    if (isset($pictures[0]['type'])) {
                        foreach ($pictures as $key => $picture) {
                            if ($picture['type'] == 'photo') {
                                $picture['url'] = getUrlImageByRow($picture);
                            } else if ($picture['type'] == 'video') {
                                $picture['url'] = "https://img.youtube.com/vi/" . $picture['filename'] . "/sddefault.jpg";
                            }
                            $pictures[$key] = $picture;
                        }
                    } else {
                        foreach ($pictures as $key => $picture) {
                            $picture['teaser'] = "";
                            $picture['filename'] = $picture['name'];
                            unset($picture['name']);
                            $picture['url'] = getUrlImageByRow($picture);
                            $picture['type'] = 'photo';
                            $pictures[$key] = $picture;
                        }
                    }
                    $classified->picture = $pictures;
                } else {
                    $classified->picture = [];
                }
            } else {
                $arrayPicture = bdsDecode($classified->picture);
                $classified->picture = $classified->picture != null && $classified->picture != "" ? getUrlImageMain($arrayPicture, 150) : config('app.thumbnail_default');
            }

        }
        return $classified;
    }



    /**
     * DELETE v2/users-favorite
     *
     * Mô tả chức năng hàm
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@id` | ID users-favorite
     * `@fields` | List fields users-favorite
     * @return \Illuminate\Http\Response
     */
    function destroy(Request $request, $id)
    {
        $user_id = getUserId($request->access_token);
        if($request->input('type')=='id_classified'){
            UserFavorite::where('usf_cla_id',$id)->where('usf_use_id',$user_id)->delete();
        }else{
            UserFavorite::where('usf_id',$id)->where('usf_use_id',$user_id)->delete();
        }
        return $this->setResponse(200, "deleted");
    }
    /**
     * POST v2/users-favorite
     *
     * Mô tả chức năng hàm
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@id` | ID users-favorite
     * `@fields` | List fields users-favorite
     * @return \Illuminate\Http\Response
     */
    function store(Request $request)
    {
        // dd($request->use_id);

        $this->validate($request, [
            'usf_cla_id' => 'numeric|min:1'
        ]);
        $user_id =(int) getUserId($request->input('access_token'));
        if ($user_id == 0) {
            return response($this->setResponse(401, null, 'access_token error!'), 401);
        }

        $check = UserFavorite::where('usf_use_id', $user_id)->where('usf_cla_id', $request->cla_id)->first();
        if (empty($check)) {
            $classified = Classified::select('cla_id')->where('cla_id', $request->cla_id)->first();
            if (empty($classified)) return response('Not found user id', 500);
            $create_favorite = new UserFavorite();
            $create_favorite->usf_use_id = $user_id;
            $create_favorite->usf_cla_id = $request->cla_id;
            $create_favorite->usf_date = time();
            if ($create_favorite->save()) {
                return $this->setResponse(200, $create_favorite);
            }
            return response("fail", 500);
        }

        return response("Exist !", 500);

    }

}
