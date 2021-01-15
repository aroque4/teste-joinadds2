<?php

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Painel\StandardController;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory;
use App\Models\Painel\Prospection;
use App\Models\Painel\EmailsTemplate;
use App\Models\Painel\MessageDefault;
use Mail;
use DB;
use Defender;
use App\User;
use App\Models\Painel\Domain;
use Illuminate\Support\Facades\Hash;

class ProspectionController extends StandardController {

  protected $nameView = 'prospection';
  protected $diretorioPrincipal = 'painel';
  protected $primaryKey = 'id_prospection';

  public function __construct(Request $request, Prospection $model, Factory $validator) {
    $this->request = $request;
    $this->model = $model;
    $this->validator = $validator;
  }

  public function getCreate() {
    if (Defender::hasPermission("{$this->nameView}")) {
      $principal = $this->diretorioPrincipal;
      $rota = $this->nameView;
      $primaryKey = $this->primaryKey;

      $templates = EmailsTemplate::get();
      $messageDefault = MessageDefault::get();

      return view("{$this->diretorioPrincipal}.{$this->nameView}.create-edit", compact('principal','messageDefault', 'rota', 'primaryKey', 'templates'));
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function postStore() {
    
    if (Defender::hasPermission("{$this->nameView}")) {
    
      $dadosForm = $this->request->all();

      $check = User::where('email', $dadosForm['email'])->first();
      if(isset($check->id)){
        session(['Information' => "Endereço de email ja está cadastrado em nossa base de e-mails"]);
        return redirect("/{$this->diretorioPrincipal}/users/lead");
      }
      
      $validator = $this->validator->make($dadosForm, $this->model->rules);
      if ($validator->fails()) {
        return redirect("/{$this->diretorioPrincipal}/{$this->nameView}/create")->withErrors($validator)->withInput();
      }
      $this->model->create($dadosForm);

      $EmailsTemplate = EmailsTemplate::find($dadosForm['id_emails_template']);
      $view = fopen(resource_path('views/emails/prospeccao.blade.php' ),'w');
      fwrite($view, $EmailsTemplate->html);
      fclose($view);

      $this->sendEmail('prospeccao',['observation' => $dadosForm['message']],$dadosForm['subject'], $dadosForm['email'], "malta@beetads.com");

      $user = User::create([
          'name' => $dadosForm['name'],
          'email' => $dadosForm['email'],
          'id_user_type' => 2,
          'password' => Hash::make("123456"),
      ]);

      $domain['id_user'] = $user->id;
      $domain['name'] = $dadosForm['domain'];
      $domain['id_domain_category'] = 1;

      Domain::create($domain);

      $user->syncRoles([7]);

      return redirect("/{$this->diretorioPrincipal}/users/lead");

    } else {

      return redirect("/{$this->diretorioPrincipal}");
    }
  }

}
