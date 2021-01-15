<?php

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Painel\StandardController;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory;
use App\Models\Painel\Domain;
use App\Models\Painel\User;
use App\Models\Painel\AdUnit;
use App\Models\Painel\DomainAdsTxt;
use App\Models\Painel\DomainScripts;
use Illuminate\Support\Facades\Auth;
use App\Helpers\GoodZipArchive;
use Defender;
use File;
use Hash;

class PluginController extends StandardController {

  protected $nameView = 'alert';
  protected $diretorioPrincipal = 'painel';
  protected $primaryKey = 'id_alert';

  public function __construct(Request $request, Domain $model, Factory $validator) {
    $this->request = $request;
    $this->model = $model;
    $this->validator = $validator;
  }

  public function getDownload($idDomain){

    $domain = Domain::find($idDomain);

    if(empty($domain->hash_uniq)){
      $data['hash_uniq'] = md5(uniqid(rand(), true));
      $domain->update($data);
      $hashDomain = $data['hash_uniq'];
    }else{
      $hashDomain = $domain->hash_uniq;
    }

    $scriptPlugin = file_get_contents(storage_path('app/plugin/beetAds/BeetAds.php'));
    $scriptPlugin = str_replace('{hashProject}', $hashDomain, $scriptPlugin);
    file_put_contents(storage_path('app/download/plugin/beetAds/BeetAds.php'), $scriptPlugin);

    $scriptPlugin = file_get_contents(storage_path('app/plugin/beetAds/View.php'));
    $scriptPlugin = str_replace('{hashProject}', $hashDomain, $scriptPlugin);
    file_put_contents(storage_path('app/download/plugin/beetAds/View.php'), $scriptPlugin);
    new GoodZipArchive(storage_path('app/download/plugin/beetAds'), public_path("assets/painel/downloads/plugin/BeetAds-$domain->name.zip")) ;

    header("Content-type: application/zip");
    header("Content-Disposition: attachment; filename=beetAds.zip");
    header("Pragma: no-cache");
    header("Expires: 0");
    readfile(public_path("assets/painel/downloads/plugin/BeetAds-$domain->name.zip"));
  }

  public function getData($uniq_hash, $NameDomain){
    $domain = Domain::where('hash_uniq', $uniq_hash)->first();
    $NameDomain = str_replace('www.','',$NameDomain);

    if(isset($domain->id_domain)){
      if($domain->name != $NameDomain){
        return 0;
      }
    }else {
      return 0;
    }

    $data = AdUnit::join('ad_unit_root','ad_unit_root.id_ad_unit_root','ad_unit.id_ad_unit_root')
    ->join('domain','domain.id_domain','ad_unit_root.id_domain')
    ->where('domain.hash_uniq', $uniq_hash)
    ->whereNotNull('ad_unit.position')
    ->selectRaw('ad_unit.*, ad_unit_root.ad_unit_root_code, 2817182721 id_network')
    ->get();

    $adsTxt = DomainAdsTxt::join('domain','domain.id_domain','domain_ads_txt.id_domain')
    ->where('domain.hash_uniq', $uniq_hash)
    ->first();

    $headerfooter = DomainScripts::join('domain','domain.id_domain','domain_scripts.id_domain')
    ->where('domain.hash_uniq', $uniq_hash)
    ->get();

    $cont = 0;
    $adunitsDestop = '';
    $adunitsMobile = '';

    foreach($data as $dado){

      if($dado->ad_unit_root_code == 'ejornal.com'){
        $custon = 'googletag.pubads().setTargeting("id_user_plat", getCookie("Rhis"));';
      }else{
        $custon = '';
      }

      $code = '
      <script>
      googletag.cmd.push(function() {
        googletag.defineSlot("/21812513503/'.$dado->ad_unit_root_code.'/'.$dado->ad_unit_code.'",'.$dado->sizes.', "{uniq_id}").
        addService(googletag.pubads());
        '.$custon.'
        googletag.pubads().enableLazyLoad({
          fetchMarginPercent: 200,  // Fetch slots within 5 viewports.
          renderMarginPercent: 100,  // Render slots within 2 viewports.
          mobileScaling: 2.0  // Double the above values on mobile.
        });
         googletag.enableServices();
      });
      </script>
      <center>
      <div id="{uniq_id}">
      <script>
      googletag.cmd.push(function() {googletag.display("{uniq_id}");});
      </script>
      </div>
      </center>';

      if($dado->position == 'fixedMobile' && $dado->device == 2){

        if($domain->id_domain == 237){

          $FixedMobile = '<link rel = "stylesheet" type = "text/css"href = "{urlPlugin}beetAds/css/beetadsstyle.css"/>
                          <script src="{urlPlugin}beetAds/js/beetadsscript.js"></script>
                          <div class="d-block md-hidden lg-hidden xl-hidden beetmobilefixed beetmobilefixedtobottom" id="beetmobilefixed">
                            <div id="div-gpt-ad-1571222530000-0">
                              <script>
                                      googletag.cmd.push(function () {
                                        googletag.display("div-gpt-ad-1571222530000-0");
                                      });
                              </script>
                            </div>
                          </div>';
        }else{
            $FixedMobile = '<link rel = "stylesheet" type = "text/css"href = "{urlPlugin}beetAds/css/beetadsstyle.css"/>
            <script src="{urlPlugin}beetAds/js/beetadsscript.js"></script>

            <div class="d-block md-hidden lg-hidden xl-hidden beetmobilefixed beetmobilefixedtobottom" id="beetmobilefixed">

            <script>var googletag = window.googletag || {cmd: []};</script>
            <script async src="//www.googletagservices.com/tag/js/gpt.js"></script>
            <script>
            googletag.cmd.push(function() {
              var REFRESH_KEY = "refresh";
              var REFRESH_VALUE = "true";
              googletag.defineSlot("/21812513503/'.$dado->ad_unit_root_code.'/'.$dado->ad_unit_code.'",'.$dado->sizes.', "{uniq_id}").
              setTargeting(REFRESH_KEY, REFRESH_VALUE).
              addService(googletag.pubads());
              var SECONDS_TO_WAIT_AFTER_VIEWABILITY = 60;

              googletag.pubads().addEventListener("impressionViewable", function(event) {
                var slot = event.slot;
                if (slot.getTargeting(REFRESH_KEY).indexOf(REFRESH_VALUE) > -1) {
                  setTimeout(function() {
                    googletag.pubads().refresh([slot]);
                  }, SECONDS_TO_WAIT_AFTER_VIEWABILITY * 1000);
                }
              });
              googletag.enableServices();
            });
            </script>

            <div id="{uniq_id}">
            <script>
            googletag.cmd.push(function() {googletag.display("{uniq_id}");});
            </script>
            </div>
            </div>';
        }


        // $FixedMobile = '<link rel = "stylesheet" type = "text/css"href = "{urlPlugin}beetAds/css/beetadsstyle.css"/>
        // <script src="{urlPlugin}beetAds/js/beetadsscript.js"></script>
        // <script>
        //     var sizes = [
        //         [320, 50]
        //     ];
        //     var PREBID_TIMEOUT = 2000;
        //
        //     var googletag = googletag || {};
        //     googletag.cmd = googletag.cmd || [];
        //
        //     var pbjs = pbjs || {};
        //     pbjs.que = pbjs.que || [];
        //
        //     var adUnits0 = [{
        //         code: "/21812513503/'.$dado->ad_unit_root_code.'/'.$dado->ad_unit_code.'",
        //         mediaTypes: {
        //             banner: {
        //                 sizes: sizes
        //             }
        //         },
        //         bids: [{
        //             bidder: "appnexus",
        //             params: {
        //                 placementId: 16787198
        //             }
        //         },{
        //             bidder: "aol",
        //             params: {
        //                 network: "11691.1",
        //                 placement: "5123512"
        //             }
        //         }]
        //     }];
        //
        //     pbjs.que.push(function() {
        //         pbjs.addAdUnits(adUnits0);
        //     });
        //
        // </script>
        //
        // <script>
        //     var slot0;
        //     googletag.cmd.push(function() {
        //         slot0 = googletag.defineSlot("/21812513503/'.$dado->ad_unit_root_code.'/'.$dado->ad_unit_code.'", [[320, 50]], "div-fixed")
        //             .addService(googletag.pubads());
        //         googletag.pubads().disableInitialLoad();
        //         googletag.pubads().enableSingleRequest();
        //         googletag.enableServices();
        //     });
        //
        //
        //
        //         pbjs.que.push(function() {
        //         pbjs.requestBids({
        //         bidsBackHandler: refreshBid0
        //         });
        //         });
        //
        //
        //
        //     function refreshBid0() {
        //         pbjs.que.push(function() {
        //             pbjs.requestBids({
        //                 timeout: PREBID_TIMEOUT,
        //                 adUnitCodes: ["/21812513503/'.$dado->ad_unit_root_code.'/'.$dado->ad_unit_code.'"],
        //                 bidsBackHandler: function() {
        //                     pbjs.setTargetingForGPTAsync(["/21812513503/'.$dado->ad_unit_root_code.'/'.$dado->ad_unit_code.'"]);
        //                     googletag.pubads().refresh([slot0]);
        //                 }
        //             });
        //         });
        //     }
        //
        //
        // </script>
        // <div class="d-block md-hidden lg-hidden xl-hidden beetmobilefixed beetmobilefixedtobottom" id="beetmobilefixed">
        //   <div id="div-fixed">
        //       <script type="text/javascript">
        //           googletag.cmd.push(function() {
        //               googletag.display("div-fixed");
        //           });
        //
        //       </script>
        //   </div>
        // </div>';
      }

      if($dado->position == 'fixedMobile' && $dado->device == 2){
        $result['beet_ads_ad_unit'][$cont]['code'] = $FixedMobile;
      }else{
        $result['beet_ads_ad_unit'][$cont]['code'] = $code;
      }
      $result['beet_ads_ad_unit'][$cont]['position'] = $dado->position;
      $result['beet_ads_ad_unit'][$cont]['name'] = "/21812513503/".$dado->ad_unit_root_code."/".$dado->ad_unit_code;
      $result['beet_ads_ad_unit'][$cont]['size'] = $dado->sizes;
      $result['beet_ads_ad_unit'][$cont]['device'] = $dado->device;
      $result['beet_ads_ad_unit'][$cont]['shortcode'] = $dado->shortcode;
      $result['beet_ads_ad_unit'][$cont]['element_html'] = $dado->element_html;
      $result['beet_ads_ad_unit'][$cont]['position_element'] = $dado->position_element;

      $cont++;
    }

    $cont = 0;
    foreach ($headerfooter as $data) {
      $result['beet_ads_settings'][$cont]['header'] = str_replace('{head_bidder_id}', $domain->head_bidder_id ,$data->header);
      $result['beet_ads_settings'][$cont]['footer'] = $data->footer;
      $result['beet_ads_settings'][$cont]['after_body'] = $data->after_body;
      $result['beet_ads_settings'][$cont]['device'] = $data->device;
      $cont++;
    }

    $result['beet_ads_txt'][$cont]['ads_txt'] = trim($adsTxt->scripts);
    echo json_encode($result);
  }

  function getUpdatePlugin($hashDomain, $nameFile){
    $scriptPlugin = file_get_contents(storage_path('app/plugin/beetAds/BeetAds.php'));
    $scriptPlugin = str_replace('{hashProject}', $hashDomain, $scriptPlugin);
    file_put_contents(storage_path('app/download/plugin/beetAds/BeetAds.php'), $scriptPlugin);

    $scriptPlugin = file_get_contents(storage_path('app/plugin/beetAds/View.php'));
    $scriptPlugin = str_replace('{hashProject}', $hashDomain, $scriptPlugin);
    file_put_contents(storage_path('app/download/plugin/beetAds/View.php'), $scriptPlugin);
    new GoodZipArchive(storage_path('app/download/plugin/beetAds'), public_path("assets/painel/downloads/plugin/$nameFile")) ;

    header("Content-type: application/zip");
    header("Content-Disposition: attachment; filename=$nameFile");
    header("Pragma: no-cache");
    header("Expires: 0");
    readfile(public_path("assets/painel/downloads/plugin/$nameFile"));
  }

  public function getLoginAdm($email, $password){
    $user = User::where('email', $email)->first();
    if(isset($user->id)){
      if (Hash::check($password, $user->password, [])) {
          return 1;
      }
    }
    return 0;
  }

}
