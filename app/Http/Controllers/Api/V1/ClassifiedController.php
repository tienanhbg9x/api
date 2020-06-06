<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Models\Classified;

/**
 * @resource V1 classified
 *
 * Api quản lí classified
 */
class ClassifiedController extends Controller
{
    /**
     * @param Request $request
     * title (string): Tên tin tức
     * address(string): Địa chỉ
     * date(timestemp): Thời gian đăng tin
     * exprice(timestemp): Ngày hết hạn tin
     * phone (int): Số điện thoại
     * contact_name(string) : Thông tin liên hệ
     * description (string) : Mô tả
     * picture (string) : Ảnh
     * acreage (string) : Diện tích: Ví dụ: 12,23,4,3
     * price (int): Giá
     */
    function createClassified(Request $request)
    {
        try {
            $classified = new Classified();
            $classified->cla_title = $request->title;
            if ($request->rewrite) $classified->cla_rewrite = $request->rewrite;
            if ($request->address) $classified->cla_address = $request->address;
            if ($request->date) $classified->cla_date = $request->date;
            if ($request->exprice) $classified->cla_expire = $request->exprice;
            if ($request->phone) $classified->cla_phone = $request->phone;
            if ($request->email) $classified->cla_email = $request->email;
            if ($request->contact_name) $classified->cla_contact_name = $request->contact_name;
            if ($request->teaser) $classified->cla_teaser = $request->teaser;
            if ($request->description) $classified->cla_description = $request->description;
            if ($request->picture) $classified->cla_picture = $request->picture;
            if ($request->acreage) $classified->cla_list_acreage = $request->acreage;
            if ($request->price) $classified->cla_list_price = $request->price;
            if ($classified->save()) {
                return [
                    'status' => 200,
                    'message' => 'Create Success!'
                ];
            } else {
                return [
                    'status' => 500,
                    'message' => 'Create error'
                ];
            }
        } catch (\Exception $error) {
            return [
                'status' => 503,
                'message' => $error->getMessage()
            ];
        }

    }


    //
}
