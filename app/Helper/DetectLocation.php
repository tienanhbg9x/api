<?php
namespace Toanld\Location;
use Foolz\SphinxQL\SphinxQL;

/**
 * Created by Lê Đình Toản.
 * User: dinhtoan1905@gmail.com
 * Date: 7/31/2018
 * Time: 4:42 PM
 */
class DetectLocation
{
    protected $iWard;
    protected $iStreet;
    protected $address_src;
    protected $sphinx_index;
    protected $address;
    protected $iCit;
    protected $iDis;

    function __construct($address = "")
    {
        $this->sphinx_index = "locations";
        $this->address = cleanKeywordSearch(convertToUnicode($address));
        $this->address_src = $this->address;
    }

    /**
     * Get City
     */
    public function getCity(){
        if($this->iCit > 0) return $this->iCit;
        $query = (new SphinxQL(app()->make("SphinxConnect")))->select('*',"weight() AS weight","PACKEDFACTORS({json=1}) AS rankers")
        ->from('locations')
        ->match('loc_address', SphinxQL::expr('"' . $this->address . '"/2'))
        ->match(['loc_name','loc_cit_name'], SphinxQL::expr('"' . $this->cutWord($this->address,-4) . '"/2'))
        //->where('loc_type','district')
        ->limit(1)
        ->option("ranker","expr('sum(exact_hit+10+lcs*(1*exact_order))*1000 +bm25')");
        //echo $query->compile()->getCompiled();
        $result = $query->execute();
        foreach($result->fetchAllAssoc() as $loc){
            //_debug($loc);
            //if(isset($loc["rankers"])) _debug(json_decode($loc["rankers"],true));
            $this->iCit = intval($loc["loc_citid"]);
            return intval($loc["loc_citid"]);
        }
    }


    /**
     * Get District
     */
    public function getDistrict(){
        if($this->iDis > 0) return $this->iDis;
        $this->iCit = $this->getCity();
        if($this->iCit <= 0) return 0;
        $query = (new SphinxQL(app()->make("SphinxConnect")))->select('*',"weight() AS weight","PACKEDFACTORS({json=1}) AS rankers")
        ->from($this->sphinx_index)
        ->match('loc_address', SphinxQL::expr('"' . $this->address . '"/2'))
        ->where('loc_type',"district")
        ->where('loc_citid',intval($this->iCit))
        ->limit(1)
        ->option("ranker","expr('sum(exact_hit+10+lcs*(1*exact_order))*1000 +bm25')");
        //echo $query->compile()->getCompiled();
        $result = $query->execute();
        foreach($result->fetchAllAssoc() as $loc){
            $this->iDis = $loc["loc_disid"];
            return intval($loc["loc_disid"]);
        }
        return 0;
    }

    /**
     * Get Ward
     */
    public function getWard(){
        if($this->iWard > 0) return $this->iWard;
        $this->iDis = $this->getDistrict();
        if($this->iDis <= 0) return 0;
        $query = (new SphinxQL(app()->make("SphinxConnect")))->select('*',"weight() AS weight","PACKEDFACTORS({json=1}) AS rankers")
        ->from($this->sphinx_index)
        ->match('loc_address', SphinxQL::expr('"' . $this->address . '"/2'))
        //->match('loc_pre', SphinxQL::expr('"' . $this->address . '"/1'))
        ->where('loc_type',"ward")
        ->where('loc_disid',intval($this->iDis))
        ->limit(10)
        ->option("ranker","expr('sum(exact_hit+10+lcs*(1*exact_order))*1000 +bm25')");
        //echo $query->compile()->getCompiled();
        $result = $query->execute();
        foreach($result->fetchAllAssoc() as $loc){
           //_debug($loc);
            if(strpos($this->address,$loc["loc_name"]) !== false) {
                $this->address = str_replace($loc["loc_name"], "", $this->address);
                $this->address = str_replace($loc["loc_pre"], "", $this->address);
                $this->address = $this->removeWord($this->address, $loc["loc_dis_name"]);
                $this->address = $this->removeWord($this->address, $loc["loc_cit_name"]);
                $this->iWard = $loc["loc_wardid"];
                return intval($loc["loc_wardid"]);
            }
        }
        return 0;
    }


    /**
     * Get Street
     */
    public function getStreet(){
        if($this->iStreet > 0) return $this->iStreet;
        $this->iDis = $this->getDistrict();
        if($this->iDis <= 0) return 0;
        $query = (new SphinxQL(app()->make("SphinxConnect")))->select('*',"weight() AS weight","PACKEDFACTORS({json=1}) AS rankers")
        ->from($this->sphinx_index)
        ->match('loc_address', SphinxQL::expr('"' . $this->address . '"/2'))
        //->match('loc_pre', SphinxQL::expr('"' . $this->address . '"/1'))
        ->where('loc_type',"street")
        ->where('loc_disid',intval($this->iDis))
        ->limit(10)
        ->option("ranker","expr('sum(exact_hit+10+lcs*(1*exact_order))*1000 +bm25')");
        //echo $query->compile()->getCompiled();
        $result = $query->execute();
        foreach($result->fetchAllAssoc() as $loc){
           //_debug($loc["add_address"]);
            if(strpos($this->address,$loc["loc_name"]) !== false){
                $this->address = str_replace($loc["loc_name"],"",$this->address);
                $this->address = str_replace($loc["loc_pre"],"",$this->address);
                $this->address = $this->removeWord($this->address,$loc["loc_dis_name"]);
                $this->address = $this->removeWord($this->address,$loc["loc_cit_name"]);
                $this->iStreet = $loc["loc_streetid"];
                return intval($loc["loc_streetid"]);
            }
        }
        return 0;
    }


    /**
     * Get Street
     */
    public function getProject($title){
        $this->iDis = $this->getDistrict();
        if($this->iDis <= 0) return 0;
        if(empty(trim($this->address))) return 0;
        $title = cleanKeywordSearch($title);

        $query = (new SphinxQL(app()->make("SphinxConnect")))->select('*',"weight() AS weight","PACKEDFACTORS({json=1}) AS rankers")
        ->from("projects")
        ->match(['proj_name','proj_title','proj_address'], SphinxQL::expr('"' . $this->address . '"/1'))
        ->where('proj_disid',intval($this->iDis))
        ->limit(15)
        ->option("ranker","expr('sum(exact_hit+10+lcs*(1*exact_order))*1000 +bm25')");
        //echo $query->compile()->getCompiled();
        $result = $query->execute();
        foreach($result->fetchAllAssoc() as $loc){
            //_debug($loc);
            //echo $loc["add_name"] . '<br>';
            if(strpos($this->address_src,$loc["proj_name"]) !== false){
                //_debug($loc);
                //_debug($loc["add_name"]);
                return intval($loc["id"]);
            }
        }
        if(empty($title)) return 0;

        $query = (new SphinxQL(app()->make("SphinxConnect")))->select('*',"weight() AS weight","PACKEDFACTORS({json=1}) AS rankers")
        ->from("projects")
        ->match('proj_name,proj_title,proj_address', SphinxQL::expr('"' . $title . '"/2'))
        ->where('proj_disid',intval($this->iDis))
        ->limit(15)
        ->option("ranker","expr('sum(exact_hit+10+lcs*(1*exact_order))*1000 +bm25')");
        //echo $query->compile()->getCompiled();
        $result = $query->execute();
        foreach($result->fetchAllAssoc() as $loc){

            //echo $loc["add_name"] . '<br>';
            if(strpos($this->address_src,$loc["proj_name"]) !== false){
                //_debug($loc);
                //_debug($loc["add_name"]);
                return intval($loc["id"]);
            }
        }

        return 0;
    }


    protected function removeWord($string, $listword){
        $listword = explode(" ",$listword);
        foreach($listword as $word){
            if(trim($word) != ""){
                $string = str_replace($word,"",$string);
            }
        }
        return $string;
    }

    protected function cutWord($listword,$numword = 0){
        $listword = str_replace("  "," ",$listword);
        $listword = str_replace("  "," ",$listword);
        $listword = str_replace("  "," ",$listword);
        $listword = str_replace("  "," ",$listword);
        $listword = str_replace("  "," ",$listword);
        $listword = str_replace("  "," ",$listword);
        $listword = str_replace("  "," ",$listword);
        $listword = explode(" ",$listword);
        $total = count($listword);
        $start = ($numword > 0) ? 0 : ($total - abs($numword));
        if($start < 0) $start = 0;
        $end = $start + abs($numword);
        if($end > $total) $end = $total;
        $arr = [];
        for($i = $start; $i < $end; $i++){
            $arr[] = $listword[$i];
        }
        return implode(" ",$arr);
    }
}