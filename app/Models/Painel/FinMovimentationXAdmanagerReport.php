<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinMovimentationXAdmanagerReport extends Model {
  use SoftDeletes;

    public $timestamps = true;

    protected $fillable = ['id_fin_movimentation','id_admanager_report','created_at'];
    protected $primaryKey = 'id';
    protected $guarded = ['id'];
    protected $table = 'fin_movimentation_x_admanager_report';
    protected $dates = ['deleted_at'];


}
