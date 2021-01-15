<?php

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Painel\StandardController;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory;
use App\Models\Painel\Role;
use App\Models\Painel\PermissionsRole;
use App\Models\Painel\Permissions;
use Defender;

class RolesController extends StandardController {

   protected $nameView = 'roles';
   protected $diretorioPrincipal = 'painel';
   protected $primaryKey = 'id';

   public function __construct(Request $request, Role $model, Factory $validator) {
      $this->request = $request;
      $this->model = $model;
      $this->validator = $validator;
   }

   public function getPermissions($idRole){
      if (Defender::hasPermission("{$this->nameView}")) {

         $Permissoes = Permissions::where('name', '!=', NULL)->get();
         $PermissionsRule = PermissionsRole::where('role_id',$idRole)->get();

         $principal = $this->diretorioPrincipal;
         $rota = $this->nameView;
         $primaryKey = $this->primaryKey;
         return view("{$this->diretorioPrincipal}.{$this->nameView}.permissions", compact('PermissionsRule','Permissoes','principal', 'rota', 'primaryKey','idRole'));
      } else {
         return redirect("/{$this->diretorioPrincipal}");
      }
   }

   public function postPermissions($idRole){
      if (Defender::hasPermission("{$this->nameView}")) {
         $dadosForm = $this->request->only('permissao');

         $roleAdmin = Defender::findRoleById($idRole);
         if (empty($dadosForm['permissao'])) {
            $rulers = [];
         } else {
            foreach ($dadosForm['permissao'] as $permissao) {
               $rulers[$permissao] = ['value' => true];
            }
         }

         $roleAdmin->syncPermissions($rulers);
         return Redirect("{$this->diretorioPrincipal}/{$this->nameView}");

      } else {
         return redirect("/{$this->diretorioPrincipal}");
      }
   }

}
