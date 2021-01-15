<?php

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Painel\StandardController;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory;
use App\Models\Painel\Article;
use App\Models\Painel\Role;
use App\Models\Painel\ArticleRole;
use App\Models\Painel\RoleUser;
use Illuminate\Support\Facades\Auth;
use Defender;

class ArticleController extends StandardController {

  protected $nameView = 'article';
  protected $diretorioPrincipal = 'painel';
  protected $primaryKey = 'id_article';

  public function __construct(Request $request, Article $model, Factory $validator) {
    $this->request = $request;
    $this->model = $model;
    $this->validator = $validator;
  }

  public function getArticles() {
    if (Defender::hasPermission("article/articles")) {

      $idRole = RoleUser::where('user_id', Auth::user()->id)->first()->role_id;

      $data = $this->model->join('article_role','article_role.id_article','article.id_article')
      ->where('id_role', $idRole)
      ->paginate($this->totalItensPorPagina);

      $principal = $this->diretorioPrincipal;
      $primaryKey = $this->primaryKey;
      $rota = $this->nameView;
      return view("{$this->diretorioPrincipal}.{$this->nameView}.articles", compact('data', 'principal', 'rota', 'primaryKey'));
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function getEye($id) {
    if (Defender::hasPermission("article/articles")) {
      $data = $this->model->findOrFail($id);
      $principal = $this->diretorioPrincipal;
      $rota = $this->nameView;
      $primaryKey = $this->primaryKey;

      return view("{$this->diretorioPrincipal}.{$this->nameView}.article", compact('data', 'principal', 'rota', 'primaryKey'));
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function getCreate() {
    if (Defender::hasPermission("{$this->nameView}")) {
      $principal = $this->diretorioPrincipal;
      $groups = Role::get();

      $rota = $this->nameView;
      $primaryKey = $this->primaryKey;
      return view("{$this->diretorioPrincipal}.{$this->nameView}.create-edit", compact('principal', 'rota', 'primaryKey', 'groups'));
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

      $selecteds = ArticleRole::where('id_article',$data->id_article)->get();
      $groups = Role::get();

      foreach($selecteds as $selected){
        $groupsSelected[] = $selected->id_role;
      }

      return view("{$this->diretorioPrincipal}.{$this->nameView}.create-edit", compact('data', 'principal', 'rota', 'primaryKey', 'groups','groupsSelected'));
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

      $article = $this->model->create($dadosForm);

      foreach ($dadosForm['id_role'] as $value) {
        $data['id_role'] = $value;
        $data['id_article'] = $article->id_article;
        ArticleRole::create($data);
      }

      return redirect("/{$this->diretorioPrincipal}/{$this->nameView}");
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

      $ArticlesUsersSaved = ArticleRole::where('id_article', $id)->get();

      foreach($ArticlesUsersSaved as $ArticleUsersSaved){
        if(!in_array($ArticleUsersSaved->id_role, $dadosForm['id_role'])){
          ArticleRole::find($ArticleUsersSaved->id_article_role)->delete();
        }
        $roleSaved[] = $ArticleUsersSaved->id_role;
      }

      if(empty($roleSaved)){
        $roleSaved[] = '';
      }
      foreach ($dadosForm['id_role'] as $value) {
        if(!in_array($value, $roleSaved)){
          $data['id_role'] = $value;
          $data['id_article'] = $id;
          ArticleRole::create($data);
        }
      }

      $this->model->findOrFail($id)->update($dadosForm);
      return redirect("/{$this->diretorioPrincipal}/{$this->nameView}");
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }
}
