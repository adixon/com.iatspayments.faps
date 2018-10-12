<?php

require_once 'faps.civix.php';
use CRM_Faps_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function faps_civicrm_config(&$config) {
  _faps_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function faps_civicrm_xmlMenu(&$files) {
  _faps_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function faps_civicrm_install() {
  _faps_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function faps_civicrm_postInstall() {
  _faps_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function faps_civicrm_uninstall() {
  _faps_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function faps_civicrm_enable() {
  _faps_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function faps_civicrm_disable() {
  _faps_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function faps_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _faps_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function faps_civicrm_managed(&$entities) {
  _faps_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function faps_civicrm_caseTypes(&$caseTypes) {
  _faps_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function faps_civicrm_angularModules(&$angularModules) {
  _faps_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function faps_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _faps_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_entityTypes
 */
function faps_civicrm_entityTypes(&$entityTypes) {
  _faps_civix_civicrm_entityTypes($entityTypes);
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *
function faps_civicrm_preProcess($formName, &$form) {

} // */

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 *
function faps_civicrm_navigationMenu(&$menu) {
  _faps_civix_insert_navigation_menu($menu, 'Mailings', array(
    'label' => E::ts('New subliminal message'),
    'name' => 'mailing_subliminal_message',
    'url' => 'civicrm/mailing/subliminal',
    'permission' => 'access CiviMail',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _faps_civix_navigationMenu($menu);
} // */

function faps_get_setting($key = NULL) {
  static $settings;
  if (empty($settings)) { 
    $settings = CRM_Core_BAO_Setting::getItem('iATS FAPS Payments Extension', 'faps_settings');
  }
  return empty($key) ?  $settings : (isset($settings[$key]) ? $settings[$key] : '');
}

/**
 * Internal utility function: return the id's of any FAPS processors matching various conditions.
 *
 * Processors: an array of payment processors indexed by id to filter by,
 *             or if NULL, it searches through all
 * subtype: the FAPS service class name subtype
 * params: an array of additional params to pass to the api call.
 */
function faps_civicrm_processors($processors, $subtype = '', $params = array()) {
  $list = array();
  $match_all = ('*' == $subtype) ? TRUE : FALSE;
  if (!$match_all) {
    $params['class_name'] = 'Payment_Faps' . $subtype;
  }

  // Set the domain id if not passed in.
  if (!array_key_exists('domain_id', $params)) {
    $params['domain_id']    = CRM_Core_Config::domainID();
  }

  $result = civicrm_api3('PaymentProcessor', 'get', $params);
  if (0 == $result['is_error'] && count($result['values']) > 0) {
    foreach ($result['values'] as $paymentProcessor) {
      $id = $paymentProcessor['id'];
      if ((is_null($processors)) || !empty($processors[$id])) {
        if (!$match_all || (0 === strpos($paymentProcessor['class_name'], 'Payment_Faps'))) {
          $list[$id] = $paymentProcessor;
        }
      }
    }
  }
  return $list;
}

/**
 * Hook_civicrm_buildForm.
 * Do a Drupal 7 style thing so we can write smaller functions.
 */
function faps_civicrm_buildForm($formName, &$form) {
  // But start by grouping a few forms together for nicer code.
  switch ($formName) {
    case 'CRM_Event_Form_Participant':
    case 'CRM_Member_Form_Membership':
    case 'CRM_Contribute_Form_Contribution':
      // Override normal convention, deal with all these backend credit card contribution forms the same way.
      $fname = 'faps_civicrm_buildForm_Contribution';
      break;

    case 'CRM_Contribute_Form_Contribution_Main':
    case 'CRM_Event_Form_Registration_Register':
    case 'CRM_Financial_Form_Payment':
      // Override normal convention, deal with all these front-end contribution forms the same way.
      $fname = 'faps_civicrm_buildForm_Contribution';
      break;
    default:
      $fname = 'faps_civicrm_buildForm_' . $formName;
      break;
  }
  if (function_exists($fname)) {
    $fname($form);
  }
  // Else echo $fname;.
}

/**
 * Add the magic sauce to cc and ach forms if I'm using FAPS
 */
function faps_civicrm_buildForm_Contribution(&$form) {
  // Skip if i don't have any processors.
  //echo '<pre>'; print_r($form); die();
  if (empty($form->_processors)) {
   // return;
  }
  $form_class = get_class($form);
  //  die($form_class);

  if ($form_class == 'CRM_Financial_Form_Payment') {
    // We're on CRM_Financial_Form_Payment, we've got just one payment processor
    $id = $form->_paymentProcessor['id'];
    $faps_processors = faps_civicrm_processors(array($id => $form->_paymentProcessor), '*');
  }
  else {
    // Handle the event and contribution page forms
    if (empty($form->_paymentProcessors)) {
      if (empty($form->_paymentProcessorIDs)) {
        return;
      }
      else {
        $form_payment_processors = array_fill_keys($form->_paymentProcessorIDs,1);
      }
    }
    else {
      $form_payment_processors = $form->_paymentProcessors;
    }
    $faps_processors = faps_civicrm_processors($form_payment_processors, '*');
  }
  if (empty($faps_processors)) {
    return;
  }
  // print_r($faps_processors); die();
  // die('test');
  $faps_processor = reset($faps_processors);
  $is_cc = ($faps_processor['payment_instrument_id'] == 1);
  $is_test = ($faps_processor['is_test'] == 1);
  if (faps_get_setting('use_cryptogram')) {
    $credentials = array(
      'transcenterId' => $faps_processor['password'],
  //    'merchantKey' => $faps_processor['signature'],
      'processorId' => $faps_processor['user_name']
    );
    $faps_domain = parse_url($faps_processor['url_site'], PHP_URL_HOST);
    $cryptojs = 'https://'.$faps_domain.'/secure/PaymentHostedForm/Scripts/firstpay/firstpay.cryptogram.js';
    $transaction_type = $is_cc ? 'Sale' : 'AchDebit';
    $iframe_src = 'https://'.$faps_domain. '/secure/PaymentHostedForm/v3/' .($is_cc ? 'CreditCard' : 'Ach');
    $iframe_style = 'width: 100%;'; // height: 100%;';
    $markup = sprintf("<iframe id=\"firstpay-iframe\" src=\"%s\" style=\"%s\" data-transcenter-id=\"%s\" data-processor-id=\"%s\" data-transaction-type=\"%s\" data-manual-submit=\"false\"></iframe>\n", $iframe_src, $iframe_style,$credentials['transcenterId'], $credentials['processorId'], $transaction_type);
    // $markup = "<iframe id=\"firstpay-iframe\" src=\"%s\" style=\"width: 100%; height: 100%\" data-transcenter-id=\"%s\" data-processor-id=\"%s\" data-transaction-type=\"%s\" data-manual-submit=\"false\"></iframe>\n";
    // print_r('<pre>'.$markup.'</pre>'); die();
    CRM_Core_Resources::singleton()->addScriptUrl($cryptojs);
    // $markup = print_r($faps_processors, TRUE);
    CRM_Core_Resources::singleton()->addScriptFile('com.iatspayments.faps', 'js/crypto.js', 10);
    CRM_Core_Resources::singleton()->addStyleFile('com.iatspayments.faps', 'css/crypto.css', 10);
    CRM_Core_Region::instance('page-body')->add(array(
          'name' => 'firstpay-iframe',
          'type' => 'markup',
          'markup' => $markup,
          'weight' => 11,
          'region' => 'page-body',
        )); 
  }
}


/* Shared utility functions */

/**
 * For a recurring contribution, find a reasonable candidate for a template, where possible.
 */
function _faps_civicrm_getContributionTemplate($contribution) {
  // Get the most recent contribution in this series that matches the same total_amount, if present.
  $template = array();
  $get = ['contribution_recur_id' => $contribution['contribution_recur_id'], 'options' => ['sort' => ' id DESC', 'limit' => 1]];
  if (!empty($contribution['total_amount'])) {
    $get['total_amount'] = $contribution['total_amount'];
  }
  $result = civicrm_api3('contribution', 'get', $get);
  if (!empty($result['values'])) {
    $template = reset($result['values']);
    $contribution_id = $template['id'];
    $template['original_contribution_id'] = $contribution_id;
    $template['line_items'] = array();
    $get = array('entity_table' => 'civicrm_contribution', 'entity_id' => $contribution_id);
    $result = civicrm_api3('LineItem', 'get', $get);
    if (!empty($result['values'])) {
      foreach ($result['values'] as $initial_line_item) {
        $line_item = array();
        foreach (array('price_field_id', 'qty', 'line_total', 'unit_price', 'label', 'price_field_value_id', 'financial_type_id') as $key) {
          $line_item[$key] = $initial_line_item[$key];
        }
        $template['line_items'][] = $line_item;
      }
    }
  }
  return $template;
}

/**
 * Function _faps_contributionrecur_next.
 *
 * @param $from_time: a unix time stamp, the function returns values greater than this
 * @param $days: an array of allowable days of the month
 *
 *   A utility function to calculate the next available allowable day, starting from $from_time.
 *   Strategy: increment the from_time by one day until the day of the month matches one of my available days of the month.
 */
function _faps_contributionrecur_next($from_time, $allow_mdays) {
  $dp = getdate($from_time);
  // So I don't get into an infinite loop somehow.
  $i = 0;
  while (($i++ < 60) && !in_array($dp['mday'], $allow_mdays)) {
    $from_time += (24 * 60 * 60);
    $dp = getdate($from_time);
  }
  return $from_time;
}

/**
 * Function _faps_contribution_payment
 *
 * @param $contribution an array of a contribution to be created (or in case of future start date,
          possibly an existing pending contribution to recycle, if it already has a contribution id).
 * @param $options must include vault code, subtype, and may include a membership id
 * @param $original_contribution_id if included, use as a template for a recurring contribution.
 *
 *   A high-level utility function for making a contribution payment from an existing recurring schedule
 *   Used in the Iatsrecurringcontributions.php job and the one-time ('card on file') form.
 *   
 */
function _faps_process_contribution_payment(&$contribution, $options, $original_contribution_id) {
  // By default, don't use repeattransaction
  $use_repeattransaction = FALSE;
  $is_recurrence = !empty($original_contribution_id);
  // First try and get the money, using my process_transaction cover function.
  // TODO: convert this into an api job?
  $result =  _faps_process_transaction($contribution, $options);
  $success = (!empty($result['isSuccess']));
  // Handle any case of a failure of some kind, either the card failed, or the system failed.
  if (!$success) {
    /* set the failed transaction status, or pending if I had a server issue */
    $contribution['contribution_status_id'] = empty($result['data']['authCode']) ? 2 : 4;
    /* and include the reason in the source field */
    $contribution['source'] .= ' ' . implode(' ',$result['errorMessages']);
    // Save any reject code here for processing by the calling function (a bit lame)
    if ($contribution['contribution_status_id'] == 4) {
      $contribution['faps_reject_code'] = $result['data']['authCode'];
    }
  }
  else {
    // I have a transaction id.
    $trxn_id = $contribution['trxn_id'] = trim($result['data']['authCode']) . ':' . trim($result['data']['referenceNumber']);
    // Initialize the status to pending
    $contribution['contribution_status_id'] = 2;
    // We'll use the repeattransaction api for successful transactions under two conditions:
    // 1. if we want it (i.e. if it's for a recurring schedule)
    // 2. if we don't already have a contribution id
    $use_repeattransaction = $is_recurrence && empty($contribution['id']);
  }
  if ($use_repeattransaction) {
    // We processed it successflly and I can try to use repeattransaction. 
    // Requires the original contribution id.
    // Issues with this api call:
    // 1. Always triggers an email and doesn't include trxn.
    // 2. Date is wrong.
    try {
      // $status = $result['contribution_status_id'] == 1 ? 'Completed' : 'Pending';
      $contributionResult = civicrm_api3('Contribution', 'repeattransaction', array(
        'original_contribution_id' => $original_contribution_id,
        'contribution_status_id' => 'Pending',
        'is_email_receipt' => 0,
        // 'invoice_id' => $contribution['invoice_id'],
        ///'receive_date' => $contribution['receive_date'],
        // 'campaign_id' => $contribution['campaign_id'],
        // 'financial_type_id' => $contribution['financial_type_id'],.
        // 'payment_processor_id' => $contribution['payment_processor'],
        'contribution_recur_id' => $contribution['contribution_recur_id'],
      ));
      // watchdog('iats_civicrm','repeat transaction result <pre>@params</pre>',array('@params' => print_r($pending,TRUE)));.
      $contribution['id'] = CRM_Utils_Array::value('id', $contributionResult);
    }
    catch (Exception $e) {
      // Ignore this, though perhaps I should log it.
    }
    if (empty($contribution['id'])) {
      // Assume I failed completely and I'll fall back to doing it the manual way.
      $use_repeattransaction = FALSE;
    }
    else {
      // If repeattransaction succeded.
      // First restore/add various fields that the repeattransaction api may overwrite or ignore.
      // TODO - fix this in core to allow these to be set above.
      civicrm_api3('contribution', 'create', array('id' => $contribution['id'], 
        'invoice_id' => $contribution['invoice_id'],
        'source' => $contribution['source'],
        'receive_date' => $contribution['receive_date'],
        'payment_instrument_id' => $contribution['payment_instrument_id'],
        // '' => $contribution['receive_date'],
      ));
      // Save my status in the contribution array that was passed in.
      $contribution['contribution_status_id'] = $result['contribution_status_id'];
      if ($result['contribution_status_id'] == 1) {
        // My transaction completed, so record that fact in CiviCRM, potentially sending an invoice.
        try {
          civicrm_api3('Contribution', 'completetransaction', array(
            'id' => $contribution['id'],
            'payment_processor_id' => $contribution['payment_processor'],
            'is_email_receipt' => (empty($options['is_email_receipt']) ? 0 : 1),
            'trxn_id' => $contribution['trxn_id'],
            'receive_date' => $contribution['receive_date'],
          ));
        }
        catch (Exception $e) {
          // log the error and continue
          CRM_Core_Error::debug_var('Unexpected Exception', $e);
        }
      }
      else {
        // just save my trxn_id for ACH/EFT verification later
        try {
          civicrm_api3('Contribution', 'create', array(
            'id' => $contribution['id'],
            'trxn_id' => $contribution['trxn_id'],
          ));
        }
        catch (Exception $e) {
          // log the error and continue
          CRM_Core_Error::debug_var('Unexpected Exception', $e);
        }
      }
    }
  }
  if (!$use_repeattransaction) {
    /* If I'm not using repeattransaction for any reason, I'll create the contribution manually */
    // This code assumes that the contribution_status_id has been set properly above, either pending or failed.
    $contributionResult = civicrm_api3('contribution', 'create', $contribution);
    // Pass back the created id indirectly since I'm calling by reference.
    $contribution['id'] = CRM_Utils_Array::value('id', $contributionResult);
    // Connect to a membership if requested.
    if (!empty($options['membership_id'])) {
      try {
        civicrm_api3('MembershipPayment', 'create', array('contribution_id' => $contribution['id'], 'membership_id' => $options['membership_id']));
      }
      catch (Exception $e) {
        // Ignore.
      }
    }
    /* And then I'm done unless it completed */
    if ($result['contribution_status_id'] == 1 && !empty($result['status'])) {
      /* success, and the transaction has completed */
      $complete = array('id' => $contribution['id'], 
        'payment_processor_id' => $contribution['payment_processor'],
        'trxn_id' => $trxn_id, 
        'receive_date' => $contribution['receive_date']
      );
      $complete['is_email_receipt'] = empty($options['is_email_receipt']) ? 0 : 1;
      try {
        $contributionResult = civicrm_api3('contribution', 'completetransaction', $complete);
      }
      catch (Exception $e) {
        // Don't throw an exception here, or else I won't have updated my next contribution date for example.
        $contribution['source'] .= ' [with unexpected api.completetransaction error: ' . $e->getMessage() . ']';
      }
      // Restore my source field that ipn code irritatingly overwrites, and make sure that the trxn_id is set also.
      civicrm_api3('contribution', 'setvalue', array('id' => $contribution['id'], 'value' => $contribution['source'], 'field' => 'source'));
      civicrm_api3('contribution', 'setvalue', array('id' => $contribution['id'], 'value' => $trxn_id, 'field' => 'trxn_id'));
      $message = $is_recurrence ? ts('Successfully processed contribution in recurring series id %1: ', array(1 => $contribution['contribution_recur_id'])) : ts('Successfully processed one-time contribution: ');
      return $message . $result['auth_result'];
    }
  }
  // Now return the appropriate message. 
  if (empty($result['status'])) {
    return ts('Failed to process recurring contribution id %1: ', array(1 => $contribution['contribution_recur_id'])) . implode(' ',$result['errorMessages']);
  }
  elseif ($result['contribution_status_id'] == 1) {
    return ts('Successfully processed recurring contribution in series id %1: ', array(1 => $contribution['contribution_recur_id'])) . $result['auth_result'];
  }
  else {
    // I'm using ACH/EFT or a processor that doesn't complete.
    return ts('Successfully processed pending recurring contribution in series id %1: ', array(1 => $contribution['contribution_recur_id'])) . $result['auth_result'];
  }
}
/**
 * Function _faps_process_transaction.
 *
 * @param $contribution an array of properties of a contribution to be processed
 * @param $options must include customer code, subtype and iats_domain
 *
 *   A low-level utility function for triggering a transaction on iATS/FAPS using a card on file.
 */
function _faps_process_transaction($contribution, $options) {
  require_once "CRM/Faps/Request.php";
  switch ($options['subtype']) {
    case 'EFT':
      die('Not implemented');
      // Will not complete.
      $contribution_status_id = 2;
      break;
    default:
      $action = 'SaleUsingVault';
      $contribution_status_id = 1;
      break;
  }
  $paymentProcessor = civicrm_api3('PaymentProcessor', 'getsingle', ['return' => ['password','user_name','signature'], 'id' => $contribution['payment_processor'], 'is_test' => $contribution['is_test']]);
  $credentials = array(
    'merchantKey' => $paymentProcessor['signature'],
    'processorId' => $paymentProcessor['user_name']
  );
  $service_params = array('action' => $action);
  $faps = new Faps_Request($service_params);
  // Build the request array.
  CRM_Core_Error::debug_var('options', $options);
  list($vaultKey,$vaultId) = explode(':', $options['vault'], 2);
  $request = array(
    'vaultKey' => $vaultKey,
    'vaultId' => $vaultId,
    'orderId' => $contribution['invoice_id'],
    'transactionAmount' => sprintf('%01.2f', CRM_Utils_Rule::cleanMoney($contribution['total_amount'])),
    'ipAddress' => (function_exists('ip_address') ? ip_address() : $_SERVER['REMOTE_ADDR']),
  );
  // remove the customerIPAddress if it's the internal loopback to prevent
  // being locked out due to velocity checks
  if ('127.0.0.1' == $request['ipAddress']) {
     $request['ipAddress'] = '';
  }
  // Make the request.
  CRM_Core_Error::debug_var('process transation request', $request);
  $result = $faps->request($credentials, $request);
  $success = (!empty($result['isSuccess']));
  // pass back the anticipated status_id based on the method (i.e. 1 for CC, 2 for ACH/EFT)
  $result['contribution_status_id'] = $contribution_status_id;
  return $result;
}

/**
 * Function _faps_get_future_start_dates
 *
 * @param $start_date a timestamp, only return dates after this.
 * @param $allow_days an array of allowable days of the month.
 *
 *   A low-level utility function for triggering a transaction on iATS.
 */
function _faps_get_future_monthly_start_dates($start_date, $allow_days) {
  // Future date options.
  $start_dates = array();
  // special handling for today - it means immediately or now.
  $today = date('Ymd').'030000';
  // If not set, only allow for the first 28 days of the month.
  if (max($allow_days) <= 0) {
    $allow_days = range(1,28);
  }
  for ($j = 0; $j < count($allow_days); $j++) {
    // So I don't get into an infinite loop somehow ..
    $i = 0;
    $dp = getdate($start_date);
    while (($i++ < 60) && !in_array($dp['mday'], $allow_days)) {
      $start_date += (24 * 60 * 60);
      $dp = getdate($start_date);
    }
    $key = date('Ymd', $start_date).'030000';
    if ($key == $today) { // special handling
      $display = ts('Now');
      $key = ''; // date('YmdHis');
    }
    else {
      $display = strftime('%B %e, %Y', $start_date);
    }
    $start_dates[$key] = $display;
    $start_date += (24 * 60 * 60);
  }
  return $start_dates;
}
