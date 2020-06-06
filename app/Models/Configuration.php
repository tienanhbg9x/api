<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configuration extends Model
{
    protected $table = 'configuration';
    protected $primaryKey = 'con_id';
    public $timestamps = false;

    /**
     * @var bool (optional) default false
     */
    protected $mappedOnly = true;


    /**
     * @var array (optional) specifies columns to alias
     */
    var $fillable = [
        'con_id' => 'id',
        'con_site_title' => 'site_title',
        'con_meta_description' => 'meta_description',
        'con_meta_keywords' => 'meta_keywords',
        'con_currency' => 'currency',
        'con_price_updating' => 'price_updating',
        'con_quantity_updating' => 'quantity_updating',
        'con_facebook_code' => 'facebook_code',
        'con_company_name' => 'company_name',
        'con_address' => 'address',
        'con_phone' => 'phone',
        'con_mobile' => 'mobile',
        'con_email' => 'email',
        'con_static_contact' => 'static_contact',
        'con_static_footer' => 'static_footer',
        'con_static_home' => 'static_home',
        'con_lang_id' => 'lang_id',
        'con_static_version' => 'static_version',
        'con_keyword_spam' => 'keyword_spam',
        'con_keyword_ok' => 'keyword_ok',
        'con_keyword_title' => 'keyword_title',
        'con_last_index' => 'last_index',
        'con_static_url' => 'static_url',
        'con_chat_on' => 'chat_on',
    ];

    public function alias($fields = null,$addField = null)
    {
        $newFields = [];
        if ($fields == "*" || empty($fields)) {
            foreach ($this->fillable as $field => $alias) {
                $newFields[] = $field . " AS " . $alias;
            }
        }
        $fields = explode(",", $fields);
        foreach ($fields as $alias) {
            $field = array_search($alias, $this->fillable);
            if (!empty($field)) $newFields[] = $field . " AS " . $alias;
        }
        if(!empty($addField)) $newFields[] = DB::raw($addField);
        return $newFields;
    }
}
