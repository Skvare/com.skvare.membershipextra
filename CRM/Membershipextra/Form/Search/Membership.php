<?php

class CRM_Membershipextra_Form_Search_Membership extends CRM_Contact_Form_Search_Custom_Base implements CRM_Contact_Form_Search_Interface {

  protected $_formValues;

  protected $_tableName = NULL;

  protected $_where = ' (1) ';

  protected $_aclFrom = NULL;
  protected $_aclWhere = NULL;

  /**
   * @param $formValues
   */
  public function __construct(&$formValues) {
    $this->_formValues = $formValues;
    $this->_columns = [
      ts('Contact ID') => 'contact_id',
      ts('Contact Type') => 'contact_type',
      ts('Name') => 'sort_name',
      ts('Email') => 'email',
      ts('Phone') => 'phone',
      ts('Membership Type Name') => 'tname',
    ];

    // Assign form value to object variable...
    $this->_includeMemTypes = CRM_Utils_Array::value('includeMemTypes', $this->_formValues, []);
    $this->_excludeMemTypes = CRM_Utils_Array::value('excludeMemTypes', $this->_formValues, []);
    $this->_memStatus = CRM_Utils_Array::value('memStatus', $this->_formValues, []);
    $this->_excludeMemStatus = CRM_Utils_Array::value('excludeMemStatus', $this->_formValues, []);

    $this->_memSinceFrom = CRM_Utils_Array::value('member_join_date_low', $this->_formValues);
    $this->_memSinceTo = CRM_Utils_Array::value('member_join_date_high', $this->_formValues);

    $this->_memStartFrom = CRM_Utils_Array::value('member_start_date_low', $this->_formValues);
    $this->_memStartTo = CRM_Utils_Array::value('member_start_date_high', $this->_formValues);

    $this->_memEndFrom = CRM_Utils_Array::value('member_end_date_low', $this->_formValues);
    $this->_memEndTo = CRM_Utils_Array::value('member_end_date_high', $this->_formValues);

    list($this->_memSinceFrom, $this->_memSinceTo) = $this->getFromTo(CRM_Utils_Array::value('member_join_date_relative', $this->_formValues), $this->_memSinceFrom, $this->_memSinceTo);
    list($this->_memStartFrom, $this->_memStartTo) = $this->getFromTo(CRM_Utils_Array::value('member_start_date_relative', $this->_formValues), $this->_memStartFrom, $this->_memStartTo);
    list($this->_memEndFrom, $this->_memEndTo) = $this->getFromTo(CRM_Utils_Array::value('member_end_date_relative', $this->_formValues), $this->_memEndFrom, $this->_memEndTo);

    //define variables
    $this->_allSearch = FALSE;
    $this->_memTypes = FALSE;
    //selected or it is empty search
    if (empty($this->_includeMemTypes)) {
      $mTypes = CRM_Member_PseudoConstant::membershipType();
      $this->_includeMemTypes = array_keys($mTypes);
    }
    if (empty($this->_includeMemTypes) && empty($this->_excludeMemTypes)) {
      //empty search
      $this->_allSearch = TRUE;
    }
    $this->_memTypes = (!empty($this->_includeMemTypes) || !empty($this->_excludeMemTypes));
  }

  public function __destruct() {
    // mysql drops the tables when connection is terminated
    // cannot drop tables here, since the search might be used
    // in other parts after the object is destroyed
  }

  /**
   * @param CRM_Core_Form $form
   */
  public function buildForm(&$form) {
    $this->setTitle(ts('Membership Type Include / Exclude Search'));

    $memTypes = CRM_Member_PseudoConstant::membershipType();
    $memStatus = CRM_Member_PseudoConstant::membershipStatus(NULL, NULL, 'label');
    if (count($memTypes) == 0) {
      CRM_Core_Session::setStatus(ts("At least one Membership type must be present for search."), ts('Missing Membership Type'));
      $url = CRM_Utils_System::url('civicrm/contact/search/custom/list', 'reset=1');
      CRM_Utils_System::redirect($url);
    }

    $select2style = [
      'multiple' => TRUE,
      'style' => 'width: 100%; max-width: 60em;',
      'class' => 'crm-select2',
      'placeholder' => ts('- select -'),
    ];

    $form->add('select', 'includeMemTypes', ts('Include Membership Type(s)'), $memTypes, FALSE, $select2style);
    $form->add('select', 'excludeMemTypes', ts('Exclude Membership Type(s)'), $memTypes, FALSE, $select2style);
    $form->add('select', 'memStatus', ts('Include Membership Status'), $memStatus, FALSE, $select2style);
    $form->add('select', 'excludeMemStatus', ts('Exclude Membership Status'), $memStatus, FALSE, $select2style);

    $form->addDatePickerRange('member_join_date', 'Membership Since', FALSE, FALSE, 'From', 'To', '', '_low', '_high');
    $form->addDatePickerRange('member_start_date', 'Membership Start', FALSE, FALSE, 'From', 'To', '', '_low', '_high');
    $form->addDatePickerRange('member_end_date', 'Membership End', FALSE, FALSE, 'From', 'To', '', '_low', '_high');


    /**
     * if you are using the standard template, this array tells the template what elements
     * are part of the search criteria
     */
    $form->assign('elements', ['includeMemTypes', 'excludeMemTypes', 'memStatus', 'excludeMemStatus', 'member_join_date', 'member_start_date', 'member_end_date']);
  }

  /**
   * Set search form field defaults here.
   * @return array
   */
  public function setDefaultValues() {
    if (!empty($this->_formValues)) {
      $defaults['includeMemTypes'] = CRM_Utils_Array::value('includeMemTypes', $this->_formValues);
      $defaults['excludeMemTypes'] = CRM_Utils_Array::value('excludeMemTypes', $this->_formValues);
      $defaults['memStatus'] = CRM_Utils_Array::value('memStatus', $this->_formValues);
      $defaults['excludeMemStatus'] = CRM_Utils_Array::value('excludeMemStatus', $this->_formValues);

      $defaults['member_join_date_low'] = CRM_Utils_Array::value('member_join_date_low', $this->_formValues);
      $defaults['member_join_date_high'] = CRM_Utils_Array::value('member_join_date_high', $this->_formValues);
      $defaults['member_join_date_relative'] = CRM_Utils_Array::value('member_join_date_relative', $this->_formValues);

      $defaults['member_start_date_low'] = CRM_Utils_Array::value('member_start_date_low', $this->_formValues);
      $defaults['member_start_date_high'] = CRM_Utils_Array::value('member_start_date_high', $this->_formValues);
      $defaults['member_start_date_relative'] = CRM_Utils_Array::value('member_start_date_relative', $this->_formValues);

      $defaults['member_end_date_low'] = CRM_Utils_Array::value('member_end_date_low', $this->_formValues);
      $defaults['member_end_date_high'] = CRM_Utils_Array::value('member_end_date_high', $this->_formValues);
      $defaults['member_end_date_relative'] = CRM_Utils_Array::value('member_end_date_relative', $this->_formValues);
    }

    return $defaults;
  }

  /**
   * @param int $offset
   * @param int $rowcount
   * @param NULL $sort
   * @param bool $includeContactIDs
   * @param bool $justIDs
   *
   * @return string
   */
  public function all($offset = 0, $rowcount = 0, $sort = NULL, $includeContactIDs = FALSE, $justIDs = FALSE) {

    if ($justIDs) {
      $selectClause = "contact_a.id as contact_id";
    }
    else {
      $selectClause = "contact_a.id as contact_id,
                       contact_a.contact_type as contact_type,
                       contact_a.sort_name    as sort_name,
                       civicrm_email.email    as email,
                       civicrm_phone.phone    as phone ";

      //distinguish column according to user selection
      if ($this->_includeMemTypes) {
        $selectClause .= ", GROUP_CONCAT(DISTINCT mtype_names  ORDER BY mtype_names ASC ) as tname";
      }
      else {
        unset($this->_columns['Membership Type ']);
      }
    }

    $from = $this->from();

    $where = $this->where($includeContactIDs);

    if (!$justIDs && !$this->_allSearch) {
      $groupBy = " GROUP BY contact_a.id";
    }
    else {
      // CRM-10850
      $groupBy = NULL;
    }

    $sql = "SELECT $selectClause $from WHERE  $where $groupBy";

    // Define ORDER BY for query in $sort, with default value
    if (!$justIDs) {
      if (!empty($sort)) {
        if (is_string($sort)) {
          $sort = CRM_Utils_Type::escape($sort, 'String');
          $sql .= " ORDER BY $sort ";
        }
        else {
          $sql .= " ORDER BY " . trim($sort->orderBy());
        }
      }
      else {
        $sql .= " ORDER BY contact_id ASC";
      }
    }
    else {
      $sql .= " ORDER BY contact_a.id ASC";
    }

    if ($offset >= 0 && $rowcount > 0) {
      $sql .= " LIMIT $offset, $rowcount ";
    }

    return $sql;
  }

  /**
   * @return string
   * @throws Exception
   */
  public function from() {

    $iMemTypes = $xMemTypes = 0;

    //define table name
    $randomNum = md5(uniqid());
    $this->_tableName = "civicrm_temp_custom_{$randomNum}";
    $memStatus = '';
    if (!empty($this->_memStatus)) {
      $memStatus = implode(',', $this->_memStatus);
    }
    $excludeMemStatus = '';
    if (!empty($this->_excludeMemStatus)) {
      $excludeMemStatus = implode(',', $this->_excludeMemStatus);
    }

    //block for Membership type search
    if ($this->_memTypes || $this->_allSearch) {
      //find all membership Type
      $mType = new CRM_Member_DAO_MembershipType();
      $mType->is_active = 1;
      $mType->find();
      while ($mType->fetch()) {
        $allMemTypes[] = $mType->id;
      }
      $includeMemTypes = implode(',', $allMemTypes);

      if (!empty($this->_includeMemTypes)) {
        $iMemTypes = implode(',', $this->_includeMemTypes);
      }
      else {
        $iMemTypes = NULL;
        $iMemTypes = $includeMemTypes;
      }
      if (is_array($this->_excludeMemTypes)) {
        $xMemTypes = implode(',', $this->_excludeMemTypes);
      }
      else {
        $xMemTypes = 0;
      }

      $sql = "CREATE TEMPORARY TABLE Xt_{$this->_tableName} ( contact_id int primary key) ENGINE=MyISAM";
      CRM_Core_DAO::executeQuery($sql);

      //used only when exclude membership type is selected
      if ($xMemTypes != 0) {
        $excludeMemTypes = "INSERT INTO  Xt_{$this->_tableName} ( contact_id )
                SELECT  DISTINCT civicrm_membership.contact_id
                FROM civicrm_membership, civicrm_contact
                WHERE
                   civicrm_contact.id = civicrm_membership.contact_id AND
                   civicrm_membership.membership_type_id IN( {$xMemTypes})";
        if (!empty($excludeMemStatus)) {
          $excludeMemTypes .= " AND civicrm_membership.status_id IN ($excludeMemStatus) ";
        }

        if (!empty($this->_memSinceFrom)) {
          $excludeMemTypes .= " AND civicrm_membership.join_date >= '{$this->_memSinceFrom}' ";
        }
        if (!empty($this->_memSinceTo)) {
          $excludeMemTypes .= " AND civicrm_membership.join_date <= '{$this->_memSinceTo}' ";
        }

        if (!empty($this->_memStartFrom)) {
          $excludeMemTypes .= " AND civicrm_membership.start_date >= '{$this->_memStartFrom}' ";
        }
        if (!empty($this->_memStartTo)) {
          $excludeMemTypes .= " AND civicrm_membership.start_date <= '{$this->_memStartTo}' ";
        }

        if (!empty($this->_memEndFrom)) {
          $excludeMemTypes .= " AND civicrm_membership.end_date >= '{$this->_memEndFrom}' ";
        }
        if (!empty($this->_memEndTo)) {
          $excludeMemTypes .= " AND civicrm_membership.end_date <= '{$this->_memEndTo}' ";
        }
        CRM_Core_DAO::executeQuery($excludeMemTypes);
      }

      $sql = "CREATE TEMPORARY TABLE It_{$this->_tableName} ( 
             id int PRIMARY KEY AUTO_INCREMENT, contact_id int, mtype_names varchar(64)) ENGINE=MyISAM";

      CRM_Core_DAO::executeQuery($sql);

      if ($iMemTypes) {
        $includeMemTypes = "INSERT INTO It_{$this->_tableName} (contact_id, mtype_names)
               SELECT civicrm_contact.id as contact_id, civicrm_membership_type.name as mtype_name
               FROM civicrm_contact
                  INNER JOIN civicrm_membership
                    ON ( civicrm_membership.contact_id = civicrm_contact.id )
                  LEFT JOIN civicrm_membership_type
                    ON civicrm_membership.membership_type_id = civicrm_membership_type.id";
      }
      else {
        $includeMemTypes = "INSERT INTO It_{$this->_tableName} (contact_id, mtype_names)
               SELECT civicrm_contact.id as contact_id, '' FROM civicrm_contact";
      }

      //used only when exclude membership type is selected
      if ($xMemTypes != 0) {
        $includeMemTypes .= " LEFT JOIN Xt_{$this->_tableName}
                                ON civicrm_contact.id = Xt_{$this->_tableName}.contact_id";
      }
      if ($iMemTypes) {
        $includeMemTypes .= " WHERE civicrm_membership.membership_type_id IN($iMemTypes)";
        if (!empty($memStatus)) {
          $includeMemTypes .= " AND civicrm_membership.status_id IN ($memStatus) ";
        }
        if (!empty($this->_memSinceFrom)) {
          $includeMemTypes .= " AND civicrm_membership.join_date >= '{$this->_memSinceFrom}' ";
        }
        if (!empty($this->_memSinceTo)) {
          $includeMemTypes .= " AND civicrm_membership.join_date <= '{$this->_memSinceTo}' ";
        }
        if (!empty($this->_memStartFrom)) {
          $includeMemTypes .= " AND civicrm_membership.start_date >= '{$this->_memStartFrom}' ";
        }
        if (!empty($this->_memStartTo)) {
          $includeMemTypes .= " AND civicrm_membership.start_date <= '{$this->_memStartTo}' ";
        }

        if (!empty($this->_memEndFrom)) {
          $includeMemTypes .= " AND civicrm_membership.end_date >= '{$this->_memEndFrom}' ";
        }
        if (!empty($this->_memEndTo)) {
          $includeMemTypes .= " AND civicrm_membership.end_date <= '{$this->_memEndTo}' ";
        }
      }
      else {
        $includeMemTypes .= " WHERE (1) ";
      }

      //used only when exclude membership type is selected
      if ($xMemTypes != 0) {
        $includeMemTypes .= " AND  Xt_{$this->_tableName}.contact_id IS NULL";
      }
      CRM_Core_DAO::executeQuery($includeMemTypes);
    }

    $from = " FROM civicrm_contact contact_a";

    /*
     * check the situation and set booleans
     * Do not delete these variable, used as $$ reference variable in following block
     */
    $It = ($iMemTypes != 0);
    $Xt = ($xMemTypes != 0);

    /*
     * Set from statement depending on array sel
     */
    $whereItems = [];
    foreach (['It'] as $inc) {
      if ($$inc) {
        $from .= " LEFT JOIN {$inc}_{$this->_tableName} temptable$inc ON (contact_a.id = temptable$inc.contact_id)";
      }

      if ($$inc) {
        $whereItems[] = "temptable$inc.contact_id IS NOT NULL";
      }
    }
    $this->_where = $whereItems ? "(" . implode(' OR ', $whereItems) . ')' : '(1)';
    foreach (['Xt'] as $exc) {
      if ($$exc) {
        $from .= " LEFT JOIN {$exc}_{$this->_tableName} temptable$exc ON (contact_a.id = temptable$exc.contact_id)";
        $this->_where .= " AND temptable$exc.contact_id IS NULL";
      }
    }
    $from .= " LEFT JOIN civicrm_phone ON ( contact_a.id = civicrm_phone.contact_id AND ( civicrm_phone.is_primary = 1 ) ) ";
    $from .= " LEFT JOIN civicrm_email ON ( contact_a.id = civicrm_email.contact_id AND ( civicrm_email.is_primary = 1 OR civicrm_email.is_bulkmail = 1 ) ) {$this->_aclFrom}";


    if ($this->_aclWhere) {
      $this->_where .= " AND {$this->_aclWhere} ";
    }

    // also exclude all contacts that are deleted
    // CRM-11627
    $this->_where .= " AND (contact_a.is_deleted != 1) ";

    return $from;
  }

  /**
   * @param bool $includeContactIDs
   *
   * @return string
   */
  public function where($includeContactIDs = FALSE) {
    if ($includeContactIDs) {
      $contactIDs = [];

      foreach ($this->_formValues as $id => $value) {
        if ($value && substr($id, 0, CRM_Core_Form::CB_PREFIX_LEN) == CRM_Core_Form::CB_PREFIX) {
          $contactIDs[] = substr($id, CRM_Core_Form::CB_PREFIX_LEN);
        }
      }

      if (!empty($contactIDs)) {
        $contactIDs = implode(', ', $contactIDs);
        $clauses[] = "contact_a.id IN ( $contactIDs )";
      }
      $where = "{$this->_where} AND " . implode(' AND ', $clauses);
    }
    else {
      $where = $this->_where;
    }

    return $where;
  }

  /*
   * Functions below generally don't need to be modified
   */

  /**
   * @inheritDoc
   */
  public function count() {
    $sql = $this->all();

    $dao = CRM_Core_DAO::executeQuery($sql);

    return $dao->N;
  }

  /**
   * @param int $offset
   * @param int $rowcount
   * @param NULL $sort
   * @param bool $returnSQL
   *
   * @return string
   */
  public function contactIDs($offset = 0, $rowcount = 0, $sort = NULL, $returnSQL = FALSE) {
    return $this->all($offset, $rowcount, $sort, FALSE, TRUE);
  }

  /**
   * @return array
   */
  public function &columns() {
    return $this->_columns;
  }

  /**
   * @return NULL
   */
  public function summary() {
    return NULL;
  }

  /**
   * @return string
   */
  public function templateFile() {
    return 'CRM/Contact/Form/Search/Custom/Membership.tpl';
  }

  /**
   * @param $title
   */
  public function setTitle($title) {
    $title = $title ?? 'Search';
    CRM_Utils_System::setTitle($title);
  }

  /**
   * @param string $tableAlias
   */
  public function buildACLClause($tableAlias = 'contact') {
    list($this->_aclFrom, $this->_aclWhere) = CRM_Contact_BAO_Contact_Permission::cacheClause($tableAlias);
  }

  public function getFromTo($relative, $from, $to, $fromTime = NULL, $toTime = NULL) {
    return CRM_Utils_Date::getFromTo($relative, $from, $to);
  }

}
