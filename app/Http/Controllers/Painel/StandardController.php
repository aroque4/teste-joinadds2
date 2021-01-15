<?php

namespace App\Http\Controllers\Painel;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Defender;
use App\Models\Painel\Settings;
use Illuminate\Support\Facades\Auth;
use ZipArchive;
use Mail;

abstract class StandardController extends BaseController {

  protected $totalItensPorPagina = 100;

  public function getIndex() {
    if (Defender::hasPermission("{$this->nameView}")) {
      $data = $this->model->paginate(100);
      $principal = $this->diretorioPrincipal;
      $primaryKey = $this->primaryKey;
      $rota = $this->nameView;
      return view("{$this->diretorioPrincipal}.{$this->nameView}.index", compact('data', 'principal', 'rota', 'primaryKey'));
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function getCreate() {
    if (Defender::hasPermission("{$this->nameView}")) {
      $principal = $this->diretorioPrincipal;
      $rota = $this->nameView;
      $primaryKey = $this->primaryKey;
      return view("{$this->diretorioPrincipal}.{$this->nameView}.create-edit", compact('principal', 'rota', 'primaryKey'));
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function postStore() {
    if (Defender::hasPermission("{$this->nameView}")) {
      $dadosForm = $this->request->all();
      $validator = $this->validator->make($dadosForm, $this->model->rules);
      if ($validator->fails()) {
        return redirect("/{$this->diretorioPrincipal}/{$this->nameView}/create")->withErrors($validator)->withInput();
      }
      $this->model->create($dadosForm);
      return redirect("/{$this->diretorioPrincipal}/{$this->nameView}");
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function getShow($id) {
    if (Defender::hasPermission("{$this->nameView}")) {
      $data = $this->model->findOrFail($id);
      $principal = $this->diretorioPrincipal;
      $rota = $this->nameView;
      $primaryKey = $this->primaryKey;
      return view("{$this->diretorioPrincipal}.{$this->nameView}.create-edit", compact('data', 'principal', 'rota', 'primaryKey'));
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function postUpdate($id) {
    if (Defender::hasPermission("{$this->nameView}")) {
      $dadosForm = $this->request->all();
      $validator = $this->validator->make($dadosForm, $this->model->rules);
      if ($validator->fails()) {
        return redirect("/{$this->diretorioPrincipal}/{$this->nameView}/show/$id")->withErrors($validator)->withInput();
      }
      $this->model->findOrFail($id)->update($dadosForm);
      return redirect("/{$this->diretorioPrincipal}/{$this->nameView}");
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function postDestroy($id) {
    if (Defender::hasPermission("{$this->nameView}")) {
      $this->model->findOrFail($id)->delete();
      return redirect("/{$this->diretorioPrincipal}/{$this->nameView}");
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function uploadFile($file, $Nome, $raiz, $pasta) {
    if (Defender::hasPermission("{$this->nameView}")) {
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
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function urlAmigavel($Titulo) {
    $url_amigavel = str_slug($Titulo, "-");
    return $url_amigavel;
  }

  public function sendEmail($template, $params, $subject, $email, $responsable = "suporte@joinads.me"){
    return Mail::send("emails.$template",$params, function ($m) use($subject, $email, $responsable){
      $m->from($responsable, "joinads.me ");
      $m->to($email)->subject($subject);
    });
  }

}
