<?php

namespace App\Http\Controllers\Painel;
use App;
use App\Http\Controllers\Painel\StandardController;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory;
use App\Models\Painel\FinMovimentation;
use App\Models\Painel\FinBank;
use App\Models\Painel\FinCategory;
use App\Models\Painel\FinForm;
use App\Models\Painel\FinCurrency;
use App\Models\Painel\FinMovimentationXAdmanagerReport;
use App\Models\Painel\User;
use App\Models\Painel\AdmanagerReport;
use App\Models\Painel\FinMassPayment;
use App\Models\Painel\FinHusky;
use App\Models\Painel\Domain;
use App\Models\Painel\FinInvalid;
use App\Models\Painel\FinInvoices;
use App\Models\Painel\Settings;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Defender;
use File;
use Mail;
use Helper;
use Illuminate\Support\Facades\DB;

class FinMovimentationController extends StandardController {

  protected $nameView = 'fin-movimentation';
  protected $diretorioPrincipal = 'painel';
  protected $primaryKey = 'id_fin_movimentation';

  public function __construct(Request $request, FinMovimentation $model, Factory $validator, FinMovimentationXAdmanagerReport $FMXAR, AdmanagerReport $AdmanagerReport, FinMassPayment $mp, FinHusky $husky) {
    $this->request = $request;
    $this->model = $model;
    $this->validator = $validator;
    $this->FMXAR = $FMXAR;
    $this->AR = $AdmanagerReport;
    $this->MP = $mp;
    $this->Husky = $husky;
    $this->totalItensPorPagina = 500;
  }

  public function getIndex($id_bank=null) {
    if (Defender::hasPermission("{$this->nameView}")) {


       if(isset($_GET['pagos'])){
        $status[] = 1;
        $status[] = 2;
      } else {
        $status[] = 1;;
      }

      if(isset($_GET['from'])){
        $data = $this->model
                      ->leftJoin('users as U','U.id','=','id_client')
                      ->orderBy('id_fin_movimentation','desc')
                      ->whereBetween('date_expiry', [Helper::formatData($_GET['from'],3), Helper::formatData($_GET['to'],3)], 'and')
                      ->whereIn('fin_movimentation.status',$status)
                      ->where('id_fin_bank',$id_bank)
                      ->get();
        // dd($data);
      } else {
        $data = $this->model
                      ->leftJoin('users as U','U.id','=','id_client')
                      ->orderBy('id_fin_movimentation','desc')
                      ->whereIn('fin_movimentation.status',$status)
                      ->where('id_fin_bank',$id_bank)
                      ->get();
      }
      

      $banks        = FinBank::pluck('name','id_fin_bank');
      $categories   = FinCategory::leftJoin('fin_category as F', 'F.id_fin_category','=','fin_category.fin_category_id')
                                  ->selectRaw('fin_category.*, F.name nameCategory')
                                  ->pluck('name','id_fin_category');
      $maes         = FinCategory::where('fin_category_id')->pluck('name','id_fin_category');
      $forms        = FinForm::pluck('name','id_fin_form');
      $clients      = User::pluck('name','id');
      $currencies   = FinCurrency::pluck('abbreviation','id_fin_currency');
      $mp = $this->MP->where('status','1')->limit(1)->orderBY('id_fin_mass_payment','desc')->first();

      $principal = $this->diretorioPrincipal;
      $primaryKey = $this->primaryKey;
      $rota = $this->nameView;
      return view("{$this->diretorioPrincipal}.{$this->nameView}.index", compact('id_bank','data','banks', 'categories', 'forms', 'clients','principal', 'rota', 'currencies','primaryKey','mp'));
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
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
  
  public function getMyIndex() {
    // if (Defender::hasPermission("{$this->nameView}")) {
      $user = Auth::user();


      $data = $this->model
      ->leftJoin('users as U','U.id','=','id_client')
      ->orderBy('id_fin_movimentation','desc')
      ->where('id_client',$user->id)
      ->paginate(10000);  
      
      if(!empty($data)){

        foreach($data as $key => $info){
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
          $data = $informations;
        }

        // dd($data);

        $banks        = FinBank::pluck('name','id_fin_bank');
        $categories   = FinCategory::leftJoin('fin_category as F', 'F.id_fin_category','=','fin_category.fin_category_id')
                                    ->selectRaw('fin_category.*, F.name nameCategory')
                                    ->pluck('name','id_fin_category');
        $maes         = FinCategory::where('fin_category_id')->pluck('name','id_fin_category');
        $forms        = FinForm::pluck('name','id_fin_form');
        $clients      = User::pluck('name','id');
        $currencies   = FinCurrency::pluck('abbreviation','id_fin_currency');
        $mp = $this->MP->where('status','1')->limit(1)->orderBY('id_fin_mass_payment','desc')->first();

        $principal = $this->diretorioPrincipal;
        $primaryKey = $this->primaryKey;
        $rota = $this->nameView;
        return view("{$this->diretorioPrincipal}.{$this->nameView}.my-index", compact('data','banks', 'categories', 'forms', 'clients','principal', 'rota', 'currencies','primaryKey','mp'));
      // } else {
      //   return redirect("/{$this->diretorioPrincipal}");
      // }
      }
  }

  public function getCreate($usertype=null) {
    if (Defender::hasPermission("{$this->nameView}")) {
      $principal = $this->diretorioPrincipal;

      // Relacionamentos
      $banks        = FinBank::pluck('name','id_fin_bank');
      $categories   = FinCategory::leftJoin('fin_category as F', 'F.id_fin_category','=','fin_category.fin_category_id')
                                  ->selectRaw('fin_category.*, F.name nameCategory')
                                  ->pluck('name','id_fin_category');
      $maes         = FinCategory::where('fin_category_id')->pluck('name','id_fin_category');
      $forms        = FinForm::pluck('name','id_fin_form');
      $clients      = User::pluck('name','id');
      $currencies   = FinCurrency::pluck('name','id_fin_currency');


      $rota = $this->nameView;
      $primaryKey = $this->primaryKey;

      $title = "Movimentações";

      if($usertype == 1){
        $title .= " | Despesas";
        $title_client = "Fornecedor";
        $status       = array(1=>'Em Aberto',2=>'Paga');
      } elseif($usertype == 2){
        $title .= " | Receitas";
        $title_client = "Parceiro";
        $status       = array(1=>'Em Aberto',2=>'Recebida');
      } elseif($usertype==4) {
        $title .= " | Impostos";
        $title_client = "Tipo de Imposto";
        $status       = array(1=>'Em Aberto',2=>'Pago');
      } else {
        $title .= " | Pró-labore";
        $title_client = "Colaborador";
        $status       = array(1=>'Em Aberto',2=>'Pago');
      }

      $anterior = $_SERVER['HTTP_REFERER'];

      return view("{$this->diretorioPrincipal}.{$this->nameView}.create-edit", compact('anterior','maes','usertype','title','title_client','principal', 'rota', 'primaryKey','banks','categories','forms','clients','currencies','status'));
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function getShow($id) {
    if (Defender::hasPermission("{$this->nameView}")) {
      $data = $this->model->findOrFail($id);
      $usertype = $data->type;
      $banks        = FinBank::pluck('name','id_fin_bank');
      $categories   = FinCategory::leftJoin('fin_category as F', 'F.id_fin_category','=','fin_category.fin_category_id')
                                  ->selectRaw('fin_category.*, F.name nameCategory')
                                  ->pluck('name','id_fin_category');
      $maes         = FinCategory::where('fin_category_id')->pluck('name','id_fin_category');
      $forms        = FinForm::pluck('name','id_fin_form');
      $clients      = User::pluck('name','id');
      $currencies   = FinCurrency::pluck('name','id_fin_currency');
      $title = "Movimentações ";
      
      if($usertype == 1){
        $title .= " | Despesas";
        $title_client = "Fornecedor";
        $status       = array(1=>'Em Aberto',2=>'Paga');
      } elseif($usertype == 2){
        $title .= " | Receitas";
        $title_client = "Parceiro";
        $status       = array(1=>'Em Aberto',2=>'Recebida');
      } elseif($usertype==4) {
        $title .= " | Impostos";
        $title_client = "Tipo de Imposto";
        $status       = array(1=>'Em Aberto',2=>'Pago');
      } else {
        $title .= " | Pró-labore";
        $title_client = "Colaborador";
        $status       = array(1=>'Em Aberto',2=>'Pago');
      }

      $data->date_expiry = $this->formatar_data($data->date_expiry,2);
      $data->date_payment = $this->formatar_data($data->date_payment,2);
      $data->value = number_format($data->value, 2, ',', '.');
      $data->tax = number_format($data->tax, 2, ',', '.');

      $principal = $this->diretorioPrincipal;
      $rota = $this->nameView;
      $primaryKey = $this->primaryKey;


      $anterior = $_SERVER['HTTP_REFERER'];

      return view("{$this->diretorioPrincipal}.{$this->nameView}.create-edit", compact('anterior','data','maes','usertype','title','title_client','principal', 'rota', 'primaryKey','banks','categories','forms','clients','currencies','status'));
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }
  
  public function getComprovante($id) {
    
      $data = $this->model
                   ->leftJoin('users as Client','Client.id','id_client')
                   ->leftJoin('fin_currency','fin_currency.id_fin_currency','fin_movimentation.id_fin_currency')
                   ->findOrFail($id);
      $usertype = $data->type;
      $banks        = FinBank::pluck('name','id_fin_bank');
      $categories   = FinCategory::leftJoin('fin_category as F', 'F.id_fin_category','=','fin_category.fin_category_id')
                                  ->selectRaw('fin_category.*, F.name nameCategory')
                                  ->pluck('name','id_fin_category');
      $maes         = FinCategory::where('fin_category_id')->pluck('name','id_fin_category');
      $forms        = FinForm::pluck('name','id_fin_form');
      $clients      = User::pluck('name','id');
      $currencies   = FinCurrency::pluck('name','id_fin_currency');
      $title = "Comprovante";
      
    
      $data->date_expiry = $this->formatar_data($data->date_expiry,2);
      $data->date_payment = $this->formatar_data($data->date_payment,2);
      $data->value = number_format($data->value, 2, ',', '.');
      $data->tax = number_format($data->tax, 2, ',', '.');

      $principal = $this->diretorioPrincipal;
      $rota = $this->nameView;
      $primaryKey = $this->primaryKey;
      //upload
      return view("{$this->diretorioPrincipal}.{$this->nameView}.comprovante", compact('data','maes','usertype','title','principal', 'rota', 'primaryKey','banks','categories','forms','clients','currencies'));
    
  }

  public function getCpdf($id){
    $data = $this->getComprovante($id);
    // echo $data;
    // die();
    //
    $pdf = App::make('dompdf.wrapper');
    $pdf->loadHTML($data);
    // $pdf->setOptions(['isHtml5ParserEnabled'=>true]);
    return $pdf->stream();
    die();
  }

  public function postStore() {
    if (Defender::hasPermission("{$this->nameView}")) {
      $user = Auth::user();
      $dadosForm = $this->request->all();
      unset($dadosForm['arquivo_envio']);
      $anterior = $dadosForm['anterior'];
      unset($dadosForm['anterior']);
      $dadosForm['date_expiry'] = $this->formatar_data($dadosForm['date_expiry'],1);
      $dadosForm['date_payment'] = $this->formatar_data($dadosForm['date_payment'],1);
      $dadosForm['value'] = $this->formatar_moeda($dadosForm['value']);
      $dadosForm['tax'] = $this->formatar_moeda($dadosForm['tax']);
      $dadosForm['id_client'] = $dadosForm['client_id'];
      $dadosForm['id_user'] = $user->id;

      $validator = $this->validator->make($dadosForm, $this->model->rules);
      if ($validator->fails()) {
        return redirect("/{$this->diretorioPrincipal}/{$this->nameView}/create")->withErrors($validator)->withInput();
      }
      $this->model->create($dadosForm);
      // return redirect("/{$this->diretorioPrincipal}/{$this->nameView}");
      return redirect($anterior);
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }
  public function postUpdate($id) {
    if (Defender::hasPermission("{$this->nameView}")) {
      $user = Auth::user();
      $dadosForm = $this->request->all();
      unset($dadosForm['arquivo_envio']);
      $voltar = $dadosForm['anterior'];
      unset($dadosForm['anterior']);
      $dadosForm['date_expiry'] = $this->formatar_data($dadosForm['date_expiry'],1);
      $dadosForm['date_payment'] = $this->formatar_data($dadosForm['date_payment'],1);
      $dadosForm['value'] = $this->formatar_moeda($dadosForm['value']);
      $dadosForm['tax'] = $this->formatar_moeda($dadosForm['tax']);
      $dadosForm['id_client'] = $dadosForm['client_id'];
      $dadosForm['id_user'] = $user->id;
      $validator = $this->validator->make($dadosForm, $this->model->rules);
      if ($validator->fails()) {
        return redirect("/{$this->diretorioPrincipal}/{$this->nameView}/show/$id")->withErrors($validator)->withInput();
      }
      $this->model->findOrFail($id)->update($dadosForm);
      // return redirect("/{$this->diretorioPrincipal}/{$this->nameView}");
      return redirect($voltar);
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function cat(){
    $data = $_POST;
    unset($data['token']);
    if($data['fin_category_id'] == ''){
      unset($data['fin_category_id']);
    }
    $save = FinCategory::create($data);
    echo json_encode($save);
  }

  public function user(){
    $data = $_POST;
    unset($data['token']);
    if($data['email'] == ''){
      $data['email'] = $data['CPF_CNPJ'].'@joinads.me';
    }
    $data['password'] = "123mudar";
    $save = User::create($data);
    echo json_encode($save);
  }

  public function enviar($id=null){

    if($_FILES){
        $files = \Request::file('file');
        $file = $this->uploadFile2($files, $_FILES['file']['name'], 'painel', '/recibos/');
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

public function formatar_data($data,$type){
  if($data != null){
      switch($type){
          case 1;
              $date = explode('/',$data);
              $newdate = $date[2].'-'.$date[1].'-'.$date[0];
          break;
          case 2;
              $date = explode('-',$data);
              $newdate = $date[2].'/'.$date[1].'/'.$date[0];
          break;
          case 3;
            $date = explode('-',$data);
            $newdate = $date[2].'-'.$date[1].'-'.$date[0];
          break;
      }
      return $newdate;
  }
}

public function formatar_moeda($data){
  if($data != null){
      $date = str_replace('.','',$data);
      $newdate = str_replace(',','.',$date);
      return $newdate;
  }
}


public function publisher_x_movimentation(){
  $dadosForm = $_POST;
  unset($dadosForm['_token']);
  // unset($dadosForm['ids']);

  $dadosForm['date_expiry'] = date('Y-m-d');
  // if(!$dadosForm['currency']){
    $dadosForm['currency'] = 1;
  // }  
  $dadosForm['id_user'] = Auth::user()->id;
  $dadosForm['id_fin_bank'] = 2;
  $dadosForm['id_fin_category'] = 1;
  $dadosForm['id_fin_form'] = 3;
  $dadosForm['id_fin_currency'] = 1;
  $dadosForm['status'] = 1;
  $dadosForm['type'] = 1;

  // $user = User::selectRaw('agency,agency_digit,account,digit,bank,email,name')->find($dadosForm['id_client']);
  // if(empty($user->account)){
  //   $conteudo = "{$user->name}, você deve cadastrar sua conta bancaria com urgência!";

  //   Mail::send('emails.notification',['observation'=>$conteudo], function ($m) use($user){
  //     $m->from("contato@joinads.me", "joinads.me");
  //     $m->to($user->email)->subject($user->name.', precisamos de sua atenção!');
  //   });
  // }
  
  $save = $this->model->create($dadosForm);

  // $ids = explode(',',$_POST['ids']);
  

  $start   = Helper::formatData($_POST['start'],3);
  $final   = Helper::formatData($_POST['final'],3);
  $id_user = $_POST['id_client'];

  $data = $this->AR->leftJoin('domain as D','D.name','site')
                   ->where('D.id_user',$id_user)
                   ->whereBetween('date',[$start,$final]);                   
                   
  $data->update(array('status_payment'=>1));

  $inter = [];
  foreach($data->get() as $id){
    $inter[] = array(
      'id_fin_movimentation'=>$save->id_fin_movimentation,
      'id_admanager_report'=>$id->id_admanager_report
    ); 
  }

  $integration = $this->FMXAR->insert($inter);

  if($integration){
    return json_encode(array('status'=>200,'msg'=>'Cadastrado com sucesso'));
  } else {
    return json_encode(array('status'=>100,'msg'=>'Problemas ao cadastrar'));
  }

}

public function add_mp(){
  $data = $_POST;
  $array = array(
    'masspay_id'=>$data['id_husky'],
    'token'=>$data['token'],
    'final_value'=>$data['value'],
    'tracking_code'=>$data['id_fin_movimentation']
  );

  $retorno = $this->Husky->husky_mp_add($array,$data['currency']);
  // dd($retorno);
  if($retorno['meta']['code'] == 200){
    $this->model->findOrFail($data['id_fin_movimentation'])->update(array('status'=>2));
    return json_encode(array('msg'=>'Pagamento Efetuado com sucesso'));
  } else {
    return json_encode(array('msg'=>'Problemas ao efetuar o pagamento'));
  }

}

public function getpublisherJson(){
  $data=$_GET;
  
    $principal = $this->diretorioPrincipal;
    $primaryKey = $this->primaryKey;
    $rota = $this->nameView;

    if($data){
          $where = "TR.date <= '".$this->formatar_data($data['to'],3)."'";
          $pre = "PP.date <= '".$this->formatar_data($data['to'],3)."'";

          $where_back = "TR.date < '".$this->formatar_data($data['from'],3)."'";
          $pre_back = "PP.date < '".$this->formatar_data($data['from'],3)."'";
      

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
            MONTH(TR.date) as month,
            YEAR(TR.date) as year,
            PP.final_value,
            (SELECT SUM(valor) FROM fin_invoices WHERE publisher_id = P.id_user AND status = 2) as antecipado,
            C.CPF_CNPJ,
            C.cep,
            C.email,
            C.whatsapp,
            C.bank_type,
            C.agency,
            C.agency_digit,
            C.account,
            C.digit,
            C.bank
        from
            admanager_report TR
        LEFT JOIN
            domain as P ON P.name = site
        LEFT JOIN
            users as C ON P.id_user = C.id
        LEFT JOIN
            fin_pre_payment as PP ON PP.id_client = P.id_user AND $pre
        where
            $where
        AND
            status_payment = 0
        group by TR.site,year,month,P.id_user,C.name,C.husky_token,PP.final_value
        ORDER BY total desc
        LIMIT 18446744073709551610
    ";

    $informations =  DB::select($sql);
    
    $array = array();

    foreach($informations as $info){
      

      
      $invalid = FinInvalid::selectRaw('SUM(value) as total')
                          ->where('id_client',$info->id_user)
                          ->where('month',$info->month)
                          ->where('year',$info->year)
                          ->groupBy('id_client')
                          ->first();


      if(isset($invalid)){
        $valinvalid = $invalid->total;
      } else {
        $valinvalid = 0;
      }
      
      

      if($info->id_user){

        $array[$info->id_user]['id_user'] = $info->id_user;
        $array[$info->id_user]['company'] = $info->company;
        $array[$info->id_user]['name'] = $info->name;
        $array[$info->id_user]['month'] = $info->month;
        $array[$info->id_user]['year'] = $info->year;
        $array[$info->id_user]['invalid'] = $valinvalid;
        $array[$info->id_user]['antecipados'] = $info->antecipado;

        $array[$info->id_user]['CPF_CNPJ'] = $info->CPF_CNPJ;
        $array[$info->id_user]['cep'] = $info->cep;
        $array[$info->id_user]['email'] = $info->email;
        $array[$info->id_user]['whatsapp'] = $info->whatsapp;
        if($info->bank_type == 1){
          $array[$info->id_user]['bank_type'] = "Checking Account";
        } else {
          $array[$info->id_user]['bank_type'] = "Savings Account";
        }
        
        $array[$info->id_user]['agency'] = $info->agency;
        $array[$info->id_user]['agency_digit'] = $info->agency_digit;
        $array[$info->id_user]['account'] = $info->account;
        $array[$info->id_user]['digit'] = $info->digit;
        $array[$info->id_user]['bank'] = Helper::bankCode($info->bank);
         
        // if(isset($array[$info->id_user]['total'])){
        //   $array[$info->id_user]['total'] += $info->total;
        // } else {
        //   $array[$info->id_user]['total'] = $info->total;
        // }
        
       


          if(empty($array[$info->id_user]['total'])){
            $array[$info->id_user]['total'] = 0;
            $array[$info->id_user]['total'] += $info->total;
          } else {
            $array[$info->id_user]['total'] += $info->total;
          }

      }
    }


   

    // if(!empty($a['total']) && !empty($b['total'])){
      usort($array, function($a,$b){
        return $a['total'] < $b['total'];
      });
    // }


    foreach ($array as $key => $value) {
      $infos[$key] = $value;
      if(!$value['antecipados']){
        $value['antecipados'] = 0;
      }
      $infos[$key]['total'] = $value['total']-($value['antecipados']+$value['invalid']);
    }

    usort($infos, function($a,$b){
      return $a['total'] < $b['total'];
    });

  
    // echo json_encode($infos);
    // die();
    }
    $fileName = 'invoices.csv';
    $headers = array(
      "Content-type"        => "text/csv",
      "Content-Disposition" => "attachment; filename=$fileName",
      "Pragma"              => "no-cache",
      "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
      "Expires"             => "0"
  );
  $columns = array(
                  'Payer Name', 
                  'UUID', 
                  'Name',
                  'Shareholder UUID', 
                  'zipcode', 
                  'Value', 
                  'Email', 
                  'Phone Number',
                  'Bank Name', 
                  'bank branch id',
                  'bank branch digit',
                  'account number',
                  'account digit',
                  'account type',
                  'Tracking Code',
                  'Payment Reason'
                );
  
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($infos as $info) {
           
                  if($info['total'] > 5){
                    $row['Payer Name'] = "GOOGLE INC"; 
                    $row['UUID'] = $info['CPF_CNPJ']; 
                    $row['Name'] = $info['name']; 
                    $row['Shareholder UUID'] = ''; 
                    $row['zipcode'] = $info['cep']; 
                    $row['Value'] = str_replace(",","",number_format($info['total'],2)); 
                    $row['Email'] = $info['email']; 
                    $row['Phone Number'] = $info['whatsapp'];
                    $row['Bank Name'] = $info['bank']; 
                    $row['bank branch id'] = $info['agency'];
                    $row['bank branch digit'] = $info['agency_digit'];
                    $row['account number'] = $info['account'];
                    $row['account digit'] = $info['digit'];
                    $row['account type'] = $info['bank_type'];
                    $row['Tracking Code'] = "";
                    $row['Payment Reason'] = "PUBLICITY AND MARKETING SERVICES";                    
                    fputcsv($file, $row);
                  }
            }

            fclose($file);
  
  
            // echo $file;

}

public function getAfiliados(){
  $data=$_GET;
  if (Defender::hasPermission("fin-movimentation/publisher")) {
    $principal = $this->diretorioPrincipal;
    $primaryKey = $this->primaryKey;
    $rota = $this->nameView;

    if($data){
          $where = "TR.date BETWEEN '".$this->formatar_data($data['from'],3)."' AND '".$this->formatar_data($data['to'],3)."'";
          $pre   = "PP.date BETWEEN '".$this->formatar_data($data['from'],3)."' AND '".$this->formatar_data($data['to'],3)."'";
          

    $sql = "
        select
            P.id_user,
            C.company,
            C.name,
            TR.site,
            C.husky_token,
            sum(TR.impressions) as impressoes,
            sum(TR.clicks) as cliques,
            sum(TR.earnings_client) as total ,
            sum(TR.earnings) as total_network ,
            max(TR.date) as date,
            MONTH(TR.date) as month,
            YEAR(TR.date) as year,
            PP.final_value,
            C.afiliados,
            (SELECT SUM(valor) FROM fin_invoices WHERE publisher_id = P.id_user AND status = 2) as antecipado
        from
            admanager_report TR
        LEFT JOIN
            domain as P ON P.name = site
        LEFT JOIN
            users as C ON P.id_user = C.id
        LEFT JOIN
            fin_pre_payment as PP ON PP.id_client = P.id_user AND $pre
        where
            $where        
        group by TR.site,year,month,P.id_user,C.name,C.husky_token,PP.final_value
        ORDER BY total desc
        LIMIT 18446744073709551610
    ";
   
    $informations =  DB::select($sql);


    $array = array();    

    foreach($informations as $Key => $info){
      

      
      $invalid = FinInvalid::selectRaw('SUM(value) as total')
                          ->where('id_client',$info->id_user)
                          ->where('month',$info->month)
                          ->where('year',$info->year)
                          ->groupBy('id_client')
                          ->first();


      if(isset($invalid)){
        $valinvalid = $invalid->total;
      } else {
        $valinvalid = 0;
      }
      
      

      if($info->id_user){

        $array[$Key]['id_user'] = $info->id_user;
        $array[$Key]['company'] = $info->company;
        $array[$Key]['name'] = $info->name;
        $array[$Key]['month'] = $info->month;
        $array[$Key]['year'] = $info->year;
        $array[$Key]['invalid'] = $valinvalid;
        $array[$Key]['antecipados'] = $info->antecipado;
        $array[$Key]['husky_token'] = $info->husky_token;
        $array[$Key]['afiliados'] = $info->afiliados;
        $array[$Key]['afiliados_valor'] = ($info->total*2/100);

      
        $array[$Key]['id_domain']            =  Domain::where('name',$info->site)->first()->id_domain_status;
        $array[$Key]['domain_status']        =  Domain::where('name',$info->site)->first()->id_domain;
        $array[$Key]['domain']               =  $info->site;
        $array[$Key]['total']                =  $info->total;
        $array[$Key]['total_network']        =  $info->total_network;
    
      }
    }



    if(!empty($a['total']) && !empty($b['total'])){
      usort($array, function($a,$b){
        return $a['total'] < $b['total'];
      });
    }
    
    $informations = $array;

    $array2 = [];

    foreach($informations as $info){
      
      if(isset($info['afiliados'])){
        
        if(!isset($array2[$info['afiliados']]['valor'])){
          $total = $info['afiliados_valor'];
        } else {
          $total = $array2[$info['afiliados']]['valor'] + $info['afiliados_valor'];
        }
        
        
        $array2[$info['afiliados']] = [
          'afiliado'=>$info['afiliados'],
          'afiliado_nome'=>Helper::nome($info['afiliados']),
          'valor'=>$total
        ];

      }

    }

    


    usort($array2, function($a,$b){
      return $a['valor'] < $b['valor'];
    });


    $informations = $array2;
    
   
    $aviso = null;
    
    } else {
      $aviso = "Selecione uma data";
      $informations = array();
    }


      return view("{$this->diretorioPrincipal}.{$this->nameView}.afiliados", compact('aviso','informations','principal', 'rota'));
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
}

public function getResultado(){
  $data=$_GET;
  if (Defender::hasPermission("fin-movimentation/publisher")) {
    $principal = $this->diretorioPrincipal;
    $primaryKey = $this->primaryKey;
    $rota = $this->nameView;

    if($data){
          $where = "TR.date BETWEEN '".$this->formatar_data($data['from'],3)."' AND '".$this->formatar_data($data['to'],3)."'";
          $pre   = "PP.date BETWEEN '".$this->formatar_data($data['from'],3)."' AND '".$this->formatar_data($data['to'],3)."'";
          

    $sql = "
        select
            P.id_user,
            C.company,
            C.name,
            TR.site,
            C.husky_token,
            sum(TR.impressions) as impressoes,
            sum(TR.clicks) as cliques,
            sum(TR.earnings_client) as total ,
            sum(TR.earnings) as total_network ,
            max(TR.date) as date,
            MONTH(TR.date) as month,
            YEAR(TR.date) as year,
            PP.final_value,
            (SELECT SUM(valor) FROM fin_invoices WHERE publisher_id = P.id_user AND status = 2) as antecipado
        from
            admanager_report TR
        LEFT JOIN
            domain as P ON P.name = site
        LEFT JOIN
            users as C ON P.id_user = C.id
        LEFT JOIN
            fin_pre_payment as PP ON PP.id_client = P.id_user AND $pre
        where
            $where        
        group by TR.site,year,month,P.id_user,C.name,C.husky_token,PP.final_value
        ORDER BY total desc
        LIMIT 18446744073709551610
    ";
   
    $informations =  DB::select($sql);


    $array = array();


    foreach($informations as $Key => $info){
      

      
      $invalid = FinInvalid::selectRaw('SUM(value) as total')
                          ->where('id_client',$info->id_user)
                          ->where('month',$info->month)
                          ->where('year',$info->year)
                          ->groupBy('id_client')
                          ->first();


      if(isset($invalid)){
        $valinvalid = $invalid->total;
      } else {
        $valinvalid = 0;
      }
      
      

      if($info->id_user){

        $array[$Key]['id_user'] = $info->id_user;
        $array[$Key]['company'] = $info->company;
        $array[$Key]['name'] = $info->name;
        $array[$Key]['month'] = $info->month;
        $array[$Key]['year'] = $info->year;
        $array[$Key]['invalid'] = $valinvalid;
        $array[$Key]['antecipados'] = $info->antecipado;
        $array[$Key]['husky_token'] = $info->husky_token;
      
        $array[$Key]['id_domain']            =  Domain::where('name',$info->site)->first()->id_domain_status;
        $array[$Key]['domain_status']        =  Domain::where('name',$info->site)->first()->id_domain;
        $array[$Key]['domain']               =  $info->site;
        $array[$Key]['total']                =  $info->total;
        $array[$Key]['total_network']        =  $info->total_network;
    
      }
    }

    


    if(!empty($a['total']) && !empty($b['total'])){
      usort($array, function($a,$b){
        return $a['total'] < $b['total'];
      });
    }
    
    $informations = $array;

    // dd($informations);
   
    $aviso = null;
    
    } else {
      $aviso = "Selecione uma data";
      $informations = array();
    }


      return view("{$this->diretorioPrincipal}.{$this->nameView}.resultado", compact('aviso','informations','principal', 'rota'));
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
}

public function getComissoes(){
  $data=$_GET;
  if (Defender::hasPermission("fin-movimentation/publisher")) {
    $principal = $this->diretorioPrincipal;
    $primaryKey = $this->primaryKey;
    $rota = $this->nameView;

    if($data){
          $where = "TR.date BETWEEN '".$this->formatar_data($data['from'],3)."' AND '".$this->formatar_data($data['to'],3)."'";
          $pre   = "PP.date BETWEEN '".$this->formatar_data($data['from'],3)."' AND '".$this->formatar_data($data['to'],3)."'";
          

    $sql = "
        select
            P.id_user,
            C.company,
            C.name,
            TR.site,
            C.husky_token,
            sum(TR.impressions) as impressoes,
            sum(TR.clicks) as cliques,
            sum(TR.earnings_client) as total ,
            sum(TR.earnings) as total_network ,
            max(TR.date) as date,
            MONTH(TR.date) as month,
            YEAR(TR.date) as year,
            PP.final_value,
            C.gerente_contas,
            (SELECT SUM(valor) FROM fin_invoices WHERE publisher_id = P.id_user AND status = 2) as antecipado
        from
            admanager_report TR
        LEFT JOIN
            domain as P ON P.name = site
        LEFT JOIN
            users as C ON P.id_user = C.id
        LEFT JOIN
            fin_pre_payment as PP ON PP.id_client = P.id_user AND $pre
        where
            $where        
        group by TR.site,year,month,P.id_user,C.name,C.husky_token,PP.final_value
        ORDER BY total desc
        LIMIT 18446744073709551610
    ";
   
    $informations =  DB::select($sql);


    $array = array();


    foreach($informations as $Key => $info){
      

      
      $invalid = FinInvalid::selectRaw('SUM(value) as total')
                          ->where('id_client',$info->id_user)
                          ->where('month',$info->month)
                          ->where('year',$info->year)
                          ->groupBy('id_client')
                          ->first();


      if(isset($invalid)){
        $valinvalid = $invalid->total;
      } else {
        $valinvalid = 0;
      }
      
      

      if($info->id_user){

        $array[$Key]['id_user'] = $info->id_user;
        $array[$Key]['company'] = $info->company;
        $array[$Key]['name'] = $info->name;
        $array[$Key]['month'] = $info->month;
        $array[$Key]['year'] = $info->year;
        $array[$Key]['invalid'] = $valinvalid;
        $array[$Key]['antecipados'] = $info->antecipado;
        $array[$Key]['husky_token'] = $info->husky_token;
        $array[$Key]['gerente_contas'] = $info->gerente_contas;
      
        $array[$Key]['id_domain']            =  Domain::where('name',$info->site)->first()->id_domain_status;
        $array[$Key]['domain_status']        =  Domain::where('name',$info->site)->first()->id_domain;
        $array[$Key]['domain']               =  $info->site;
        $array[$Key]['total']                =  $info->total;
        $array[$Key]['total_network']        =  $info->total_network;
        $array[$Key]['ravshare']             =  100-(round(($info->total/$info->total_network)*100));

    
      }
    }



    if(!empty($a['total']) && !empty($b['total'])){
      usort($array, function($a,$b){
        return $a['total'] < $b['total'];
      });
    }
    
    $informations = $array;

    

    foreach($informations as $info){
      $val = Helper::comissaoPorcentagem(($info['total_network']-$info['total']),$info['ravshare']);
      
      if(isset($info['gerente_contas']) && isset($val['ravshare'])){
        $narray[$info['gerente_contas']]['id_user'] = $info['gerente_contas'];
        $narray[$info['gerente_contas']]['name'] = Helper::nome($info['gerente_contas']);
        if(isset($narray[$info['gerente_contas']]['total'])){
          $narray[$info['gerente_contas']]['total'] += $val['valor'];
        } else {
          $narray[$info['gerente_contas']]['total'] = $val['valor'];
        }

      }

    }
    
    foreach($informations as $info){
      $val = Helper::comissaoPorcentagem(($info['total_network']-$info['total']),$info['ravshare']);
      
      if(isset($info['gerente_contas']) && isset($val['ravshare'])){
        $narray[3]['id_user'] = 3;
        $narray[3]['name'] = 'Eduardo Araujo';
        if(isset($narray[3]['total'])){
          $narray[3]['total'] += $val['valor'];
        } else {
          $narray[3]['total'] = $val['valor'];
        }

      }

    }

    
   
    $aviso = null;
    
    } else {
      $aviso = "Selecione uma data";
      $informations = array();
    }


      return view("{$this->diretorioPrincipal}.{$this->nameView}.comissoes", compact('aviso','informations','principal', 'rota','narray'));
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
}

public function publisher(){
  $data=$_GET;
  if (Defender::hasPermission("fin-movimentation/publisher")) {
    $principal = $this->diretorioPrincipal;
    $primaryKey = $this->primaryKey;
    $rota = $this->nameView;

    if($data){
          $where = "TR.date <= '".$this->formatar_data($data['to'],3)."'";
          $pre = "PP.date <= '".$this->formatar_data($data['to'],3)."'";          

          $where = "TR.date BETWEEN '".$this->formatar_data($data['from'],3)."' AND '".$this->formatar_data($data['to'],3)."'";
          $pre   = "PP.date BETWEEN '".$this->formatar_data($data['from'],3)."' AND '".$this->formatar_data($data['to'],3)."'";
      

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
            MONTH(TR.date) as month,
            YEAR(TR.date) as year,
            PP.final_value,
            (SELECT SUM(valor) FROM fin_invoices WHERE publisher_id = P.id_user AND status = 2) as antecipado
        from
            admanager_report TR
        LEFT JOIN
            domain as P ON P.name = site
        LEFT JOIN
            users as C ON P.id_user = C.id
        LEFT JOIN
            fin_pre_payment as PP ON PP.id_client = P.id_user AND $pre
        where
            $where
        AND
            status_payment = 0
        AND
            disapproved = 0
        group by TR.site,year,month,P.id_user,C.name,C.husky_token,PP.final_value
        ORDER BY total desc
        LIMIT 18446744073709551610
    ";


    $informations =  DB::select($sql);
    
    

    $array = array();

    foreach($informations as $info){
      

      
      $invalid = FinInvalid::selectRaw('SUM(value) as total')
                          ->where('id_client',$info->id_user)
                          ->where('month',$info->month)
                          ->where('year',$info->year)
                          ->groupBy('id_client')
                          ->first();


      if(isset($invalid)){
        $valinvalid = $invalid->total;
      } else {
        $valinvalid = 0;
      }
      
      

      if($info->id_user){

        $array[$info->id_user]['id_user'] = $info->id_user;
        $array[$info->id_user]['company'] = $info->company;
        $array[$info->id_user]['name'] = $info->name;
        $array[$info->id_user]['month'] = $info->month;
        $array[$info->id_user]['year'] = $info->year;
        $array[$info->id_user]['invalid'] = $valinvalid;
        $array[$info->id_user]['antecipados'] = $info->antecipado;
        $array[$info->id_user]['husky_token'] = $info->husky_token;

        $array[$info->id_user]['report'][] = array(
          'ids'=>$info->ids,
          'id_domain'=>Domain::where('name',$info->site)->first()->id_domain,
          'domain'=>$info->site,
          'total'=>$info->total,
          'status'=>Domain::where('name',$info->site)->first()->id_domain_status,
        );


        if(empty($array[$info->id_user]['geral_ids'])){
          $array[$info->id_user]['geral_ids'] = $info->ids;
        } else {
          $array[$info->id_user]['geral_ids'] .= ','.$info->ids;
        }

        // $id_domain_status = Domain::where('name',$info->site)->first()->id_domain_status;


          if(empty($array[$info->id_user]['total'])){
            $array[$info->id_user]['total'] = 0;
            $array[$info->id_user]['total'] += $info->total;
          } else {
            $array[$info->id_user]['total'] += $info->total;
          }

      }
    }

    


    if(!empty($a['total']) && !empty($b['total'])){
      usort($array, function($a,$b){
        return $a['total'] < $b['total'];
      });
    }

    
    

    $informations = $array;

    $informations_back = $array;
 
    $aviso = null;
    
    } else {
      $aviso = "Selecione uma data";
      $informations = array();
      $informations_back = array();
    }

    // dd($informations);

      return view("{$this->diretorioPrincipal}.{$this->nameView}.publisher", compact('aviso','informations','informations_back','principal', 'rota'));
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  } 


public function getResultadoPublisher(){
  $data=$_GET;
  if (Defender::hasPermission("fin-movimentation/resultado-publisher")) {
    $principal = $this->diretorioPrincipal;
    $primaryKey = $this->primaryKey;
    $rota = $this->nameView;

    if($data){
          $where = "TR.date <= '".$this->formatar_data($data['to'],3)."'";
          $pre = "PP.date <= '".$this->formatar_data($data['to'],3)."'";          

          $where = "TR.date BETWEEN '".$this->formatar_data($data['from'],3)."' AND '".$this->formatar_data($data['to'],3)."'";
          $pre   = "PP.date BETWEEN '".$this->formatar_data($data['from'],3)."' AND '".$this->formatar_data($data['to'],3)."'";

          if(Helper::dados(Auth::user()->id)->role_id == 9){
            $gerente = "AND gerente_contas = ".Auth::user()->id;
          } else {
            $gerente = "";
          }
          

    $sql = "
        select
            P.id_user,
            C.company,
            C.name,
            C.gerente_contas,
            TR.site,
            C.husky_token,
            GROUP_CONCAT(DISTINCT TR.id_admanager_report SEPARATOR ',') as ids,
            sum(TR.impressions) as impressoes,
            sum(TR.clicks) as cliques,
            sum(TR.earnings_client) as total ,
            max(TR.date) as date,
            MONTH(TR.date) as month,
            YEAR(TR.date) as year,
            PP.final_value,
            (SELECT SUM(valor) FROM fin_invoices WHERE publisher_id = P.id_user AND status = 2) as antecipado
        from
            admanager_report TR
        LEFT JOIN
            domain as P ON P.name = site
        LEFT JOIN
            users as C ON P.id_user = C.id
        LEFT JOIN
            fin_pre_payment as PP ON PP.id_client = P.id_user AND $pre
        where
            $where
        AND
            status_payment = 0
        AND
            disapproved = 0
        $gerente
        group by TR.site,year,month,P.id_user,C.name,C.husky_token,PP.final_value
        ORDER BY total desc
        LIMIT 18446744073709551610
    ";


    $informations =  DB::select($sql);
    
    

    $array = array();

    foreach($informations as $info){
      

      
      $invalid = FinInvalid::selectRaw('SUM(value) as total')
                          ->where('id_client',$info->id_user)
                          ->where('month',$info->month)
                          ->where('year',$info->year)
                          ->groupBy('id_client')
                          ->first();


      if(isset($invalid)){
        $valinvalid = $invalid->total;
      } else {
        $valinvalid = 0;
      }
      
      

      if($info->id_user){

        $array[$info->id_user]['id_user'] = $info->id_user;
        $array[$info->id_user]['company'] = $info->company;
        $array[$info->id_user]['name'] = $info->name;
        $array[$info->id_user]['month'] = $info->month;
        $array[$info->id_user]['year'] = $info->year;
        $array[$info->id_user]['invalid'] = $valinvalid;
        $array[$info->id_user]['antecipados'] = $info->antecipado;
        $array[$info->id_user]['husky_token'] = $info->husky_token;
        $array[$info->id_user]['gerente_contas'] = $info->gerente_contas;

        $array[$info->id_user]['report'][] = array(
          'ids'=>$info->ids,
          'id_domain'=>Domain::where('name',$info->site)->first()->id_domain,
          'domain'=>$info->site,
          'total'=>$info->total,
          'status'=>Domain::where('name',$info->site)->first()->id_domain_status,
        );


        if(empty($array[$info->id_user]['geral_ids'])){
          $array[$info->id_user]['geral_ids'] = $info->ids;
        } else {
          $array[$info->id_user]['geral_ids'] .= ','.$info->ids;
        }

        // $id_domain_status = Domain::where('name',$info->site)->first()->id_domain_status;


          if(empty($array[$info->id_user]['total'])){
            $array[$info->id_user]['total'] = 0;
            $array[$info->id_user]['total'] += $info->total;
          } else {
            $array[$info->id_user]['total'] += $info->total;
          }

      }
    }

    


    if(!empty($a['total']) && !empty($b['total'])){
      usort($array, function($a,$b){
        return $a['total'] < $b['total'];
      });
    }

    
    

    $informations = $array;

    $informations_back = $array;
 
    $aviso = null;
    
    } else {
      $aviso = "Selecione uma data";
      $informations = array();
      $informations_back = array();
    }

    // dd($informations);

      return view("{$this->diretorioPrincipal}.{$this->nameView}.publisher-resultado", compact('aviso','informations','informations_back','principal', 'rota'));
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  } 


  public function getCheckingAccount(){
        $principal = $this->diretorioPrincipal;
        $primaryKey = $this->primaryKey;
        $rota = $this->nameView;
        $id = Auth::user()->id;    
        
        $sql_ad_manager = "
                  select
                      P.id_user,
                      sum(TR.impressions) as impressoes,
                      sum(TR.clicks) as cliques,
                      sum(TR.earnings_client) as total ,
                      TR.site,
                      TR.date
                  from
                      admanager_report TR
                  LEFT JOIN
                      domain as P ON P.name = site
                  LEFT JOIN
                      users as C ON P.id_user = C.id
                  WHERE
                      P.id_user = $id      
                  group by TR.date,TR.site,P.id_user
                  ORDER BY date";
    
        $ad_manager    = DB::select($sql_ad_manager);
        $movimentacoes = FinMovimentation::where('id_client',$id)->get();        
        $invoices      = FinInvoices::where('publisher_id',$id)->get();
        $invalids      = FinInvalid::where('id_client',$id)->get();
        
        $receitas = 0;
        $saidas = 0;
        $invalidos = 0;
        $vetor = [];

        foreach ($ad_manager as $key => $data) {
          $vetor[$data->date]['receita'][] = [
            'impressoes'=>$data->impressoes,
            'clicks'=>$data->cliques,
            'valor'=>$data->total,
            'site'=>$data->site,
          ];
          $receitas += $data->total;
        }
        
        foreach ($invoices as $key => $data) {
          $date_vetor = explode(' ',$data->created_at);
          if($data->status==2){
            $vetor[$date_vetor[0]]['invoices'][] = [
              'id_fin_invoices'=>$data->id_fin_invoices,
              'valor'=>$data->valor,
              'status'=>$data->status
            ];
            $saidas += $data->valor;
          }
        }

        foreach ($movimentacoes as $key => $data) {
          $date_vetor = explode(' ',$data->created_at);
          $vetor[$date_vetor[0]]['movimentation'][] = [
            'id_fin_movimentation'=>$data->id_fin_movimentation,
            'valor'=>$data->value,
            'status'=>$data->status
          ];
          $saidas += $data->value;
        }      
        
        foreach ($invalids as $key => $data) {
          $date_vetor = explode(' ',$data->created_at);
          $vetor[$date_vetor[0]]['invalid'][] = [
            'id_fin_invalid'=>$data->id_fin_invalid,
            'valor'=>$data->value,
            'status'=>$data->status
          ];
          $invalidos += $data->value;
        }      
        
        
        $final = [
          'saidas'=>$saidas,
          'receitas'=>$receitas,
          'invalidos'=>$invalidos,
          'total'=>$receitas-($saidas+$invalidos)
        ];
        krsort($vetor);
        

        return view("{$this->diretorioPrincipal}.{$this->nameView}.checking-account", compact('principal','primaryKey','rota','vetor','final'));    
  }
  
  public function getMyOpened(){
        $principal = $this->diretorioPrincipal;
        $primaryKey = $this->primaryKey;
        $rota = $this->nameView;
        $adx = $this->getFinanAdx(Auth::user()->id);      
        if(!isset($adx)){ $adx = []; }
        return view("{$this->diretorioPrincipal}.{$this->nameView}.my-opened", compact('principal','primaryKey','rota','adx'));    
  }
 
  public function getAllInvoices($status=null){
           $roles = User::join('role_user','role_user.user_id','users.id')
              ->where('role_user.user_id', Auth::user()->id)
              ->pluck('role_id');
          $role = 0;
          foreach ($roles as $xrole) {
          $role = $xrole;
          }

          $mp = $this->MP->where('status','1')->limit(1)->orderBY('id_fin_mass_payment','desc')->first();
      
      if($role == 1){

        $principal = $this->diretorioPrincipal;
        $primaryKey = $this->primaryKey;
        $rota = $this->nameView;        
        $total = 0;
        $subtotal = 0;
        $invoices = FinInvoices::get();
        
        return view("{$this->diretorioPrincipal}.{$this->nameView}.all-invoices", compact('principal','primaryKey','rota','adx','total','invoices','dolar','status','mp'));    
      } else {
        return redirect("/{$this->diretorioPrincipal}");
      }
  }
  
  public function postAllInvoices(){
    $data = $_POST;
    $array = array(
      'masspay_id'=>$data['mp'],
      'token'=>$data['husky'],
      'final_value'=>$data['value'],
      'tracking_code'=>$data['id']
    );

    $retorno = $this->Husky->husky_mp_add($array,$data['currency']);


    if($retorno['meta']['code'] == 200){
      $data2 = [
        'status'=>2,
        'user_id'=>Auth::user()->id,
        'id_fin_invoices'=>$data['id']

      ];
      $invoices = FinInvoices::where('id_fin_invoices',$data['id']);
  
      $dados = $invoices->first('publisher_id');
      $info = Helper::dados($dados->publisher_id);
      
      $invoices->update($data2);

      $nome = explode(' ',$info->name);

      $titulo = 'Olá '.$nome[0].', Pagamento enviado!';
      $informacao = 'Atenção enviado em breve estará em sua conta bancaria.';
      $this->sendEmail('pagamentos', ['title'=>$titulo,'informacao'=>$informacao] ,$titulo,$info->email,'financeiro@joinads.me');
      
      echo json_encode($invoices);
    } else {
      echo json_encode(array('msg'=>'Problemas ao efetuar o pagamento'));
    }
  }

  public function getStatus($status=null,$id=null){
    $data = [
              'status'=>$status,
              'user_id'=>Auth::user()->id,
              'id_fin_invoices'=>$id
  
    ];
    // 1 - Aguardando / 2 - Pago / 3 - Recusado / 4 - Extono
    $invoices = FinInvoices::where('id_fin_invoices',$id);
  
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
    // $info->email = 'caio@caionorder.com';
    $this->sendEmail('pagamentos', ['title'=>$titulo,'informacao'=>$informacao] ,$titulo,$info->email,'financeiro@joinads.me');
    
    echo json_encode($invoices);
  
    die();
  }
  
  public function getMyInvoices($status=null){

        if(Auth::user()->net_pag == 1){

        $principal = $this->diretorioPrincipal;
        $primaryKey = $this->primaryKey;
        $rota = $this->nameView;
        $adx = $this->getFinanAdx(Auth::user()->id);   

          $total = 0;      
          $dolar = Settings::first()->dolar;
          if($adx){  
          foreach($adx as $ad){
            $total += ($ad['value']-$ad['invalid']);
          }
        }

        $subtotal = 0;
        $invoices = FinInvoices::where('publisher_id',Auth::user()->id)
                                 ->get();

        foreach($invoices as $in){
          if($in['status']!=3 && $in['status']!=4){
            $subtotal += $in->valor;
          }
        }

        $total = $total-$subtotal;
        
        return view("{$this->diretorioPrincipal}.{$this->nameView}.my-invoices", compact('principal','primaryKey','rota','adx','total','invoices','dolar','status'));    
      } else {
        return redirect("{$this->diretorioPrincipal}/");
      }
  }

  public function postMyInvoices(){
    $dadosForm = $this->request->all();
    $dadosForm['user_id'] = Auth::user()->id;

    if($dadosForm['value'] < 50){
      return redirect("{$this->diretorioPrincipal}/fin-movimentation/my-invoices/3");
    }
  
    $adx = $this->getFinanAdx(Auth::user()->id);  
    
    $dolar = Settings::first()->dolar;
    
    $total = 0;
    foreach($adx as $ad){
      $total += $ad['value'];
    }
  
    $subtotal = 0;
    $invoices = FinInvoices::where('publisher_id',Auth::user()->id)
                            ->whereIn('status',[1,2])
                            ->get();

    foreach($invoices as $in){
      $subtotal += $in->valor;
    }
  
    $total = $total-$subtotal;

    $calcula_dolar = $dadosForm['value']*$dolar;
    $husk = ($calcula_dolar*3.7)/100;

    $final = $calcula_dolar-$husk;
  
    $dadosForm['ganhos'] = $total;
    if($dadosForm['value'] <= $dadosForm['ganhos']){
      FinInvoices::insert([
                                    'valor'=>$dadosForm['value'],
                                    'dolar'=>$dolar,
                                    'final'=>$final,
                                    'publisher_id'=>$dadosForm['user_id'],
                                    'created_at'=>date('Y-m-d H:i:s')
                                  ]);
      $status = 1;
    } else {
      $status = 2;
    }
  
    return redirect("{$this->diretorioPrincipal}/fin-movimentation/my-invoices/".$status);
    
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
            PP.final_value,
            (SELECT SUM(valor) FROM fin_invoices WHERE publisher_id = P.id_user AND status = 2) as antecipado
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
        $array2[$info2->id_user]['report']['dados'][$mesano]['antecipado'] = $info2->antecipado;

        
        

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

    // dd($array2);

    if(empty($array2)){
      $array2 = [];
    }

    $informations_back = $array2;


    if($informations_back){
      $adx_report = $informations_back[$id]['report']['dados'];
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

    
    
    return $adx_report;


  }

  public function postDestroy($id) {
    if (Defender::hasPermission("{$this->nameView}")) {
      $this->model->findOrFail($id)->delete();
      return redirect($_SERVER['HTTP_REFERER']);
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

}
