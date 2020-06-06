<?php

namespace App\Http\Controllers\Api\V1;


use App\Models\Link;
use Illuminate\Http\Request;
use App\Models\Classified;
use App\Models\Location;
use App\Models\Category;
use Illuminate\Support\Facades\Cache;

/**
 * @resource V1 Home
 *
 * Api Trang chủ
 */
class HomeController extends Controller
{
    /**
     * @param {type}: Kiểu khu vực
     * @param Request:
     * keyword (string): Tên địa điểm
     */
    function searchLocation($type, Request $request)
    {
        $keyword = $request->keyword;
        $locations = Location::select('loc_id', 'loc_name')->where('loc_type', $type)->where('loc_name', 'like', "%$keyword%")->get();
        if ($this->api_token != null) {
            return [
                'status' => 200,
                'api_token' => $this->api_token,
                'data' => $locations
            ];
        } else {
            return [
                'status' => 200,
                'data' => $locations
            ];
        }


    }

    /**
     * Tin mới nhất
     */
    function getLastClassified()
    {

        $classifieds = Classified::select('classifieds.cla_id', 'classifieds.cla_title', 'classifieds.cla_disid', 'classifieds.cla_rewrite', 'classifieds.cla_date', 'classifieds.cla_has_picture', 'classifieds.cla_picture', 'classifieds.cla_description', 'classifieds.cla_price', 'classifieds.cla_list_acreage')
            ->where('classifieds.cla_active', 1)->orderBy('cla_date', 'desc')->limit(20)->get();
        $classifieds = $this->filterClassifieds($classifieds);


        if ($this->api_token != null) {
            return [
                'status' => 200,
                'api_token' => $this->api_token,
                'data' => $classifieds
            ];
        } else {
            return [
                'status' => 200,
                'data' => $classifieds
            ];
        }


    }
    /**
     * Danh sách chuyên mục
     */
    function getCategories()
    {
        $categories_id = Classified::select('cla_cat_id')->where('cla_active', 1)->orderBy('cla_id', 'desc')->limit(500)->get()->groupBy('cla_cat_id')->keys()->take(30);
        $categories = Link::select('lin_catid as id', 'lin_title as title', 'lin_rewrite as rewrite')->whereIn('lin_catid', $categories_id)->limit(30)->get();
        return $this->setResponse(200, $categories);
    }
    /**
     * Slider theo vị trí
     */
    function getImageMapLocation()
    {
        $links_date_desc = Link::select('lin_id as id', 'lin_title as title', 'lin_rewrite as rewrite', 'lin_citid')->orderBy('lin_total_record', 'desc')->limit(3)->get();
        $arr_location_id = $links_date_desc->map(function ($item) {
            return $item->lin_citid;
        });
        $location = Location::select('loc_id', 'loc_address','loc_cit_name')->whereIn('loc_id', $arr_location_id)->get();

        $link_map_location = $links_date_desc->map(function ($item) use ($location) {
            $location_link = $location->where('loc_id', $item->lin_citid)->first();
            $image = $location_link ? "https://maps.googleapis.com/maps/api/staticmap?size=382x128&zoom=10&center=$location_link->loc_address&format=png" : 'https://maps.googleapis.com/maps/api/staticmap?size=400x250&zoom=10&center=ha%20noi&format=png';
//           $url_image_map = signUrlGoogleApi($image.'&key='.env('APP_GOOGLE_MAP_KEY'),env('APP_GOOGLE_MAP_SECRET'));
            return ['id' => $item->id, 'title' => $item->title, 'location_name' => $location_link->loc_cit_name, 'rewrite' => $item->rewrite, 'image_location' => $image];
        });
        return $this->setResponse(200, $link_map_location);
    }
}
