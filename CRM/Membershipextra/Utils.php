<?php

use CRM_Membershipextra_ExtensionUtil as E;

class CRM_Membershipextra_Utils {

  public static function getSettingsNames(): array {
    return [
      'limit_renewal',
      'renewal_period_number',
      'renewal_period_unit',
      'restrict_to_groups',
      'check_unauthenticated_contacts',
    ];
  }

  /**
   * @param $typeID
   * @return array
   */
  public static function getSettings($typeID) {
    foreach (self::getSettingsNames() as $name) {
      $settings[$name] = Civi::settings()->get("Membershipextra:$name:$typeID");
    }
    return $settings ?? [];
  }

  /**
   * @param $value
   * @param $name
   * @param $typeID
   */
  public static function setSetting($value, $name, $typeID) {
    Civi::settings()->set("Membershipextra:$name:$typeID", $value);
  }

  /**
   * Function to provide Membership type details for each membership type present on the online form.
   * @param $form
   * @return array
   */
  public static function getMembershipTypeConfiguredonForm($form) {
    $formMembershipTypeIds = $membershipExtras = $groupConfigured = [];
    foreach ($form->_values['fee'] as $fee) {
      if (!is_array($fee['options'])) {
        continue;
      }
      foreach ($fee['options'] as $option_id => $option) {
        // if priceset contain the membership type, then proceed.
        if (isset($option['membership_type_id'])) {
          $membershipTypeId = $option['membership_type_id'];
          $formMembershipTypeIds[$option_id] = $membershipTypeId;

          // get Custom Setting for each Membership type
          $membershipExtras[$membershipTypeId] = CRM_Membershipextra_Utils::getSettings($membershipTypeId);
          $membershipExtras[$membershipTypeId]['my_id'] = $membershipTypeId;

          // get membership details from civicrm core function.
          $membershipExtrasDetails = CRM_Member_BAO_MembershipType::getMembershipType($membershipTypeId);
          $membershipExtrasAdditionalDetails = [$membershipTypeId => $membershipExtrasDetails];

          // convert rollover over period to Text format.
          CRM_Member_BAO_MembershipType::convertDayFormat($membershipExtrasAdditionalDetails);

          $membershipExtrasDetails = reset($membershipExtrasAdditionalDetails);

          // if membership of fixed type
          if ($membershipExtrasDetails['period_type'] == 'fixed') {
            $membershipExtras[$membershipTypeId]['period_type'] = 'fixed';
            $membershipExtras[$membershipTypeId]['duration_unit'] = $membershipExtrasDetails['duration_unit'];
            $membershipExtras[$membershipTypeId]['duration_interval'] = $membershipExtrasDetails['duration_interval'];
            $membershipExtras[$membershipTypeId]['fixed_period_start_day'] = CRM_Utils_Array::value('fixed_period_start_day', $membershipExtrasDetails);
            $membershipExtras[$membershipTypeId]['fixed_period_rollover_day'] = $membershipExtrasDetails['fixed_period_rollover_day'];

            // unset custom field belong to rolling type
            unset($membershipExtras[$membershipTypeId]['renewal_period_number']);
            unset($membershipExtras[$membershipTypeId]['renewal_period_unit']);
          }
          else {
            $membershipExtras[$membershipTypeId]['period_type'] = 'rolling';

            // unset custom field belong to fixed type
            unset($membershipExtras[$membershipTypeId]['limit_renewal']);
          }

          // check group limitation applied on membership type
          if (!empty($membershipExtras[$membershipTypeId]['restrict_to_groups'])) {
            $groupConfigured = array_merge($groupConfigured, $membershipExtras[$membershipTypeId]['restrict_to_groups']);
          }
        }
      }
    }

    // this is all group configured acrros multiple membership types, make theme unique.
    $groupConfigured = array_unique($groupConfigured);

    return [$formMembershipTypeIds, $membershipExtras, $groupConfigured];
  }

  /**
   * Function to get current activie membership to provided contact id
   * @param $contact_id
   * @return array
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
        $contactMembershipType[$membership['id']] = $membership['membership_type_id'];
        $contactMembershipTypeDetails[$membership['id']] = ['type' => $membership['membership_type_id'], 'end_date' => $membership['end_date']];
      }
    }

    return [$contactMembershipType, $contactMembershipTypeDetails];
  }

  /**
   * Function to validate Fixed type of membership types
   * @param $membershipTypeId
   * @param $currentEndDate
   * @param $membershipDetail
   * @return array
   */
  public static function validation($membershipTypeId, $currentEndDate, $membershipDetail) {
    // get end date membership year
    $membershipEndYear = date('Y', strtotime($currentEndDate));
    // this for when duration is in 'Year'
    if ($membershipDetail['duration_unit'] == 'year') {

      // get start date
      $startDate = date('Ymd', strtotime($membershipDetail['fixed_period_start_day'] . ' ' . $membershipEndYear));
      // get rollover date based on current membership End year with rollover over day month
      $rollverDay = date('Ymd', strtotime($membershipDetail['fixed_period_rollover_day'] . ' ' . $membershipEndYear));

      // Calculate New end.
      $dateNewEdnDate = date('Ymd', strtotime("{$startDate} - 1 day + 1 year"));
      $currentDate = date('Ymd');

      // show when renewal is allowed
      $rollverDayFomated = CRM_Utils_Date::customFormat($rollverDay);

      // current day is in between rollver day and actual end date, then allow renewal
      if ($currentDate >= $rollverDay && $currentDate <= $dateNewEdnDate) {
        // allow
        return [TRUE, $rollverDayFomated];
      }

      // deny
      return [FALSE, $rollverDayFomated];
    }
    elseif ($membershipDetail['duration_unit'] == 'month') {
      $membershipEndYear = date(' M Y', strtotime($currentEndDate));
      $rollverDay = date('Ymd', strtotime($membershipDetail['fixed_period_rollover_day'] . ' ' . $membershipEndYear));
      $currentDate = date('Ymd');

      // show when renewal is allowed
      $rollverDayFomated = CRM_Utils_Date::customFormat($rollverDay);

      // current day is greater than rollver day, then allow renewal
      if ($currentDate >= $rollverDay) {
        // allow
        return [TRUE, $rollverDayFomated];
      }

      // deny
      return [FALSE, $rollverDayFomated];
    }
  }

  /**
   * Function to validate rollover date for rolling membership tpyes
   * @param $membershipTypeId
   * @param $currentEndDate
   * @param $membershipDetail
   * @return array
   */
  public static function validationRolling($membershipTypeId, $currentEndDate, $membershipDetail) {
    // substract the day and month from current end date, that;s the rollover day for rolling membership type
    $rolloverPeriodDay = $membershipDetail['renewal_period_number'] . ' ' . $membershipDetail['renewal_period_unit'] . ' + 1 day';
    $rollverDay = date('Ymd', strtotime("{$currentEndDate} - {$rolloverPeriodDay}"));

    // show when renewal is allowed
    $rollverDayFomated = CRM_Utils_Date::customFormat($rollverDay);
    $currentDate = date('Ymd');
    // current day is greater than rollver day, then allow renewal
    if ($currentDate >= $rollverDay) {
      // allow
      return [TRUE, $rollverDayFomated];
    }

    // deny
    return [FALSE, $rollverDayFomated];
  }

}

