<?php
/**
 * Created by PhpStorm.
 * User: minhngoc
 * Date: 09/11/2019
 * Time: 09:03
 */

namespace App\Http\Controllers\Api\V3;


use App\Models\Classified;
use App\Models\UserFavorite;
use Illuminate\Http\Request;

class ClassifiedController extends Controller
{
    function index(Request $request){
        $classifieds = new Classified();
        $offset = $this->page * $this->limit - $this->limit;
        $classifieds = $classifieds->select($classifieds->alias($this->fields));
        if ($this->where != null) $classifieds = whereRawQueryBuilder($this->where, $classifieds, 'mysql', 'classifieds');
        if ($request->input('order') == 'date') {
            $classifieds = $classifieds->orderBy('cla_date', 'desc');
        } else {
            $classifieds = $classifieds->orderBy('cla_id', 'desc');
        }
        $classifieds = $classifieds->offset($offset)->limit($this->limit)->get();
        $classifieds = $this->filterClassifieds($classifieds);
        $data = [
            'current_page' => $this->page,
            'per_page' => $this->limit,
            'classifieds' => $classifieds
        ];
        return $this->createResponse($data);
    }

    function filterClassifieds($classifieds)
    {
        $classifieds = $classifieds->map(function ($item) use (&$location_arr) {
            return $this->filterClassified($item);
        });
        //hai_fix
        if ($this->access_token != null && strpos('user_favorite', $this->fields) != -1) {
            $user_id = getUserId($this->access_token);
            $arr_cla_id = [];
            foreach ($classifieds as $cla) {
                $arr_cla_id[] = $cla['id'];
            }
            if (count($arr_cla_id) == 0) {
                $use_favorites = [];
            } else {
                $use_favorites = UserFavorite::select('usf_cla_id')->where('usf_use_id', $user_id)->whereIn('usf_cla_id', $arr_cla_id)->get()->map(function ($item) {
                    return $item->usf_cla_id;
                })->toArray();
            }
            foreach ($classifieds as $cla) {
                if (in_array($cla['id'], $use_favorites)) $cla['use_favorite'] = 1;
                else $cla['use_favorite'] = 0;
            }
        }

        //end hai_fix
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

        }
        return $classified;
    }
}