<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;

class AdManagerReportMonth extends Model {

    public $rules = [
    ];

    protected $primaryKey = 'id_ad_manager_report_month';
    protected $guarded = ['id_ad_manager_report_month'];
    protected $table = 'ad_manager_report_month';

}
