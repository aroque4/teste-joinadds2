<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;

class DomainNotification extends Model {

    public $rules = [
      'subject' => 'required',
      'message' => 'required'
    ];

    protected $fillable = ['subject','message','id_domain'];
    protected $primaryKey = 'id_domain_notification';
    protected $guarded = ['id_domain_notification'];
    protected $table = 'domain_notification';

}
