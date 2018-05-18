<?php

require_once 'CRM/Core/Payment.php';

class CRM_Core_Payment_Faps extends CRM_Core_Payment {

  /**
   * mode of operation: live or test
   *
   * @var object
   * @static
   */
  protected $_mode = null;
  protected $use_cryptogram = FALSE;

  /**
   * Constructor
   *
   * @param string $mode the mode of operation: live or test
   *
   * @return void
   */
  function __construct( $mode, &$paymentProcessor ) {
    $this->_mode             = $mode;
    $this->_paymentProcessor = $paymentProcessor;
    $this->_processorName    = ts('iATS Payments 1st American Payment System Interface');
    $this->use_cryptogram   = faps_get_setting('use_cryptogram');
  }

  /**
   * This function checks to see if we have the right config values
   *
   * @return string the error message if any
   * @public
   */
  function checkConfig( ) {

    $error = array();

    if (empty($this->_paymentProcessor['user_name'])) {
      $error[] = ts('Processor Id is not set in the Administer CiviCRM &raquo; System Settings &raquo; Payment Processors.');
    }
    if (empty($this->_paymentProcessor['password'])) {
      $error[] = ts('Transaction Center Id is not set in the Administer CiviCRM &raquo; System Settings &raquo; Payment Processors.');
    }
    if (empty($this->_paymentProcessor['signature'])) {
      $error[] = ts('Merchant Key is not set in the Administer CiviCRM &raquo; System Settings &raquo; Payment Processors.');
    }

    if (!empty($error)) {
      return implode('<p>', $error);
    }
    else {
      return NULL;
    }
    // TODO: check urls vs. what I'm expecting?
  }

  /**
   * Get array of fields that should be displayed on the payment form for credit cards.
   * Use FAPS cryptojs to gather the senstive card information, if enabled.
   *
   * @return array
   */

  protected function getCreditCardFormFields() {
    $fields = parent::getCreditCardFormFields();
    if ($this->use_cryptogram) {
      $fields = array('cryptogram'); // + $fields;
    }
    return $fields;
  }

  /**
   * Return an array of all the details about the fields potentially required for payment fields.
   *
   * Only those determined by getPaymentFormFields will actually be assigned to the form
   *
   * @return array
   *   field metadata
   */
  public function getPaymentFormFieldsMetadata() {
    $metadata = parent::getPaymentFormFieldsMetadata();
    if ($this->use_cryptogram) {
      $metadata['cryptogram'] = array(
        'htmlType' => 'text',
        'cc_field' => TRUE,
        'name' => 'checkout-cryptogram',
        'title' => ts('Placeholder'),
        'attributes' => array(
          'class' => 'cryptogram',
          'size' => 30,
          'maxlength' => 60,
        ),
        'is_required' => FALSE,
      );
    }
    return $metadata;
  }

  function doDirectPayment(&$params) {

    // Check for valid currency
    if ('USD' != $params['currencyID']) {
      return self::error('Invalid currency selection: ' . $params['currencyID']);
    }
    // Use the Faps Request object for interacting with FAPS
    require_once "CRM/Faps/Request.php";
    $isRecur = CRM_Utils_Array::value('is_recur', $params) && $params['contributionRecurID'];
    // $method = $isRecur ? 'create_credit_card_customer' : 'cc';
    $options = array('action' => 'Sale');
    if ($this->_mode == 'test') {
      $options['test'] = 1;
    }
    $faps = new Faps_Request($options);
    $request = $this->convertParams($params, $options['action']);
    $request['ipAddress'] = (function_exists('ip_address') ? ip_address() : $_SERVER['REMOTE_ADDR']);
    $credentials = array(
      'merchantKey' => $this->_paymentProcessor['signature'],
      'processorId' => $this->_paymentProcessor['user_name']
    );
    // Make the request.
    $result = $faps->request($credentials, $request);
    $success = (!empty($result['isSuccess']));
    if ($success) {
      $params['contribution_status_id'] = 1;
      // For versions >= 4.6.6, the proper key.
      $params['payment_status_id'] = 1;
      $params['trxn_id'] = trim($result['data']['authCode']) . ':' . trim($result['data']['referenceNumber']);
      $params['gross_amount'] = $params['amount'];
      if ($isRecur) { // store it in the vault
        // return self::error(ts('Recurring function is not implemented'));
        $vaultKey = preg_replace("/[^a-z0-9]/", '', strtolower($request['ownerEmail']));
        $vaultKey .= '!'.md5(uniqid(rand(), TRUE));
        $options = array('action' => 'VaultCreateCCRecord');
        $vault = new Faps_Request($options);
        $create = array(
          'cardExpMonth' => $request['cardExpMonth'],
          'cardExpYear' => $request['cardExpYear'],
          'creditCardToken' => $result['data']['referenceNumber'],
          'ipAddress' => $request['ipAddress'],
          'vaultKey' => $vaultKey
        );
        $create_result = $vault->request($credentials,$create); 
        if (!empty($create_result['isSuccess'])) {
          $update = array('processor_id' => $vaultKey.':'.$create_result['data']['id']);
          // Setting the next_sched_contribution_date param doesn't do anything, commented out, work around in setRecurReturnParams
          $params = $this->setRecurReturnParams($params, $update);
        }
        else {
          CRM_Core_Error::debug_var('vault request', $create_result);
        }
      }
      return $params;
    }
    else {
      return self::error($result);
    }
  }

  /**
   * Todo?
   *
   * @param array $params name value pair of contribution data
   *
   * @return void
   * @access public
   *
   */
  function doTransferCheckout( &$params, $component ) {
    CRM_Core_Error::fatal(ts('This function is not implemented'));
  }

  /**
   * Convert the values in the civicrm params to the request array with keys as expected by FAPS
   *
   * @param array $params
   * @param string $action
   *
   * @return array
   */
  protected function convertParams($params, $method) {
    $request = array();
    $convert = array(
      'ownerEmail' => 'email',
      'ownerStreet' => 'street_address',
      'ownerCity' => 'city',
      'ownerState' => 'state_province',
      'ownerZip' => 'postal_code',
      'ownerCountry' => 'country',
      'orderId' => 'invoiceID',
      'cardNumber' => 'credit_card_number',
//      'cardtype' => 'credit_card_type',
      'cVV' => 'cvv2',
    );
    foreach ($convert as $r => $p) {
      if (isset($params[$p])) {
        $request[$r] = htmlspecialchars($params[$p]);
      }
    }
    if (empty($params['email'])) {
      if (isset($params['email-5'])) {
        $request['ownerEmail'] = $params['email-5'];
      }
      elseif (isset($params['email-Primary'])) {
        $request['ownerEmail'] = $params['email-Primary'];
      }
    }
    $request['ownerName'] = $params['billing_first_name'].' '.$params['billing_last_name'];
    $request['cardExpMonth'] = sprintf('%02d', $params['month']);
    $request['cardExpYear'] = sprintf('%02d', $params['year'] % 100);
    $request['transactionAmount'] = sprintf('%01.2f', CRM_Utils_Rule::cleanMoney($params['amount']));
    // print_r($request); print_r($params); die();
    return $request;
  }


  /**
   *
   */
  public function &error($error = NULL) {
    $e = CRM_Core_Error::singleton();
    if (is_object($error)) {
      $e->push($error->getResponseCode(),
        0, NULL,
        $error->getMessage()
      );
    }
    elseif ($error && is_numeric($error)) {
      $e->push($error,
        0, NULL,
        $this->errorString($error)
      );
    }
    elseif (is_array($error)) {
      $error_string = '';
      if ($error['isError']) {
        foreach($error['errorMessages'] as $message) {
          $error_string .= 'Error on '.$message['key'].': '.$message['message'];
        }
      }
      if ($error['validationHasFailed']) {
        foreach($error['validationFailures'] as $message) {
          $error_string .= 'Validation failure for '.$message['key'].': '.$message['message'];
        }
      }
      $e->push(9002,
        0, NULL,
        $error_string
      );
    }
    else {
      $e->push(9001, 0, NULL, "Unknown System Error.");
    }
    return $e;
  }

  /*
   * Set the return params for recurring contributions.
   *
   * Implemented as a function so I can do some cleanup and implement
   * the ability to set a future start date for recurring contributions.
   * This functionality will apply to back-end and front-end,
   * As enabled when configured via the iATS admin settings.
   *
   * This function will alter the recurring schedule as an intended side effect.
   * and return the modified the params.
   */
  protected function setRecurReturnParams($params, $update) {
    // Merge in the updates
    $params = array_merge($params, $update);
    // If the recurring record already exists, let's fix the next contribution and start dates,
    // in case core isn't paying attention.
    // We also set the schedule to 'in-progress' (even for ACH/EFT when the first one hasn't been verified),
    // because we want the recurring job to run for this schedule.
    if (!empty($params['contributionRecurID'])) {
      $recur_id = $params['contributionRecurID'];
      $recur_update = array(
        'id' => $recur_id,
        'contribution_status_id' => 'In Progress',
        'processor_id' => $update['processor_id'],
      );
      // use the receive date to set the next sched contribution date.
      // By default, it's empty, unless we've got a future start date.
      if (empty($update['receive_date'])) {
        $next = strtotime('+' . $params['frequency_interval'] . ' ' . $params['frequency_unit']);
        $recur_update['next_sched_contribution_date'] = date('Ymd', $next) . '030000';
      }
      else {
        $recur_update['start_date'] = $recur_update['next_sched_contribution_date'] = $update['receive_date'];
        // If I've got a monthly schedule, let's set the cycle_day for niceness
        if ('month' == $params['frequency_interval']) {
          $recur_update['cycle_day'] = date('j', strtotime($recur_update['start_date']));
        }
      }
      try {
        $result = civicrm_api3('ContributionRecur', 'create', $recur_update);
      }
      catch (CiviCRM_API3_Exception $e) {
        // Not a critical error, just log and continue.
        $error = $e->getMessage();
        Civi::log()->info('Unexpected error updating the next scheduled contribution date for id {id}: {error}', array('id' => $recur_id, 'error' => $error));
      }
    }
    else {
      Civi::log()->info('Unexpectedly unable to update the next scheduled contribution date, missing id.');
    }
    return $params;
  }



}



