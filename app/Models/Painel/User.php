<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model {
  use SoftDeletes;

    public $rules = [
      'name' => 'required',
      // 'email' => 'required',
    ];

    protected $fillable = ['name','email','type_profile','invite_admanager','send_invite_admanager','status_waiting','observation_waiting','whatsapp','status_admanager','CPF_CNPJ','photo','type_earnings','send_mail','type_account','disapproved','observation_disapproved','bank','agency','account','digit','CMP','blog','PIS','password','enter_at','value_pay','id_user_type','user_type','website','address','city','state','agency_digit','doc_id_doc','doc_back_id_doc','doc_proof_of_address','doc_sign','doc_pic','bank_type','husky_token','company','iban','swift','sub_adx','afiliados','afiliados_porcentagem','civil','nacionalidade','endereco','bairro','numero','cidade','estado','CNPJ','titular_nome','titular_documento','cep','gerente_contas','brinde','net_pag','pix'];
    protected $primaryKey = 'id';
    protected $guarded = ['id'];
    protected $table = 'users';
    protected $dates = ['deleted_at'];


}
