<?php

namespace App\Http\Controllers\Painel;

use App\Helpers\Helper;
use App\Http\Controllers\Painel\StandardController;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory;
use App\Models\Painel\Domain;
use App\Models\Painel\DomainStatus;
use App\Models\Painel\DomainCategory;
use App\Models\Painel\DomainFixed;
use App\Models\Painel\User;
use App\Models\Painel\AdUnitRoot;
use App\Models\Painel\DomainScripts;
use App\Models\Painel\AdUnit;
use App\Models\Painel\PrebidBids;
use App\Models\Painel\PrebidPlacement;
use App\Models\Painel\PrebidVersion;
use App\Models\Painel\AdUnitBid;
use App\Models\Painel\DomainNotification;
use Illuminate\Support\Facades\Auth;
use Defender;
use Storage;
use App\Helpers\HunterObfuscator\HunterObfuscator;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use NodejsPhpFallback\Uglify;

class DomainController extends StandardController {

  protected $nameView = 'domain';
  protected $diretorioPrincipal = 'painel';
  protected $primaryKey = 'id_domain';

  public function __construct(Request $request, Domain $model, Factory $validator) {
    $this->request = $request;
    $this->model = $model;
    $this->validator = $validator;
  }

  public function getIndex() {
    if (Defender::hasPermission("{$this->nameView}")) {
      $data = $this->model->join('users','users.id','domain.id_user')
      ->selectRaw('domain.*, users.name nameClient, gerente_contas')
      ->paginate(2000);

      $principal = $this->diretorioPrincipal;
      $primaryKey = $this->primaryKey;
      $rota = $this->nameView;

      $domainStatus = DomainStatus::get();


      return view("{$this->diretorioPrincipal}.{$this->nameView}.index", compact('data', 'principal', 'rota', 'primaryKey','domainStatus'));
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function getViewSite($id=null,$type=null){
      $principal = $this->diretorioPrincipal;
      $primaryKey = $this->primaryKey;
      $rota = $this->nameView;
      $data = $this->model
                  ->join('users','users.id','domain.id_user')
                  ->selectRaw('domain.*, users.name nameClient')
                  ->where('domain.id_domain',$id)
                  ->first();
      // dd($data);
      return view("{$this->diretorioPrincipal}.{$this->nameView}.view-site", compact('data', 'principal', 'rota', 'primaryKey'));
  }


  public function postSearch() {
    if (Defender::hasPermission("{$this->nameView}")) {
      $dadosForm = $this->request->all();
      session(['name_domain' => $dadosForm['name']."%"]);

      $data = $this->model->join('users','users.id','domain.id_user')
      ->selectRaw('domain.*, users.name nameClient')
      ->where('domain.name','LIKE',"%".session('name_domain')."%")
      ->paginate($this->totalItensPorPagina);

      $principal = $this->diretorioPrincipal;
      $primaryKey = $this->primaryKey;
      $rota = $this->nameView;

      return view("{$this->diretorioPrincipal}.{$this->nameView}.index", compact('data', 'principal', 'rota', 'primaryKey'));
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function getSearch() {
    if (Defender::hasPermission("{$this->nameView}")) {

      $data = $this->model->join('users','users.id','domain.id_user')
      ->selectRaw('domain.*, users.name nameClient')
      ->where('domain.name','LIKE',"%".session('name_domain')."%")
      ->paginate($this->totalItensPorPagina);

      $principal = $this->diretorioPrincipal;
      $primaryKey = $this->primaryKey;
      $rota = $this->nameView;

      return view("{$this->diretorioPrincipal}.{$this->nameView}.index", compact('data', 'principal', 'rota', 'primaryKey'));
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function getMyDomains() {
    if (Defender::hasPermission("domain/my-domains")) {
      $data = $this->model->where('id_user', Auth::user()->id)
                          ->whereNotIn('id_domain_status',[5])
                          ->paginate($this->totalItensPorPagina);
      $principal = $this->diretorioPrincipal;
      $primaryKey = $this->primaryKey;
      $rota = $this->nameView;
      $domainStatus = DomainStatus::get();
      return view("{$this->diretorioPrincipal}.{$this->nameView}.my-domains", compact('data', 'principal', 'rota', 'primaryKey','domainStatus'));
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function getAdUnitPositionsPublisher($idDomain){
    
      $principal = $this->diretorioPrincipal;
      $rota = $this->nameView;
      $primaryKey = $this->primaryKey;

      $data = AdUnitRoot::join('ad_unit','ad_unit.id_ad_unit_root','ad_unit_root.id_ad_unit_root')
      ->join('domain','domain.id_domain', 'ad_unit_root.id_domain')
      ->where('ad_unit_root.id_domain', $idDomain)
      ->where('domain.id_user', Auth::user()->id)
      ->whereNotNull('ad_unit.position')
      ->selectRaw('ad_unit.*, domain.file_do')
      ->get();

      

      if(Auth::user()->status_full_access != 1){
        $domain = $this->model
        ->where('id_user',Auth::user()->id)
        ->where('id_domain',$idDomain)
        ->firstOrFail();
      } else {
        $domain = $this->model
      ->where('id_domain',$idDomain)
      ->firstOrFail();
      }

      $block = true;
      $bids = [];

      $function = '-publisher';

      // Gera script
      $this->getNewAds($idDomain);
      

      return view("{$this->diretorioPrincipal}.{$this->nameView}.ad-unit-positions-publisher", compact('data','bids','domain','block','function','principal','rota','primaryKey','idDomain'));
   
  }
  
  public function getPinto(){
    $this->getNewAdsAll();
  }

  public function getNewAdsAll(){
    $data = AdUnitRoot::get('id_domain');
    foreach ($data as $d) {
      
      $adUnits = AdUnit::join('ad_unit_root','ad_unit_root.id_ad_unit_root','ad_unit.id_ad_unit_root')
                    ->selectRaw('ad_unit_root_code, ad_unit_code')                  
                    ->where('ad_unit_root.id_domain',$d->id_domain)
                    ->where('ad_unit.ad_unit_status','ACTIVE')
                    ->first();
      if($adUnits){
        $this->getNewAds($d->id_domain);
        echo $d->id_domain.' - Gerado<br />';
      }
    }
    die();
  }

  public function getNewAds($idDomain){

    $adUnits = AdUnit::join('ad_unit_root','ad_unit_root.id_ad_unit_root','ad_unit.id_ad_unit_root')
                    ->selectRaw('ad_unit_root_code, ad_unit_code')                  
                    ->where('ad_unit_root.id_domain',$idDomain)
                    ->where('ad_unit.ad_unit_status','ACTIVE')
                    ->first();


      $dominio = $adUnits->ad_unit_root_code;
      $infos = explode('_',$adUnits->ad_unit_code);
      $prename = $infos[0];
      $last = $infos[(count($infos)-1)];

      $network = '22088551542';
      $date = $last;
      $domain = $dominio;
      $subdomain = $prename;

      $file = 'var $jscomp=$jscomp||{};$jscomp.scope={};$jscomp.createTemplateTagFirstArg=function(a){return a.raw=a};$jscomp.createTemplateTagFirstArgWithRaw=function(a,b){a.raw=b;return a};$jscomp.ASSUME_ES5=!1;$jscomp.ASSUME_NO_NATIVE_MAP=!1;$jscomp.ASSUME_NO_NATIVE_SET=!1;$jscomp.SIMPLE_FROUND_POLYFILL=!1;$jscomp.ISOLATE_POLYFILLS=!1;$jscomp.FORCE_POLYFILL_PROMISE=!1;$jscomp.FORCE_POLYFILL_PROMISE_WHEN_NO_UNHANDLED_REJECTION=!1;
      $jscomp.defineProperty=$jscomp.ASSUME_ES5||"function"==typeof Object.defineProperties?Object.defineProperty:function(a,b,c){if(a==Array.prototype||a==Object.prototype)return a;a[b]=c.value;return a};$jscomp.getGlobal=function(a){a=["object"==typeof globalThis&&globalThis,a,"object"==typeof window&&window,"object"==typeof self&&self,"object"==typeof global&&global];for(var b=0;b<a.length;++b){var c=a[b];if(c&&c.Math==Math)return c}throw Error("Cannot find global object");};$jscomp.global=$jscomp.getGlobal(this);
      $jscomp.IS_SYMBOL_NATIVE="function"===typeof Symbol&&"symbol"===typeof Symbol("x");$jscomp.TRUST_ES6_POLYFILLS=!$jscomp.ISOLATE_POLYFILLS||$jscomp.IS_SYMBOL_NATIVE;$jscomp.polyfills={};$jscomp.propertyToPolyfillSymbol={};$jscomp.POLYFILL_PREFIX="$jscp$";var $jscomp$lookupPolyfilledValue=function(a,b){var c=$jscomp.propertyToPolyfillSymbol[b];if(null==c)return a[b];c=a[c];return void 0!==c?c:a[b]};
      $jscomp.polyfill=function(a,b,c,d){b&&($jscomp.ISOLATE_POLYFILLS?$jscomp.polyfillIsolated(a,b,c,d):$jscomp.polyfillUnisolated(a,b,c,d))};$jscomp.polyfillUnisolated=function(a,b,c,d){c=$jscomp.global;a=a.split(".");for(d=0;d<a.length-1;d++){var f=a[d];if(!(f in c))return;c=c[f]}a=a[a.length-1];d=c[a];b=b(d);b!=d&&null!=b&&$jscomp.defineProperty(c,a,{configurable:!0,writable:!0,value:b})};
      $jscomp.polyfillIsolated=function(a,b,c,d){var f=a.split(".");a=1===f.length;d=f[0];d=!a&&d in $jscomp.polyfills?$jscomp.polyfills:$jscomp.global;for(var k=0;k<f.length-1;k++){var e=f[k];if(!(e in d))return;d=d[e]}f=f[f.length-1];c=$jscomp.IS_SYMBOL_NATIVE&&"es6"===c?d[f]:null;b=b(c);null!=b&&(a?$jscomp.defineProperty($jscomp.polyfills,f,{configurable:!0,writable:!0,value:b}):b!==c&&($jscomp.propertyToPolyfillSymbol[f]=$jscomp.IS_SYMBOL_NATIVE?$jscomp.global.Symbol(f):$jscomp.POLYFILL_PREFIX+f,f=
      $jscomp.propertyToPolyfillSymbol[f],$jscomp.defineProperty(d,f,{configurable:!0,writable:!0,value:b})))};$jscomp.underscoreProtoCanBeSet=function(){var a={a:!0},b={};try{return b.__proto__=a,b.a}catch(c){}return!1};$jscomp.setPrototypeOf=$jscomp.TRUST_ES6_POLYFILLS&&"function"==typeof Object.setPrototypeOf?Object.setPrototypeOf:$jscomp.underscoreProtoCanBeSet()?function(a,b){a.__proto__=b;if(a.__proto__!==b)throw new TypeError(a+" is not extensible");return a}:null;
      $jscomp.arrayIteratorImpl=function(a){var b=0;return function(){return b<a.length?{done:!1,value:a[b++]}:{done:!0}}};$jscomp.arrayIterator=function(a){return{next:$jscomp.arrayIteratorImpl(a)}};$jscomp.makeIterator=function(a){var b="undefined"!=typeof Symbol&&Symbol.iterator&&a[Symbol.iterator];return b?b.call(a):$jscomp.arrayIterator(a)};$jscomp.generator={};
      $jscomp.generator.ensureIteratorResultIsObject_=function(a){if(!(a instanceof Object))throw new TypeError("Iterator result "+a+" is not an object");};$jscomp.generator.Context=function(){this.isRunning_=!1;this.yieldAllIterator_=null;this.yieldResult=void 0;this.nextAddress=1;this.finallyAddress_=this.catchAddress_=0;this.finallyContexts_=this.abruptCompletion_=null};
      $jscomp.generator.Context.prototype.start_=function(){if(this.isRunning_)throw new TypeError("Generator is already running");this.isRunning_=!0};$jscomp.generator.Context.prototype.stop_=function(){this.isRunning_=!1};$jscomp.generator.Context.prototype.jumpToErrorHandler_=function(){this.nextAddress=this.catchAddress_||this.finallyAddress_};$jscomp.generator.Context.prototype.next_=function(a){this.yieldResult=a};
      $jscomp.generator.Context.prototype.throw_=function(a){this.abruptCompletion_={exception:a,isException:!0};this.jumpToErrorHandler_()};$jscomp.generator.Context.prototype.return=function(a){this.abruptCompletion_={return:a};this.nextAddress=this.finallyAddress_};$jscomp.generator.Context.prototype.jumpThroughFinallyBlocks=function(a){this.abruptCompletion_={jumpTo:a};this.nextAddress=this.finallyAddress_};$jscomp.generator.Context.prototype.yield=function(a,b){this.nextAddress=b;return{value:a}};
      $jscomp.generator.Context.prototype.yieldAll=function(a,b){a=$jscomp.makeIterator(a);var c=a.next();$jscomp.generator.ensureIteratorResultIsObject_(c);if(c.done)this.yieldResult=c.value,this.nextAddress=b;else return this.yieldAllIterator_=a,this.yield(c.value,b)};$jscomp.generator.Context.prototype.jumpTo=function(a){this.nextAddress=a};$jscomp.generator.Context.prototype.jumpToEnd=function(){this.nextAddress=0};
      $jscomp.generator.Context.prototype.setCatchFinallyBlocks=function(a,b){this.catchAddress_=a;void 0!=b&&(this.finallyAddress_=b)};$jscomp.generator.Context.prototype.setFinallyBlock=function(a){this.catchAddress_=0;this.finallyAddress_=a||0};$jscomp.generator.Context.prototype.leaveTryBlock=function(a,b){this.nextAddress=a;this.catchAddress_=b||0};
      $jscomp.generator.Context.prototype.enterCatchBlock=function(a){this.catchAddress_=a||0;a=this.abruptCompletion_.exception;this.abruptCompletion_=null;return a};$jscomp.generator.Context.prototype.enterFinallyBlock=function(a,b,c){c?this.finallyContexts_[c]=this.abruptCompletion_:this.finallyContexts_=[this.abruptCompletion_];this.catchAddress_=a||0;this.finallyAddress_=b||0};
      $jscomp.generator.Context.prototype.leaveFinallyBlock=function(a,b){b=this.finallyContexts_.splice(b||0)[0];if(b=this.abruptCompletion_=this.abruptCompletion_||b){if(b.isException)return this.jumpToErrorHandler_();void 0!=b.jumpTo&&this.finallyAddress_<b.jumpTo?(this.nextAddress=b.jumpTo,this.abruptCompletion_=null):this.nextAddress=this.finallyAddress_}else this.nextAddress=a};$jscomp.generator.Context.prototype.forIn=function(a){return new $jscomp.generator.Context.PropertyIterator(a)};
      $jscomp.generator.Context.PropertyIterator=function(a){this.object_=a;this.properties_=[];for(var b in a)this.properties_.push(b);this.properties_.reverse()};$jscomp.generator.Context.PropertyIterator.prototype.getNext=function(){for(;0<this.properties_.length;){var a=this.properties_.pop();if(a in this.object_)return a}return null};$jscomp.generator.Engine_=function(a){this.context_=new $jscomp.generator.Context;this.program_=a};
      $jscomp.generator.Engine_.prototype.next_=function(a){this.context_.start_();if(this.context_.yieldAllIterator_)return this.yieldAllStep_(this.context_.yieldAllIterator_.next,a,this.context_.next_);this.context_.next_(a);return this.nextStep_()};
      $jscomp.generator.Engine_.prototype.return_=function(a){this.context_.start_();var b=this.context_.yieldAllIterator_;if(b)return this.yieldAllStep_("return"in b?b["return"]:function(c){return{value:c,done:!0}},a,this.context_.return);this.context_.return(a);return this.nextStep_()};
      $jscomp.generator.Engine_.prototype.throw_=function(a){this.context_.start_();if(this.context_.yieldAllIterator_)return this.yieldAllStep_(this.context_.yieldAllIterator_["throw"],a,this.context_.next_);this.context_.throw_(a);return this.nextStep_()};
      $jscomp.generator.Engine_.prototype.yieldAllStep_=function(a,b,c){try{var d=a.call(this.context_.yieldAllIterator_,b);$jscomp.generator.ensureIteratorResultIsObject_(d);if(!d.done)return this.context_.stop_(),d;var f=d.value}catch(k){return this.context_.yieldAllIterator_=null,this.context_.throw_(k),this.nextStep_()}this.context_.yieldAllIterator_=null;c.call(this.context_,f);return this.nextStep_()};
      $jscomp.generator.Engine_.prototype.nextStep_=function(){for(;this.context_.nextAddress;)try{var a=this.program_(this.context_);if(a)return this.context_.stop_(),{value:a.value,done:!1}}catch(b){this.context_.yieldResult=void 0,this.context_.throw_(b)}this.context_.stop_();if(this.context_.abruptCompletion_){a=this.context_.abruptCompletion_;this.context_.abruptCompletion_=null;if(a.isException)throw a.exception;return{value:a.return,done:!0}}return{value:void 0,done:!0}};
      $jscomp.generator.Generator_=function(a){this.next=function(b){return a.next_(b)};this.throw=function(b){return a.throw_(b)};this.return=function(b){return a.return_(b)};this[Symbol.iterator]=function(){return this}};$jscomp.generator.createGenerator=function(a,b){b=new $jscomp.generator.Generator_(new $jscomp.generator.Engine_(b));$jscomp.setPrototypeOf&&a.prototype&&$jscomp.setPrototypeOf(b,a.prototype);return b};
      $jscomp.asyncExecutePromiseGenerator=function(a){function b(d){return a.next(d)}function c(d){return a.throw(d)}return new Promise(function(d,f){function k(e){e.done?d(e.value):Promise.resolve(e.value).then(b,c).then(k,f)}k(a.next())})};$jscomp.asyncExecutePromiseGeneratorFunction=function(a){return $jscomp.asyncExecutePromiseGenerator(a())};$jscomp.asyncExecutePromiseGeneratorProgram=function(a){return $jscomp.asyncExecutePromiseGenerator(new $jscomp.generator.Generator_(new $jscomp.generator.Engine_(a)))};
      $jscomp.initSymbol=function(){};$jscomp.polyfill("Symbol",function(a){if(a)return a;var b=function(f,k){this.$jscomp$symbol$id_=f;$jscomp.defineProperty(this,"description",{configurable:!0,writable:!0,value:k})};b.prototype.toString=function(){return this.$jscomp$symbol$id_};var c=0,d=function(f){if(this instanceof d)throw new TypeError("Symbol is not a constructor");return new b("jscomp_symbol_"+(f||"")+"_"+c++,f)};return d},"es6","es3");$jscomp.initSymbolIterator=function(){};
      $jscomp.polyfill("Symbol.iterator",function(a){if(a)return a;a=Symbol("Symbol.iterator");for(var b="Array Int8Array Uint8Array Uint8ClampedArray Int16Array Uint16Array Int32Array Uint32Array Float32Array Float64Array".split(" "),c=0;c<b.length;c++){var d=$jscomp.global[b[c]];"function"===typeof d&&"function"!=typeof d.prototype[a]&&$jscomp.defineProperty(d.prototype,a,{configurable:!0,writable:!0,value:function(){return $jscomp.iteratorPrototype($jscomp.arrayIteratorImpl(this))}})}return a},"es6",
      "es3");$jscomp.initSymbolAsyncIterator=function(){};$jscomp.iteratorPrototype=function(a){a={next:a};a[Symbol.iterator]=function(){return this};return a};
      $jscomp.polyfill("Promise",function(a){function b(){this.batch_=null}function c(e){return e instanceof f?e:new f(function(g,h){g(e)})}if(a&&!($jscomp.FORCE_POLYFILL_PROMISE||$jscomp.FORCE_POLYFILL_PROMISE_WHEN_NO_UNHANDLED_REJECTION&&"undefined"===typeof $jscomp.global.PromiseRejectionEvent))return a;b.prototype.asyncExecute=function(e){if(null==this.batch_){this.batch_=[];var g=this;this.asyncExecuteFunction(function(){g.executeBatch_()})}this.batch_.push(e)};var d=$jscomp.global.setTimeout;b.prototype.asyncExecuteFunction=
      function(e){d(e,0)};b.prototype.executeBatch_=function(){for(;this.batch_&&this.batch_.length;){var e=this.batch_;this.batch_=[];for(var g=0;g<e.length;++g){var h=e[g];e[g]=null;try{h()}catch(l){this.asyncThrow_(l)}}}this.batch_=null};b.prototype.asyncThrow_=function(e){this.asyncExecuteFunction(function(){throw e;})};var f=function(e){this.state_=0;this.result_=void 0;this.onSettledCallbacks_=[];this.isRejectionHandled_=!1;var g=this.createResolveAndReject_();try{e(g.resolve,g.reject)}catch(h){g.reject(h)}};
      f.prototype.createResolveAndReject_=function(){function e(l){return function(m){h||(h=!0,l.call(g,m))}}var g=this,h=!1;return{resolve:e(this.resolveTo_),reject:e(this.reject_)}};f.prototype.resolveTo_=function(e){if(e===this)this.reject_(new TypeError("A Promise cannot resolve to itself"));else if(e instanceof f)this.settleSameAsPromise_(e);else{a:switch(typeof e){case "object":var g=null!=e;break a;case "function":g=!0;break a;default:g=!1}g?this.resolveToNonPromiseObj_(e):this.fulfill_(e)}};f.prototype.resolveToNonPromiseObj_=
      function(e){var g=void 0;try{g=e.then}catch(h){this.reject_(h);return}"function"==typeof g?this.settleSameAsThenable_(g,e):this.fulfill_(e)};f.prototype.reject_=function(e){this.settle_(2,e)};f.prototype.fulfill_=function(e){this.settle_(1,e)};f.prototype.settle_=function(e,g){if(0!=this.state_)throw Error("Cannot settle("+e+", "+g+"): Promise already settled in state"+this.state_);this.state_=e;this.result_=g;2===this.state_&&this.scheduleUnhandledRejectionCheck_();this.executeOnSettledCallbacks_()};
      f.prototype.scheduleUnhandledRejectionCheck_=function(){var e=this;d(function(){if(e.notifyUnhandledRejection_()){var g=$jscomp.global.console;"undefined"!==typeof g&&g.error(e.result_)}},1)};f.prototype.notifyUnhandledRejection_=function(){if(this.isRejectionHandled_)return!1;var e=$jscomp.global.CustomEvent,g=$jscomp.global.Event,h=$jscomp.global.dispatchEvent;if("undefined"===typeof h)return!0;"function"===typeof e?e=new e("unhandledrejection",{cancelable:!0}):"function"===typeof g?e=new g("unhandledrejection",
      {cancelable:!0}):(e=$jscomp.global.document.createEvent("CustomEvent"),e.initCustomEvent("unhandledrejection",!1,!0,e));e.promise=this;e.reason=this.result_;return h(e)};f.prototype.executeOnSettledCallbacks_=function(){if(null!=this.onSettledCallbacks_){for(var e=0;e<this.onSettledCallbacks_.length;++e)k.asyncExecute(this.onSettledCallbacks_[e]);this.onSettledCallbacks_=null}};var k=new b;f.prototype.settleSameAsPromise_=function(e){var g=this.createResolveAndReject_();e.callWhenSettled_(g.resolve,
      g.reject)};f.prototype.settleSameAsThenable_=function(e,g){var h=this.createResolveAndReject_();try{e.call(g,h.resolve,h.reject)}catch(l){h.reject(l)}};f.prototype.then=function(e,g){function h(n,p){return"function"==typeof n?function(q){try{l(n(q))}catch(r){m(r)}}:p}var l,m,t=new f(function(n,p){l=n;m=p});this.callWhenSettled_(h(e,l),h(g,m));return t};f.prototype.catch=function(e){return this.then(void 0,e)};f.prototype.callWhenSettled_=function(e,g){function h(){switch(l.state_){case 1:e(l.result_);
      break;case 2:g(l.result_);break;default:throw Error("Unexpected state: "+l.state_);}}var l=this;null==this.onSettledCallbacks_?k.asyncExecute(h):this.onSettledCallbacks_.push(h);this.isRejectionHandled_=!0};f.resolve=c;f.reject=function(e){return new f(function(g,h){h(e)})};f.race=function(e){return new f(function(g,h){for(var l=$jscomp.makeIterator(e),m=l.next();!m.done;m=l.next())c(m.value).callWhenSettled_(g,h)})};f.all=function(e){var g=$jscomp.makeIterator(e),h=g.next();return h.done?c([]):new f(function(l,
      m){function t(q){return function(r){n[q]=r;p--;0==p&&l(n)}}var n=[],p=0;do n.push(void 0),p++,c(h.value).callWhenSettled_(t(n.length-1),m),h=g.next();while(!h.done)})};return f},"es6","es3");
      var monetiza={constructor:function(){},query:function(){return document.getElementById("join-ads-load")?document.getElementById("join-ads-load"):document.getElementById("ad-unit-load")},blocosX:function(){var a=[];if(document.querySelectorAll(".join-ads")){var b=document.querySelectorAll(".join-ads");b.forEach(function(c){a.push(c)})}document.querySelectorAll(".ad-unit")&&(b=document.querySelectorAll(".ad-unit"),b.forEach(function(c){a.push(c)}));return a},config:function(){return{network:"'.$network.'",date:"'.$date.'",domain:"'.$domain.'",subdomain:"'.$subdomain.'"}},debug:function(){return(document.getElementById("join-ads-load")?document.getElementById("join-ads-load"):document.getElementById("ad-unit-load")).dataset.debug},analytics:function(){function a(){dataLayer.push(arguments)}var b,c,d;return $jscomp.asyncExecutePromiseGeneratorProgram(function(f){if(1==f.nextAddress)return b=monetiza,c=document.createElement("script"),c.async=!0,c.type="text/javascript",c.src="https://www.googletagmanager.com/gtag/js?id=G-7K1FEGD034",
      d=document.getElementsByTagName("head")[0],d.insertBefore(c,d.firstChild),f.yield(b.sleep(200),2);window.dataLayer=window.dataLayer||[];a("js",new Date);a("config","G-7K1FEGD034");f.jumpToEnd()})},sleep:function(a){return new Promise(function(b){return setTimeout(b,a)})},load_gtp:function(){var a=document.createElement("script");a.async=!0;a.type="text/javascript";a.src="https://www.googletagservices.com/tag/js/gpt.js";var b=document.getElementsByTagName("head")[0];b.insertBefore(a,b.firstChild)},
      isMob:function(){if(sessionStorage.desktop)return!1;if(localStorage.mobile)return!0;var a="iphone;ipad;android;blackberry;nokia;opera mini;windows mobile;windows phone;iemobile;tablet;mobi".split(";"),b=navigator.userAgent.toLowerCase(),c;for(c in a)if(-1<b.indexOf(a[c]))return!0;return!1},generateCSS:function(){var a=document.createElement("style");a.append("\n        .adMonetiza-fixed-top {\n            position: fixed;\n            z-index: 9995;\n            top: 0;\n            text-align: center;\n            left: 50%!important;\n            transform: translate(-50%);\n        }\n        \n        .adMonetiza-fixed-bottom {\n            position: fixed;\n            z-index: 9995;\n            bottom: 0;\n            text-align: center;\n            left: 50%;\n            transform: translate(-50%);\n        }\n        \n        .adMonetiza-fixed-left {\n            position: fixed;\n            z-index: 9995;\n            left: 0;\n            text-align: center;\n            top: 50%;\n            transform: translateY(-50%);\n        }\n        \n        .adMonetiza-fixed-right {\n            position: fixed;\n            z-index: 9995;\n            right: 0;\n            text-align: center;\n            top: 50%;\n            transform: translateY(-50%);\n        }\n        \n        .native-col {\n            width: 100%;\n            max-width: 320px;\n            display: inline-block;\n            padding: 10px;\n            box-sizing: border-box;\n        }       \n       \n        .ad-unit > div,\n        .join-ads > div{\n            display:inline-block;\n        }\n\n        .ad-unit > div:after,\n        .join-ads > div:after{\n          content:\'Advertiser by \ud83d\ude80 JoinAds.me\';\n          font-weight:500;\n          width:100%;\n          display:inline-block;\n          text-align:center;\n          font-size:10px;\n          line-height:15px;\n          padding:5px 0;\n          color:#999\n        }\n        \n        .ad-unit > div,\n        .join-ads > div{\n          display:inline-block\n        }\n\n        [data-bloco=\'Fixed1\']>div:after,\n        [data-bloco=\'Fixed2\']>div:after,\n        [data-bloco=\'Fixed3\']>div:after{\n          display:none\n        }\n        \n        ");
      var b=document.getElementsByTagName("head")[0];b.insertBefore(a,b.firstChild)},sizes:function(a,b){if(a.match(/Native.*/))return"[[1, 1], \'fluid\' ]";if(a.match(/Content.*/))return"[[250, 250],[250, 360],[300, 250],[336, 280]]";if(a.match(/Sidebar.*/))return"[[250, 250],[250, 360],[300, 250],[336, 280],[120, 600],[160, 600],[300, 600]]";if(1===b){if(a.match(/Fixed.*/))return"[[320, 50]]"}else if(a.match(/Fixed.*/))return"[[728, 90]]"},elementInViewport:function(a){for(var b=a.offsetTop,c=a.offsetLeft,
      d=a.offsetWidth,f=a.offsetHeight;a.offsetParent;)a=a.offsetParent,b+=a.offsetTop,c+=a.offsetLeft;return b>=window.pageYOffset&&c>=window.pageXOffset&&b+f<=window.pageYOffset+window.innerHeight&&c+d<=window.pageXOffset+window.innerWidth},generateSlot:function(a,b){var c=monetiza.config();b=1==b?"MOBILE":"WEB";if(a.match(/Native.*/))return"/"+c.network+"/"+c.domain+"/"+c.subdomain+"_WEB_Native_Content_"+c.date;if(a.match(/Content.*/))return"/"+c.network+"/"+c.domain+"/"+c.subdomain+"_"+b+"_Square_"+
      a+"_Posts_"+c.date;if(a.match(/Sidebar.*/))return"/"+c.network+"/"+c.domain+"/"+c.subdomain+"_"+b+"_Vertical_"+a+"_Content_"+c.date;if(a.match(/Fixed.*/))return"/"+c.network+"/"+c.domain+"/"+c.subdomain+"_"+b+"_Horizontal_"+a+"_Content_"+c.date;if(a.match(/Interstitial.*/))return"/"+c.network+"/"+c.domain+"/"+c.subdomain+"_WEB_Interstitial_Content_"+c.date},get_postId:function(){var a=monetiza,b=a.query().dataset.postId,c=document.createElement("script");c.append("googletag.cmd.push(function() {");
      c.append("googletag.pubads().setTargeting(\'id_post_wp\', [\'"+b+"\']);");c.append("});");var d=document.getElementsByTagName("body")[0];d.insertBefore(c,d.firstChild);0<a.debug()&&console.debug("postId: ",b)},get_blocos:function(){var a,b;return $jscomp.asyncExecutePromiseGeneratorProgram(function(c){a=monetiza;b=a.blocosX();b.forEach(function(d,f){if("Interstitial"===d.dataset.bloco){var k=a.generateSlot(d.dataset.bloco,d.dataset.mobile);a.Interstitial(k)}else if("Native"==d.dataset.bloco){var e="undefined"===
      typeof d.dataset.count?6:d.dataset.count;e=parseInt(e)+1;for(var g=1;g<e;g++){k=a.generateSlot(d.dataset.bloco,d.dataset.mobile);var h=d.dataset.bloco+"_"+g;b[f].appendChild(a.newBlock(k,h,0,"native-col"))}}else k=a.generateSlot(d.dataset.bloco,d.dataset.mobile),h=d.dataset.bloco,e=d.dataset.mobile,a.isMob()?0<e&&b[f].appendChild(a.newBlock(k,h,1,d.dataset.sizes)):1>e&&b[f].appendChild(a.newBlock(k,h,0,d.dataset.sizes)),monetizado=1});c.jumpToEnd()})},newBlock:function(a,b,c,d,f){var k=monetiza,e=
      k.sizes(b,c);c=document.createElement("div");"undefined"!==typeof d&&(e=d);"undefined"!==typeof f&&(c.className=f);d=document.createElement("script");d.append("var "+b+"_slot;");d.append("googletag.cmd.push(function() {");d.append("var mapping = googletag.sizeMapping().addSize([0, 0], "+e+").build();");d.append(""+b+\'_slot = googletag.defineSlot("\'+a+\'",[[0, 0]], "\'+b+\'")\');d.append(".defineSizeMapping(mapping)");d.append(".setCollapseEmptyDiv(true)");d.append(".addService(googletag.pubads());");
      d.append("googletag.pubads().enableLazyLoad({");d.append("fetchMarginPercent: 200,");d.append("renderMarginPercent: 100,");d.append("mobileScaling: 2.0");d.append("});");d.append("googletag.enableServices();");d.append("});");c.appendChild(d);d=document.createElement("script");d.append(\'googletag.cmd.push(function() { googletag.display("\'+b+\'"); });\');f=document.createElement("div");f.id=b;f.appendChild(d);c.appendChild(f);0<k.debug()&&console.debug("Slot: ",a);return c},validaRefresh:function(){var a,
      b;return $jscomp.asyncExecutePromiseGeneratorProgram(function(c){if(1==c.nextAddress)return a=monetiza,c.yield(a.sleep(3E4),2);b=a.blocosX();b.forEach(function(d,f){if(a.elementInViewport(d)&&"Interstitial"!==d.dataset.bloco)if("Native"===d.dataset.bloco){d="undefined"===typeof d.dataset.count?6:d.dataset.count;d=parseInt(d)+1;for(var k=1;k<d;k++)f=eval("Native_"+k+"_slot"),googletag.pubads().refresh([f]),0<a.debug()&&console.debug("Refresh: ","Native_"+k+"_slot")}else f=eval(d.dataset.bloco+"_slot"),
      googletag.pubads().refresh([f]),0<a.debug()&&console.debug("Refresh: ",d.dataset.bloco+"_slot")});a.validaRefresh();c.jumpToEnd()})},validaRefreshNative:function(){var a,b;return $jscomp.asyncExecutePromiseGeneratorProgram(function(c){if(1==c.nextAddress)return a=monetiza,c.yield(a.sleep(3E4),2);b=a.blocosX();b.forEach(function(d,f){if("Native"===d.dataset.bloco)for(d="undefined"===typeof d.dataset.count?6:d.dataset.count,d=parseInt(d)+1,f=1;f<d;f++)if(a.elementInViewport(document.getElementById("Native_"+
      f))){var k=eval("Native_"+f+"_slot");googletag.pubads().refresh([k]);0<debug_blocos&&console.debug("Refresh: ","Native_"+f+"_slot")}});a.validaRefreshNative();c.jumpToEnd()})},Interstitial:function(a){var b=document.createElement("script");b.append("window.googletag = window.googletag || {cmd: []};");b.append("googletag.cmd.push(function() {");b.append("var slot = googletag.defineOutOfPageSlot(\'"+a+"\', googletag.enums.OutOfPageFormat.INTERSTITIAL);");b.append("if (slot) slot.addService(googletag.pubads());");
      b.append("googletag.enableServices();");b.append("googletag.display(slot);");b.append("});");a=document.getElementsByTagName("head")[0];a.insertBefore(b,a.firstChild)},start:function(){var a;return $jscomp.asyncExecutePromiseGeneratorProgram(function(b){switch(b.nextAddress){case 1:return a=monetiza,a.generateCSS(),0<a.debug()&&console.info("------------------ Load GPT ------------------------ ",new Date),a.load_gtp(),b.yield(a.sleep(500),2);case 2:return 0<a.debug()&&console.info("------------------ Load Slots ---------------------- ",
      new Date),a.get_blocos(),b.yield(a.sleep(100),3);case 3:return 0<a.debug()&&console.info("------------------ Load Refresh -------------------- ",new Date),a.validaRefresh(),a.validaRefreshNative(),b.yield(a.sleep(100),4);case 4:return 0<a.debug()&&console.info("------------------ Load Analytics ------------------ ",new Date),a.analytics(),b.yield(a.sleep(3E3),5);case 5:0<a.debug()&&console.info("------------------ Load PostId --------------------- ",new Date),a.get_postId(),b.jumpToEnd()}})}};
      window.onload=monetiza.start;';

      $fileX = 'scripts/ads'.$idDomain.'.js';
      $fileI = 'scripts/adin'.$idDomain.'.txt';
      
      if (!file_exists($fileX)) {
        touch($fileX);
      }
      
      if (!file_exists($fileI)) {
        touch($fileI);
      }     
      
      $test = file_get_contents('model.txt');
      $test = unserialize(base64_decode($test));
      $test['h']['code'] = str_replace('https://office.joinads.me/scripts/ads805.js','https://office.joinads.me/'.$fileX,$test['h']['code']);
      $test = serialize($test);
      $test = base64_encode($test);
      
      $save  = file_put_contents('scripts/ads'.$idDomain.'.js',$file);
      $save2 = file_put_contents('scripts/adin'.$idDomain.'.txt',$test);

      return $save;
  }

  public function postAdUnitPositionsPublisher($idDomain){
    if (Defender::hasPermission("domain/my-domains")) {

      $domain = Domain::find($idDomain);
      if(isset($domain->id_domain)){
        $dadosForm = $this->request->only('update');
        foreach ($dadosForm['update'] as $key => $data) {
          AdUnit::find($key)->update($data);
        }
        return redirect("{$this->diretorioPrincipal}/{$this->nameView}/ad-unit-positions/{$idDomain}");
      }else{
        return redirect("/{$this->diretorioPrincipal}");
      }
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }


  public function getCreate() {
    
    $roles = User::join('role_user','role_user.user_id','users.id')
                // ->where('role_user.role_id', 3)
                ->where('role_user.user_id', Auth::user()->id)
                ->pluck('role_id');
    $role = 0;
    foreach ($roles as $xrole) {
      $role = $xrole;
    }
    

    // if (Defender::hasPermission("{$this->nameView}")) {
      $principal = $this->diretorioPrincipal;
      $rota = $this->nameView;
      $primaryKey = $this->primaryKey;

      $domainStatuss= DomainStatus::get();
      $domainCategorys = DomainCategory::get();
      $users = User::get();

      $accountManager = User::join('role_user','role_user.user_id','users.id')
      ->where('role_user.role_id', 3)
      ->paginate(5);
      $prebidVersions = PrebidVersion::get();

      $user = Auth::user()->id;

      return view("{$this->diretorioPrincipal}.{$this->nameView}.create-edit", compact('principal','prebidVersions','accountManager', 'domainStatuss','domainCategorys','users', 'rota', 'primaryKey','role','user'));
    // } else {
    //   return redirect("/{$this->diretorioPrincipal}");
    // }
  }

  public function postStore() {
    // if (Defender::hasPermission("{$this->nameView}")) {
      $dadosForm = $this->request->all();
      $validator = $this->validator->make($dadosForm, $this->model->rules);
      if ($validator->fails()) {
        return redirect("/{$this->diretorioPrincipal}/{$this->nameView}/create")->withErrors($validator)->withInput();
      }
      $this->model->create($dadosForm);
      return redirect("/{$this->diretorioPrincipal}/{$this->nameView}");
    // } else {
    //   return redirect("/{$this->diretorioPrincipal}");
    // }
  }


  public function postAdstxt(){
    function getURL($url){
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_TIMEOUT, 1);
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
      if(FALSE === ($retval = curl_exec($ch))) {
        error_log(curl_error($ch));
      } else {
        return $retval;
      }
    }

    $arrContextOptions=array(
        "ssl"=>array(
            "verify_peer"=>false,
            "verify_peer_name"=>false,
        ),
    );  
  
    $dadosForm = $this->request->all();
    $dominio = 'https://'.$dadosForm['domain'].'/ads.txt';
    $resultado = getURL($dominio);

    if(strstr($resultado,'pub-4273737718115653')){
      echo json_encode(array('msg'=>'<i class="fa fa-thumbs-up" aria-hidden="true" style="color:#136e38;font-size:40px"></i> <br /><br />O ads.txt está configurado corretamente.','status'=>1));
    } else {
      echo json_encode(array('msg'=>'<i class="fa fa-warning" aria-hidden="true" style="color:#FF0000;font-size:40px"></i> <br /><br />O ads.txt não está configurado.','status'=>2));
    }
    
    die();
  }

  public function getShow($id) {
     $roles = User::join('role_user','role_user.user_id','users.id')
                ->where('role_user.user_id', Auth::user()->id)
                ->pluck('role_id');
    $role = 0;
    foreach ($roles as $xrole) {
      $role = $xrole;
    }
    if($role != 1 && $role != 9 && $role != 10){
      $data = $this->model
                   ->where('id_user',Auth::user()->id)
                   ->where('id_domain',$id)
                   ->firstOrFail();
    } else {
      $data = $this->model->findOrFail($id);
    }

    // if (Defender::hasPermission("{$this->nameView}")) {
      
      $principal = $this->diretorioPrincipal;
      $rota = $this->nameView;
      $primaryKey = $this->primaryKey;

      $domainStatuss= DomainStatus::get();
      $domainCategorys = DomainCategory::get();
      $prebidVersions = PrebidVersion::get();
      $users = User::get();
      $accountManager = User::join('role_user','role_user.user_id','users.id')
      ->whereIn('role_user.role_id', [3,9,10])
      ->paginate(5);

      $domainStatusSelected = DomainStatus::find($data->id_domain_status);
      $domainCategorySelected = DomainCategory::find($data->id_domain_category);
      $userSelected = User::find($data->id_user);
      $accountManagerSelected = User::join('role_user','role_user.user_id','users.id')
      ->whereIn('role_user.role_id', [3,9,10])
      ->where('users.id', $data->id_account_manager)
      ->first();

      $prebidVersionSelected = PrebidVersion::find($data->id_prebid_version);


      return view("{$this->diretorioPrincipal}.{$this->nameView}.create-edit", compact('data','prebidVersions','prebidVersionSelected','accountManager','accountManagerSelected', 'domainStatusSelected', 'domainCategorySelected', 'userSelected', 'domainStatuss','domainCategorys','users','principal', 'rota', 'primaryKey','role'));
    // } else {
      // return redirect("/{$this->diretorioPrincipal}");
    // }
  }

  public function getScripts($idDomain) {
    if (Defender::hasPermission("{$this->nameView}")) {
      
      $dataDesktop = DomainScripts::where('id_domain',$idDomain)->where('device',1)->first();
      $dataMobile = DomainScripts::where('id_domain',$idDomain)->where('device',2)->first();

      $principal = $this->diretorioPrincipal;
      $rota = $this->nameView;
      $primaryKey = $this->primaryKey;

      return view("{$this->diretorioPrincipal}.{$this->nameView}.scripts", compact('dataMobile','dataDesktop', 'idDomain','principal', 'rota', 'primaryKey'));
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function postScripts($idDomain){
    if (Defender::hasPermission("{$this->nameView}")) {
      $dadosForm = $this->request->all();
      
      DomainScripts::where('id_domain',$idDomain)->where('device',1)->update($dadosForm['devices']['desktop']);
      DomainScripts::where('id_domain',$idDomain)->where('device',2)->update($dadosForm['devices']['mobile']);

      return redirect("{$this->diretorioPrincipal}/{$this->nameView}/scripts/{$idDomain}");
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function getAdUnitPositions($idDomain){
    if (Defender::hasPermission("{$this->nameView}")) {
      $principal = $this->diretorioPrincipal;
      $rota = $this->nameView;
      $primaryKey = $this->primaryKey;

      $data = AdUnitRoot::join('ad_unit','ad_unit.id_ad_unit_root','ad_unit_root.id_ad_unit_root')
      ->where('ad_unit_root.id_domain', $idDomain)
      ->selectRaw('ad_unit.*')
      ->get();
      $function = '';

      $adUnitIds = [];
      foreach($data as $adUnit){
        $adUnitIds[] =  $adUnit->id_ad_unit;
      }

      $bids = PrebidBids::get();
      $bidsSelected = AdUnitBid::whereIn('id_ad_unit', $adUnitIds)->get();
      $domain = $this->model->find($idDomain);

      return view("{$this->diretorioPrincipal}.{$this->nameView}.ad-unit-positions", compact('data','bids','bidsSelected','domain','function','principal','rota','primaryKey','idDomain'));
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function postAdUnitPositions($idDomain){
    if (Defender::hasPermission("{$this->nameView}")) {
      $dadosForm = $this->request->only('update');
      //dd($dadosForm);
      foreach ($dadosForm['update'] as $key => $data) {

        AdUnitBid::where('id_ad_unit', $key)->delete();
        if(isset($data['bids'])){
          foreach($data['bids'] as $bid){
            $adUnitBid['id_ad_unit'] = $key;
            $adUnitBid['id_prebid_bids'] = $bid;
            AdUnitBid::create($adUnitBid);
            unset($adUnitBid);
          }
        }

        AdUnit::find($key)->update($data);
      }
      return redirect("{$this->diretorioPrincipal}/{$this->nameView}/ad-unit-positions/{$idDomain}");
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function getChangeStatusChecklist($idDomain){
    $this->model->find($idDomain)->update(['status_checklist' => 1]);
    return 1;
  }

  public function getClient($idClient) {
    if (Defender::hasPermission("{$this->nameView}")) {
      $data = $this->model->join('users','users.id','domain.id_user')
      ->where('domain.id_user',$idClient)
      ->selectRaw('domain.*, users.name nameClient')
      ->paginate($this->totalItensPorPagina);

      $principal = $this->diretorioPrincipal;
      $primaryKey = $this->primaryKey;
      $rota = $this->nameView;
      return view("{$this->diretorioPrincipal}.{$this->nameView}.index", compact('data', 'principal', 'rota', 'primaryKey'));
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function getDownloadAdUnits($idDomain){
    $data = $this->model->join('ad_unit_root','ad_unit_root.id_domain','domain.id_domain')
    ->join('ad_unit','ad_unit.id_ad_unit_root','ad_unit_root.id_ad_unit_root')
    ->where('domain.id_domain', $idDomain)
    ->get();

    $maior = 0;
    $cont = 0;
    $File = '///////////////////HEADER////////////////////////////////
            <script async src="https://securepubads.g.doubleclick.net/tag/js/gpt.js"></script>
            <script>var googletag = window.googletag || {cmd: []};</script>';

    foreach($data as $dado){

      $uniq_id = uniqid();

      if($dado->position == 'fixedMobile' && $dado->device == 2){
        $bloco = '<link rel = "stylesheet" type = "text/css"href = "https://beetads.com/externo/css/beetadsstyle.css"/>
        <script src="https://beetads.com/externo/js/beetadsscript.js"></script>

        <div class="d-block md-hidden lg-hidden xl-hidden beetmobilefixed beetmobilefixedtobottom" id="beetmobilefixed">
        <div class="w-100 text-left" style="margin-left: 10px;">
        <span onclick="beet_admob_close(); beet_admob_class_remove();" style="font-size: 30px;">×</span>
        </div>

        <script>var googletag = window.googletag || {cmd: []};</script>
        <script async src="//www.googletagservices.com/tag/js/gpt.js"></script>
        <script>
        googletag.cmd.push(function() {
          var REFRESH_KEY = "refresh";
          var REFRESH_VALUE = "true";
          googletag.defineSlot("/22088551542/'.$dado->ad_unit_root_code.'/'.$dado->ad_unit_code.'",'.$dado->sizes.', "'.$uniq_id.'").
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

        <div id="'.$uniq_id.'">
          <script>
            googletag.cmd.push(function() {googletag.display("'.$uniq_id.'");});
          </script>
        </div>
        </div>';
      }else{

        if(strstr($dado->ad_unit_code,'Interstitial')){

          $bloco = '<script>
          
          googletag.cmd.push(function() {
            var slot = googletag.defineOutOfPageSlot(
              \'/22088551542/'.$dado->ad_unit_root_code.'/'.$dado->ad_unit_code.'\',          
              googletag.enums.OutOfPageFormat.INTERSTITIAL);
            if (slot) slot.addService(googletag.pubads());
            googletag.enableServices();
            googletag.display(slot);
        });
        
        </script>';

        } else {

              $bloco ='<script>
              googletag.cmd.push(function() {
                googletag.defineSlot("/22088551542/'.$dado->ad_unit_root_code.'/'.$dado->ad_unit_code.'",'.$dado->sizes.', "'.$uniq_id.'").
                addService(googletag.pubads());
                googletag.pubads().enableLazyLoad({
                  fetchMarginPercent: 200,  // Fetch slots within 5 viewports.
                  renderMarginPercent: 100,  // Render slots within 2 viewports.
                  mobileScaling: 2.0  // Double the above values on mobile.
                });
                googletag.enableServices();
              });
              </script>
              <center>
              <div id="'.$uniq_id.'">
                <script>
                  googletag.cmd.push(function() {googletag.display("'.$uniq_id.'");});
                </script>
              </div>
              </center>';
        }  
      }



      $File .= "\n\n///////////////////////////////////////////////////////////////\n\n".$bloco;
    }

    file_put_contents('assets/painel/uploads/adunits/adUnit.txt', $File);

    header("Content-type: application/zip");
    header("Content-Disposition: attachment; filename=adUnit.txt");
    header("Pragma: no-cache");
    header("Expires: 0");
    readfile("assets/painel/uploads/adunits/adUnit.txt");
  }
  
  public function getDownloadAdUnitsRefresh($idDomain){
    $data = $this->model->join('ad_unit_root','ad_unit_root.id_domain','domain.id_domain')
    ->join('ad_unit','ad_unit.id_ad_unit_root','ad_unit_root.id_ad_unit_root')
    ->where('domain.id_domain', $idDomain)
    ->get();

    $maior = 0;
    $cont = 0;
    $File = '///////////////////HEADER////////////////////////////////
            <script async src="https://securepubads.g.doubleclick.net/tag/js/gpt.js"></script>
            <script>var googletag = window.googletag || {cmd: []};</script>';

    foreach($data as $key => $dado){

      $uniq_id = uniqid();

      if(strstr($dado->ad_unit_code,'Interstitial')){

        $bloco = '<script>
                  
                  googletag.cmd.push(function() {
                    var slot = googletag.defineOutOfPageSlot(
                      \'/22088551542/'.$dado->ad_unit_root_code.'/'.$dado->ad_unit_code.'\',          
                      googletag.enums.OutOfPageFormat.INTERSTITIAL);
                    if (slot) slot.addService(googletag.pubads());
                    googletag.enableServices();
                    googletag.display(slot);
                });
                
                </script>';

      } else {

        $bloco ='<script>

        googletag.cmd.push(function() {

          // VARIABLES
          var REFRESH_KEY_'.$uniq_id.'   = \'refresh\';
          var REFRESH_VALUE_'.$uniq_id.' = \'true\';
          var SECONDS_TO_WAIT_AFTER_VIEWABILITY_'.$uniq_id.' = 30;

          // DEFINITION BLOCK
          googletag
                  .defineSlot("/22088551542/'.$dado->ad_unit_root_code.'/'.$dado->ad_unit_code.'",'.$dado->sizes.', "'.$uniq_id.'")
                  .setTargeting(REFRESH_KEY_'.$uniq_id.', REFRESH_VALUE_'.$uniq_id.')
                  .addService(googletag.pubads());

          // REFRESH
          googletag.pubads().addEventListener(\'impressionViewable\', function(event) {
            var slot_'.$uniq_id.' = event.slot;
            if (slot_'.$uniq_id.'.getTargeting(REFRESH_KEY_'.$uniq_id.').indexOf(REFRESH_VALUE_'.$uniq_id.') > -1) {
              setTimeout(function() {
                googletag.pubads().refresh([slot_'.$uniq_id.']);
                console.debug(\'REFRESH:\',"slot_'.$uniq_id.'");
              }, SECONDS_TO_WAIT_AFTER_VIEWABILITY_'.$uniq_id.' * 1000);
            }
          });
          
          // LAZY LOAD
          googletag.pubads().enableLazyLoad({
            fetchMarginPercent: 200,  
            renderMarginPercent: 100, 
            mobileScaling: 2.0  
          });

          googletag.enableServices();

        });

        </script>
        <center>
        <div id="'.$uniq_id.'">
          <script>
            googletag.cmd.push(function() {googletag.display("'.$uniq_id.'");});
          </script>
        </div>
        </center>';
      
      }

      $File .= "\n\n///////////////////////////////////////////////////////////////\n\n".$bloco;
    }

    file_put_contents('assets/painel/uploads/adunits/adUnitRefresh.txt', $File);

    header("Content-type: application/zip");
    header("Content-Disposition: attachment; filename=adUnit.txt");
    header("Pragma: no-cache");
    header("Expires: 0");
    readfile("assets/painel/uploads/adunits/adUnitRefresh.txt");
  }

  public function getMapTags($idDomain){
    if (Defender::hasPermission("{$this->nameView}")) {
      $data = $this->model->join('ad_unit_root','ad_unit_root.id_domain','domain.id_domain')
      ->join('ad_unit','ad_unit.id_ad_unit_root','ad_unit_root.id_ad_unit_root')
      ->where('domain.id_domain', $idDomain)
      ->get();

      $maior = 0;
      $cont = 0;
      $File = '';
      foreach($data as $dado){
        $sizes = explode('],[',$dado->sizes);
        foreach($sizes as $size){
          $numbers = str_replace(['[',']',' '],'',$size);
          $total = array_sum(explode(',', $numbers));
          if($cont == 0){
            $maior = $total;
            $sizeBig = $numbers;
            $cont++;
          }elseif($total > $maior){
            $maior = $total;
            $sizeBig = $numbers;
          }
        }
        $File .= $dado->ad_unit_name.";".str_replace([','],'x',$sizeBig)."\n";
      }
      file_put_contents('assets/painel/uploads/adunits/adUnit.txt', $File);
      header("Content-type: application/zip");
      header("Content-Disposition: attachment; filename=adUnit.csv");
      header("Pragma: no-cache");
      header("Expires: 0");
      readfile("assets/painel/uploads/adunits/adUnit.txt");
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function getTeste(){
    $domains = Domain::get();
    $devices = [1,2];
    foreach ($domains as $domain) {
      foreach ($devices as $device) {
        $dataForm['header'] = '<script async src="https://securepubads.g.doubleclick.net/tag/js/gpt.js"></script>
                <script>var googletag = window.googletag || {cmd: []};</script>
                <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({"gtm.start": new Date().getTime(),event:"gtm.js"});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!="dataLayer"?"&l="+l:"";j.async=true;j.src="https://www.googletagmanager.com/gtm.js?id="+i+dl;f.parentNode.insertBefore(j,f);})(window,document,"script","dataLayer","GTM-PRDTNQK")</script>
                <script> googletag.cmd.push(function(){ googletag.pubads().setTargeting("id_post_wp", "{id_post}"); }); </script>';
        $dataForm['footer'] = '';
        $dataForm['after_body'] = '<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-PRDTNQK" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>';
        $dataForm['device'] = $device;
        $dataForm['id_domain'] = $domain->id_domain;

        $saved = DomainScripts::where('device', $device)->where('id_domain', $domain->id_domain)->first();
        if(empty($saved->id_domain)){
          DomainScripts::create($dataForm);
        }
      }
    }
  }

  public function getChangeStatus($idDomain){
    if (Defender::hasPermission("{$this->nameView}")) {
      $domain = Domain::find($idDomain);
      if($domain->id_domain_status == 4){
        $domain->update(['id_domain_status' => 5]);
      }else{
        $domain->update(['id_domain_status' => 4]);
      }
      return 1;
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function UpdatePosts(){

    $domains = $this->model->get();

    foreach($domains as $domain){
      $sitemap_URL = "http://$domain->name/sitemap.xml";
      $result = json_decode(json_encode(@simplexml_load_file($sitemap_URL) ), TRUE);

      if(isset($result['sitemap'])){
        $items = $result['sitemap'];
      }else if(isset($result['url'])){
        $items = $result['url'];
      }else{
        $items = [];
      }

      $cont = 0;
      foreach($items as $item){
        if(isset($item['lastmod'])){
            if($cont == 0){
                $lastUpdate[$domain->id_domain] = date('Y-m-d', strtotime($item['lastmod']));
                $cont++;
            }
            if(date('Y-m-d', strtotime($item['lastmod'])) > date('Y-m-d', strtotime($lastUpdate[$domain->id_domain])) ){
              $lastUpdate[$domain->id_domain] = date('Y-m-d', strtotime($item['lastmod']));
            }
          }
          unset($items);
        }
    }

    foreach($lastUpdate as $key => $value){
      $this->model->find($key)->update(['posted_at' => $value]);
    }
  }

  public function getDigitalOcean($idDomain, $hash = "", $version = ""){
    if (Defender::hasPermission("{$this->nameView}")) {

      $adUnits = AdUnitRoot::join('ad_unit','ad_unit.id_ad_unit_root','ad_unit_root.id_ad_unit_root')
      ->where('ad_unit_root.id_domain', $idDomain)
      //->whereRaw('ad_unit_name LIKE "%_WEB_%"')
      ->whereNotNull('position')
      ->selectRaw('ad_unit.*, ad_unit_root.ad_unit_root_code')
      ->get();
      // foreach($adUnits as $adUnit){
      //   echo $adUnit->sizes."<br>";
      // }

      $positionsAllowCheck = ['Position_Content1','Position_Content2','Position_Content3','Position_Content4','Position_Content5'];
      $positionsAdUnitsDesktop = [];
      $positionsAdUnitsMobile = [];

      $bids = PrebidBids::where('enable',1)->get();
      $placement = PrebidPlacement::join('prebid_bids','prebid_bids.id_prebid_bids', 'prebid_placement.id_prebid_bids')->where('enable',1)->get();
      $content['desktop'] = '';
      $content['mobile'] = '';

      //DESKTOP
      foreach ($adUnits as $adUnit) {

        $bidsSelected = AdUnitBid::join('ad_unit', 'ad_unit.id_ad_unit', 'ad_unit_bid.id_ad_unit')
                        ->join('ad_unit_root','ad_unit_root.id_ad_unit_root','ad_unit.id_ad_unit_root')
                        ->where('ad_unit_root.id_domain',$idDomain)
                        ->where('ad_unit.id_ad_unit',$adUnit->id_ad_unit)
                        ->selectRAW('ad_unit_bid.id_prebid_bids')
                        ->groupBy('ad_unit_bid.id_prebid_bids')
                        ->get();
        $idBidsAdUnit = [];
        foreach($bidsSelected as $bid){
          $idBidsAdUnit[] = $bid->id_prebid_bids;
        }

        $placement = PrebidPlacement::join('prebid_bids','prebid_bids.id_prebid_bids', 'prebid_placement.id_prebid_bids')
        ->whereIn('prebid_bids.id_prebid_bids', $idBidsAdUnit)
        ->where('enable',1)
        ->get();
        unset($idBidsAdUnit);

        $array = explode('_', $adUnit->ad_unit_code);
        if(in_array('WEB', $array)){
          $tem = false;
          foreach($bids as $bid){
            foreach($placement as $key => $placem){
              if($placem->id_prebid_bids == $bid->id_prebid_bids && $placem->slot_sizes == str_replace(' ','',$adUnit->sizes)){
                $tem = true;
              }
            }
          }

          if($tem == true){

                  if(!empty($adUnit->id_div)){
                    $uniqId = $adUnit->id_div;
                    $return['divId'][] = $adUnit->id_div;
                  }else{
                    $uniqId = "Position_".explode('_', $adUnit->ad_unit_code)[3];
                    $return['divId'][] = "Position_".$uniqId;
                  }
                    if(in_array($uniqId, $positionsAllowCheck) && $adUnit->position_element != null){
                      if(!in_array($uniqId, $positionsAdUnitsDesktop)){
                        $positionsAdUnitsDesktop[] = "'".$adUnit->position_element."x".$uniqId."'";
                      }
                    }

                    if($adUnit->lazyload == 1){
                      $lazy_loading = 1;
                      $lazy_loading_offset = 200;
                    }else{
                      $lazy_loading = 0;
                      $lazy_loading_offset = 0;
                    }

                    $refreh = $adUnit->refresh;


                    $content['desktop'] .= '{
                                  "hbm_zone": {
                                  "userid": 381,
                                  "websiteid": 1093,
                                  "zoneid": '.$adUnit->ad_unit_id.',
                                  "lazy_loading": '.$lazy_loading.',
                                  "lazy_loading_offset": '.$lazy_loading_offset.',
                                  "refresh": '.$refreh.',
                                  "refresh_limit": 0,
                                  "nontracked": 0,
                                  "outofpage": 0,
                                  "slot_code": "/22088551542/'.$adUnit->ad_unit_root_code.'/'.$adUnit->ad_unit_code.'",
                                  "slot_sizes": '.$adUnit->sizes.'
                              },
                              "code": "'.$uniqId.'",
                              "mediaTypes": {
                                  "banner": {
                                      "sizes": '.$adUnit->sizes.'
                                  }
                              },
                              "bids": [';

                              foreach($bids as $bid){
                                $cont = 0;
                                foreach($placement as $key => $placem){
                                  if($placem->id_prebid_bids == $bid->id_prebid_bids && $placem->slot_sizes == str_replace(' ','',$adUnit->sizes) && $cont == 0){

                                     $deleteKey = $key;
                                     $params = '';

                                     if(isset($placem->zoneId)){ $params .= '"zoneId": '.$placem->zoneId.',';  }
                                     if(isset($placem->placement)){ $params .= '"placement": "'.$placem->placement.'",';  }
                                     if(isset($bid->network)){ $params .= '"network": "'.$bid->network.'"'.',';  }
                                     if(isset($bid->bid_floor)){ $params .= '"bidFloor": "'.$bid->bid_floor.'"'.',';  }
                                     if(isset($placem->publisherId)){ $params .= '"publisherId": "'.$placem->publisherId.'",';  }
                                     if(isset($placem->placementId)){ $params .= '"placementId": "'.$placem->placementId.'",';  }
                                     if(isset($bid->reserve)){ $params .= '"reserve": '.$bid->reserve.',';  }
                                     if(isset($placem->region)){ $params .= '"region": "'.$placem->region.'",';  }
                                     if(isset($placem->pageId)){ $params .= '"pageId": '.$placem->pageId.',';  }

                                      $content['desktop'] .= '
                                      {
                                          "bidder": "'.$bid->bidder.'",
                                          "params": {
                                              '.rtrim($params,',').'
                                          }
                                      },';

                                      unset($params);
                                      $tem = true;
                                       $cont++;
                                  }
                                }
                                if(isset($deleteKey)){
                                    $placement->forget($deleteKey);
                                    unset($deleteKey);
                                }
                              }
                              $content['desktop'] = rtrim($content['desktop'],',');

                              $content['desktop'] .= '
                              ]
                          },';
                          unset($p);
                          $tem = false;
                }
              }
            }

            //MOBILE

            $placement = PrebidPlacement::join('prebid_bids','prebid_bids.id_prebid_bids', 'prebid_placement.id_prebid_bids')->where('enable',1)->get();

            foreach ($adUnits as $adUnit) {

              $bidsSelected = AdUnitBid::join('ad_unit', 'ad_unit.id_ad_unit', 'ad_unit_bid.id_ad_unit')
                              ->join('ad_unit_root','ad_unit_root.id_ad_unit_root','ad_unit.id_ad_unit_root')
                              ->where('ad_unit_root.id_domain',$idDomain)
                              ->where('ad_unit.id_ad_unit',$adUnit->id_ad_unit)
                              ->selectRAW('ad_unit_bid.id_prebid_bids')
                              ->groupBy('ad_unit_bid.id_prebid_bids')
                              ->get();
              $idBidsAdUnit = [];
              foreach($bidsSelected as $bid){
                $idBidsAdUnit[] = $bid->id_prebid_bids;
              }

              $placement = PrebidPlacement::join('prebid_bids','prebid_bids.id_prebid_bids', 'prebid_placement.id_prebid_bids')
              ->whereIn('prebid_bids.id_prebid_bids', $idBidsAdUnit)
              ->where('enable',1)
              ->get();
              unset($idBidsAdUnit);

              $array = explode('_', $adUnit->ad_unit_code);
              if(in_array('MOBILE', $array)){
                $tem = false;
                foreach($bids as $bid){
                  foreach($placement as $key => $placem){
                    if($placem->id_prebid_bids == $bid->id_prebid_bids && $placem->slot_sizes == str_replace(' ','',$adUnit->sizes)){
                      $tem = true;
                    }
                  }
                }

                if($tem == true){
                        if(!empty($adUnit->id_div)){
                          $uniqId = $adUnit->id_div;
                          $return['divId'][] = $adUnit->id_div;
                        }else{
                          $uniqId = "Position_".explode('_', $adUnit->ad_unit_code)[3];
                          $return['divId'][] = "Position_".$uniqId;
                        }

                          if(in_array($uniqId, $positionsAllowCheck) && $adUnit->position_element != null){
                            if(!in_array($uniqId, $positionsAdUnitsMobile)){
                              $positionsAdUnitsMobile[] = "'".$adUnit->position_element."x".$uniqId."'";
                            }
                          }

                          $lazy_loading = 1;

                          $refreh = $adUnit->refresh;

                          if($adUnit->lazyload == 1){
                            $lazy_loading = 1;
                            $lazy_loading_offset = 200;
                          }else{
                            $lazy_loading = 0;
                            $lazy_loading_offset = 0;
                          }

                          $refreh = $adUnit->refresh;


                          $content['mobile'] .= '{
                                        "hbm_zone": {
                                        "userid": 381,
                                        "websiteid": 1093,
                                        "zoneid": '.$adUnit->ad_unit_id.',
                                        "lazy_loading": '.$lazy_loading.',
                                        "lazy_loading_offset": '.$lazy_loading_offset.',
                                        "refresh": '.$refreh.',
                                        "refresh_limit": 0,
                                        "nontracked": 0,
                                        "outofpage": 0,
                                        "slot_code": "/22088551542/'.$adUnit->ad_unit_root_code.'/'.$adUnit->ad_unit_code.'",
                                        "slot_sizes": '.$adUnit->sizes.'
                                    },
                                    "code": "'.$uniqId.'",
                                    "mediaTypes": {
                                        "banner": {
                                            "sizes": '.$adUnit->sizes.'
                                        }
                                    },
                                    "bids": [';

                                    foreach($bids as $bid){
                                      $cont = 0;
                                      foreach($placement as $key => $placem){
                                        if($placem->id_prebid_bids == $bid->id_prebid_bids && $placem->slot_sizes == str_replace(' ','',$adUnit->sizes) && $cont == 0){

                                           $deleteKey = $key;
                                           $params = '';

                                           if(isset($placem->zoneId)){ $params .= '"zoneId": '.$placem->zoneId.',';  }
                                           if(isset($placem->placement)){ $params .= '"placement": '.$placem->placement.',';  }
                                           if(isset($bid->network)){ $params .= '"network": "'.$bid->network.'"'.',';  }
                                           if(isset($bid->bid_floor)){ $params .= '"bidFloor": "'.$bid->bid_floor.'"'.',';  }
                                           if(isset($placem->publisherId)){ $params .= '"publisherId": "'.$placem->publisherId.'",';  }
                                           if(isset($placem->placementId)){ $params .= '"placementId": "'.$placem->placementId.'",';  }
                                           if(isset($bid->reserve)){ $params .= '"reserve": '.$bid->reserve.',';  }
                                           if(isset($placem->region)){ $params .= '"region": "'.$placem->region.'",';  }
                                           if(isset($placem->pageId)){ $params .= '"pageId": '.$placem->pageId.',';  }

                                            $content['mobile'] .= '
                                            {
                                                "bidder": "'.$bid->bidder.'",
                                                "params": {
                                                    '.rtrim($params,',').'
                                                }
                                            },';

                                            unset($params);
                                            $tem = true;
                                             $cont++;
                                        }
                                      }
                                      if(isset($deleteKey)){
                                          $placement->forget($deleteKey);
                                          unset($deleteKey);
                                      }
                                    }
                                    $content['mobile'] = rtrim($content['mobile'],',');

                                    $content['mobile'] .= '
                                    ]
                                },';
                                unset($p);
                                $tem = false;
                      }
                    }
                  }

      if($version == ""){
        $version = PrebidVersion::where('enabled', 1)->first()->version;
      }

      $content['desktop'] =  rtrim($content['desktop'],',');

      // if($idDomain == 271)
      // {
      //   $script = str_replace('{adUnitChangeDesktop}', $content['desktop'], file_get_contents(storage_path('/prebid/version-teste.js')));
      // }else{
        $script = str_replace('{adUnitChangeDesktop}', $content['desktop'], file_get_contents(storage_path('/prebid/version-'.$version.'.js')));
      // }


      $content['mobile'] =  rtrim($content['mobile'],',');
      $script = str_replace('{adUnitChangeMobile}', $content['mobile'], $script);

      $domain = $this->model->find($idDomain);


      //start Versão 1.3

      $recaptchaScript = "(function() {
        var styleRecaptcha = document.createElement('style');
        styleRecaptcha.type = 'text/css';
        styleRecaptcha.innerHTML = '.grecaptcha-badge { z-index: 9999 !important; bottom: 160px !important;}';
        document.getElementsByTagName('head')[0].appendChild(styleRecaptcha);

        var reCAPTCHAScript = document.createElement('script');
        reCAPTCHAScript.src = 'https://www.google.com/recaptcha/api.js?render={keyReCAPTCHA}';
        var target = document.getElementsByTagName('head')[0];
        target.insertBefore(reCAPTCHAScript, target.firstChild);
      })();";

      $recapchaExec = "  try{
          var reCAPTCHAStatus = 'inválido';
          setTimeout(function(){
            grecaptcha.ready(function() {
           		grecaptcha.execute('{keyReCAPTCHA}').then(function(token) {
                reCAPTCHAStatus = 'válido';
           		});
            });

            setTimeout(function(){
              googletag.cmd.push(function(){ googletag.pubads().setTargeting('reCAPTCHA', reCAPTCHAStatus); });
            }, 2000);
          }, 1000);


          if(document.querySelector('.status-publish').getAttribute('id')){
             googletag.cmd.push(function(){ googletag.pubads().setTargeting('id_post_wp', document.querySelector('.status-publish').getAttribute('id').split('-')[1]); });
          }
        } catch (e) {}";

      if(!empty($domain->key_recaptcha)){
        $script = str_replace('{recaptchaScript}', $recaptchaScript, $script);
        $script = str_replace('{recapchaExec}', $recapchaExec, $script);
        $script = str_replace('{keyReCAPTCHA}', $domain->key_recaptcha, $script);
      }
      // end Versão 1.3

      $script = str_replace('{positionsAdUnitsDesktop}', implode(',',$positionsAdUnitsDesktop), $script);
      $script = str_replace('{positionsAdUnitsMobile}', implode(',',$positionsAdUnitsMobile), $script);

      if($hash == ""){
        $hash = uniqid();
      }else{
        $hash = str_replace(['.js','p_'],'',$hash);
      }

      // if($idDomain == 271)
      // {
      //     $hunter = new HunterObfuscator($script);
      //     $script = $hunter->Obfuscate();
      // }

      file_put_contents("script_temp.js",$script);

      if($version == '1.2'){
        $script = $this->minify();
      }

    //   protected static $metaOptions = [
    //     'CacheControl',
    //     'Expires',
    //     'StorageClass',
    //     'ServerSideEncryption',
    //     'Metadata',
    //     'ACL',
    //     'ContentType',
    //     'ContentDisposition',
    //     'ContentLanguage',
    //     'ContentEncoding',
    // ];

      $url = Storage::disk('do_spaces')->put("/crm/".$idDomain."/p_$hash.js", $script, [
              'visibility' => 'public',
              'CacheControl' => 'max-age=60',
              'ContentType' => 'application/javascript',
              'ContentEncoding' => 'gzip'
        ]);

      $this->model->find($idDomain)->update(['file_do' => "p_$hash.js"]);

      $return['urlCDN'][] = '<script async type="text/javascript" src="https://beetadsscripts.nyc3.cdn.digitaloceanspaces.com/crm/'.$idDomain.'/p_'.$hash.'.js"> </script>';
      return json_encode($return);
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }
  //// var_dump(Storage::disk('do_spaces')->files("/"));
  public function getUpdateFilesDigitalOcean($idDomain = ""){

    if(empty($idDomain)){
      $domains = $this->model->whereNotNull('file_do')->get();
    }else{
      $domains = $this->model->where('id_domain', $idDomain)->whereNotNull('file_do')->get();
    }

    foreach ($domains as $domain) {
      $PrebidVersion = PrebidVersion::where('enabled', 1)->first();
      $this->getDigitalOcean($domain->id_domain, $domain->file_do, $PrebidVersion->version);
    }
  }


  public function postUpdate($id) {
    // if (Defender::hasPermission("{$this->nameView}")) {
      
      $dadosForm = $this->request->all();
      $validator = $this->validator->make($dadosForm, $this->model->rulesUpdate);
     
      if ($validator->fails()) {
        return redirect("/{$this->diretorioPrincipal}/{$this->nameView}/show/$id")->withErrors($validator)->withInput();
      }
      $data = $this->model->find($id);
      
      if(!empty($dadosForm['id_prebid_version']) && !empty($data->id_prebid_version)){
        if($dadosForm['id_prebid_version'] != $data->id_prebid_version){
          $PrebidVersion = PrebidVersion::find($dadosForm['id_prebid_version']);
          $this->getDigitalOcean($data->id_domain, $data->file_do, $PrebidVersion->version);
        }
      }

      

      if($dadosForm['id_domain_status'] == 4){
        $title = "Site Aprovado!";
        $msg = "Seu site foi aprovado, você pode baixar os blocos para instalação na sua dashboard ou solicitar através de um ticket para nossa equipe de desenvolvimento fazer a instalação.";
        $this->sendEmail('pagamentos', ['title'=>$title, 'informacao'=>$msg], $title, Helper::email($dadosForm['id_user']));
        DomainNotification::create(['subject'=>$title,'message'=>$msg,'id_domain'=>$id]);
      }

      if($dadosForm['id_domain_status'] == 7){
        $title = "Site Reprovado!";
        $msg = "Seu site está fora das políticas de monetização do Google Ad Exchange.";
        $this->sendEmail('pagamentos', ['title'=>$title, 'informacao'=>$msg], $title, Helper::email($dadosForm['id_user']));
        DomainNotification::create(['subject'=>$title,'message'=>$msg,'id_domain'=>$id]);
      }

      // dd($dadosForm);
      // die();
      $data->update($dadosForm);
      // return redirect("/{$this->diretorioPrincipal}/{$this->nameView}");
      return redirect("/{$this->diretorioPrincipal}/{$this->nameView}/show/$id")->with('message','Salvo');
    // } else {
      // return redirect("/{$this->diretorioPrincipal}");
    // }
  }

  public function minify(){

    //$command = storage_path('minify-google/venv/bin/python2')."  -m jsmin ".public_path('script_temp.js'); //storage_path('/prebid/version-teste.js');
    $command = "python -m jsmin ".public_path('script_temp.js'); //storage_path('/prebid/version-teste.js');

    $process = new Process($command);
    $process->run();

    if (!$process->isSuccessful()) {
        throw new ProcessFailedException($process);
    }
    return $process->getOutput();
  }

  public function getPositionsPrebid($idDomain){
    $adUnits = AdUnitRoot::join('ad_unit','ad_unit.id_ad_unit_root','ad_unit_root.id_ad_unit_root')
    ->where('ad_unit_root.id_domain', $idDomain)
    // ->whereNotNull('position')
    // ->where('position','!=','')
    ->selectRaw('ad_unit.*, ad_unit_root.ad_unit_root_code')
    ->get();

    foreach($adUnits as $adUnit){
      $array = explode('_', $adUnit->ad_unit_code);

      if(in_array('Content1', $array)){
          AdUnit::find($adUnit->id_ad_unit)->update(['position' => 'first_paragraph']);
      }elseif(in_array('Content2', $array)){
          AdUnit::find($adUnit->id_ad_unit)->update(['position' => 'second_paragraph']);
      }elseif(in_array('Content3', $array)){
          AdUnit::find($adUnit->id_ad_unit)->update(['position' => 'third_paragraph']);
      }elseif(in_array('MOBILE', $array) && in_array('Fixed', $array) && in_array('Posts', $array)){
          AdUnit::find($adUnit->id_ad_unit)->update(['position' => 'fixedMobile']);
      }elseif(in_array('TopFixed', $array) && in_array('Posts', $array)){
          AdUnit::find($adUnit->id_ad_unit)->update(['position' => 'TopFixed']);
      }elseif(in_array('Sidebar', $array) && in_array('Posts', $array)){
          AdUnit::find($adUnit->id_ad_unit)->update(['position' => 'Sidebar']);
      }else{
        AdUnit::find($adUnit->id_ad_unit)->update(['position' => null]);
      }
    }
    return back();
  }

  public function getDisable($idDomain){
    if (Defender::hasPermission("{$this->nameView}")) {
      $domain = $this->model->find($idDomain);
      Storage::disk('do_spaces')->put("/crm/".$domain->id_domain."/$domain->file_do", "", 'public');
      return back();
    } else {
      return redirect("/{$this->diretorioPrincipal}");
    }
  }

  public function getBlocosFixos($idDomain){

    function fixedStatus($id_domain,$type){
      $buscas = DomainFixed::where('id_domain',$id_domain)
                         ->where('type',$type)
                         ->get();
      return count($buscas);
    }
    
    if(Auth::user()->status_full_access != 1){
      $domain = $this->model
      ->where('id_user',Auth::user()->id)
      ->where('id_domain',$idDomain)
      ->firstOrFail();
    } else {
      $domain = $this->model
    ->where('id_domain',$idDomain)
    ->firstOrFail();
    }


    $principal = $this->diretorioPrincipal;
    $primaryKey = $this->primaryKey;
    $rota = $this->nameView;


    $blocos = [
      0=>[
        'cod'=>'99',
        'img'=>'/assets/painel/img/refresh.png',
        'name'=>'Refresh (A cada 30s)',
        'field'=>'rfh',
        'status'=>fixedStatus($idDomain,'rfh')
      ],
      1=>[
        'cod'=>'1',
        'img'=>'/assets/painel/img/desktop_first.png',
        'name'=>'Desktop - Fixo Topo',
        'field'=>'dfb',
        'status'=>fixedStatus($idDomain,'dfb')
      ],
      2=>[
        'cod'=>'2',
        'img'=>'/assets/painel/img/desktop_side.png',
        'name'=>'Desktop - Fixo Direita',
        'field'=>'dsr',
        'status'=>fixedStatus($idDomain,'dsr')
      ],
      3=>[
        'cod'=>'2',
        'img'=>'/assets/painel/img/desktop_side_right.png',
        'name'=>'Desktop - Fixo Esquerda',
        'field'=>'dsl',
        'status'=>fixedStatus($idDomain,'dsl')
      ],
      5=>[
        'cod'=>'5',
        'img'=>'/assets/painel/img/desktop_under.png',
        'name'=>'Desktop - Fixo Rodapé',
        'field'=>'dub',
        'status'=>fixedStatus($idDomain,'dub')
      ],
      10=>[
        'cod'=>'10',
        'img'=>'/assets/painel/img/mobile_first.png',
        'name'=>'Mobile - Fixo Topo',
        'field'=>'mfb',
        'status'=>fixedStatus($idDomain,'mfb')
      ],
      13=>[
        'cod'=>'13',
        'img'=>'/assets/painel/img/mobile_under.png',
        'name'=>'Mobile - Fixo Rodapé',
        'field'=>'mub',
        'status'=>fixedStatus($idDomain,'mub')
      ],
      14=>[
        'cod'=>'13',
        'img'=>'/assets/painel/img/mobile_interstatial.png',
        'name'=>'Mobile - Interstitial (<span style="color:#FF0000;font-weight:bold">NOVO</span>)',
        'field'=>'int',
        'status'=>fixedStatus($idDomain,'int')
      ],
    ];

    return view("{$this->diretorioPrincipal}.{$this->nameView}.blocos-fixos", compact('principal', 'rota', 'primaryKey','blocos','domain'));

  }

  public function postBlocosFixos($idDomain){
    $dadosForm = $this->request->all();

    $buscas = DomainFixed::where('id_domain',$dadosForm['id_domain'])
                         ->where('type',$dadosForm['type'])
                         ->get();
    
    if(count($buscas) > 0){      
      
      foreach($buscas as $busca){        
          DomainFixed::findOrFail($busca->id_domain_fixed)->delete();
      }

    } else {
      
      $salva = DomainFixed::create($dadosForm);
      if($salva){
        // $birl = $this->getGenerateBlockFixed($idDomain);
        echo json_encode(['status'=>200,'msg'=>'Salvo com sucesso.']);;
      } else {
        echo json_encode(['status'=>500,'msg'=>'Problemas ao salvar.']);
      }
      
    }
    
  }

  public function getGenerateAllBlockFixed(){
    $dominios = DomainFixed::groupBy('id_domain')->pluck('id_domain');
    foreach ($dominios as $dominio) {   
      if($this->getGenerateBlockFixed($dominio)){
        echo '1<br >';
      } else {
        echo '2<br >';
      }
    }
  }

  public function getGenerateBlockFixed($idDomain){


    $adUnits = AdUnit::join('ad_unit_root','ad_unit_root.id_ad_unit_root','ad_unit.id_ad_unit_root')
                      ->selectRaw('ad_unit_root_code, ad_unit_code')                  
                      ->where('ad_unit_root.id_domain',$idDomain)
                      ->where('ad_unit.ad_unit_status','ACTIVE')
                      ->first();

      if($adUnits){
      $dominio = $adUnits->ad_unit_root_code;
      $infos = explode('_',$adUnits->ad_unit_code);
      $prename = $infos[0];
      $last = $infos[(count($infos)-1)];

      $buscas = DomainFixed::where('id_domain',$idDomain)
                          ->get();

      $ativos = array();

      foreach ($buscas as $key => $busca) {
        
        // Refresh
        if($busca['type'] == 'rfh'){}
        
        // Mobile Header
        if($busca['type'] == 'mfb'){
          
          $ativos[] = [
            'bloco' => "/22088551542/".$dominio.'/'.$prename.'_MOBILE_Horizontal_Fixed_Content_'.$last,
            'div'   => 'MfixedTop',
            'className' =>'fixed-top',
            'size' =>'[320, 50]',
            'mobile' => 1
          ];

        }
        
        // Mobile Footer
        if($busca['type'] == 'mub'){
          $ativos[] = [
            'bloco' => '/22088551542/'.$dominio.'/'.$prename.'_MOBILE_Horizontal_Fixed1_Content_'.$last,
            'div' => 'MfixedBottom',
            'className' => 'fixed-bottom',
            'size' => '[320, 50]',
            'mobile' => 1
          ];
        }
        
        // Desktop Top
        if($busca['type'] == 'dfb'){
          $ativos[] = [
            'bloco' => '/22088551542/'.$dominio.'/'.$prename.'_WEB_Horizontal_TopFixed_Content_'.$last,
            'div' => 'fixedTop',
            'className' => 'fixed-top',
            'size' => '[960, 90], [980, 120], [970, 90], [728, 90], [750, 100], [950, 90], [980, 90], [970, 66]',
            'mobile' => 0
          ];
        }
        
        // Desktop Bottom
        if($busca['type'] == 'dub'){
          $ativos[] = [
            'bloco' => '/22088551542/'.$dominio.'/'.$prename.'_WEB_Horizontal_TopFixed1_Content_'.$last,
            'div' => 'fixedBottom',
            'className' => 'fixed-bottom',
            'size' => '[960, 90], [980, 120], [970, 90], [728, 90], [750, 100], [950, 90], [980, 90], [970, 66]',
            'mobile' => 0
          ];
        }
        
        // Sidebar Right
        if($busca['type'] == 'dsr'){
          $ativos[] = [
            'bloco' => '/22088551542/'.$dominio.'/'.$prename.'_WEB_Vertical_Sidebar_Content_'.$last,
            'div' => 'fixedRight',
            'className' => 'fixed-right',
            'size' => '[120, 600], [160, 600]',
            'mobile' => 0
          ];
        }
        
        // Sidebar Right
        if($busca['type'] == 'dsl'){
          $ativos[] = [
            'bloco' => '/22088551542/'.$dominio.'/'.$prename.'_WEB_Vertical_Sidebar1_Content_'.$last,
            'div' => 'fixedLeft',
            'className' => 'fixed-left',
            'size' => '[120, 600], [160, 600]',
            'mobile' => 0
          ];
        }
        
      }
      
      $script = "
                  var _gaq=[['_setAccount','UA-171621938-2'],['_trackPageview']];
                    (function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];
                    g.src=('https:'==location.protocol?'//ssl':'//www')+'.google-analytics.com/ga.js';
                    s.parentNode.insertBefore(g,s)}(document,'script'));
                    
                  window.monetiza = {

                      load_gtp: function (){
                        var Interstitial = document.createElement('script');
                            Interstitial.append('window.googletag = window.googletag || {cmd: []};');
                            Interstitial.append('googletag.cmd.push(function() {');
                            Interstitial.append(\"var slot = googletag.defineOutOfPageSlot('/22088551542/{$dominio}/{$prename}_WEB_Interstitial_Content_{$last}', googletag.enums.OutOfPageFormat.INTERSTITIAL);\");
                            Interstitial.append('if (slot) slot.addService(googletag.pubads());');
                            Interstitial.append('googletag.enableServices();');
                            Interstitial.append('googletag.display(slot);');
                            Interstitial.append('});');

                        var gads = document.createElement('script');
                            gads.async = true;
                            gads.type = 'text/javascript';
                            gads.src = 'https://www.googletagservices.com/tag/js/gpt.js';
                        
                        var target = document.getElementsByTagName('head')[0];
                            
                            target.insertBefore(Interstitial, target.firstChild);
                            target.insertBefore(gads, target.firstChild);
                      },

                      isMob: function(){
                          if (sessionStorage.desktop){
                              return false;
                            } else if (localStorage.mobile){
                              return true;
                            }            
                            var mobile = ['iphone', 'ipad', 'android', 'blackberry', 'nokia', 'opera mini', 'windows mobile', 'windows phone', 'iemobile', 'tablet', 'mobi'];
                            var ua = navigator.userAgent.toLowerCase();
                            for (var i in mobile)
                                if (ua.indexOf(mobile[i]) > -1) return true;
                            return false;
                      },
                      
                      agora: function (blocos){          
                            
                          window.monetiza.generateCSS();
                            
                            window.monetiza.load_gtp();

                            var target = document.getElementsByTagName('body')[0];
                            blocos.forEach(function(a,b){
                              if(window.monetiza.isMob()){
                                  if(a.mobile > 0){
                                      target.appendChild(window.monetiza.generateBlock(a.bloco,a.div,a.className,a.size));
                                  }
                              } else {
                                  if(a.mobile < 1){
                                      target.appendChild(window.monetiza.generateBlock(a.bloco,a.div,a.className,a.size));
                                  }
                              }
                              
                            })
                      },
                    
                      generateBlock: function(slot,id,className,size){
                        var divBlock = document.createElement(\"div\");
                            divBlock.className = className;  
                            
                        var preTag = document.createElement(\"script\");
                            preTag.append('window.googletag = window.googletag || {cmd: []};');
                            preTag.append('googletag.cmd.push(function() {');
                            preTag.append('googletag.defineSlot(\"'+slot+'\", ['+size+'], \"'+id+'\").setCollapseEmptyDiv(true).addService(googletag.pubads());');
                            preTag.append('googletag.enableServices();');
                            preTag.append('});');
                    
                            divBlock.appendChild(preTag);
                    
                    
                        var posTag = document.createElement(\"script\");
                            posTag.append('googletag.cmd.push(function() { googletag.display(\"'+id+'\"); });');
                    
                        var subDivBlock = document.createElement(\"div\");
                            subDivBlock.id = id;
                            subDivBlock.appendChild(posTag);
                    
                            divBlock.appendChild(subDivBlock);  
                            return divBlock;
                      },

                      generateCSS: function(){
                          var cssLoad = document.createElement('style');
                              cssLoad.append('.fixed-top{position:fixed;z-index:9995;top:0;text-align:center;left:50%!important;transform:translate(-50%);}.fixed-bottom{position:fixed;z-index:9995;bottom:0;text-align:center;left:50%;transform:translate(-50%);}.fixed-left{position:fixed;z-index:9995;left:0;text-align:center;top:50%;transform:translateY(-50%);}.fixed-right{position:fixed;z-index:9995;right:0;text-align:center;top:50%;transform:translateY(-50%);}');
                              var target = document.getElementsByTagName('head')[0];
                              target.insertBefore(cssLoad, target.firstChild);
                      }
                    
                    };
      ";

      $script .= '  var blocos ='.json_encode($ativos).';';
      $script .= 'window.onload = window.monetiza.agora(blocos);;';
      $script .= 'setTimeout(function() { googletag.pubads().refresh(); }, 60 * 1000);';
      $script .= "console.debug('START MONETIZE');";
      
      $file = 'scripts/domain_'.$idDomain.'.js';
      
      if (!file_exists($file)) {
        touch($file);
      }

      $save = file_put_contents('scripts/domain_'.$idDomain.'.js',$script);


      // $uglify = new Uglify($file);
      // $uglify->write($file);


      return $save;
    } else {
      return false;
    }
    // die();
  }


}
