<?php

/**
 * @file iATSPayments FAPS Request object.
 *
 * A lightweight object that encapsulates the details of the iATS Payments FAPS interface.
 *
 * Provides the REST interface details, replacing the official "gateway.php" version on github
 *
 * Require the method string on construction and any options like trace, logging.
 * Require the specific payment details, and the client credentials, on request
 *
 * TODO: provide logging options for the request, exception and response
 *
 * Expected usage:
 * $faps = new Faps_Request($options)
 * where options usually include
 *   action: one of the API actions
 *   category: 'Transactions', 'Ach', or 'Vault'
 *   test: set to anything non-empty for testing
 * $result = $faps->request($credentials, $request_params)
 * 
 **/

class Faps_Transaction implements JsonSerializable {
  /**
  * Transaction class: Ties into the PHP JSON Functions & makes them easily available to the Faps_Request class.
  * Using the class like so: $a = json_encode(new Faps_Transaction($txnarray), JSON_PRETTY_PRINT)
  * Will produce json data that the gateway should understand.
  */
  public function __construct(array $array) {
    $this->array = $array;
  }
  public function jsonSerialize() {
    return $this->array;
  }
}

/**
 *
 */
class Faps_Request {


  public $result = array();
  public $status = "";
  private $liveUrl = "https://secure.1stpaygateway.net/secure/RestGW/Gateway/Transaction/";
  private $testUrl = "https://secure-v.goemerchant.com/secure/RestGW/Gateway/Transaction/";

  /**
   *
   */
  public function __construct($options) {
    // category not yet checked/validated/used
    // $this->category = $options['category'];
    // TODO: verify action is valid and in category
    $this->action = $options['action'];
    $this->apiRequest = (empty($options['test']) ? $this->liveUrl : $this->testUrl ) . $this->action;
  }

  public function request($credentials, $request_params) {
    CRM_Core_Error::debug_var('Request Params', $request_params);
    CRM_Core_Error::debug_var('Request URL', $this->apiRequest);
    $data = array_merge($credentials, $request_params);
    try {
      if ($data == NULL) {
        $data = array(); 
      }
      $url = $this->apiRequest;
      $this->result = array();
      $jsondata = json_encode(new Faps_Transaction($data), JSON_PRETTY_PRINT);
      $jsondata = utf8_encode($jsondata);
      $curl_handle = curl_init();
      curl_setopt($curl_handle, CURLOPT_URL, $url);
      curl_setopt($curl_handle, CURLOPT_CUSTOMREQUEST, "POST");
      curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $jsondata);
      curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, TRUE);
      curl_setopt($curl_handle, CURLOPT_HTTPHEADER, array(
        "Content-type: application/json; charset-utf-8",
        "Content-Length: " . strlen($jsondata)
      ));
      curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, FALSE);
      $this->response = curl_exec($curl_handle);
      $this->status = curl_getinfo($curl_handle,CURLINFO_HTTP_CODE);
      if (connection_aborted()) {
        // handle aborted requests that PHP can detect, returning a result that indicates POST was aborted.
        $this->result = array(
          "isError" => TRUE,
          "errorMessages" => "Request Aborted",
          "isValid" => FALSE,
          "validations" => array(),
          "action" => "gatewayError"
        );
      }
      elseif (curl_errno($curl_handle) == 28 ){
        //This will handle timeouts as per cURL error definitions.
        $this->result = array(
          "isError" => TRUE,
          "errorMessages" => "Request Timed Out",
          "isValid" => FALSE,
          "validations" => array(),
          "action" => "gatewayError"
        );
      }
      else {
        CRM_Core_Error::debug_var('Response', $this->response);
        $this->result = json_decode($this->response, TRUE);
        /* if (empty($this->result['isSuccess'])) {
          $this->result['errorMessages'] = $this->result['data']['authResponse'];
        } */
      }
      CRM_Core_Error::debug_var('Result', $this->result);
      return $this->result;
    }
    catch (Exception $e){
      CRM_Core_Error::debug_var('Exception on request', $e);
      return $e->getMessage();
    }
  }
}
