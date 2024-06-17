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
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_uninstall
 */
function membershipextra_civicrm_uninstall() {
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
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_upgrade
 */
function membershipextra_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return;
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
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_alterSettingsFolders
 */
function membershipextra_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
 */
function membershipextra_civicrm_entityTypes(&$entityTypes) {
}

/**
 * Implements hook_civicrm_thems().
 */
function membershipextra_civicrm_themes(&$themes) {
}

function membershipextra_civicrm_buildForm($formName, &$form) {
  if ($formName == 'CRM_Member_Form_MembershipType') {
    $membershipTypes = CRM_Member_PseudoConstant::membershipType();
    if ($form->_action & CRM_Core_Action::UPDATE) {
      unset($membershipTypes[$form->_id]);
    }

    $form->addElement('checkbox', 'limit_renewal', E::ts('Limit renewal to 1 period ahead'));

    $form->add('text', 'renewal_period_number', E::ts('Disallow renewal until'));
    $units = CRM_Core_SelectValues::unitList();
    unset($units['year']);

    $form->addElement('select', 'renewal_period_unit', E::ts('Disallow renewal until'), ['' => '-select'] + $units);

    $groups = CRM_Core_PseudoConstant::nestedGroup();
    $form->add('select', 'restrict_to_groups', E::ts('Only allow members of groups'),
      $groups, FALSE, ['class' => 'crm-select2 huge', 'multiple' => 1]);

    $form->add('checkbox', 'check_unauthenticated_contacts', E::ts('Apply renewal restrictions to unauthenticated users'));

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
      $id = CRM_Core_DAO::getFieldValue(
        'CRM_Member_DAO_MembershipType',
        $form->_submitValues['name'],
        'id',
        'name');
    }
    if ($id) {
      $membershipExtras = [
        'limit_renewal' => $form->_submitValues['limit_renewal'] ?? 0,
        'renewal_period_number' => $form->_submitValues['renewal_period_number'] ?? 0,
        'renewal_period_unit' => $form->_submitValues['renewal_period_unit'] ?? '',
        'restrict_to_groups' => $form->_submitValues['restrict_to_groups'] ?? '',
        'check_unauthenticated_contacts' => $form->_submitValues['check_unauthenticated_contacts'] ?? 0,
      ];
      foreach ($membershipExtras as $name => $value) {
        CRM_Membershipextra_Utils::setSetting($value, $name, $id);
      }
    }
  }
}

function membershipextra_civicrm_validateForm($formName, &$fields, &$files, &$form, &$errors) {
  if ($formName !== 'CRM_Contribute_Form_Contribution_Main') {
    return;
  }

  // Check Membership configured in the form.
  [$formMembershipTypeIds, $membershipExtras, $groupConfigured] =
    CRM_Membershipextra_Utils::getMembershipTypeConfiguredonForm($form);
  // if no membership on form, do not go further.
  if (empty($formMembershipTypeIds)) {
    return;
  }

  foreach ($fields as $key => $value) {
    if ((substr($key, 0, 6) == 'price_')
      && is_numeric(substr($key, 6))
      && !is_array($value)
      && array_key_exists($value, $formMembershipTypeIds)) {
      // this is membership type option
      // get submitted membership type id
      $submittedMembershipType = $formMembershipTypeIds[$value];
      break;
    }
  }

  $settings = CRM_Membershipextra_Utils::getSettings($submittedMembershipType);

  // get contact ID
  $contact_id = _membershipextra_get_form_contact_id($form, $fields, $settings);
  if (!$contact_id) {
    return;
  }
  // get groups associated with contact
  $groupContact = [];
  $groups = CRM_Core_PseudoConstant::nestedGroup(FALSE);
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
  [$contactMembershipType, $contactMembershipTypeDetails] =
    CRM_Membershipextra_Utils::getContactMemberships($contact_id);

  if (in_array($submittedMembershipType, $contactMembershipType)) {
    if (array_key_exists($submittedMembershipType, $membershipExtras)) {

      $membershipDetail = $membershipExtras[$submittedMembershipType];

      $validateRenewalLimit = TRUE;
      if ($membershipDetail['period_type'] == 'fixed' && !empty($membershipDetail['limit_renewal'])) {

        $membershipID = CRM_Utils_Array::key($submittedMembershipType, $contactMembershipType);
        $endDate = $contactMembershipTypeDetails[$membershipID]['end_date'];
        [
          $validateRenewalLimit,
          $rolloverDayFormatted,
        ] = CRM_Membershipextra_Utils::validation($submittedMembershipType, $endDate, $membershipDetail);

      }
      elseif ($membershipDetail['period_type'] == 'rolling' && !empty($membershipDetail['renewal_period_number'])) {

        $membershipID = CRM_Utils_Array::key($submittedMembershipType, $contactMembershipType);
        $endDate = $contactMembershipTypeDetails[$membershipID]['end_date'];
        [
          $validateRenewalLimit,
          $rolloverDayFormatted,
        ] = CRM_Membershipextra_Utils::validationRolling($submittedMembershipType, $endDate, $membershipDetail);

      }

      // if denied
      if (!$validateRenewalLimit) {
        $errors[$key] = E::ts('It is too early to renew your membership. Please try again after %1.',
          [1 => $rolloverDayFormatted]);
      }
    }
  }
  // process restrict group id on membership type.
  $membershipDetail = $membershipExtras[$submittedMembershipType];
  if (!empty($membershipDetail['restrict_to_groups'])) {
    $isGroupPresent = array_intersect($membershipDetail['restrict_to_groups'], $groupContact);
    $groupRequired = [];
    foreach ($membershipDetail['restrict_to_groups'] as $gid) {
      $groupRequired[] = $groups[$gid];
    }
    // show the list of groups with error message
    $groupRequiredList = implode(', ', $groupRequired);
    if (empty($isGroupPresent)) {
      $errors[$key] = E::ts('This membership type is only available to members of the following group(s): %1. Please contact the administrator.', [1 => $groupRequiredList]);
    }
  }
}

function _membershipextra_get_form_contact_id($form, $fields, $settings) {
  /*if (!empty($form->_pId)) {
    $contact_id = $form->_pId;
  }
  // Look for contact_id in the form.
  elseif ($form->getVar('_contactID')) {
    $contact_id = $form->getVar('_contactID');
  }
  // note that contact id variable is not consistent on some forms hence we need this double check :(
  // we need to clean up CiviCRM code sometime in future
  elseif ($form->getVar('_contactId')) {
    $contact_id = $form->getVar('_contactId');
  }
  // Otherwise look for contact_id in submit values.
  elseif (!empty($form->_submitValues['contact_select_id'][1])) {
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
  }*/

  $contact_id = $form->getContactID();

  if (empty($contact_id) && $settings['check_unauthenticated_contacts']) {
    // copied from \CRM_Contribute_Form_Contribution_Confirm::processFormSubmission
    // CRM/Contribute/Form/Contribution/Confirm.php:2323
    $dupeParams = $fields;

    if (!empty($dupeParams['onbehalf'])) {
      unset($dupeParams['onbehalf']);
    }
    if (!empty($dupeParams['honor'])) {
      unset($dupeParams['honor']);
    }

    $contact_id = CRM_Contact_BAO_Contact::getFirstDuplicateContact(
      $dupeParams,
      'Individual',
      'Unsupervised',
      [],
      FALSE
    );
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
