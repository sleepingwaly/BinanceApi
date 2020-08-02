<?php
namespace Binance;

class Api{

  protected $base = 'https://api.binance.com';
  protected $api_key;
  protected $api_secret;
  protected $info = [
    'timeOffset'=>0
  ];
  protected $httpDebug=false;
  protected $curlOpts = [];

  public function __construct($api_key, $api_secret)
  {
    $this->api_key = $api_key;
    $this->api_secret = $api_secret;
  }

  /*====================行情接口START======================*/
  /**
   * 测试能否联通 Rest API。
   * @return [type] [description]
   */
  public function ping()
  {
    return $this->httpRequest("/api/v3/ping");
  }

  /**
   * 测试能否联通 Rest API 并 获取服务器时间。
   * @return [type] [description]
   */
  public function serverTime()
  {
    return $this->httpRequest("/api/v3/time");
  }

  /**
   * 使用RestAPI服务器时间【通过计算本地服务器与BINANCE服务器时间偏差实现】
   * @return [type] [description]
   */
  public function useServerTime()
  {
      $serverTime = $this->serverTime();
      if (isset($serverTime['serverTime'])) {
          $this->info['timeOffset'] = $serverTime['serverTime'] - (microtime(true) * 1000);
      }
  }

  /**
   * 获取交易规则和交易对信息。
   * @return [type] [description]
   */
  public function exchangeInfo()
  {
    return $this->httpRequest("/api/v3/exchangeInfo");
  }

  /**
   * 获取深度信息
   * @param  string      $symbol [description]
   * @param  int|integer $limit  [可选值[5, 10, 20, 50, 100, 500, 1000, 5000]]
   * @return [type]              [description]
   */
  public function depth(string $symbol, int $limit=100)
  {
    $params = [
      'symbol' => $symbol,
      'limit'  => $limit
    ];
    return $this->httpRequest("/api/v3/depth", "GET", $params);
  }

  /**
   * 近期成交列表
   * @param  string      $symbol [description]
   * @param  int|integer $limit  [description]
   * @return [type]              [description]
   */
  public function trades(string $symbol, int $limit=500)
  {
    $params = [
      'symbol' => $symbol,
      'limit'  => $limit
    ];
    return $this->httpRequest("/api/v3/trades", "GET", $params);
  }

  /**
   * 获取历史成交。
   * @param  string      $symbol [description]
   * @param  int|integer $limit  [description]
   * @param  int|null    $fromId [description]
   * @return [type]              [description]
   */
  public function historyTrades(string $symbol, int $limit=500, $fromId=null)
  {
    $params = [
      'symbol' => $symbol,
      'limit'  => $limit,
      'fromId' => $fromId
    ];
    return $this->httpRequest("/api/v3/historicalTrades", "GET", $params);
  }

  /**
   * 近期成交(归集), 归集交易与逐笔交易的区别在于，同一价格、同一方向、同一时间的trade会被聚合为一条
   * 如果发送startTime和endTime，间隔必须小于一小时。
   * 如果没有发送任何筛选参数(fromId, startTime,endTime)，默认返回最近的成交记录
   * @param  string      $symbol    [description]
   * @param  int|null    $fromId    [从包含fromId的成交id开始返回结果]
   * @param  [type]      $startTime [从该时刻之后的成交记录开始返回结果]
   * @param  [type]      $endTime   [返回该时刻为止的成交记录]
   * @param  int|integer $limit     [默认 500; 最大 1000.]
   * @return [type]                 [description]
   */
  public function aggTrades(string $symbol, $fromId = null, $startTime=null, $endTime=null, int $limit=500)
  {
    $params = [
      'symbol' => $symbol,
      'fromId' => $fromId,
      'startTime' => $startTime,
      'endTime'   => $endTime,
      'limit'     => $limit
    ];
    return $this->httpRequest("/api/v3/aggTrades", "GET", $params);
  }

  /**
   * K线数据
   * 每根K线代表一个交易对。
   * 每根K线的开盘时间可视为唯一ID
   * 如果未发送 startTime 和 endTime ，默认返回最近的交易。
   * @param  string      $symbol    [description]
   * @param  [type]      $interval  [k线规格]
   * @param  [type]      $startTime [description]
   * @param  [type]      $endTime   [description]
   * @param  int|integer $limit     [description]
   * @return [type]                 [description]
   */
  public function klines(string $symbol, $interval, $startTime=null, $endTime=null, int $limit=500)
  {
    $params = [
      'symbol'  =>  $symbol,
      'interval'=>  $interval,
      'startTime' => $startTime,
      'endTime'   => $endTime,
      'limit'     => $limit
    ];
    return $this->httpRequest("/api/v3/klines", "GET", $params);
  }

  /**
   * 当前平均价格
   * @param  string $symbol [description]
   * @return [type]         [description]
   */
  public function avgPrice(string $symbol)
  {
    $params = [
      'symbol' => $symbol
    ];
    return $this->httpRequest("/api/v3/avgPrice");
  }

  /**
   * 24 小时滚动窗口价格变动数据。 
   * 请注意，不携带symbol参数会返回全部交易对数据，不仅数据庞大，而且权重极高
   * @param  string $symbol [description]
   * @return [type]         [description]
   */
  public function ticker24hr(string $symbol='')
  {
    $params = [
      'symbol' => $symbol
    ];
    return $this->httpRequest("/api/v3/ticker/24hr");
  }

  /**
   * 获取交易对最新价格
   * 不发送交易对参数，则会返回所有交易对信息
   * @param  string $symbol [description]
   * @return [type]         [description]
   */
  public function tickerPrice(string $symbol='')
  {
    $params = [
      'symbol' => $symbol
    ];
    return $this->httpRequest("/api/v3/ticker/price");
  }

  /**
   * 返回当前最优的挂单(最高买单，最低卖单)
   * 不发送交易对参数，则会返回所有交易对信息
   * @param  string $symbol [description]
   * @return [type]         [description]
   */
  public function tickerBookTicker(string $symbol='')
  {
    $params = [
      'symbol'  => $symbol
    ];
    return $this->httpRequest("/api/v3/ticker/bookTicker");
  }

  /*====================行情接口 END=====================*/

  /*=================杠杆账户和交易接口 START====================*/


  public function marginHistory(string $symbol, bool $isIsolated=FALSE, $startTime=null, $endTime=null, $fromId=null, int $limit=500, $recvWindow=null)
  {
    
    $params = [
      'symbol'  => $symbol,
      'isIsolated'  => $isIsolated,
      'startTime'   => $startTime,
      'endTime'     => $endTime,
      'fromId'      => $fromId,
      'limit'       => $limit,
      'recvWindow'  => $recvWindow
    ];
    $ts = (microtime(true) * 1000) + $this->info['timeOffset'];
    $params['timestamp'] = number_format($ts, 0, '.', '');

    return $this->httpRequest("/sapi/v1/margin/myTrades", "GET", $params, true);
  }







  /*=================杠杆账户和交易接口 END=====================*/


  protected function httpRequest(string $url, string $method = "GET", array $params = [], bool $signed = false)
  {
      if (function_exists('curl_init') === false) {
          throw new \Exception("Sorry cURL is not installed!");
      }


      $curl = curl_init();
      curl_setopt($curl, CURLOPT_VERBOSE, $this->httpDebug);
      $query = http_build_query($params, '', '&');

      // signed with params
      if ($signed === true) {
          if (empty($this->api_key)) {
              throw new \Exception("signedRequest error: API Key not set!");
          }

          if (empty($this->api_secret)) {
              throw new \Exception("signedRequest error: API Secret not set!");
          }

          $base = $this->base;
          $ts = (microtime(true) * 1000) + $this->info['timeOffset'];
          $params['timestamp'] = number_format($ts, 0, '.', '');
          
  
          $query = http_build_query($params, '', '&');
          $signature = hash_hmac('sha256', $query, $this->api_secret);
          if ($method === "POST") {
              $endpoint = $base . $url;
              $params['signature'] = $signature; // signature needs to be inside BODY
              $query = http_build_query($params, '', '&'); // rebuilding query
          } else {
              $endpoint = $base . $url . '?' . $query . '&signature=' . $signature;
          }

          curl_setopt($curl, CURLOPT_URL, $endpoint);
      }
      // params so buildquery string and append to url
      else if (count($params) > 0) {
          curl_setopt($curl, CURLOPT_URL, $this->base . $url . '?' . $query);
      }
      // no params so just the base url
      else {
          curl_setopt($curl, CURLOPT_URL, $this->base . $url);
      }
      curl_setopt($curl, CURLOPT_HTTPHEADER, array(
          'X-MBX-APIKEY: ' . $this->api_key,
      ));

      curl_setopt($curl, CURLOPT_USERAGENT, "User-Agent: Mozilla/4.0 (compatible; PHP Binance API)");
      // Post and postfields
      if ($method === "POST") {
          curl_setopt($curl, CURLOPT_POST, true);
          curl_setopt($curl, CURLOPT_POSTFIELDS, $query);
      }
      // Delete Method
      if ($method === "DELETE") {
          curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
      }

      // PUT Method
      if ($method === "PUT") {
          curl_setopt($curl, CURLOPT_PUT, true);
      }

    
      curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
      curl_setopt($curl, CURLOPT_HEADER, true);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_TIMEOUT, 60);

      // set user defined curl opts last for overriding
      foreach ($this->curlOpts as $key => $value) {
          curl_setopt($curl, constant($key), $value);
      }


      $output = curl_exec($curl);
      // Check if any error occurred
      if (curl_errno($curl) > 0) {
          // should always output error, not only on httpdebug
          // not outputing errors, hides it from users and ends up with tickets on github
          echo 'Curl error: ' . curl_error($curl) . "\n";
          return [];
      }
  
      $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
      $header = substr($output, 0, $header_size);
      $output = substr($output, $header_size);
      
      curl_close($curl);
      
      $json = json_decode($output, true);
      
      

      if(isset($json['msg'])){
          // should always output error, not only on httpdebug
          // not outputing errors, hides it from users and ends up with tickets on github
          echo "signedRequest error: {$output}" . PHP_EOL;
      }

      return $json;
  }


}