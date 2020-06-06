<?php
namespace Toanld\MakeLink;
use App\Models\Category;
use App\Models\Link;
use App\Models\Location;
use App\Models\Project;
use Illuminate\Support\Facades\DB;

/**
 * Created by Lê Đình Toản.
 * User: dinhtoan1905@gmail.com
 * Date: 8/2/2018
 * Time: 1:04 PM
 */
class MakeLink
{
    protected $arrCat = [];
    protected $iWard;
    protected $iStreet;
    protected $iCit;
    protected $iDis;
    protected $iProj;
    protected $iCat;
    protected $keyword;
    function __construct($arr)
    {
        $this->iCat = isset($arr["catid"]) ? intval($arr["catid"]) : 0;
        $this->iCit = isset($arr["citid"]) ? intval($arr["citid"]) : 0;
        $this->iDis = isset($arr["disid"]) ? intval($arr["disid"]) : 0;
        $this->iWard = isset($arr["wardid"]) ? intval($arr["wardid"]) : 0;
        $this->iStreet = isset($arr["streetid"]) ? intval($arr["streetid"]) : 0;
        $this->iProj = isset($arr["projid"]) ? intval($arr["projid"]) : 0;
        $this->keyword = isset($arr["keyword"]) ? $arr["keyword"] : null;
    }

    function getLinkId($arrWhere){
        $this->getCategory();
        $item = Link::select('lin_id');
        foreach($arrWhere as $filed => $val){
            $item->where($filed,$val);
        }
        $item = $item->limit(1)->get()->toArray();
        foreach($item as $row){
            return intval($row["lin_id"]);
        }
        return 0;
    }


    function createLink(){
        $arrParent = [];
        $arrWhere = ["lin_catid" => $this->iCat,"lin_citid" => 0,"lin_disid" => 0,"lin_wardid" => 0,"lin_streetid" => 0,"lin_projid" => 0];
        $arrWhere["lin_citid"] = $this->iCit;
        $cit_id = $this->getLinkId($arrWhere);

        if($cit_id <= 0) $cit_id = $this->addLink($arrWhere);
        $arrParent[$cit_id] = $cit_id;


        $arrWhere["lin_disid"] = $this->iDis;
        $dis_id = $this->getLinkId($arrWhere);
        if($dis_id <= 0) $dis_id = $this->addLink(array_merge($arrWhere,["lin_all_parent" => implode(",",$arrParent),"lin_parentid" => $cit_id]));
        $arrParent[$dis_id] = $dis_id;

        $arrWhere["lin_wardid"] = $this->iWard;
        $ward_id = $this->getLinkId($arrWhere);
        if($ward_id <= 0) $ward_id = $this->addLink(array_merge($arrWhere,["lin_all_parent" => implode(",",$arrParent),"lin_parentid" => $dis_id]));
        $arrParent[$ward_id] = $ward_id;

        $arrWhere["lin_streetid"] = $this->iStreet;
        $street_id = $this->getLinkId($arrWhere);
        if($street_id <= 0) $street_id = $this->addLink(array_merge($arrWhere,["lin_all_parent" => implode(",",$arrParent),"lin_parentid" => ($ward_id > 0) ? $ward_id : $dis_id]));
        $arrParent[$street_id] = $street_id;

        $arrWhere["lin_projid"] = $this->iProj;
        $proj_id = $this->getLinkId($arrWhere);
        if($proj_id <= 0) $proj_id = $this->addLink(array_merge($arrWhere,["lin_all_parent" => implode(",",$arrParent),"lin_parentid" => ($street_id > 0) ? $street_id : $dis_id]));
        $arrParent[] = $proj_id;
        return $proj_id;
    }

    function checkLink($string){
        $arrReturn = ['exists' => 0,"fields" => []];
        $string = cleanKeywordSearch($string);
        $string = removeAccent($string);
        $string = preg_replace("/([^0-9a-z]+)/"," ",$string);
        if(empty(trim($string))) return $arrReturn;
        $string = str_replace(" ","-",trim($string));
        for($i = 0; $i < 10; $i++) $string = str_replace("--","-",$string);
        $length = strlen($string);
        $lin_md5 = md5($string);
        $item = Link::select('lin_length','lin_md5',"lin_rewrite");
        $item->where("lin_length",$length);
        $item->where("lin_md5",$lin_md5);
        $item = $item->limit(1)->get()->toArray();
        if(!empty($item)){
            $arrReturn["exists"] = 1;
            foreach($item as $row){
                $arrReturn["fields"] = $row;
            }
        }else{
            $arrReturn["fields"] = ["lin_length" => $length,"lin_md5" => $lin_md5,"lin_rewrite" => $string];
        }
        return $arrReturn;
    }


    function addLink($arrWhere){
        $this->getCategory();
        $link = new Link();
        foreach($arrWhere as $field => $val){
            $link->{$field}        = $val;
        }
        $link->lin_what         = isset($this->arrCat[$this->iCat]["cat_name"]) ? $this->arrCat[$this->iCat]["cat_name"] : '';
        $loc_id = 0;
        if($link->lin_streetid > 0 && isset($arrWhere["lin_streetid"])){
            $loc_id = $link->lin_streetid;
        }elseif($link->lin_wardid && isset($arrWhere["lin_wardid"])){
            $loc_id = $link->lin_wardid;
        }elseif($link->lin_disid && isset($arrWhere["lin_disid"])){
            $loc_id = $link->lin_disid;
        }elseif($link->lin_citid && isset($arrWhere["lin_citid"])){
            $loc_id = $link->lin_citid;
        }

        $wardName = '';
        if($link->lin_wardid > 0){
            $loc = Location::find($link->lin_wardid);
            if($loc != null) $loc = $loc->toArray();
            if(!empty($loc)){
                $wardName    = $loc["loc_pre"] . " " .$loc["loc_name"];
            }
            unset($loc);
        }

        $loc = Location::find($loc_id);
        if($loc != null) $loc = $loc->toArray();
        if(!empty($loc)){
            if($wardName != "" && $link->lin_streetid > 0){
                $link->lin_short_title = $link->lin_what . " " . $loc["loc_pre"] . " " . $loc["loc_name"] . ", " . $wardName;
                $link->lin_where    = $loc["loc_pre"] . " " . $loc["loc_name"] . ", " . $wardName . ", " . $loc["loc_dis_name"] . ", " . $loc["loc_cit_name"];
            }else{
                $link->lin_short_title = $link->lin_what . " " . $loc["loc_pre"] . " " . $loc["loc_name"];
                $link->lin_where    = $loc["loc_address"];
            }
        }

        if($this->iProj > 0){
            $proj = Project::find($this->iProj)->toArray();
            if(!empty($proj)){
                $link->lin_lat = $proj["proj_lat"];
                $link->lin_lng = $proj["proj_lng"];
                $link->lin_short_title = $proj["proj_name"] . " " . $link->lin_short_title;
                $link->lin_where            = $proj["proj_name"] . " " . $link->lin_where;
            }
        }
        $link->lin_title = $link->lin_what . " " . $link->lin_where;
        $link->lin_total_record = 1;
        $rewrite = $this->checkLink($link->lin_short_title);
        if($rewrite["exists"] == 1) $rewrite = $this->checkLink($link->lin_title);
        foreach($rewrite["fields"] as $key => $val) $link->{$key}        = $val;
        if($link->save()){
            return $link->lin_id;
        }
    }

    function getCategory(){
        if(!empty($this->arrCat)) return $this->arrCat;
        $result = Category::all()->toArray();
        foreach($result as $item){
            $this->arrCat[$item["cat_id"]] = $item;
        }
        return $this->arrCat;
    }

}