<?php
namespace Binance;

class Api{

  protected $base = 'https://api.binance.com';
  protected $api_key;
  protected $api_secret;
  protected $info = [
    'timeOffset'=>0
  ];

  public function __construct($api_key, $api_secret)
  {
    $this->api_key = $api_key;
    $this->api_secret = $api_secret;
  }

  public function ping()
  {
    return $this->httpRequest("/api/v3/ping");
  }

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
          curl_setopt($curl, CURLOPT_HTTPHEADER, array(
              'X-MBX-APIKEY: ' . $this->api_key,
          ));
      }
      // params so buildquery string and append to url
      else if (count($params) > 0) {
          curl_setopt($curl, CURLOPT_URL, $this->base . $url . '?' . $query);
      }
      // no params so just the base url
      else {
          curl_setopt($curl, CURLOPT_URL, $this->base . $url);
          curl_setopt($curl, CURLOPT_HTTPHEADER, array(
              'X-MBX-APIKEY: ' . $this->api_key,
          ));
      }
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