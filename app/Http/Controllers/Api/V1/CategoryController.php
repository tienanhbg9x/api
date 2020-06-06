<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Category;
use App\Models\Classified;
use App\Models\Link;
use Illuminate\Http\Request;

/**
 * @resource V1 Categories
 *
 * Api for page Category
 */
class CategoryController extends Controller
{
    /**
     *
     *
     * Lấy danh sách tin tức theo chuyên mục
     *
     */
    function getListClassified($slug, Request $request)
    {
        $slug = urldecode($slug);
        $sphinx = app()->make('sphinx');
        $slug_md5 = md5($slug);
        //Check slug
        $link = Link::select('lin_id', 'lin_title', 'lin_short_title', 'lin_what', 'lin_catid', 'lin_citid', 'lin_disid', 'lin_wardid', 'lin_streetid', 'lin_projid', 'lin_all_parent', 'lin_parentid', 'lin_keyword')->where('lin_length', strlen($slug))->where('lin_md5', $slug_md5)->first();
        if ($link) {
            //Check params request
            $request = $request->all();
            $offset = isset($request['page']) ? ($request['page'] * 20 -20) : 0;
            $query_option = isset($request['query_option']) ? $request['query_option'] : '';
            $column_order = 'cla_date';
            $type_order = 'desc';
            if ($query_option != '') {
                if ($query_option == 'price_desc') {
                    $column_order = 'cla_price';
                } else if ($query_option == 'price_asc') {
                    $column_order = 'cla_price';
                    $type_order = 'asc';
                }
            }
            //Get cla_id in classifieds from sphinx
            $query = $sphinx->select('id')->from('classifieds')->where('cla_cat_id', '=', $link->lin_catid)->where('cla_active', 1);
            if ($link->lin_citid != 0) $query->where('cla_citid', $link->lin_citid);
            if ($link->lin_disid != 0) $query->where('cla_disid', $link->lin_disid);
            if ($link->lin_warid != 0) $query->where('cla_wardid', $link->lin_warid);
            if ($link->lin_streetid != 0) $query->where('cla_streetid', $link->lin_streetid);
            if ($link->lin_projid != 0) $query->where('cla_projid', $link->lin_projid);
            $query->orderBy($column_order, $type_order);
//            dd($query);
            $arr_id = $query->limit($offset, 20)->execute()->fetchAllAssoc();
            $arr_id = collect($arr_id)->map(function ($item) {
                return $item['id'];
            });
            //Get data classifieds
            $classifieds = Classified::select('cla_id', 'cla_title', 'cla_disid', 'cla_rewrite', 'cla_date', 'cla_has_picture', 'cla_picture', 'cla_description', 'cla_price', 'cla_list_acreage')
                ->whereIn('cla_id', $arr_id)
                ->orderBy($column_order, $type_order)->get();
            $classifieds = $this->filterClassifieds($classifieds);
            //Create meta tag
            $meta = [
                [
                    'name' => 'title',
                    'content' => $link->lin_short_title
                ],
                [
                    'name' => 'og:title',
                    'content' => $link->lin_short_title
                ],
                [
                    'name' => 'description',
                    'content' => $link->lin_meta_description,
                ],
                [
                    "name" => "og:description",
                    "content" => $link->lin_meta_description,
                ],
                [
                    'name' => 'keywords',
                    'content' => $link->lin_keyword
                ],
                [
                    "name" => "revisit-after",
                    "content" => "1 days"
                ],
                [
                    "name" => "og:url",
                    "content" => "http://nhazi.com/link/" . $slug
                ],
                [
                    "name" => "DC.language",
                    "content" => "scheme=utf-8 content=vi"
                ],
                [
                    "name" => "robots",
                    "content" => "index, follow"
                ]
            ];
            //Create breadcrumb
            if ($link->lin_parentid != 0) {
                $arr_all_parent_id = explode(',', $link->lin_all_parent);
                $breadcrumb = Link::select('lin_title as title', 'lin_rewrite as rewrite')->whereIn('lin_id', $arr_all_parent_id)->get()->push(['title' => $link->lin_title, 'rewrite' => '#'])->toArray();
            } else {
                $breadcrumb = [['title' => $link->lin_title, 'rewrite' => '#']];
            }

            $category = ['title' => $link->lin_title, 'rewrite' => $link->lin_rewrite == null ? '#' : $link->lin_rewrite];
            //Return response
            return $this->setResponse(200, ['breadcrumb' => $breadcrumb, 'category' => $category, 'title' => $link->lin_title, 'meta' => $meta, 'classifieds' => $classifieds]);
        } else {
            return $this->setResponse(404, null, 'Not found link');
        }

    }

    //

    /**
     *
     *
     * Lấy danh sách tin tức liên quan  theo chuyên mục
     *
     */
    function getRelatedClassifieds($slug)
    {
        $slug = urldecode($slug);
        $sphinx = app()->make('sphinx');
        $slug_md5 = md5($slug);
        //Check slug
        $link = Link::select('lin_id', 'lin_title', 'lin_short_title', 'lin_what', 'lin_catid', 'lin_citid', 'lin_disid', 'lin_wardid', 'lin_streetid', 'lin_projid', 'lin_all_parent', 'lin_parentid', 'lin_keyword')->where('lin_length', strlen($slug))->where('lin_md5', $slug_md5)->first();
        if ($link) {
            // Get cla_id limit
            $arr_cla_id_limit = $sphinx->select('id')->from('classifieds')->where('cla_cat_id', '=', $link->lin_catid)->where('cla_active', 1);
            if ($link->lin_citid != 0) $arr_cla_id_limit->where('cla_citid', $link->lin_citid);
            if ($link->lin_disid != 0) $arr_cla_id_limit->where('cla_disid', $link->lin_disid);
            if ($link->lin_warid != 0) $arr_cla_id_limit->where('cla_wardid', $link->lin_warid);
            if ($link->lin_streetid != 0) $arr_cla_id_limit->where('cla_streetid', $link->lin_streetid);
            if ($link->lin_projid != 0) $arr_cla_id_limit->where('cla_projid', $link->lin_projid);
            $arr_cla_id_limit->orderBy('id', 'desc');
            $arr_cla_id_limit = $arr_cla_id_limit->limit(20)->execute()->fetchAllAssoc();
            $arr_cla_id_limit = collect($arr_cla_id_limit)->map(function ($item) {
                return (int)$item['id'];
            })->toArray();
            // Get link related
            $link_related = Link::select('lin_catid as cat_id')->whereNotIn('lin_id', [$link->lin_id])->whereRaw("(lin_parentid = " . $link->lin_id . " OR lin_parentid = " . $link->lin_parentid . ")")->groupBy('lin_catid')->get();
            if ($link_related) {
                $arr_cat_id = $link_related->map(function ($item) {
                    return (int)$item['cat_id'];
                })->toArray();

                //Get cla_id with condition not  cla_limit
                $arr_cla_id = $sphinx->select('id')->from('classifieds')
                    ->where('cla_cat_id', 'IN', $arr_cat_id)
                    ->where('cla_cat_id', 'NOT IN', $arr_cla_id_limit)
                    ->where('cla_active', 1)
                    ->orderBy('id', 'desc')
                    ->limit(20)->execute()->fetchAllAssoc();

                //Get classifieds
                if(count($arr_cla_id)==0) {
                    return $this->setResponse(200, []);
                }
                $classifieds = Classified::select('cla_id', 'cla_title', 'cla_disid', 'cla_rewrite', 'cla_date', 'cla_has_picture', 'cla_picture', 'cla_description', 'cla_price', 'cla_list_acreage')
                    ->whereIn('cla_id', $arr_cla_id)
                    ->orderBy('cla_id', 'desc')->limit(20)->get();
                //Filter classifieds and response
                $classifieds = $this->filterClassifieds($classifieds);
                return $this->setResponse(200, $classifieds);
            }
            return $this->setResponse(200, []);
        } else {
            return $this->setResponse(404, null, 'Not found link');
        }
    }
}
