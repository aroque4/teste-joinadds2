<?php

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Painel\StandardController;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory;
use App\Models\Painel\InfluencersPosts;
use App\Models\Painel\InfluencersVisits;
use App\Models\Painel\InfluencersInvoices;
use App\Models\Painel\Domain;
use App\Models\Painel\RoleUser;
use App\Models\Painel\Settings;
use App\Models\Painel\InfluencersVisitsReal;
use App\Models\Painel\User;
use Illuminate\Support\Facades\Auth;
use Defender;
use File;
use Image;
use Analytics;
use App\Helpers\Helper;
use Spatie\Analytics\Period;
use Exception;
use DB;
use Mail;

class InfluencersPostsController extends StandardController {

  protected $nameView = 'influencers';
  protected $diretorioPrincipal = 'painel';
  protected $primaryKey = 'id';

  public function __construct(Request $request, InfluencersPosts $model, Factory $validator) {
    $this->request = $request;
    $this->model = $model;
    $this->validator = $validator;
  }

  public function getPosts(){
    
    $dominios = Domain::where('id_prebid_version','1')->pluck('name','id_domain');
    $save = [];
    
    foreach ($dominios as $id => $dominio) {
        $posts = json_decode(file_get_contents('https://'.$dominio.'/wp-content/plugins/joinads-influencers/api.php'),true);
        $posts = array_reverse($posts,true);
        foreach($posts as $k => $post){
           



            if(!$this->valid($post['id'],$id)){
                $save[$k] = [
                  'domain_id'=>$id,
                  'post_id'=>$post['id'],
                  'title'=>$post['title'],
                  'link'=>$post['link'],
                  'thumb'=>$post['images']['destacada'],
                  'description'=>$post['content'][0],
                  'category'=>serialize($post['category'])
                ];
            }
        }
    }

    if(count($save) > 0){
      $saved = InfluencersPosts::insert($save);
      if($saved){
        echo 1;
      } else {
        echo 2;
      }
    }
    die();
  }

  public function valid($post_id=null,$id_domain=null){
    
    $saida = InfluencersPosts::where('domain_id',$id_domain)
                              ->where('post_id',$post_id)
                              ->pluck('domain_id','post_id');
    if(count($saida) > 0){
        return true;
    } else {
        return false;
    }

}

  public function getIndex() {

    $Role = RoleUser::where('user_id', Auth::user()->id)->first();
    $idRole = $Role->role_id;

      $data = InfluencersPosts::leftJoin('domain as D','D.id_domain','domain_id')
                              ->leftJoin('domain_category as DC','DC.id_domain_category','D.id_domain_category')
                              ->selectRaw('id,post_id,title,thumb,description,created,link,domain_id,DC.id_domain_category,D.name,DC.name as categoria')
                              ->where('D.id_prebid_version',1)
                              ->orderBy('id','desc')
                              ->limit(50)
                              ->get();
                              

      $principal = $this->diretorioPrincipal;
      $primaryKey = $this->primaryKey;
      $rota = $this->nameView;
      $url = "{$this->diretorioPrincipal}.{$this->nameView}.index";
      // $idRole = $this->idRole;
      $titulo  = "Matérias";
      $categories = Helper::categoriesNames();
      $compact = compact('data', 'principal', 'rota', 'primaryKey','idRole','titulo','categories');
      return view($url, $compact); 
      
  }
  
  public function getCategory($name=null,$user=null,$type='normal') {

    if($user == null){
      $user = Auth::user()->id;
    }

    $Role = RoleUser::where('user_id', $user)->first();
    $idRole = $Role->role_id;

      $ids = Helper::categoriesIds($name);    
      $categories = Helper::categoriesNames();    

      $data = InfluencersPosts::whereIn('id',$ids['ids'])
                              ->leftJoin('domain as D','D.id_domain','domain_id')
                              ->leftJoin('domain_category as DC','DC.id_domain_category','D.id_domain_category')
                              ->selectRaw('id,post_id,title,thumb,description,created,link,domain_id,DC.id_domain_category,D.name,DC.name as categoria')
                              ->where('D.id_prebid_version',1)
                              ->orderBy('id','desc')
                              ->limit(50)
                              ->get();
                              

      $principal = $this->diretorioPrincipal;
      $primaryKey = $this->primaryKey;
      $rota = $this->nameView;
      $url = "{$this->diretorioPrincipal}.{$this->nameView}.index";
      // $idRole = $this->idRole;
      $titulo  = "Matérias - ".$ids['name'];
      $xname = $ids['name'];
      $compact = compact('data', 'principal', 'rota', 'primaryKey','idRole','titulo','categories','name','xname');
      
      if($type=='normal'){
        return view($url, $compact); 
      } elseif($type=='rss'){
        $news = [];
        $saida = "<rss version='2.0' xmlns:atom='http://www.w3.org/2005/Atom'>\n";
        $saida .= "<channel>\n";
        $saida .= "<title>$titulo</title>\n";
        foreach($data as $new){
          $news[] = [
            'title'=>$new->title,
            'thumb'=>$new->thumb,
            'link'=>$new->link.'?utm_source=joinads&utm_medium=cpm&utm_campaign='.$user.'&utm_term='.$new->post_id.'&utm_content='.$new->post_id.''
          ];

          $saida .= "<item>\n";
            $saida .= "<title>$new->title</title>\n";
            $saida .= "<description>".str_replace('&nbsp;','',$new->description)."</description>\n";
            $saida .= '<link>'.$new->link.'?utm_source=joinads&amp;utm_medium=cpm&amp;utm_campaign='.$user.'&amp;utm_term='.$new->post_id.'&amp;utm_content='.$new->post_id.'</link>'."\n";
            $saida .= '<guid>'.$new->link.'?utm_source=joinads&amp;utm_medium=cpm&amp;utm_campaign='.$user.'&amp;utm_term='.$new->post_id.'&amp;utm_content='.$new->post_id.'</guid>'."\n";
            $saida .= '<atom:link href="'.$new->link.'?utm_source=joinads&amp;utm_medium=cpm&amp;utm_campaign='.$user.'&amp;utm_term='.$new->post_id.'&amp;utm_content='.$new->post_id.'" rel="sel" type="application/rss+xml"/>'."\n";
          $saida .= "</item>\n";
          
        }

        $saida .= "</channel>\n";
        $saida .= "</rss>\n";
        
        header('Content-type: application/xml');
        echo $saida;
        die();
      }

  }
  public function getCategoryRss($name=null,$user=null,$type='rss') {

    $this->getCategory($name,$user,'rss');

  }


  public function getMaisVistas($periodo=null) {
    $categories = Helper::categoriesNames();    
    $Role = RoleUser::where('user_id', Auth::user()->id)->first();
    $idRole = $Role->role_id;

      $pre_data = InfluencersPosts::leftJoin('domain as D','D.id_domain','domain_id')
                              ->leftJoin('domain_category as DC','DC.id_domain_category','D.id_domain_category')
                              ->where('D.id_prebid_version',1)
                              ->orderBy('sessoes','desc')
                              ->limit(150);

      $titulo  = "Matérias mais vistas";
                                                         

      if($periodo == 'today'){
        $pre_data = $pre_data->selectRaw('in_posts.id,in_posts.post_id,in_posts.title,in_posts.thumb,in_posts.description,in_posts.created,in_posts.link,in_posts.domain_id,DC.id_domain_category,D.name,DC.name as categoria, (select sum(VS.session) as total from in_visits_realtime as VS where VS.post_id = in_posts.post_id and VS.date = "'.date('Y-m-d').'") as sessoes');
        $titulo  .= ' de hoje!';
      } elseif($periodo == 'week'){
        $pre_data = $pre_data->selectRaw('in_posts.id,in_posts.post_id,in_posts.title,in_posts.thumb,in_posts.description,in_posts.created,in_posts.link,in_posts.domain_id,DC.id_domain_category,D.name,DC.name as categoria, (select sum(VS.session) as total from in_visits_realtime as VS where VS.post_id = in_posts.post_id and VS.date between "'.date('Y-m-d', strtotime('-7 day',strtotime(date('Y-m-d')))).'" and "'.date('Y-m-d').'") as sessoes');
        $titulo  .= ' da semana!';
      } else {
        $pre_data = $pre_data->selectRaw('in_posts.id,in_posts.post_id,in_posts.title,in_posts.thumb,in_posts.description,in_posts.created,in_posts.link,in_posts.domain_id,DC.id_domain_category,D.name,DC.name as categoria, (select sum(VS.session) as total from in_visits_realtime as VS where VS.post_id = in_posts.post_id) as sessoes');
      }
      
      $data = $pre_data->get();
      
      $principal = $this->diretorioPrincipal;
      $primaryKey = $this->primaryKey;
      $rota = $this->nameView;
      $url = "{$this->diretorioPrincipal}.{$this->nameView}.index";
      // $idRole = $this->idRole;
      
      $compact = compact('data', 'principal', 'rota', 'primaryKey','idRole','titulo','categories');
      return view($url, $compact); 
  }



  public function getStory($id=null){
      
      $data = $this->model
                   ->where('id','=',$id)
                   ->first();
      
      $width       = 720;
      $height      = 1280;
      $center_x    = $width / 2;
      $center_y    = 150;
      $max_len     = 36;
      $font_size   = 35;
      $font_height = 20;
      $text        = $data->title;
      $color       = '#FFF';
      $foto        = $data->thumb;

      $img = Image::make($foto);
      $img->resize(720, null, function ($constraint) {
          $constraint->aspectRatio();
      });
      $img->resizeCanvas(720, 1280, 'center', false,'FFF');

      $lines = explode("\n", wordwrap($text, $max_len));
      $y     = $center_y - ((count($lines) - 1) * $font_height);

      foreach ($lines as $line) {
          $img->text($line, $center_x, $y, function($font) use ($font_size){
              $font->file('./font.otf');
              $font->size($font_size);
              $font->color('#000000');
              $font->align('center');
              $font->valign('top');
          });

          $y += $font_height * 2;
      }
      
      $img->insert('modelo-ok.png');

      $headers = [
        'Content-Type' => 'image/png',
        'Content-Disposition' => 'attachment; filename=story.png',
    ];
    return response()->stream(function() use ($img) {
      echo $img->save('story.png');
    }, 200, $headers);
    
    die();      

  } 

  public function getProgramaticRealTime(){
    header("Access-Control-Allow-Origin: *");

    $data = $this->request->all();
    
    if(session('t')){
      echo 1;
       //se existir então
      if(session('t')===1){ //verifica se já foi contabilizada
        //echo " já contabilizou ";
        die();// se já foi contabilizada então para
      }
    } else {
      if( (microtime(true)) - session('t')<4.5){ // verifica se time está certo
        echo "t";
        exit;// se não o time n tá certo para
      }
      session(['t' => 1]); // marca que já contabilizou essa seção;     
    }

    if($data['utm_campaign'] && $data['utm_content']){
      
      $ID_User=$data['utm_campaign'];
      $ID_Post=$data['utm_content'];
      $today=date('Y-m-d');
      $cpm = Settings::first()->cpm;

      $array = [
        'post_id'=>$ID_Post,
        'user_id'=>$ID_User,
        'session'=>1,
        'date'=>$today,
        'cpm'=>$cpm
      ];
          
      try{
        InfluencersVisitsReal::insert($array);
      } catch(Exception $e){
        $array = [
          'post_id'=>$ID_Post,
          'user_id'=>$ID_User,
          'date'=>$today,
        ];
        InfluencersVisitsReal::where($array)->update(['session'=>DB::raw('session+1')]);
      }
    }

  }

  public static function analytics($days = 0){
    // $cpm = Settings::first()->cpm;
    // $active = Analytics::getAnalyticsService();
    // $retorno = $active->data_realtime->get(
    //   'ga:232587360',
    //   'rt:activeUsers',
    //   [
    //     'dimensions'=>'ga:campaign, rt:keyword, ga:country'
    //   ]
            
    // );

    // $analyticsData = Analytics::performQuery(
    //   Period::days($days),
    //     'ga:sessions',
    //     [
    //         'metrics' => 'ga:sessions',
    //         'dimensions' => 'ga:campaign,ga:keyword,ga:country,ga:date'
    //     ]
    // );  
    
    // foreach($retorno as $data){
    //   if($data[2] == 'Brazil'){
    //     if($data[0] != '(not set)'){
    //       $subsession[] = [
    //         'date'=>date("Y-m-d"),
    //         'user_id'=>$data[0],
    //         'post_id'=>$data[1],
    //         'sessions'=>$data[3],
    //         'cpm'=>$cpm
    //       ];
    //     }
    //   }
    // }    

    // foreach ($analyticsData as $data) {
    //   if($data[2] == 'Brazil'){
    //     if(is_numeric($data[0])){

    //       $sessions[] = [
    //         'date'=>date("Y-m-d", strtotime($data[3])),
    //         'user_id'=>$data[0],
    //         'post_id'=>$data[1],
    //         'sessions'=>$data[4],
    //         'cpm'=>$cpm
    //       ];

          
    //     }
    //   }
    // }

    // if($sessions){
    //   foreach ($sessions as $key => $session) {
        
    //     foreach ($subsession as $xkey => $value) {
    //       if($session['date'] == $value['date'] && $session['user_id'] == $value['user_id'] && $session['post_id'] == $value['post_id']){

    //           $session['sessions'] = $session['sessions']+$value['sessions'];
    //           unset($subsession[$xkey]);

    //         } 
    //         $xsession[$key] = $session;
    //     }

    //   }
    // }

    // $session = array_merge($xsession,$subsession); 

    // $datas = [];
    // foreach ($session as $visita) {
    //   if(!in_array($visita['date'],$datas)){
    //     $datas[] = $visita['date'];
    //   }      
    // }

    // foreach ($session as $x => $value) {
    //   $info = InfluencersVisits::where('date',$value['date'])
    //                            ->where('user_id',$value['user_id'])
    //                            ->where('post_id',$value['post_id'])
    //                            ->first();
      
    //     if(isset($info)){
    //       if($value['sessions'] < $info->sessions){
    //         $session[$x]['sessions'] = $info->sessions;
    //       }
    //     }
      
    // }
    


    // InfluencersVisits::whereIn('date',$datas)->delete();   
    // InfluencersVisits::insert($session);  

  }

  

  public function getAnalytics($days = 0){

    // $this->analytics($days);
    
  }
  

  public function postFilter($page){
          

    $dadosForm = $this->request->all();
   

    if(isset($dadosForm['filter'])){
      session(['filter' => $dadosForm['filter']]);
      
    }

    
    session(['start_date' => $dadosForm['start_date']]);
    session(['end_date' => $dadosForm['end_date']]);

    

    return redirect("{$this->diretorioPrincipal}/influencers-posts/$page");

}

public function getMyInvoices($status=null){
    $principal = $this->diretorioPrincipal;
    $rota = $this->nameView;
    $primaryKey = $this->primaryKey;
    $data = InfluencersVisitsReal::where('user_id',Auth::user()->id)
                              ->leftJoin('in_posts as Posts','Posts.post_id','in_visits_realtime.post_id')
                              ->selectRaw('in_visits_realtime.date, Posts.title, in_visits_realtime.session as sessions, in_visits_realtime.cpm, in_visits_realtime.post_id, in_visits_realtime.user_id')
                              ->get();    

    $invoices = InfluencersInvoices::where('publisher_id',Auth::user()->id)->get();
    
    return view("{$this->diretorioPrincipal}.{$this->nameView}.my-invoices", compact('principal', 'rota', 'primaryKey', 'data', 'invoices','status'));
}

public function getAllInvoices(){

  $roles = User::join('role_user','role_user.user_id','users.id')
              ->where('role_user.user_id', Auth::user()->id)
              ->pluck('role_id');
          $role = 0;
          foreach ($roles as $xrole) {
          $role = $xrole;
          }
      
  if($role == 1 || $role == 10){

    $principal = $this->diretorioPrincipal;
    $rota = $this->nameView;
    $primaryKey = $this->primaryKey;
    $data = InfluencersVisitsReal::
                              leftJoin('in_posts as Posts','Posts.post_id','in_visits_realtime.post_id')
                              ->selectRaw('in_visits_realtime.date, Posts.title, in_visits_realtime.session as sessions, in_visits_realtime.cpm, in_visits_realtime.post_id, in_visits_realtime.user_id')
                              ->orderBy('date')
                              ->get();    
    
    $invoices = InfluencersInvoices::get();
    
    return view("{$this->diretorioPrincipal}.{$this->nameView}.all-invoices", compact('principal', 'rota', 'primaryKey', 'data', 'invoices','status'));
      } else {
        return redirect("/{$this->diretorioPrincipal}");
      }
}

public function getStatus($status=null,$id=null){
  $data = [
            'status'=>$status,
            'user_id'=>Auth::user()->id,
            'id_in_invoices'=>$id

  ];

  // 1 - Aguardando / 2 - Pago / 3 - Recusado / 4 - devolvido
  $invoices = InfluencersInvoices::where('id_in_invoices',$id);
  $dados = $invoices->first('publisher_id');
  $info = Helper::dados($dados->publisher_id);
  
  $invoices->update($data);

  $nome = explode(' ',$info->name);
  
  if($status == 2){
    $titulo = 'Olá '.$nome[0].', Pagamento enviado!';
    $informacao = 'Atenção enviado em breve estará em sua conta bancaria.';
  }
  
  if($status == 3){
    $titulo = 'Olá '.$nome[0].', Pagamento recusado!';
    $informacao = 'Atenção entre em contato com nossa area financeira.';
  }
  
  if($status == 4){
    $titulo = 'Olá '.$nome[0].', Pagamento devolvido!';
    $informacao = 'Atenção o banco efetuou o extorno de seu pagamento, confira seus dados bancarios e faça sua solicitação de pagamento novamente.';
  }

  $this->sendEmail('pagamentos', ['title'=>$titulo,'informacao'=>$informacao] ,$titulo,$info->email,'financeiro@joinads.me');
  echo json_encode($invoices);
  die();
}

public function sendEmail($template, $params, $subject, $email, $responsable = "suporte@joinads.me"){
  return Mail::send("emails.$template",$params, function ($m) use($subject, $email, $responsable){
    $m->from($responsable, "joinads.me");
    $m->to($email)->subject($subject);
  });
}

public function getRanked(){
    $principal = $this->diretorioPrincipal;
    $rota = $this->nameView;
    $primaryKey = $this->primaryKey;
    
    $startDate = date('Y-m').'-01';    
    $endDate = date('Y-m').'-31';
    
    $data = InfluencersVisitsReal::leftJoin('users','users.id','in_visits_realtime.user_id')
                              ->selectRaw('substring_index(users.name," ",1) as name, sum(in_visits_realtime.session) as sessions, in_visits_realtime.user_id')
                              ->whereBetween('date',[$startDate, $endDate])
                              ->groupBy('in_visits_realtime.user_id')
                              ->orderBy('sessions','DESC')
                              ->get();           

    return view("{$this->diretorioPrincipal}.{$this->nameView}.ranked", compact('principal', 'rota', 'primaryKey', 'data'));
}

public function getBalance(){
    $principal = $this->diretorioPrincipal;
    $rota = $this->nameView;
    $primaryKey = $this->primaryKey;

    $startDate = date('Y-m-d', strtotime(session('start_date')));
    $endDate = date('Y-m-d', strtotime(session('end_date')));

    $data = InfluencersVisitsReal::
                              leftJoin('in_posts as Posts','Posts.post_id','in_visits_realtime.post_id')
                              ->leftJoin('users','users.id','in_visits_realtime.user_id')
                              ->selectRaw('users.id, users.name, in_visits_realtime.date, Posts.title, in_visits_realtime.session as sessions, in_visits_realtime.cpm, in_visits_realtime.post_id, in_visits_realtime.user_id')
                              ->whereBetween('date',[$startDate, $endDate])
                              ->where('users.status_admanager',1)
                              ->orderBy('date')
                              ->get();         
    
    
    $data2 = InfluencersVisitsReal::selectRaw('sum(session) as total, cpm')
                              ->leftJoin('users','users.id','in_visits_realtime.user_id')   
                              ->whereBetween('date',[$startDate, $endDate])
                              ->where('users.status_admanager',1)
                              ->groupBy('cpm')                           
                              ->get();
    $somaPeriodo = 0;   
    foreach($data2 as $totais){
      $somaPeriodo += ($totais->total/1000)*$totais->cpm;
    }
    
    $array = [];
    
    foreach($data as $d){
      $array[$d->user_id][] = [
        
        'cpm'=>$d->cpm,
        'session'=>$d->sessions
      ];

      if(@$array[$d->user_id]['total'] > 0){
        $array[$d->user_id]['total'] += (($d->sessions/1000)*$d->cpm);
      } else {
        $array[$d->user_id]['total'] = (($d->sessions/1000)*$d->cpm);
      }
      
      if(@$array[$d->user_id]['impressoes'] > 0){
        $array[$d->user_id]['impressoes'] += (($d->sessions));
      } else {
        $array[$d->user_id]['impressoes'] = (($d->sessions));
      }

      $array[$d->user_id]['id']   = $d->user_id;
      $array[$d->user_id]['name'] = $d->name;

    }   

    $data = $array;

    $invoicesAll = InfluencersInvoices::where('status',2)
                                      ->selectRaw('sum(valor) as total')
                                      ->whereBetween('updated_at',[$startDate, $endDate])
                                      ->pluck('total');

    $totalFinal = $invoicesAll[0];
    
    return view("{$this->diretorioPrincipal}.{$this->nameView}.balance", compact('principal', 'rota', 'primaryKey', 'data', 'invoices','status','totalFinal','somaPeriodo'));
}

public function postMyInvoices(){
  $dadosForm = $this->request->all();
  $dadosForm['user_id'] = Auth::user()->id;

  $data = InfluencersVisitsReal::where('user_id',Auth::user()->id)
                              ->leftJoin('in_posts as Posts','Posts.post_id','in_visits_realtime.post_id')
                              ->selectRaw('in_visits_realtime.date, Posts.title, in_visits_realtime.session as sessions, in_visits_realtime.cpm, in_visits_realtime.post_id, in_visits_realtime.user_id')
                              ->get();  
  $total = 0;

  foreach($data as $dd){
      $total += (($dd->sessions/1000)*$dd->cpm);
  }

  $subtotal = 0;
  $invoices = InfluencersInvoices::where('publisher_id',Auth::user()->id)->get();

  foreach($invoices as $in){
   
    // if($in['status']!=3 || $in['status']!=4){
     if($in['status'] == 2 || $in['status'] == 1){
      $subtotal += $in->valor;
    }

  }

  

  $total = $total-$subtotal;
  // dd($total);
  $dadosForm['ganhos'] = $total;
  if($dadosForm['value'] <= $dadosForm['ganhos']){
    InfluencersInvoices::insert([
                                  'valor'=>$dadosForm['value'],
                                  'publisher_id'=>$dadosForm['user_id'],
                                  'created_at'=>date('Y-m-d H:i:s')
                                ]);
    $status = 1;
  } else {
    $status = 2;
  }

  return redirect("{$this->diretorioPrincipal}/influencers-posts/my-invoices/".$status);
  
}

  public function getGanhos(){
    $principal = $this->diretorioPrincipal;
    $rota = $this->nameView;
    $primaryKey = $this->primaryKey;
        
    $startDate = date('Y-m-d', strtotime(session('start_date')));
    $endDate = date('Y-m-d', strtotime(session('end_date')));

    $data = InfluencersVisitsReal::where('user_id',Auth::user()->id)
                              ->whereBetween('in_visits_realtime.date',[$startDate, $endDate])
                              ->leftJoin('in_posts as Posts','Posts.post_id','in_visits_realtime.post_id')
                              ->selectRaw('in_visits_realtime.date, Posts.title, in_visits_realtime.session as sessions, in_visits_realtime.cpm, in_visits_realtime.post_id, in_visits_realtime.user_id')
                              ->orderBy('in_visits_realtime.date')
                              ->get();                              
    
    return view("{$this->diretorioPrincipal}.{$this->nameView}.ganhos", compact('principal', 'rota', 'primaryKey', 'data'));
  }

  public function getCreate() {
    if (Defender::hasPermission("{$this->nameView}")) {
      $principal = $this->diretorioPrincipal;
      $currencies = FinCurrency::get();

      $rota = $this->nameView;
      $primaryKey = $this->primaryKey;
      return view("{$this->diretorioPrincipal}.{$this->nameView}.create-edit", compact('principal', 'rota', 'primaryKey', 'currencies'));
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
  
      $currencies = FinCurrency::get();
      $currenciesSelected = FinCurrency::find($data->id_fin_currency);
  
      return view("{$this->diretorioPrincipal}.{$this->nameView}.create-edit", compact('data','principal','rota','primaryKey','currencies','currenciesSelected'));
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }


}
