<?php

namespace App\Http\Controllers\Api\V2;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;


/**
 * @resource V2 AutoSave
 *
 * Api for AutoSave
 */
class AutoSaveController extends Controller
{

    /**
     * GET v2/auto-save
     *
     *  Lấy dữ liệu trong bộ nhớ cache theo khóa
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@key` | Key cache
     * `@access_token ` | khóa truy cập
     * @return \Illuminate\Http\Response
     */
    function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'key' => 'required|string',
            'access_token' => 'required|string',
        ], [
            'key.required' => 'Bạn chưa cung cấp key',
            'key.string' => 'Kiểu dữ liệu của key sai',
            'access_token.required' => 'Không tìm thấy khóa truy cập',
            'access_token.string' => 'Kiểu dữ liệu sai(access_token)',
        ]);
        if (collect($validator->messages())->collapse()->count() !== 0) {
            return $this->setResponse(500, '', collect($validator->messages())->collapse());
        }

        $data_auth = getDataAuth($request->input('access_token'));
        $user_id = $data_auth->data->id;
        $key = "auto_save_{$request->input('key')}_{$user_id}";
        $data = Cache::get($key);
        if (!$data) {
            return response($this->setResponse(404, null, 'Not found data autosave!'),200);
        }
        $data = json_decode($data);
        return $this->setResponse(200, $data);
    }


    /**
     * POST v2/auto-save
     *
     *  Thêm dữ liệu vào trong bộ nhớ cache theo khóa
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@key` | Key cache
     * `@access_token ` | khóa truy cập
     * @return \Illuminate\Http\Response
     */
    function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'key' => 'required|string',
            'access_token' => 'required|string',
        ], [
            'key.required' => 'Bạn chưa cung cấp key',
            'key.string' => 'Kiểu dữ liệu của key sai',
            'access_token.required' => 'Không tìm thấy khóa truy cập',
            'access_token.string' => 'Kiểu dữ liệu sai(access_token)',
        ]);
        if (collect($validator->messages())->collapse()->count() !== 0) {
            return $this->setResponse(500, '', collect($validator->messages())->collapse());
        }

        if (getDataAuth($request->input('access_token')) !== false) {
            $data_auth = getDataAuth($request->input('access_token'));
            $user_id = $data_auth->data->id;
            $data = $request->all();
            $key = "auto_save_{$request->input('key')}_{$user_id}";
            Cache::forget($key);
            unset($data['access_token']);
            unset($data['key']);
            Cache::remember($key, 14400, function () use ($data) {
                return json_encode($data);
            });
            return $this->setResponse(200, 'Saved');
        } else {
            return $this->setResponse(500, null, 'Auto save fail! Not found user_id');
        }
    }


    /**
     * PUT v2/auto-save/{id}
     *
     *  Xóa dữ liệu trong bộ nhớ cache theo khóa
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@key` | Key cache
     * `@access_token ` | Khóa truy cập
     * @return \Illuminate\Http\Response
     */
    function destroy(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'key' => 'required|string',
            'access_token' => 'required|string',
        ], [
            'key.required' => 'Bạn chưa cung cấp key',
            'key.string' => 'Kiểu dữ liệu của key sai',
            'access_token.required' => 'Không tìm thấy khóa truy cập',
            'access_token.string' => 'Kiểu dữ liệu sai(access_token)',
        ]);
        if (collect($validator->messages())->collapse()->count() !== 0) {
            return $this->setResponse(500, '', collect($validator->messages())->collapse());
        }
        if (checkAuthUser($request->access_token, $id)) {
            $key = "auto_save_{$request->input('key')}_{$id}";
            $status = Cache::forget($key);
            if ($status) {
                return $this->setResponse(200, 'Delete');
            } else {
                return $this->setResponse(500, null, 'Delete fail');
            }

        }
    }


}
