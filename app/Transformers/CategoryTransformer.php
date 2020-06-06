<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 10/1/18
 * Time: 12:50
 */

namespace App\Transformers;


use App\Helper\Transformer\TransformerAbstract;

class CategoryTransformer extends TransformerAbstract
{

    public $availableIncludes = [
        'childs'
    ];

    public function transform($category)
    {
        // Các key cần filter theo fields truyền trên đường dẫn
        $data = [
            'id' => (int)$category->cat_id,
            'name' => $category->cat_name,
            'seo_text' => $category->cat_seo_text,
            'meta_description' => $category->cat_meta_description,
            'meta_title' => $category->cat_meta_title,
            'description' => $category->cat_description,
            'type' => $category->cat_type,
            'parent_id' => $category->cat_parent_id,
            'has_child' => $category->cat_has_child,
            'all_child' => $category->cat_all_child,
            'root' => $category->cat_root,
            'link' => '',
            'picture' => [
                'name' => $category->cat_picture,
                'path' => '',
            ],
            'icon' => [
                'name' => $category->cat_icon,
                'path' => '',
            ],
        ];

        $data = $this->filterFromFields($data);

        // Các key luôn luôn cần trả về thì set vào sau khi đã filter theo fields
//        $data += [
//            'picture' => [
//                'name' => $category->cat_picture,
//                'path' => $category->cat_picture ? pictureProductFullsize($category->cat_picture) : null,
//            ],
//        ];

        return $data;
    }

    public function includeChilds($category)
    {
        return $this->collection($category->childs, new static($this->fields));
    }

}