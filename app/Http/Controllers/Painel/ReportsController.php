<?php

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Painel\StandardController;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory;
use App\Models\Painel\Domain;
use App\Models\Painel\AdmanagerReport;
use App\Models\Painel\AdmanagerReportUrlCriteria;
use App\Models\Painel\GeneralEntries;
use App\Models\Painel\GeneralEntriesJoinAds;
use App\Models\Painel\GeneralEntriesAdPush;
use App\Models\Painel\InfluencersVisitsReal as InfluencersVisits;
use Illuminate\Support\Facades\Auth;
use App\User as Users;
use Defender;
use DB;

class ReportsController extends StandardController {

  protected $nameView = 'reports';
  protected $diretorioPrincipal = 'painel';
  protected $primaryKey = 'id_domain';

  public function __construct(Request $request, AdmanagerReport $model, Factory $validator) {
    $this->request = $request;
    $this->model = $model;
    $this->validator = $validator;
  }


  public function getEarnings($start_date=null,$end_date=null){

    function result($valores,$empresa,$rev_final){
      $rev        = 0;
      $fechamento = 0;
      $bruto      = 0;

      foreach ($valores as $key => $dado) {
        $total = (($dado->earnings*$dado->rev)/100);
        $final = $dado->earnings-$total;

        $bruto += $dado->earnings;
        $rev += $total;
        $fechamento += $final;
     
      }

      $business = (($rev*$rev_final)/100);

      $rev = $rev-$business ;

      return [
              'bruto'=>$bruto,
              'porcem'=>$rev_final,
              'empresa'=>$empresa,
              'rev'=>$rev,
              'final'=>$fechamento,
              'business'=>$business
            ];

    }

      $principal = $this->diretorioPrincipal;  

      $startDate = date('Y-m-d', strtotime(session('start_date')));         
      $endDate = date('Y-m-d', strtotime(session('end_date')));         


      $data_monetiza = GeneralEntries::selectRAW('date, site, SUM(earnings) as earnings, id_domain, rev, id_user, name')
      ->whereBetween('date',[$startDate, $endDate])
      ->groupBy('date','site')
      ->orderBy('date')
      ->get();
      $resultado[1] = result($data_monetiza,'Monetiza.ai',100);
      $resultado[1]['data'] = $data_monetiza;
      
      // $data_join = GeneralEntriesJoinAds::selectRAW('date, site, SUM(earnings) as earnings, id_domain, rev, id_user, name')
      // ->whereBetween('date',[$startDate, $endDate])
      // ->groupBy('date','site')
      // ->orderBy('date')
      // ->get();

      // $resultado[2] = result($data_join,'JoinAds',50);
      // $resultado[2]['data'] = $data_join;
      
      $data_adpush = GeneralEntriesAdPush::selectRAW('date, site, SUM(earnings) as earnings, id_domain, rev, id_user, name')
      ->whereBetween('date',[$startDate, $endDate])
      ->groupBy('date','site')
      ->orderBy('date')
      ->get();

      $resultado[3] = result($data_adpush,'AdPush',15);
      $resultado[3]['data'] = $data_adpush;

      $data = $resultado;

      $rota = $this->nameView;
      $primaryKey = $this->primaryKey;
      return view("{$this->diretorioPrincipal}.{$this->nameView}.earnings", compact('principal','data', 'rota', 'primaryKey'));
    
  }

  public function getMyEarnings(){
    if (Defender::hasPermission("{$this->nameView}/my-earnings")) {
      $domains = Domain::where('id_user', Auth::user()->id)->get();
      $principal = $this->diretorioPrincipal;

      $startDate = date('Y-m-d', strtotime(session('start_date')));
      $endDate = date('Y-m-d', strtotime(session('end_date')));

      $data = $this->model->join('domain','domain.name','admanager_report.site')
      ->selectRAW('admanager_report.date, SUM(impressions) impressions, SUM(clicks) clicks, AVG(ctr) ctr, AVG(ecpm_client) ecpm, SUM(earnings_client) earnings, AVG(active_view_viewable) active_view_viewable')
      ->where('id_user', Auth::user()->id)
      ->whereBetween('date',[$startDate, $endDate])
      ->where('id_domain', session('id_domain_admanager_report'))
      ->groupBy('admanager_report.date')
      ->orderBy('admanager_report.date')
      ->get();

      $rota = $this->nameView;
      $primaryKey = $this->primaryKey;
      return view("{$this->diretorioPrincipal}.{$this->nameView}.my-earnings", compact('principal','data','domains', 'rota', 'primaryKey'));
    }else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }
  
  public function getAfiliados(){
    
    $domains = Domain::where('id_user', Auth::user()->id)->get();
    $principal = $this->diretorioPrincipal;

    $startDate = date('Y-m-d', strtotime(session('start_date')));
    $endDate = date('Y-m-d', strtotime(session('end_date')));

    $afiliados = Users::where('afiliados',Auth::user()->id)->get();


    // dd($afiliados);
        
    $data = $this->model->join('domain','domain.name','admanager_report.site')
    ->selectRAW('admanager_report.date, SUM(impressions) impressions, SUM(clicks) clicks, AVG(ctr) ctr, AVG(ecpm_client) ecpm, SUM(earnings) earnings, AVG(active_view_viewable) active_view_viewable')
    ->whereBetween('date',[$startDate, $endDate])
    ->where('id_user', session('afiliados'))
    ->groupBy('admanager_report.date')
    ->orderBy('admanager_report.date')
    ->get();
    $data2 = [];
    $filhos = session('filhos');
    if(isset($filhos)){
      foreach($filhos as $key => $filho){
      $porcentagem = Users::where('id',$key)->pluck('afiliados_porcentagem');

      $data2[$key]['nome'] = $filho;
      $data2[$key]['porcentagem'] = $porcentagem[0];
      $data2[$key]['ganhos'] = $this->model->join('domain','domain.name','admanager_report.site')
        ->selectRAW('admanager_report.date, SUM(impressions) impressions, SUM(clicks) clicks, AVG(ctr) ctr, AVG(ecpm_client) ecpm, SUM(earnings) earnings, AVG(active_view_viewable) active_view_viewable')
        ->whereBetween('date',[$startDate, $endDate])
        ->where('id_user', $key)
        ->groupBy('admanager_report.date')
        ->orderBy('admanager_report.date')
        ->get();
      }
    } 

    $rota = $this->nameView;
    $primaryKey = $this->primaryKey;
    return view("{$this->diretorioPrincipal}.{$this->nameView}.afiliados", compact('principal','data','data2','domains', 'rota', 'primaryKey','afiliados'));
  
}
  
  public function getAfiliadosInfluencers(){
    
      $domains = Domain::where('id_user', Auth::user()->id)->get();
      $principal = $this->diretorioPrincipal;

      $startDate = date('Y-m-d', strtotime(session('start_date')));
      $endDate = date('Y-m-d', strtotime(session('end_date')));

      $afiliados = Users::where('afiliados',Auth::user()->id) 
                          ->where('id_user_type',4)                         
                          ->get();

                          // dd($afiliados);

      $data = InfluencersVisits::where('user_id',session('afiliados'))
                          ->whereBetween('in_visits_realtime.date',[$startDate, $endDate])
                          ->leftJoin('in_posts as Posts','Posts.post_id','in_visits_realtime.post_id')
                          ->selectRaw('in_visits_realtime.date, SUM(in_visits_realtime.session) sessoes, AVG(in_visits_realtime.cpm) cpm')
                          ->groupBy('in_visits_realtime.date')
                          ->orderBy('in_visits_realtime.date')
                          ->get();   

      $rota = $this->nameView;
      $primaryKey = $this->primaryKey;
      return view("{$this->diretorioPrincipal}.{$this->nameView}.afiliados-influencers", compact('principal','data', 'rota', 'primaryKey','afiliados'));
    
  }


  public function getAdUnits(){
    if (Defender::hasPermission("{$this->nameView}/my-earnings")) {
      $domains = Domain::where('id_user', Auth::user()->id)->get();

      $startDate = date('Y-m-d', strtotime(session('start_date')));
      $endDate = date('Y-m-d', strtotime(session('end_date')));

      $data = $this->model->join('domain','domain.name','admanager_report.site')
      ->selectRAW('admanager_report.ad_unit, SUM(impressions) impressions, SUM(clicks) clicks, AVG(ctr) ctr, AVG(ecpm_client) ecpm, SUM(earnings_client) earnings, AVG(active_view_viewable) active_view_viewable')
      ->where('id_user', Auth::user()->id)
      ->whereBetween('date',[$startDate, $endDate])
      ->where('id_domain', session('id_domain_admanager_report'))
      ->groupBy('admanager_report.ad_unit')
      ->orderBy('admanager_report.ad_unit')
      ->get();

      $principal = $this->diretorioPrincipal;
      $rota = $this->nameView;
      $primaryKey = $this->primaryKey;
      return view("{$this->diretorioPrincipal}.{$this->nameView}.ad-units", compact('principal','data','domains', 'rota', 'primaryKey'));
    }else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function getDevice(){
   if (Defender::hasPermission("{$this->nameView}/device")) {

      $domains = Domain::where('id_user', Auth::user()->id)->get();

      $startDate = date('Y-m-d', strtotime(session('start_date')));
      $endDate = date('Y-m-d', strtotime(session('end_date')));

      $dataDesktop = $this->model->join('domain','domain.name','admanager_report.site')
      ->selectRAW('"Desktop" device, SUM(impressions) impressions, SUM(clicks) clicks, AVG(ctr) ctr, AVG(ecpm_client) ecpm, SUM(earnings_client) earnings, AVG(active_view_viewable) active_view_viewable')
      ->where('id_user', Auth::user()->id)
      ->whereBetween('date',[$startDate, $endDate])
      ->where('id_domain', session('id_domain_admanager_report'))
      ->where('ad_unit', 'LIKE', '%_WEB_%');

      $dataMobile = $this->model->join('domain','domain.name','admanager_report.site')
      ->selectRAW('"Mobile" device, SUM(impressions) impressions, SUM(clicks) clicks, AVG(ctr) ctr, AVG(ecpm_client) ecpm, SUM(earnings_client) earnings, AVG(active_view_viewable) active_view_viewable')
      ->where('id_user', Auth::user()->id)
      ->whereBetween('date',[$startDate, $endDate])
      ->where('id_domain', session('id_domain_admanager_report'))
      ->where('ad_unit', 'LIKE', '%_MOBILE_%')
      ->union($dataDesktop)
      ->get();
      $data = $dataMobile;

      $principal = $this->diretorioPrincipal;
      $rota = $this->nameView;
      $primaryKey = $this->primaryKey;

      return view("{$this->diretorioPrincipal}.{$this->nameView}.device", compact('principal','data','domains', 'rota', 'primaryKey'));
    }else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function getUrlCriteria(){
    if (Defender::hasPermission("{$this->nameView}/url-criteria")) {

      

      $domains = Domain::where('id_user', Auth::user()->id)->get();

      if(session('start_date')){
        $startDate = date('Y-m-d', strtotime(session('start_date')));
      } else {
        $startDate = date('Y-m-d');
      }
      
      if(session('end_date')){
        $endDate = date('Y-m-d', strtotime(session('end_date')));
      } else {
        $endDate = date('Y-m-d');
      }


      $data = AdmanagerReportUrlCriteria::selectRAW('
                                        date,
                                        url_id,
                                        url,
                                        SUM(impressions) as impressions,
                                        SUM(clicks) as clicks, 
                                        SUM(earnings_client) as earnings_client,
                                        FORMAT(AVG(ctr),2) as ctr,
                                        FORMAT(AVG(ecpm_client),2) as ecpm_client,
                                        FORMAT(AVG(active_view_viewable),2) as active_view_viewable
                                        ')
                                        ->leftjoin('ad_unit_root','ad_unit_root.ad_unit_root_code','admanager_report_url_criteria.site')
                                        ->leftjoin('domain','domain.id_domain','ad_unit_root.id_domain')
                                        ->whereBetween('date',[$startDate,$endDate])
                                        ->where('domain.id_user',Auth::user()->id)
                                        ->groupBy('url_id')
                                        ->get();

      
      $data2 = DB::select("SELECT a.url_id,
        a.url,
        (IFNULL(SUM(a.impressions), 0) + IFNULL(SUM(b.impressions), 0)) 'impressions',
        (IFNULL(SUM(a.clicks), 0) + IFNULL(SUM(b.clicks), 0)) 'clicks',
        SUM(a.ecpm) 'ecpmMobile',
        SUM(b.ecpm) 'ecpmDesktop',
        SUM(a.earnings) 'earningsMobile',
        SUM(b.earnings) 'earningsDesktop',
        (IFNULL(SUM(a.earnings), 0) + IFNULL(SUM(b.earnings), 0)) 'earnings'
        FROM (
          SELECT url_id,
          url,
          SUM(impressions) impressions,
          SUM(clicks) clicks,
          SUM(ecpm_client) ecpm,
          SUM(earnings_client) earnings
          FROM domain d
          INNER JOIN admanager_report_url_criteria adrc ON adrc.site = d.name
          WHERE d.id_user = ".Auth::user()->id."
          AND date BETWEEN '$startDate' AND '$endDate'
          AND d.id_domain = '".session('id_domain_admanager_report')."'
          AND earnings_client IS NOT NULL
          
          AND custon_key = 'id_post_wp'
          GROUP BY adrc.url_id, adrc.url) a
          LEFT JOIN (
            SELECT url_id,
            url,
            SUM(impressions) impressions,
            SUM(clicks) clicks,
            SUM(ecpm_client) ecpm,
            SUM(earnings_client) earnings
            FROM domain d
            INNER JOIN admanager_report_url_criteria adrc ON adrc.site = d.name
            WHERE d.id_user = ".Auth::user()->id."
            AND date BETWEEN '$startDate' AND '$endDate'
            AND d.id_domain = '".session('id_domain_admanager_report')."'
            AND earnings_client IS NOT NULL
            
            AND custon_key = 'id_post_wp'
            GROUP BY adrc.url_id, adrc.url) b
            ON b.url_id = a.url_id
            GROUP BY a.url_id, a.url");

            $principal = $this->diretorioPrincipal;
            $rota = $this->nameView;
            $primaryKey = $this->primaryKey;

            return view("{$this->diretorioPrincipal}.{$this->nameView}.url-criteria", compact('principal','data','domains', 'rota', 'primaryKey'));
          }else {
            return redirect("/{$this->diretorioPrincipal}");
          }
        }


        public function postFilter($page){
          

            $dadosForm = $this->request->all();

            if(isset($dadosForm['filter'])){
              session(['filter' => $dadosForm['filter']]);
              
            }

            if(isset($dadosForm['id_domain_admanager_report'])){
              session(['id_domain_admanager_report' => $dadosForm['id_domain_admanager_report']]);
              
            }

            if(isset($dadosForm['afiliados'])){
              session(['afiliados' => $dadosForm['afiliados']]);
              $afiliados2 = Users::where('afiliados',$dadosForm['afiliados'])->pluck('name','id');
              if(isset($afiliados2)){
                session(['filhos'=>$afiliados2]);
              }
            }
            
            session(['start_date' => $dadosForm['start_date']]);
            session(['end_date' => $dadosForm['end_date']]);

            

            return redirect("{$this->diretorioPrincipal}/{$this->nameView}/$page");

        }

        public function getAdManagerAnalytcs(){
          if (Defender::hasPermission("{$this->nameView}/ad-manager-analytcs")) {
            $startDate = date('Y-m-d', strtotime(session('start_date')));
            $endDate = date('Y-m-d', strtotime(session('end_date')));

            $data = $this->model->join('domain','domain.name','admanager_report.site')
            ->selectRAW('admanager_report.site, domain.id_domain, domain.status_checklist, SUM(impressions) impressions, SUM(clicks) clicks, AVG(ctr) ctr, AVG(ecpm_client) ecpm, SUM(earnings_client) earnings, AVG(active_view_viewable) active_view_viewable')
            ->whereBetween('date',[$startDate, $endDate])
            ->groupBy('admanager_report.site')
            ->groupBy('domain.id_domain')
            ->groupBy('domain.status_checklist')
            ->get();

            $principal = $this->diretorioPrincipal;
            $rota = $this->nameView;
            $primaryKey = $this->primaryKey;

            return view("{$this->diretorioPrincipal}.{$this->nameView}.ad-manager-analytcs", compact('principal','data', 'rota', 'primaryKey'));
          } else {
            return redirect("/{$this->diretorioPrincipal}");
          }
        }

        public function getAlert(){
          if (Defender::hasPermission("{$this->nameView}/ad-manager-analytcs")) {
            $startDate = date('Y-m-d', strtotime(session('start_date')));
            $endDate = date('Y-m-d', strtotime(session('end_date')));

            $data = $this->model->join('domain','domain.name','admanager_report.site')
            ->selectRAW('admanager_report.site, domain.id_domain, domain.status_checklist, SUM(impressions) impressions, SUM(clicks) clicks, AVG(ctr) ctr, AVG(ecpm_client) ecpm, SUM(earnings_client) earnings, AVG(active_view_viewable) active_view_viewable')
            ->whereBetween('date',[$startDate, $endDate])
            ->groupBy('admanager_report.site')
            ->groupBy('domain.id_domain')
            ->groupBy('domain.status_checklist')
            ->get();

            $principal = $this->diretorioPrincipal;
            $rota = $this->nameView;
            $primaryKey = $this->primaryKey;

            return view("{$this->diretorioPrincipal}.{$this->nameView}.alert", compact('principal','data', 'rota', 'primaryKey'));
          } else {
            return redirect("/{$this->diretorioPrincipal}");
          }
        }
      }
