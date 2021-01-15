<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Builder;

use Illuminate\Database\Eloquent\Model;

class InfluencersVisitsReal extends Model {

    public $rules = [];

    public $incrementing = false;
    
    protected $primaryKey = ['post_id','user_id','date'];
    
    protected $table = 'in_visits_realtime';

    protected function setKeysForSaveQuery(Builder $query) {
        $keys = $this->getKeyName();
        if(!is_array($keys)){
            return parent::setKeysForSaveQuery($query);
        }
        foreach($keys as $keyName){
            $query->where($keyName, '=', $this->getKeyForSaveQuery($keyName));
        }
        return $query;
    }

    protected function getKeyForSaveQuery($keyName = null) {
        if(is_null($keyName)){
            $keyName = $this->getKeyName();
        }

        if (isset($this->original[$keyName])) {
            return $this->original[$keyName];
        }

        return $this->getAttribute($keyName);
    }
    

}
