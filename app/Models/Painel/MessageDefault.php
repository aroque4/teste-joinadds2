<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;

class MessageDefault extends Model {

    public $rules = [
      'subject' => 'required',
      'message' => 'required'
    ];

    protected $fillable = ['subject','message'];
    protected $primaryKey = 'id_message_default';
    protected $guarded = ['id_message_default'];
    protected $table = 'message_default';

}
