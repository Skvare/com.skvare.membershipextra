<?php

require_once 'membershipextra.civix.php';
use CRM_Membershipextra_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/ 
 */
function membershipextra_civicrm_config(&$config) {
  _membershipextra_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_xmlMenu
 */
function membershipextra_civicrm_xmlMenu(&$files) {
  _membershipextra_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function membershipextra_civicrm_install() {
  _membershipextra_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postInstall
 */
function membershipextra_civicrm_postInstall() {
  _membershipextra_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_uninstall
 */
function membershipextra_civicrm_uninstall() {
  _membershipextra_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function membershipextra_civicrm_enable() {
  _membershipextra_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_disable
 */
function membershipextra_civicrm_disable() {
  _membershipextra_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_upgrade
 */
function membershipextra_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _membershipextra_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_managed
 */
function membershipextra_civicrm_managed(&$entities) {
  _membershipextra_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_caseTypes
 */
function membershipextra_civicrm_caseTypes(&$caseTypes) {
  _membershipextra_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_angularModules
 */
function membershipextra_civicrm_angularModules(&$angularModules) {
  _membershipextra_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_alterSettingsFolders
 */
function membershipextra_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _membershipextra_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
 */
function membershipextra_civicrm_entityTypes(&$entityTypes) {
  _membershipextra_civix_civicrm_entityTypes($entityTypes);
}

/**
 * Implements hook_civicrm_thems().
 */
function membershipextra_civicrm_themes(&$themes) {
  _membershipextra_civix_civicrm_themes($themes);
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_preProcess
 *
function membershipextra_civicrm_preProcess($formName, &$form) {

} // */

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_navigationMenu
 *
function membershipextra_civicrm_navigationMenu(&$menu) {
  _membershipextra_civix_insert_navigation_menu($menu, 'Mailings', array(
    'label' => E::ts('New subliminal message'),
    'name' => 'mailing_subliminal_message',
    'url' => 'civicrm/mailing/subliminal',
    'permission' => 'access CiviMail',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _membershipextra_civix_navigationMenu($menu);
} // */



function membershipextra_civicrm_buildForm($formName, &$form) {
  if ($formName == 'CRM_Member_Form_MembershipType') {
    $membershipTypes = CRM_Member_PseudoConstant::membershipType();
    if ($form->_action & CRM_Core_Action::UPDATE) {
      unset($membershipTypes[$form->_id]);
    }

    $form->addElement('checkbox', 'limit_renewal', ts('Limit renewal to 1 period ahead'));

    $form->add('text', 'allow_renewal_before', ts('Allow Renewal Before ending'));
    $units = CRM_Core_SelectValues::unitList();
    unset($units['year']);

    $form->addElement('select', 'allow_renewal_before_unit', ts('Allow Renewal Before ending'), ['' => '-select'] + $units);

    $groups = CRM_Core_PseudoConstant::nestedGroup();
    $form->add('select', 'restrict_to_groups', ts('Contact in this groups see membership'),
      $groups, FALSE, ['class' => 'crm-select2 huge', 'multiple' => 1]);
    if ($form->_action & CRM_Core_Action::UPDATE) {
      $membershipExtras = CRM_Membershipextra_Utils::getSettings($form->_id);
      $form->setDefaults($membershipExtras);
    }
  }
}

/**
 * Implementation of hook_civicrm_postProcess
 */
function membershipextra_civicrm_postProcess($formName, &$form) {
  if ($formName == 'CRM_Member_Form_MembershipType') {
    if ($form->_id) {
      $id = $form->_id;
    }
    else {
      $id = CRM_Core_DAO::getFieldValue('CRM_Member_DAO_MembershipType', $form->_submitValues['name'], 'id', 'name');
    }
    if ($id) {
      $limit_renewal = $form->_submitValues['limit_renewal'] ?? 0;
      $allow_renewal_before = $form->_submitValues['allow_renewal_before'] ?? 0;
      $allow_renewal_before_unit = $form->_submitValues['allow_renewal_before_unit'] ?? '';
      $allow_renewal_before_unit = $form->_submitValues['restrict_to_groups'] ?? '';

      $membershipExtras = [
        'limit_renewal' => $form->_submitValues['limit_renewal'] ?? 0,
        'allow_renewal_before' => $form->_submitValues['allow_renewal_before'] ?? 0,
        'allow_renewal_before_unit' => $form->_submitValues['allow_renewal_before_unit'] ?? '',
        'restrict_to_groups' => $form->_submitValues['restrict_to_groups'] ?? '',
      ];
      foreach ($membershipExtras as $name => $value) {
        CRM_Membershipextra_Utils::setSetting($value, $name, $id);
      }
    }
  }
}


function membershipextra_civicrm_validateForm($formName, &$fields, &$files, &$form, &$errors) {
  if ($formName == 'CRM_Contribute_Form_Contribution_Main') {
    if (!CRM_Utils_System::isUserLoggedIn()) {
      return;
    }

    // Check Membership configured in the form.
    list($formMembershipTypeIds, $membershipExtras, $groupConfigured) = CRM_Membershipextra_Utils::getMembershipTypeConfiguredonForm($form);
    // if no membership on form, do not go further.
    if (empty($formMembershipTypeIds))
      return;

    // get contact ID
    $contact_id = _membershipextra_get_form_contact_id($form);
    if (!$contact_id) {
      return;
    }
    // get groups associated with contact
    $groupContact = [];
    $groups = CRM_Core_PseudoConstant::nestedGroup();
    if (!empty($groupConfigured)) {
      $result = civicrm_api3('Contact', 'getsingle', [
        'return' => ["group"],
        'id' => $contact_id,
      ]);

      if (!empty($result['groups'])) {
        $groupContact = explode(',', $result['groups']);
      }
    }
    // get contact membership types
    list($contactMembershipType, $contactMembershipTypeDetails) = CRM_Membershipextra_Utils::getContactMemberships($contact_id);
    foreach ($fields as $key => $value) {
      if ((substr($key, 0, 6) == 'price_') && is_numeric(substr($key, 6))) {
        if (!is_array($value)) {
          if (array_key_exists($value, $formMembershipTypeIds)) { // this is membership type option
            $submittedMembershipType = $formMembershipTypeIds[$value]; // get submitted membership type id
            if (in_array($submittedMembershipType, $contactMembershipType)) {
              if (array_key_exists($submittedMembershipType, $membershipExtras)) {

                $membershipDetail = $membershipExtras[$submittedMembershipType];
                
                $validateRenewalLimit = TRUE;
                if ($membershipDetail['period_type'] == 'fixed' && !empty($membershipDetail['limit_renewal'])) {

                  $memberhipID = CRM_Utils_Array::key($submittedMembershipType, $contactMembershipType);
                  $endDate = $contactMembershipTypeDetails[$memberhipID]['end_date'];
                  list($validateRenewalLimit, $rollverDayFomated) = CRM_Membershipextra_Utils::validation($submittedMembershipType, $endDate, $membershipDetail);

                }
                elseif ($membershipDetail['period_type'] == 'rolling' && !empty($membershipDetail['allow_renewal_before'])) {

                  $memberhipID = CRM_Utils_Array::key($submittedMembershipType, $contactMembershipType);
                  $endDate = $contactMembershipTypeDetails[$memberhipID]['end_date'];
                  list($validateRenewalLimit, $rollverDayFomated) = CRM_Membershipextra_Utils::validationRolling($submittedMembershipType, $endDate, $membershipDetail);

                }

                // if denied
                if (!$validateRenewalLimit) {
                  $errors[$key] = ts("You already have Active Membership, Admin disabled additional renewal until {$rollverDayFomated}.");
                }

                // process restrict gorup id on membership type.
                if (!empty($membershipDetail['restrict_to_groups'])) {
                  $isGroupPresent = array_intersect($membershipDetail['restrict_to_groups'], $groupContact);
                  $groupRequired = [];
                  foreach ($membershipDetail['restrict_to_groups'] as $gid) {
                    $groupRequired[] = $groups[$gid];
                  }
                  // show the list of groups with error message
                  $groupRequiredList = implode(', ', $groupRequired);
                  if (empty($isGroupPresent)) {
                    $errors[$key] = ts("This Membership subscription only available for {$groupRequiredList} groups(s).");
                  }
                }
              }
            }
          }
        }
      }
    }
  }
}

function _membershipextra_get_form_contact_id($form) {
  if (!empty($form->_pId)) {
    $contact_id = $form->_pId;
  }
  // Look for contact_id in the form.
  else if ($form->getVar('_contactID')) {
    $contact_id = $form->getVar('_contactID');
  }
  // note that contact id variable is not consistent on some forms hence we need this double check :(
  // we need to clean up CiviCRM code sometime in future
  else if ($form->getVar('_contactId')) {
    $contact_id = $form->getVar('_contactId');
  }
  // Otherwise look for contact_id in submit values.
  else if (!empty($form->_submitValues['contact_select_id'][1])) {
    $contact_id = $form->_submitValues['contact_select_id'][1];
  }
  // Otherwise use the current logged-in user.
  else {
    $contact_id = CRM_Core_Session::singleton()->get('userID');
  }

  //For anonymous user fetch contact ID on basis of checksum
  if (empty($contact_id)) {
    $cid = CRM_Utils_Request::retrieve('cid', 'Positive', $form);

    if (!empty($cid)) {
      //check if this is a checksum authentication
      $userChecksum = CRM_Utils_Request::retrieve('cs', 'String', $form);
      if ($userChecksum) {
        //check for anonymous user.
        $validUser = CRM_Contact_BAO_Contact_Utils::validChecksum($cid, $userChecksum);
        if ($validUser) {
          return $cid;
        }
      }
    }
  }

  return $contact_id;
}

// https://github.com/freeform/prorated_memberships/blob/master/prorated_memberships.module
// https://github.com/strangerstudios/pmpro-membership-card/blob/master/pmpro-membership-card.php
// https://github.com/aghstrategies/com.aghstrategies.proratemembership
// https://www.smith-consulting.com/Portals/0/docs/SmithCartManual/NetHelp/index.html#!Documents/giftcardcertificatesserialnumbers.htm
// https://www.voucherify.io/blog/powerful-gift-card-voucher-system
// https://github.com/joashp/simple-php-coupon-code-generator
// http://sparagino.it/2015/11/27/unique-random-coupon-code-generation-in-php/