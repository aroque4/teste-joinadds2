
<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Banco BMG</title>

        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
        <link rel="stylesheet" href="{{URL('/assets/css/home')}}/template.css">
{{--        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>--}}

        <link rel="shortcut icon" href="{{URL('/assets/img/')}}/indice.png">
        <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700&amp;family=Montserrat:wght@300;400;600;700&amp;display=swap" rel="stylesheet">
    </head>
    <body>
        <div class="fundo">
            <div class="info-direita">
                <div class="detalhes">
                    <img class="logo" src="{{URL('/assets/img/')}}/LOGO_BMG3.svg">
                    <br class="clear">
                    <p>Conta digital grátis Bmg</p>
                    <label>Tem tudo o que você precisa e ainda te ajuda a guardar dinheiro.</label>
                </div>
            </div>
            <div class="info-esquerda">
                    <div class="row">
                        <div class="col-sm-2"></div>
                        <div class="col-sm-8">
                        <br><br><br><br>
                            <h3>Solicite sua conta e cartão!</h3>
                            <p>Para agilizar seu cadastro, tenha em mãos um documento de identificação e comprovante de endereço.</p>
                            <form id="info_cpf">
                            <input required="" id="cpf_inicio" name="cpf_inicio" maxlength="14" class="form-control form-rounded input_margem" placeholder="CPF">
                            <div class="dados_complementares">
                                <br>
                                <input required="" id="nome" name="nome" maxlength="14" class="form-control form-rounded input_margem" placeholder="Nome">
                                <br>
                                <input required="" id="telefone" name="telefone" maxlength="14" class="form-control form-rounded input_margem" placeholder="Telefone">
                                <br>
                                <input required="" id="email" name="email" maxlength="14" class="form-control form-rounded input_margem" placeholder="E-mail">
                            </div>
                            <button type="button" class="btn btn-primary btn-lg btn-block btn_laranja" id="btn_continuar_dados">Continuar</button>
                            <button type="button" class="btn btn-primary btn-lg btn-block btn_laranja" id="enviar_dados">Enviar</button>
                            <label><img src="{{URL('/assets/img/')}}/ambiente-seguro.svg"> Ambiente seguro e confidencial </label>
                        </form>
                        </div>
                        <div class="col-sm-2"></div>
                    </div>
                    <div class="row informacoes_complementar">
                    <br><br><br><br>
                        <div class="col-sm-3"></div>
                        <div class="col-sm-3">
                            <img src="{{URL('/assets/img/')}}/logo_ka_verde.png" style="margin-bottom: 10px; opacity: 1;" width="140">
                        </div>
                        <div class="col-sm-3">
                            <img src="{{URL('/assets/img/')}}/selo2.png" style="margin-bottom: 10px; opacity: 1;" width="140">
                        </div>
                    </div>
                    <div class="row informacoes_complementar">
                        <div class="col-sm-2"></div>
                        <div class="col-sm-8 texto_info">
                            BANCO BMG S.A. é instituição financeira autorizada pelo Banco Central do Brasil. O mero envio do cadastro não implica a abertura da conta eletrônica e a contratação de produtos e serviços oferecidos. A rentabilidade obtida no passado não representa garantia de rentabilidade futura. Os serviços disponíveis na plataforma são facultativos e dependem de prévia concordância do cliente em ambiente eletrônico. Sobre determinadas operações incidirá IOF, conforme previsto na legislação vigente. Consulte o valor mínimo, remuneração, prazos, tributação e demais regras aplicáveis aos produtos em www.bancobmg.com.br. Condições sujeitas a alteraçõso sem aviso prévio. Esta instituição é aderente ao código ANBIMA de regulação e melhores práticas para atividade de distribuição de produtos de investimento no varejo II. SAC: 0800 9799 099. Deficientes auditivos ou de fala: 0800 9797 333. Ouvidoria: 0800 723 2044.
                            <br><br>               
                            Somos uma plataforma digital que atua como correspondente Bancário e Sociedade de Crédito Direto para facilitar o processo de contratação de empréstimos. Como Correspondente Bancário, seguimos as diretrizes da Resolução nº 3.954 e como Sociedade de Crédito Direto a resolução nº 4.656, ambas do Banco Central do Brasil. A DF Baccan Serviços atua como correspondente bancário do Banco BMG S/A CNPJ 61.186.680/0001-74. Maiores detalhes consulte o 
                            <a href="{{URL('/assets/docs/')}}/Termo_de_Uso_e_Politica_de_privacidade.pdf" download="" style="color: #f28502; text-decoration: underline;">Termo de Uso e Política de Privacidade.</a>
                        </div>
                    </div>
            </div>
        </div>
    </body>
    <script src="{{URL('/assets/bibliotecas')}}/assets/js/jquery-3.5.1.min.js"></script>
    <script src="{{URL('/assets/bibliotecas')}}/jquery.mask.js"></script>
    <!-- Bootstrap js-->
    <script src="{{URL('/assets/bibliotecas')}}/assets/js/bootstrap/popper.min.js"></script>
    <script src="{{URL('/assets/bibliotecas')}}/assets/js/bootstrap/bootstrap.js"></script>
    <!-- feather icon js-->
    <script src="{{URL('/assets/bibliotecas')}}/assets/js/icons/feather-icon/feather.min.js"></script>
    <script src="{{URL('/assets/bibliotecas')}}/assets/js/icons/feather-icon/feather-icon.js"></script>
    <!-- Sidebar jquery-->
    <script src="{{URL('/assets/bibliotecas')}}/assets/js/sidebar-menu.js"></script>
    <script src="{{URL('/assets/bibliotecas')}}/assets/js/config.js"></script>
    <!-- Plugins JS start-->
    <script src="{{URL('/assets/bibliotecas')}}/assets/js/chart/chartist/chartist.js"></script>
    <script src="{{URL('/assets/bibliotecas')}}/assets/js/chart/chartist/chartist-plugin-tooltip.js"></script>
    <script src="{{URL('/assets/bibliotecas')}}/assets/js/chart/knob/knob.min.js"></script>
    <script src="{{URL('/assets/bibliotecas')}}/assets/js/chart/knob/knob-chart.js"></script>
    <script src="{{URL('/assets/bibliotecas')}}/assets/js/chart/apex-chart/apex-chart.js"></script>
    <script src="{{URL('/assets/bibliotecas')}}/assets/js/chart/apex-chart/stock-prices.js"></script>
    <script src="{{URL('/assets/bibliotecas')}}/assets/js/notify/bootstrap-notify.min.js"></script>
    <!-- <script src="{{URL('/assets/bibliotecas')}}/assets/js/dashboard/default.js"></script> -->
    <script src="{{URL('/assets/bibliotecas')}}/assets/js/notify/index.js"></script>
    <script src="{{URL('/assets/bibliotecas')}}/assets/js/datepicker/date-picker/datepicker.js"></script>
    <script src="{{URL('/assets/bibliotecas')}}/assets/js/datepicker/date-picker/datepicker.en.js"></script>
    <script src="{{URL('/assets/bibliotecas')}}/assets/js/datepicker/date-picker/datepicker.custom.js"></script>s

    <script src="{{URL('/assets/bibliotecas')}}/assets/js/datatable/datatables/jquery.dataTables.min.js"></script>
    <script src="{{URL('/assets/bibliotecas')}}/assets/js/datatable/datatables/datatable.custom.js"></script>

    <script src="{{URL('/assets/bibliotecas')}}/assets/js/select2/select2.full.min.js"></script>
    <script src="{{URL('/assets/bibliotecas')}}/assets/js/select2/select2-custom.js"></script>

    <script src="{{URL('/assets/js')}}/home/home.js"></script>
</html>