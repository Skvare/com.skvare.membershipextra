<?php

class CRM_Membershipextra_Form_Report_Member_Renewal extends CRM_Report_Form {

  protected $_addressField = FALSE;

  protected $_emailField = FALSE;

  protected $_phoneField = FALSE;

  protected $_contribField = FALSE;

  protected $_summary = NULL;

  protected $_customGroupExtends = [
    'Membership',
    'Contribution',
    'Contact',
    'Individual',
    'Household',
    'Organization',
  ];

  protected $_customGroupGroupBy = FALSE;

  /**
   * This report has not been optimised for group filtering.
   *
   * The functionality for group filtering has been improved but not
   * all reports have been adjusted to take care of it. This report has not
   * and will run an inefficient query until fixed.
   *
   * CRM-19170
   *
   * @var bool
   */
  protected $groupFilterNotOptimised = TRUE;

  /**
   * Class constructor.
   */
  public function __construct() {

    // Check if CiviCampaign is a) enabled and b) has active campaigns
    $config = CRM_Core_Config::singleton();
    $campaignEnabled = in_array("CiviCampaign", $config->enableComponents);
    if ($campaignEnabled) {
      $getCampaigns = CRM_Campaign_BAO_Campaign::getPermissionedCampaigns(NULL, NULL, TRUE, FALSE, TRUE);
      $this->activeCampaigns = $getCampaigns['campaigns'];
      asort($this->activeCampaigns);
    }

    $this->_columns = [
        'civicrm_contact' => [
          'dao' => 'CRM_Contact_DAO_Contact',
          'fields' => $this->getBasicContactFields(),
          'filters' => [
            'sort_name' => [
              'title' => ts('Contact Name'),
              'operator' => 'like',
            ],
            'id' => ['no_display' => TRUE],
          ],
          'order_bys' => [
            'sort_name' => [
              'title' => ts('Last Name, First Name'),
              'default' => '1',
              'default_weight' => '0',
              'default_order' => 'ASC',
            ],
          ],
          'grouping' => 'contact-fields',
        ],
        'civicrm_membership' => [
          'dao' => 'CRM_Member_DAO_Membership',
          'fields' => [
            'membership_type_id' => [
              'title' => ts('Membership Type'),
              'required' => TRUE,
              'no_repeat' => TRUE,
            ],
            'membership_start_date' => [
              'title' => ts('Start Date'),
              'default' => TRUE,
            ],
            'membership_end_date' => [
              'title' => ts('End Date'),
              'default' => TRUE,
            ],
            'join_date' => [
              'title' => ts('Join Date'),
              'default' => TRUE,
            ],
            'source' => ['title' => ts('Source')],
          ],
          'filters' => [
            'join_date' => [
              'title' => ts('Member Since'),
              'operatorType' => CRM_Report_Form::OP_DATE
            ],
            'membership_start_date' => [
              'title' => ts('Membership Start Date'),
              'operatorType' => CRM_Report_Form::OP_DATE
            ],
            'membership_end_date' => [
              'title' => ts('Membership End Date'),
              'operatorType' => CRM_Report_Form::OP_DATE
            ],
            'owner_membership_id' => [
              'title' => ts('Membership Owner ID'),
              'operatorType' => CRM_Report_Form::OP_INT,
            ],
            'tid' => [
              'name' => 'membership_type_id',
              'title' => ts('Membership Types'),
              'type' => CRM_Utils_Type::T_INT,
              'operatorType' => CRM_Report_Form::OP_MULTISELECT,
              'options' => CRM_Member_PseudoConstant::membershipType(),
            ],
          ],
          'order_bys' => [
            'membership_type_id' => [
              'title' => ts('Membership Type'),
              'default' => '0',
              'default_weight' => '1',
              'default_order' => 'ASC',
            ],
          ],
          'grouping' => 'member-fields',
          'group_bys' => [
            'id' => [
              'title' => ts('Membership'),
              'default' => TRUE,
            ],
          ],
        ],
        'civicrm_membership_status' => [
          'dao' => 'CRM_Member_DAO_MembershipStatus',
          'alias' => 'mem_status',
          'fields' => [
            'name' => [
              'title' => ts('Status'),
              'default' => TRUE,
            ],
          ],
          'filters' => [
            'sid' => [
              'name' => 'id',
              'title' => ts('Status'),
              'type' => CRM_Utils_Type::T_INT,
              'operatorType' => CRM_Report_Form::OP_MULTISELECT,
              'options' => CRM_Member_PseudoConstant::membershipStatus(NULL, NULL, 'label'),
            ],
          ],
          'grouping' => 'member-fields',
        ],
        'civicrm_email' => [
          'dao' => 'CRM_Core_DAO_Email',
          'fields' => ['email' => NULL],
          'grouping' => 'contact-fields',
        ],
        'civicrm_phone' => [
          'dao' => 'CRM_Core_DAO_Phone',
          'fields' => ['phone' => NULL],
          'grouping' => 'contact-fields',
        ],
        'civicrm_activity' => [
          'dao' => 'CRM_Activity_DAO_Activity',
          'fields' => [
            'activity_date_time' => [
              'title' => ts('Renewal Date'),
              'required' => TRUE,
            ],
          ],
          'filters' => [
            'activity_date_time' => [
              'default' => 'this.month',
              'title' => ts('Renewal Date'),
              'operatorType' => CRM_Report_Form::OP_DATE,
            ],
          ],
          'grouping' => 'activity-fields',
          'alias' => 'activity',
        ],
        'civicrm_contribution' => [
          'dao' => 'CRM_Contribute_DAO_Contribution',
          'fields' => [
            'contribution_id' => [
              'name' => 'id',
              'no_display' => TRUE,
              'required' => TRUE,
            ],
            'financial_type_id' => ['title' => ts('Financial Type')],
            'contribution_status_id' => ['title' => ts('Contribution Status')],
            'payment_instrument_id' => ['title' => ts('Payment Type')],
            'currency' => [
              'required' => TRUE,
              'no_display' => TRUE,
            ],
            'trxn_id' => NULL,
            'receive_date' => NULL,
            'receipt_date' => NULL,
            'fee_amount' => NULL,
            'net_amount' => NULL,
            'total_amount' => [
              'title' => ts('Payment Amount (most recent)'),
              'statistics' => ['sum' => ts('Amount')],
            ],
          ],
          'filters' => [
            'receive_date' => ['operatorType' => CRM_Report_Form::OP_DATE],
            'financial_type_id' => [
              'title' => ts('Financial Type'),
              'operatorType' => CRM_Report_Form::OP_MULTISELECT,
              'options' => CRM_Contribute_PseudoConstant::financialType(),
              'type' => CRM_Utils_Type::T_INT,
            ],
            'payment_instrument_id' => [
              'title' => ts('Payment Type'),
              'operatorType' => CRM_Report_Form::OP_MULTISELECT,
              'options' => CRM_Contribute_PseudoConstant::paymentInstrument(),
              'type' => CRM_Utils_Type::T_INT,
            ],
            'currency' => [
              'title' => ts('Currency'),
              'operatorType' => CRM_Report_Form::OP_MULTISELECT,
              'options' => CRM_Core_OptionGroup::values('currencies_enabled'),
              'default' => NULL,
              'type' => CRM_Utils_Type::T_STRING,
            ],
            'contribution_status_id' => [
              'title' => ts('Contribution Status'),
              'operatorType' => CRM_Report_Form::OP_MULTISELECT,
              'options' => CRM_Contribute_PseudoConstant::contributionStatus(),
              'type' => CRM_Utils_Type::T_INT,
            ],
            'total_amount' => ['title' => ts('Contribution Amount')],
          ],
          'order_bys' => [
            'receive_date' => [
              'title' => ts('Date Received'),
              'default_weight' => '2',
              'default_order' => 'DESC',
            ],
          ],
          'grouping' => 'contri-fields',
        ],
      ] + $this->getAddressColumns([
        // These options are only excluded because they were not previously present.
        'order_by' => FALSE,
        'group_by' => FALSE,
      ]);
    $this->_groupFilter = TRUE;
    $this->_tagFilter = TRUE;

    // If we have active campaigns add those elements to both the fields and filters
    if ($campaignEnabled && !empty($this->activeCampaigns)) {
      $this->_columns['civicrm_membership']['fields']['campaign_id'] = [
        'title' => ts('Campaign'),
        'default' => 'false',
      ];
      $this->_columns['civicrm_membership']['filters']['campaign_id'] = [
        'title' => ts('Campaign'),
        'operatorType' => CRM_Report_Form::OP_MULTISELECT,
        'options' => $this->activeCampaigns,
        'type' => CRM_Utils_Type::T_INT,
      ];
      $this->_columns['civicrm_membership']['order_bys']['campaign_id'] = ['title' => ts('Campaign')];

    }

    $this->_currencyColumn = 'civicrm_contribution_currency';
    parent::__construct();
  }

  public function preProcess() {
    $this->assign('reportTitle', ts('Membership Detail Report'));
    parent::preProcess();
  }

  public function select() {
    $select = $this->_columnHeaders = [];

    foreach ($this->_columns as $tableName => $table) {
      if (array_key_exists('fields', $table)) {
        foreach ($table['fields'] as $fieldName => $field) {
          if (!empty($field['required']) || !empty($this->_params['fields'][$fieldName])) {
            if ($tableName == 'civicrm_email') {
              $this->_emailField = TRUE;
            }
            elseif ($tableName == 'civicrm_phone') {
              $this->_phoneField = TRUE;
            }
            elseif ($tableName == 'civicrm_contribution') {
              $this->_contribField = TRUE;
            }
            $select[] = "{$field['dbAlias']} as {$tableName}_{$fieldName}";
            if (array_key_exists('title', $field)) {
              $this->_columnHeaders["{$tableName}_{$fieldName}"]['title'] = $field['title'];
            }
            $this->_columnHeaders["{$tableName}_{$fieldName}"]['type'] = CRM_Utils_Array::value('type', $field);
          }
        }
      }
    }

    $this->_selectClauses = $select;
    $this->_select = "SELECT " . implode(', ', $select) . " ";
  }

  public function from() {
    $activityTypes = CRM_Core_PseudoConstant::activityType(TRUE, FALSE, FALSE, 'name');
    $renewalActTypeID = CRM_Utils_Array::key('Membership Renewal', $activityTypes);
    $this->_from = "
         FROM  civicrm_contact {$this->_aliases['civicrm_contact']} {$this->_aclFrom}
               INNER JOIN civicrm_membership {$this->_aliases['civicrm_membership']}
                          ON {$this->_aliases['civicrm_contact']}.id =
                             {$this->_aliases['civicrm_membership']}.contact_id AND {$this->_aliases['civicrm_membership']}.is_test = 0
               INNER JOIN civicrm_activity {$this->_aliases['civicrm_activity']} 
                  ON ({$this->_aliases['civicrm_activity']}.source_record_id = {$this->_aliases['civicrm_membership']}.id 
                  AND {$this->_aliases['civicrm_activity']}.activity_type_id = $renewalActTypeID)
               LEFT  JOIN civicrm_membership_status {$this->_aliases['civicrm_membership_status']}
                          ON {$this->_aliases['civicrm_membership_status']}.id =
                             {$this->_aliases['civicrm_membership']}.status_id ";

    if ($this->isTableSelected('civicrm_address')) {
      $this->_from .= "
             LEFT JOIN civicrm_address {$this->_aliases['civicrm_address']}
                       ON {$this->_aliases['civicrm_contact']}.id =
                          {$this->_aliases['civicrm_address']}.contact_id AND
                          {$this->_aliases['civicrm_address']}.is_primary = 1\n";
    }

    //used when email field is selected
    if ($this->_emailField) {
      $this->_from .= "
              LEFT JOIN civicrm_email {$this->_aliases['civicrm_email']}
                        ON {$this->_aliases['civicrm_contact']}.id =
                           {$this->_aliases['civicrm_email']}.contact_id AND
                           {$this->_aliases['civicrm_email']}.is_primary = 1\n";
    }
    //used when phone field is selected
    if ($this->_phoneField) {
      $this->_from .= "
              LEFT JOIN civicrm_phone {$this->_aliases['civicrm_phone']}
                        ON {$this->_aliases['civicrm_contact']}.id =
                           {$this->_aliases['civicrm_phone']}.contact_id AND
                           {$this->_aliases['civicrm_phone']}.is_primary = 1\n";
    }
    //used when contribution field is selected
    if ($this->_contribField) {
      $this->_from .= "
             LEFT JOIN (SELECT membership_id, MAX(contribution_id) contribution_id FROM civicrm_membership_payment group by membership_id) cmp
                 ON {$this->_aliases['civicrm_membership']}.id = cmp.membership_id
             LEFT JOIN civicrm_contribution {$this->_aliases['civicrm_contribution']}
                 ON cmp.contribution_id={$this->_aliases['civicrm_contribution']}.id\n";
    }
  }

  public function postProcess() {

    $this->beginPostProcess();

    // get the acl clauses built before we assemble the query
    $this->buildACLClause($this->_aliases['civicrm_contact']);
    $sql = $this->buildQuery(TRUE);

    $rows = [];
    $this->buildRows($sql, $rows);

    $this->formatDisplay($rows);
    $this->doTemplateAssignment($rows);
    $this->endPostProcess($rows);
  }

  /**
   * Alter display of rows.
   *
   * Iterate through the rows retrieved via SQL and make changes for display purposes,
   * such as rendering contacts as links.
   *
   * @param array $rows
   *   Rows generated by SQL, with an array for each row.
   */
  public function alterDisplay(&$rows) {
    $entryFound = FALSE;
    $checkList = [];

    $contributionTypes = CRM_Contribute_PseudoConstant::financialType();
    $contributionStatus = CRM_Contribute_PseudoConstant::contributionStatus();
    $paymentInstruments = CRM_Contribute_PseudoConstant::paymentInstrument();

    $repeatFound = FALSE;
    foreach ($rows as $rowNum => $row) {
      if ($repeatFound == FALSE ||
        $repeatFound < $rowNum - 1
      ) {
        unset($checkList);
        $checkList = [];
      }
      if (!empty($this->_noRepeats) && $this->_outputMode != 'csv') {
        // not repeat contact display names if it matches with the one
        // in previous row
        foreach ($row as $colName => $colVal) {
          if (in_array($colName, $this->_noRepeats) &&
            $rowNum > 0
          ) {
            if ($rows[$rowNum][$colName] == $rows[$rowNum - 1][$colName] ||
              (!empty($checkList[$colName]) &&
                in_array($colVal, $checkList[$colName]))
            ) {
              $rows[$rowNum][$colName] = "";
              // CRM-15917: Don't blank the name if it's a different contact
              if ($colName == 'civicrm_contact_exposed_id') {
                $rows[$rowNum]['civicrm_contact_sort_name'] = "";
              }
              $repeatFound = $rowNum;
            }
          }
          if (in_array($colName, $this->_noRepeats)) {
            $checkList[$colName][] = $colVal;
          }
        }
      }

      if (array_key_exists('civicrm_membership_membership_type_id', $row)) {
        if ($value = $row['civicrm_membership_membership_type_id']) {
          $rows[$rowNum]['civicrm_membership_membership_type_id'] = CRM_Member_PseudoConstant::membershipType($value, FALSE);
        }
        $entryFound = TRUE;
      }

      if (array_key_exists('civicrm_address_state_province_id', $row)) {
        if ($value = $row['civicrm_address_state_province_id']) {
          $rows[$rowNum]['civicrm_address_state_province_id'] = CRM_Core_PseudoConstant::stateProvince($value, FALSE);
        }
        $entryFound = TRUE;
      }

      if (array_key_exists('civicrm_address_country_id', $row)) {
        if ($value = $row['civicrm_address_country_id']) {
          $rows[$rowNum]['civicrm_address_country_id'] = CRM_Core_PseudoConstant::country($value, FALSE);
        }
        $entryFound = TRUE;
      }

      if (array_key_exists('civicrm_contact_sort_name', $row) &&
        $rows[$rowNum]['civicrm_contact_sort_name'] &&
        array_key_exists('civicrm_contact_id', $row)
      ) {
        $url = CRM_Utils_System::url("civicrm/contact/view",
          'reset=1&cid=' . $row['civicrm_contact_id'],
          $this->_absoluteUrl
        );
        $rows[$rowNum]['civicrm_contact_sort_name_link'] = $url;
        $rows[$rowNum]['civicrm_contact_sort_name_hover'] = ts("View Contact Summary for this Contact.");
        $entryFound = TRUE;
      }

      if ($value = CRM_Utils_Array::value('civicrm_contribution_financial_type_id', $row)) {
        $rows[$rowNum]['civicrm_contribution_financial_type_id'] = $contributionTypes[$value];
        $entryFound = TRUE;
      }
      if ($value = CRM_Utils_Array::value('civicrm_contribution_contribution_status_id', $row)) {
        $rows[$rowNum]['civicrm_contribution_contribution_status_id'] = $contributionStatus[$value];
        $entryFound = TRUE;
      }
      if ($value = CRM_Utils_Array::value('civicrm_contribution_payment_instrument_id', $row)) {
        $rows[$rowNum]['civicrm_contribution_payment_instrument_id'] = $paymentInstruments[$value];
        $entryFound = TRUE;
      }

      // Convert campaign_id to campaign title
      if (array_key_exists('civicrm_membership_campaign_id', $row)) {
        if ($value = $row['civicrm_membership_campaign_id']) {
          $rows[$rowNum]['civicrm_membership_campaign_id'] = $this->activeCampaigns[$value];
          $entryFound = TRUE;
        }
      }
      $entryFound = $this->alterDisplayAddressFields($row, $rows, $rowNum, 'member/detail', 'List all memberships(s) for this ') ? TRUE : $entryFound;
      $entryFound = $this->alterDisplayContactFields($row, $rows, $rowNum, 'member/detail', 'List all memberships(s) for this ') ? TRUE : $entryFound;

      if (!$entryFound) {
        break;
      }
    }
  }

}
