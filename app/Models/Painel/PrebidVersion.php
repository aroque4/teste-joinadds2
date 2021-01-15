<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;

class PrebidVersion extends Model {

    public $rules = [
      'name' => 'required',
      'version' => 'required'
    ];

    protected $fillable = ['name','version','enabled'];
    protected $primaryKey = 'id_prebid_version';
    protected $guarded = ['id_prebid_version'];
    protected $table = 'prebid_version';

}
