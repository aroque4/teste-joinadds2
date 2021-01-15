<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;

class DomainEarningsInvalid extends Model {

    public $rules = [
      'value' => 'required',
      'month' => 'required',
      'year' => 'required'
    ];

    protected $fillable = ['value','month','year','id_domain','description'];
    protected $primaryKey = 'id_domain_earnings_invalid';
    protected $guarded = ['id_domain_earnings_invalid'];
    protected $table = 'domain_earnings_invalid';

}
