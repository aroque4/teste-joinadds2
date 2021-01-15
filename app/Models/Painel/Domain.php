<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;

class Domain extends Model {

  public $rules = [
    'name' => 'required|unique:domain',
    'rev_share_admanager' => 'required',
    'rev_share_adserver' => 'required',
    'page_views' => 'required',
    'id_domain_category' => 'required',
    'id_user' => 'required',
    'id_domain_status' => 'required'
  ];

  public $rulesUpdate = [
    'rev_share_admanager' => 'required',
    'rev_share_adserver' => 'required',
    'page_views' => 'required',
    'id_domain_category' => 'required',
    'id_user' => 'required',
    'id_domain_status' => 'required'
  ];


    protected $fillable = ['name','id_prebid_version','key_recaptcha','hash_uniq','file_do','login','posted_at','password','google_analytcs_id','head_bidder_id','observation','rev_share_admanager','rev_share_adserver','rev_share_account_manager','page_views', 'status_checklist','id_domain_category','id_user','id_account_manager','id_domain_status'];
    protected $primaryKey = 'id_domain';
    protected $guarded = ['id_domain'];
    protected $table = 'domain';

}
