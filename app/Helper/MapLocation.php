<?php
/**
 * Created by PhpStorm.
 * User: minhngoc
 * Date: 29/10/2018
 * Time: 08:57
 */

namespace App\Helper;

use App\Models\Location;


class MapLocation
{
    function mapLocation($id_location=null)
    {
        $locations = new Location();
        if($id_location!=null){
            $location = $locations->select($locations->alias('id,name,address,cit_name,dis_name,ward_name,type,cit_id,dis_id,ward_id,street_id,citid,disid,wardid,streetid'))->where('loc_id',$id_location)->first();
            if($location!=null){
                if($location->type=="city"){
                    $this->mapLocationCity($location);
                }else if($location->type =="district"){
                    $this->mapLocationDistrict($location);
                }else if($location->type == "ward"){
                    $this->mapLocationWard($location);
                }else if($location->type == "street"){
                    $this->mapLocationStreet($location);
                }
            }else{
                return "Not found id!";
            }
            return true;
        }
    }

    function mapLocationCity($location){
                $this->updateLocationStatusCheck($location->id);
                $index_elastic_data = "address";
                $fields_elastic_data = ["full_name^5","cit_name"];
                $query_elastic_data = convertToUnicode($location->cit_name);
                $terms_elastic_data = ["type"=>'city'];
                $source_elastic_data = ["id", "name", "address", "cit_name", "dis_name", "ward_name", "type", "ward_name", "citid", "disid", "wardid", "pre","full_name"];
                $data = $this->searchDataElastic($index_elastic_data,$fields_elastic_data,$query_elastic_data,$terms_elastic_data,$source_elastic_data);
                if($data!=null){
                    if($location->type=="city"){
                        if(strpos(str_slug($location->cit_name), str_slug($data['cit_name'])) === false){
                            return false;
                        }
                        $location = Location::find($location->id);
                        $location->loc_citid = $data['citid'];
                        $location->loc_add_id = $data['id'];
                        if (!$location->save()) {
                            return false;
                        } else {
                            $count_update =  Location::where('loc_cit_id',$location->loc_cit_id)->update(['loc_citid'=>$data['citid']]);
                            return true;
                        }
                    }
                    return true;
                }
                return false;
    }

    function mapLocationDistrict($location){
                $this->updateLocationStatusCheck($location->id);
                $index_elastic_data = "address";
                $fields_elastic_data = ["name^3","full_name^5"];
                $query_elastic_data = convertToUnicode($location->name);
                $terms_elastic_data = ["type"=>'district',"citid"=>$location->citid];
                $source_elastic_data = ["id", "name", "address", "cit_name", "dis_name", "ward_name", "type", "ward_name", "citid", "disid", "wardid", "pre","full_name"];
                $data = $this->searchDataElastic($index_elastic_data,$fields_elastic_data,$query_elastic_data,$terms_elastic_data,$source_elastic_data);
                if($data!=null){
                    if($location->type=="district"){
                        if(strpos(str_slug($location->dis_name), str_slug($data['dis_name'])) === false){
                            if($this->searchTrigram($location,"district")){
                                return true;
                            }
                            return false;
                        }
                        $location = Location::find($location->id);
                        $location->loc_disid = $data['disid'];
                        $location->loc_add_id = $data['id'];
                        if (!$location->save()) {
                            return false;
                        } else {
                            $count_update = Location::where('loc_dis_id',$location->loc_dis_id)->update(['loc_disid'=>$data['disid']]);
                            return true;
                        }
                    }
                    return true;
                }else{
                    return false;
                }
    }

    function mapLocationWard($location){
                $this->updateLocationStatusCheck($location->id);
                $index_elastic_data = "address";
                $fields_elastic_data = ["full_name^5","name^4","ward_name^3"];
                $query_elastic_data = convertToUnicode($location->ward_name);
                $terms_elastic_data = ["type"=>'ward',"citid"=>$location->citid,"disid"=>$location->disid];
                $source_elastic_data = ["id", "name", "address", "cit_name", "dis_name", "ward_name", "type", "ward_name", "citid", "disid", "wardid", "pre","full_name"];
                $data = $this->searchDataElastic($index_elastic_data,$fields_elastic_data,$query_elastic_data,$terms_elastic_data,$source_elastic_data);
                if($data!=null){
                    if(strpos(str_slug($location->ward_name), str_slug($data['ward_name'])) === false){
                        if($this->searchTrigram($location,"ward")){
                            return true;
                        };
                        return false;
                    }
                    $location = Location::find($location->id);
                    $location->loc_wardid = $data['wardid'];
                    $location->loc_add_id = $data['id'];
                    if (!$location->save()) {
                        return false;
                    } else {
                        $count_update = Location::where('loc_ward_id',$location->loc_ward_id)->update(['loc_wardid'=>$data['wardid']]);
                        return true;
                    }
                }
                return false;
    }

    function mapLocationStreet($location){
                $this->updateLocationStatusCheck($location->id);
                $index_elastic_data = "address";
                $fields_elastic_data = ["full_name^6","name^5"];
                $query_elastic_data = convertToUnicode($location->name);
                $terms_elastic_data = ["type"=>'street',"citid"=>$location->citid,"disid"=>$location->disid];
                $source_elastic_data = ["id", "name", "address", "cit_name", "dis_name", "ward_name", "type", "ward_name", "citid","streetid", "disid", "wardid", "pre","full_name"];
                $data = $this->searchDataElastic($index_elastic_data,$fields_elastic_data,$query_elastic_data,$terms_elastic_data,$source_elastic_data);
                if($data!=null){
                    if(strpos(str_slug($location->name), str_slug($data['name'])) === false){
                        if($this->searchTrigram($location,"street")){
                         return true;
                        }
                      return false;
                    }
                    $location = Location::find($location->id);
                    $location->loc_add_id = $data['id'];
                    $location->loc_streetid = $data['streetid'];
                    if (!$location->save()) {
                        return false;
                    } else {
                        return true;
                    }
                }else{
                    return false;
                }
    }

    function updateLocationStatusCheck($id){
        Location::where('loc_id',$id)->update(['rsync_address'=>1]);
    }

    function searchTrigram($location,$location_type){
//        $this->error("Data map error location_id = {$location->id}. Searching type TRIGRAM...");
        $terms_elastic_data = '';
        if($location_type == 'district'){
            $terms_elastic_data = ["type"=>'district',"citid"=>$location->citid];
        }else if($location_type == "ward"){
            $terms_elastic_data = ["type"=>'ward',"citid"=>$location->citid,"disid"=>$location->disid];
        }else if($location_type=='street'){
            $terms_elastic_data = ["type"=>'street',"citid"=>$location->citid,"disid"=>$location->disid];
        }
        $index_elastic_data = "address";
        $fields_elastic_data = ["name1^5"];
        $source_elastic_data = ["id", "name", "address", "cit_name", "dis_name", "ward_name", "type", "ward_name", "citid", "disid", "wardid","streetid", "pre","full_name"];
        $key = BuildTrigrams($location->name);
        $query_elastic_data = $key;
        $data_2 = $this->searchDataElastic($index_elastic_data,$fields_elastic_data,$query_elastic_data,$terms_elastic_data,$source_elastic_data);
        if($data_2!=null){
            if(strpos(str_slug(str_replace(' ',"",$location->name)), str_slug(str_replace(' ',"",$data_2['name']))) === false){
                return false;
            }
            $location = Location::find($location->id);
            if($location_type == 'district'){
                $location->loc_disid = $data_2['disid'];
            }else if($location_type == "ward"){
                $location->loc_disid = $data_2['wardid'];
            }else if($location_type=='street'){
                $location->loc_streetid = $data_2['streetid'];
            }
            $location->loc_add_id = $data_2['id'];
            if (!$location->save()) {
                return false;
            } else {
                if($location_type == 'district'){
                    Location::where('loc_dis_id',$location->loc_dis_id)->update(['loc_disid'=>$data_2['disid']]);
                }else if($location_type == "ward"){
                    Location::where('loc_ward_id',$location->loc_ward_id)->update(['loc_wardid'=>$data_2['wardid']]);
                }
                return true;
            }
        }
        return false;
    }

//    function getError($location,$data,$info_update = null){
////        $this->error("============");
////        $this->error("Map data fail:{$location->id} - {$location->type}");
////        $this->error("Location: {$location->address}");
////        $this->error("Address: {$data['address']}");
//        if($info_update!=null){
////            $this->error("Updated: {$info_update}");
//        }
//    }
//
//    function getInfo($location,$data){
////        $this->info("=====");
////        $this->comment("Location: {$location->loc_address}" );
////        $this->comment("Address: {$data['address']}");
////        $this->info("Update {$location->loc_type}: {$location->loc_id}");
//    }


    function searchDataElastic($index,$fields,$query,$terms=null,$source){
        $params= null;
        if($terms!=null){
            $arr_terms = [];
            foreach ($terms as $key=>$term){
                $arr_terms[] = [
                    "terms"=>[
                        $key=>[$term]
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
                                                "fields" =>$fields,
                                                "query" =>$query
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
        }else{
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
                                                "fields" =>$fields,
                                                "query" =>$query
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
        return isset($data_search['hits']['hits'][0]['_source'])?$data_search['hits']['hits'][0]['_source']:null;
    }

}