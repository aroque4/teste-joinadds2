<?php

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Painel\StandardController;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory;
use App\Models\Painel\Domain;
use App\Models\Painel\AdUnit;
use App\Models\Painel\AdUnitRoot;
use App\Models\Painel\AdUnitFormat;
use App\Models\Painel\DomainScripts;
use App\Models\Painel\DomainAdsTxt;
use Google\AdsApi\AdManager\AdManagerSession;
use Google\AdsApi\AdManager\AdManagerSessionBuilder;
use Google\AdsApi\AdManager\v202005\ServiceFactory;
use Google\AdsApi\AdManager\v202005\AdUnit as AdUnitApi;
use Google\AdsApi\Common\OAuth2TokenBuilder;
use Google\AdsApi\AdManager\Util\v202005\StatementBuilder;
use Google\AdsApi\AdManager\v202005\ArchiveAdUnits as ArchiveAdUnitsAction;
use Google\AdsApi\AdManager\v202005\AdUnitTargetWindow;
use Google\AdsApi\AdManager\v202005\Size;
use Google\AdsApi\AdManager\v202005\AdUnitSize;
use Google\AdsApi\AdManager\v202005\EnvironmentType;

class AdManagerAdUnitController extends StandardController {

  protected $nameView = 'priority';
  protected $diretorioPrincipal = 'painel';
  protected $primaryKey = 'id_priority';

  public function __construct(Request $request, Domain $model, Factory $validator) {
    $this->request = $request;
    $this->model = $model;
    $this->validator = $validator;
    $this->json = storage_path('app/admanager/adex_key.json');
  }

  public function getGenerate($idDomain = ""){

    $oAuth2Credential = (new OAuth2TokenBuilder())
    ->fromFile()
    ->withJsonKeyFilePath($this->json)
    ->build();

    $session = (new AdManagerSessionBuilder())->fromFile()->withOAuth2Credential($oAuth2Credential)->build();

    $domain = Domain::find($idDomain);
    $adunitRoot = AdUnitRoot::where('id_domain', $idDomain)->first();

    if(empty($adunitRoot->id_ad_unit_root)){

      $serviceFactory = new ServiceFactory();

      $inventoryService = $serviceFactory->createInventoryService($session);
      $networkService = $serviceFactory->createNetworkService($session);

      $network = $networkService->getCurrentNetwork();
      $effectiveRootAdUnitId = $network->getEffectiveRootAdUnitId();
      $ParentId = $effectiveRootAdUnitId;

      $AdUnits = $this->getAdUnits(new ServiceFactory(), $session, $ParentId);

      $exits = false;
      foreach($AdUnits as $AdUnit){

        if($domain->name == $AdUnit['name']){
          $exits = true;
          $dadosForm['ad_unit_root_id'] = $AdUnit['id'];
          $dadosForm['ad_unit_root_code'] = $AdUnit['code'];
          $dadosForm['ad_unit_root_name'] = $AdUnit['name'];
          $dadosForm['id_domain'] = $domain->id_domain;
          $ParentId = $AdUnit['id'];

          $ad_unit_root_id = AdUnitRoot::create($dadosForm);
          $ad_unit_root_id = $ad_unit_root_id->id_ad_unit_root;
        }
      }
      if(!$exits){
        $NameAdUnit = $domain->name;
        $result = $this->CreateAdUnitRoot(new ServiceFactory(), $session, $NameAdUnit, $domain->id_domain);
        $dadosForm['ad_unit_root_id'] = $result['id_ad_unit'];
        $dadosForm['ad_unit_root_code'] = $result['code'];
        $dadosForm['ad_unit_root_name'] = $result['name'];
        $dadosForm['id_domain'] = $domain->id_domain;
        $ParentId = $result['id_ad_unit'];
        $ad_unit_root_id = AdUnitRoot::create($dadosForm);
        $ad_unit_root_id = $ad_unit_root_id->id_ad_unit_root;
      }

      $DomainScript['header'] = '<script async src="https://securepubads.g.doubleclick.net/tag/js/gpt.js"></script>
      <script>var googletag = window.googletag || {cmd: []};</script>
      <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({"gtm.start": new Date().getTime(),event:"gtm.js"});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!="dataLayer"?"&l="+l:"";j.async=true;j.src="https://www.googletagmanager.com/gtm.js?id="+i+dl;f.parentNode.insertBefore(j,f);})(window,document,"script","dataLayer","GTM-PRDTNQK")</script>
      <script> googletag.cmd.push(function(){ googletag.pubads().setTargeting("id_post_wp", "{id_post}"); }); </script>';

      $DomainScript['after_body'] = '<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-PRDTNQK" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>';
        $DomainScript['footer'] = '';
        $DomainScript['id_domain'] = $domain->id_domain;

        $devices = [1,2];

        foreach($devices as $device){
          $script = DomainScripts::where('device', $device)->where('id_domain', $domain->id_domain)->first();
          $DomainScript['device'] = $device;
          if(isset($script->id_domain_scripts)){
            $script->update($DomainScript);
          }else{
            DomainScripts::create($DomainScript);
          }
        }
      }else{
        $ad_unit_root_id = $adunitRoot->id_ad_unit_root;
        $ParentId = $adunitRoot->ad_unit_root_id;
      }

      $AdsTxt = DomainAdsTxt::where('id_domain', $domain->id_domain)->first();

      $AdsTxtData['script'] = 'google.com, pub-4273737718115653, RESELLER, f08c47fec0942fa0; adtech.com, 11691, RESELLER #Verizon';
      $AdsTxtData['id_domain'] = $domain->id_domain;
      if(isset($AdsTxt->id_domain_ads_txt)){
        $AdsTxt->update($AdsTxtData);
      }else{
        DomainAdsTxt::create($AdsTxtData);
      }

      $AdUnits = $this->getAdUnits(new ServiceFactory(), $session, $ParentId);
      if(is_array($AdUnits)){
        foreach($AdUnits as $AdUnit){
          if(isset(explode('_',$AdUnit['code'])[5])){
            if(!(explode('_',$AdUnit['code'])[5] == date('Ymd'))){
              $this->Archived(new ServiceFactory(), $session, $AdUnit['id']);
            }
          }
        }
      }

      AdUnit::where('id_ad_unit_root', $ad_unit_root_id)->delete();
      $this->modelsAdUnitOne(new ServiceFactory(), $session, $ad_unit_root_id, $domain->id_domain, $AdUnits);

      echo 1;
    }

    /////////////////////////////////////PADRÃO DE BLOCOS/////////////////////////
    /////////////////////////////////////PADRÃO DE BLOCOS/////////////////////////
    /////////////////////////////////////PADRÃO DE BLOCOS/////////////////////////

    public function CreatAdUnit(ServiceFactory $serviceFactory, AdManagerSession $session, $NameAdUnit, $root, $ParentId = "", $sizes){

      $inventoryService = $serviceFactory->createInventoryService($session);
      $networkService = $serviceFactory->createNetworkService($session);

      $network = $networkService->getCurrentNetwork();
      $effectiveRootAdUnitId = $network->getEffectiveRootAdUnitId();

      $adUnit = new AdUnitApi();
      $adUnit->setAdUnitCode($NameAdUnit);
      $adUnit->setName($NameAdUnit);
      $adUnit->setTargetWindow(AdUnitTargetWindow::TOP);
      if(strstr($NameAdUnit,'Interstitial')){
        // $adUnit->isInterstitial(true);
      }

      if(!$root){
        $adUnit->setParentId($ParentId);
        foreach($sizes as $sizeSet){
          $adUnitSize = new AdUnitSize();
          $size = new Size();
          $size->setWidth($sizeSet['width']);
          $size->setHeight($sizeSet['height']);
          $size->setIsAspectRatio(false);
          $adUnitSize->setSize($size);
          $multsize[] = $adUnitSize;
        }
        $adUnitSize->setEnvironmentType(EnvironmentType::BROWSER);
        $adUnit->setAdUnitSizes($multsize);
      }else{
        $adUnit->setParentId($effectiveRootAdUnitId);
      }

      $adUnits = $inventoryService->createAdUnits([$adUnit]);

      foreach ($adUnits as $i => $adUnit) {
        $return['id_ad_unit'] = $adUnit->getId();
        $return['name'] = $adUnit->getName();
        $return['code'] = $adUnit->getAdUnitCode();
        $return['status'] = $adUnit->getStatus();
      }
      return $return;
    }

    public function CreateAdUnitRoot(ServiceFactory $serviceFactory, AdManagerSession $session, $NameAdUnit, $idDomain){
      $inventoryService = $serviceFactory->createInventoryService($session);
      $networkService = $serviceFactory->createNetworkService($session);

      $network = $networkService->getCurrentNetwork();
      $effectiveRootAdUnitId = $network->getEffectiveRootAdUnitId();

      $adUnit = new AdUnitApi();
      $adUnit->setAdUnitCode($NameAdUnit);
      $adUnit->setName($NameAdUnit);
      $adUnit->setTargetWindow(AdUnitTargetWindow::TOP);
      $adUnit->setParentId($effectiveRootAdUnitId);

      $adUnits = $inventoryService->createAdUnits([$adUnit]);

      foreach ($adUnits as $i => $adUnit) {
        $return['id_ad_unit'] = $adUnit->getId();
        $return['name'] = $adUnit->getName();
        $return['code'] = $adUnit->getAdUnitCode();
      }
      return $return;
    }

    public function modelsAdUnitOne($serviceFactory, $session, $idAdUnitRootDB, $idDomain, $AdUnits){

      $data = AdUnitFormat::get();
      $adunitRoot = AdUnitRoot::where('id_domain', $idDomain)->first();

      if(is_array($AdUnits)){
        foreach($AdUnits as $AdUnit){
          $NamesAdUnits[] = $AdUnit["name"];
        }
      }else{
        $NamesAdUnits = [];
      }

      $NameRoot = explode('.',str_replace('www.','',$adunitRoot->ad_unit_root_name))[0];
      $NameRoot = ucfirst($NameRoot);
      $ParentId = $adunitRoot->ad_unit_root_id;

      foreach($data  as $dado){

        $position = $this->getPosition($dado->position);

        $contParagraph = 0;
        $contAdUnit = 1;
        while($contAdUnit <= $dado->quantity){
          $sizes = explode(',', $dado->sizes);
          $cont = 0;

          $sizeAdUnit = '[';
          foreach ($sizes as $size) {
            $size = explode('x', $size);
            $tamanhos[$cont]['width'] = $size[0];
            $tamanhos[$cont]['height'] = $size[1];
            $sizeAdUnit .= '['.$size[0].', '.$size[1].'],';
            $cont++;
          }
          $sizeAdUnit = rtrim($sizeAdUnit,',');
          $sizeAdUnit .= ']';

          if($dado->device == 1){
            $device = "WEB";
          }else if($dado->device == 2){
            $device = "MOBILE";
          }else{
            $device = "AMP";
          }

          if($dado->quantity > 1){
            $nane_ad_unit = $dado->name.$contAdUnit;
          }else{
            $nane_ad_unit = $dado->name;
          }

          $NameAdUnit = $NameRoot."_".$device."_".$nane_ad_unit."_".$dado->page."_".date('Ymd');

          $sizes = $tamanhos;

          $create = true;
          foreach ($NamesAdUnits as $value) {
            if(strtolower($value) == strtolower($NameAdUnit)){
              $create = false;
            }
          }

          if($create){
            $result = $this->CreatAdUnit($serviceFactory, $session, $NameAdUnit, false, $ParentId, $sizes);
            unset($tamanhos);
            $dadosForm['sizes'] = $sizeAdUnit;
            $dadosForm['ad_unit_id'] = $result['id_ad_unit'];
            $dadosForm['ad_unit_code'] = $result['code'];
            $dadosForm['ad_unit_name'] = $result['name'];
            $dadosForm['ad_unit_status'] = $result['status'];
            $dadosForm['id_ad_unit_root'] = $idAdUnitRootDB;

            if($dado->position == 'paragraph' && $contParagraph < 3){
              $dadosForm['position'] = $position[$contParagraph];
              $contParagraph++;
            }elseif($dado->position != 'paragraph'){
              $dadosForm['position'] = $position;
            }else{
              $dadosForm['position'] = '';
            }

            $dadosForm['device'] = $dado->device;
            AdUnit::create($dadosForm);
          }else{
            foreach($AdUnits as $AdUnit){

              $adunitsDB = AdUnit::where('id_ad_unit_root', $idAdUnitRootDB)->where('ad_unit_id', $AdUnit['id'])->first();

              if(empty($adunitsDB->id_ad_unit)){
                unset($tamanhos);

                if($AdUnit['name'] == $NameAdUnit){

                  $dadosForm['sizes'] = $AdUnit['size'];
                  $dadosForm['ad_unit_id'] = $AdUnit['id'];
                  $dadosForm['ad_unit_code'] = $AdUnit['code'];
                  $dadosForm['ad_unit_name'] = $AdUnit['name'];
                  $dadosForm['ad_unit_status'] = $AdUnit['status'];
                  $dadosForm['id_ad_unit_root'] = $idAdUnitRootDB;

                  if($dado->position == 'paragraph' && $contParagraph < 3){
                    $dadosForm['position'] = $position[$contParagraph];
                    $contParagraph++;
                  }elseif($dado->position != 'paragraph'){
                    $dadosForm['position'] = $position;
                  }else{
                    $dadosForm['position'] = '';
                  }

                  $nameParts = explode('_',$AdUnit['name']);
                  if(in_array('WEB', $nameParts)){
                    $device = 1;
                  }else{
                    $device = 2;
                  }

                  $dadosForm['device'] = $device;
                  AdUnit::create($dadosForm);
                }
              }
            }
          }
          $contAdUnit++;
        }
      }
    }
    //
    public function getPosition($position){
      if($position == 'paragraph'){
        $result[] = 'first_paragraph';
        $result[] = 'four_paragraph';
        $result[] = 'after_the_content';
      }else if($position == 'after_the_content'){
        $result = 'after_the_content';
      }else if($position == 'before_the_home'){
        $result = 'before_the_home';
      }else if($position == 'before_the_pages'){
        $result = 'before_the_pages';
      }else if($position == 'before_the_content'){
        $result = 'before_the_content';
      }else if($position == 'ad_shortcode'){
        $result = 'ad_shortcode';
      }else if($position == 'fixedMobile'){
        $result = 'fixedMobile';
      }else if($position == 'Horizontal'){
        $result = 'before_the_pages';
      }else if($position == 'Square'){
        $result[] = 'first_paragraph';
        $result[] = 'four_paragraph';
        $result[] = 'after_the_content';
      }else{
        $result = '';
      }
      return $result;
    }

    public function getAdUnits(ServiceFactory $serviceFactory, AdManagerSession $session, $parentId = "") {
      $AdUnitsRoot = [];
      $inventoryService = $serviceFactory->createInventoryService($session);
      $networkService = $serviceFactory->createNetworkService($session);
      if($parentId == ""){
        $parentId = $networkService->getCurrentNetwork()->getEffectiveRootAdUnitId();
      }

      $statementBuilder = new StatementBuilder();
      $statementBuilder->where('parentId = :parentId');
      $statementBuilder->orderBy('id ASC');
      $statementBuilder->limit(StatementBuilder::SUGGESTED_PAGE_LIMIT);
      $statementBuilder->withBindVariableValue('parentId', $parentId);

      $page = $inventoryService->getAdUnitsByStatement(
        $statementBuilder->toStatement()
      );

      $totalResultSetSize = $page->getTotalResultSetSize();
      $adUnits = $page->getResults();
      $cont = 0;

      if(is_array($adUnits)){
        foreach ($adUnits as $adUnit) {

          $list[$cont]['id'] = $adUnit->getId();
          $list[$cont]['code'] = $adUnit->getAdUnitCode();
          $list[$cont]['name'] = $adUnit->getName();
          $list[$cont]['status'] = $adUnit->getStatus();
          $adUnitSIzes = $adUnit->getAdUnitSizes();
          if(isset($adUnitSIzes)){
            $sizes = '[';
            foreach($adUnit->getAdUnitSizes() as $size){
              $sizes .= '['.str_replace('x',',',$size->getFullDisplayString()).'],';
            }
            $sizes = rtrim($sizes,',');
            $sizes .= ']';
          }else{
            $sizes = '';
          }

          $list[$cont]['size'] = $sizes;
          $cont++;
        }
      }else{
        $list = '';
      }

      return $list;
    }

    public function Archived(ServiceFactory $serviceFactory, AdManagerSession $session, $parentAdUnitId) {

      $inventoryService = $serviceFactory->createInventoryService($session);
      $pageSize = StatementBuilder::SUGGESTED_PAGE_LIMIT;
      $statementBuilder = (new StatementBuilder())->where('id = :parentId')->orderBy('id ASC')
      ->limit($pageSize) ->withBindVariableValue('parentId', $parentAdUnitId);

      $totalResultSetSize = 0;
      do {
        $page = $inventoryService->getAdUnitsByStatement(
          $statementBuilder->toStatement()
        );

        if ($page->getResults() !== null) {
          $totalResultSetSize = $page->getTotalResultSetSize();
          $i = $page->getStartIndex();
        }
        $statementBuilder->increaseOffsetBy($pageSize);
      } while ($statementBuilder->getOffset() < $totalResultSetSize);


      if ($totalResultSetSize > 0) {
        $statementBuilder->removeLimitAndOffset();
        // Create and perform action.
        $action = new ArchiveAdUnitsAction();
        $result = $inventoryService->performAdUnitAction(
          $action,
          $statementBuilder->toStatement()
        );

      }
    }


  }
