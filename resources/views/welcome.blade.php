
<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Banco BMG</title>

        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
        <link rel="stylesheet" href="../assets/css/template.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    </head>
    <style>
            body {
                margin: 0;
                font-family: lato,sans-serif;
                font-size: 14px;
                line-height: 1.42857143;
                color: #333;
                background-color: #fff;
            }
            .fundo{
                display: flex;
                min-height: 224vh;
                height: 224vh;
                width: 100%;
                align-items: center;
                position: relative;
            }
            .info-direita {
                background: linear-gradient(to right,#fa6300,#fa6300);
                min-height: 100vh;
                height: 100%;
                width: 45%;
                float:left;
            }
            .detalhes{
                padding-left: 65px;
                position: absolute;
                top: 13%;
                color: #fff;
                width:44%;
            }
            .detalhes p{
                font-family: sans-serif;
                font-weight: 700;
                font-size: 35px;
                line-height: 1.25;
                margin-bottom: 20px;
            }
            .detalhes label{
                font-size: 21px;
                font-weight: 400;
                line-height: 1.25;
                letter-spacing:.3px;
                font-family: sans-serif;
            }
            .info-esquerda{
                background-color: #f4f5f6;
                min-height: 100vh;
                height: 100%;
                width: 50%;
                float:left;
            }
            .logo {
                width: auto;
                height: 85px;
                margin-bottom: 20px;
            }
            .info-esquerda h3 {
                margin: 30px 0;
                font-size: 43px;
                font-weight: 700;
                color: #3f3e3e;
                font-family: sans-serif;
                line-height: 1.1;
            }
            .info-esquerda p{
                font-size: 16px;
                font-weight: 400;
                color: #9ca2ab;
                margin-bottom: 20px;
            }
            .info-esquerda label{
                font-size: 11px;
                text-align: center;
                width: 100%;
            }
            .btn_laranja{
                background-image: linear-gradient(104deg,#fa6300,#fa6300);
                border: 0px;
                font-weight: 700;
                margin: 26px 1px 26px 1px;
                border-radius: 35px;
            }
            .input_margem {
                border-radius: 35px;
            }
            .informacoes_complementar{
                margin-right: 0 !important;
                margin-left: 0 !important;
            }
            .texto_info{
                font-size: 11px;
                text-align: justify;
                color: #9ca2ab;
            }
        </style>    
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.0/jquery.mask.js"></script>
        <script>
            $(document).ready(function () { 
                var $seuCampoCpf = $("#cpf_inicio");
                $seuCampoCpf.mask('000.000.000-00', {reverse: true});

                var $seuCampoCpf = $("#telefone");
                $seuCampoCpf.mask('(00) 00000-0000', {reverse: true});

                $('.dados_complementares').css('display','none');
                $( "#btn_continuar_dados" ).click(function() {
                    $('.dados_complementares').css('display','block');
                });
            });
        </script>
    <body>
        <div class="fundo">
            <div class="info-direita">
                <div class="detalhes">
                    <img class="logo" src="https://contadigital.kontaazul.co/img/LOGO_BMG3.svg">
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
                                <input required="" id="cpf_inicio" name="nome" maxlength="14" class="form-control form-rounded input_margem" placeholder="Nome">
                                <br>
                                <input required="" id="telefone" name="telefone" maxlength="14" class="form-control form-rounded input_margem" placeholder="Telefone">
                                <br>
                                <input required="" id="email" name="email" maxlength="14" class="form-control form-rounded input_margem" placeholder="E-mail">
                            </div>
                            <button type="button" class="btn btn-primary btn-lg btn-block btn_laranja" id="btn_continuar_dados">Continuar</button>
                            <label><img src="https://contadigital.kontaazul.co/img/ambiente-seguro.svg"> Ambiente seguro e confidencial </label>
                        </form>
                        </div>
                        <div class="col-sm-2"></div>
                    </div>
                    <div class="row informacoes_complementar">
                    <br><br><br><br>
                        <div class="col-sm-3"></div>
                        <div class="col-sm-3">
                            <img src="https://contadigital.kontaazul.co/img/logo_ka_verde.png" style="margin-bottom: 10px; opacity: 1;" width="140">
                        </div>
                        <div class="col-sm-3">
                            <img src="https://contadigital.kontaazul.co/img/selo2.png" style="margin-bottom: 10px; opacity: 1;" width="140">
                        </div>
                    </div>
                    <div class="row informacoes_complementar">
                        <div class="col-sm-2"></div>
                        <div class="col-sm-8 texto_info">
                            BANCO BMG S.A. é instituição financeira autorizada pelo Banco Central do Brasil. O mero envio do cadastro não implica a abertura da conta eletrônica e a contratação de produtos e serviços oferecidos. A rentabilidade obtida no passado não representa garantia de rentabilidade futura. Os serviços disponíveis na plataforma são facultativos e dependem de prévia concordância do cliente em ambiente eletrônico. Sobre determinadas operações incidirá IOF, conforme previsto na legislação vigente. Consulte o valor mínimo, remuneração, prazos, tributação e demais regras aplicáveis aos produtos em www.bancobmg.com.br. Condições sujeitas a alteraçõso sem aviso prévio. Esta instituição é aderente ao código ANBIMA de regulação e melhores práticas para atividade de distribuição de produtos de investimento no varejo II. SAC: 0800 9799 099. Deficientes auditivos ou de fala: 0800 9797 333. Ouvidoria: 0800 723 2044.
                            <br><br>               
                            Somos uma plataforma digital que atua como correspondente Bancário e Sociedade de Crédito Direto para facilitar o processo de contratação de empréstimos. Como Correspondente Bancário, seguimos as diretrizes da Resolução nº 3.954 e como Sociedade de Crédito Direto a resolução nº 4.656, ambas do Banco Central do Brasil. A DF Baccan Serviços atua como correspondente bancário do Banco BMG S/A CNPJ 61.186.680/0001-74. Maiores detalhes consulte o 
                            <a href="https://contadigital.kontaazul.co/Termo_de_Uso_e_Politica_de_privacidade.pdf" download="" style="color: #f28502; text-decoration: underline;">Termo de Uso e Política de Privacidade.</a>
                        </div>
                    </div>
            </div>
        </div>
    </body>
</html>