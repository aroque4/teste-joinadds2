<?php

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Painel\StandardController;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory;
use App\Models\Painel\FinHusky;
use App\Models\Painel\User;
use App\Models\Painel\FinMassPayment;
use Illuminate\Support\Facades\Auth;
use Defender;
use File;

class FinHuskyController extends StandardController {

  protected $nameView = 'fin-husky';
  protected $diretorioPrincipal = 'painel';
  protected $primaryKey = 'id_fin_bank';

  public function __construct(Request $request, FinHusky $model, Factory $validator, User $user, FinMassPayment $mp) {
    $this->request = $request;
    $this->model = $model;
    $this->validator = $validator;
    $this->CM = $user;
    $this->HM = $model;
    $this->MP = $mp;
  }

  public function getIndex($id=null){

    if (Defender::hasPermission("fin-husky")) {
      $title = 'Husky Gateway';
      $principal = $this->diretorioPrincipal;
      $rota = $this->nameView;
      $data = $this->HM->husky_mp_information($id)['data'];

      usort($data, function($a, $b) {
        return $a['id'] <=> $b['id'];
      });

      krsort($data);

      // dd($data);
      
      $mp = $this->MP->where('status','1')->limit(1)->orderBY('id_fin_mass_payment','desc')->first();
      return view("{$this->diretorioPrincipal}.{$this->nameView}.index", compact('data','title','principal','rota','mp'));

    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function getView($id=null){
    if (Defender::hasPermission("fin-husky")) {
     $title = 'Husky Gateway - Order nº: '.$id;
      $principal = $this->diretorioPrincipal;
      $rota = $this->nameView;
      $data = $this->HM->husky_mp_consult($id)['data'];
      $information = $this->HM->husky_mp_information($id)['data'];
      return view("{$this->diretorioPrincipal}.{$this->nameView}.view", compact('data','information','title','principal','rota'));
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
   }

  public function create($id=null){
    $data = $this->CM->get($id);
    $name = explode(' ',$data->bank_titular);

    $cliente_id = "iX2Fp31b9EFVi9YoA6dt0KNHS2RWVuEBDKNMuWYrKOE";
    $secret_id  = "YpL66KYmbXGCEMJqdQ8Crbfy87wG3P4Wjme0YZOcc_Y";
    
    $array = array(
        "first_name" =>  $name[0],
        "last_name" => @$name[1].' '.@$name[2].' '.@$name[3],
        "email"=>$data->bank_email,
        "cell_phone"=>$data->phonenumber,
        "password"=>"beets1987",
        "password_confirmation"=>"beets1987",
        "account_type"=>'individual',
        "document_id"=>$data->bank_cpf,
        "client_id"=>$cliente_id,
        "client_secret"=>$secret_id
    );

    $integration = $this->HM->husky_create($array);
    
    if($integration['meta']['message'] == 'success'){
            $update['husky_token'] = $integration['data']['token'];  
            
            if($this->CM->update($update,$id)){
                echo json_encode($integration['meta']);
            } else {
                echo json_encode($integration['meta']);
            }
            
    } else {
        echo json_encode($integration);
    }
    
  }

  public function create_mp(){
    $id = Auth::user()->id;
    $data  = $this->HM->husky_mp();
    $array = array(
        'id_husky'=>$data['data']['mass_payment_id'],
        'id_user' => $id,
        'status'=>1,
        'created'=>date('Y-m-d h:i:s')
    );

    if($this->MP->create($array)){
        $saida = array('status'=>200,'msg'=>'Aberto com sucesso');
    } else {
        print_r($this->db->_error_message());
        $saida = array('status'=>100,'msg'=>'Problemas ao abrir');
    }
    echo json_encode($array);

  }

  public function close_mp($id=null,$husky_id=null){
    $data = $this->HM->husky_mp_close($husky_id);
    $array = array(
        'id_fin_mass_payment'=>$id,
        'status'=>0
    );
    $this->MP->findOrFail($id)->update($array);
    return $data;
  }
  
  
  public function compliance_staff($id=null){
    $data = $this->SM->get($id);
    $integration = $this->HM->husky_compliance($data->husky_token);
    echo json_encode($integration);
  }

  public function compliance($id=null){
    $data = $this->CM->get($id);
    $integration = $this->HM->husky_compliance($data->husky_token);
    echo json_encode($integration);
  }
 
  public function upload_staff($data=null){
    $get_token = $this->SM->get($_POST['id']);
    $token = $get_token->husky_token;

    $data = array(
        'attachment'=>'@'.$_SERVER["DOCUMENT_ROOT"].$_POST['file'],
        "class_attribute"=>$_POST['name']
    );
    
    
    $integration = $this->HM->husky_upload($data,$token);
    echo json_encode($integration);
  }

  public function upload($data=null){
    $get_token = $this->CM->get($_POST['id']);
    $token = $get_token->husky_token;

    $data = array(
        'attachment'=>'@'.$_SERVER["DOCUMENT_ROOT"].$_POST['file'],
        "class_attribute"=>$_POST['name']
    );
    
    
    $integration = $this->HM->husky_upload($data,$token);
    echo json_encode($integration);
  }

  public function destination_staff($id=null){
    $data = $this->SM->get($id);
    $token = $data->husky_token;

    if($data->bank_tipo != '001'){
        $tipo = 'Savings Account';
    } else {
        $tipo = 'Checking Account';
    }

    $array = array(
        "bank_name"=>$data->bank_banco.' - '.$this->bank_name($data->bank_banco),
        "account_owner_name"=>$data->bank_titular,
        "account_owner_id"=>$data->bank_cpf,
        "account_number"=>$data->bank_conta,
        "account_digit"=>$data->bank_conta_numero,
        "bank_branch_id"=>$data->bank_agencia,
        "bank_branch_digit"=>$data->bank_agencia_numero,
        "destination_country"=>"Brazil",
        "account_type"=>$tipo
    );
    


    $integration = $this->HM->husky_destination($array,$token);
    
    if($integration['meta']['message'] == 'success'){
        echo json_encode($integration['meta']);
    } else {
        echo json_encode($integration);
    }
    
  }
  
  public function destination($id=null){
    $data = $this->CM->get($id);
    $token = $data->husky_token;

    if($data->bank_tipo != '001'){
        $tipo = 'Savings Account';
    } else {
        $tipo = 'Checking Account';
    }

    $array = array(
        "bank_name"=>$data->bank_banco.' - '.$this->bank_name($data->bank_banco),
        "account_owner_name"=>$data->bank_titular,
        "account_owner_id"=>$data->bank_cpf,
        "account_number"=>$data->bank_conta,
        "account_digit"=>$data->bank_conta_numero,
        "bank_branch_id"=>$data->bank_agencia,
        "bank_branch_digit"=>$data->bank_agencia_numero,
        "destination_country"=>"Brazil",
        "account_type"=>$tipo
    );

    
    $integration = $this->HM->husky_destination($array,$token);
    
    if($integration['meta']['message'] == 'success'){
        echo json_encode($integration['meta']);
    } else {
        echo json_encode($integration);
    }
    
  }

  public function destination_verify($id=null){
    $data = $this->CM->get($id);
    $token = $data->husky_token;
    $integration = $this->HM->husky_destination_verify($token);
    echo json_encode($integration);
  }
  
  public function destination_verify_staff($id=null){
    $data = $this->SM->get($id);
    $token = $data->husky_token;
    $integration = $this->HM->husky_destination_verify($token);
    echo json_encode($integration);
  }


}
