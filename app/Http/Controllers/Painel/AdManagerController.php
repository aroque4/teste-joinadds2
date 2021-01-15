<?php

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Painel\StandardController;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory;
use App\Models\Painel\AdmanagerReportTemp;
use App\Models\Painel\AdmanagerReport;
use App\Models\Painel\Domain;
use App\Models\Painel\AdmanagerReportUrlCriteria;
use App\Models\Painel\AdManagerReportMonth;
use Illuminate\Support\Facades\Auth;
use Defender;
use DB;
use Google\AdsApi\AdManager\AdManagerSession;
use Google\AdsApi\AdManager\AdManagerSessionBuilder;
use Google\AdsApi\AdManager\v202005\ServiceFactory;
use Google\AdsApi\Common\OAuth2TokenBuilder;
use Google\AdsApi\AdManager\Util\v202005\StatementBuilder;
use Google\AdsApi\AdManager\v202005\ReportQuery;
use Google\AdsApi\AdManager\Util\v202005\ReportDownloader;
use Google\AdsApi\AdManager\v202005\Column;
use Google\AdsApi\AdManager\Util\v202005\AdManagerDateTimes;
use Google\AdsApi\AdManager\v202005\DateRangeType;
use Google\AdsApi\AdManager\v202005\ReportQueryAdUnitView;
use Google\AdsApi\AdManager\v202005\Dimension;
use Google\AdsApi\AdManager\v202005\ExportFormat;
use Google\AdsApi\AdManager\v202005\ReportJob;
use DateTime;
use DateTimeZone;
use Google\AdsApi\AdManager\v202005\InventoryTargeting;
use Google\AdsApi\AdManager\v202005\Targeting;
use UnexpectedValueException;



class AdManagerController extends StandardController {

  protected $nameView = 'priority';
  protected $diretorioPrincipal = 'painel';
  protected $primaryKey = 'id_priority';

  public function __construct(Request $request, AdmanagerReportTemp $model, Factory $validator) {
    $this->request = $request;
    $this->model = $model;
    $this->validator = $validator;
    $this->json = storage_path('app/admanager/adex_key.json');
  }

  public function getImportReportManual(){
    $handle = fopen("assets/painel/uploads/admanager/report/manual.csv", 'r');
    $cont = 0;
    $domains = Domain::get();

    while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {

      if($cont > 0){

        $dataForm['date'] = date('Y-m-d', strtotime($row[0]));
        $dataForm['site_id'] = '21878536848';
        $dataForm['site'] = 'dicasfinanceira.com';
        $dataForm['ad_unit_id'] = 0;
        $dataForm['ad_unit'] = 'N/A';
        $dataForm['impressions'] = $row[1];
        $dataForm['clicks'] = $row[2];
        $dataForm['earnings'] =  $row[3];
        $dataForm['ecpm'] =  $row[4];

        $dataForm['ctr'] = str_replace(',','.',$dataForm['clicks'] / $dataForm['impressions']);

        $dataForm['updated_at'] = date('Y-m-d H:i:s');
        $dataForm['active_view_viewable'] = '68.43';

        $domain = $domains->where('name',$dataForm['site'])->first();

          if(isset($domain->id_domain)){
            $earningsClient = $dataForm['earnings'] - ($dataForm['earnings']  * $domain->rev_share_admanager / 100);
            $dataForm['earnings_client'] = str_replace(',','.',$earningsClient);
          }
          $dataForm['ecpm_client'] = str_replace(',','.',(($dataForm['earnings_client'] / $dataForm['impressions']) * 1000));

        if(isset($dataForm['earnings_client'])){
          $insertData[] = $dataForm;
        }

        unset($dataForm);
      }
      $cont++;
    }

    fclose($handle);

    foreach (array_chunk($insertData,2000) as $t)
    {
      AdmanagerReport::insert($t);
    }
  }

// Set the line item to be updated.
  // const LINE_ITEM_ID = '5147979503';
  // public function runExample(
  //     ServiceFactory $serviceFactory,
  //     AdManagerSession $session,
  //     $lineItemId
  // ) {
  //     $lineItemService = $serviceFactory->createLineItemService($session);
  //     // Create a statement to only select a single line item by ID.
  //     $statementBuilder = new StatementBuilder();
  //     $statementBuilder->Where('id = :id');
  //     $statementBuilder->OrderBy('id ASC');
  //     $statementBuilder->Limit(1);
  //     $statementBuilder->WithBindVariableValue('id', $lineItemId);
  //     // Get the line item.
  //     $page = $lineItemService->getLineItemsByStatement(
  //         $statementBuilder->toStatement()
  //     );
  //     $lineItem = $page->getResults()[0];
  //     dd($lineItem);
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //     $contentCustomCriteria = new CmsMetadataCriteria();
  //     $contentCustomCriteria
  //         ->setOperator(CmsMetadataCriteriaComparisonOperator::AND );
  //     $contentCustomCriteria->setCmsMetadataValueIds([$cmsMetadataValueId]);
  //     $customCriteriaSet = new CustomCriteriaSet();
  //     $customCriteriaSet
  //         ->setLogicalOperator(CustomCriteriaSetLogicalOperator::AND_VALUE);
  //     $customCriteriaSet->setChildren([$contentCustomCriteria]);
  //     // Create targeting.
  //     $targeting = new Targeting();
  //     $targeting->setContentTargeting($contentTargeting);
  //     $targeting->setInventoryTargeting($inventoryTargeting);
  //     $targeting->setVideoPositionTargeting($videoPositionTargeting);
  //     $targeting->setRequestPlatformTargeting($requestPlatformTargeting);
  //     $targeting->setCustomTargeting($customCriteriaSet);
  //     // Create local line item object.
  //     $lineItem = new LineItem();
  //     $lineItem->setName("Video line item #" . uniqid());
  //     $lineItem->setOrderId($orderId);
  //     $lineItem->setTargeting($targeting);
  //
  //
  //
  //
  //
  //
  //
  //
  //
  //     $lineItemService = $serviceFactory->createLineItemService($session);
  //     $inventoryTargeting = new InventoryTargeting();
  //     $inventoryTargeting->setTargetedPlacementIds(['21811513368']);
  //     $inventoryTargeting->setCustomTargeting(['']);
  //
  //     $targeting = new Targeting();
  //     $targeting->setInventoryTargeting($inventoryTargeting);
  //     $lineItem->setTargeting($targeting);
  //     // Update the notes of the line item.
  //     // $lineItem->setTargeting(
  //     //     'Spoke to advertiser about this line item. All is well.'
  //     // );
  //     // Update the line item on the server.
  //     $lineItems = $lineItemService->updatelineItems([$lineItem]);
  //     foreach ($lineItems as $updatedLineItem) {
  //         printf(
  //             "Line item with ID %d and name '%s' was updated.%s",
  //             $updatedLineItem->getId(),
  //             $updatedLineItem->getName(),
  //             PHP_EOL
  //         );
  //     }
  // }
  // public function getMain()
  // {
  //     // Generate a refreshable OAuth2 credential for authentication.
  //     $oAuth2Credential = (new OAuth2TokenBuilder())
  //     ->fromFile()
  //     ->withJsonKeyFilePath($this->json)
  //     ->build();
  //     // Construct an API session configured from an `adsapi_php.ini` file
  //     // and the OAuth2 credentials above.
  //     $session = (new AdManagerSessionBuilder())->fromFile()
  //         ->withOAuth2Credential($oAuth2Credential)
  //         ->build();
  //     self::runExample(
  //         new ServiceFactory(),
  //         $session,
  //         intval(self::LINE_ITEM_ID)
  //     );
  // }


  const SAVED_QUERY_ID = '11428246171';
      public static function runExample(
          ServiceFactory $serviceFactory,
          AdManagerSession $session,
          $savedQueryId
      ) {
          $reportService = $serviceFactory->createReportService($session);
          // Create statement to retrieve the saved query.
          $statementBuilder = (new StatementBuilder())->where('id = :id')
              ->orderBy('id ASC')
              ->limit(1)
              ->withBindVariableValue('id', $savedQueryId);
          $savedQueryPage = $reportService->getSavedQueriesByStatement(
              $statementBuilder->toStatement()
          );
          $savedQuery = $savedQueryPage->getResults()[0];
          if ($savedQuery->getIsCompatibleWithApiVersion() === false) {
              throw new UnexpectedValueException(
                  'The saved query is not compatible with this API version.'
              );
          }
          // Optionally modify the query.
          $reportQuery = $savedQuery->getReportQuery();

          $reportQuery->setAdUnitView(ReportQueryAdUnitView::TOP_LEVEL);
          // Create report job using the saved query.
          $reportJob = new ReportJob();
          $reportJob->setReportQuery($reportQuery);
          $reportJob = $reportService->runReportJob($reportJob);

          // Create report downloader to poll report's status and download when
          // ready.
          $reportDownloader = new ReportDownloader(
              $reportService,
              $reportJob->getId()
          );
          DD($reportJob);
          if ($reportDownloader->waitForReportToFinish()) {
              // Write to system temp directory by default.
              $filePath = sprintf(
                  '%s.csv.gz',
                  tempnam(sys_get_temp_dir(), 'saved-report-')
              );
              printf("Downloading report to %s ...%s", $filePath, PHP_EOL);
              // Download the report.
              $reportDownloader->downloadReport(
                  ExportFormat::CSV_DUMP,
                  $filePath
              );
              print "done.\n";
          } else {
              print "Report failed.\n";
          }
      }
      public function getMain()
      {
            $oAuth2Credential = (new OAuth2TokenBuilder())
            ->fromFile()
            ->withJsonKeyFilePath($this->json)
            ->build();
          $session = (new AdManagerSessionBuilder())->fromFile()
              ->withOAuth2Credential($oAuth2Credential)
              ->build();
          self::runExample(
              new ServiceFactory(),
              $session,
              intval(self::SAVED_QUERY_ID)
          );
      }








































  public function getIndex(){

    $oAuth2Credential = (new OAuth2TokenBuilder())
    ->fromFile()
    ->withJsonKeyFilePath($this->json)
    ->build();

    $session = (new AdManagerSessionBuilder())->fromFile()->withOAuth2Credential($oAuth2Credential)->build();
    //  $this->getReportSavedTeste(new ServiceFactory(), $session, 11328626035);
  }

  public function getReport($start, $end){

    $oAuth2Credential = (new OAuth2TokenBuilder())->fromFile()->withJsonKeyFilePath($this->json)->build();
    $session = (new AdManagerSessionBuilder())->fromFile()->withOAuth2Credential($oAuth2Credential)->build();

    //PREBID
    $reportQuery = new ReportQuery();
    $reportQuery->setDimensions(
      [
        Dimension::ORDER_NAME,
        Dimension::DATE,
        Dimension::AD_UNIT_NAME
      ]
    );

    $reportQuery->setColumns(
      [
        Column::AD_SERVER_IMPRESSIONS,
        Column::AD_SERVER_CLICKS,
        Column::AD_SERVER_CTR	,
        Column::AD_SERVER_CPM_AND_CPC_REVENUE,
        Column::AD_SERVER_WITH_CPD_AVERAGE_ECPM,
        Column::AD_SERVER_ACTIVE_VIEW_VIEWABLE_IMPRESSIONS_RATE

      ]
    );

    $statementBuilder = (new StatementBuilder()); //->where('order_name like :order_name')->withBindVariableValue('order_name', '%Prebid All%');
    $this->ReportSaved(new ServiceFactory(), $session, $start, $end, $reportQuery, $statementBuilder, $file = 'prebid.csv');

    //ADXx
    $reportQuery = new ReportQuery();
    $reportQuery->setDimensions(
      [
        Dimension::DATE,
        Dimension::AD_UNIT_NAME
      ]
    );

    $reportQuery->setColumns(
      [
        Column::AD_EXCHANGE_LINE_ITEM_LEVEL_IMPRESSIONS,
        Column::AD_EXCHANGE_LINE_ITEM_LEVEL_CLICKS,
        Column::AD_EXCHANGE_LINE_ITEM_LEVEL_CTR	,
        Column::AD_EXCHANGE_LINE_ITEM_LEVEL_REVENUE,
        Column::AD_EXCHANGE_LINE_ITEM_LEVEL_AVERAGE_ECPM,
        Column::AD_EXCHANGE_ACTIVE_VIEW_VIEWABLE_IMPRESSIONS_RATE

      ]
    );

    $statementBuilder = (new StatementBuilder());;
    $this->ReportSaved(new ServiceFactory(),$session, $start, $end,  $reportQuery, $statementBuilder, $file = 'adx.csv');

    $this->saveReportTemp();

    $rows = AdmanagerReportTemp::selectRAW(" date, site, site_id, ad_unit, ad_unit_id, SUM(impressions) 'impressions', SUM(clicks) 'clicks', SUM(earnings) 'earnings', SUM(earnings_client) 'earnings_client', AVG(active_view_viewable) 'active_view_viewable',  sum( if (type = 'P', earnings , 0 )) 'earnings_prebid', sum( if (type = 'A', earnings , 0 )) 'earnings_adx'")
    ->where('impressions','>',0)
    ->where('earnings','>',0.0)
    ->groupBy('date')
    ->groupBy('site')
    ->groupBy('site_id')
    ->groupBy('ad_unit')
    ->groupBy('ad_unit_id')
    ->get();

    foreach($rows as $row){
      $dataForm['date'] = $row->date;
      $dataForm['site_id'] = $row->site_id;
      $dataForm['site'] = $row->site;
      $dataForm['ad_unit_id'] = (int) $row->ad_unit_id;
      $dataForm['ad_unit'] = trim($row->ad_unit);
      $dataForm['impressions'] = $row->impressions;
      $dataForm['clicks'] = $row->clicks;
      $dataForm['earnings'] =  $row->earnings;
      $dataForm['earnings_client'] =  $row->earnings_client;
      $dataForm['active_view_viewable'] = $row->active_view_viewable;
      $dataForm['ctr'] = str_replace(',','.',$row->clicks / $row->impressions);
      $dataForm['ecpm_client'] = str_replace(',','.',(($row->earnings_client / $row->impressions) * 1000));
      $dataForm['ecpm'] = str_replace(',','.',(($row->earnings / $row->impressions) * 1000));
      $dataForm['ecpm_client'] = str_replace(',','.',(($row->earnings_client / $row->impressions) * 1000));
      $dataForm['earnings_prebid'] =  $row->earnings_prebid;
      $dataForm['earnings_adx'] =  $row->earnings_adx;

      $report = AdmanagerReport::where('date', $row->date)
      ->where('ad_unit_id', $row->ad_unit_id)
      ->where('site_id', $row->site_id)
      ->first();

      if(isset($report->id_admanager_report)){
        $report->update($dataForm);
      }else{
        AdmanagerReport::create($dataForm);
      }
      unset($dataForm);
    }


    // URL CRITERIA

    $reportQuery = new ReportQuery();
    $reportQuery->setDimensions(
      [
        Dimension::DATE,
        Dimension::AD_UNIT_NAME,
        Dimension::CUSTOM_CRITERIA
      ]
    );

    $reportQuery->setColumns(
      [
        Column::AD_EXCHANGE_LINE_ITEM_LEVEL_IMPRESSIONS,
        Column::AD_EXCHANGE_LINE_ITEM_LEVEL_CLICKS,
        Column::AD_EXCHANGE_LINE_ITEM_LEVEL_CTR	,
        Column::AD_EXCHANGE_LINE_ITEM_LEVEL_REVENUE,
        Column::AD_EXCHANGE_LINE_ITEM_LEVEL_AVERAGE_ECPM,
        Column::AD_EXCHANGE_ACTIVE_VIEW_VIEWABLE_IMPRESSIONS_RATE
      ]
    );

    $statementBuilder = (new StatementBuilder());;
    $this->ReportSaved(new ServiceFactory(),$session, $start, $end,  $reportQuery, $statementBuilder, $file = 'urlcriteria.csv');


    $handle = fopen('assets/painel/uploads/admanager/report/urlcriteria.csv','r');
    $cont = 0;
    AdmanagerReportUrlCriteria::truncate();
    $domains = Domain::get();
    foreach ($domains as $domain) {
      $ArrayRavShare[strtolower($domain->name)] = $domain->rev_share_admanager;
    }

    while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
      if($cont > 0){
        $custom = explode('=', $row[3]);

        if(isset($custom[1])){
          $dataForm['date'] = $row[0];
          $dataForm['site_id'] = $row[4];
          $dataForm['site'] = $row[1];
          $dataForm['ad_unit_id'] = (int) $row[5];
          $dataForm['ad_unit'] = trim($row[2]);
          $dataForm['url'] = "http://".$dataForm['site']."/?p=".$custom[1];
          $dataForm['custon_key'] = $custom[0];
          $dataForm['custon_value'] = $custom[1];
          $dataForm['impressions'] = $row[7];
          $dataForm['clicks'] = $row[8];
          $dataForm['ctr'] = str_replace(',','.',($row[9]*100));
          $dataForm['earnings'] = str_replace(',','.',($row[10]/1000000));

          if($dataForm['earnings'] >0 && $dataForm['impressions'] > 0.0){
            $dataForm['ecpm'] = ($dataForm['earnings'] / $dataForm['impressions']) * 1000;
            $dataForm['ecpm'] = str_replace(',','.',$dataForm['ecpm']);
          }else{
            $dataForm['ecpm'] = 0;
          }

          $dataForm['updated_at'] = date('Y-m-d H:i:s');
          $dataForm['active_view_viewable'] = str_replace(',','.',($row[12]*100));
          $dataForm['url_id'] = (int) $custom[1];

          if(array_key_exists(strtolower($dataForm['site']), $ArrayRavShare)){
                        
            $earningsClient = $dataForm['earnings'] - ($dataForm['earnings'] * $ArrayRavShare[strtolower($dataForm['site'])] / 100);
            
            if($earningsClient > 0 && $dataForm['impressions'] > 0){
              $ecpmClient = ($earningsClient / $dataForm['impressions']) * 1000;
              $dataForm['ecpm_client'] = str_replace(',','.',$ecpmClient);
            }else{
              $dataForm['ecpm_client'] = 0;
            }
            $dataForm['earnings_client'] = str_replace(',','.',$earningsClient);

          }else{
            $dataForm['ecpm_client'] = 0;
            $dataForm['earnings_client'] = 0;
          }

          $insert[] = $dataForm;

          unset($dataForm);
        }
      }
      $cont++;
    }
    fclose($handle);
    if(isset($insert)){
      foreach (array_chunk($insert,2000) as $t)
      {
        AdmanagerReportUrlCriteria::insert($t);
      }
    }

  }


  public function ReportSaved(ServiceFactory $serviceFactory,  AdManagerSession $session, $start, $end, $reportQuery, $statementBuilder, $file) {

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

      $filePath = sprintf('%s.csv.gz', tempnam(sys_get_temp_dir(), 'report-'));
      $reportDownloader->downloadReport(ExportFormat::CSV_DUMP, $filePath);

      $resultado = file_get_contents('compress.zlib://'.$filePath);

      $file = fopen('assets/painel/uploads/admanager/report/'.$file,'w+');
      fwrite($file, $resultado);
      fclose($file);
    }
  }


  public function saveReportTemp(){

    $files[1]['name'] = 'prebid';
    $files[1]['ravshare'] = 50;

    $files[2]['name'] = 'adx';
    $files[2]['ravshare'] = 0;
    AdmanagerReportTemp::truncate();

    foreach($files as $file){

      $domains = Domain::get();

      $handle = fopen("assets/painel/uploads/admanager/report/".$file['name'].".csv", 'r');
      $cont = 0;

      while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {

        if($cont > 0){

          $positionCount = 0;
          if($file['name'] == 'prebid'){
              // $dataForm['order_name'] = $row[$positionCount];
              $positionCount += 1;
              $dataForm['date'] = $row[$positionCount];
          }else{
              $dataForm['date'] = $row[$positionCount];
          }

          $positionCount += 1;
          $dataForm['site'] = $row[$positionCount];
          $positionCount += 1;
          $dataForm['ad_unit'] = trim($row[$positionCount]);
          if($file['name'] == 'prebid'){
            $positionCount += 2;
            $dataForm['type'] = 'P';
          }else{
            $positionCount += 1;
            $dataForm['type'] = 'A';
          }

          $dataForm['site_id'] = $row[$positionCount];
          $positionCount += 1;
          $dataForm['ad_unit_id'] = (int) $row[$positionCount];
          $positionCount += 1;
          $dataForm['impressions'] = (int) $row[$positionCount];
          $positionCount += 1;
          $dataForm['clicks'] = $row[$positionCount];
          $positionCount += 2;
          $dataForm['earnings'] =  str_replace(',','.',($row[$positionCount]/1000000));
          $positionCount += 2;
          $dataForm['updated_at'] = date('Y-m-d H:i:s');
          $dataForm['active_view_viewable'] = str_replace(',','.',($row[$positionCount]*100));

          $domain = $domains->where('name',$dataForm['site'])->first();

          if($file['ravshare'] == 0){
            if(isset($domain->id_domain)){
              $earningsClient = $dataForm['earnings'] - ($dataForm['earnings']  * $domain->rev_share_admanager / 100);
              $dataForm['earnings_client'] = str_replace(',','.',$earningsClient);
            }
          }else if(isset($domain->id_domain)){
            $earningsClient = $dataForm['earnings'] - ($dataForm['earnings']  * $domain->rev_share_adserver / 100);
            $dataForm['earnings_client'] = str_replace(',','.',$earningsClient);
          }

          if(isset($dataForm['earnings_client'])){
            $insertData[] = $dataForm;
          }

          unset($dataForm);
        }
        $cont++;
      }

      fclose($handle);
    }



    foreach (array_chunk($insertData,2000) as $t)
    {
      AdmanagerReportTemp::insert($t);
    }

    // if(isset($insertData)){
    //   AdmanagerReportTemp::insert($insertData);
    // }
    //$this->admanager_model->createTemp($insertData);
  }

  public function getDataMonth(ServiceFactory $serviceFactory){
    // FECHAMENTO MENSAL

    $oAuth2Credential = (new OAuth2TokenBuilder())->fromFile()->withJsonKeyFilePath($this->json)->build();
    $session = (new AdManagerSessionBuilder())->fromFile()->withOAuth2Credential($oAuth2Credential)->build();

    $reportQuery = new ReportQuery();
    $reportQuery->setDimensions(
      [
        Dimension::AD_UNIT_NAME
      ]
    );

    $reportQuery->setColumns(
      [
        Column::AD_SERVER_CPM_AND_CPC_REVENUE,
        Column::TOTAL_LINE_ITEM_LEVEL_CPM_AND_CPC_REVENUE,
        Column::AD_EXCHANGE_LINE_ITEM_LEVEL_REVENUE
      ]
    );

    $statementBuilder = (new StatementBuilder());;

    $reportService = $serviceFactory->createReportService($session);
    $networkService = $serviceFactory->createNetworkService($session);
    $rootAdUnitId = $networkService->getCurrentNetwork()->getEffectiveRootAdUnitId();

    $reportQuery->setStatement($statementBuilder->toStatement());
    $reportQuery->setDateRangeType(DateRangeType::CUSTOM_DATE);
    $reportQuery->setAdUnitView(ReportQueryAdUnitView::TOP_LEVEL);


    $reportQuery->setStartDate(
      AdManagerDateTimes::fromDateTime(new DateTime(date("Y-n-j 00:00:00", strtotime("first day of previous month")), new DateTimeZone('America/New_York')))->getDate()
    );

    $reportQuery->setEndDate(
      AdManagerDateTimes::fromDateTime(new DateTime(date("Y-n-j 23:59:59", strtotime("last day of previous month")), new DateTimeZone('America/New_York')))->getDate()
    );

    $reportJob = new ReportJob();
    $reportJob->setReportQuery($reportQuery);
    $reportJob = $reportService->runReportJob($reportJob);

    $reportDownloader = new ReportDownloader($reportService,  $reportJob->getId());

    if ($reportDownloader->waitForReportToFinish()) {

      $filePath = sprintf('%s.csv.gz', tempnam(sys_get_temp_dir(), 'report-'));
      $reportDownloader->downloadReport(ExportFormat::CSV_DUMP, $filePath);

      $resultado = file_get_contents('compress.zlib://'.$filePath);

      $file = fopen('assets/painel/uploads/admanager/report/data-month.csv','w+');
      fwrite($file, $resultado);
      fclose($file);
    }

    $handle = fopen('assets/painel/uploads/admanager/report/data-month.csv','r');
    $cont = 0;

    $domains = Domain::get();
    foreach ($domains as $domain) {
      $ArrayRavShareAdManager[strtolower($domain->name)] = $domain->rev_share_admanager;
      $ArrayRavShareAdServer[strtolower($domain->name)] = $domain->rev_share_adserver;
    }
    while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
      if($cont > 0){

          $dataForm['start_date'] = date("Y-n-j", strtotime("first day of previous month"));
          $dataForm['end_date'] = date("Y-n-j", strtotime("last day of previous month"));
          $dataForm['site_id'] = $row[1];
          $dataForm['site'] = $row[0];
          $dataForm['earnings_ad_manager'] = str_replace(',','.',($row[4]/1000000));
          $dataForm['earnings_ad_server'] = str_replace(',','.',($row[2]/1000000));
          $dataForm['earnings_ad_total'] = str_replace(',','.',($row[3]/1000000));

          if(array_key_exists(strtolower($dataForm['site']), $ArrayRavShareAdManager)){
            $dataForm['earnings_client_ad_manager'] = $dataForm['earnings_ad_manager'] - ($dataForm['earnings_ad_manager']  * $ArrayRavShareAdManager[$dataForm['site']] / 100);
          }else{
            $dataForm['earnings_client_ad_manager'] = 0;
          }

          if(array_key_exists(strtolower($dataForm['site']), $ArrayRavShareAdServer)){
            $dataForm['earnings_client_ad_server'] = $dataForm['earnings_ad_server'] - ($dataForm['earnings_ad_server']  * $ArrayRavShareAdServer[$dataForm['site']] / 100);
          }else{
            $dataForm['earnings_client_ad_server'] = 0;
          }

          $dataForm['earnings_client_ad_total'] = $dataForm['earnings_client_ad_manager'] + $dataForm['earnings_client_ad_server'];

          $insert[] = $dataForm;


          $check = AdManagerReportMonth::where('start_date', $dataForm['start_date'])
          ->where('end_date', $dataForm['end_date'])
          ->where('site', $dataForm['site'])
          ->first();

          if(empty($check->id_ad_manager_report_month)){
            AdManagerReportMonth::create($dataForm);
          }else{
            $check->update($dataForm);
          }

          unset($dataForm);
      }
      $cont++;
    }
    fclose($handle);

  }

  public function getTeste(){
        $this->saveReportTemp();
  }

}
