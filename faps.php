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
  // die('test');
  $faps_processor = reset($faps_processors);
  if (faps_get_setting('use_cryptogram')) {
    $credentials = array(
      'transcenterId' => $faps_processor['password'],
  //    'merchantKey' => $faps_processor['signature'],
      'processorId' => $faps_processor['user_name']
    );
    $cryptojs = 'https://secure.1stpaygateway.net/restgw/cdn/cryptogram.js';
    $markup = sprintf("<script type=\"text/javascript\" 
  id=\"checkout-js\" src=\"%s\" 
  data-transcenter=\"%s\"
  data-processor=\"%s\"
  data-styleembed=\"False\"
  xdata-hideframe=\"True\"
  data-type=\"%s\"></script>\n", 
    $cryptojs, $credentials['transcenterId'], $credentials['processorId'], 'sale');
    // CRM_Core_Resources::singleton()->addScriptUrl($cryptojs);
    // $markup = print_r($faps_processors, TRUE);
    CRM_Core_Resources::singleton()->addScriptFile('com.iatspayments.faps', 'js/crypto.js', 10);
    CRM_Core_Resources::singleton()->addStyleFile('com.iatspayments.faps', 'css/crypto.css', 10);
    CRM_Core_Region::instance('page-footer')->add(array(
          'name' => $cryptojs,
          'type' => 'markup',
          'markup' => $markup,
          'weight' => 11,
          'region' => 'page-footer',
        ));
  }
}
