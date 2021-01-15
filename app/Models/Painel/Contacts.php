<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;

class Contacts extends Model {

    protected $fillable = ['id_contact','information','feedback','id_user','id_prospect','return_in','created_at','updated_at'];
    protected $primaryKey = 'id_contact';
    protected $guarded = ['id_contact'];
    protected $table = 'contacts';

}