<?php
/**
 * Created by PhpStorm.
 * User: minhngoc
 * Date: 10/09/2019
 * Time: 09:57
 */

namespace App\Helper;


use phpDocumentor\Reflection\Types\Boolean;
use phpDocumentor\Reflection\Types\Integer;

class ElasticSearchQuery
{
    private $source = null;
    private $terms = [];
    private $search = [];
    private $range_query = [];
    private $limit = 30;
    private $index = null;
    private $doc = null;
    private $from = 0;
    private $sort = null;
    private $convert_data = true;

    public function __construct(string $index, string $doc)
    {
        $this->index = $index;
        $this->doc = $doc;
    }

    public function select(string $fields)
    {
        $this->source = explode(',', $fields);
        return $this;
    }

    public function where($column, $value_1, $value_2 = null)
    {
        if ($value_2 != null) {
            if ($value_1 == '>') {
                $this->range_query[$column] = ['gte' => $value_2];
            } else if ($value_1 == '<') {
                $this->range_query[$column] = ['lte' => $value_2];
            }
        } else {
            $this->terms[] = [
                "terms" => [
                    $column => [$value_1]
                ]
            ];
        }
        return $this;
    }

    public function whereIn($column,$value){
        $this->terms[] = [
            "terms" => [
                $column => $value
            ]
        ];
    }

    public function whereBetween(string $column, array $value)
    {
        $this->range_query[$column] = ['gte' => $value[0], 'lte' => $value[1]];
        return $this;
    }

    public function orderBy($column, $sort = 'asc')
    {
//        $this->sort = [$column => ['order' => $sort]];
        $this->sort = [$column =>$sort];
        return $this;
    }

    public function limit($limit)
    {
        $this->limit =(int) $limit;
        return $this;
    }

    public function offset( $offset)
    {
        $this->from =(int) $offset;
        return $this;
    }

    public function queryString($column, $keyword)
    {
        $this->search = [
            [
                "match" => [
                    $column => $keyword
                ]
            ]
        ];
        return $this;
    }

    public function searchGeoLocation($lat,$lon,$order,$unit){

    }

    public function first(){
        $this->limit = 1;
        $value = $this->get();
        return isset($value[0])?$value[0]:[];
    }

    public function get(bool $info_query=false){
        $params = [
            'index' => $this->index,
            'type' => $this->doc,
            'body' => [
                "query" => [
                    "bool" => [
                        "must" => []
                    ]

                ],
                "from" =>$this->from,
                "size" => $this->limit
            ]
        ];
        if($this->search!=null) $params['body']['query']['bool']['must'][] =$this->search;
        if(count($this->terms)!=0) $params['body']['query']['bool']['must'][] =$this->terms;
        if(count($this->range_query))$params['body']['query']['bool']['must'][] = ['range'=>$this->range_query];
        if($this->source!=null) $params['body']['_source'] = $this->source;
        if($this->sort!=null) $params['body']['sort'] = [$this->sort];
        try{
            $data_search = app('elastic')->search($params);
        }catch (\Exception $error){
            dd([
                'status' =>'Error',
                'message'=>json_decode($error->getMessage()),
                'line'=>$error->getLine(),
            'code'=>$error->getCode(),
                'file'=>$error->getFile(),
                'query'=>$params
            ]);
        }
        if($info_query){
            $value = null;
            if (isset($data_search['hits']['hits'])) {
                $value = [];
                foreach ($data_search['hits']['hits'] as $data) {
                    $value[] = $data['_source'];
                }
            }

            return $value;
        }
        $data_search['query'] = $params;
        return $data_search;
    }

}