<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;

class EmailsTemplate extends Model {

    public $rules = [
      'name' => 'required',
      'html' => 'required'
    ];

    protected $fillable = ['name','html'];
    protected $primaryKey = 'id_emails_template';
    protected $guarded = ['id_emails_template'];
    protected $table = 'emails_template';

}
