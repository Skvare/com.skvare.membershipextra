<?php

class CRM_Membershipextra_Form_Report_Member_MembershipDonation extends CRM_Report_Form {
  protected $_addressField = FALSE;

  protected $_emailField = FALSE;

  protected $_summary = NULL;
  protected $_allBatches = NULL;

  protected $_customGroupExtends = [
    'Contribution',
    'Membership',
  ];

  public function __construct() {
    $config = CRM_Core_Config::singleton();
    $campaignEnabled = in_array('CiviCampaign', $config->enableComponents);
    if ($campaignEnabled) {
      $getCampaigns = CRM_Campaign_BAO_Campaign::getPermissionedCampaigns(NULL, NULL, TRUE, FALSE, TRUE);
      $this->activeCampaigns = $getCampaigns['campaigns'];
      asort($this->activeCampaigns);
    }
    $this->_columns = [
        'civicrm_contact' => [
          'dao' => 'CRM_Contact_DAO_Contact',
          'fields' => [
            'sort_name' => [
              'title' => ts('Contact Name'),
              'required' => TRUE,
              //'no_repeat' => TRUE,
            ],
            'first_name' => [
              'title' => ts('First Name'),
              //'no_repeat' => TRUE,
            ],
            'last_name' => [
              'title' => ts('Last Name'),
              //'no_repeat' => TRUE,
            ],
            'contact_type' => [
              'title' => ts('Contact Type'),
              //'no_repeat' => TRUE,
            ],
            'contact_sub_type' => [
              'title' => ts('Contact Subtype'),
              //'no_repeat' => TRUE,
            ],
            'do_not_email' => [
              'title' => ts('Do Not Email'),
              //'no_repeat' => TRUE,
            ],
            'is_opt_out' => [
              'title' => ts('No Bulk Email(Is Opt Out)'),
              //'no_repeat' => TRUE,
            ],
            'id' => [
              'no_display' => TRUE,
              'required' => TRUE,
              'csv_display' => TRUE,
              'title' => ts('Contact ID'),
            ],
          ],
          'filters' => [
            'sort_name' => [
              'title' => ts('Contact Name'),
              'operator' => 'like',
            ],
            'id' => [
              'title' => ts('Contact ID'),
              'no_display' => TRUE,
            ],
          ],
          'grouping' => 'contact-fields',
        ],
        'civicrm_email' => [
          'dao' => 'CRM_Core_DAO_Email',
          'fields' => [
            'email' => [
              'title' => ts('Contact Email'),
              'default' => TRUE,
              //'no_repeat' => TRUE,
            ],
          ],
          'grouping' => 'contact-fields',
        ],
        'civicrm_phone' => [
          'dao' => 'CRM_Core_DAO_Phone',
          'fields' => [
            'phone' => [
              'title' => ts('Contact Phone'),
              'default' => TRUE,
              //'no_repeat' => TRUE,
            ],
          ],
          'grouping' => 'contact-fields',
        ],
        'civicrm_contribution' => [
          'dao' => 'CRM_Contribute_DAO_Contribution',
          'fields' => [
            'contribution_id' => [
              'name' => 'id',
              'no_display' => TRUE,
              'required' => TRUE,
              'csv_display' => TRUE,
              'title' => ts('Contribution ID'),
            ],
            'financial_type_id' => [
              'title' => ts('Financial Type'),
              'default' => TRUE,
            ],
            'contribution_recur_id' => [
              'title' => ts('Recurring Contribution Id'),
              'name' => 'contribution_recur_id',
              'required' => TRUE,
              'no_display' => TRUE,
              'csv_display' => TRUE,
            ],
            'contribution_status_id' => [
              'title' => ts('Contribution Status'),
            ],
            'payment_instrument_id' => [
              'title' => ts('Payment Type'),
            ],
            'contribution_source' => [
              'name' => 'source',
              'title' => ts('Contribution Source'),
            ],
            'currency' => [
              'required' => TRUE,
              'no_display' => TRUE,
            ],
            'trxn_id' => NULL,
            'receive_date' => ['default' => TRUE],
            'receipt_date' => NULL,
            'fee_amount' => NULL,
            'net_amount' => NULL,
            'total_amount' => [
              'title' => ts('Total Amount'),
              'required' => TRUE,
            ],
          ],
          'filters' => [
            'receive_date' => [
              'title' => ts('Receive Date'),
              'operatorType' => CRM_Report_Form::OP_DATE
            ],
            'financial_type_id' => [
              'title' => ts('Financial Type'),
              'operatorType' => CRM_Report_Form::OP_MULTISELECT,
              'options' => CRM_Contribute_PseudoConstant::financialType(),
            ],
            'currency' => [
              'title' => 'Currency',
              'operatorType' => CRM_Report_Form::OP_MULTISELECT,
              'options' => CRM_Core_OptionGroup::values('currencies_enabled'),
              'default' => NULL,
              'type' => CRM_Utils_Type::T_STRING,
            ],
            'payment_instrument_id' => [
              'title' => ts('Payment Type'),
              'operatorType' => CRM_Report_Form::OP_MULTISELECT,
              'options' => CRM_Contribute_PseudoConstant::paymentInstrument(),
            ],
            'contribution_status_id' => [
              'title' => ts('Contribution Status'),
              'operatorType' => CRM_Report_Form::OP_MULTISELECT,
              'options' => CRM_Contribute_PseudoConstant::contributionStatus(),
              'default' => [1],
            ],
            'total_amount' => ['title' => ts('Contribution Amount')],
          ],
          'grouping' => 'contri-fields',
        ],
        'civicrm_line_item_donation' => [
          'dao' => 'CRM_Price_DAO_LineItem',
          'fields' => [
            'line_total_donation' => [
              'title' => ts('Donation Income'),
              'required' => TRUE,
              'name' => 'line_total',
              'default' => TRUE,
            ],
          ],
          'filters' => [
            'line_total_donation' => [
              'type' => CRM_Utils_Type::T_MONEY,
              'name' => 'line_total',
              'title' => ts('Donation Amount'),
            ],
          ],
        ],
        'civicrm_line_item' => [
          'dao' => 'CRM_Price_DAO_LineItem',
          'fields' => [
            'line_total' => [
              'title' => ts('Membership Income'),
              'required' => TRUE,
              'default' => TRUE,
            ],
          ],
          'filters' => [
            'line_total' => [
              'type' => CRM_Utils_Type::T_MONEY,
              'name' => 'line_total',
              'title' => ts('Membership Amount'),
            ],
          ],
        ],
        'civicrm_membership' => [
          'dao' => 'CRM_Member_DAO_Membership',
          'fields' => [
            'membership_type_id' => [
              'title' => ts('Membership Type'),
              'required' => TRUE,
              //'no_repeat' => TRUE,
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
            'source' => ['title' => ts('Membership Source')],
          ],
          'filters' => [
            'join_date' => ['title' => ts('Member Since'),
              'operatorType' => CRM_Report_Form::OP_DATE
            ],
            'membership_start_date' => ['title' => ts('Membership Start Date'),
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
          'grouping' => 'member-fields',
        ],
        'civicrm_membership_status' => [
          'dao' => 'CRM_Member_DAO_MembershipStatus',
          'alias' => 'mem_status',
          'fields' => [
            'membership_status_name' => [
              'name' => 'name',
              'title' => ts('Membership Status'),
              'default' => TRUE,
            ],
          ],
          'filters' => [
            'sid' => [
              'name' => 'id',
              'title' => ts('Membership Status'),
              'type' => CRM_Utils_Type::T_INT,
              'operatorType' => CRM_Report_Form::OP_MULTISELECT,
              'options' => CRM_Member_PseudoConstant::membershipStatus(NULL, NULL, 'label'),
            ],
          ],
          'grouping' => 'member-fields',
        ],
      ] + $this->addAddressFields(FALSE);

    $this->_groupFilter = TRUE;
    $this->_tagFilter = TRUE;


    if ($campaignEnabled && !empty($this->activeCampaigns)) {
      $this->_columns['civicrm_contribution']['fields']['campaign_id'] = [
        'title' => ts('Campaign'),
        'default' => 'false',
      ];
      $this->_columns['civicrm_contribution']['filters']['campaign_id'] = [
        'title' => ts('Campaign'),
        'operatorType' => CRM_Report_Form::OP_MULTISELECT,
        'options' => $this->activeCampaigns,
      ];
      $this->_columns['civicrm_contribution']['order_bys']['campaign_id'] = ['title' => ts('Campaign')];
    }

    $this->_currencyColumn = 'civicrm_contribution_currency';
    parent::__construct();
  }

  public function preProcess() {
    parent::preProcess();
  }

  public function select() {
    $select = [];

    $this->_columnHeaders = [];
    foreach ($this->_columns as $tableName => $table) {
      if (array_key_exists('fields', $table)) {
        foreach ($table['fields'] as $fieldName => $field) {
          if (!empty($field['required']) ||
            !empty($this->_params['fields'][$fieldName])
          ) {
            if ($tableName == 'civicrm_address') {
              $this->_addressField = TRUE;
            }
            if ($tableName == 'civicrm_email') {
              $this->_emailField = TRUE;
            }

            $select[] = "{$field['dbAlias']} as {$tableName}_{$fieldName}";
            $this->_columnHeaders["{$tableName}_{$fieldName}"]['title'] = CRM_Utils_Array::value('title', $field);
            $this->_columnHeaders["{$tableName}_{$fieldName}"]['type'] = CRM_Utils_Array::value('type', $field);
          }
        }
      }
    }

    $this->_select = 'SELECT ' . implode(', ', $select) . ' ';
  }

  public function from() {
    $this->_from = "
              FROM civicrm_membership {$this->_aliases['civicrm_membership']}
              LEFT JOIN civicrm_line_item {$this->_aliases['civicrm_line_item']} ON ({$this->_aliases['civicrm_line_item']}.entity_id = {$this->_aliases['civicrm_membership']}.id AND {$this->_aliases['civicrm_line_item']}.entity_table = 'civicrm_membership' )
              LEFT JOIN civicrm_contribution {$this->_aliases['civicrm_contribution']}
                      ON ({$this->_aliases['civicrm_line_item']}.contribution_id = {$this->_aliases['civicrm_contribution']}.id)
              LEFT JOIN civicrm_line_item {$this->_aliases['civicrm_line_item_donation']} ON ({$this->_aliases['civicrm_line_item_donation']}.entity_id = {$this->_aliases['civicrm_contribution']}.id AND {$this->_aliases['civicrm_line_item_donation']}.entity_table = 'civicrm_contribution' )                      
              LEFT JOIN civicrm_membership_payment cmp ON (cmp.contribution_id = {$this->_aliases['civicrm_contribution']}.id AND cmp.membership_id = {$this->_aliases['civicrm_membership']}.id)
              INNER JOIN civicrm_contact {$this->_aliases['civicrm_contact']}
                      ON ({$this->_aliases['civicrm_membership']}.contact_id = {$this->_aliases['civicrm_contact']}.id)
              LEFT  JOIN civicrm_membership_status {$this->_aliases['civicrm_membership_status']}
                          ON {$this->_aliases['civicrm_membership_status']}.id = {$this->_aliases['civicrm_membership']}.status_id ";


    if (!empty($this->_params['fields']['phone'])) {
      $this->_from .= "
               LEFT JOIN  civicrm_phone {$this->_aliases['civicrm_phone']}
                      ON ({$this->_aliases['civicrm_contact']}.id = {$this->_aliases['civicrm_phone']}.contact_id AND
                         {$this->_aliases['civicrm_phone']}.is_primary = 1)";
    }

    if ($this->_addressField OR
      (!empty($this->_params['state_province_id_value']) OR
        !empty($this->_params['country_id_value']))
    ) {
      $this->_from .= "
            LEFT JOIN civicrm_address {$this->_aliases['civicrm_address']}
                   ON {$this->_aliases['civicrm_contact']}.id = {$this->_aliases['civicrm_address']}.contact_id AND
                      {$this->_aliases['civicrm_address']}.is_primary = 1\n";
    }

    if ($this->_emailField) {
      $this->_from .= "
            LEFT JOIN civicrm_email {$this->_aliases['civicrm_email']}
                   ON {$this->_aliases['civicrm_contact']}.id = {$this->_aliases['civicrm_email']}.contact_id AND
                      {$this->_aliases['civicrm_email']}.is_primary = 1\n";
    }
  }

  public function orderBy() {
    $this->_orderBy = " ORDER BY {$this->_aliases['civicrm_contact']}.sort_name, {$this->_aliases['civicrm_contact']}.id ";
  }

  public function postProcess() {
    // get the acl clauses built before we assemble the query
    $this->buildACLClause($this->_aliases['civicrm_contact']);
    parent::postProcess();
  }

  /**
   * @param $rows
   */
  public function alterDisplay(&$rows) {
    // custom code to alter rows
    $checkList = [];

    $entryFound = FALSE;
    $contributionTypes = CRM_Contribute_PseudoConstant::financialType();
    $contributionStatus = CRM_Contribute_PseudoConstant::contributionStatus();
    $paymentInstruments = CRM_Contribute_PseudoConstant::paymentInstrument();

    //altering the csv display adding additional fields
    if ($this->_outputMode == 'csv') {
      foreach ($this->_columns as $tableName => $table) {
        if (array_key_exists('fields', $table)) {
          foreach ($table['fields'] as $fieldName => $field) {
            if (!empty($field['csv_display']) && !empty($field['no_display'])) {
              $this->_columnHeaders["{$tableName}_{$fieldName}"]['title'] = CRM_Utils_Array::value('title', $field);
              $this->_columnHeaders["{$tableName}_{$fieldName}"]['type'] = CRM_Utils_Array::value('type', $field);
            }
          }
        }
      }
    }

    // allow repeat for first donation amount and date in csv
    $fAmt = '';
    $fDate = '';
    foreach ($rows as $rowNum => $row) {
      if (!empty($this->_noRepeats) && $this->_outputMode != 'csv') {
        $repeatFound = FALSE;

        $display_flag = NULL;
        if (array_key_exists('civicrm_contact_id', $row)) {
          if ($cid = $row['civicrm_contact_id']) {
            if ($rowNum == 0) {
              $prev_cid = $cid;
            }
            else {
              if ($prev_cid == $cid) {
                $display_flag = 1;
                $prev_cid = $cid;
              }
              else {
                $display_flag = 0;
                $prev_cid = $cid;
              }
            }

            if ($display_flag) {
              foreach ($row as $colName => $colVal) {
                if (in_array($colName, $this->_noRepeats)) {
                  unset($rows[$rowNum][$colName]);
                }
              }
            }
            $entryFound = TRUE;
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

      // convert donor sort name to link
      if (array_key_exists('civicrm_contact_sort_name', $row) &&
        !empty($rows[$rowNum]['civicrm_contact_sort_name']) &&
        array_key_exists('civicrm_contact_id', $row)
      ) {
        $url = CRM_Utils_System::url('civicrm/contact/view',
          'reset=1&cid=' . $row['civicrm_contact_id'],
          $this->_absoluteUrl
        );

        $rows[$rowNum]['civicrm_contact_sort_name_link'] = $url;
        $rows[$rowNum]['civicrm_contact_sort_name_hover'] = ts('View Contact Summary for this Contact.');
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
      if (($value = CRM_Utils_Array::value('civicrm_contribution_total_amount_sum', $row)) &&
        CRM_Core_Permission::check('access CiviContribute')
      ) {
        $url = CRM_Utils_System::url('civicrm/contact/view/contribution',
          'reset=1&id=' . $row['civicrm_contribution_contribution_id'] .
          '&cid=' . $row['civicrm_contact_id'] .
          '&action=view&context=contribution&selectedChild=contribute',
          $this->_absoluteUrl
        );
        $rows[$rowNum]['civicrm_contribution_total_amount_sum_link'] = $url;
        $rows[$rowNum]['civicrm_contribution_total_amount_sum_hover'] = ts('View Details of this Contribution.');
        $entryFound = TRUE;
      }

      // convert campaign_id to campaign title
      if (array_key_exists('civicrm_contribution_campaign_id', $row)) {
        if ($value = $row['civicrm_contribution_campaign_id']) {
          $rows[$rowNum]['civicrm_contribution_campaign_id'] = $this->activeCampaigns[$value];
          $entryFound = TRUE;
        }
      }

      $entryFound = $this->alterDisplayAddressFields($row, $rows, $rowNum, 'member/contributionDetail', 'List all contribution(s) for this ') ? TRUE : $entryFound;

      // skip looking further in rows, if first row itself doesn't
      // have the column we need
      if (!$entryFound) {
        break;
      }
      $lastKey = $rowNum;
    }
  }

}