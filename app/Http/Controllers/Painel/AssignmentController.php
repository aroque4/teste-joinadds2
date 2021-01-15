<?php

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Painel\StandardController;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory;
use App\Models\Painel\Priority;
use App\Models\Painel\User;
use App\Models\Painel\Assignment;
use App\Models\Painel\AssignmentUser;
use App\Models\Painel\AssignmentServiceHour;
use App\Models\Painel\AssignmentStatus;
use App\Models\Painel\TicketResponse;
use App\Models\Painel\AdmanagerReport;
use App\Models\Painel\Domain;
use App\Models\Painel\Department;
use Illuminate\Support\Facades\Auth;
use Defender;
use DB;

class AssignmentController extends StandardController {

  protected $nameView = 'assignment';
  protected $diretorioPrincipal = 'painel';
  protected $primaryKey = 'id_assignment';

  public function __construct(Request $request, Assignment $model, Factory $validator) {
    $this->request = $request;
    $this->model = $model;
    $this->validator = $validator;
  }

  public function getIndex() {
    if (Defender::hasPermission("{$this->nameView}")) {

      if(empty(session('id_assignment_status'))){
        session(['id_assignment_status' => '']);
      }

      if(empty(session('id_department'))){
        session(['id_department' => '']);
      }

      if(empty(session('user_id'))){
        session(['user_id' => '']);
      }

      if(session('id_assignment_status') == ''){
        $exclude = 4;
      }else{
        $exclude = 0;
      }

      $data = $this->model->leftjoin(DB::raw('(
        SELECT id_assignment, id_assignment_service_hour, id_user FROM (
                                    SELECT id_assignment, MAX(id_assignment_service_hour) id_assignment_service_hour, id_user
                                    FROM assignment_service_hour
                                    GROUP BY id_assignment, id_user
                                  ) x
       ) assignment_service_hour_sub'),
       function($join)
       {
          $join->on('assignment.id_assignment', '=', 'assignment_service_hour_sub.id_assignment')->where('id_user', Auth::user()->id);
       })
       ->leftjoin('assignment_service_hour','assignment_service_hour.id_assignment_service_hour','assignment_service_hour_sub.id_assignment_service_hour')
       ->leftjoin('assignment_status','assignment_status.id_assignment_status','assignment.id_assignment_status')
       ->leftjoin('users','users.id','assignment.id_client')
       ->leftjoin('domain','domain.id_domain','assignment.id_domain')
       // ->leftjoin('assignment_user','assignment_user.id_assignment','assignment.id_assignment')
      ->selectRaw('assignment.*, assignment_service_hour.status statusWork, assignment_service_hour.start, assignment_status.name status, assignment_status.color, domain.name domainName, users.name clientName')
      ->whereRaw("(assignment.id_assignment_status = '".session('id_assignment_status')."' OR '".session('id_assignment_status')."' = '')")
      ->whereRaw("(assignment.id_department = '".session('id_department')."' OR '".session('id_department')."' = '')")
      ->where("assignment.id_assignment_status", "!=", $exclude)
      ->whereRaw("(assignment_service_hour_sub.id_user = '".session('user_id')."' OR '".session('user_id')."' = '')")
      ->whereRaw("(assignment.subject LIKE '%".session('subject')."%' OR '".session('subject')."' = '')")
      ->get();

      $principal = $this->diretorioPrincipal;
      $primaryKey = $this->primaryKey;
      $rota = $this->nameView;

      $status = AssignmentStatus::get();
      $departments = Department::get();
      $users= User::join('role_user','role_user.user_id','users.id')
      ->whereIn('role_user.role_id', [1,3])
      ->get();

      $page = "";

      return view("{$this->diretorioPrincipal}.{$this->nameView}.index", compact('data','departments','users', 'page', 'status', 'principal', 'rota', 'primaryKey'));
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function getMy() {
    if (Defender::hasPermission("assignment/my")) {

      if(empty(session('id_assignment_status'))){
        session(['id_assignment_status' => '']);
        session(['subject' => '']);
      }

      if(empty(session('id_department'))){
        session(['id_department' => '']);
      }

      if(empty(session('user_id'))){
        session(['user_id' => '']);
      }

      if(session('id_assignment_status') == ''){
        $exclude = 4;
      }else{
        $exclude = 0;
      }

      $data = $this->model->leftjoin(DB::raw('(
        SELECT id_assignment, id_assignment_service_hour, id_user FROM (
                                    SELECT id_assignment, MAX(id_assignment_service_hour) id_assignment_service_hour, id_user
                                    FROM assignment_service_hour
                                    GROUP BY id_assignment, id_user
                                  ) x
       ) assignment_service_hour_sub'),
       function($join)
       {
          $join->on('assignment.id_assignment', '=', 'assignment_service_hour_sub.id_assignment')->where('id_user', Auth::user()->id);
       })
       ->leftjoin('assignment_service_hour','assignment_service_hour.id_assignment_service_hour','assignment_service_hour_sub.id_assignment_service_hour')
       ->leftjoin('assignment_status','assignment_status.id_assignment_status','assignment.id_assignment_status')
       ->leftjoin('assignment_user','assignment_user.id_assignment','assignment.id_assignment')
       ->leftjoin('users','users.id','assignment.id_client')
       ->leftjoin('domain','domain.id_domain','assignment.id_domain')
      ->selectRaw('assignment.*, assignment_service_hour.status statusWork, assignment_service_hour.start, assignment_status.name status, assignment_status.color, domain.name domainName, users.name clientName')
      ->whereRaw("(assignment.id_assignment_status = '".session('id_assignment_status')."' OR '".session('id_assignment_status')."' = '')")
      ->whereRaw("(assignment.subject LIKE '%".session('subject')."%' OR '".session('subject')."' = '')")
      ->whereRaw("(assignment.id_department = '".session('id_department')."' OR '".session('id_department')."' = '')")
      ->where("assignment.id_assignment_status", "!=", $exclude)
      ->where('assignment_user.id_user', Auth::user()->id)
      ->get();

      $principal = $this->diretorioPrincipal;
      $primaryKey = $this->primaryKey;
      $rota = $this->nameView;


      $status = AssignmentStatus::get();
      $departments = Department::get();
      $users= User::join('role_user','role_user.user_id','users.id')
      ->whereIn('role_user.role_id', [1,3])
      ->get();

      $page = "/my";

      return view("{$this->diretorioPrincipal}.{$this->nameView}.index", compact('data', 'departments', 'page','users', 'status', 'principal', 'rota', 'primaryKey'));
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function getDomainClient($idClient) {
    if (Defender::hasPermission("assignment/my") || Defender::hasPermission("assignment")) {
      $data = Domain::join('users','users.id','domain.id_user')
      ->where('domain.id_user',$idClient)
      ->selectRaw('domain.*, users.name nameClient')
      ->get();

      $options = '';

      foreach ($data as $dado) {
        $options .= '<option value="'.$dado->id_domain.'">'.$dado->name.'</option>';
      }
      return $options;
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function getReport(){
    if (Defender::hasPermission("{$this->nameView}")) {

      $startDate = date('Y-m-d', strtotime(session('start_date')));
      $endDate = date('Y-m-d', strtotime(session('end_date')));

      $data = AssignmentServiceHour::join('users', 'users.id', 'assignment_service_hour.id_user')
      ->selectRAW('name, SUM(TIMESTAMPDIFF(HOUR,start,end)) total')
      ->whereNotNull('end')
      ->whereBetween('start',[$startDate, $endDate])
      ->groupBy('users.id')
      ->groupBy('users.name')
      ->paginate($this->totalItensPorPagina);

      $principal = $this->diretorioPrincipal;
      $primaryKey = $this->primaryKey;
      $rota = $this->nameView;

      $page = "/my";

      return view("{$this->diretorioPrincipal}.{$this->nameView}.report", compact('data', 'page', 'principal', 'rota', 'primaryKey'));
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function postReport(){
    if (Defender::hasPermission("{$this->nameView}")) {
      $dadosForm = $this->request->all();

      if(isset($dadosForm['filter'])){
        session(['filter' => $dadosForm['filter']]);
      }

      session(['start_date' => $dadosForm['start_date']]);
      session(['end_date' => $dadosForm['end_date']]);

      return redirect("{$this->diretorioPrincipal}/{$this->nameView}/report");
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function postFilter($page = ""){
    $dadosForm = $this->request->all();

    session(['id_assignment_status' => $dadosForm['status']]);
    session(['subject' => $dadosForm['subject']]);
    session(['id_department' => $dadosForm['department']]);
    session(['user_id' => $dadosForm['user_id']]);
    return redirect("/{$this->diretorioPrincipal}/{$this->nameView}/$page");
  }

  public function getCreate($idTicketResponse = "") {
    if (Defender::hasPermission("assignment/my") || Defender::hasPermission("assignment")) {
      $principal = $this->diretorioPrincipal;
      $rota = $this->nameView;
      $primaryKey = $this->primaryKey;

      $prioritys= Priority::get();
      $users= User::join('role_user','role_user.user_id','users.id')
      ->whereIn('role_user.role_id', [1,3,9,10])
      ->get();

      $status = AssignmentStatus::get();
      $domains = Domain::get();
      $departments = Department::get();
      $clients = User::join('role_user','role_user.user_id','users.id')
      ->where('role_user.role_id', 4)
      ->get();

      $ticketResponse = TicketResponse::join('ticket','ticket.id_ticket','ticket_response.id_ticket')
      ->join('domain','domain.id_domain','ticket.id_domain')
      ->find($idTicketResponse);

      return view("{$this->diretorioPrincipal}.{$this->nameView}.create-edit", compact('principal','departments','ticketResponse','clients','domains' ,'status','users', 'prioritys', 'rota', 'primaryKey'));
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function getShow($id) {
    if (Defender::hasPermission("assignment/my") || Defender::hasPermission("assignment")) {
      $data = $this->model->findOrFail($id);
      $principal = $this->diretorioPrincipal;
      $rota = $this->nameView;
      $primaryKey = $this->primaryKey;

      $prioritys= Priority::get();
      $prioritySelected = Priority::find($data->id_priority);

      $status = AssignmentStatus::get();
      $statusSelected = AssignmentStatus::find($data->id_assignment_status);

      $users= User::join('role_user','role_user.user_id','users.id')
      ->whereIn('role_user.role_id', [1,3,9,10])
      ->get();
      $usersSelecteds = AssignmentUser::where('id_assignment',$id)->get();

      $domains = Domain::get();
      $domainSelected = Domain::find($data->id_domain);

      $clients = User::join('role_user','role_user.user_id','users.id')
      ->where('role_user.role_id', 4)
      ->get();

      $clientSelected = User::find($data->id_client);

      $departments = Department::get();
      $departmentSelected = Department::find($data->id_department);

      foreach ($usersSelecteds as $user) {
        $usersSelected[] = $user->id_user;
      }

      return view("{$this->diretorioPrincipal}.{$this->nameView}.create-edit", compact('data', 'departments', 'departmentSelected','clients','clientSelected','domains', 'domainSelected' ,'status', 'statusSelected','users','usersSelected', 'prioritys', 'prioritySelected','principal', 'rota', 'primaryKey'));
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function postStore() {
    if (Defender::hasPermission("assignment/my") || Defender::hasPermission("assignment")) {

      $dadosForm = $this->request->except('id_user');
      $assignmentUserSave = $this->request->only('id_user');

      $dadosForm['start_date'] = date('Y-m-d', strtotime($dadosForm['start_date']));
      $dadosForm['end_date'] = date('Y-m-d', strtotime($dadosForm['end_date']));


      $startDate = date('Y-m-d', strtotime(date('Y-m-d').'-30 days'));
      $endDate = date('Y-m-d');

      $earnings = AdmanagerReport::join('domain','domain.name','admanager_report.site')
      ->selectRAW('SUM(earnings_client) earnings')
      ->where('id_user', $dadosForm['id_client'])
      ->whereBetween('date',[$startDate, $endDate])
      ->first();

      if($earnings->earnings == null){
        $earnings = 0;
      }else{
        $earnings = $earnings->earnings;
      }

      $priority = Priority::where('earnings', '<=', $earnings)->orderBy('earnings')->first();
      $dadosForm['id_priority'] = $priority->id_priority;

      $validator = $this->validator->make($dadosForm, $this->model->rules);
      if ($validator->fails()) {
        return redirect("/{$this->diretorioPrincipal}/{$this->nameView}/create")->withErrors($validator)->withInput();
      }
      $AssignmentUser = $this->model->create($dadosForm);

      if(isset($assignmentUserSave['id_user'])){
        foreach ($assignmentUserSave['id_user'] as $value) {
          $data['id_user'] = $value;
          $data['id_assignment'] = $AssignmentUser->id_assignment;

          AssignmentUser::create($data);
        }
      }

      return redirect("/{$this->diretorioPrincipal}/assignment/my");
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function postUpdate($id) {
    if (Defender::hasPermission("assignment/my") || Defender::hasPermission("assignment")) {
      $dadosForm = $this->request->except('id_user');
      $assignmentUserSave = $this->request->only('id_user');

      $dadosForm['start_date'] = date('Y-m-d', strtotime($dadosForm['start_date']));
      $dadosForm['end_date'] = date('Y-m-d', strtotime($dadosForm['end_date']));


      $startDate = date('Y-m-d', strtotime(date('Y-m-d').'-30 days'));
      $endDate = date('Y-m-d');

      $earnings = AdmanagerReport::join('domain','domain.name','admanager_report.site')
      ->selectRAW('SUM(earnings_client) earnings')
      ->where('id_user', $dadosForm['id_client'])
      ->whereBetween('date',[$startDate, $endDate])
      ->first();

      if($earnings->earnings == null){
        $earnings = 0;
      }else{
        $earnings = $earnings->earnings;
      }


      $priority = Priority::where('earnings', '<=', $earnings)->orderBy('earnings')->first();
      $dadosForm['id_priority'] = $priority->id_priority;

      $validator = $this->validator->make($dadosForm, $this->model->rules);
      if ($validator->fails()) {
        return redirect("/{$this->diretorioPrincipal}/{$this->nameView}/show/$id")->withErrors($validator)->withInput();
      }

      $AssignmentUsersSaved = AssignmentUser::where('id_assignment', $id)->get();

      foreach($AssignmentUsersSaved as $AssignmentUserSaved){
        if(!in_array($AssignmentUserSaved->id_user, $assignmentUserSave['id_user'])){
          AssignmentUser::find($AssignmentUserSaved->id_assignment_user)->delete();
        }
        $userSaved[] = $AssignmentUserSaved->id_user;
      }

      if(empty($userSaved)){
        $userSaved = [];
      }

      foreach ($assignmentUserSave['id_user'] as $value) {
        if(!in_array($value, $userSaved)){
          $data['id_user'] = $value;
          $data['id_assignment'] = $id;
          AssignmentUser::create($data);
        }
      }

      $assignment = $this->model->findOrFail($id);
      $assignment->update($dadosForm);

      $users = User::whereIn('id', $assignmentUserSave['id_user'])->get();
      $status = AssignmentStatus::find($dadosForm['id_assignment_status']);
      foreach ($users as $user) {
        $this->sendEmail('assigment-interaction',['dataEmail' => $assignment, 'status' => $status],"Tarefa com nova interação", $user->email);
      }

      return redirect("/{$this->diretorioPrincipal}/{$this->nameView}/my");
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function getStartService($idAssignment){
    $dataForm['start'] = date('Y-m-d H:i:s');
    $dataForm['status'] = 1;
    $dataForm['id_user'] = Auth::user()->id;
    $dataForm['id_assignment'] = $idAssignment;
    AssignmentServiceHour::create($dataForm);
    return 1;
  }

  public function getStopService($idAssignment){
    $dataForm['end'] = date('Y-m-d H:i:s');
    $dataForm['status'] = 0;

    AssignmentServiceHour::where('id_user', Auth::user()->id)
    ->where('id_assignment', $idAssignment)
    ->where('status',1)
    ->update($dataForm);

    return 1;
  }

  public function postChangeStatus(){
    $dadosForm = $this->request->all();
    $update = Assignment::find($dadosForm['id_assignment'])->update(['id_assignment_status' => $dadosForm['id_assignment_status']]);
    return 1;
  }
}
