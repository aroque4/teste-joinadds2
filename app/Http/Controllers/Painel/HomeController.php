<?php

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Painel\StandardController;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory;
use Defender;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Models\Painel\User as Profile;
use App\Models\Painel\Modal;
use App\Models\Painel\AdmanagerReport;
use App\Models\Painel\RoleUser;
use App\Models\Painel\DomainEarningsInvalid;
use App\Models\Painel\InfluencersVisitsReal as InfluencersVisits;
use DB;
use Mail;

use App\Http\Controllers\Painel\InfluencersPostsController;



class HomeController extends StandardController {

  protected $nameView = 'home';
  protected $titulo = 'Home';
  protected $diretorioPrincipal = 'painel';
  protected $Rota = 'motor-pesquisa-hu';
  protected $primaryKey = 'home';

  public function __construct(Request $request, Defender $model, Factory $validator) {
    $this->request = $request;
    $this->model = $model;
    $this->validator = $validator;
  }

  public function getIndex(){

    InfluencersPostsController::analytics(); 
    

    $modal = Modal::leftJoin('modal_user as mu', function($join)
    {
      $join->on('mu.id_modal', '=', 'modal.id_modal');
      $join->on('mu.id_user','=',DB::raw(Auth::user()->id));
    })
    ->where('status', 1)
    ->whereNull('id_modal_user')
    ->selectRaw('modal.id_modal, image, id_modal_user')
    ->first();

    $Role = RoleUser::where('user_id', Auth::user()->id)->first();
    $idRole = $Role->role_id;

    if(Auth::user()->send_mail == 0){
      $this->BoasVindas();
    }

    $startDate = date('Y-m-01',strtotime(date('Y-m-d')."- 1 months"));
    $endDate = date('Y-m-d');


    if($idRole == 8) {

      $data = InfluencersVisits::where('user_id',Auth::user()->id)
                              ->leftJoin('in_posts as Posts','Posts.post_id','in_visits_realtime.post_id')
                              ->selectRaw('in_visits_realtime.date, Posts.title, in_visits_realtime.session as sessions, in_visits_realtime.cpm, in_visits_realtime.post_id, in_visits_realtime.user_id')
                              ->get();   
                              
                              
        $today_data = $data->where('date', date('Y-m-d'));
        $today['sessions'] = 0;
        $today['receita'] = 0;
        foreach ($today_data as $td) {

          $today['sessions'] += @$td->sessions;
          $today['receita']  += ((@$td->sessions/1000)*@$td->cpm);

        }
        
        $yesterday_data = $data->where('date', date('Y-m-d', strtotime(date('Y-m-d')." -1 day")));
        $yesterday['sessions'] = 0;
        $yesterday['receita'] = 0;
        foreach ($yesterday_data as $yd) {
          $yesterday['sessions'] += @$yd->sessions;
          $yesterday['receita']  += ((@$yd->sessions/1000)*@$yd->cpm);
        }

        
        
        $month_data = $data->whereBetween('date', [date('Y-m-01'), date('Y-m-t')]);
        
        $month = [
          'sessions_total'=>0,
          'receita_total'=>0
        ];

        foreach($month_data as $dt){
          $month['sessions_total'] += $dt->sessions;
          $month['receita_total'] += (($dt->sessions/1000)*$dt->cpm);
        }
        
        $monthLast_data = $data->whereBetween('date', [date('Y-m-01', strtotime(date('Y-m-d')." -1 months")), date('Y-m-t', strtotime(date('Y-m-d')." -1 months"))]);
        
        $monthLast = [
          'sessions_total'=>0,
          'receita_total'=>0
        ];

        foreach($monthLast_data as $dt){
          $monthLast['sessions_total'] += $dt->sessions;
          $monthLast['receita_total'] += (($dt->sessions/1000)*$dt->cpm);
        }
        // dd($month);

    } else {

      if(Auth::user()->status_full_access == 1){
        $data = AdmanagerReport::selectRAW('admanager_report.date, SUM(impressions) impressions, SUM(clicks) clicks, AVG(ctr) ctr, AVG(ecpm_client) ecpm, SUM(earnings_client) earnings, SUM(earnings) earnings_total, AVG(active_view_viewable) active_view_viewable')
        ->whereBetween('date',[$startDate, $endDate])
        ->groupBy('admanager_report.date')
        ->orderBy('admanager_report.date')
        ->get();
        session(['recipe_beetads_day' => ($data->where('date', date('Y-m-d'))->SUM('earnings_total') - $data->where('date', date('Y-m-d'))->SUM('earnings'))]);
        session(['recipe_beetads_month' => ($data->where('date', '>=', date('Y-m-01'))->SUM('earnings_total') - $data->where('date', '>=', date('Y-m-01'))->SUM('earnings'))]);
      }else{
        $data = AdmanagerReport::join('domain','domain.name','admanager_report.site')
        ->selectRAW('admanager_report.date, SUM(impressions) impressions, SUM(clicks) clicks, AVG(ctr) ctr, AVG(ecpm_client) ecpm, SUM(earnings_client) earnings, AVG(active_view_viewable) active_view_viewable')
        ->where('id_user', Auth::user()->id)
        ->whereBetween('date',[$startDate, $endDate])
        ->groupBy('admanager_report.date')
        ->orderBy('admanager_report.date')
        ->get();
      }

      $earningsInvalid = DomainEarningsInvalid::join('domain','domain.id_domain','domain_earnings_invalid.id_domain')
      ->join('users','users.id', 'domain.id_user')
      ->where('users.id', Auth::user()->id)
      ->where('year', date('Y', strtotime(date('Y-m-d')." -1 months")))
      ->where('month', date('m', strtotime(date('Y-m-d')." -1 months")))
      ->selectRAW('SUM(value) value')
      ->first();

    

    $today = $data->where('date', date('Y-m-d'))->first();
    $yesterday = $data->where('date', date('Y-m-d', strtotime(date('Y-m-d')." -1 day")))->first();
    $month = $data->whereBetween('date', [date('Y-m-01'), date('Y-m-t')])->SUM('earnings');
    $monthLast = $data->whereBetween('date', [date('Y-m-01', strtotime(date('Y-m-d')." -1 months")), date('Y-m-t', strtotime(date('Y-m-d')." -1 months"))])->SUM('earnings');
    
    }

    return view("{$this->diretorioPrincipal}.{$this->nameView}.index", compact('idRole','modal','data','today','yesterday','month','monthLast','earningsInvalid'));
  }

  public function BoasVindas(){
    Mail::send('emails.boas-vindas',[], function ($m){
      $m->from("contato@joinads.me", "joinads.me");
      $m->to(Auth::user()->email)->subject(Auth::user()->name.', seja bem-vindo a joinads.me!');
    });
    Profile::find(Auth::user()->id)->update(['send_mail' => 1]);
  }

}
