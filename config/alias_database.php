<?php
/**
 * Created by PhpStorm.
 * User: minhngoc
 * Date: 01/09/2018
 * Time: 22:49
 */

return [
    'mysql' => [
        'classifieds' => [
            'cla_id' => 'id',
            'cla_dis_id' => 'dis_id',
            'cla_cit_id' => 'cit_id',
            'cla_ward_id' => 'ward_id',
            'cla_street_id' => 'street_id',
            'cla_proj_id' => 'proj_id',
            'cla_disid' => 'disid',
            'cla_citid' => 'citid',
            'cla_wardid' => 'wardid',
            'cla_streetid' => 'streetid',
            'cla_projid' => 'projid',
            'cla_date' => 'date',
            'cla_price' => 'price',
            'cla_type_vip' => 'type_vip',
            'cla_cat_id' => 'cat_id',
            'cla_use_id' => 'use_id',
            'cla_active' => 'active',
            'cla_type' =>'type',
            'cla_has_picture'=>'has_picture',
            'cla_rew_id'=>'rew_id'
        ],
        'projects' => [
            'proj_id' => 'id',
            'proj_name' => 'name',
            'proj_inv_id' => 'inv_id',
            'proj_cat_id' => 'cat_id',
            'proj_dis_id' => 'dis_id',
            'proj_cit_id' => 'cit_id',
            'proj_ward_id' => 'ward_id',
            'proj_street_id' => 'street_id'
        ],
        'projects_compare' => [
            'prc_id' => 'id',
            'prc_min_id' => 'min_id',
            'prc_max_id' => 'max_id',
            'prc_title' => 'title',
            'prc_active' => "active",
            'prc_cit_id' => 'cit_id',
            'prc_dis_id' => 'dis_id',
            'prc_ward_id' => 'ward_id',
            'prc_street_id' => 'street_id',
        ],
        'categories' => [
            'cat_id' => 'id',
            'cat_type' => 'type',
            'cat_parent_id' => 'parent_id',
            'cat_all_child' => 'childs',
            'cat_has_child' => 'has_child',
            'cat_vg_id' => 'vg_id',
            'cat_icon' => 'icon',
            'cat_order' => 'order',
            'cat_active' => 'active',
            'cat_hot' => 'hot'
        ],
        'investors' => [
            'inv_id' => 'id',
            'inv_city_id' => 'city_id',
            'inv_dis_id' => 'dis_id',
            'inv_update' => 'update'
        ],
        'locations' => [
            'loc_id' => 'id',
            'loc_pre' => 'pre',
            'loc_code' => 'code',
            'loc_type' => 'type',
            'loc_citid' => 'citid',
            'loc_disid' => 'disid',
            'loc_projid' => 'projid',
            'loc_wardid' => 'wardid',
            'loc_streetid' => 'streetid',
            'loc_cit_id' => 'cit_id',
            'loc_dis_id' => 'dis_id',
            'loc_proj_id' => 'proj_id',
            'loc_ward_id' => 'ward_id',
            'loc_street_id' => 'street_id',
            'loc_parent_id' => 'parent_id',
            'loc_lat' => 'lat',
            'loc_lng' => 'lng',
            'loc_update' => 'update',
            'loc_all_child' => 'all_child',
            'loc_all_parent' => 'all_parent'
        ],
        'rewrites' => [
            'rew_id' => 'id',
            'rew_date' => 'date',
            'rew_table' => 'table',
            'rew_id_value' => 'id_value',
            'rew_count_word' => 'count_word',
            'rew_length' => 'length',
            'rew_cat_id' => 'cat_id',
            'rew_dis_id' => 'dis_id',
            'rew_cit_id' => 'cit_id',
            'rew_ward_id' => 'ward_id',
            'rew_street_id' => 'street_id',
            'rew_proj_id' => 'proj_id'
        ],
        'news' => [
            'new_id' => 'id',
            'new_cat_id' => 'cat_id',
            'new_cat_root_id' => 'cat_root_id',
            'new_use_id' => 'use_id',
            'new_time_create' => 'time_create',
            'new_time_update' => 'time_update',
            'new_status' => 'status',
            'new_citid' => 'citid',
            'new_disid' => 'disid',
            'new_wardid' => 'wardid',
            'new_streetid' => 'streetid'
        ],
        'users' => [
            'use_id' => 'id',
            'use_id_vatgia' => 'id_vatgia',
            'use_active' => 'active',
            'use_birthdays' => 'birthdays',
            'use_zip_code' => 'zip_code',
            'use_phone' => 'phone',
            'use_date' => 'date',
            'use_rol' =>'rol'
        ],
        'classifieds_vip' => [
            'clv_id' => 'id',
            'clv_cla_id' => 'cla_id',
            'clv_date' => 'date',
            'clv_type_vip'=>'type_vip'
        ],
        'baokim_payment_notification' => [
            'bkp_id' => 'id',
            'bkp_user_id' => 'user_id',
            'bkp_bank_fee' => "bank_fee",
            'bkp_created_on' => 'created_on',
            'bkp_customer_account_id' => 'customer_account_id',
            'bkp_customer_phone' => 'customer_phone',
            'bkp_fee_amount' => 'fee_amount',
            'bkp_from_fee' => 'from_fee',
            'bkp_merchant_id' => 'merchant_id',
            'bkp_merchant_phone' => 'merchant_phone',
            'bkp_net_amount' => 'net_amount',
            'bkp_order_amount' => 'order_amount',
            'bkp_order_id' => 'order_id',
            'bkp_payment_type' => 'payment_type',
            'bkp_to_fee' => 'to_fee',
            'bkp_total_amount' => 'total_amount',
            'bkp_transaction_id' => 'transaction_id',
            'bkp_transaction_status' => 'transaction_status',
            'bkp_usd_vnd_exchange_rate' => 'usd_vnd_exchange_rate',
            'bkp_created_at' => 'created_at',
            'bkp_updated_at' => 'updated_at',
        ],
        'configuration_app' => [
            'coa_id' => 'id',
            'coa_type' => 'type',
            'coa_key' => 'key',
            'coa_value' => 'coa_value'
        ],
        'money' => [
            'mon_id' => 'id',
            'mon_user_id' => 'user_id',
            'mon_count' => "count",
            'mon_created_at' => 'created_at',
            'mon_updated_at' => 'updated_at',
        ],
        'user_spend_history'=>[
            'ush_id' => 'id',
            'ush_user_id'=>'user_id',
            'ush_count'=>'count',
            'ush_message'=>'message',
            'ush_order_id'=>'order_id',
            'ush_ip'=>'ip',
            'ush_status'=>'status',
            'ush_user_agent'=>'user_agent',
            'ush_created_at' => 'created_at',
            'ush_updated_at'=>'updated_at',
            'ush_type' =>'type',
        ],
        'user_customer'=>[
            'ucm_id' => 'id',
            'ucm_use_id' => 'use_id',
            'ucm_feature'=>'feature',
            'ucm_created_at' => 'created_at',
            'ucm_uth_id'=>'uth_id',
            'ucm_type'=>'type',
            'ucm_cla_id'=>'cla_id'
        ],
        'theme_source'=>[
            'ths_id' => 'id',
        ],
        'themes'=>[
            'thm_id' => 'id',
            'thm_project_id'=>'project_id',
            'thm_active'=>'active',
            'thm_price' => 'price',
            'thm_created_at'=>'created_at'
        ],
        'user_themes'=>[
            'uth_id' => 'id',
            'uth_active' => 'active',
            'uth_user_id' => 'user_id',
        ],
        'queue_payment'=>[
            'qpm_id' => 'id',
            'qpm_created_at' => 'created_at'
        ],
        'classified_vip_configuration'=>[
            'cvc_id' => 'id',
            'cvc_price'=>'price',
            'cvc_time_start'=>'time_start',
            'cvc_time_end'=>'time_end',
            'cvc_classified_limit'=>'classified_limit',
            'cvc_type'=>'type',
            'cvc_active' =>'active'
        ],
        'classified_vip_show'=>[
            'cvs_id' => 'id',
            'cvs_date'=>'date',
            'cvs_cla_id'=>'cla_id',
            'cvs_cvc_id'=>'cvc_id',
            'cvs_count'=>'count'
        ],
        'user_notify'=>[
            'usn_id' => 'id',
            'usn_use_id' => 'use_id',
            'usn_create_use_id' => 'create_use_id',
            'usn_status'=>'status',
            'usn_created_at'=>'created_at',
        ],
        'user_contacts'=>[
            'usc_id' => 'id',
            'usc_user_id' => 'user_id',
            'usc_guest_id'=>'guest_id',
            'usc_rew_id'=>'rew_id',
            'usc_cat_id'=>'cat_id',
            'usc_cit_id'=>'cit_id',
            'usc_dis_id'=>'dis_id',
            'usc_ward_id'=>'ward_id',
            'usc_proj_id'=>'proj_id',
            'usc_status'=>'status',
            'usc_date'=>'date',
            'usc_phone' => 'phone'
        ],
        'address_review'=>[
            'adr_id' => 'id',
            'adr_add_id' => 'add_id',
            'adr_total_star' => 'total_star',
            'adr_total_review' => 'total_review',
            'adr_star_first' => 'star_first',
            'adr_star_second' => 'star_second',
            'adr_star_third' => 'star_third',
            'adr_star_fourth' => 'star_fourth',
            'adr_star_fifth' => 'star_fifth'
        ],
        'comments'=>[
            'com_star' => 'star',
            'com_add_id' => 'add_id',
            'com_add_cit_id'=>'add_cit_id',
            'com_add_dis_id'=>'add_dis_id',
            'com_add_ward_id'=>'add_ward_id',
            'com_add_street_id'=>'add_street_id',
            'com_date' => 'date',
            'com_active' => 'active',
            'com_user_id' => 'user_id',
            'com_cla_id' => 'cla_id'
        ],
        'configuration_comment' =>[
            'com_id' => 'id',
            'com_star' => 'star',
            'com_active' => 'active'
        ],
        'user_favorite'=>[
            'usf_use_id' => 'use_id',
            'usf_cla_id'=>'cla_id',
            'usf_date'=>'date'
        ]
    ]
];