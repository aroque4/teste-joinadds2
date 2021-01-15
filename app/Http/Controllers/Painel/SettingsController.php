<?php

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Painel\StandardController;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory;
use App\Models\Painel\Settings;
use App\Models\Painel\Company;
use App\Models\Painel\User;
use Illuminate\Support\Facades\Auth;
use Defender;
use File;

class SettingsController extends StandardController {

   protected $nameView = 'settings';
   protected $diretorioPrincipal = 'painel';
   protected $primaryKey = 'id_settings';

   public function __construct(Request $request, Settings $model, Factory $validator) {
      $this->request = $request;
      $this->model = $model;
      $this->validator = $validator;
   }

   public function getEmail(){
      
      $data = $this->model->first();
      $principal = $this->diretorioPrincipal;
      $primaryKey = $this->primaryKey;
      $rota = $this->nameView;
      return view("{$this->diretorioPrincipal}.{$this->nameView}.emails", compact('data', 'principal', 'rota', 'primaryKey'));     
   }

   public function postEmail(){
      $dadosForm = $this->request->all();
      $users = User::where('status',1)->pluck('email','name');
      
      foreach($users as $name => $email){
         $info = $this->sendEmail('novidades',['observation' => $dadosForm['message']],'Olá '.$name.', '.$dadosForm['subject'], $email);
      }
     
      $data = $this->model->first();
      $principal = $this->diretorioPrincipal;
      $primaryKey = $this->primaryKey;
      $rota = $this->nameView;

      return view("{$this->diretorioPrincipal}.{$this->nameView}.emails", compact('data', 'principal', 'rota', 'primaryKey'));     
   }

   public function getIndex() {
      if (Defender::hasPermission("{$this->nameView}")) {
      $data = $this->model->first();
      $principal = $this->diretorioPrincipal;
      $primaryKey = $this->primaryKey;
      $rota = $this->nameView;
      return view("{$this->diretorioPrincipal}.{$this->nameView}.index", compact('data', 'principal', 'rota', 'primaryKey'));
      } else {
         return redirect("/{$this->diretorioPrincipal}");
      }
   }

   public function postStore() {
      if (Defender::hasPermission("{$this->nameView}")) {
         $dadosForm = $this->request->all();

         $validator = $this->validator->make($dadosForm, $this->model->rules);

         if (!empty($this->request->file('logo_white'))) {
            $dadosForm['logo_white'] = $this->uploadFile($this->request->file('logo_white'), $dadosForm['name_system'], "painel", "settings");
         }

         if (!empty($this->request->file('logo_black'))) {
            $dadosForm['logo_black'] = $this->uploadFile($this->request->file('logo_black'), $dadosForm['name_system'], "painel", "settings");
         }

         if (!empty($this->request->file('fiv_icon'))) {
            $dadosForm['fiv_icon'] = $this->uploadFile($this->request->file('fiv_icon'), $dadosForm['name_system'], "painel", "settings");
         }

         if (!empty($this->request->file('backgroud_login'))) {
            $dadosForm['backgroud_login'] = $this->uploadFile($this->request->file('backgroud_login'), $dadosForm['name_system'], "painel", "settings");
         }

         if ($validator->fails()) {
            return redirect("/{$this->diretorioPrincipal}/{$this->nameView}/create")->withErrors($validator)->withInput();
         }
         $this->model->create($dadosForm);
         return redirect("/{$this->diretorioPrincipal}/{$this->nameView}");
      } else {
         return redirect("/{$this->diretorioPrincipal}");
      }
   }

   public function postUpdate($id) {
      if (Defender::hasPermission("{$this->nameView}")) {
         $dadosForm = $this->request->all();
         $validator = $this->validator->make($dadosForm, $this->model->rules);
         $Configuracoes = $this->model->findOrFail($id);

         if ($validator->fails()) {
            return redirect("/{$this->diretorioPrincipal}/{$this->nameView}/show/$id")->withErrors($validator)->withInput();
         }

         if (!empty($this->request->file('logo_white'))) {
            file::delete('assets/painel/uploads/settings/' . $Configuracoes->logo_white);
            $dadosForm['logo_white'] = $this->uploadFile($this->request->file('logo_white'), $dadosForm['name_system'], "painel", "settings");
         }

         if (!empty($this->request->file('logo_black'))) {
            file::delete('assets/painel/uploads/settings/' . $Configuracoes->logo_black);
            $dadosForm['logo_black'] = $this->uploadFile($this->request->file('logo_black'), $dadosForm['name_system'], "painel", "settings");
         }

         if (!empty($this->request->file('fiv_icon'))) {
            file::delete('assets/painel/uploads/settings/' . $Configuracoes->fiv_icon);
            $dadosForm['fiv_icon'] = $this->uploadFile($this->request->file('fiv_icon'), $dadosForm['name_system'], "painel", "settings");
         }

         if (!empty($this->request->file('backgroud_login'))) {
            file::delete('assets/painel/uploads/settings/' . $Configuracoes->backgroud_login);
            $dadosForm['backgroud_login'] = $this->uploadFile($this->request->file('backgroud_login'), $dadosForm['name_system'], "painel", "settings");
         }

         $this->model->findOrFail($id)->update($dadosForm);
         return redirect("/{$this->diretorioPrincipal}/{$this->nameView}");
      } else {
         return redirect("/{$this->diretorioPrincipal}");
      }
   }

}
