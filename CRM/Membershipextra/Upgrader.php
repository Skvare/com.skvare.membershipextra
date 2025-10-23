<?php
use CRM_Membershipextra_ExtensionUtil as E;

/**
 * Collection of upgrade steps.
 */
class CRM_Membershipextra_Upgrader extends CRM_Extension_Upgrader_Base {

  // By convention, functions that look like "function upgrade_NNNN()" are
  // upgrade tasks. They are executed in order (like Drupal's hook_update_N).

  /**
   * Example: Run an external SQL script when the module is installed.
   */
  public function install() {
    $this->installMetaData();
  }

  public function installMetaData() {
    try {
      civicrm_api3('CustomSearch', 'create', [
        'sequential' => 1,
        'option_group_id' => "custom_search",
        'name' => "CRM_Membershipextra_Form_Search_Membership",
        'is_active' => 1,
        'label' => "CRM_Membershipextra_Form_Search_Membership",
        'description' => "Membership Include/Exclude",
      ]);
    }
    catch (CRM_Core_Exception $e) {
      $msg = 'Exception thrown in ' . __METHOD__ . '. Likely the CustomSearch value already exists.';
      CRM_Core_Error::debug_log_message($msg, FALSE, 'com.skvare.membershipextra');
    }
  }

  public function upgrade_1001() {
    $this->ctx->log->info('Applying update 1001');

    $allCiviSettings = Civi::settings()->all();

    $ourNewSettingsNames = CRM_Membershipextra_Utils::getSettingsNames();
    $ourSettingsNames =
      array_combine($ourNewSettingsNames, $ourNewSettingsNames)
      + [
        'allow_renewal_before' => 'renewal_period_number',
        'allow_renewal_before_unit' => 'renewal_period_unit',
      ];

    foreach ($ourSettingsNames as $ourSettingName => $ourNewSettingName) {
      $nameLen = strlen($ourSettingName);
      foreach ($allCiviSettings as $existingKey => $value) {
        if (substr($existingKey, 0, $nameLen) === $ourSettingName) {
          $membershipTypeID = substr($existingKey, $nameLen + 1);
          if (!is_numeric($membershipTypeID)) {
            continue;
          }
          $newKey = "Membershipextra:$ourNewSettingName:$membershipTypeID";
          Civi::settings()->set($newKey, $value);
          Civi::settings()->revert($existingKey);
        }
      }
    }

    return TRUE;
  }

}
