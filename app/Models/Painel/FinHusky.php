<?php

namespace App\Models\Painel;

use Illuminate\Database\Eloquent\Model;

class FinHusky extends Model {

    public static function __callStatic($method, $parameters){
        return (new static)->$method(...$parameters);
    }

    public function husky_create($data_array=null){
            
        $make_call = $this->husky(
                                    'POST', 
                                    'https://app.husky.io/api/v1/users', 
                                    json_encode($data_array),
                                    null,
                                    'application/json'
                                );
    
        $response = json_decode($make_call, true);
        return $response;
    }

    public function husky_mp(){

        $beets_token= "V7CqVf7MbHgeXeUisaYiXJELW2cFA2CZRRm48VxA22Y";           
        $data_array['transactions'] =  array();
        $make_call = $this->husky(
                                    'POST', 
                                    'https://app.husky.io/api/v1/mass_payments/new', 
                                    json_encode($data_array),
                                    $beets_token,
                                    'application/json'
                                );
    
        $response = json_decode($make_call, true);
        return $response;
    }

    public function husky_mp_add($data=null,$tipo){
    
        $beets_token= "V7CqVf7MbHgeXeUisaYiXJELW2cFA2CZRRm48VxA22Y";
        
        if($tipo==1){
            $data_array['transactions'][] =  array(
                'token'=>$data['token'],
                'value'=>$data['final_value'],
                'currency'=>'USD',
                'tracking_code'=>$data['tracking_code'],
                'invoice_url'=>'http://office.joinads.me/painel/recibo/'.$data['tracking_code']
             );
        } else {
            $data_array['transactions'][] =  array(
                'token'=>$data['token'],
                'final_value'=>$data['final_value'],
                'currency'=>'USD',
                'tracking_code'=>$data['tracking_code'],
                'invoice_url'=>'http://office.joinads.me/painel/recibo/'.$data['tracking_code']
             );
        }
                    
        $make_call = $this->husky(
                                    'PUT', 
                                    'https://app.husky.io/api/v1/mass_payments/'.$data['masspay_id'].'/transactions', 
                                    json_encode($data_array),
                                    $beets_token,
                                    'application/json'
                                );
    
        $response = json_decode($make_call, true);
        return $response;
    }
    
    public function husky_mp_close($id=null){
        
        $beets_token= "V7CqVf7MbHgeXeUisaYiXJELW2cFA2CZRRm48VxA22Y";    
                
        $make_call = $this->husky(
                                    'PUT', 
                                    'https://app.husky.io/api/v1/mass_payments/'.$id.'/close', 
                                    false,
                                    $beets_token,
                                    'application/json'
                                );
    
        $response = json_decode($make_call, true);
        return $response;
    }
    
    public function husky_mp_consult($id=null){
        
        $beets_token= "V7CqVf7MbHgeXeUisaYiXJELW2cFA2CZRRm48VxA22Y";
        
                
        $make_call = $this->husky(
                                    'GET', 
                                    'https://app.husky.io/api/v1/mass_payments/'.$id.'/transactions', 
                                    false,
                                    $beets_token,
                                    'application/json'
                                );
    
        $response = json_decode($make_call, true);
        return $response;
    }
    
    public function husky_mp_information($id=null){
        
        $beets_token= "V7CqVf7MbHgeXeUisaYiXJELW2cFA2CZRRm48VxA22Y";
        
                
        $make_call = $this->husky(
                                    'GET', 
                                    'https://app.husky.io/api/v1/mass_payments/'.$id.'', 
                                    false,
                                    $beets_token,
                                    'application/json'
                                );

        $response = json_decode($make_call, true);
       
        return $response;
    }
    
    public function husky_upload($data=null,$token=null){
        
        $get_data = $this->husky('POST', 'https://app.husky.io/api/v1/upload', $data, $token, 'multipart/form-data');
        $response = json_decode($get_data, true);
        return $response;
    
    }
    
    
    public function husky_destination($data=null,$token=null){
        
        $get_data = $this->husky(
                                'POST', 
                                'https://app.husky.io/api/v1/destination_accounts/', 
                                json_encode($data), 
                                $token, 
                                'application/json'
                            );
        $response = json_decode($get_data, true);
        return $response;
        
    }
    
    public function husky_destination_verify($token=null){
        
        $get_data = $this->husky(
                                'GET', 
                                'https://app.husky.io/api/v1/destination_accounts/', 
                                null, 
                                $token, 
                                'application/json'
                            );
        $response = json_decode($get_data, true);
        return $response;
    
    }
    
    public function husky_compliance($token=null){
        $get_data = $this->husky('GET', 'https://app.husky.io/api/v1/compliance', false, $token,'application/json');
        $response = json_decode($get_data, true);
        return $response;
    }

    public function banks(){
        $beets_token= "5197ca74bef0f2fb9a625d9857da70b7bb17a3923ff060d4b5942928a2e8da45";              
        return json_decode($this->husky('GET','https://app.husky.io/api/v1/banks/',false,$beets_token,'application/json'),true);
    }
    
    public function husky($method, $url, $data, $key=null,$type=null){
        $curl = curl_init();
        
        switch ($method){
           case "POST":
              curl_setopt($curl, CURLOPT_POST, 1);
            //   curl_setopt($curl, CURLOPT_SAFE_UPLOAD, false);
              if ($data)
                 curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
              break;
           case "PUT":
              curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
              if ($data)
                 curl_setopt($curl, CURLOPT_POSTFIELDS, $data);			 					
              break;
           default:
              if ($data)
                 $url = sprintf("%s?%s", $url, http_build_query($data));
        }
        
        // OPTIONS:
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
           'Authorization: '.$key,
           'Content-Type: '.$type,
        ));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        
        
        // EXECUTE:
        $result = curl_exec($curl);
        if(!$result){die("Connection Failure");}
        curl_close($curl);
        return $result;
    
     }

}
