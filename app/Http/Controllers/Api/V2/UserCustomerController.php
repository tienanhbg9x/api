<?php

namespace App\Http\Controllers\Api\V2;

use App\Jobs\sendMailToUserWhenCustomerRegister;
use App\Models\Classified;
use App\Models\UserTheme;
use Illuminate\Http\Request;
use App\Models\UserCustomer;


/**
 * @resource v2 User-customer
 *
 * Api for User-customer
 */
class UserCustomerController extends Controller
{
    //


    /**
     * GET v2/user-customer
     *
     * Cho phép trả về danh sách các bản ghi cơ sở dữ liệu.
     *
     * Bạn cần lưu ý rằng hệ thộng mặc định chỉ trả về 30 bản ghi, nếu muốn lấy nhiều hơn bạn có thể truyền thêm các tham số.
     *
     * ### Thông tin cơ bản:
     * Info route | Description
     * --------- | -------
     * `version_route` | Version v2
     * `controller` | UserCustomerController
     * `route_name` | user-customer
     *
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Default | Mô tả chi tiết
     * --------- | ------- | -------
     * `@fields` | NULL | Danh sách các trường dữ liệu user-customer ví dụ (id,name,v..v.v..), nếu không truyền tham số này thì sẽ trả về 1 số trường mặc định.
     * `@limit` | NULL | Số lượng bản ghi user-customer cần lấy ra, nếu không truyền tham số này thì sẽ mặc định là 30 bản ghi.
     * `@page` | NULL | Số thứ tự trang user-customer cần lấy ra, nếu không truyền tham số này thì sẽ mặc định là trang 1.
     * `@type` | NULL | Kiểu lấy dữ liệu
     * `@date_start` | NULL | Ngày bắt đầu
     * `@date_end` | NULL | Ngày kết thúc
     * `@keyword` | NULL | Key tìm kiếm theo tên khách hàng (Yêu cầu có tham số `type = search ` đi kèm)
     * @return \Illuminate\Http\Response
     */
    function index(Request $request)
    {
        $this->validate($request, [
            'type' => 'string',
            'fields' => 'string|min:1|max:255',
            'page' => 'numeric|min:1',
            'limit' => 'numeric|min:1',
            'where' => 'string|max:255|min:3',
            'date_start' => 'string|max:255|min:3',
            'date_end' => 'string|max:255|min:3',
        ]);
        if ($request->input('type') == 'search') {
            if ($request->input('type') != null) {
                return $this->setResponse(200, $this->searchCustomer($request));
            }
            return response($this->setResponse(500, null, 'Not found param keyword'), 500);
        }
        $user_customer = new UserCustomer();
        $offset = $this->page * $this->limit - $this->limit;
        $user_customer = $user_customer->select($user_customer->alias($this->fields));
        if ($this->where != null) $user_customer = whereRawQueryBuilder($this->where, $user_customer, 'mysql', 'user_customer');
        if ($request->input('date_start') !== null && $request->input('date_start') !== null) {
            $date_start = strtotime($request->input('date_start'));
            $date_end =(int) strtotime($request->input('date_end')) + 86400;
            if($date_start==$date_end){
                $user_customer = $user_customer->where('ucm_created_at', $date_start);
            }else{
                $user_customer = $user_customer->whereBetween('ucm_created_at', [$date_start, $date_end]);
            }

        }
        $user_customer = $user_customer->orderBy('ucm_id', 'desc')->offset($offset)->limit($this->limit)->get();
        if ($user_customer->count() != 0) {
            if($request->input('type')=='classified'){
                $id_cla = $user_customer->groupBy('cla_id')->keys()->toArray();
                $classifieds = Classified::select('cla_id as id','cla_title as title','cla_rewrite as rewrite','cla_active as active')->whereIn('cla_id',$id_cla)->get();
                $classifieds = $classifieds->groupBy('id')->toArray();
                foreach ($user_customer as $key => $data) {
                    if($user_customer[$key]->info){
                        $user_customer[$key]->info = json_decode($data->info);
                    }
                    if($user_customer[$key]->created_at){
                        $user_customer[$key]->created_at = date('d-m-Y h:i:s',$user_customer[$key]->created_at);
                    }
                    if($user_customer[$key]->cla_id!=null){
                        $user_customer[$key]->classified = $classifieds[$user_customer[$key]->cla_id][0];
                    }
                }
            }else{
                $id_uth = $user_customer->groupBy('uth_id')->keys()->toArray();
                $user_theme = UserTheme::select('uth_id as id','uth_name as name','uth_rewrite as rewrite','uth_active as active')->whereIn('uth_id',$id_uth)->get();
                $user_theme = $user_theme->groupBy('id')->toArray();
                foreach ($user_customer as $key => $data) {
                    if($user_customer[$key]->info){
                        $user_customer[$key]->info = json_decode($data->info);
                    }
                    if($user_customer[$key]->created_at){
                        $user_customer[$key]->created_at = date('d-m-Y h:i:s',$user_customer[$key]->created_at);
                    }
                    if($user_customer[$key]->uth_id!=null&&$user_customer[$key]->uth_id!=0&&isset($user_theme[$user_customer[$key]->uth_id])){
                        $user_customer[$key]->uth_theme = $user_theme[$user_customer[$key]->uth_id][0];
                    }

                }
            }

        }
        if ($request->input('type') == 'load_file_excel') {
            $excel_data = [];
            $row_count = 1;
            foreach ($user_customer as $user){
                $row = [];
                $row['STT'] = $row_count;
                $row['Tên'] = $user->name;
                $row['Địa chỉ'] = $user->address;
                $row['Số điện thoại'] = $user->phone;
                $row['Địa chỉ email'] = $user->email;
                $row['Website cá nhân'] = $user->web;
                $row['Ngày đăng kí'] = $user->created_at;
//                $row['Thông tin bổ xung'] =   implode(',',$user->info);
                $excel_data[] = $row;
                $row_count++;
            }
            return $this->setResponse(200,$excel_data);
        }
        $data = [
            'current_page' => $this->page,
            'per_page' => $this->limit,
            'data' => $user_customer
        ];
        return $this->setResponse(200, $data);
    }



    function searchCustomer($request)
    {
        $keyword = str_replace("'", '', str_replace('/', '', str_replace("\"", '', urldecode($request->input('keyword')))));
        $keyword = trim($keyword);
        if (str_replace(" ", "", $keyword) == "") {
            return $data = [
                'current_page' => $this->page,
                'per_page' => $this->limit,
                'data' => []
            ];
        }
        $terms_elastic_data = [];
        if ($request->input('where')) {
            $where = explode(',', $request->input('where'));
            foreach ($where as $item) {
                $item = explode(" ", $item);
                if (isset($item[1])) $terms_elastic_data[$item[0]] = $item[1];
            }
        }
        if ($this->fields != null) {
            $source_elastic_data = explode(',', $this->fields);
        } else {
            $source_elastic_data = ["id", "name", "use_id", "feature", "address", "email", "phone", "web", "info", "created_at"];
        }
        $query_elastic_data = BuildPhraseTrigrams($keyword);
        $data_return = searchDataElastic('user_customers', ["name2"], $query_elastic_data, $terms_elastic_data, $source_elastic_data, $this->limit);
        foreach ($data_return as $key => $data) {
            $data_return[$key]['info'] = json_decode($data['info']);
        }
        return $data = [
            'current_page' => $this->page,
            'per_page' => $this->limit,
            'data' => $data_return
        ];


    }


    /**
     * GET v2/user-customer/{id}
     *
     * Lấy dữ liệu theo id
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@id` | ID user-customer
     * `@fields` | List fields user-customer
     * @return \Illuminate\Http\Response
     */
    function show(Request $request, $id)
    {
        if (checkAuthUser($request->input('access_token'), $request->input('user_id'))) {
            $user_customer = new UserCustomer();
            $user_customer = $user_customer->select($user_customer->alias($this->fields))->where('ucm_id', $id)->first();
            if ($user_customer) {
                if ($user_customer->info) {
                    $user_customer->info = json_decode($user_customer->info);
                }
                if ($user_customer->created_at) {
                    $user_customer->created_at = date('d/m/Y H:i:s', $user_customer->created_at);
                }
                return $this->setResponse(200, $user_customer);
            }
            return $this->setResponse(404, null, 'Not found id');
        }
        return response($this->setResponse(401, null, 'Not auth'), 401);
    }


    /**
     * POST v2/user-customer
     *
     * Thêm dữ liệu vào cơ sở dữ liệu
     * ### Thông số dữ liệu:
     * Trường dữ liệu (Param) | Default |Mô tả chi tiết
     * --------- | ------- |---------
     * `@type` | single | Kiểu thêm dữ liệu
     * `@data`| null | Dữ liệu (array['field'=>value])
     * `@user_id`| null | Id của người dùng
     *
     * ### Thông số `type`:
     * Giá trị | Mô tả chi tiết
     * ---------| -------
     * `multi` | Thêm nhiều dữ liệu trong môt reqest. Yêu cầu `data` là kiểu array
     * `single` | Thêm một dữ liệu, Data là một object
     * @return \Illuminate\Http\Response
     */
    function store(Request $request)
    {
//        dd($request->all());
        if (checkAuthUser($request->input('access_token'), $request->input('user_id')) && $request->input('data') != null) {
            $user_id = $request->input('user_id');
            if ($request->input('type') == 'multi') {
                $data = $request->input('data');
                if (isset($data[0]['name']) && isset($data[0]['email']) && isset($data[0]['address']) && isset($data[0]['email']) && isset($data[0]['phone']) && isset($data[0]['web'])) {
                    $data_insert = [];
                    foreach ($data as $item) {
                        array_push($data_insert,
                            [
                                'ucm_use_id' => $user_id,
                                'ucm_name' => $item['name'],
                                'ucm_address' => $item['address'],
                                'ucm_email' => $item['email'],
                                'ucm_phone' => $item['phone'],
                                'ucm_web' => $item['web'],
                                'ucm_created_at' => time()
                            ]);
                    }
                    try {
                        UserCustomer::insert($data_insert);
                        return $this->setResponse(200, 'Save');
                    } catch (\Exception $error) {
                        return $this->setResponse(500, 'Save error');
                    }


                } else {
                    return $this->setResponse(500, null, 'Error! Not enough found value!');
                }
            } else if ($request->input('type') == 'single') {
                $data = $request->input('data');
                if (!isset($data['name']) || !isset($data['phone']) || !isset($data['address'])) {
                    return response($this->setResponse(500, null, 'data not found'), 500);
                }
                $user_custom = new UserCustomer();
                $user_custom->ucm_use_id = $user_id;
                $user_custom->ucm_name = $data['name'];
                $user_custom->ucm_phone = $data['phone'];
                $user_custom->ucm_address = $data['address'];
                $user_custom->ucm_email = isset($data['email']) ? $data['email'] : null;
                $user_custom->ucm_web = isset($data['web']) ? $data['web'] : null;
                $user_custom->ucm_info = isset($data['info']) ? json_encode($data['info']) : null;
                $user_custom->ucm_created_at = time();
                if ($user_custom->save()) {
                    return $this->setResponse(200, 'Saved');
                } else {
                    return $this->setResponse(500, null, 'Save error');
                }

            }
        }
        return $this->setResponse(500, null, 'Not auth');

    }


    /**
     * PUT v2/user-customer/{id}
     *
     * Cập nhật dữ liệu
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@id` | ID user-customer
     * `@use_id` | Id người dùng
     * `@access_token` | Mã xác thực
     * @return \Illuminate\Http\Response
     */
    function update(Request $request, $id)
    {
        if (checkAuthUser($request->input('access_token'), $request->input('use_id'))) {
            $data = $request->all();
            unset($data['access_token']);
            unset($data['use_id']);
            unset($data['id']);
            unset($data['created_at']);
            $user_customer = UserCustomer::find($id);
            if ($user_customer) {
                if ($this->setDataModel($user_customer, $data)->save()) {
                    return $this->setResponse(200, 'Saved');
                }
                return response($this->setResponse(503, null, 'Server error'), 503);

            } else {
                return response($this->setResponse(404, null, 'Not found id'), 404);
            }
        }
        return response($this->setResponse(500, null, 'Update error'), 500);
    }

    function setDataModel($user_customer, $data)
    {
        foreach ($data as $key => $value) {
            if (in_array($key, $user_customer->fillable)) {
                $user_customer->{'ucm_' . $key} = $value;
            }
        }
        return $user_customer;
    }


    /**
     * DELETE v2/user-customer/{id}
     *
     * Mô tả: Xóa bản ghi trong hệ thống
     * @return \Illuminate\Http\Response
     */
    function destroy(Request $request, $id)
    {
        $user_customer = UserCustomer::find($id);
        if ($user_customer) {
            if (checkAuthUser($request->input('access_token'), $user_customer->ucm_use_id)) {
                $user_customer->delete();
                return $this->setResponse(200, 'Deleted');
            }
            return response($this->setResponse(401, null, 'Not auth'), 401);
        }
        return response($this->setResponse(404, null, "Not found id"), 404);
    }


    /**
     * GET v2/file-excel-sample.xlxs
     *
     * Mô tả: Tải file excel mẫu
     * @return \Illuminate\Http\Response
     */
    function downloadFileExcelSample(Request $request)
    {
        if ($request->header("isdoc", 0)) return '';
        $filename = public_path() . "/file/excel_sample.xlsx";
        $chunksize = 5 * (1024 * 1024); //5 MB (= 5 242 880 bytes) per one chunk of file.
        if (file_exists($filename)) {
            set_time_limit(300);
            $size = intval(sprintf("%u", filesize($filename)));
            header('Content-Type: application/octet-stream');
            header('Content-Transfer-Encoding: binary');
            header('Content-Length: ' . $size);
            header('Content-Disposition: attachment;filename="' . basename($filename) . '"');

            if ($size > $chunksize) {
                $handle = fopen($filename, 'rb');

                while (!feof($handle)) {
                    print(@fread($handle, $chunksize));

                    ob_flush();
                    flush();
                }

                fclose($handle);
            } else readfile($filename);
            exit;
        } else {
            exit('Requested file does not exist on our server!');
        }
    }

    function addUserFromTemplate(Request $request)
    {
        if ($request->input('theme_secret')) {
            $user_info = getInfoFormSecret($request->input('theme_secret'));
            if ($user_info == false) {
                return response($this->setResponse(401, null, 'form secret error!'), 401);
            }
            $user_theme_id = $user_info->user_theme_id;
            $user_custom = new UserCustomer();
            $user_custom->ucm_use_id = (int)$user_info->id;
            $user_custom->ucm_name = $request->input('name');
            $user_custom->ucm_phone = $request->input('phone');
            $user_custom->ucm_address = $request->input('address');
            $user_custom->ucm_email = $request->input('email');
            $user_custom->ucm_web = $request->input('web');
            $user_custom->ucm_info = $request->input('content') ? json_encode([['key' => 'Thông tin khách hàng', 'description' => $request->input('content')]]) : null;
            $user_custom->ucm_uth_id = $user_theme_id;
            $user_custom->ucm_created_at = time();
            if ($user_custom->save()) {
                $user_id = $user_info->id;
                sendMailToUserWhenCustomerRegister::dispatch($user_id, $user_theme_id, $user_custom);
                return $this->setResponse(200, 'Saved');
            } else {
                return $this->setResponse(500, null, 'Save error');
            }
        } else {
            return response($this->setResponse(401, 'form secret not found!'), 401);
        }
    }

}
