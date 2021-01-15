<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;

class InfluencersInvoices extends Model {

    public $rules = [];

    protected $primaryKey = 'id_in_invoices';
    protected $table = 'in_invoices';

}
