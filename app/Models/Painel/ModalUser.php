<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;

class ModalUser extends Model {

    public $rules = [

    ];

    protected $fillable = ['id_modal','id_user'];
    protected $primaryKey = 'id_modal_user';
    protected $guarded = ['id_modal_user'];
    protected $table = 'modal_user';

}
