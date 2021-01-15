<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;

class Prospection extends Model {

    public $rules = [
      'name' => 'required',
      'email' => 'required',
      'domain' => 'required',
      'subject' => 'required',
      'subject' => 'required',
      'id_emails_template' => 'required'
    ];

    protected $fillable = ['name','email','domain','subject','message','id_emails_template'];
    protected $primaryKey = 'id_prospection';
    protected $guarded = ['id_prospection'];
    protected $table = 'prospection';

}
