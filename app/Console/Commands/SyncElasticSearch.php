<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Classified;
use App\Models\GeoLocation;
use App\Models\Project;
use App\Models\Rewrite;
use App\Models\RewriteNoaccent;
use App\Models\Theme;
use App\Models\UserCustomer;
use Illuminate\Console\Command;
use Elasticsearch\ClientBuilder;
use App\Models\Location;
use App\Models\Address;

class SyncElasticSearch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elastic:sync {option} {index_name?}';
    private $index_name = null;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync data from mysql to elasticseach ( \'index\',\'update\',\'index_mapping\') (\'location\', \'address\', \'projects\', \'bds_classifieds\', \'themes\', \'geolocation\', \'categories_multi\', \'user_customers\', "rewrites")';

    protected $arr_index = ['location', 'address', 'projects', 'bds_classifieds', 'themes', 'geolocation', 'categories_multi', 'user_customers', "rewrites"];
    protected $arr_option = ['index','update','index_mapping'];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
//        $this->indexGeoLocationV2();
//        dd('ok');
        $option = $this->argument('option');
        if ( in_array($option,$this->arr_option)) {
            $this->indexDocument($option);
        } else {
            $this->info("Not thing to do!!!");
        }
    }

    function indexDocument($type = 'index')
    {
        $this->index_name = $this->argument('index_name');
        if (!in_array($this->index_name, $this->arr_index)) {
            $this->warn('Not found index' . $this->index_name);
            return false;
        }
        if ($this->index_name == null) return $this->warn("Index name empty!!!");
        if ($this->checkIndex($type)) {
            if ($this->index_name == "location") $this->indexLocations();
            if ($this->index_name == "address") $this->indexAddress();
            if ($this->index_name == "projects") $this->indexProject();
            if ($this->index_name == "bds_classifieds") $this->indexClassifieds();
            if ($this->index_name == "themes") $this->indexThemes();
            if ($this->index_name == "rewrites") $this->indexRewrite();
            if ($this->index_name == "geolocation") $this->indexGeoLocationV2();
            if ($this->index_name == "categories_multi") $this->indexCategories();
            if ($this->index_name == "user_customers") $this->indexUserCustomer();
        }
        $this->info("insert finish!!!");

    }

    function indexRewrite()
    {
        $page = 1;
        $this->info("Inserting Rewrite....");
        $params = ['body' => []];
        for (; ;) {
            $offset = $page * 1000 - 1000;
            $myModel = new Rewrite();
            $myModel = $myModel->select($myModel->alias())->offset($offset)->limit(1000)->get();
            if ($myModel->count() == 0) break;
            foreach ($myModel as $data) {
                $params['body'][] = [
                    'index' => [
                        '_index' => 'rewrites',
                        '_type' => '_doc',
                        '_id' => $data->id
                    ]
                ];
                $data = $data->toArray();
                $data["search"] = trim(createTextSearchNgram($data['title']));
                $params['body'][] = $data;
            }
            $responses = app('elastic')->bulk($params);
            $params = ['body' => []];
            unset($responses);
            $this->info($offset);
            $page++;
        }

        $page = 1;
        $this->info("Inserting RewriteNoaccent....");
        $params = ['body' => []];
        for (; ;) {
            $offset = $page * 1000 - 1000;
            $myModel = new RewriteNoaccent();
            $myModel = $myModel->select($myModel->alias())->offset($offset)->limit(1000)->get();
            if ($myModel->count() == 0) break;
            foreach ($myModel as $data) {
                $params['body'][] = [
                    'index' => [
                        '_index' => 'rewrites',
                        '_type' => '_doc',
                        '_id' => $data->id
                    ]
                ];
                $data = $data->toArray();
                $data["search"] = trim(createTextSearchNgram($data['title']));
                $params['body'][] = $data;
            }
            $responses = app('elastic')->bulk($params);
            $params = ['body' => []];
            unset($responses);
            $this->info($offset);
            $page++;
        }
    }

    function indexLocations()
    {
        $page = 1;
        $this->info("Inserting....");
        $params = ['body' => []];
        for (; ;) {
            $offset = $page * 1000 - 1000;
            $locations = new Location();
            $locations = $locations->select($locations->alias())->whereNotIn('loc_name', [""])->offset($offset)->limit(1000)->get();
            if ($locations->count() == 0) break;
            foreach ($locations as $data) {
                $params['body'][] = [
                    'index' => [
                        '_index' => 'location',
                        '_type' => '_doc',
                        '_id' => $data->id
                    ]
                ];
                $data = $data->toArray();
                foreach ($data as $key => $val) $data[$key] = convertToUnicode($val);
                $data["full_name"] = trim($data["pre"] . " " . $data["name"]);
                $data["name1"] = BuildTrigrams($data['name']);
                $data["name2"] = BuildPhraseTrigrams($data['name']);
                $data["name_search"] = trim(createTextSearchNgram($data['name']));
                $data["search"] = trim(createTextSearchNgram($data['address']) . " " . createTextSearchNgram($data['keyword']));
                $params['body'][] = $data;
            }
            $responses = app('elastic')->bulk($params);
            $params = ['body' => []];
            unset($responses);
            $this->info($offset);
            $page++;
        }
    }

    function indexClassifieds()
    {
        $page = 1;
        $this->info("Inserting....");
        $arrayCategory = [];
        $arrayProject = [];
        $dateIndex = time() - 86400*200;
        $query = new Category();
        $query = $query->select(["cat_id", "cat_type", "cat_name"])->get();
        foreach ($query as $row) {
            $arrayCategory[$row->cat_id] = $row->toArray();
        }
        $query = new Project();
        $query = $query->select(["proj_id", "proj_title"])->get();
        foreach ($query as $row) {
            $arrayProject[$row->proj_id] = $row->toArray();
        }
        unset($query);

        $fields = "cla_id as id,cla_id,cla_address,cla_mobile,cla_phone,cla_email,cla_contact_name,cla_rewrite,cla_teaser,cla_title,cla_cat_id,cla_cit_id,cla_dis_id,cla_ward_id,cla_street_id,cla_proj_id,cla_date,cla_expire,cla_use_id,cla_vg_id,cla_fields_check,cla_active,cla_type,cla_type_vip,cla_price,cla_lat,cla_lng,cla_list_acreage,cla_list_price,cla_list_badroom,cla_list_toilet,cla_picture,cla_has_picture,cla_citid,cla_disid,cla_wardid,cla_streetid,cla_cat_prentid,cla_has_video";
        $fields = explode(",", $fields);
        $params = ['body' => []];
        $last_id = 0;
        for (; ;) {
            $offset = $page * 2000 - 2000;
            $locations = new Classified();
            $locations = $locations->select($fields)->where("cla_id", '>', $last_id)->where("cla_date",'>',$dateIndex)->orderBy("cla_id", "ASC")->limit(2000)->get();
            if ($locations->count() == 0) break;
            foreach ($locations as $data) {
                $params['body'][] = [
                    'index' => [
                        '_index' => 'bds_classifieds',
                        '_type' => 'classifieds',
                        '_id' => $data->id
                    ]
                ];
                $last_id = $data->id;
                $row = $data->toArray();
                $row["cla_order"] = $row["cla_date"] + $row["cla_date"] * $row["cla_type_vip"];
                $row["cla_title"] = Spinner($row["cla_title"]);
                $row["cla_list_acreage"] = convertListFloatToArray($row["cla_list_acreage"]);
                $row["cla_list_price"] = convertListFloatToArray($row["cla_list_price"]);
                $row["cla_list_badroom"] = convertListIntToArray($row["cla_list_badroom"]);
                $row["cla_list_toilet"] = convertListIntToArray($row["cla_list_toilet"]);
                if (isset($arrayCategory[$row["cla_cat_id"]])) $row = array_merge($row, $arrayCategory[$row["cla_cat_id"]]);
                if (isset($arrayProject[$row["cla_cat_id"]])) $row = array_merge($row, $arrayProject[$row["cla_cat_id"]]);
                //print_r($row);exit();
                foreach ($row as $k => $v){
                    if(is_numeric($v)){
                        $row[$k] = doubleval($v);
                    }
                }
                $params['body'][] = $row;
            }
            $responses = app('elastic')->bulk($params);
            $params = ['body' => []];
            unset($responses);
            $this->info($offset);
            $page++;
        }
    }

    function indexAddress()
    {
        $page = 1;
        $this->info("Inserting....");
        $params = ['body' => []];
        for (; ;) {
            $offset = $page * 1000 - 1000;
            $address = new Address();
            $address = $address->select($address->alias(null))->offset($offset)->limit(1000)->get();
            if ($address->count() == 0) break;

            foreach ($address as $data) {

                $params['body'][] = [
                    'index' => [
                        '_index' => 'address',
                        '_type' => '_doc',
                        '_id' => $data->id
                    ]
                ];
                try{
                    $geo_location = json_decode($data->geometry,true);

                    $data = $data->toArray();
                    foreach ($data as $key => $val) $data[$key] = convertToUnicode($val);
                    $data["full_name"] = trim($data["pre"] . " " . $data["name"]);
                    $data["name1"] = BuildTrigrams($data['name']);
                    $data["name2"] = BuildPhraseTrigrams($data['name']);
                    $data["name_search"] = trim(createTextSearchNgram($data['name']));
                    if(!empty($geo_location)) {
                        $location = $geo_location['location'];
                        $coords = [];
                        $startpoint = [];
                        $endpoint   = [];
                        $coords[] = ["lat" => $location['lat'], "lon" => isset($location['lng'])?$location['lng']:$location['lon']];
                        $data["location"] =  [["lat" => $location['lat'], "lon" => isset($location['lng'])?$location['lng']:$location['lon']]];
                        if(isset($geo_location['viewport'])){
                            $viewport = $geo_location['viewport'];
                            if(is_array($viewport)){
                                foreach($viewport as $kname => $latlon){
                                    if(isset($latlon["lon"])) $latlon["lng"] = $latlon["lon"];
                                    $data["location"][] =  ["lat" => $latlon['lat'], "lon" =>$latlon["lng"]];
                                    $coords[] = ["lat" => $latlon['lat'], "lon" =>$latlon["lng"]];
                                    if($kname == "northeast") $startpoint = ["lat" => $latlon['lat'], "lon" =>$latlon["lng"]];
                                    if($kname == "southwest") $endpoint = ["lat" => $latlon['lat'], "lon" =>$latlon["lng"]];
                                }
                            }
                        }
                        //print_r($coords);
                        //*
                        if(count($data["location"]) > 1){
                            $center = getCenter($data["location"]);
                            $data["location"][] = $center;
                            if(!empty($startpoint)) $data["location"][] = getCenter([$startpoint,$center]);
                            if(!empty($endpoint)) $data["location"][] = getCenter([$endpoint,$center]);

                            print_r($data["location"]);
                        }
                        //*/
                    }
                    $data["search"] = trim(createTextSearchNgram($data['address']) . " " . createTextSearchNgram($data['keyword']));
                    $params['body'][] = $data;
                }catch (\Exception $e){
                    dd($e->getMessage());
                    dd($data['id']);
                }

            }
            $responses = app('elastic')->bulk($params);
            $params = ['body' => []];
            unset($responses);
            $this->info($offset);
            $page++;
        }
    }

    function indexCategories()
    {
        $page = 1;
        $this->info("Inserting....");
        $params = ['body' => []];
        for (; ;) {
            $offset = $page * 1000 - 1000;
            $projects = new Category();
            $projects = $projects->select($projects->alias())->offset($offset)->limit(1000)->get();
            if ($projects->count() == 0) break;
            foreach ($projects as $data) {
                $params['body'][] = [
                    'index' => [
                        '_index' => 'categories_multi',
                        '_type' => '_doc',
                        '_id' => $data->id
                    ]
                ];
                $row = $data->toArray();
                $row["search"] = createTextSearchNgram($data['name']) . " " . createTextSearchNgram(removeAccent($data['name']));
                $params['body'][] = $row;
            }
            $responses = app('elastic')->bulk($params);
            $params = ['body' => []];
            unset($responses);
            $this->info($offset);
            $page++;
        }
    }

    function indexProject()
    {
        $page = 1;
        $this->info("Inserting....");
        $params = ['body' => []];
        for (; ;) {
            $offset = $page * 1000 - 1000;
            $projects = new Project();
            $projects = $projects->select($projects->alias())->offset($offset)->limit(1000)->get();
            if ($projects->count() == 0) break;
            foreach ($projects as $data) {
                $params['body'][] = [
                    'index' => [
                        '_index' => 'projects',
                        '_type' => '_doc',
                        '_id' => $data->id
                    ]
                ];
                $data = $data->toArray();
                $data["name1"] = BuildTrigrams($data['name']);
                $data["name2"] = BuildPhraseTrigrams($data['name']);
                $params['body'][] = $data;
            }
            $responses = app('elastic')->bulk($params);
            $params = ['body' => []];
            unset($responses);
            $this->info($offset);
            $page++;
        }
    }

    function indexThemes()
    {
        $page = 1;
        $this->info("Inserting....");
        $params = ['body' => []];
        for (; ;) {
            $offset = $page * 1000 - 1000;
            $themes = new Theme();
            $themes = $themes->select($themes->alias())->offset($offset)->limit(1000)->get();
            if ($themes->count() == 0) break;
            foreach ($themes as $data) {
                $params['body'][] = [
                    'index' => [
                        '_index' => 'themes',
                        '_type' => '_doc',
                        '_id' => $data->id
                    ]
                ];
                $data_return = $data->toArray();
                unset($data_return['source']);
                unset($data_return['html_path']);
                $data_return["name1"] = BuildTrigrams($data_return['name']);
                $data_return["name2"] = BuildPhraseTrigrams($data_return['name']);
                $params['body'][] = $data_return;
            }
            $responses = app('elastic')->bulk($params);
            $params = ['body' => []];
            unset($responses);
            $this->info($offset);
            $page++;
        }
    }

    function indexUserCustomer()
    {
        $page = 1;
        $this->info("Inserting....");
        $params = ['body' => []];
        for (; ;) {
            $offset = $page * 1000 - 1000;
            $user_customers = new UserCustomer();
            $user_customers = $user_customers->select($user_customers->alias())->offset($offset)->limit(1000)->get();
            if ($user_customers->count() == 0) break;
            foreach ($user_customers as $data) {
                $params['body'][] = [
                    'index' => [
                        '_index' => 'user_customers',
                        '_type' => '_doc',
                        '_id' => $data->id
                    ]
                ];
                $data_return = $data->toArray();
                $data_return["name1"] = BuildTrigrams($data_return['name']);
                $data_return["name2"] = BuildPhraseTrigrams($data_return['name']);
                $params['body'][] = $data_return;
            }
            $responses = app('elastic')->bulk($params);
            $params = ['body' => []];
            unset($responses);
            $this->info($offset);
            $page++;
        }
    }

    function indexGeoLocationV2()
    {
        $page = 1;
        $params = ['body' => []];
        for (; ;) {
            $offset = $page * 2000 - 2000;
            $query = new GeoLocation();
            $locations = $query->select($query->alias())->offset($offset)->limit(2000)->get();
            if ($locations->count() == 0) break;
            foreach ($locations as $data) {
                $params['body'][] = [
                    'index' => [
                        '_index' => 'geolocation',
                        '_type' => '_doc',
                        '_id' => $data->id
                    ]
                ];
                $data = $data->toArray();
                foreach ($data as $key => $val) $data[$key] = convertToUnicode($val);
                $data["location"] =  ["lat" => floatval($data["lat"]), "lon" => floatval($data["lng"])];
                $data["address"] = $query->formatCoccocAddress($data["address"]);
                $params['body'][] = $data;
            }
            $responses = app('elastic')->bulk($params);
            $params = ['body' => []];
            unset($responses);
            $this->info($offset);
            $page++;
        }
    }

//    function indexGeoLocation()
//    {
//        $params = [
//            'index' => 'geolocation',
//            'body' => [
//                'settings' => [
//                    'number_of_shards' => 15,
//                    'number_of_replicas' => 2
//                ]
//            ]
//        ];
//
//        //$response =  app('elastic')->indices()->delete($params);
//        app('elastic')->indices()->delete(['index' => $this->index_name]);
//        $response = app('elastic')->indices()->create($params);
//
//        $params = [];
//        // Set the index and type
//        $params['index'] = 'geolocation';
//        $params['type'] = 'location';
//
//        // Adding a new type to an existing index
//        $myTypeMapping2 = array(
//            'properties' => array(
//                'first_name' => array(
//                    'type' => 'string',
//                    'analyzer' => 'standard'
//                ),
//                'age' => array(
//                    'type' => 'integer'
//                )
//            )
//        );
//        $params['body']['location'] = $myTypeMapping2;
//
//        // Update the index mapping
//        app('elastic')->indices()->putMapping($params);
//
//        $this->info("Reset data index: indexGeoLocation");
//        //exit();
//        $page = 1;
//        $this->info("Inserting....");
//        //$params = ['body' => []];
//        //$params["body"]["mappings"] = $myTypeMapping;
//        for (; ;) {
//            $offset = $page * 1000 - 1000;
//            $query = new GeoLocation();
//            $locations = $query->select($query->alias())->offset($offset)->limit(1000)->get();
//            if ($locations->count() == 0) break;
//            foreach ($locations as $data) {
//                $params['body'][] = [
//                    'index' => [
//                        '_index' => 'geolocation',
//                        '_type' => 'location',
//                        '_id' => $data->id
//                    ]
//                ];
//                $data = $data->toArray();
//                foreach ($data as $key => $val) $data[$key] = convertToUnicode($val);
//                $data["location"] = ["lat" => floatval($data["lat"]), "lon" => floatval($data["lng"])];
//                $data["address"] = $query->formatCoccocAddress($data["address"]);
//                dd($data);
//                exit();
//                $params['body'][] = $data;
//            }
//            $responses = app('elastic')->bulk($params);
//            $params = ['body' => []];
//            unset($responses);
//            $this->info($offset);
//            $page++;
//        }
//    }

    function checkIndex($type = 'index')
    {
        if(($type=='index'||$type=='index_mapping')){
            if(!$this->confirm('Tất cả dữ liệu sẽ bị xóa! Bạn có muốn tiếp tục?')) {
                $this->info('Hành động được hủy bỏ!!!');
                return false;
            }
        }

        if ($type == 'update') {
            $this->info("Update index:" . $this->index_name);
            return true;
        }
        $params = [
            'index' => $this->index_name,
            'body' => [
                'settings' => [
                    'number_of_shards' => 15,
                    'number_of_replicas' => 1
                ]
            ]
        ];
        if($type=='index_mapping'){
            $params['body']['mappings'] = [
                '_source' => [
                    'enabled' => true
                ],
                'properties' => [
                    'location' => [
                        'type' => 'geo_point'
                    ]
                ]
            ];
        }
        try {
            app('elastic')->indices()->create($params);
            $this->info("Created index");
            return true;
        } catch (\Exception $e) {
            $debug = json_decode($e->getMessage());
            if ($debug == null) return dd($e);
            if ($debug->error->type) {
                $this->info("Index $this->index_name existed!!");
                $this->warn("Delete index:" . $this->index_name);
                app('elastic')->indices()->delete(['index' => $this->index_name]);
                app('elastic')->indices()->create($params);
                $this->info("Reset data index: " . $this->index_name);
                return true;
            }
            $this->warn($debug);
            return false;
        }


    }
}
