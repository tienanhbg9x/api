<?php

namespace App\Http\Controllers\Api\V2;


use App\Http\Controllers\Api\V2\Controller;
use App\Models\Investor;
use App\Models\Project;
use App\Models\ProjectDescription;
use App\Models\Rewrite;
use Illuminate\Http\Request;

/**
 * @resource V2 Projects
 *
 * Api for page Project
 */
class ProjectController extends Controller
{

    protected $request = null;
    protected $fields_order = ['id','update'];

    /**
     * GET v2/projects
     *
     * Cho phép trả về danh sách các bản ghi cơ sở dữ liệu.
     *
     * Bạn cần lưu ý rằng hệ thộng mặc định chỉ trả về 30 bản ghi, nếu muốn lấy nhiều hơn bạn có thể truyền thêm các tham số.
     *
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Default | Mô tả chi tiết
     * --------- | ------- | -------
     * `@limit` | 30 |  Giới hạn bảng ghi lấy về
     * `@fiedls` | null | Trường dữ liệu muốn lấy (id,name,...)
     * `@type` | null |  Tùy chọn kiểu lấy dữ liệu
     * `@slug` | null | Giá trị của trường `rewrite` của `project`. Để  sử dụng được bắt buộc phải có `type=detail` đi kèm theo.
     * `@page` | 1 | Phân trang
     * `@orderBy` | 1 | Sắp xếp theo một trường(id,date)
     * `@keyword` | null | Từ khóa tìm kiếm
     *
     * ### Tùy chọn với tham số `where`:
     *  `where={name_column}+{value}`
     *
     *  Giá trị (Value) |  Mô tả chi tiết
     * ----------------  |  -------
     * `name_column` | Tên trường muốn gán điều kiện
     * `type_value` |  Khai báo kiểu giá trị cho value(tham số này có hoặc không),  hỗ trợ kiểu giá trị là `float`. Mặc định là  `int`
     * `value` | Giá trị cần tham chiếu
     *
     * - Có gán kiểu giá trị:    `where=id+234.56,active+0`
     *
     *
     *
     * ### Tùy chọn với tham số `type`:
     *  Giá trị (Value) | Điều kiện | Mô tả chi tiết
     * --------- | ------- | -------
     * `detail` | (trả về thông tin chi tiết của một dự án ) với điều kiện có tham số `slug` đi kèm .
     * `search` | Tìm kiếm một dự án theo từ khóa với điều kiện có tham số `keyword` đi kèm
     * @return \Illuminate\Http\Response
     */
    function index(Request $request)
    {
        $this->validate($request, [
            'fields' => 'string|max:255',
            'limit' => 'numeric',
            'page' => 'numeric',
            'type' => 'string',
            'slug' => 'string|max:255|min:1',
            'where' => 'string|max:255|min:3',
            'keyword' => 'string',
            'ssr' => 'boolean'
        ]);
        $this->request = $request;
        $field_order = $request->input('orderBy')?(in_array($request->orderBy,$this->fields_order)?'proj_'.$request->orderBy:'proj_id'):'proj_id';
        if ($request->input('type') == 'detail') {
            if ($request->input('slug') != null) {
                return $this->getProject();
            } else {
                return $this->setResponse(404, null, 'Not found param `slug`');
            }

        } else if ($request->input('type') == 'search') {
            if ($request->input('keyword') != null) {
                if(strlen($request->input('keyword'))<3){
                    return $this->setResponse(403, null, 'Từ khóa tìm kiếm quá ngắn!');
                }
                $data_search =  $this->searchProject($request->input('keyword'));
                return $this->setResponse(200,$data_search);
            } else {
                return $this->setResponse(404, null, 'Not found param `keyword`');
            }
        }
        $projects = new Project();
        $offset = $this->page * $this->limit - $this->limit;
        $projects = $projects->select($projects->alias($this->fields))->whereRaw(\DB::raw('proj_rewrite IS NOT null'));
        if ($this->where != null) $projects = whereRawQueryBuilder($this->where, $projects, 'mysql', 'projects');
        $projects = $projects->orderBy($field_order, 'desc')->offset($offset)->limit($this->limit)->get();
        $projects = $this->filterProject($projects);
        return $this->setResponse(200, $projects);
    }

    /**
     * GET v2/projects{id}
     *
     *  Lấy dự án  theo id
     * ### Thông số lấy dữ liệu:
     * Trường dữ liệu (Param) | Mô tả chi tiết
     * --------- | -------
     * `@id` | ID projects
     * `@fields` | Trường dữ liệu muốn lấy (id,name,...)
     * @return \Illuminate\Http\Response
     */
    function show($id)
    {
        $project = new Project();
        $project = $project->select($project->alias($this->fields))->where('proj_id', $id)->first();
        if ($project) {
            $project->picture = $project->picture != '' ? getUrlPicture($project->picture) : config('app.thumbnail_default');
            return $this->setResponse(200, $project);
        }
        return $this->setResponse(404, null, 'Not found data!');
    }

    function getProject($rewrite = null)
    {
        $slug = urldecode($_REQUEST['slug']);
        if ($rewrite == null) {
            $slug_md5 = md5($slug);
            $rewrite = Rewrite::select('rew_title', 'rew_proj_id')->where('rew_length', strlen($slug))->where('rew_md5', $slug_md5)->first();
        }
        $project = new Project();
        $project = $project->select($project->alias($this->fields))->where('proj_id', $rewrite->rew_proj_id)->first();
        if ($project) {
            $project = $this->addDescriptionProject($project);
//            //Create meta tag
//            $meta = [];
//            if ($project->title) {
//                $meta['title'] = $project->title;
//                $meta['og:title'] = $project->title;
//            }
//
//            if ($project->teaser) {
//                $meta['description'] = mb_substr($project->teaser, 0, 200);
//                $meta['og:description'] = mb_substr($project->teaser, 0, 200);
//            }
//
//            if ($project->picture) {
//                $meta['og:image'] = $project->picture['full'][0];
//            }
//
////            if ($project_description->long_keyword) $meta['keywords'] = $project_description->long_keyword;
//
//            $meta['revisit-after'] = "1 days";
//            $meta['og:url'] = env('APP_DOMAIN') . '/' . $slug;
//            $meta['DC.language'] = "scheme=utf-8 content=vi";
//            $meta['robots'] = "index, follow";
//            $meta = createMeta($meta);

            $data = ['project' => $project, 'type_page' => 'project'];
            return $this->setResponse(200, $data);
        }
        return $this->setResponse(404, null, 'Not found data!');
    }

    function addDescriptionProject($project)
    {
        if ($project->picture) {
            $project->picture = $project->picture != '' ? getUrlPictures($project->picture, 150) : ['full' => config('app.thumbnail_default')];
        }
        $project_description = new ProjectDescription();
        $project_description = $project_description->select($project_description->alias())->where('proj_proj_id', $project->id)->first();
        if ($project_description) {
            $project->intro = $project_description->intro;
            $project->position = $project_description->position;
            $project->infrastructure = $project_description->infrastructure;
            $project->template = $project_description->template != '' ? getUrlPictures($project_description->template) : ['count_image' => 0];
        }
        if ($project->inv_id) {
            $investor = new Investor();
            $investor = $investor->select($investor->alias('name,phone,picture,description,email,website,address'))->where('inv_id', $project->inv_id)->first();
            if ($investor) {
                $investor->picture = $investor->picture != null || $investor->picture != '' ? getUrlPictureInvestor($investor->picture, 0, true) : config('app.thumbnail_default');
                $project->investor = $investor;
            }
        }
        return $project;
    }

    function filterProject($projects)
    {
        $projects = $projects->map(function ($item) {
            if ($item->picture) {
                $pictures  = bdsDecode($item->picture);
                foreach ($pictures as $key=>$item_image){
                    $pictures[$key]['url'] =  getUrlImageByRow($item_image);
                }
                $item->picture = $pictures;
//                $item->picture = $item->picture != '' ? getUrlPicture($item->picture) : config('app.thumbnail_default');
            }
            if ($item->address) {
                $item->address = showAddresFromListv2($item->address);
            }
            return $item;
        });
        return $projects;
    }


    function searchProject($keyword)
    {
        $keyword =  trim(urldecode($keyword));
        $keyword = BuildPhraseTrigrams($keyword);
        $terms = filterWhereElasticssearch($this->where);
        $index = "projects";
        $fields = ["name2"];
        $source = [ 'id','name','inv_name','inv_id','title','rewrite','address','picture','video','distribution','construction','price_from','lat','lng','cats','active','cron','size','teaser','cat_name','overview','cat_id','dis_id','cit_id','ward_id','street_id'];
        if($this->fields!=''){
            $source = explode(',',$this->fields);
        }
        $params = null;
        if ($terms != null) {
            $arr_terms = [];
            foreach ($terms as $key => $term) {
                $arr_terms[] = [
                    "terms" => [
                        $key => [$term]
                    ]
                ];
            }
            $params = [
                'index' => $index,
                'type' => '_doc',
                'body' => [
                    "query" => [
                        "function_score" => [
                            "query" => [
                                "bool" => [
                                    "must" => [
                                        [
                                            "simple_query_string" => [
                                                "fields" => $fields,
                                                "query" => $keyword
                                            ]
                                        ],
                                        $arr_terms
                                    ]
                                ]
                            ]
                        ]
                    ],
                    "_source" => $source
                ]
            ];
        } else {
            $params = [
                'index' => $index,
                'type' => '_doc',
                'body' => [
                    "query" => [
                        "function_score" => [
                            "query" => [
                                "bool" => [
                                    "must" => [
                                        [
                                            "simple_query_string" => [
                                                "fields" => $fields,
                                                "query" => $keyword
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    "_source" => $source
                ]
            ];
        }

        $data_search = app('elastic')->search($params);
        $value =[];
        foreach ($data_search['hits']['hits'] as $key=>$data){
            array_push($value, $data['_source']);
        }
        return $value;
    }

    function getListProject(array $filters = []){
        $projects = new Project();
        $offset = $this->page * $this->limit - $this->limit;
        $projects = $projects->select($projects->alias($this->fields))->whereRaw(\DB::raw('proj_rewrite IS NOT null'));
        foreach ($filters as $key=>$value){
            $projects = $projects->where($key,$value);
        }

        $projects = $projects->orderBy('proj_update', 'desc')->offset($offset)->limit($this->limit)->get();
        $projects = $this->filterProject($projects);
        return $this->setResponse(200, ['type_page'=>'projects','projects'=>$projects]);
    }

}
