<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;

class FinInvoices extends Model {

    public $rules = [];

    protected $primaryKey = 'id_in_finvoices';
    protected $table = 'fin_invoices';

}
