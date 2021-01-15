<?php

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Painel\StandardController;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory;
use App\Models\Painel\Usuario;
use Illuminate\Support\Facades\Auth;
use App\Models\Painel\Role;
use App\Models\Painel\RoleUser;
use App\Models\Painel\ContagemCliques;
use App\Models\Painel\AdmanagerReport;
use App\Models\Painel\Configuracao;
use App\Models\Painel\User;
use App\Models\Painel\Domain;
use App\Models\Painel\Contract;
use App\Models\Painel\ContractUser;
use App\Models\Painel\Alert;
use App\Models\Painel\MessageDefault;
use App\Models\Painel\UserType;
use Illuminate\Support\Facades\Hash;
use App\Models\Painel\FinHusky;
use App\Models\Painel\Ticket;
use App\Models\Painel\FinMovimentation;
use App\Models\Painel\FinMovimentationXAdmanagerReport;
use App\Models\Painel\FinCurrency;
use App\Models\Painel\FinInvalid;
use App\User as Users;
use Defender;
use Mail;
use DB;
use Carbon\Carbon;
use File;
use Storage;

class UsersController extends StandardController {

  protected $nameView = 'users';
  protected $titulo = 'Usuario';
  protected $diretorioPrincipal = 'painel';
  protected $primaryKey = 'id';

  public function __construct(Request $request, User $model, Factory $validator, FinHusky $husky) {
    $this->request = $request;
    $this->model = $model;
    $this->validator = $validator;
    $this->Husky = $husky;
  }
  
  public function getIndex() {
    if (Defender::hasPermission("{$this->nameView}")) {
      $data = $this->model->orderBy('created_at','DESC')->paginate(1000);
      $titulo = "Listar " . $this->titulo;
      $principal = $this->diretorioPrincipal;
      $primaryKey = $this->primaryKey;
      $rota = $this->nameView;
      $Permissao = RoleUser::get();
      $idPermission = '';
      
      return view("{$this->diretorioPrincipal}.{$this->nameView}.index", compact('data','idPermission', 'titulo', 'principal', 'rota', 'primaryKey','Permissao'));
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }


  public function getCreate() {
    
      $principal = $this->diretorioPrincipal;
      $rota = $this->nameView;
      $primaryKey = $this->primaryKey;
      $data = null;
      $banks = $this->Husky->banks();
      $bancos = $banks['data'];
      $Grupos = Role::get();
      $UserTypes = UserType::get();

      return view("{$this->diretorioPrincipal}.{$this->nameView}.create", compact('data','Grupos','UserTypes','bancos','principal','rota', 'primaryKey'));
  
  }

  public function getCreateProspeccao() {
    
      $principal = $this->diretorioPrincipal;
      $rota = $this->nameView;
      $primaryKey = $this->primaryKey;
      $data = null;
      $banks = $this->Husky->banks();
      $bancos = $banks['data'];
      $Grupos = Role::get();
      $UserTypes = UserType::get();

      return view("{$this->diretorioPrincipal}.{$this->nameView}.create-prospeccao", compact('data','Grupos','UserTypes','bancos','principal','rota', 'primaryKey'));
  
  }


  public function getProspeccoes() {  

      $data = $this->model->join('role_user','role_user.user_id','users.id')
      ->whereIn('role_user.role_id', [7])
      ->paginate(1000);

      $principal = $this->diretorioPrincipal;
      $primaryKey = $this->primaryKey;
      $rota = $this->nameView;

      return view("{$this->diretorioPrincipal}.{$this->nameView}.prospeccoes", compact('data', 'principal', 'rota', 'primaryKey'));
    
  }

  public function postStoreinternal() {
    
      $dadosForm = $this->request->all();
      $validator = $this->validator->make($dadosForm, $this->model->rules);
      if ($validator->fails()) {
        return redirect("/{$this->diretorioPrincipal}/{$this->nameView}/create")->withErrors($validator)->withInput();
      }
      $data = User::create($dadosForm);
            
      RoleUser::create(['user_id'=>$data->id,'role_id'=>$dadosForm['role']]);
      
      return redirect("/{$this->diretorioPrincipal}/{$this->nameView}");
    
  }
  
  
  public function getFornecedores() {
    // if (Defender::hasPermission("{$this->nameView}")) {
      $data = $this->model
                    ->where('user_type',1)
                    ->orderBy('created_at','DESC')
                    ->paginate(1000);
      $titulo = "Listar " . $this->titulo;
      $principal = $this->diretorioPrincipal;
      $primaryKey = $this->primaryKey;
      $rota = $this->nameView;
      $Permissao = RoleUser::get();
      $idPermission = '';
      return view("{$this->diretorioPrincipal}.{$this->nameView}.fornecedores", compact('data','idPermission', 'titulo', 'principal', 'rota', 'primaryKey','Permissao'));
    // } else {
    //   return redirect("/{$this->diretorioPrincipal}");
    // }
  }
  

  public function getLead() {
    if (Defender::hasPermission("users/lead")) {

      if(session('PesquisaStatus') != 'disapproved'){
        $exclude = 0;
      }else{
        $exclude = 1;
      }

      $data = $this->model->join('role_user','role_user.user_id','users.id')
      ->join('roles','roles.id','role_user.role_id')
      ->whereIn('role_user.role_id', [2])
      ->orderBy('users.created_at','DESC')
      ->selectRaw('users.*, roles.name nameRole')
      ->where("disapproved", $exclude)
      ->paginate(1000);

      $titulo = "Leads";
      $principal = $this->diretorioPrincipal;
      $primaryKey = $this->primaryKey;
      $rota = $this->nameView;
      $Permissao = RoleUser::get();
      $idPermission = 2;

      return view("{$this->diretorioPrincipal}.{$this->nameView}.index", compact('data','idPermission', 'titulo', 'principal', 'rota', 'primaryKey','Permissao'));
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }
  
  
  public function getInfluencers() {
    if (Defender::hasPermission("users/influencers")) {

      if(session('PesquisaStatus') != 'disapproved'){
        $exclude = 0;
      }else{
        $exclude = 1;
      }

      $data = $this->model->join('role_user','role_user.user_id','users.id')
      ->join('roles','roles.id','role_user.role_id')
      ->whereIn('role_user.role_id', [8])
      ->orderBy('users.created_at','DESC')
      ->selectRaw('users.*, roles.name nameRole')
      ->where("disapproved", $exclude)
      ->paginate(1000);

      $titulo = "Influencers";
      $principal = $this->diretorioPrincipal;
      $primaryKey = $this->primaryKey;
      $rota = $this->nameView;
      $Permissao = RoleUser::get();
      $idPermission = 2;

      return view("{$this->diretorioPrincipal}.{$this->nameView}.index", compact('data','idPermission', 'titulo', 'principal', 'rota', 'primaryKey','Permissao'));
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function getSetup() {
    if (Defender::hasPermission("users/lead")) {
      $data = $this->model->join('role_user','role_user.user_id','users.id')
      ->where('role_user.role_id', 5)
      ->orderBy('created_at','DESC')
      ->paginate(1000);
      $titulo = "Leads";
      $principal = $this->diretorioPrincipal;
      $primaryKey = $this->primaryKey;
      $rota = $this->nameView;
      $Permissao = RoleUser::get();
      $idPermission = 5;

      return view("{$this->diretorioPrincipal}.{$this->nameView}.index", compact('data','idPermission', 'titulo', 'principal', 'rota', 'primaryKey','Permissao'));
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function getClients($id=null) {

    if (Defender::hasPermission("users/clients")) {

      if($id){
        $data = $this->model->join('role_user','role_user.user_id','users.id')
        ->join(DB::raw('(
                          SELECT * FROM contract_user
                          WHERE id_contract_user IN (SELECT id_contract_user
                                                      FROM (SELECT MAX(id_contract_user) id_contract_user, id_user
                                                      FROM contract_user
                                                      GROUP BY id_user) a)) contract_user'),
         function($join)
         {
            $join->on('contract_user.id_user', '=', 'users.id');
         })
        ->selectRAW('users.email,users.CPF_CNPJ, users.id, users.name, users.password, users.company, users.created_at, users.updated_at, contract_user.signature, users.status_admanager, users.disapproved, users.status_waiting, users.invite_admanager,users.gerente_contas')
        ->where('id', $id)
        ->paginate(1000);

      } else {
        $data = $this->model->join('role_user','role_user.user_id','users.id')
        ->join(DB::raw('(
          SELECT * FROM contract_user
          WHERE id_contract_user IN (SELECT id_contract_user
                                      FROM (SELECT MAX(id_contract_user) id_contract_user, id_user
                                      FROM contract_user
                                      GROUP BY id_user) a)) contract_user'),
          function($join)
          {
          $join->on('contract_user.id_user', '=', 'users.id');
          })
        ->selectRAW('users.email,users.CPF_CNPJ, users.id, users.name, users.password, users.company, users.created_at, users.updated_at,  users.status_admanager, users.disapproved, users.status_waiting, users.invite_admanager, GROUP_CONCAT(D.name) as dominios, users.gerente_contas')
        ->where('role_user.role_id', 4)
        ->join('domain as D','D.id_user','users.id')
        ->groupBy('users.id')
        ->paginate(1000);

        

      }

      $countContractUser = ContractUser::whereNotNull('signature')
      ->groupBy('id_user')
      ->selectRAW('id_user')
      ->get();
      $countContractUser = count($countContractUser);

      $countClientActive = AdmanagerReport::join('domain','domain.name', 'admanager_report.site')
      ->groupBy('domain.id_user')
      ->selectRAW('domain.id_user')
      ->whereBetween('date', [date('Y-m-d', strtotime(date('Y-m-d').'- 30 days')), date('Y-m-d')])
      ->get();

      $countClientActive = $countClientActive->count();
      $report = 1;
      $titulo = "Clientes";
      $principal = $this->diretorioPrincipal;
      $primaryKey = $this->primaryKey;
      $rota = $this->nameView;
      $Permissao = RoleUser::get();
      $idPermission = 4;
      return view("{$this->diretorioPrincipal}.{$this->nameView}.index", compact('data','report','countContractUser','countClientActive','idPermission', 'titulo', 'principal', 'rota', 'primaryKey','Permissao'));
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }


  public function getColaboradores($id=null) {

    if (Defender::hasPermission("users/clients")) {

      $data = $this->model->join('role_user','role_user.user_id','users.id')
      ->whereIn('role_user.role_id', [1,3,9,10])
      ->paginate(1000);


      $countContractUser = ContractUser::whereNotNull('signature')
      ->groupBy('id_user')
      ->selectRAW('id_user')
      ->get();
      $countContractUser = count($countContractUser);

      $countClientActive = AdmanagerReport::join('domain','domain.name', 'admanager_report.site')
      ->groupBy('domain.id_user')
      ->selectRAW('domain.id_user')
      ->whereBetween('date', [date('Y-m-d', strtotime(date('Y-m-d').'- 30 days')), date('Y-m-d')])
      ->get();

      $countClientActive = $countClientActive->count();
      $report = 1;
      $titulo = "Leads";
      $principal = $this->diretorioPrincipal;
      $primaryKey = $this->primaryKey;
      $rota = $this->nameView;
      $Permissao = RoleUser::get();
      $idPermission = 4;
      return view("{$this->diretorioPrincipal}.{$this->nameView}.colaboradores", compact('data','report','countContractUser','countClientActive','idPermission', 'titulo', 'principal', 'rota', 'primaryKey','Permissao'));
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }
  
  
  public function getBanidos($id=null) {

    if (Defender::hasPermission("users/clients")) {

      $data = $this->model->join('role_user','role_user.user_id','users.id')
      ->whereIn('role_user.role_id', [6])
      ->paginate(1000);


      $countContractUser = ContractUser::whereNotNull('signature')
      ->groupBy('id_user')
      ->selectRAW('id_user')
      ->get();
      $countContractUser = count($countContractUser);

      $countClientActive = AdmanagerReport::join('domain','domain.name', 'admanager_report.site')
      ->groupBy('domain.id_user')
      ->selectRAW('domain.id_user')
      ->whereBetween('date', [date('Y-m-d', strtotime(date('Y-m-d').'- 30 days')), date('Y-m-d')])
      ->get();

      $countClientActive = $countClientActive->count();
      $report = 1;
      $titulo = "Leads";
      $principal = $this->diretorioPrincipal;
      $primaryKey = $this->primaryKey;
      $rota = $this->nameView;
      $Permissao = RoleUser::get();
      $idPermission = 4;
      return view("{$this->diretorioPrincipal}.{$this->nameView}.banidos", compact('data','report','countContractUser','countClientActive','idPermission', 'titulo', 'principal', 'rota', 'primaryKey','Permissao'));
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }
  
  public function getAfiliados($id=null) {

    if (Defender::hasPermission("users/clients")) {

      $data = $this->model->join('role_user','role_user.user_id','users.id')
      ->whereIn('role_user.role_id', [8])
      ->paginate(1000);


      $countContractUser = ContractUser::whereNotNull('signature')
      ->groupBy('id_user')
      ->selectRAW('id_user')
      ->get();
      $countContractUser = count($countContractUser);

      $countClientActive = AdmanagerReport::join('domain','domain.name', 'admanager_report.site')
      ->groupBy('domain.id_user')
      ->selectRAW('domain.id_user')
      ->whereBetween('date', [date('Y-m-d', strtotime(date('Y-m-d').'- 30 days')), date('Y-m-d')])
      ->get();

      $countClientActive = $countClientActive->count();
      $report = 1;
      $titulo = "Afiliados";
      $principal = $this->diretorioPrincipal;
      $primaryKey = $this->primaryKey;
      $rota = $this->nameView;
      $Permissao = RoleUser::get();
      $idPermission = 4;
      return view("{$this->diretorioPrincipal}.{$this->nameView}.index", compact('data','report','countContractUser','countClientActive','idPermission', 'titulo', 'principal', 'rota', 'primaryKey','Permissao'));
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function postPesquisa() {
    if (Defender::hasPermission("users/lead") || Defender::hasPermission("users") || Defender::hasPermission("users/clients")) {
      $dadosForm = $this->request->all();
      session(['PesquisaCampo' => $dadosForm['campo']]);
      session(['PesquisaValor' => $dadosForm['valor']]);
      session(['PesquisaStatus' => $dadosForm['status']]);
      session(['PesquisaIdPermission' => $dadosForm['idPermission']]);
      if(empty(session('PesquisaStatus'))){
        session(['PesquisaStatus' => 1]);
      }

      if(session('PesquisaStatus') != 'disapproved'){
        $exclude = 0;
      }else{
        $exclude = 1;
      }

      $data = $this->model->join('role_user','role_user.user_id','users.id')
      ->where($dadosForm['campo'],'LIKE',"%".$dadosForm['valor']."%")
      ->whereRaw("(".session('PesquisaStatus')." = 1 OR '".session('PesquisaStatus')."' = '')")
      ->where("disapproved", $exclude)
      ->whereRaw("(role_user.role_id = '".session('PesquisaIdPermission')."' OR '".session('PesquisaIdPermission')."' = '')")
      ->paginate(1000);

      $titulo = "Listar " . $this->titulo;
      $principal = $this->diretorioPrincipal;
      $primaryKey = $this->primaryKey;
      $rota = $this->nameView;
      $Permissao = RoleUser::get();
      $idPermission = $dadosForm['idPermission'];
      return view("{$this->diretorioPrincipal}.{$this->nameView}.index", compact('data','idPermission', 'titulo', 'principal', 'rota', 'primaryKey','Permissao'));
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function getPesquisa() {
    if (Defender::hasPermission("users/lead") || Defender::hasPermission("users") || Defender::hasPermission("users/clients")) {
      $dadosForm = $this->request->all();
      if(empty(session('PesquisaStatus'))){
        session(['PesquisaStatus' => 1]);
      }

      if(session('PesquisaStatus') != 'disapproved'){
        $exclude = 0;
      }else{
        $exclude = 1;
      }

      $data = $this->model->join('role_user','role_user.user_id','users.id')
      ->where(session('PesquisaCampo'),'LIKE',"%".session('PesquisaValor')."%")
      ->whereRaw("(".session('PesquisaStatus')." = 1 OR '".session('PesquisaStatus')."' = '')")
      ->where("disapproved", $exclude)
      ->whereRaw("(role_user.role_id = ".session('PesquisaIdPermission')." OR '".session('PesquisaIdPermission')."' = '')")
      ->paginate(1000);

      $titulo = "Listar " . $this->titulo;
      $principal = $this->diretorioPrincipal;
      $primaryKey = $this->primaryKey;
      $rota = $this->nameView;
      $Permissao = RoleUser::get();
      $idPermission = session('PesquisaIdPermission');
      return view("{$this->diretorioPrincipal}.{$this->nameView}.index", compact('data', 'idPermission', 'titulo', 'principal', 'rota', 'primaryKey','Permissao'));
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function getShow($id) {
    session(['urlAnterior' => $referrer = $this->request->headers->get('referer')]);
    if (Defender::hasPermission("users/lead") || Defender::hasPermission("users") || Defender::hasPermission("users/clients")) {
      $data = $this->model->findOrFail($id);
      $titulo = "Editar " . $this->titulo;
      $principal = $this->diretorioPrincipal;
      $rota = $this->nameView;
      $primaryKey = $this->primaryKey;
      $banks = $this->Husky->banks();
      $bancos = $banks['data'];

      $Grupos = Role::get();
      $GrupoSelecionado = RoleUser::where('user_id', $id)->first();

      $users = User::pluck('name','id');

      $UserTypes = UserType::get();
      $UserTypeSelected = UserType::where('id_user_type', $data->id_user_type)->first();

      $domains = Domain::join('users','users.id','domain.id_user')
      ->where('domain.id_user',$id)
      ->selectRaw('domain.*, users.name nameClient')
      ->paginate(1000);

      $tickets = $this->model->join('domain','domain.id_user','users.id')
      ->join('ticket','ticket.id_domain','domain.id_domain')
      ->where('users.id', $id)
      ->orderBy('ticket.id_ticket','DESC')
      ->selectRaw('ticket.id_ticket, users.name, ticket.subject, ticket.description, ticket.created_at, domain.name domain')
      ->get();

      $currencies   = FinCurrency::pluck('abbreviation','id_fin_currency');

      $finans = FinMovimentation::leftJoin('users as U','U.id','=','id_client')
      ->orderBy('id_fin_movimentation','desc')
      ->where('id_client',$id)
      ->paginate(10000);

      if(!empty($finans)){

        foreach($finans as $key => $info){
          $validation = $this->getSubQuery($info->id_fin_movimentation);
          if($validation){
            $informations[$key]['id_user'] = $info->id_client;
            $informations[$key]['id_fin_movimentation'] = $info->id_fin_movimentation;
            $informations[$key]['date_expiry'] = $info->date_expiry;
            $informations[$key]['value'] = $info->value;
            $informations[$key]['tax'] = $info->tax;
            $informations[$key]['status'] = $info->status;
            $informations[$key]['type'] = $info->type;
            $informations[$key]['file'] = $info->file;
            $informations[$key]['id_fin_currency'] = $info->id_fin_currency;
            $informations[$key]['report'] = $this->getSubQuery($info->id_fin_movimentation);
          }
        }

        if(isset($informations)){
          $finans = $informations;
        }
      }

      $adx = $this->getFinanAdx($id);
      
      if($adx){
        $adx_report = $adx[$id]['report']['dados'];
        foreach($adx_report as $key => $test){
        
          foreach($test as $t){
            if(is_array($t)){  
              foreach($t as $b){
                $adx_report[$key]['value'] += $b['total'];
              }
            }
          }
         }
      } else {
        $adx_report = null;
      }    

      return view("{$this->diretorioPrincipal}.{$this->nameView}.create-edit", compact('data','domains','UserTypes','UserTypeSelected', 'titulo', 'principal', 'rota', 'primaryKey', 'Grupos', 'GrupoSelecionado','bancos','tickets','currencies','finans', 'adx','adx_report','users'));
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function postStore() {
    if (Defender::hasPermission("users/lead") || Defender::hasPermission("users") || Defender::hasPermission("users/clients")) {
      $dadosForm = $this->request->except('foto');

      $validator = $this->validator->make($dadosForm, $this->model->rules);
      if ($validator->fails()) {
        return redirect("/{$this->diretorioPrincipal}/perfil")->withErrors($validator)->withInput();
      }

      if (!empty($this->request->file('foto'))) {
        $dadosForm['foto'] = $this->uploadFile($this->request->file('foto'), $dadosForm['name']."-foto", "site", "usuario");
      }

      $this->model->create($dadosForm);
      return redirect("/{$this->diretorioPrincipal}/{$this->nameView}");
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function getFinanAdx($id){
    $sql_back = "
        select
            P.id_user,
            C.company,
            C.name,
            TR.site,
            C.husky_token,
            GROUP_CONCAT(DISTINCT TR.id_admanager_report SEPARATOR ',') as ids,
            sum(TR.impressions) as impressoes,
            sum(TR.clicks) as cliques,
            sum(TR.earnings_client) as total ,
            MONTH(TR.date) as date,
            YEAR(TR.date) as year,
            PP.final_value
        from
            admanager_report TR
        LEFT JOIN
            domain as P ON P.name = site
        LEFT JOIN
            users as C ON P.id_user = C.id
        LEFT JOIN
            fin_pre_payment as PP ON PP.id_client = P.id_user
        WHERE
            P.id_user = $id
        AND
          status_payment = 0
        group by TR.site,date,year,P.id_user,C.name,C.husky_token,PP.final_value
        ORDER BY date

    ";
    
    $informations_back =  DB::select($sql_back);
    $semitotal = 0;
    foreach($informations_back as $info2){
      if($info2->id_user){

        $array2[$info2->id_user]['id_user'] = $info2->id_user;
        $array2[$info2->id_user]['company'] = $info2->company;
        $array2[$info2->id_user]['name'] = $info2->name;

        $mesano = $info2->date.'/'.$info2->year;

        $array2[$info2->id_user]['report']['dados'][$mesano]['data'][] = array(
          'ids'=>$info2->ids,
          'id_domain'=>Domain::where('name',$info2->site)->first()->id_domain,
          'domain'=>$info2->site,
          'total'=>$info2->total,
          'status'=>Domain::where('name',$info2->site)->first()->id_domain_status,
        );
        $array2[$info2->id_user]['report']['dados'][$mesano]['month'] = $info2->date;
        $array2[$info2->id_user]['report']['dados'][$mesano]['year'] = $info2->year;

        $invalid = FinInvalid::selectRaw('SUM(value) as total')
                          ->where('id_client',$info2->id_user)
                          ->where('month',$info2->date)
                          ->where('year',$info2->year)
                          ->groupBy('id_client')
                          ->first();
        
        if(isset($invalid)){
          $valinvalid = $invalid->total;
        } else {
          $valinvalid = 0;
        }

        $array2[$info2->id_user]['report']['dados'][$mesano]['invalid'] = $valinvalid;
        $array2[$info2->id_user]['report']['dados'][$mesano]['value'] = 0;
 

        if(empty($array2[$info2->id_user]['geral_ids'])){
          $array2[$info2->id_user]['geral_ids'] = $info2->ids;
        } else {
          $array2[$info2->id_user]['geral_ids'] .= ','.$info2->ids;
        }

        $id_domain_status = Domain::where('name',$info2->site)->first()->id_domain_status;


          if(empty($array2[$info2->id_user]['total'])){
            $array2[$info2->id_user]['total'] = 0;
            $array2[$info2->id_user]['total'] += $info2->total;
          } else {
            $array2[$info2->id_user]['total'] += $info2->total;
          }

      }
    }

    if(empty($array2)){
      $array2 = [];
    }

    $informations_back = $array2;

    
    return $array2;


  }


  public function getSubQuery($id){
    $informations = array();
      // echo $id.'<br />';
      $subitens = FinMovimentationXAdmanagerReport::where('id_fin_movimentation',$id)->pluck('id_admanager_report');

      if(!empty($subitens[0])) {

        foreach($subitens as $subiten){
          $ids[] = $subiten;
        }

        $ids = implode(',',$ids);

        $sql = "
          select
              P.id_user,
              C.company,
              C.name,
              TR.site,
              C.husky_token,
              GROUP_CONCAT(DISTINCT TR.id_admanager_report SEPARATOR ',') as ids,
              sum(TR.impressions) as impressoes,
              sum(TR.clicks) as cliques,
              sum(TR.earnings_client) as total ,
              max(TR.date) as date,
              PP.final_value
          from
              admanager_report TR
          LEFT JOIN
              domain as P ON P.name = site
          LEFT JOIN
              users as C ON P.id_user = C.id
          LEFT JOIN
              fin_pre_payment as PP ON PP.id_client = P.id_user
          where
            id_admanager_report in ($ids)
          group by TR.site,P.id_user,C.name,C.husky_token,PP.final_value

      ";

        $info = DB::select($sql);
        return $info;
    } else {
      return false;
    }
  }

  public function postUpdate($id) {
    session(['urlAnterior' => $referrer = $this->request->headers->get('referer')]);
    if (Defender::hasPermission("users/lead") || Defender::hasPermission("users") || Defender::hasPermission("users/clients")) {
      $dadosForm = $this->request->all();

      if(($dadosForm['status_admanager'] == '1' and $dadosForm['role'] == '7') or ($dadosForm['status_admanager'] == '1' and $dadosForm['role'] == '2')){
        $dadosForm['role'] = 5;
      }

      if($dadosForm['disapproved'] == 1){
        $dadosForm['role'] = 6;
      }

      $userRole = $this->model->join('role_user', 'role_user.user_id', 'users.id')
      ->where('users.id', $id)
      ->first();

      if($userRole->role_id != $dadosForm['role']){
        if($dadosForm['role'] == 6){

          $domains = Domain::where('id_user', $id)->get();
          foreach($domains as $domain){
            Storage::disk('do_spaces')->put("/crm/".$domain->id_domain."/$domain->file_do", "", 'public');
          }

          $message = MessageDefault::find(2);
          // Mail::send('emails.banned',['title' => $message->subject, 'observation' => $message->message], function ($m) use($userRole, $message){
          //   $m->from("contato@joinads.me", "joinads.me");
          //   $m->to($userRole->email)->subject($message->subject);
          // });
        }

        if($dadosForm['role'] == 8){
          
          $message = MessageDefault::find(6);
          Mail::send('emails.banned',['title' => $message->subject, 'observation' => $message->message], function ($m) use($userRole, $message){
            $m->from("contato@joinads.me", "joinads.me");
            $m->to($userRole->email)->subject($message->subject);
            // $m->to('caio@caionorder.com')->subject($message->subject);
          });

        }
        
        if($dadosForm['role'] == 4){
          
          $message = MessageDefault::find(7);
          Mail::send('emails.banned',['title' => $message->subject, 'observation' => $message->message], function ($m) use($userRole, $message){
            $m->from("contato@joinads.me", "joinads.me");
            $m->to($userRole->email)->subject($message->subject);
            // $m->to('caio@caionorder.com')->subject($message->subject);
          });

        }



      }

      $user = Users::find($id);
      if(isset($dadosForm['role'])){
        $roles = [$dadosForm['role']]; // Using an array of ids
        $user->syncRoles($roles);

        if($dadosForm['role'] == 4){
          $contract = Contract::where('status', 1)->first();
          $dataContract['start_date'] = date('Y-m-d');
          $dataContract['end_date'] = date('Y-m-d', strtotime(date('Y-m-d').'+1 Years'));
          $dataContract['id_user'] = $user->id;
          $dataContract['id_contract'] = $contract->id_contract;

        
          $contrato = ContractUser::where('id_user',$user->id)->first();;          

          if(is_null($contrato)){
            ContractUser::create($dataContract);
          }
          

          // Mail::send('emails.contrato',[], function ($m){
          //   $m->from("contato@joinads.me", "joinads.me");
          //   $m->to(Auth::user()->email)->subject(Auth::user()->name.', seu contrato ja está disponível!');
          // });

          // $this->getResetPassword($id);
        }
      }

      $validator = $this->validator->make($dadosForm, $this->model->rules);
      if ($validator->fails()) {
        return redirect("/{$this->diretorioPrincipal}/users/show/$id")->withErrors($validator)->withInput();
      }

      if(isset($dadosForm['password']) && $dadosForm['password'] != NULL ){
        $dadosForm['password'] = bcrypt($dadosForm['password']);
      }else{
        unset($dadosForm['password']);
      }



      $this->model->findOrFail($id)->update($dadosForm);
      $user = $this->model->findOrFail($id);

      if(isset($user->invite_admanager) && $user->send_invite_admanager == 0){
        Mail::send('emails.invite-admanager',['invite_admanager' => $dadosForm['invite_admanager']], function ($m) use($user){
          $m->from("contato@joinads.me", "joinads.me");
          $m->to($user->email)->subject($user->name.', Novidades para você!');
        });
        $this->model->findOrFail($id)->update(['send_invite_admanager' => 1]);
      }

      if(isset($user->observation_disapproved) && $user->disapproved == 1){
        // Mail::send('emails.disapproved',['observation' => $user->observation_disapproved], function ($m) use($user){
        //   $m->from("contato@joinads.me", "joinads.me");
        //   $m->to($user->email)->subject($user->name.', Novidades para você!');
        // });
      }



      return redirect(session('urlAnterior'));
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
    return redirect()->previous();
  }


  public function getAlert($idUser){
    if (Defender::hasPermission("users/lead") || Defender::hasPermission("users") || Defender::hasPermission("users/clients")) {

      $principal = $this->diretorioPrincipal;
      $rota = $this->nameView;
      $primaryKey = $this->primaryKey;

      $data = Alert::where('id_user', $idUser)->get();
      $messageDefault = MessageDefault::get();

      return view("{$this->diretorioPrincipal}.{$this->nameView}.alert", compact('data','idUser','principal','rota','primaryKey','messageDefault'));
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function postAlert($idUser){
    if (Defender::hasPermission("users/lead") || Defender::hasPermission("users") || Defender::hasPermission("users/clients")) {

      $dadosForm = $this->request->all();
      $alert = Alert::where('id_user', $idUser)->first();

      $dadosForm['id_user'] = $idUser;
      Alert::create($dadosForm);
      $user = $this->model->find($idUser);
      $this->sendEmail('alert',['observation' => $dadosForm['text'], 'title' => $dadosForm['title']],$dadosForm['title'], $user->email);

      return redirect("/{$this->diretorioPrincipal}/{$this->nameView}/alert/{$idUser}");
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function getResetPassword($id){

    if (Defender::hasPermission("users/lead") || Defender::hasPermission("users") || Defender::hasPermission("users/clients")) {
      $user = Users::find($id);
      $password = base_convert(uniqid('pass', true), 10, 36);
      $dadosForm['password'] = bcrypt($password);

      $user->update($dadosForm);
      $roles = [4];
      $user->syncRoles($roles);

      Mail::send('emails.reset-password',['password' => $password, 'email' => $user->email], function ($m) use($user){
        $m->from("contato@joinads.me", "joinads.me");
        $m->to($user->email)->subject($user->name.', Definição de senha!');
      });

      session(['Notificacao' => 'Boas notícias para você ;)']);
      return redirect("/{$this->diretorioPrincipal}/{$this->nameView}");
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }

  }

  public function getAuthUser($id = "", $hash = ""){
    if(Auth::check()){
      $user = Users::find($id);
      if(isset($user->id)){
        if(str_replace('/','',$user->password) == $hash){
          session(['idLoginAdmin' => Auth::user()->id]);
          Auth::login($user);
        }
      }
    }
    return redirect('/painel');
  }

  public function getAuthAdmin(){
    if(session('idLoginAdmin')){
        $user = Users::find(session('idLoginAdmin'));
        Auth::login($user);
        $request = new Request();
        session(['idLoginAdmin' => null]);
        return redirect('/painel');
    }
  }

  public function RegisterSite(Request $request){
    $data = $request->all();

    $check = Users::where('email', $data['email'])->first();
    if(empty($check->id)){
      $user = Users::create([
        'name' => $data['name_user'],
        'email' => $data['email'],
        'whatsapp' => $data['whatsapp'],
        'id_user_type' => 2,
        'password' => Hash::make("12345678"),
      ]);

      $newUser = Users::where('email', $data['email'])->first();
      DB::insert("INSERT INTO role_user (user_id, role_id) VALUES ($newUser->id, 2);");

      $domain['id_user'] = $newUser->id;
      $domain['name'] = str_replace(['https://', 'http://'],'',$data['domain']);
      $domain['page_views'] = $data['page_views'];
      $domain['id_domain_category'] = 1;
      Domain::create($domain);

      Mail::send('emails.boas-vindas',[], function ($m) use($user){
        $m->from("contato@joinads.me", "joinads.me");
        $m->to($user->email)->subject($user->name.', seja bem-vindo a joinads.me!');
      });
      Profile::find($user->id)->update(['send_mail' => 1]);

    }
    return 1;
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

  public function husky_create(){
    $data = User::find($_POST['id']);
    $name = explode(' ',$data->name);
    $cliente_id = "iX2Fp31b9EFVi9YoA6dt0KNHS2RWVuEBDKNMuWYrKOE";
    $secret_id  = "YpL66KYmbXGCEMJqdQ8Crbfy87wG3P4Wjme0YZOcc_Y";

    $data->CPF_CNPJ = str_replace('-','',str_replace('.','',$data->CPF_CNPJ));

    if(strlen($data->CPF_CNPJ) > 11){
      $tipo = 'business';
    } else {
      $tipo = 'individual';
    }

    $array = array(
        "first_name" =>  $name[0],
        "last_name" => @$name[1].' '.@$name[2].' '.@$name[3],
        "email"=>$data->email,
        "cell_phone"=>$data->whatsapp,
        "password"=>"beets1987",
        "password_confirmation"=>"beets1987",
        "account_type"=>$tipo,
        "document_id"=>$data->CPF_CNPJ,
        "client_id"=>$cliente_id,
        "client_secret"=>$secret_id,
        "zipcode"=>$data->cep
    );

    $integration = $this->Husky->husky_create($array);
    
    if($integration['meta']['message'] == 'success'){
          $update['husky_token'] = $integration['data']['token'];
          
          if($data->update($update)){
              echo $this->husky_destination($data->id);
          } else {
              echo $this->husky_destination($data->id);
          }

    } else {
      echo json_encode($integration);
    }

  }

  public function husky_destination($id=null){
    if(!$id){
      $id = $_POST['id'];
    }
    $data =  User::find($id);

    $token = $data->husky_token;

    if($data->bank_type == 1){
      $tipo = 'Checking Account';
    } else {
      $tipo = 'Savings Account';
    }

    if(strlen($data->CPF_CNPJ) > 11){
      $nome = $data->company;
    } else {
      $nome = $data->name;
    }

    $banks = $this->Husky->banks();

    $array = array(
        "bank_name"=>$banks['data'][$data->bank],
        "account_owner_name"=>$nome,
        "account_owner_id"=>$data->CPF_CNPJ,
        "account_number"=>$data->account,
        "account_digit"=>$data->digit,
        "bank_branch_id"=>$data->agency,
        "bank_branch_digit"=>$data->agency_digit,
        "destination_country"=>"Brazil",
        "account_type"=>$tipo
    );

    $integration = $this->Husky->husky_destination($array,$token);

    if($integration['meta']['message'] == 'success'){
        echo json_encode($integration['meta']);
    } else {
        echo json_encode($integration);
    }
  }


  public function getSendMailFull(){

    $data = $this->model->join('role_user','role_user.user_id','users.id')->whereIn('role_id', [4,1])->get();
    foreach($data as $user){
      Mail::send('emails.default',[], function ($m) use($user){
        $m->from("contato@joinads.me", "joinads.me");
        $m->to($user->email)->subject($user->name.', manutenção finalizada!');
      });
    }
    return back();
  }

}
