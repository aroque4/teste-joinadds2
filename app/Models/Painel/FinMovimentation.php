<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinMovimentation extends Model {

    use SoftDeletes;

    public $rules = [
      'id_fin_category' => 'required',
      'id_fin_bank' => 'required',
      'id_user' => 'required',
      'id_fin_form' => 'required',
      'id_client' => 'required',
      'id_fin_currency' => 'required',
    ];

    protected $fillable = ['date_expiry','date_payment','number_doc','value','file','obs','currency','month_reference','id_client','id_user','id_fin_bank','id_fin_category','id_fin_form','id_fin_currency','type','status','id_husky','tax'];
    protected $primaryKey = 'id_fin_movimentation';
    protected $guarded = ['id_fin_movimentation'];
    protected $table = 'fin_movimentation';
    protected $dates = ['deleted_at'];

    public function user(){
        return $this->hasMany('App\User','id', 'id_user');
    }
    
    public function client(){
        return $this->hasMany('App\User','id', 'id_client');
    }

}
