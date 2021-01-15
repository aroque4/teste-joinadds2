<?php

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Painel\StandardController;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory;
use App\Models\Painel\User;
use App\Models\Painel\FinHusky;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Defender;
use File;

class ProfileController extends StandardController {

    protected $nameView = 'profile';
    protected $titulo = 'Profile';
    protected $diretorioPrincipal = 'painel';
    protected $primaryKey = 'id';

    public function __construct(Request $request, User $model, Factory $validator, FinHusky $husky) {
        $this->request = $request;
        $this->model = $model;
        $this->validator = $validator;
        $this->Husky = $husky;
    }

    public function getIndex() {
        $data = $this->model->find(Auth::user()->id);
        $titulo = "Listar " . $this->titulo;
        $principal = $this->diretorioPrincipal;
        $primaryKey = $this->primaryKey;
        $banks = $this->Husky->banks();
        $bancos = $banks['data'];
        $rota = $this->nameView;
        return view("{$this->diretorioPrincipal}.{$this->nameView}.index", compact('data', 'titulo', 'principal', 'rota', 'primaryKey','bancos'));
    }

    public function postStore() {
       $dadosForm = $this->request->except('foto');

       $validator = $this->validator->make($dadosForm, $this->model->rules);
       if ($validator->fails()) {
           return redirect("/{$this->diretorioPrincipal}/profile")->withErrors($validator)->withInput();
       }

        if (!empty($this->request->file('foto'))) {
            $dadosForm['foto'] = $this->uploadFile($this->request->file('foto'), $dadosForm['name']."-foto", "site", "usuario");
        }

       $this->model->create($dadosForm);
       return redirect("/{$this->diretorioPrincipal}/{$this->nameView}");
   }

   public function postUpdate($id) {

        if($id != Auth::user()->id){
            return redirect("/{$this->diretorioPrincipal}/profile");
        }

       $dadosForm = $this->request->all();
       $validator = $this->validator->make($dadosForm, $this->model->rules);
       if ($validator->fails()) {
           return redirect("/{$this->diretorioPrincipal}/profile")->withErrors($validator)->withInput();
       }

       if (!empty($this->request->file('foto'))) {
           $dadosForm['foto'] = $this->uploadFile($this->request->file('foto'), $dadosForm['name']."-foto", "site", "usuario");
       }
       $Usuario = $this->model->findOrFail($id);

       if(isset($dadosForm['password']) && $dadosForm['password'] != NULL ){
         $dadosForm['password'] = bcrypt($dadosForm['password']);
       }else{
         unset($dadosForm['password']);
       }

       $Usuario->update($dadosForm);
       session(['Notificacao' => "Dados atualizados com sucesso!"]);

       return redirect("/{$this->diretorioPrincipal}/{$this->nameView}");
   }

   public function postUpdateTypeProfile(){
     $dadosForm = $this->request->only('type_profile');
     $this->model->findOrFail(Auth::user()->id)->update($dadosForm);
     return redirect('/painel');
   }

   public function upload($id=null){

    if($id == null){
        $id = Auth::user()->id;
    }
       
    if($_FILES){
        $files = \Request::file('file');
        
        $file = $this->uploadFile2($files, $_FILES['file']['name'], 'painel', '/documentos/'.$id);

        if($file){
            $json = array(
                        'res'=>1,
                        'msg'=>$file,
                        'dir'=>$file
                    );
        } else {
            $json = array(
                        'res'=>2,
                        'msg'=>'erro ao enviar'
                    );
        }
        echo json_encode($json);
        exit();
    }
}

public function uploadFile2($file, $Nome, $raiz, $pasta) {
  
    $urlAmigavel = $this->urlAmigavel($Nome . "-" . md5(Carbon::now() . $file->getClientOriginalName())); 

    if ($file->isValid()) {
      if ($file->getClientOriginalExtension() == "pdf" || $file->getClientOriginalExtension() == "png" || $file->getClientOriginalExtension() == "jpg" || $file->getClientOriginalExtension() == "ico" || $file->getClientOriginalExtension() == "jpeg" || $file->getClientOriginalExtension() == "gif") {
        $nomeArquivo = $urlAmigavel;
        $extensao = $file->getClientOriginalExtension();
        $file->move('assets/' . $raiz . '/uploads/' . $pasta, $nomeArquivo . ".$extensao");
        return $nomeArquivo . ".$extensao";
      } else {
        $validator[] = "Permitido apenas imagem (png ou jpeg) ou pdf";
        return redirect("/{$this->diretorioPrincipal}/{$this->nameView}/create")->withErrors($validator)->withInput();
      }
    }

}

}
