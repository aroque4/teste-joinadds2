@inject('Settings', 'App\Models\Painel\Settings')
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
  <meta name="og:image" content="{{URL('assets/site/imgs/1.jpg')}}">
  <meta name="og:title" content="Faça parte do Clube HURB!">
  <meta name="og:description" content="Ganhe comissão sendo um afiliado do Hotel Urtbano - HURB!">
  <title>CLUBE HU Hotel Urbano - HURB</title>
  <link rel="stylesheet" href="{{URL('assets/site')}}/css/bootstrap.min.css">
  <link rel="stylesheet" href="{{URL('assets/site')}}/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css">
  <link rel="stylesheet" type="text/css" href="{{URL('/assets/painel')}}/bootstrap-sweetalert/sweet-alert.css" />
  <!-- Global site tag (gtag.js) - Google Analytics -->
   <script src="//cdn.pn.vg/sites/bcde8285-5efb-49f9-9c9e-b5196b877b41.js" async></script>


   <script>
     window.dataLayer = window.dataLayer || [];
     function gtag(){dataLayer.push(arguments);}
     gtag('js', new Date());
     gtag('config', 'UA-20223616-11');
   </script>

   <!-- Google Tag Manager -->
  <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
  new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
  j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
  'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
  })(window,document,'script','dataLayer','GTM-MJ642N7');</script>
  <!-- End Google Tag Manager -->

   <!--
   <script type='text/javascript' data-cfasync='false'>
      window.purechatApi = { l: [], t: [], on: function () { this.l.push(arguments); } }; (function () { var done = false; var script = document.createElement('script'); script.async = true; script.type = 'text/javascript'; script.src = 'https://app.purechat.com/VisitorWidget/WidgetScript'; document.getElementsByTagName('HEAD').item(0).appendChild(script); script.onreadystatechange = script.onload = function (e) { if (!done && (!this.readyState || this.readyState == 'loaded' || this.readyState == 'complete')) { var w = new PCWidget({c: '72184a6c-0d98-40b1-87b9-ce20dbb12dd8', f: true }); done = true; } }; })();
   </script>
   -->
</head>
<body class="fixed-left">
  <script async src="https://www.googletagmanager.com/gtag/js?id=UA-20223616-11"></script>

  <!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-MJ642N7"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->

  <div id="wrapper">
  <nav class="navbar navbar-default">
    <div class="container-fluid">
      <!-- Brand and toggle get grouped for better mobile display -->
      <div class="navbar-header">
        <button type="button" class="navbar-toggle collapsed navbar-right" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
          <span class="sr-only">Toggle navigation</span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand logo" href="#" style="margin-left: 0px !important">
          <img src="{{URL('/assets/painel/uploads/settings')}}/{{$Settings->first()->logo_white}}" class="img-responsive">
        </a>
      </div>

      <!-- Collect the nav links, forms, and other content for toggling -->
      <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
        <ul class="nav navbar-nav navbar-right" style="margin-right: 5%  !important">
          <li>
            <a href="#produtos">Produtos</a>
          </li>
          <li>
            <a href="#faq">FAQ</a>
          </li>
          <li>
            <a href="/painel">Login</a>
          </li>
          <!--
            <li class="active">
              <a href="#">Cadastre-se</a>
            </li>
          -->
        </ul>
      </li>
    </ul>
  </div><!-- /.navbar-collapse -->
</div><!-- /.container-fluid -->
</nav>

<section class="login-block">
  <div class="container">
    <div class="row">

      <div class="col-md-8">
        <div class="col-md-12 frase-banner">

        </div>
      </div>

      <div class="col-md-4 login-sec registro" style="z-index: 99999;">
        <h2>Cadastre-se e saiba como:</h2>
        <form class="form-horizontal" method="POST" action="{{ route('register') }}">
          @csrf

          @if(isset($id))
          <?php session(['id_user_mgm' => $id]);?>
          @endif

          @if(isset($_GET['utm_source']))
          <?php session(['utm_source' => $_GET['utm_source']]);?>
          @elseif(empty(session('utm_source')))
          <?php session(['utm_source' => null]);?>
          @endif


          <?php $utms = ''; ?>
          @foreach($_GET as $key => $value)
            <?php $utms .= $key."=".$value."&";  ?>
          @endforeach

          <input type="hidden" name="id_user" value="@if(!empty(session('id_user_mgm'))){{session('id_user_mgm')}}@endif">
          <input type="hidden" name="origem" value="{{session('utm_source')}}">
          <input type="hidden" name="utms" value="{{$utms}}">

          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <input type="text" class="form-control" placeholder="Nome Completo" name="name" value="{{ old('name') }}" required autofocus>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <input type="email" class="form-control" placeholder="E-mail" name="email" value="{{ old('email') }}" required>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <input type="email" class="form-control" placeholder="Confirmar E-mail" name="email_confirmation" value="{{ old('email_confirmation') }}" required>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <input type="password" class="form-control" placeholder="Senha" name="password" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <input type="password" class="form-control" placeholder="Confirmar Senha" name="password_confirmation" required>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <input type="text" class="form-control" placeholder="Nome da Empresa (opcional)" name="empresa">
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <input type="text" class="form-control" placeholder="Url do site (opcional)" name="blog">
              </div>
            </div>
          </div>
          <div class="row text-center">
            <div class="col-md-12">
              <button type="submit" class="btn">CADASTRAR</button>
            </div>
          </div>
          <div class="row row-termos">
            <div class="col-md-7">
              <a class="termos" href="#" data-toggle="modal" data-target="#exampleModal">
                Termos de responsabilidade
              </a>
            </div>

            <div class="col-md-5 aceitar-termos form-horizontal">
              <div class="form-group">
                <label for="inputType" class="control-label">
                  <input type="radio" name="termos" class="aceito" required>
                  <i>
                    Eu li e aceito
                  </i>
                </label>
              </div>
            </div>
          </div>
        </form>
      </div>

    </div>
  </section>

  <!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" style="z-index: 100000;">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <h5> <b>CONTRATO DE PRESTAÇÃO DE SERVIÇOS DE PUBLICIDADE ONLINE </b> </h5>
        <p>São partes neste instrumento,</p>
        <p><b>GRUPO HURB VIAGENS E TURISMO S.A.</b>, sociedade anônima com sede na cidade e Estado do Rio de Janeiro, na Avenida João Cabral de Mello Neto, nº 400, 7º andar, Barra da Tijuca, CEP 22.775-057, inscrita no CNPJ sob o nº 12.954.744/0001-24, devidamente qualificada na forma do seu contrato social, doravante denominada simplesmente <b>“GRUPO HURB”</b>;</p>
        <p>e, por outro lado;</p>
        <p><b>PARCEIRO</b>, devidamente qualificado no Formulário de Serviços, doravante denominada de “PARCEIRO”; e</p>
        <p>(doravante referidas em conjunto como <b>“PARTES”</b>, e cada uma delas, individual e indistintamente referida como <b>“PARTE”</b>),</p>
        <p>Decidem, livre e espontaneamente, de comum acordo, celebrar o presente <b>CONTRATO DE PRESTAÇÃO DE SERVIÇOS DE PUBLICIDADE ONLINE (“CONTRATO”)</b>, regido pelas cláusulas e condições abaixo estabelecidas:</p>

        <br>

        <h5> <b>CLÁUSULA PRIMEIRA – DEFINIÇÃO</b> </h5>

        <p><b>1.1.</b> Para a justa e correta interpretação deste Contrato, serão adotadas as seguintes definições:</p>
        <p>i. “Divulgações” – É a divulgação, pelo <b>PARCEIRO</b> de produtos e serviços comercializados pelo <b>GRUPO HURB</b>. As Divulgações poderão ser feitas pelo <b>PARCEIRO</b> por meio da inclusão de banners, imagens e motores de busca em seu Portal, bem como pela criação de conteúdo personalizado que divulgue os produtos e serviços do <b>GRUPO HURB</b>, da forma mais conveniente ao <b>PARCEIRO</b> e desde que em conformidade com os termos do presente Contrato.</p>
        <p>ii. “Portal” – Espaço virtual de propriedade do <b>PARCEIRO</b>, tais quais, mas não se limitando a, e-mails, rede sociais, homepages, websites, blogs, portais e aplicativos para celulares. O endereço do Portal do <b>PARCEIRO</b> está devidamente qualificado no Formulário de Serviços e não serão considerados integrantes do Portal nenhum outro espaço virtual que não aquele indicado no Formulário de Serviços;</p>
        <p>iii. “Internauta” – Usuários de internet que acessarem o Portal do <b>PARCEIRO</b>;</p>
        <p>iv. “Conteúdo” – Toda e qualquer informação produzida, coletada, compilada, armazenada, hospedada, utilizada, modificada ou reproduzida e disponibilizada nos ambientes do Portal do <b>PARCEIRO</b>, seja de forma direta ou indireta, por qualquer usuário ou responsável pela inserção de dados no Portal. Considera-se, ainda, como “Conteúdo” todo e qualquer programa de computador, ferramenta, aplicativos, vírus e/ou outros elementos congêneres;</p>
        <p>v. “Plataforma” – Espaço virtual fornecido pelo <b>GRUPO HURB</b>, por meio do qual o <b>PARCEIRO</b> poderá acessar com login e senha de sua responsabilidade. Através da Plataforma, o <b>PARCEIRO</b> poderá acompanhar o número de Compras Comissionáveis realizadas no site do <b>GRUPO HURB</b> advindas do direcionamento de suas Divulgações, bem como ter acesso a promoções, banners e links fornecidos pelo <b>GRUPO HURB</b> para auxiliá-lo em suas Divulgações.</p>
        <p>vi. “Comissão” – Valor devido pelo <b>GRUPO HURB</b> ao <b>PARCEIRO</b>, equivalente a uma porcentagem, definida no presente Contrato, do valor de cada compra realizada no site do <b>GRUPO HURB</b> oriunda das Divulgações;</p>
        <p>vii. “Compra Comissionável” – Compra de produto ou serviço realizada pelo Internauta no site do <b>GRUPO HURB</b>, a qual é passível de recebimento de Comissão pelo <b>PARCEIRO</b>. A Compra Comissionável deverá ser necessariamente advinda das Divulgações, as quais deverão remeter os Internautas ao site do <b>GRUPO HURB</b> por meio da inclusão de um link com o Código do </b>PARCEIRO</b>, seguindo todas as instruções fornecidas pelo <b>GRUPO HURB</b>. Caso as Divulgações não contenham o Código fornecido pelo <b>GRUPO HURB</b> ao <b>PARCEIRO</b>, as compras dali advindas não serão consideradas comissionáveis. Somente serão consideradas Compras Comissionáveis as compras decorrentes do last click no link contendo o Código do <b>PARCEIRO</b>, ou seja, as compras decorrentes do acesso imediato ao site do <b>GRUPO HURB</b> pelas Divulgações do <b>PARCEIRO</b>. As compras feitas pelos Internautas no site do <b>GRUPO HURB</b> utilizando exclusivamente créditos que o Internauta possua no site não serão consideradas Compras Comissionáveis. Caso uma compra feita por um Internauta no site do <b>GRUPO HURB</b> envolva simulações fraudulentas de transações comerciais, incluindo sem se limitar a uso de dados de terceiros sem consentimento ou uso de dados falsos quando do pedido de produtos e serviços ou do registro online, a referida transação não será considerada uma Compra Comissionável, ainda que o <b>PARCEIRO</b> não tenha culpa na fraude realizada pelo Internauta.</p>
        <p>viii. "Código” – O <b>GRUPO HURB</b> fornecerá ao <b>PARCEIRO</b> um código CMP próprio, o qual terá a função de rastrear as compras originadas das Divulgações.</p>
        <p>ix. “Informações Confidenciais” – Significam o conteúdo do Contrato e todas as informações fornecidas (seja de forma escrita, digital e/ou oral, direta ou indiretamente) por uma Parte para a outra Parte, seja antes ou depois da assinatura do Contrato, independentemente de ser previamente marcada ou indicada como confidencial, ou cuja Parte que a receba entenda, de forma razoável, serem Informações Confidenciais devido à natureza das informações ou às circunstâncias dos serviços. As Informações Confidenciais incluem, mas não se limitam a, informações relacionadas ao fornecimento de informações sobre produtos e serviços, operações, clientes e prospectos, tecnologia, know-how, pesquisa e desenvolvimento, direitos sobre desenhos, segredos de mercado, oportunidades de mercado ou relações comerciais da Parte. As Informações Confidenciais incluem informações fornecidas por ou em nome de qualquer uma das Partes para a outra Parte. Também se entendem como Informações Confidenciais aquelas das quais as Partes usufruam de valor econômico, real e/ou potencial, por não serem conhecidas de modo geral e não sendo prontamente definidas pelos devidos meios por outras pessoas que possam obter valor econômico através da sua divulgação ou uso e estejam sujeitas a esforços razoáveis em vista das circunstâncias para manter o seu sigilo, incluindo tal informação em mídia tangível, tal como mídia em forma escrita, fitas, meios magnéticos e/ou outros meios eletrônicos, divulgações verbais, e/ou por todo e qualquer outro meio. </p>

        <br>
        <h5> <b>CLÁUSULA SEGUNDA – OBJETO</b> </h5>

        <p>2.1. O presente Contrato tem por objeto a prestação de serviços de publicidade, pelo <b>PARCEIRO</b> ao <b>GRUPO HURB</b> através de seu Portal, conforme as condições comerciais definidas no presente Contrato.</p>
        <p>2.2. Durante a vigência do presente Contrato, o <b>PARCEIRO</b> realizará Divulgações em seu Portal.</p>
        <p>2.3. O <b>GRUPO HURB</b> irá remunerar o <b>PARCEIRO</b>, nos termos dispostos no presente Contrato, a depender do número de Compras Comissionáveis efetivamente realizadas e aprovadas no site do <b>GRUPO HURB</b> provenientes do direcionamento das Divulgações do <b>PARCEIRO</b>.</p>

        <br>
        <h5> <b>CLÁUSULA TERCEIRA – FORMA DE PAGAMENTO</b> </h5>

        <p>3.1. Como contraprestação pelas Divulgações realizadas pelo <b>PARCEIRO</b>, o <b>GRUPO HURB</b> pagará ao <b>PARCEIRO</b> uma comissão equivalente a 6,5% (seis e meio por cento) do valor de cada Compra Comissionável. A depender do volume de Compras Comissionáveis decorrentes das Divulgações do <b>PARCEIRO</b>, o <b>GRUPO HURB</b> poderá, a seu exclusivo critério, deliberar pela atribuição de uma comissão superior a 6,5% (seis e meio por cento) do valor de cada Compra Comissionável.</p>
        <p>3.2. Para fins de cálculo do valor devido ao <b>PARCEIRO</b> a título de comissão, deverá ser considerado o relatório de Compras Comissionáveis disponibilizado pelo <b>GRUPO HURB</b> na Plataforma;   </p>
        <p>3.5. Não serão comissionadas ao <b>PARCEIRO</b> compras realizadas exclusivamente com créditos, independentemente de sua natureza.</p>
        <p>3.5.1. Nas compras feitas parcialmente em créditos e parcialmente em dinheiro, apenas o valor pago em dinheiro será comissionado, na forma do disposto na cláusula 3.1 acima, não cabendo ao <b>PARCEIRO</b> o direito ao recebimento de qualquer comissão sobre o valor correspondente aos créditos utilizados para conclusão da compra.</p>
        <p>3.6. No caso de uso de cupons de descontos e/ou créditos para compras pelos Internautas, fica ressalvado que o valor da comissão incidirá tão somente sobre o valor da compra pago pelo Internauta em dinheiro, excetuando-se, portanto, o valor abatido do preço final a título de crédito e/ou cupons de desconto.</p>
        <p>3.7. Os pagamentos ao <b>PARCEIRO</b> serão realizados mediante depósito bancário na conta corrente por ele indicada no Formulário de Serviços.</p>
        <p>3.7.1. Os pagamentos ao <b>PARCEIRO</b> somente serão realizados mediante o envio de Nota Fiscal ou Recibo de Pagamento Autônomo ao <b>GRUPO HURB</b>. O <b>GRUPO HURB</b> realizará os pagamentos após, no mínimo, 15 (quinze) dias úteis contados do recebimento da Nota Fiscal ou do Recibo de Pagamento Autônomo.</p>
        <p>3.8. O pagamento da comissão será realizado de forma mensal, desde que o crédito do <b>PARCEIRO</b> seja igual ou superior a R$ 100,00 (cem reais).</p>
        <p>3.8.1. Caso o valor da comissão seja inferior àquele discriminado na Cláusula 3.8, o mesmo será mantido como crédito a ser pago no mês imediatamente seguinte cumulado com o valor devido a título de comissão do respectivo mês.</p>
        <p>3.9. No valor do Contrato já estão inclusos todos os custos, diretos e indiretos, necessários à completa execução do Contrato, incluindo, mas não se limitando a, margem de lucro, deslocamento, mão-de-obra, especializada ou não, contribuições previdenciárias, todos os ônus e encargos decorrentes da legislação trabalhista e social, seguros e garantias exigidas por lei e/ou estabelecidos neste Contrato, todos os tributos que sejam devidos em decorrência, diretos ou indiretos, do presente Contrato, ou de sua execução.</p>
        <p>3.10. Em decorrência do disposto no item anterior, o <b>PARCEIRO</b> não poderá pleitear qualquer majoração do valor estabelecido no presente Contrato, sob alegação de falta ou omissão de sua estipulação ou quaisquer outras alegações de qualquer natureza.</p>
        <p>3.11. O <b>GRUPO HURB</b> se reserva no direito de suspender os pagamentos e reter todos e quaisquer valores devidos ao <b>PARCEIRO</b>, caso este último descumpra qualquer cláusula do presente Contrato ou obrigação acordada entre as Partes, ou ainda, caso o <b>GRUPO HURB</b> seja notificado ou instado a pagar dívidas de qualquer natureza do <b>PARCEIRO</b>, incluindo, mas não se limitando a, quaisquer pagamentos por produtos ou serviços subcontratados ou terceirizados que sejam essenciais à continuidade do presente Contrato, inclusive, em virtude de condenações judiciais por obrigações cíveis, trabalhistas, previdenciárias ou fiscais, devendo o <b>PARCEIRO</b> sanar as irregularidades ou, conforme o caso, providenciar o ressarcimento ao <b>GRUPO HURB</b> da(s) dívida(s) de sua responsabilidade, no prazo máximo de 15 (quinze) dias a contar do desembolso por parte do <b>GRUPO HURB</b> ou da constatação da irregularidade pelo <b>GRUPO HURB</b>, a depender do caso.</p>
        <p>3.12. A hipótese de suspensão de pagamento de que trata o item 3.11. acima não está sujeita a qualquer correção ou incidência de encargos de mora durante o período em que a(s) obrigação(ões) que originou(aram) a suspensão permanecer(em) pendente(s) de regularização.</p>
        <p>3.13. Cada Parte será responsável pela retenção, dedução, recolhimento e pagamento dos tributos, encargos, contribuições e respectivas obrigações acessórias a que der causa em razão do Contrato, nos termos da legislação aplicável.</p>
        <p>3.14. É expressamente vedada a extração de duplicata, salvo autorização por escrito do <b>GRUPO HURB</b>, renunciando o <b>PARCEIRO</b> expressamente à faculdade de extrair duplicata da fatura emitida em razão dos serviços, ou qualquer outro documento hábil a instrumentalizar protesto, sob pena de incorrer em penalidade equivalente ao valor do documento indevidamente extraído, sem prejuízo de representação criminal, propositura de ação para reparação de danos e das demais cominações previstas neste Contrato, além da faculdade do <b>GRUPO HURB</b> em rescindir o presente instrumento imediatamente, sem que seja devida qualquer multa e/ou penalidade de qualquer natureza por parte do <b>GRUPO HURB</b>.</p>
        <p>3.15. Qualquer obrigação financeira do <b>GRUPO HURB</b>, incluindo sem se limitar às obrigações de pagamento de qualquer natureza, em face do <b>PARCEIRO</b> que esteja vencida e não seja exigida pelo <b>PARCEIRO</b> por via escrita registrada dentro do prazo de 12 (doze) meses será considerada imediatamente extinta de pleno direito, nada mais podendo o <b>PARCEIRO</b> reclamar.</p>
        <p>3.16. O <b>PARCEIRO</b> não fará jus ao recebimento da comissão mencionada na Cláusula 3.1 se as transações comerciais provenientes das Divulgações envolverem simulações fraudulentas de transações comerciais, incluindo sem se limitar a, uso de dados de terceiros sem consentimento ou pelo uso de dados falsos quando do pedido de produtos e serviços ou do registro online.</p>
        <p>3.17. Somente serão contabilizadas as transações comerciais oriundas das Divulgações que contenham o Código disponibilizado pelo <b>GRUPO HURB</b>.</p>

        <br>
        <h5> <b>CLÁUSULA QUARTA – PRAZO E RESCISÃO</b> </h5>

        <p>4.1. O presente Contrato é firmado pelo prazo de 12 (doze) meses, iniciando-se na data de seu aceite pelo PARCEIRO, ficando automaticamente renovado por igual período de tempo caso as Partes não se manifestem em sentido contrário.</p>
        <p>4.2. Fica assegurado ao GRUPO HURB o direito de resilir o presente Contrato mediante simples comunicação ao PARCEIRO, sendo plenamente válida e eficaz a notificação realizada por meio digital, inclusive e-mail, sem o pagamento de qualquer multa e/ou indenização de qualquer natureza, respeitadas, entretanto, todas as obrigações assumidas até aquela data. No caso de resilição pelo GRUPO HURB, este poderá, inclusive, desativar o Código inserido na Plataforma, sem que seja necessário o cumprimento de qualquer formalidade além das previstas neste item 4.2. Nenhuma compra realizada no site do GRUPO HURB após a resilição pelo GRUPO HURB será considerada Compra Comissionável.</p>
        <p>4.3. Fica assegurado ao PARCEIRO o direito de resilir o presente Contrato, mediante a comunicação por e-mail ao GRUPO HURB, sem o pagamento de qualquer multa e/ou indenização de qualquer natureza, respeitadas, entretanto, todas as obrigações assumidas até aquela data. Nenhuma compra realizada no site do GRUPO HURB após a resilição pelo PARCEIRO será considerada Compra Comissionável.</p>

        <br>
        <h5> <b>CLÁUSULA QUINTA – OBRIGAÇÕES DAS PARTES</b> </h5>

        <p>5.1. São obrigações do <b>GRUPO HURB</b>:</p>
        <p>a) Realizar a contraprestação ao <b>PARCEIRO</b> nos moldes acordados;</p>
        <p>b) Disponibilizar ao <b>PARCEIRO</b> o Código a ser inserido nas Divulgações, bem como o login e senha de acesso à Plataforma;</p>
        <p>c) A verificação e cômputo das Compras Comissionáveis, bem como a disponibilização ao <b>PARCEIRO</b> do relatório de Compras Comissionáveis na Plataforma;</p>
        <p>d) Responder diretamente, nos termos da Lei, pelas perdas e danos a que der causa, causados ao <b>PARCEIRO</b> ou a terceiros, originados de ação ou omissão pertinentes às suas atividades, por si ou pelos seus funcionários, assumindo toda a responsabilidade e os ônus daí advindos. O <b>GRUPO HURB</b> não responderá por nenhum defeito, incluindo sem se limitar a quedas do ar, bugs, erros de formatação e lentidão, causados no Portal em razão da inserção de banners, links ou quaisquer outras mídias fornecidas ao <b>PARCEIRO</b>;</p>
        <p>e) A responsabilidade trabalhista, individual ou solidária, eventualmente estabelecida, entre o <b>PARCEIRO</b> e o pessoal do quadro de empregados do <b>GRUPO HURB</b>, é imputável única e exclusivamente a este último, que deste modo se obriga a ressarcir civilmente ao <b>PARCEIRO</b> os valores que porventura forem despendidos com ações trabalhistas ajuizadas por seus funcionários, inclusive no que seja referente a danos morais, custas processuais e honorários de sucumbência.</p>

        <br>
        <p>5.2. São obrigações do <b>PARCEIRO</b>: </p>

        <p>a) Responder diretamente, nos termos da lei, pelas perdas e danos a que der causa ao <b>GRUPO HURB</b> ou a terceiros, originados por ação ou omissão pertinentes às suas atividades, por si ou pelos seus funcionários, assumindo toda a responsabilidade e os ônus daí advindos;</p>
        <p>b) A responsabilidade trabalhista, individual ou solidária, eventualmente estabelecida, entre o <b>GRUPO HURB</b> e o pessoal do quadro de empregados do <b>PARCEIRO</b>, é imputável única e exclusivamente a este último, que deste modo se obriga a ressarcir civilmente ao <b>GRUPO HURB</b> os valores que porventura forem despendidos com ações trabalhistas ajuizadas por seus funcionários, inclusive no que seja referente a danos morais, custas processuais e honorários de sucumbência;</p>
        <p>c) O <b>PARCEIRO</b> é o único responsável por todo o Conteúdo disponibilizado por ele em sua Portal, independente da ferramenta, sistema ou forma em que o Conteúdo for disponibilizado;</p>
        <p>d) O <b>PARCEIRO</b> garante e declara deter todos os direitos sobre seu Conteúdo, incluindo direitos autorais e direitos conexos, marcas, direito de arena, bem como as imagens, sons e outras características retratadas;</p>
        <p>e) O <b>PARCEIRO</b> garante e declara que não associará as Divulgações a qualquer conteúdo ilícito, difamatório, obsceno, de ódio, discriminatório, pornográfico, contendo nudez, ou, de qualquer modo impróprio ou contrário aos bons costumes, ou a qualquer tipo de produtos ou serviços que caracterizem concorrência desleal, violação de direitos de marca, violação da privacidade ou da intimidade e privacidade de pessoas, bem como violação de segredos de negócios, indústria ou comércio;</p>
        <p>f) O <b>PARCEIRO</b> garante e declara que não veicula ou reproduz em seu Portal conteúdos classificados como conteúdo adulto (destinado a maiores de dezoito anos) ou gore (incitação à violência), bem como seu Portal não produz, reproduz, patrocina ou utiliza conteúdo protegido por direitos autorais, Pay to Click/Autosurf, pirâmide financeira, marketing multinível, vírus, malware;</p>
        <p>g) O <b>PARCEIRO</b> garante e declara que seu Portal não é um site em branco (sem conteúdo), em construção, offline, ou com login obrigatório;</p>
        <p>h) O <b>PARCEIRO</b> concorda que caberá única e exclusivamente ao <b>GRUPO HURB</b> a verificação e cômputo das Compras Comissionáveis;</p>
        <p>i) O <b>PARCEIRO</b> concorda em isentar o <b>GRUPO HURB</b> de quaisquer responsabilidades, demandas, ações judiciais, pedidos ou solicitações de terceiros decorrentes das associações feitas ao seu Conteúdo, incluindo despesas processuais e honorários advocatícios;</p>
        <p>j) O <b>PARCEIRO</b> reconhece ser o <b>GRUPO HURB</b> legítimo e exclusivo titular das marcas “HURB”, “HU”; “Hurb”; “Hotel Urbano – Viajar é possível”; “Viva Mais Música”; “Viva Mais Histórias”; e “#vivamaishistórias”;</p>
        <p>k) O <b>PARCEIRO</b> não poderá, em nenhuma hipótese, alterar o conteúdo das mídias fornecidas pelo <b>GRUPO HURB</b> por meio da Plataforma, seja em sua composição visual ou técnica, no todo ou em parte. De igual modo, o <b>PARCEIRO</b> não poderá alterar o Código fornecido pelo <b>GRUPO HURB</b>;</p>
        <p>l) É vedada ao <b>PARCEIRO</b> qualquer forma de fraude, tal como a conclusão de negócios por métodos desleais ou por meios inadmissíveis por força de lei ou deste Contrato, capaz de violar os direitos ora assegurados, como por exemplo, mas sem se limitar, as seguintes práticas:</p>
        <p>(i) utilização de sistemas onde o cadastro seja involuntário e/ ou automático no site do <b>GRUPO HURB</b>;</p>
        <p>(ii) oferta de dinheiro ou qualquer tipo de artigos, produtos, vantagens, brindes ou serviços e/ou realizar alguma promoção com o fim de gerar vendas, ações ou cadastros nos sites do <b>GRUPO HURB</b>, sem prévia autorização por escrito deste;</p>
        <p>(iii) envio massificado de e-mails com o intuito de promover algum produto cadastrado no site do <b>GRUPO HURB</b> (spam) sob qualquer condição;</p>
        <p>(iv) envio de correspondência, eletrônica ou não, que induza o destinatário em erro ou confusão acerca do remetente do e-mail, levando-o a crer que este seria a próprio <b>GRUPO HURB</b> ou utilizando as marcas do <b>GRUPO HURB</b> sem autorização prévia;</p>
        <p>(v) utilização de cadastro de terceiros para fraudar o sistema e, de alguma forma, quebrar as restrições aqui descritas ou nos Termos de Uso do <b>GRUPO HURB</b> (https://www.hotelurbano.com/termos-de-uso);</p>
        <p>(vi) valer-se de qualquer mecanismo que vise obter remuneração sem a realização devida das Divulgações, tal como, mas sem se limitar a, aposição de cookies indiscriminadamente, sem que o Internauta que receba o referido cookie haja clicado em um link que contenha o Código do <b>PARCEIRO</b>;</p>
        <p>(vii) criar sites com domínios que guardem semelhança fonética ou semântica com o <b>GRUPO HURB</b>; e</p>
        <p>(viii) contratar com quaisquer sites de busca a compra de “links patrocinados” como forma de geração de tráfego para os sites do <b>GRUPO HURB</b>.</p>
        <p>m) O <b>PARCEIRO</b> se obriga a não realizar qualquer ataque eletrônico ao GRUPO HURB. Entende-se como ataque eletrônico, incluindo sem se limitar a, tentativas de ultrapassar, desviar ou tornar ineficaz de qualquer maneira os mecanismos de segurança do <b>GRUPO HURB</b>, o emprego de programas para a seleção automática de dados, a aplicação e/ou a difusão de vírus, worms, trojans, brute force attacks, spam ou o emprego de links ou procedimentos capazes de lesar o <b>GRUPO HURB</b>.</p>

        <br>
        <h5> <b>CLÁUSULA SEXTA – CONDIÇÕES GERAIS</b> </h5>

        <p>6.1. Este Contrato configura a expressão final dos entendimentos entre as Partes referentes a seus respectivos objetos e substituem todas as negociações e documentos por escrito havidos entre as Partes e/ou entre empresas às mesmas vinculadas, anteriormente à sua celebração e afetos ao período de vigência contratual.</p>
        <p>6.2. A tolerância de qualquer das Partes quanto a qualquer violação a dispositivos deste Contrato será sempre entendida como mera liberalidade, não constituindo novação, não gerando, portanto, qualquer direito oponível pelas Partes nem a perda da prerrogativa em exigir o pleno cumprimento das obrigações contratuais avençadas e a reparação de qualquer dano.</p>
        <p>6.3. As Partes obrigam-se a manter o mais absoluto sigilo e confidencialidade sobre todas as Informações Comerciais, financeiras e técnicas, de propriedade da outra Parte, que lhe tenham sido confiadas para o perfeito e completo atendimento do objeto deste Contrato, bem como das cláusulas e condições entre as Partes aqui estabelecidas, na vigência e mesmo após o término do presente.</p>
        <p>6.4. O presente Contrato não implica em exclusividade ou associação de qualquer natureza, tampouco constituí vínculo empregatício ou societário entre as Partes ou qualquer outra forma de assunção de obrigações mútuas além das que aqui constam especificamente.</p>
        <p>6.5. As Partes se autorizam, mutuamente, a divulgar a relação comercial estabelecida neste Contrato, para fins de divulgação de portfólio de cliente ou fornecedores, conforme o caso, sem prejuízo do constante do item 6.3. desta cláusula, e obrigando-se a respeitar as normas dos departamentos de marketing da outra Parte na apresentação das marcas de cada um.</p>
        <p>6.6. Este Contrato não poderá ser cedido ou transferido a terceiros sem o prévio e expresso consentimento das Partes. Em caso de cessão, a parte cedente permanecerá respondendo solidariamente por todas as obrigações contraídas pela parte cessionária.</p>
        <p>6.7. Cada uma das Partes declara ter os poderes necessários para firmar o presente Contrato, inclusive ser proprietário de todos os direitos necessários para o devido cumprimento do objeto contratual, bem como não haver qualquer impedimento contratual ou legal que impeça a celebração do presente Contrato.</p>
        <p>6.8. A cópia digital do presente instrumento, enviada por qualquer representante de qualquer das Partes à outra Parte, quando enviada por qualquer via digital, incluindo sem se limitar ao envio através de e-mail, será plenamente válida e terá os mesmos efeitos que a via original física.</p>
        <p>6.9. Caso qualquer das cláusulas ou termos do presente Contrato sejam consideradas inválidas ou ineficazes, todas as demais cláusulas e termos permanecerão em pleno vigor e efeito.</p>
        <p>6.10. Este Contrato obriga as Partes e seus sucessores, somente podendo ser alterado por escrito, através de aditivo contratual que formalize as alterações negociais.</p>
        <p>6.11. Todos os avisos e demais comunicações entre as Partes que tenham por objeto os termos e condições previstas neste Contrato deverão ser feitos por escrito ao destinatário, sendo admissível a comunicação através de e-mail.</p>
        <p>6.12. Cada Parte manterá a outra devidamente atualizada com relação ao respectivo endereço e a pessoa responsável pelo recebimento de comunicações. <b>O PARCEIRO</b> deverá manter todas as suas informações atualizadas na Plataforma, não se responsabilizando o <b>GRUPO HURB</b> por quaisquer mensagens não recebidas pelo <b>PARCEIRO</b> em razão da mudança de seus dados de contato sem a imediata alteração na Plataforma.</p>
        <p>6.13. As Partes acordam ainda que todos os “Cookies” terão uma duração de 30 (trinta) dias corridos.</p>
        <p>6.14. Nenhuma das Partes praticará qualquer ato que, de alguma forma, possa pôr em risco ou desabonar a imagem da outra, seus produtos, serviços e marca, perante autoridades governamentais, clientes e terceiros em geral e, caso alguma das Partes venha a causar qualquer dano, material ou moral, à outra, será responsável pela reparação, na forma da lei.</p>

        <br>
        <h5> <b>CLÁUSULA OITAVA – FORO</b> </h5>
        <p>8.1. As Partes elegem o Foro da Comarca da Cidade do Rio de Janeiro, como o único competente para dirimir as questões decorrentes deste Contrato, com renúncia expressa a qualquer outro, por mais privilegiado que seja.</p>

        <br>
        <h5> <b>Dados do Produto Contratado – Condições Comerciais</b> </h5>
        <p>Ao dar aceite no Contrato de Prestação de Serviços de Publicidade Online, registrado no Oficial de Registro de Títulos e Documentos na Comarca do Rio de Janeiro, Estado do Rio de Janeiro, em {{date('d')}}/{{date('m')}}/{{date('y')}}, declaramo-nos vinculados às disposições contidas no Contrato e de estar de pleno acordo com todas as cláusulas e condições comerciais.</p>


      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>

    <div class="row text-center espaco-topo">
      <div class="col-md-10 col-sm-offset-1">
        <h2>O que é o Clube Hurb?</h2>
        <p>
          O Clube Hurb é a rede de vendedores do Hurb e foi criado para que qualquer pessoa acima de 18 anos ganhe uma renda extra revendendo nossos pacotes, hotéis e experiências sem sair de casa.
        <br>
          E não é só! Nosso programa é 100% gratuito, ou seja, você não paga nada para se afiliar.
        </p>
      </div>
    </div>

    <div class="row text-center espaco-topo">
      <div class="col-md-10 col-sm-offset-1">
        <h2>Como funciona o Clube Hurb?</h2>
        <p>
          Após realizar o seu cadastro a equipe analisará seus dados e então você receberá um e-mail de confirmação com a liberação do seu acesso para começar a revender nossas ofertas.
        </p>
        <p>
          Você será comissionado em cima de todas as vendas aprovadas que fizer vindo de sua divulgação. Nossos membros do Clube divulgam nossas ofertas de diversas maneiras, como: boca a boca com os amigos e familiares, através de suas redes sociais (Instagram, Facebook, WhatsApp...), via site/blog ou entre outros canais de comunicação. O importante é: você escolhe por onde divulgar, vende, ganha aquela renda extra e não paga nada!!! Simples assim ;)
        </p>
        <p>
          Você consegue acompanhar toda a sua performance através de um painel de controle na plataforma que atualizamos diariamente. Além disso, você também receberá um relatório mensal de fechamento com suas vendas e comissões realizadas no respectivo mês.
        </p>
      </div>


    <div class="row text-center espaco-topo">
      <div class="col-md-10 col-sm-offset-1">
        <h2>Seja um membro do Clube e faça parte de:</h2>
        <div class="col-md-3">
          <img src="{{URL('assets/site')}}/imgs/motivos/1.png">
          <p>+ de 50 mil cadastrados no programa </p>
        </div>
        <div class="col-md-2">
          <img src="{{URL('assets/site')}}/imgs/motivos/2.png">
          <p>Ofertas exclusivas para os afiliados. </p>
        </div>
        <div class="col-md-2">
          <img src="{{URL('assets/site')}}/imgs/motivos/3.png">
          <p>Programa 100% gratuito.  </p>
        </div>
        <div class="col-md-2">
          <img src="{{URL('assets/site')}}/imgs/motivos/4.png">
          <p>Comissão excelente.</p>
        </div>
        <div class="col-md-3">
          <img src="{{URL('assets/site')}}/imgs/motivos/5.png">
          <p>Membros Clube Hurb com ganhos de + de 6 mil reais por mês.</p>
        </div>
      </div>
    </div>


    <div class="container text-center espaco-topo" id="produtos">
      <h2>Produtos</h2>
      <ul class="nav nav-tabs" >
        <li class="active largura-tabs"><a data-toggle="tab" href="#mgm" style="font-size: 10px;">Member Get Member</a></li>
        <li class="largura-tabs"><a data-toggle="tab" href="#home" style="font-size: 10px;">Buscador</a></li>
        <li class="largura-tabs"><a data-toggle="tab" href="#menu4" style="font-size: 10px;">Dinâmico</a></li>
        <li class="largura-tabs"><a data-toggle="tab" href="#menu1" style="font-size: 10px;">Banner</a></li>
        <li class="largura-tabs"><a data-toggle="tab" href="#menu2" style="font-size: 10px;">Landing page</a></li>
      </ul>

      <div class="tab-content">
         <div id="mgm" class="tab-pane fade in active">
           <div class="row vertical-align text-center">
             <div class="col-md-5 col-sm-offset-2 text-produto">
               <p>
                  Ganhe dinheiro convidando amigos para o Clube Hurb.
                  Isso mesmo, você indica e se a pessoa vender você ganha 15% de comissão em cima do que o seu indicado ganhar!
               </p>
             </div>
             <div class="col-md-3">
               <img src="{{URL('assets/site')}}/imgs/produtos/5.png" class="img-responsive image-tabs" width="250">
             </div>
           </div>
         </div>

         <div id="home" class="tab-pane fade">
           <div class="row vertical-align text-center">
             <div class="col-md-5 col-sm-offset-2 text-produto">
               <p>
                 Nosso banner buscador ajuda os leitores a terem uma navegação rápida,
                 facilitando na busca dos resultados. Na plataforma você encontra opções para
                 personalizar seu banner, podendo deixar a sua cara, escolhendo formatos e
                 cores que se adequam melhor ao seu site.
               </p>
             </div>
             <div class="col-md-3">
               <img src="{{URL('assets/site')}}/imgs/produtos/1.png" class="img-responsive image-tabs" width="250">
             </div>
           </div>
         </div>

        <div id="menu4" class="tab-pane fade">
          <div class="row vertical-align text-center">
            <div class="col-md-5 col-sm-offset-2 text-produto">
              <p>
                Nosso banner dinâmico é uma funcionalidade muito bacana e prática para você que nem sempre tem tempo de ficar atualizando os banners no seu site/blog.
                Funciona assim: a gente atualiza a campanha no nosso site e ela atualiza automaticamente no seu.
              </p>
            </div>
            <div class="col-md-3">
              <img src="{{URL('assets/site')}}/imgs/produtos/4.png" class="img-responsive image-tabs" width="250">
            </div>
          </div>
        </div>

        <div id="menu1" class="tab-pane fade">
          <div class="row vertical-align text-center">
            <div class="col-md-5 col-sm-offset-2 text-produto">
              <p>
                Nossos banners personalizados ajudam bastante na divulgação. Você poderá
                solicitar banners de qualquer pacote ou campanha exclusiva do nosso site.
              </p>
            </div>
            <div class="col-md-3">
              <img src="{{URL('assets/site')}}/imgs/produtos/2.png" class="img-responsive image-tabs"  style="width: 150px !important;">
            </div>
          </div>
        </div>

        <div id="menu2" class="tab-pane fade">
          <div class="row vertical-align text-center">
            <div class="col-md-5 col-sm-offset-2 text-produto">
              <p>
                É uma página personalizada dentro do site do Hotel Urbano, onde selecionamos nossas melhores ofertas e disponibilizamos de forma exclusiva para cada afiliado. Nesta página personalizada, conseguimos passar o máximo de credibilidade possível para o leitor, pois colocamos em destaque sua marca em parceria com o Hotel Urbano.
              </p>
            </div>
            <div class="col-md-3">
              <img src="{{URL('assets/site')}}/imgs/produtos/3.png" class="img-responsive image-tabs" width="250">
            </div>
          </div>
        </div>
      </div>
    </div>



    <div class="row text-center espaco-topo">
      <div class="col-md-10 col-sm-offset-1">
        <h2>Benefício para seus clientes</h2>
        <div class="col-md-3">
          <img src="{{URL('assets/site')}}/imgs/beneficios/1.png">
          <p>Parcelamento em até 12x</p>
        </div>
        <div class="col-md-3">
          <img src="{{URL('assets/site')}}/imgs/beneficios/2.png">
          <p> Cancelamento grátis até 14 dias após a compra.</p>
        </div>
        <div class="col-md-3">
          <img src="{{URL('assets/site')}}/imgs/beneficios/3.png">
          <p>Compre com até 2 cartões de créditos</p>
        </div>
        <div class="col-md-3">
          <img src="{{URL('assets/site')}}/imgs/beneficios/4.png">
          <p>Atendimento personalizado pelo Chat/Telefone/WhatsApp 24 horas</p>
        </div>
      </div>
    </div>

  </div>

  <div class="container" id="faq">
    <div class="panel-group" id="accordion">

      <div class="row text-center espaco-topo">
        <div class="col-md-8 col-sm-offset-2">
          <h2>Faq</h2>
        </div>
      </div>

      <?php $cont = 0; ?>
      @foreach($Faqs as $Faq)
      <div class="panel panel-default">
        <div class="panel-heading">
          <h4 class="panel-title">
            <a class="accordion-toggle @if($cont != 0) collapsed @endif" data-toggle="collapse" data-parent="#accordion" href="#collapse{{$cont}}">
              {{$Faq->title}}
            </a>
          </h4>
        </div>
        <div id="collapse{{$cont}}" class="panel-collapse collapse @if($cont == 0) in @endif">
          <div class="panel-body">
            {!! $Faq->text !!}
          </div>
        </div>
      </div>
      <?php $cont++; ?>
      @endforeach
    </div>
  </div>



  <div class="row rodape text-center vertical-align espaco-topo">
    <div class="col-md-12">
      © Copyright 2018 | Hurb - Todos os direitos reservados.
    </div>
  </div>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script src="{{URL('assets/site')}}/js/bootstrap.min.js"></script>
<script src="{{URL('/assets/painel')}}/bootstrap-sweetalert/sweet-alert.min.js"></script>

  @if(count($errors) > 0)
  <?php $erros = ''; ?>
  @foreach($errors->all() as $error)
  <?php $erros .= $error . " - "; ?>
  @endforeach
  <script>
  window.onload = function () {
    swal({
      title: "Erros Encontrados",
      type: "error",
      text: '{!! $erros !!}',
      confirmButtonClass: 'btn-danger',
      //closeOnCancel: false
      html: true
    });
  }
  </script>
  @endif
</body>
</html>
