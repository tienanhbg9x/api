<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Project;
use App\Models\ProjectCompare;
use App\Models\Rewrite;
use Illuminate\Http\Request;

/**
 * @resource v2 Projects-compare
 *
 * Api for Projects-compare
 */
class ProjectCompareController extends Controller
{


    /**
     * GET v2/projects-compare
     *
     * Cho phép trả về danh sách các bản ghi cơ sở dữ liệu.
     *
     * Bạn cần lưu ý rằng hệ thộng mặc định chỉ trả về 30 bản ghi, nếu muốn lấy nhiều hơn bạn có thể truyền thêm các tham số.
     *
     * ### Thông tin cơ bản:
     * Info route | Description
     * --------- | -------
     * `version_route` | Version v2
     * `controller` | ProjectCompareController
     * `route_name` | projects-compare
     *
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Default | Mô tả chi tiết
     * --------- | ------- | -------
     * `@fields` | NULL | Danh sách các trường dữ liệu projects-compare ví dụ (id,name,v..v.v..), nếu không truyền tham số này thì sẽ trả về 1 số trường mặc định.
     * `@orderby` | NULL | Trường ưu tiên sắp xếp trong projects-compare ví dụ (date), nếu không truyền tham số này thì sẽ sắp xếp mặc định.
     * `@limit` | NULL | Số lượng bản ghi projects-compare cần lấy ra, nếu không truyền tham số này thì sẽ mặc định là 30 bản ghi.
     * `@page` | NULL | Số thứ tự trang projects-compare cần lấy ra, nếu không truyền tham số này thì sẽ mặc định là trang 1.
     * `@ssr` | boolean | Chế độ gọi api cho web .  Mặc định là `false`
     *
     * ### Tùy chọn với tham số `where`:
     *  `where={name_column}+({type_value}){value}`
     *
     *  Giá trị (Value) |  Mô tả chi tiết
     * ----------------  |  -------
     * `name_column` | Tên trường muốn gán điều kiện
     * `type_value` |  Khai báo kiểu giá trị cho value(tham số này có hoặc không),  hỗ trợ kiểu giá trị là `float`. Mặc định là  `int`
     * `value` | Giá trị cần tham chiếu
     *
     * - Có gán kiểu giá trị:    `where=id+(float)234.56,active+0`
     * @return \Illuminate\Http\Response
     */
    function index(Request $request)
    {
        $this->validate($request, [
            'type' => 'string',
            'fiedls' => 'string|min:1|max:255',
            'slug' => 'string|min:1|max:255',
            'page' => 'numeric|min:1',
            'limit' => 'numeric|min:1',
            'keyword' => 'string|max:255|min:1',
            'where' => 'string|max:255|min:3',
            'ssr' =>'boolean'
        ]);
        if (isset($_REQUEST['type'])) {
            if ($_REQUEST['type'] == 'detail') {
                if (isset($_REQUEST['slug'])) {
                    return $this->getProjectCompare();
                } else {
                    return $this->setResponse(404, null, 'Not found param `slug`');
                }
            }
        }
        $projects = new ProjectCompare();
        $projects = $projects->select($projects->alias($this->fields));
        $offset = $this->page * $this->limit - $this->limit;
        if ($this->where != null) $projects = whereRawQueryBuilder($this->where, $projects, 'mysql', 'projects_compare');
        $projects = $projects->orderBy('prc_id', 'desc')->offset($offset)->limit($this->limit)->get();
        $projects = $this->filterProjectsCompare($projects);
        return $this->setResponse(200, $projects);
    }


    /**
     * GET v2/projects-compare
     *
     * Mô tả chức năng hàm
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@id` | ID projects-compare
     * `@fields` | List fields projects-compare
     * @return \Illuminate\Http\Response
     */
    function show($id)
    {
        $project = new ProjectCompare();
        $project = $project->select($project->alias($this->fields))->where('prc_id', $id);
        if ($this->where != null) $project = whereRawQueryBuilder($this->where, $project, 'mysql', 'projects_compare');
        $project = $project->first();
        if (!$project) {
            return $this->setResponse(404, null, "Not found data!");
        }
        if (isset($project->picture)) {
            $project->picture = $project->picture != null || $project->picture != "" ? getUrlPicture($project->picture, 150) : [config('app.thumbnail_default')];
        }
        return $this->setResponse(200, $project);
    }

    function getProjectCompare($rewrite = null)
    {
        $slug = urldecode($_REQUEST['slug']);
        if ($rewrite == null) {
            $slug_md5 = md5($slug);
            $rewrite = Rewrite::select('rew_rewrite', 'rew_title', 'rew_param','rew_description','rew_keyword','rew_picture')->where('rew_length', strlen($slug))->where('rew_md5', $slug_md5)->first();
        }
        if ($rewrite) {
            $rewrite->rew_param = bdsDecode($rewrite->rew_param);
            if(!isset($rewrite->rew_param['iMin'])){
                return $this->setResponse(404, null, 'Not found slug');
            }
            $projects = new Project();
            $idMin = $rewrite->rew_param['iMin'];
            $idMax = $rewrite->rew_param['iMax'];
            $projects = $projects->select($projects->alias())->whereIn('proj_id', [$idMin, $idMax])->get();
            $project_controller = new ProjectController();
            $projects = $projects->map(function ($project) use ($project_controller) {
                $project = $project_controller->addDescriptionProject($project);
                return $project;
            });
//            //Create meta tag
//            $meta = [];
//            if ($rewrite->rew_title) {
//                $meta['title'] = $rewrite->rew_title;
//                $meta['og:title'] = $rewrite->rew_title;
//            }
//
//            if ($rewrite->rew_description) {
//                $meta['description'] = mb_substr($rewrite->rew_description, 0, 200);
//                $meta['og:description'] = mb_substr($rewrite->rew_description, 0, 200);
//            }
//
//            if ($rewrite->rew_picture) {
//                $meta['og:image'] = $rewrite->rew_picture['full'][0];
//            }
//
////            if ($project_description->long_keyword) $meta['keywords'] = $project_description->long_keyword;
//
//            $meta['revisit-after'] = "1 days";
//            $meta['og:url'] = env('APP_DOMAIN') . '/' . $slug;
//            $meta['DC.language'] = "scheme=utf-8 content=vi";
//            $meta['robots'] = "index, follow";
//            $meta = createMeta($meta);
            $data = ['projects' => $projects, 'type_page' => 'projects_compare'];
            return $this->setResponse(200, $data);
        }
        return $this->setResponse(404, null, 'Not found slug');
    }

    function filterProjectsCompare($items)
    {
        $items = $items->map(function ($item) {
            if (isset($item->picture)) {
                $pictures = bdsDecode($item->picture);
                foreach ($pictures as $key=>$picture){
                    $pictures[$key]['url'] = getUrlPicture($picture['filename'],150,true);
                }
                $item->picture = $pictures;
//                $item->picture = $item->picture != null || $item->picture != "" ? getUrlPicture($item->picture, 150) : [config('app.thumbnail_default')];
            }
            return $item;
        });
        return $items;
    }

}
