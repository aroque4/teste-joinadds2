<?php

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Painel\StandardController;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory;
use App\Models\Painel\Ticket;
use App\Models\Painel\TicketStatus;
use App\Models\Painel\TicketResponse;
use App\Models\Painel\AdmanagerReport;
use App\Models\Painel\Priority;
use App\Models\Painel\Department;
use App\Models\Painel\Domain;
use App\Models\Painel\Settings;
use Illuminate\Support\Facades\Auth;
use Defender;

class TicketController extends StandardController {

  protected $nameView = 'ticket';
  protected $diretorioPrincipal = 'painel';
  protected $primaryKey = 'id_ticket';

  public function __construct(Request $request, Ticket $model, Factory $validator) {
    $this->request = $request;
    $this->model = $model;
    $this->validator = $validator;
  }

  public function getManager($idStatus = null, $ticket = ""){

    if (Defender::hasPermission("ticket/manager")) {
      $principal = $this->diretorioPrincipal;
      $rota = $this->nameView;
      $primaryKey = $this->primaryKey;

      $ticketStatuss = TicketStatus::leftjoin('ticket','ticket.id_ticket_status','ticket_status.id_ticket_status')
      ->selectRaw('ticket_status.id_ticket_status, ticket_status.name, SUM(IF(ticket.id_ticket, 1, 0)) total')
      ->groupBy('ticket_status.id_ticket_status')
      ->groupBy('ticket_status.name')
      ->get();

      
      if($idStatus){
      $tickets = Ticket::leftjoin('users as U','U.id','ticket.id_user')
      ->join('domain','domain.id_domain','ticket.id_domain')
      ->join('users','users.id','domain.id_user')
      ->join('department','ticket.id_department','department.id_department')
      ->whereRaw("((id_ticket_status = '$idStatus' OR '$idStatus' = ''))")
      ->selectRaw('users.name, ticket.id_ticket, ticket.subject, ticket.description, ticket.created_at, domain.name domain, U.name as usuario,department.id_department,department.name as departamento')
      ->orderBy('ticket.id_ticket','DESC')
      ->get();
      } else {
        $tickets = [];
      }


      $ticketSelected = Ticket::leftjoin('users as U','U.id','ticket.id_user')
      ->join('domain','domain.id_domain','ticket.id_domain')
      ->join('users','users.id','domain.id_user')
      ->join('department','ticket.id_department','department.id_department')
      ->where('ticket.id_ticket', $ticket)
      ->selectRaw('users.id, users.name, ticket.id_ticket, ticket.subject, ticket.description, ticket.created_at, domain.name domain, U.name as usuario, department.id_department,department.name as departamento')
      ->first();

      $status = TicketStatus::get();
      
      $responses = TicketResponse::leftjoin('users as U','U.id','ticket_response.id_user')
      ->where('id_ticket', $ticket)
      ->selectRaw('id_ticket_response, response, type, id_ticket, ticket_response.id_user, ticket_response.created_at, ticket_response.updated_at, U.name as usuario')
      ->get();

      return view("{$this->diretorioPrincipal}.{$this->nameView}.manager", compact('ticketStatuss','ticket','status','ticketSelected','responses','idStatus','tickets','responses', 'rota', 'primaryKey','principal'));
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function getResponse($idStatus = "", $ticket = ""){
    if (Defender::hasPermission("ticket/response")) {
      $principal = $this->diretorioPrincipal;
      $rota = $this->nameView;
      $primaryKey = $this->primaryKey;

      if(Auth::user()->id_user_type == 4){

        $ticketStatuss = TicketStatus::leftjoin('ticket','ticket.id_ticket_status','ticket_status.id_ticket_status')
                        ->leftjoin('domain','domain.id_domain','ticket.id_domain')
                        ->selectRaw('ticket_status.id_ticket_status, ticket_status.name, SUM(IF(ticket.id_ticket, 1, 0)) total')
                        ->where('ticket.id_user', Auth::user()->id)
                        ->groupBy('ticket_status.id_ticket_status')
                        ->groupBy('ticket_status.name')
                        ->get();
        
        $tickets = Ticket::leftjoin('users as U','U.id','ticket.id_user')
        ->join('domain','domain.id_domain','ticket.id_domain')
        ->join('users','users.id','domain.id_user')
        ->whereRaw("ticket.id_user = ".Auth::user()->id." AND ((id_ticket_status = '$idStatus' OR '$idStatus' = ''))")
        ->selectRaw('users.name, users.id, ticket.id_ticket, ticket.subject, ticket.description, ticket.created_at, domain.name domain, U.name as usuario')
        ->get();

        $ticketSelected = Ticket::leftjoin('users as U','U.id','ticket.id_user')
        ->join('domain','domain.id_domain','ticket.id_domain')
        ->join('users','users.id','domain.id_user')
        ->where('ticket.id_ticket', $ticket)
        ->where('ticket.id_user', Auth::user()->id)
        ->selectRaw('users.name, ticket.id_ticket, ticket.subject, ticket.description, ticket.created_at, domain.name domain, U.name as usuario')
        ->first();

        $responses = TicketResponse::leftjoin('users as U','U.id','ticket_response.id_user')
        ->where('id_ticket', $ticket)
        ->selectRaw('id_ticket_response, response, type, id_ticket, ticket_response.id_user, ticket_response.created_at, ticket_response.updated_at, U.name as usuario')
        ->get();

      } else {

        $ticketStatuss = TicketStatus::leftjoin('ticket','ticket.id_ticket_status','ticket_status.id_ticket_status')
        ->leftjoin('domain','domain.id_domain','ticket.id_domain')
        ->selectRaw('ticket_status.id_ticket_status, ticket_status.name, SUM(IF(ticket.id_ticket, 1, 0)) total')
        ->where('domain.id_user', Auth::user()->id)
        ->groupBy('ticket_status.id_ticket_status')
        ->groupBy('ticket_status.name')
        ->get();     

        $tickets = Ticket::leftjoin('users as U','U.id','ticket.id_user')
        ->join('domain','domain.id_domain','ticket.id_domain')
        ->join('users','users.id','domain.id_user')
        ->whereRaw("users.id = ".Auth::user()->id." AND ((id_ticket_status = '$idStatus' OR '$idStatus' = ''))")
        ->selectRaw('users.name, users.id, ticket.id_ticket, ticket.subject, ticket.description, ticket.created_at, domain.name domain, U.name as usuario')
        ->get();

        $ticketSelected = Ticket::leftjoin('users as U','U.id','ticket.id_user')
        ->join('domain','domain.id_domain','ticket.id_domain')
        ->join('users','users.id','domain.id_user')
        ->where('ticket.id_ticket', $ticket)
        ->where('users.id', Auth::user()->id)
        ->selectRaw('users.name, ticket.id_ticket, ticket.subject, ticket.description, ticket.created_at, domain.name domain, U.name as usuario')
        ->first();

        $responses = TicketResponse::leftjoin('users as U','U.id','ticket_response.id_user')
        ->where('id_ticket', $ticket)
        ->selectRaw('id_ticket_response, response, type, id_ticket, ticket_response.id_user, ticket_response.created_at, ticket_response.updated_at, U.name as usuario')
        ->get();
      }

      return view("{$this->diretorioPrincipal}.{$this->nameView}.response", compact('ticketStatuss','ticketSelected','responses','idStatus','tickets','responses', 'rota', 'primaryKey','principal'));
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function postStore() {
    // if (Defender::hasPermission("ticket/create")) {
      $dadosForm = $this->request->all();

      $startDate = date('Y-m-d', strtotime(date('Y-m-d').'-30 days'));
      $endDate = date('Y-m-d');

      $earnings = AdmanagerReport::join('domain','domain.name','admanager_report.site')
      ->selectRAW('SUM(earnings_client) earnings')
      ->where('id_user', Auth::user()->id)
      ->whereBetween('date',[$startDate, $endDate])
      ->first();

      if($earnings->earnings == null){
        $earnings = 0;
      }else{
        $earnings = $earnings->earnings;
      }

      $priority = Priority::where('earnings', '<=', $earnings)->orderBy('earnings')->first();
      $dadosForm['id_priority'] = $priority->id_priority;
      $dadosForm['id_user'] = Auth::user()->id;

      $validator = $this->validator->make($dadosForm, $this->model->rules);
      if ($validator->fails()) {
        return redirect("/{$this->diretorioPrincipal}/{$this->nameView}/create")->withErrors($validator)->withInput();
      }

      $settings = Settings::first();
      $this->sendEmail('new-ticket',[],'Novo ticket aberto', $settings->email_ticket);

      $ticket = $this->model->create($dadosForm);
      $dataResponse['response'] = $ticket->description;
      $dataResponse['type'] = 1;
      $dataResponse['id_ticket'] = $ticket->id_ticket;
      $dataResponse['id_user'] = Auth::user()->id;

      TicketResponse::create($dataResponse);

      return redirect("/{$this->diretorioPrincipal}/{$this->nameView}/response");
    // } else {
    //   return redirect("/{$this->diretorioPrincipal}");
    // }
  }

  public function postResponse(){
    if (Defender::hasPermission("ticket/response")) {
      $dadosForm = $this->request->all();
      $dadosForm['id_user'] = Auth::user()->id;
      TicketResponse::create($dadosForm);
      return redirect("/{$this->diretorioPrincipal}/{$this->nameView}/".$dadosForm['redirect']."/".$dadosForm['status']."/".$dadosForm['id_ticket']);
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function getCreate() {
    if (Defender::hasPermission("ticket/response")) {
      $principal = $this->diretorioPrincipal;
      $rota = $this->nameView;
      $primaryKey = $this->primaryKey;

      $prioritys = Priority::get();
      $departments = Department::get();
      if(Auth::user()->id_user_type == 4){
        $domains = Domain::where('id_domain','801')->get();
      } else {
        $domains = Domain::where('id_user',Auth::user()->id)->get();
      }
      

      return view("{$this->diretorioPrincipal}.{$this->nameView}.create-edit", compact('principal','domains','departments', 'prioritys', 'rota', 'primaryKey'));
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }
  
  public function getSolicitarMateria() {
    // if (Defender::hasPermission("ticket/response")) {
      $principal = $this->diretorioPrincipal;
      $rota = $this->nameView;
      $primaryKey = $this->primaryKey;

      $prioritys = Priority::get();
      $departments = Department::get();
      $domains = Domain::where('id_user',Auth::user()->id)->get();

      return view("{$this->diretorioPrincipal}.{$this->nameView}.materia", compact('principal','domains','departments', 'prioritys', 'rota', 'primaryKey'));
    // } else {
    //   return redirect("/{$this->diretorioPrincipal}");
    // }
  }

  public function getShow($id) {
    if (Defender::hasPermission("{$this->nameView}")) {
      $data = $this->model->findOrFail($id);
      $principal = $this->diretorioPrincipal;
      $rota = $this->nameView;
      $primaryKey = $this->primaryKey;

      $prioritys= Priority::get();
      $prioritySelected = Priority::find($data->id_priority);

      $departments = Department::get();
      $departmentSelected = Department::find($data->id_department);

      $domains = Domain::where('id_user',Auth::user()->id)->get();
      $domainSelected = Domain::find($data->id_domain);

      return view("{$this->diretorioPrincipal}.{$this->nameView}.create-edit", compact('data','domains','departments', 'prioritys','prioritySelected','departmentSelected','domainSelected','principal', 'rota', 'primaryKey', 'promotionCategory'));
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function getChangeStatus($idticket, $status){
      $status = $this->model->find($idticket)->update(['id_ticket_status' => $status]);
      return 1;
  }
}
