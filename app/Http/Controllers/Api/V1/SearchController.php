<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Models\Classified;

class SearchController extends Controller
{

    function searchClassified(Request $request)
    {
        try {
            $classifieds = $this->searchClassifiedsSphinx($request->all());
            $classifieds = $this->filterClassifieds(collect($classifieds));
            return $this->setResponse(200, $classifieds);
        } catch (\Exception $error) {
            return $this->setResponse(404, null, $error->getMessage());
        }

    }

    function searchClassifiedsSphinx($request)
    {
        $sphinx = app()->make('sphinx');
        $query_sphinx = $sphinx->select('id')->from('classifieds')->where('cla_active', '=', 1);
        //Filter request
        $offset = isset($request['page']) ? ($request['page'] * 20 -20): 0;
        $query = [];
        foreach ($request as $key => $data) {
            if ($key == 'cla_price_min' || $key == 'cla_price_max' || $key == 'cla_title' || $key == 'page' || $key == 'cla_date_from' || $key == 'cla_date_to' || $key == 'query_option' || $key == 'api_token') {
                continue;
            }
            $query[$key] = $data;
        }

        foreach ($query as $key => $value) {
            $query_sphinx->where($key, '=', (int)$value);
        }


        if (isset($request['cla_title']) && $request['cla_title'] != null) {
            $request['cla_title'] = urldecode($request['cla_title']);
            $query_sphinx->match('cla_title', $request['cla_title']);
        }
        // Filter price
        $query_sphinx = $this->createFilterMinMaxSphinx($query_sphinx, $request, 'cla_price', 'cla_price_max', 'cla_price_min');
        // Filter date
        $query_sphinx = $this->createFilterMinMaxSphinx($query_sphinx, $request, 'cla_date', 'cla_date_to', 'cla_date_from');
        //Check order By
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

        $query_sphinx->orderBy($column_order, $type_order);

        $query_sphinx->limit($offset, 20);
        $result = $query_sphinx->execute()->fetchAllAssoc();
        //Filter id classifies
        $arr_id = $this->filterIdSphinx($result);
        //Get classifieds from mysql
        $classifieds = Classified::select('classifieds.cla_id', 'classifieds.cla_title', 'classifieds.cla_disid', 'classifieds.cla_rewrite', 'classifieds.cla_date', 'classifieds.cla_has_picture', 'classifieds.cla_picture', 'classifieds.cla_description', 'classifieds.cla_price', 'classifieds.cla_list_acreage')
            ->whereIn('cla_id', $arr_id)->orderBy($column_order, $type_order)->get();
        return $classifieds;

    }

    function createFilterMinMaxSphinx(&$sphinx, $request, $name_column, $value_max, $value_min)
    {
        if (isset($request[$value_max]) && isset($request[$value_min])) {
            $sphinx->where($name_column, 'BETWEEN', [(int)$request[$value_min], (int)$request[$value_max]]);
        } else if (isset($request[$value_min])) {
            $sphinx->where($name_column, '>', (int)$request[$value_min]);
        } else if (isset($request[$value_max])) {
            $sphinx->where($name_column, '<', (int)$request[$value_max]);
        }
        return $sphinx;
    }

//    function filterRequest($request)
//    {
//
//        $offset = isset($request['page']) ? $request['page'] * 20 : 0;
//
//        $query = [];
//        foreach ($request as $key => $data) {
//            if ($key == 'cla_price_min' || $key == 'cla_price_max' || $key == 'cla_title' || $key == 'page' || $key == 'cla_date_from' || $key == 'cla_date_to'||$key=='query_option'||$key=='api_token') {
//                continue;
//            }
//            $query[$key] = $data;
//        }
//        $query_str = '';
//        $count_query = 1;
//        foreach ($query as $key => $value) {
//            if (count($query) == $count_query) {
//                $query_str .= " $key = $value ";
//                continue;
//            }
//            $query_str .= " $key = $value and";
//            $count_query++;
//        }
//
//        if (isset($request['cla_title']) && $request['cla_title'] != null) {
//            $request['cla_title'] = urldecode($request['cla_title']);
//            $query_str .= count($query) != 0 ? " and cla_title like '%" . $request['cla_title'] . "%' " : " cla_title like '%" . $request['cla_title'] . "%' ";
//        }
//        $now = explode('.', microtime(true))[0];
//        $column_join = isset($request['cla_disid']) ? ' cla_disid' : ' cla_citid';
//
//        $query_str .= $this->createFilterMinMax($query_str, $request, 'cla_price', 'cla_price_max', 'cla_price_min');
//
//        $query_str .= $this->createFilterMinMax($query_str, $request, 'cla_date', 'cla_date_to', 'cla_date_from');
//
//        $query_str = ($query_str == '' ? ' WHERE cla_active = 1  ' : ' WHERE cla_active = 1 AND ') . $query_str;
//
//        $query_option = isset($request['query_option'])?$request['query_option'] : '';
//        $column_order = 'cla_date';
//        $type_order = 'desc';
//
//        if($query_option!=''){
//            if($query_option=='price_desc'){
//                $column_order =  'cla_price';
//            }else if($query_option=='price_asc'){
//                $column_order = 'cla_price';
//                $type_order = 'asc';
//            }
//        }
//
//        $order_by = " ORDER BY $column_order $type_order ";
//
//        $select_column = 'cla_id, cla_title, cla_disid, cla_rewrite,cla_date, cla_has_picture, cla_picture, cla_description, cla_price, cla_list_acreage';
//        $query_str = "SELECT $select_column FROM classifieds $query_str $order_by  LIMIT 20 OFFSET $offset ";
//        $data = DB::connection('mysql')->select($query_str);
//        return $data;
//    }
//
//    function createFilterMinMax($query_str, $request, $name_column, $value_max, $value_min)
//    {
//        $query = '';
//        if (isset($request[$value_max]) && isset($request[$value_min])) {
//            $query .= $query_str != '' ? " AND  $name_column  BETWEEN " . $request[$value_min] . ' AND ' . $request[$value_max] . ' ' : " $name_column  BETWEEN  ". $request[$value_min] . ' AND ' . $request[$value_max] . ' ';
//        } else if (isset($request[$value_min])) {
//            $query .= $query_str != '' ? " AND $name_column > " . $request[$value_min] . ' ' :  " $name_column  > " . $request[$value_min] . ' ';
//        } else if (isset($request[$value_max])) {
//            $query .= $query_str != '' ? " AND  $name_column  < " . $request[$value_max] . ' ' : "  $name_column  < " . $request[$value_max] . ' ';
//        }
//        return $query;
//    }
}
