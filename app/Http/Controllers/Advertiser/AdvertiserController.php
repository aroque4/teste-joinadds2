<?php

namespace App\Http\Controllers\Advertiser;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory;
use App\Models\Painel\Advertiser;
use App\Models\Painel\AdvertiserToken;
use App\Models\Painel\AdmanagerAdvertiserReport;
use Illuminate\Support\Facades\Auth;
use Defender;
use File;

use Google\AdsApi\AdManager\AdManagerSession;
use Google\AdsApi\AdManager\AdManagerSessionBuilder;
use Google\AdsApi\AdManager\v201908\ServiceFactory;
use Google\AdsApi\Common\OAuth2TokenBuilder;
use Google\AdsApi\AdManager\Util\v201908\StatementBuilder;
use Google\AdsApi\AdManager\v201908\ReportQuery;
use Google\AdsApi\AdManager\Util\v201908\ReportDownloader;
use Google\AdsApi\AdManager\v201908\Column;
use Google\AdsApi\AdManager\Util\v201908\AdManagerDateTimes;
use Google\AdsApi\AdManager\v201908\DateRangeType;
use Google\AdsApi\AdManager\v201908\ReportQueryAdUnitView;
use Google\AdsApi\AdManager\v201908\Dimension;
use Google\AdsApi\AdManager\v201908\ExportFormat;
use Google\AdsApi\AdManager\v201908\ReportJob;
use DateTime;
use DateTimeZone;

use Google\AdsApi\AdManager\v201908\Order;
use Google\AdsApi\AdManager\v201908\InventoryTargeting;
use Google\AdsApi\AdManager\v201908\GeoTargeting;
use Google\AdsApi\AdManager\v201908\Location;
use Google\AdsApi\AdManager\v201908\UserDomainTargeting;
use Google\AdsApi\AdManager\v201908\DayOfWeek;
use Google\AdsApi\AdManager\v201908\DayPart;
use Google\AdsApi\AdManager\v201908\DayPartTargeting;
use Google\AdsApi\AdManager\v201908\TimeOfDay;
use Google\AdsApi\AdManager\v201908\MinuteOfHour;
use Google\AdsApi\AdManager\v201908\Technology;
use Google\AdsApi\AdManager\v201908\TechnologyTargeting;
use Google\AdsApi\AdManager\v201908\BrowserTargeting;
use Google\AdsApi\AdManager\v201908\Targeting;
use Google\AdsApi\AdManager\v201908\LineItem;
use Google\AdsApi\AdManager\v201908\LineItemType;
use Google\AdsApi\AdManager\v201908\CreativePlaceholder;
use Google\AdsApi\AdManager\v201908\Size;
use Google\AdsApi\AdManager\v201908\CreativeRotationType;
use Google\AdsApi\AdManager\v201908\StartDateTimeType;
use Google\AdsApi\AdManager\v201908\CostType;
use Google\AdsApi\AdManager\v201908\Money;
use Google\AdsApi\AdManager\v201908\Goal;
use Google\AdsApi\AdManager\v201908\GoalType;
use Google\AdsApi\AdManager\v201908\UnitType;
use Google\AdsApi\AdManager\v201908\ImageCreative;
use Google\AdsApi\AdManager\v201908\CreativeAsset;
use Google\AdsApi\AdManager\v201908\LineItemCreativeAssociation;
use Google\AdsApi\AdManager\v201908\ApproveOrders as ApproveOrdersAction;
use Google\AdsApi\AdManager\v201908\PauseOrders as PauseOrdersAction;
use Google\AdsApi\AdManager\v201908\ResumeOrders as ResumeOrdersAction;
use Google\AdsApi\AdManager\v201908\CustomCriteria;
use Google\AdsApi\AdManager\v201908\CustomCriteriaComparisonOperator;
use Google\AdsApi\AdManager\v201908\CustomCriteriaSet;
use Google\AdsApi\AdManager\v201908\CustomCriteriaSetLogicalOperator;

use Google\AdsApi\AdManager\v201908\StringCreativeTemplateVariableValue;
use Google\AdsApi\AdManager\v201908\TemplateCreative;
use Google\AdsApi\AdManager\v201908\AssetCreativeTemplateVariableValue;
use Google\AdsApi\AdManager\v201908\CreativeSizeType;
class AdvertiserController extends Controller {

  protected $nameView = 'alert';
  protected $diretorioPrincipal = 'painel';
  protected $primaryKey = 'id_alert';

  public function __construct(Request $request, Advertiser $model, Factory $validator) {
    $this->request = $request;
    $this->model = $model;
    $this->validator = $validator;
    $this->key = '9632103258dc4a7f8fa62642a1b845ce';
    $this->json = storage_path('app/admanager/beetads-3b6a72f41964.json');
  }

  public function postInfo($key){

    if($this->key == $key){
      $fields = ['token','title','image','url','total','description', 'cpc', 'start_date','end_date', 'type_campaign', 'advertiser_id_integration'];
      $dadosForm = $this->request->only(['token','title','total','image','url','description', 'cpc', 'start_date','end_date','type_campaign','advertiser_id_integration']);

      foreach($fields as $field){
        if(!array_key_exists($field, $dadosForm)){
          return "O campo $field, é obrigatório";
        }
      }

      $AdvertiserCheck = Advertiser::where('advertiser_id_integration', $dadosForm['advertiser_id_integration'])->first();
      $check = $this->checkToken($dadosForm['token']);

      if($check != 1){
        return $check;
      }

      $dadosForm['start_date'] = date('Y-m-d H:i:s', strtotime($dadosForm['start_date']));
      $dadosForm['end_date'] = date('Y-m-d H:i:s', strtotime($dadosForm['end_date']));

      if(isset($AdvertiserCheck->advertiser_id_integration)){
        $AdvertiserCheck->update($dadosForm);
        $Advertiser = $AdvertiserCheck->id_advertiser;
      }else{
        $Advertiser = Advertiser::create($dadosForm);
      }

      return $Advertiser->id_advertiser;

    }else{
      echo "Chave Inválida.";
    }
  }

  public function getGenerateToken($key){
    if($this->key == $key){
      $tokenSaved = AdvertiserToken::first();
      $dataForm['token'] = $this->createToken();
      $dataForm['expire'] = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s').'+ 1 hours'));

      if(isset($tokenSaved->token)){
        $tokenSaved->update($dataForm);
      }else{
        AdvertiserToken::create($dataForm);
      }
      return $dataForm['token'];

    }else{
      echo "Chave Inválida.";
    }
  }

  public function createToken(){
    return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0x0C2f ) | 0x4000, mt_rand( 0, 0x3fff ) | 0x8000, mt_rand( 0, 0x2Aff ), mt_rand( 0, 0xffD3 ), mt_rand( 0, 0xff4B ));
  }

  // PRODUÇÃO
  const ADVERTISER_ID = '4802916223';
  const SALESPERSON_ID = '245407560';
  const TRAFFICKER_ID = '245407560';
  const PLACEMENT_ID = '29624335';

  //HOMOLOGAÃO
  // const ADVERTISER_ID = '4775944295';
  // const SALESPERSON_ID = '245494347';
  // const TRAFFICKER_ID = '245494347';
  // const PLACEMENT_ID = '29536035';

  public function approve($idAdvertiser, $token){
    // Generate a refreshable OAuth2 credential for authentication.
    $check = $this->checkToken($token);

    if($check != 1){
      return $check;
    }

    $oAuth2Credential = (new OAuth2TokenBuilder())
    ->fromFile()
    ->withJsonKeyFilePath($this->json)
    ->build();

    $session = (new AdManagerSessionBuilder())->fromFile()
    ->withOAuth2Credential($oAuth2Credential)
    ->build();
    $Advertiser = $this->model->find($idAdvertiser);
    $idOrder = $this->createOrder(new ServiceFactory(),$session, intval(self::ADVERTISER_ID),intval(self::SALESPERSON_ID), intval(self::TRAFFICKER_ID), $Advertiser);
    $Advertiser->update(['order_id' => $idOrder]);

  }

  public function createOrder(ServiceFactory $serviceFactory, AdManagerSession $session, $advertiserId, $salespersonId, $traffickerId, $Advertiser) {
    $orderService = $serviceFactory->createOrderService($session);
    $order = new Order();
    $order->setName('Order #' . uniqid());
    $order->setAdvertiserId($advertiserId);
    $order->setSalespersonId($salespersonId);
    $order->setTraffickerId($traffickerId);

    $results = $orderService->createOrders([$order]);

    foreach ($results as $i => $order) {
      $this->createLineItem(new ServiceFactory(), $session, intval($order->getId()), intval(self::PLACEMENT_ID), $Advertiser);
    }
    return $order->getId();
  }

  public function createLineItem(ServiceFactory $serviceFactory, AdManagerSession $session, $orderId, $placementId, $Advertiser) {

    $lineItemService = $serviceFactory->createLineItemService($session);
    $inventoryTargeting = new InventoryTargeting();
    $inventoryTargeting->setTargetedPlacementIds([$placementId]);

    $targeting = new Targeting();
    $targeting->setInventoryTargeting($inventoryTargeting);


    $lineItem = new LineItem();
    $lineItem->setName('Line item #' . uniqid());
    $lineItem->setOrderId($orderId);
    $lineItem->setTargeting($targeting);
    $lineItem->setLineItemType(LineItemType::PRICE_PRIORITY);
    $lineItem->setAllowOverbook(true);
    // Create the creative placeholder.
    $creativePlaceholder = new CreativePlaceholder();
    $creativePlaceholder->setSize(new Size(1, 1, false));
    $nativeAppInstallTemplateId = 10004520;

    $creativePlaceholder->setCreativeTemplateId($nativeAppInstallTemplateId);
    $creativePlaceholder->setCreativeSizeType(CreativeSizeType::NATIVE);


    $lineItem->setCreativePlaceholders([$creativePlaceholder]);

    // Set the creative rotation type to even.
    $lineItem->setCreativeRotationType(CreativeRotationType::EVEN);
    // Set the length of the line item to run.
    $lineItem->setStartDateTime(AdManagerDateTimes::fromDateTime(new DateTime(date('Y-m-d H:i:s', strtotime($Advertiser->start_date)), new DateTimeZone('America/New_York'))));
    $lineItem->setEndDateTime(AdManagerDateTimes::fromDateTime(new DateTime(date('Y-m-d H:i:s', strtotime($Advertiser->end_date)), new DateTimeZone('America/New_York'))));
    // Set the cost per unit to $2.
    if($Advertiser->type_campaign == 1){
      $totalAction = (int) $Advertiser->total/$Advertiser->cpc;
      $lineItem->setCostType(CostType::CPC);
    }else{
      $totalAction = (int) ($Advertiser->total/$Advertiser->cpc)*1000;
      $lineItem->setCostType(CostType::CPM);
    }

    $lineItem->setCostPerUnit(new Money('USD', ($Advertiser->cpc*1000000)));
    // Set the number of units bought to 500,000 so that the budget is
    // $1,000.
    $goal = new Goal();
    $goal->setUnits($totalAction);
    if($Advertiser->type_campaign == 1){
      $goal->setUnitType(UnitType::CLICKS);
    }else{
      $goal->setUnitType(UnitType::IMPRESSIONS);
    }
    //  $goal->setGoalType(GoalType::LIFETIME);
    $lineItem->setPrimaryGoal($goal);
    // Create the line items on the server.
    $results = $lineItemService->createLineItems([$lineItem]);
    // Print out some information for each created line item.
    $Advertiser->update(['line_item_id' => $lineItem->getId()]);

    foreach ($results as $i => $lineItem) {
      $idCreative = $this->createCreative(new ServiceFactory(), $session, intval(self::ADVERTISER_ID), $Advertiser);
      self::LineItemCriative(new ServiceFactory(), $session, intval($lineItem->getId()), intval($idCreative));
    }
    $this->approveCampaing($Advertiser->id_advertiser, $session);
    return 1;

  }

  public function getTeste($idAdvertiser){
    $Advertiser = $this->model->find($idAdvertiser);

    $oAuth2Credential = (new OAuth2TokenBuilder())
    ->fromFile()
    ->withJsonKeyFilePath($this->json)
    ->build();

    $session = (new AdManagerSessionBuilder())->fromFile()
    ->withOAuth2Credential($oAuth2Credential)
    ->build();

    $idCreative = $this->createCreative(new ServiceFactory(), $session, intval(self::ADVERTISER_ID), $Advertiser);

    self::LineItemCriative(new ServiceFactory(), $session, intval('5232598494'), intval($idCreative));
  }

  public function createCreative(ServiceFactory $serviceFactory, AdManagerSession $session, $advertiserId, $Advertiser) {


        $creativeService = $serviceFactory->createCreativeService($session);
        // Use the system defined native app install creative template.
        $nativeAppInstallTemplateId = 10004520;
      //  $nativeAppInstallTemplateId = 10004400;

        // Use 1x1 as the size for native creatives.
        $size = new Size();
        $size->setWidth(1);
        $size->setHeight(1);
        $size->setIsAspectRatio(false);
        // Create a native app install creative for the Pie Noon app.
        $nativeAppInstallCreative = new TemplateCreative();
        $nativeAppInstallCreative->setName('Native creative #' . uniqid());
        $nativeAppInstallCreative->setAdvertiserId($advertiserId);
        $nativeAppInstallCreative->setDestinationUrl("{$Advertiser->url}");
        $nativeAppInstallCreative->setCreativeTemplateId(
            $nativeAppInstallTemplateId
        );
        $nativeAppInstallCreative->setSize($size);

        // Set the headline.
        $headlineVariableValue = new StringCreativeTemplateVariableValue();
        $headlineVariableValue->setUniqueName('Headline');
        $headlineVariableValue->setValue("{$Advertiser->title}");
        $variableValues = [$headlineVariableValue];
        // Set the body text.
        $bodyVariableValue = new StringCreativeTemplateVariableValue();
        $bodyVariableValue->setUniqueName('Body');
        $bodyVariableValue->setValue("{$Advertiser->description}");
        $variableValues[] = $bodyVariableValue;
        // Set the image asset.
        $imageVariableValue = new AssetCreativeTemplateVariableValue();
        $imageVariableValue->setUniqueName('Image');
        $imageAsset = new CreativeAsset();
        $imageAsset->setFileName('image' . uniqid() . '.png');
        $imageAsset->setAssetByteArray(
            file_get_contents("{$Advertiser->image}")
        );
        $imageVariableValue->setAsset($imageAsset);
        $variableValues[] = $imageVariableValue;
        // Set the price.
        $priceVariableValue = new StringCreativeTemplateVariableValue();
        $priceVariableValue->setUniqueName('Attribution');
        $priceVariableValue->setValue('Free');
        $variableValues[] = $priceVariableValue;
        // Set app icon image asset.
        // $appIconVariableValue = new AssetCreativeTemplateVariableValue();
        // $appIconVariableValue->setUniqueName('Attribution');
        // $appIconAsset = new CreativeAsset();
        // $appIconAsset->setFileName('icon' . uniqid() . '.png');
        // $appIconAsset->setAssetByteArray(
        //     file_get_contents("https://painel.beetads.com/assets/painel/uploads/settings/beetads-31e0abb039d6f233ce90d15899f2a254.png")
        // );
        //  $appIconVariableValue->setAsset($appIconAsset);
        //  $variableValues[] = $appIconVariableValue;
        // Set the call to action text.
        $callToActionVariableValue = new StringCreativeTemplateVariableValue();
        $callToActionVariableValue->setUniqueName('Calltoaction');
        $callToActionVariableValue->setValue('ABRIR');
        $variableValues[] = $callToActionVariableValue;
        // Set the star rating.
        // $starRatingVariableValue = new StringCreativeTemplateVariableValue();
        // $starRatingVariableValue->setUniqueName('Starrating');
        // $starRatingVariableValue->setValue('4');
        // $variableValues[] = $starRatingVariableValue;
        // Set the store type.
        // $storeVariableValue = new StringCreativeTemplateVariableValue();
        // $storeVariableValue->setUniqueName('Store');
        // $storeVariableValue->setValue('Google Play');
        // $variableValues[] = $storeVariableValue;
        // Set the deep link URL.
        // $deepLinkVariableValue = new UrlCreativeTemplateVariableValue();
        // $deepLinkVariableValue->setUniqueName('DeeplinkclickactionURL');
        // $deepLinkVariableValue->setValue(
        //     'market://details?id=com.google.fpl.pie_noon');
        // $variableValues[] = $deepLinkVariableValue;
        $nativeAppInstallCreative->setCreativeTemplateVariableValues(
            $variableValues
        );
        // Create the native creatives on the server.
        $results = $creativeService->createCreatives(
            [$nativeAppInstallCreative]
        );
        // Print out some information for each created native creative.

        foreach ($results as $i => $nativeAppInstallCreative) {
            return $nativeAppInstallCreative->getId();
        }

    ////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////
    // $creativeService = $serviceFactory->createCreativeService($session);
    // $imageCreative = new ImageCreative();
    // $imageCreative->setName('Image creative #' . uniqid());
    // $imageCreative->setAdvertiserId($advertiserId);
    // $imageCreative->setDestinationUrl("{$Advertiser->url}");
    // // Set the size of the image creative.
    // $size = new Size();
    // $size->setWidth(1);
    // $size->setHeight(1);
    // $size->setIsAspectRatio(false);
    // $imageCreative->setSize($size);
    // Set the creative's asset.
    // $creativeAsset = new CreativeAsset();
    // $creativeAsset->setFileName(300);
    // $creativeAsset->setAssetByteArray(
    //   file_get_contents("{$Advertiser->image}")
    // );
    //
    // $imageCreative->setPrimaryImageAsset($creativeAsset);
    // // Create the image creatives on the server.
    // $results = $creativeService->createCreatives([$imageCreative]);
    // Print out some information for each created image creative.
    // foreach ($results as $i => $imageCreative) {
    //   return $imageCreative->getId();
    // }
  }

  public function LineItemCriative(ServiceFactory $serviceFactory, AdManagerSession $session, $lineItemId, $creativeId) {
    $licaService =
    $serviceFactory->createLineItemCreativeAssociationService($session);
    $lica = new LineItemCreativeAssociation();
    $lica->setCreativeId($creativeId);
    $lica->setLineItemId($lineItemId);
    $results = $licaService->createLineItemCreativeAssociations([$lica]);
  }

  public function checkToken($token){
    $token = AdvertiserToken::where('token', $token)->first();
    if(isset($token->expire)){
      if($token->expire < date('Y-m-d H:i:s')){
          return "Token Expirado";
      }
      return 1;
    }else{
      return "Token não existe";
    }
  }

  public function reports($token){
    $check = $this->checkToken($token);

    if($check != 1){
      return $check;
    }

    $data = AdmanagerAdvertiserReport::join('advertiser','advertiser.order_id','admanager_advertiser_report.order_id')
    ->selectRaw('advertiser.id_advertiser, admanager_advertiser_report.date, admanager_advertiser_report.impressions, admanager_advertiser_report.clicks, admanager_advertiser_report.ctr, admanager_advertiser_report.active_view')
    ->get();

    return $data->toJSON();

  }

  public function updateLineItem($idAdvertiser, $token) {

        if($check != 1){
          return $check;
        }

        $serviceFactory = new ServiceFactory();
        $oAuth2Credential = (new OAuth2TokenBuilder())->fromFile()->withJsonKeyFilePath($this->json)->build();
        $session = (new AdManagerSessionBuilder())->fromFile()->withOAuth2Credential($oAuth2Credential)->build();

        $Advertiser = $this->model->find($idAdvertiser);

        $lineItemId = $Advertiser->line_item_id;
        $lineItemService = $serviceFactory->createLineItemService($session);

        $statementBuilder = new StatementBuilder();
        $statementBuilder->Where('id = :id');
        $statementBuilder->OrderBy('id ASC');
        $statementBuilder->Limit(1);
        $statementBuilder->WithBindVariableValue('id', $lineItemId);

        $page = $lineItemService->getLineItemsByStatement(
            $statementBuilder->toStatement()
        );
        $lineItem = $page->getResults()[0];

        $lineItem->setEndDateTime(AdManagerDateTimes::fromDateTime(new DateTime(date('Y-m-d H:i:s', strtotime($Advertiser->end_date)), new DateTimeZone('America/New_York'))));
        $lineItem->setCostPerUnit(new Money('USD', ($Advertiser->cpc*1000000)));

        $lineItems = $lineItemService->updatelineItems([$lineItem]);
        return 1;
  }

  public function updateLineItemTeste() {
        $serviceFactory = new ServiceFactory();
        $oAuth2Credential = (new OAuth2TokenBuilder())->fromFile()->withJsonKeyFilePath($this->json)->build();
        $session = (new AdManagerSessionBuilder())->fromFile()->withOAuth2Credential($oAuth2Credential)->build();

        $lineItemId = '5206050364';
        $lineItemService = $serviceFactory->createLineItemService($session);

        $statementBuilder = new StatementBuilder();
        //$statementBuilder->Where('id = :id');
      //  $statementBuilder->Where('order_id = :id');
        $statementBuilder->OrderBy('id ASC');
      //  $statementBuilder->Limit(1);
      //  $statementBuilder->WithBindVariableValue('id', $lineItemId);
        $page = $lineItemService->getLineItemsByStatement(
            $statementBuilder->toStatement()
        );

        //$lineItem = $page->getResults()[0];
        $iIs = $page->getResults();
        foreach($iIs as $lineItem){
          if($lineItem->getOrderId() == 2618598165){

            //dd($lineItem->getTargeting()->getCustomTargeting());
            try {
              if(!empty($lineItem->getTargeting())){
                if(!empty($lineItem->getTargeting()->getCustomTargeting())){
                  if(!empty($lineItem->getTargeting()->getCustomTargeting()->getChildren()[0])){


                      $keyId = $lineItem->getTargeting()->getCustomTargeting()->getChildren()[0]->getChildren()[0]->getKeyId();
                      $value = $lineItem->getTargeting()->getCustomTargeting()->getChildren()[0]->getChildren()[0]->getValueIds()[0];

                      $inventoryTargeting = new InventoryTargeting();
                      $inventoryTargeting->setTargetedPlacementIds(['29597076']);

                      $customCriteria = new CustomCriteria();
                      $customCriteria->setKeyId($keyId);
                      $customCriteria->setOperator(CustomCriteriaComparisonOperator::IS);
                      $customCriteria->setValueIds([$value]);

                       $topCustomCriteriaSet = new CustomCriteriaSet();
                       $topCustomCriteriaSet->setLogicalOperator(
                           CustomCriteriaSetLogicalOperator::OR_VALUE
                       );

                       $topCustomCriteriaSet->setChildren(
                           [$customCriteria]
                       );

                     $targeting = new Targeting();
                     $targeting->setInventoryTargeting($inventoryTargeting);
                     $targeting->setCustomTargeting($topCustomCriteriaSet);

                     $lineItem->setTargeting($targeting);

                     $lineItems = $lineItemService->updatelineItems([$lineItem]);
                   }
                 }
               }
           } catch (\Exception $e) {
             dd($lineItem);
           }
         }
       }
        return 1;
  }




  public function ReportAdvertiser(ServiceFactory $serviceFactory){

    $start = '1';
    $end = '0';

    $oAuth2Credential = (new OAuth2TokenBuilder())->fromFile()->withJsonKeyFilePath($this->json)->build();
    $session = (new AdManagerSessionBuilder())->fromFile()->withOAuth2Credential($oAuth2Credential)->build();

    //PREBID
    $reportQuery = new ReportQuery();
    $reportQuery->setDimensions(
      [
        Dimension::DATE,
        Dimension::ORDER_ID
      ]
    );

    $reportQuery->setColumns(
      [
        Column::AD_SERVER_IMPRESSIONS,
        Column::AD_SERVER_CLICKS,
        Column::AD_SERVER_CTR,
        Column::AD_SERVER_ACTIVE_VIEW_VIEWABLE_IMPRESSIONS_RATE
      ]
    );


    $statementBuilder = (new StatementBuilder());

    $reportService = $serviceFactory->createReportService($session);
    $networkService = $serviceFactory->createNetworkService($session);
    $rootAdUnitId = $networkService->getCurrentNetwork()->getEffectiveRootAdUnitId();

    $reportQuery->setStatement($statementBuilder->toStatement());
    $reportQuery->setDateRangeType(DateRangeType::CUSTOM_DATE);
    $reportQuery->setAdUnitView(ReportQueryAdUnitView::HIERARCHICAL);

    $reportQuery->setStartDate(
      AdManagerDateTimes::fromDateTime(new DateTime("-$start days", new DateTimeZone('America/New_York')))->getDate()
    );

    $reportQuery->setEndDate(
      AdManagerDateTimes::fromDateTime(new DateTime("-$end days",new DateTimeZone('America/New_York')))->getDate()
    );

    $reportJob = new ReportJob();
    $reportJob->setReportQuery($reportQuery);
    $reportJob = $reportService->runReportJob($reportJob);

    $reportDownloader = new ReportDownloader($reportService,  $reportJob->getId());

    if ($reportDownloader->waitForReportToFinish()) {

      $filePath = sprintf('%s.csv.gz', tempnam(sys_get_temp_dir(), 'report-teste'));
      $reportDownloader->downloadReport(ExportFormat::CSV_DUMP, $filePath);

      $resultado = file_get_contents('compress.zlib://'.$filePath);

      $file = fopen('assets/painel/uploads/admanager/report/advertiser.csv','w+');
      fwrite($file, $resultado);
      fclose($file);

      $handle = fopen("assets/painel/uploads/admanager/report/advertiser.csv", 'r');
      $cont = 0;

      AdmanagerAdvertiserReport::truncate();

      $Advertiser = $this->model->where('status_approved', 1)->get();
      $cont = 0;
      while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
        if($cont != 0){
          if($Advertiser->where('order_id', $row[1])->count() > 0){
            $dataForm['date'] = $row[0];
            $dataForm['order_id'] = $row[1];
            $dataForm['impressions'] = $row[2];
            $dataForm['clicks'] = $row[3];
            $dataForm['ctr'] = $row[4];
            $dataForm['active_view'] = $row[5];
            $insertData[] = $dataForm;
            unset($dataForm);
          }
        }
        $cont++;
      }

      if(isset($insertData)){
        AdmanagerAdvertiserReport::insert($insertData);
      }
    }
  }


    public function changeStatus(ServiceFactory $serviceFactory,AdManagerSession $session, $orderId, $action){
      $orderService = $serviceFactory->createOrderService($session);

      $pageSize = StatementBuilder::SUGGESTED_PAGE_LIMIT;
      $statementBuilder = (new StatementBuilder())->where('id = :id')
      ->orderBy('id ASC')
      ->limit($pageSize)
      ->withBindVariableValue('id', $orderId);

      $totalResultSetSize = 0;
      do {
        $page = $orderService->getOrdersByStatement(
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
        $result = $orderService->performOrderAction($action, $statementBuilder->toStatement());
      }
    }

    public function startCampaing($idAdvertiser, $token){
      $check = $this->checkToken($token);

      if($check != 1){
        return $check;
      }

      $oAuth2Credential = (new OAuth2TokenBuilder())->fromFile()->withJsonKeyFilePath($this->json)->build();
      $session = (new AdManagerSessionBuilder())->fromFile()->withOAuth2Credential($oAuth2Credential)->build();
      
      $advertiser = Advertiser::find($idAdvertiser);
      if(isset($advertiser->id_advertiser)){
        if($advertiser->status_approved == 0){
          return "Campanha está aguardando aprovação.";
        }
        $action = new ResumeOrdersAction();
        $this->changeStatus(new ServiceFactory(),$session, intval($advertiser->order_id), $action);
        return 1;
      }else{
        return "ID Não encontrado";
      }
    }

    public function pauseCampaing($idAdvertiser, $token){
      $check = $this->checkToken($token);

      if($check != 1){
        return $check;
      }
      $oAuth2Credential = (new OAuth2TokenBuilder())->fromFile()->withJsonKeyFilePath($this->json)->build();
      $session = (new AdManagerSessionBuilder())->fromFile()->withOAuth2Credential($oAuth2Credential)->build();

      $advertiser = Advertiser::find($idAdvertiser);
      if(isset($advertiser->id_advertiser)){
        if($advertiser->status_approved == 0){
          return "Campanha está aguardando aprovação.";
        }
        $action = new PauseOrdersAction();
        $this->changeStatus(new ServiceFactory(),$session, intval($advertiser->order_id), $action);
        return 1;
      }else{
        return "ID Não encontrado";
      }
    }

    public function approveCampaing($idAdvertiser, $session){
      $advertiser = Advertiser::find($idAdvertiser);
      if(isset($advertiser->id_advertiser)){
        $action = new ApproveOrdersAction();
        $this->changeStatus(new ServiceFactory(),$session, intval($advertiser->order_id), $action);
        $advertiser->update(['status_approved' => 1]);
      }else{
        return "ID Não encontrado";
      }
    }

}
