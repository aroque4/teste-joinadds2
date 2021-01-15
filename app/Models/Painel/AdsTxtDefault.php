<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;

class AdsTxtDefault extends Model {

    public $rules = [
    ];

    protected $fillable = ['ads_txt'];
    protected $primaryKey = 'id_ads_txt_default';
    protected $guarded = ['id_ads_txt_default'];
    protected $table = 'ads_txt_default';

}
