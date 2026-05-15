<?php

use CRM_Membershipextra_ExtensionUtil as E;

class CRM_Membershipextra_Utils {

  /**
   * Returns the list of custom setting names managed by this extension.
   * Each setting is stored per membership type using the key format:
   * "Membershipextra:<name>:<membershipTypeId>" in Civi::settings().
   */
  public static function getSettingsNames(): array {
    return [
      'limit_renewal',           // block renewal before the rollover window opens
      'renewal_period_number',   // how many units before end date renewal opens (rolling)
      'renewal_period_unit',     // unit for renewal_period_number (day/month/year)
      'restrict_to_groups',      // limit membership availability to specific contact groups
      'check_unauthenticated_contacts', // apply group check even for anonymous users
      'levels_for_upgrade',      // membership type IDs this type is allowed to upgrade to
    ];
  }

  /**
   * Returns all extension settings for a given membership type ID.
   * Keys match getSettingsNames(); missing settings return NULL.
   *
   * @param int $typeID Membership type ID
   * @return array
   */
  public static function getSettings($typeID) {
    foreach (self::getSettingsNames() as $name) {
      // Each setting is namespaced by extension + setting name + type ID
      // so settings from different membership types never collide.
      $settings[$name] = Civi::settings()->get("Membershipextra:$name:$typeID");
    }
    return $settings ?? [];
  }

  /**
   * Persists a single extension setting for a given membership type.
   *
   * @param mixed $value
   * @param string $name One of getSettingsNames()
   * @param int $typeID Membership type ID
   */
  public static function setSetting($value, $name, $typeID) {
    Civi::settings()->set("Membershipextra:$name:$typeID", $value);
  }

  /**
   * Builds a map of all membership types configured on the contribution form,
   * merging core CiviCRM membership type details with this extension's settings.
   *
   * Returns three values:
   *   $formMembershipTypeIds  — array( price_option_id => membership_type_id )
   *   $membershipExtras       — array( membership_type_id => merged settings + core details )
   *   $groupConfigured        — flat unique list of all group IDs across all types (for restrict_to_groups)
   *
   * @param CRM_Core_Form $form
   * @return array [$formMembershipTypeIds, $membershipExtras, $groupConfigured]
   */
  public static function getMembershipTypeConfiguredonForm($form) {
    $formMembershipTypeIds = $membershipExtras = $groupConfigured = [];
    foreach ($form->_values['fee'] as $fee) {
      if (!is_array($fee['options'])) {
        continue;
      }
      foreach ($fee['options'] as $option_id => $option) {
        // Only process price options that represent a membership type.
        if (isset($option['membership_type_id'])) {
          $membershipTypeId = $option['membership_type_id'];

          // Map price option ID → membership type ID for fast lookup during validation.
          $formMembershipTypeIds[$option_id] = $membershipTypeId;

          // Load this extension's custom settings for the membership type.
          $membershipExtras[$membershipTypeId] = CRM_Membershipextra_Utils::getSettings($membershipTypeId);
          $membershipExtras[$membershipTypeId]['my_id'] = $membershipTypeId;

          // Load core CiviCRM membership type details (period type, duration, rollover dates).
          $membershipExtrasDetails = CRM_Member_BAO_MembershipType::getMembershipType($membershipTypeId);
          $membershipExtrasAdditionalDetails = [$membershipTypeId => $membershipExtrasDetails];

          // Convert numeric rollover day/month values to human-readable text (e.g. "January 1").
          CRM_Member_BAO_MembershipType::convertDayFormat($membershipExtrasAdditionalDetails);

          $membershipExtrasDetails = reset($membershipExtrasAdditionalDetails);

          if ($membershipExtrasDetails['period_type'] == 'fixed') {
            // Fixed memberships run on a calendar cycle (e.g. Jan 1 – Dec 31).
            // Copy the fields relevant to fixed-period renewal validation.
            $membershipExtras[$membershipTypeId]['period_type'] = 'fixed';
            $membershipExtras[$membershipTypeId]['duration_unit'] = $membershipExtrasDetails['duration_unit'];
            $membershipExtras[$membershipTypeId]['duration_interval'] = $membershipExtrasDetails['duration_interval'];
            $membershipExtras[$membershipTypeId]['fixed_period_start_day'] = $membershipExtrasDetails['fixed_period_start_day'] ?? NULL;
            $membershipExtras[$membershipTypeId]['fixed_period_rollover_day'] = $membershipExtrasDetails['fixed_period_rollover_day'];

            // Rolling-only settings are not applicable; remove them to avoid confusion.
            unset($membershipExtras[$membershipTypeId]['renewal_period_number']);
            unset($membershipExtras[$membershipTypeId]['renewal_period_unit']);
          }
          else {
            // Rolling memberships expire relative to the join date (e.g. 1 year from signup).
            $membershipExtras[$membershipTypeId]['period_type'] = 'rolling';

            // Fixed-only setting is not applicable; remove it to avoid confusion.
            unset($membershipExtras[$membershipTypeId]['limit_renewal']);
          }

          // Collect group IDs across all membership types so we can do a single
          // contact-group lookup later instead of one API call per type.
          if (!empty($membershipExtras[$membershipTypeId]['restrict_to_groups'])) {
            $groupConfigured = array_merge($groupConfigured, $membershipExtras[$membershipTypeId]['restrict_to_groups']);
          }
        }
      }
    }

    // Deduplicate group IDs that appear on multiple membership types.
    $groupConfigured = array_unique($groupConfigured);

    return [$formMembershipTypeIds, $membershipExtras, $groupConfigured];
  }

  /**
   * Returns all active, non-test memberships for a contact.
   *
   * Returns two arrays both keyed by membership ID (not membership type ID),
   * so a contact with multiple active memberships of different types is handled correctly:
   *   $contactMembershipType        — array( membership_id => membership_type_id )
   *   $contactMembershipTypeDetails — array( membership_id => ['type' => ..., 'end_date' => ...] )
   *
   * @param int $contact_id
   * @return array [$contactMembershipType, $contactMembershipTypeDetails]
   */
  public static function getContactMemberships($contact_id) {
    $contactMembershipType = [];
    $resultMembership = civicrm_api3('Membership', 'get', [
      'contact_id' => $contact_id,
      'active_only' => 1,
      'is_test' => 0,
    ]);

    if (!empty($resultMembership['values'])) {
      foreach ($resultMembership['values'] as $membership) {
        // Key by membership ID (not type ID) so the end_date can be retrieved
        // by membership record when checking renewal windows.
        $contactMembershipType[$membership['id']] = $membership['membership_type_id'];
        $contactMembershipTypeDetails[$membership['id']] = ['type' => $membership['membership_type_id'], 'end_date' => $membership['end_date']];
      }
    }

    return [$contactMembershipType, $contactMembershipTypeDetails];
  }

  /**
   * Determines whether a fixed-period membership is within its renewal window.
   *
   * Renewal is allowed if:
   *   (a) The membership has already expired (current date > end date), or
   *   (b) The current date falls between the rollover day and the next period's end date.
   *
   * For yearly memberships the rollover day is evaluated against the membership's
   * end year, so a "November 1" rollover always refers to the correct calendar year.
   *
   * Returns [bool $allowed, string $rolloverDayFormatted].
   *
   * @param int $membershipTypeId
   * @param string $currentEndDate (Y-m-d)
   * @param array $membershipDetail
   * @return array
   */
  public static function validation($membershipTypeId, $currentEndDate, $membershipDetail) {
    // Extract the year from the current end date to anchor rollover calculations.
    $membershipEndYear = date('Y', strtotime($currentEndDate));

    if ($membershipDetail['duration_unit'] == 'year') {
      $startDate = date('Ymd', strtotime($membershipDetail['fixed_period_start_day'] . ' ' . $membershipEndYear));
      // Rollover day: the earliest date the member may renew in the current cycle.
      $rollverDay = date('Ymd', strtotime($membershipDetail['fixed_period_rollover_day'] . ' ' . $membershipEndYear));

      // The next period's end date is one year after the start date, minus one day.
      $dateNewEdnDate = date('Ymd', strtotime("{$startDate} - 1 day + 1 year"));
      $currentDate = date('Ymd');

      $rollverDayFomated = CRM_Utils_Date::customFormat($rollverDay);

      // If the membership has already expired, always allow renewal.
      if ($currentDate > date('Ymd', strtotime($currentEndDate))) {
        return [TRUE, $rollverDayFomated];
      }
      // Allow if today is inside the rollover window (rollover day ≤ today ≤ next end date).
      if ($currentDate >= $rollverDay && $currentDate <= $dateNewEdnDate) {
        return [TRUE, $rollverDayFomated];
      }

      return [FALSE, $rollverDayFomated];
    }
    elseif ($membershipDetail['duration_unit'] == 'month') {
      // For monthly fixed memberships, anchor the rollover to the end month/year.
      $membershipEndYear = date(' M Y', strtotime($currentEndDate));
      $rollverDay = date('Ymd', strtotime($membershipDetail['fixed_period_rollover_day'] . ' ' . $membershipEndYear));
      $currentDate = date('Ymd');

      $rollverDayFomated = CRM_Utils_Date::customFormat($rollverDay);

      // Allow once today reaches or passes the rollover day.
      if ($currentDate >= $rollverDay) {
        return [TRUE, $rollverDayFomated];
      }

      return [FALSE, $rollverDayFomated];
    }
  }

  /**
   * Determines whether a rolling membership is within its renewal window.
   *
   * For rolling memberships the renewal window opens a configured period before
   * the end date (e.g. 30 days). The rollover day is:
   *   end_date - renewal_period_number renewal_period_unit - 1 day
   *
   * Example: end date = 2026-12-31, renewal_period = 30 days
   *   rollover day = 2026-12-31 - 30 days - 1 day = 2026-11-30
   *   → renewal opens on November 30.
   *
   * Returns [bool $allowed, string $rolloverDayFormatted].
   *
   * @param int $membershipTypeId
   * @param string $currentEndDate (Y-m-d)
   * @param array $membershipDetail
   * @return array
   */
  public static function validationRolling($membershipTypeId, $currentEndDate, $membershipDetail) {
    // Calculate the rollover day by subtracting the configured renewal period from the end date.
    // The "+ 1 day" offset is included in the period string so we subtract one extra day,
    // meaning the window opens the day *before* the period boundary.
    $rolloverPeriodDay = $membershipDetail['renewal_period_number'] . ' ' . $membershipDetail['renewal_period_unit'] . ' + 1 day';
    $rollverDay = date('Ymd', strtotime("{$currentEndDate} - {$rolloverPeriodDay}"));

    $rollverDayFomated = CRM_Utils_Date::customFormat($rollverDay);
    $currentDate = date('Ymd');

    // Allow renewal once today reaches or passes the rollover day.
    if ($currentDate >= $rollverDay) {
      return [TRUE, $rollverDayFomated];
    }

    return [FALSE, $rollverDayFomated];
  }

}
