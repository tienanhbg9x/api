<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Classified;
use App\Models\ClassIFiedSource;
use Hashids\Hashids;

class RedirectController extends Controller
{
    function redirectLink($link)
    {
        $link = urldecode($link);
        $id = $this->getHashId($link);
        if ($id == 0) {
            return [
                'status' => 404,
                'message' => 'Not found url'
            ];
        } else {
            $classified = ClassIFiedSource::select('cla_link')->where('cla_id', $id)->first();
            $classified_meta = Classified::select('cla_title','cla_rewrite','cla_description')->where('cla_id',$id)->first();
            if ($classified) {
                $meta = [
                    [
                        'name' => 'title',
                        'content' => $classified_meta->cla_title
                    ],
                    [
                        'name' => 'og:title',
                        'content' => $classified_meta->cla_title
                    ],
                    [
                        'name' => 'description',
                        'content' => mb_substr($classified_meta->cla_description,0,200),
                    ],
                    [
                        "name" => "og:description",
                        "content" =>mb_substr($classified_meta->cla_description,0,200),
                    ],
//                    [
//                        'name' => 'keywords',
//                        'content' => $category->keyword
//                    ],
                    [
                        "name" => "revisit-after",
                        "content" => "1 days"
                    ],
                    [
                        "name" => "og:url",
                        "content" => "http://nhazi.com/redirect/".$classified_meta->cla_rewrite
                    ],
                    [
                        "name"=> "DC.language",
                        "content"=> "scheme=utf-8 content=vi"
                    ],
                    [
                        "name"=> "robots",
                        "content"=> "index, follow"
                    ]
                ];
                $data_return =  [
                    'meta'=>$meta,
                    'title'=>$classified_meta->cla_title,
                    'full_link'=>$classified->cla_link,
                    'domain'=>$this->filterDomain($classified->cla_link)
                ];
                return $this->setResponse(200,$data_return);

            } else {
                return $this->setResponse(404,null,"Not found url");
            }

        }
    }

    function getHashId($link)
    {
        if (preg_match('/cla([0-9a-zA-Z]+)|new([0-9a-zA-Z]+)/', $link, $match)) {
            if (!isset($match[1])) return 0;
            $hashids = new Hashids('fjdsljjl3j4j23fdl');
            $numbers = $hashids->decode($match[1]);
            return isset($numbers[0]) ? $numbers[0] : 0;
        }
        return 0;
    }

    //
}
