<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;

class ContractUser extends Model {

    public $rules = [
      'start_date' => 'required',
      'end_date' => 'required',
      'id_user' => 'required',
      'id_contract' => 'required',
      'pay_time'=>'required'
    ];

    protected $fillable = ['start_date','end_date','signature','id_user', 'status', 'signature_admin','id_contract','rev_share','pay_time'];
    protected $primaryKey = 'id_contract_user';
    protected $guarded = ['id_contract_user'];
    protected $table = 'contract_user';

}
