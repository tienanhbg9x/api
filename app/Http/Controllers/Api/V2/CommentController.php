<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Address;
use App\Models\AddressReview;
use App\Models\Comment;
use Illuminate\Http\Request;
use App\Models\Location;
use \DB;
use Illuminate\Support\Facades\Cache;

/**
 * @resource v2 Comments
 *
 * Api for Comments
 */
class CommentController extends Controller
{
    //


    /**
     * GET v2/comments
     *
     * Cho phép trả về danh sách các bản ghi cơ sở dữ liệu.
     *
     * Bạn cần lưu ý rằng hệ thộng mặc định chỉ trả về 30 bản ghi, nếu muốn lấy nhiều hơn bạn có thể truyền thêm các tham số.
     *
     * ### Thông tin cơ bản:
     * Info route | Description
     * --------- | -------
     * `version_route` | Version v2
     * `controller` | CommentController
     * `route_name` | comments
     *
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Default | Mô tả chi tiết
     * --------- | ------- | -------
     * `@fields` | NULL | Danh sách các trường dữ liệu comments ví dụ (id,name,v..v.v..), nếu không truyền tham số này thì sẽ trả về 1 số trường mặc định.
     * `@orderby` | NULL | Trường ưu tiên sắp xếp trong comments ví dụ (date), nếu không truyền tham số này thì sẽ sắp xếp mặc định.
     * `@limit` | NULL | Số lượng bản ghi comments cần lấy ra, nếu không truyền tham số này thì sẽ mặc định là 30 bản ghi.
     * `@page` | NULL | Số thứ tự trang comments cần lấy ra, nếu không truyền tham số này thì sẽ mặc định là trang 1.
     * `@type` | NULL | Tùy chọn lấy thông tin theo một số trường hợp đặc biệt.
     *
     * ### Tùy chọn lấy dữ liệu với tham số  `@type`:
     * Giá trị  | Mô tả chi tiết
     * `show_comment_child` \ Hiển thị gồm cả comments cấp con.(VD: hiển thị comment tại một tỉnh thì sẽ lấy tất cả comment của tất cả các huyện trong tỉnh đó)
     *
     * @return \Illuminate\Http\Response
     */
    function index(Request $request)
    {
        $this->validate($request, [
            'fields' => 'string|min:1|max:255',
            'page' => 'numeric|min:1',
            'limit' => 'numeric|min:1',
            'where' => 'string|max:255|min:3',
            'type' => 'string'
        ]);
        $offset = $this->page * $this->limit - $this->limit;
        $comment = new Comment();
        $comment = $comment->select($comment->alias($this->fields));
        if($request->input('type')=='show_comment_child'){
            $where = $request->input('where',null);
            if(!empty($where)){
                $where = explode(',',$where);
                foreach ($where as $value){
                    $value = explode(" ",$value);
                    if(isset($value[1])&&$value[0]=='add_id'){
                        $add_id = (int) $value[1];
                        break;
                    }
                }
                if(!empty($add_id)){
                    $address = Address::select('add_id','add_citid','add_disid','add_wardid','add_streetid','add_type')->where('add_id',$add_id)->first();
                    if($address){
                        switch ($address->add_type){
                            case 'city':
                                $comment = $comment->where('com_add_cit_id',$address->add_citid)->where('com_cla_id',0);
                                break;
                            case 'district':
                                $comment = $comment->where('com_add_dis_id',$address->add_disid)->where('com_cla_id',0);
                                break;
                            case 'ward':
                                $comment = $comment->where('com_add_ward_id',$address->add_wardid)->where('com_cla_id',0);
                                break;
                            case 'street':
                                $comment = $comment->where('com_add_street_id',$address->add_streetid)->where('com_cla_id',0);
                                break;
                            default:
                                if ($this->where != null) $comment = whereRawQueryBuilder($this->where, $comment, 'mysql', 'comments');
                        }
                    }
                }
            }

        }else{
            if ($this->where != null) $comment = whereRawQueryBuilder($this->where, $comment, 'mysql', 'comments');
        }
        $comment = $comment->orderBy('com_id', 'desc')->offset($offset)->limit($this->limit)->get();
        if(isset($comment[0])&&$comment[0]->date){
            $comment = $comment->map(function($item){
               $item->date = today_yesterday_v2($item->date);
                return $item;
            });
        }
        $data = [
            'current_page' => $this->page,
            'per_page' => $this->limit,
            'comments' => $comment
        ];
        return $this->setResponse(200, $data);
    }


    /**
     * GET v2/comments
     *
     * Mô tả chức năng hàm
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@id` | ID comments
     * `@fields` | List fields comments
     * @return \Illuminate\Http\Response
     */
    function show($id)
    {

    }


    /**
     * PUT v2/comments
     *
     * Mô tả chức năng hàm
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@id` | ID comments
     * `@fields` | List fields comments
     * @return \Illuminate\Http\Response
     */
    function update(Request $request, $id)
    {

    }


    /**
     * POST v2/comments
     *
     * Mô tả chức năng hàm
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@id` | ID comments
     * `@fields` | List fields configuration-comment
     * @return \Illuminate\Http\Response
     */
    function store(Request $request)
    {
        if ($request->input("type") == 'from_web') {
            $this->validate($request, [
                'star' => 'required',
                'add_id'=>'required',
                'content' => 'string|min:5',
                'description' => 'string',
//                'user_id' => 'numeric',
                'user_name' => 'required|string|min:2|max:50',
                'access_token' => 'required|string'
            ]);
            if (getInfoFormSecret($request->input('access_token')) !== false) {
                if ($request->input('add_id')&&$request->input('add_id')!=0) {
                    $key_cache = 'comment_'.$request->user_id.'_'.$request->add_id;
                    if(Cache::has($key_cache)){
                        return response('Bạn đã đánh giá khu vực này! Vui lòng quay lại sau 60 phút nữa.',500);
                    }else{
                        Cache::remember($key_cache,3600,function(){
                            return 1;
                        });
                    }
                    $address = Address::select('add_citid', 'add_disid', 'add_wardid', 'add_streetid', 'add_type')->where('add_id', $request->add_id)->first();
                }
                if (empty($address)) return response('Not found address', 500);

                $comment = new Comment();
                $comment->com_add_id = $request->add_id;
                $comment->com_add_cit_id = $address->add_citid;
                $comment->com_add_dis_id = $address->add_disid;
                $comment->com_add_ward_id = $address->add_wardid;
                $comment->com_add_street_id = $address->add_streetid;
                $comment->com_star = (int) $request->star;
                $comment->com_cla_id = (int) $request->input('cla_id',0);
                if ($request->input('description')) {
                    $description_star = explode('.', $request->input('description'));
                    $description_star = implode('. ', array_filter($description_star));
                    if (!empty($description_star) && $request->input('content')) {
                        $comment->com_content = "$description_star. " . $request->input('content');
                    } else {
                        $comment->com_content = $description_star;
                    }
                }else{
                    $comment->com_content = $request->input('content');
                }
                $comment->com_active = 1;
                if ($request->input('user_id')) $comment->com_user_id =(int) $request->user_id;
                $comment->com_user_name = $request->user_name;
                $comment->com_date = time();
                if ($comment->save()) {
                    $address_review = AddressReview::where('adr_add_id', $comment->com_add_id)->first();
                    $this->updateAddressReview($address, $comment->com_star);
                    if ($address_review) {
                        $address_review->adr_total_star = DB::raw("adr_total_star + ".$comment->com_star);
                        $address_review->adr_total_review = DB::raw("adr_total_review + 1");
                        $address_review->adr_star_first = ($comment->com_star == 1 ? DB::raw("adr_star_first + 1") : $address_review->adr_star_first);
                        $address_review->adr_star_second = ($comment->com_star == 2 ? DB::raw("adr_star_second + 1") : $address_review->adr_star_second);
                        $address_review->adr_star_third = ($comment->com_star == 3 ? DB::raw("adr_star_third + 1") : $address_review->adr_star_third);
                        $address_review->adr_star_fourth = ($comment->com_star == 4 ? DB::raw("adr_star_fourth + 1") : $address_review->adr_star_fourth);
                        $address_review->adr_star_fifth = ($comment->com_star == 5 ? DB::raw("adr_star_fifth + 1") : $address_review->adr_star_fifth);
                    } else {
                        $address_review = new AddressReview();
                        $address_review->adr_add_id = $comment->com_add_id;
                        $address_review->adr_total_star = $comment->com_star;
                        $address_review->adr_total_review = 1;
                        if ($comment->com_star == 1) $address_review->adr_star_first = 1;
                        if ($comment->com_star == 2) $address_review->adr_star_second = 1;
                        if ($comment->com_star == 3) $address_review->adr_star_third = 1;
                        if ($comment->com_star == 4) $address_review->adr_star_fourth = 1;
                        if ($comment->com_star == 5) $address_review->adr_star_fifth = 1;
                    }
                    if ($address_review->save()){
                        $comment =  $comment->toArray();
                        $comment_tmp = [];
                        foreach ($comment as $key=>$value){
                            $comment_tmp[str_replace('com_','',$key)] = $value;
                        }
                        $comment_tmp['date'] = today_yesterday_v2( $comment_tmp['date']);
                        return $this->setResponse(200, $comment_tmp);
                    }
                }
                return response('Save error!', 500);
            } else {
                return response($this->setResponse(401, null, 'Not auth'), 401);
            }
        } else {
            $this->validate($request, [
                'start' => 'required|numeric|min:1|max:10',
                'content' => 'required|string|min:1|max:225',
                'add_id' => 'numeric',
                'user_id' => 'numeric',
                'user_name' => 'required|string|min:3|max:50'
            ]);
            $comment = new Comment();
            $comment->com_star = $request->star;
            $comment->com_content = $request->input('content');
            $comment->com_add_id = $request->add_id;
            $comment->com_active = 1;
            if ($request->user_id) $comment->user_id = $request->user_id;
            $comment->com_user_name = $request->user_name;

            if ($comment->save()) {
                return $this->setResponse(200, 'Saved');
            }
            return response('Save error!', 500);
        }


    }

    function updateAddressReview($address,$star){
        $arr_update = ['adr_total_star'=>DB::raw(" adr_total_star + $star"),'adr_total_review'=>DB::raw('adr_total_review + 1')];
        switch ($star){
            case 1:
                $arr_update['adr_star_first'] = DB::raw("adr_star_first + 1");
                break;
            case 2:
                $arr_update['adr_star_second'] = DB::raw("adr_star_second + 1");
                break;
            case 3:
                $arr_update['adr_star_third'] = DB::raw("adr_star_third + 1");
                break;
            case 4:
                $arr_update['adr_star_fourth'] = DB::raw("adr_star_fourth + 1");
                break;
            case 5:
                $arr_update['adr_star_fifth'] = DB::raw("adr_star_fifth + 1");
                break;
        }
        switch ($address->add_type){
            case 'district':
                //                AddressReview::where('adr_add_id',$address->add_citid)->update($arr_update);
                $address_review = AddressReview::where('adr_add_id',$address->add_citid)->first();
                if($address_review){
                    $this->updateOrCreateDB($address_review,$arr_update);
                }else{
                    $this->updateOrCreateDB($address_review,$arr_update,$address->add_citid);
                }
                break;
            case 'ward':
                $address_review =  AddressReview::where('adr_add_id',$address->add_citid)->first();
                if($address_review){
                    $this->updateOrCreateDB($address_review,$arr_update);
                }else{
                    $this->updateOrCreateDB($address_review,$arr_update,$address->add_citid);
                }
                $address_review =  AddressReview::where('adr_add_id',$address->add_disid)->first();
                if($address_review){
                    $this->updateOrCreateDB($address_review,$arr_update);
                }else{
                    $this->updateOrCreateDB($address_review,$arr_update,$address->add_disid);
                }
//                AddressReview::where('adr_add_id',$address->add_citid)->update($arr_update);
//                AddressReview::where('adr_add_id',$address->add_disid)->update($arr_update);
                break;
            case 'street':
                $address_review =  AddressReview::where('adr_add_id',$address->add_citid)->first();
                if($address_review){
                    $this->updateOrCreateDB($address_review,$arr_update);
                }else{
                    $this->updateOrCreateDB($address_review,$arr_update,$address->add_citid);
                }
                $address_review =  AddressReview::where('adr_add_id',$address->add_disid)->first();
                if($address_review){
                    $this->updateOrCreateDB($address_review,$arr_update);
                }else{
                    $this->updateOrCreateDB($address_review,$arr_update,$address->add_disid);
                }
                $address_review =  AddressReview::where('adr_add_id',$address->add_wardid)->first();
                if($address_review){
                    $this->updateOrCreateDB($address_review,$arr_update);
                }else{
                    $this->updateOrCreateDB($address_review,$arr_update,$address->add_wardid);
                }
//                AddressReview::where('adr_add_id',$address->add_citid)->update($arr_update);
//                AddressReview::where('adr_add_id',$address->add_disid)->update($arr_update);
//                AddressReview::where('adr_add_id',$address->add_wardid)->update($arr_update);
                break;
        }
    }

    function updateOrCreateDB($model,$data,$add_id=null){
        if(!empty($add_id)){
            $model = new AddressReview();
            $data['adr_add_id'] = $add_id;
        }
        foreach ($data as $key=>$value){
            $model->{$key} = $value;
        }
        $model->save();
    }
}
