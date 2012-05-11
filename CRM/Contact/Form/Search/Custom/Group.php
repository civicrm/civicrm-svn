<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.0                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2011                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2011
 * $Id$
 *
 */
class CRM_Contact_Form_Search_Custom_Group extends CRM_Contact_Form_Search_Custom_Base implements CRM_Contact_Form_Search_Interface {

  protected $_formValues;

  protected $_tableName = NULL;

  protected $_where = ' (1) ';

  protected $_aclFrom = NULL;
  protected $_aclWhere = NULL; function __construct(&$formValues) {
    $this->_formValues = $formValues;
    $this->_columns = array(
      ts('Contact Id') => 'contact_id',
      ts('Contact Type') => 'contact_type',
      ts('Name') => 'sort_name',
      ts('Group Name') => 'gname',
      ts('Tag Name') => 'tname',
    );

    $this->_includeGroups = CRM_Utils_Array::value('includeGroups', $this->_formValues, array());
    $this->_excludeGroups = CRM_Utils_Array::value('excludeGroups', $this->_formValues, array());
    $this->_includeTags = CRM_Utils_Array::value('includeTags', $this->_formValues, array());
    $this->_excludeTags = CRM_Utils_Array::value('excludeTags', $this->_formValues, array());
    $this->_activity_include = json_decode(CRM_Utils_Array::value('activity_include', $this->_formValues, ''));
    $this->_activity_exclude = json_decode(CRM_Utils_Array::value('activity_exclude', $this->_formValues, ''));

    //define variables
    //NEEDED?
    $this->_allSearch = FALSE;
    $this->_groups    = FALSE;
    $this->_tags      = FALSE;
    $this->_andOr     = CRM_Utils_Array::value('andOr', $this->_formValues);


    //make easy to check conditions for groups and tags are
    //selected or it is empty search
    if (empty($this->_includeGroups) && empty($this->_excludeGroups) &&
      empty($this->_includeTags) && empty($this->_excludeTags) && empty($this->_activity_include) && empty($this->_activity_exclude)
    ) {
      //empty search
      $this->_allSearch = TRUE;
    }

    $this->_groups = (!empty($this->_includeGroups) || !empty($this->_excludeGroups));

    $this->_tags = (!empty($this->_includeTags) || !empty($this->_excludeTags));

    $this->_activities = (!empty($this->_activity_include) || !empty($this->_activity_exclude));
  }

  function __destruct() {
    // mysql drops the tables when connectiomn is terminated
    // cannot drop tables here, since the search might be used
    // in other parts after the object is destroyed
  }

  function buildForm(&$form) {

    $this->setTitle(ts('Include / Exclude Search'));

    $groups = CRM_Core_PseudoConstant::group();

    $tags = CRM_Core_PseudoConstant::tag();
    if (count($groups) == 0 || count($tags) == 0) {
      CRM_Core_Session::setStatus(ts("Atleast one Group and Tag must be present, for Custom Group / Tag search."));
      $url = CRM_Utils_System::url('civicrm/contact/search/custom/list', 'reset=1');
      CRM_Utils_System::redirect($url);
    }

    $inG = &$form->addElement('advmultiselect', 'includeGroups',
      ts('Include Group(s)') . ' ', $groups,
      array(
        'size' => 5,
        'style' => 'width:240px',
        'class' => 'advmultiselect',
      )
    );

    $outG = &$form->addElement('advmultiselect', 'excludeGroups',
      ts('Exclude Group(s)') . ' ', $groups,
      array(
        'size' => 5,
        'style' => 'width:240px',
        'class' => 'advmultiselect',
      )
    );

    $andOr = array('1' => ts('Require all inclusion criteria'), '0' => ts('Select contacts with any of the criteria for inclusion'));
    $form->addRadio('andOr', ts('And/or'), $andOr, TRUE, NULL, TRUE);

    $int = &$form->addElement('advmultiselect', 'includeTags',
      ts('Include Tag(s)') . ' ', $tags,
      array(
        'size' => 5,
        'style' => 'width:240px',
        'class' => 'advmultiselect',
      )
    );

    $outt = &$form->addElement('advmultiselect', 'excludeTags',
      ts('Exclude Tag(s)') . ' ', $tags,
      array(
        'size' => 5,
        'style' => 'width:240px',
        'class' => 'advmultiselect',
      )
    );

    //add/remove buttons for groups
    $inG->setButtonAttributes('add', array('value' => ts('Add >>')));;
    $outG->setButtonAttributes('add', array('value' => ts('Add >>')));;
    $inG->setButtonAttributes('remove', array('value' => ts('<< Remove')));;
    $outG->setButtonAttributes('remove', array('value' => ts('<< Remove')));;

    //add/remove buttons for tags
    $int->setButtonAttributes('add', array('value' => ts('Add >>')));;
    $outt->setButtonAttributes('add', array('value' => ts('Add >>')));;
    $int->setButtonAttributes('remove', array('value' => ts('<< Remove')));;
    $outt->setButtonAttributes('remove', array('value' => ts('<< Remove')));;

    // Text box for Activity Subject
    $form->add('text',
      'activity_subject',
      ts('Activity Subject')
    );

    // Select box for Activity Type
    $activityType = array('' => ts(' - select activity - ')) + CRM_Core_PseudoConstant::activityType();

    $form->add('select', 'activity_type_id', ts('Activity Type'),
      $activityType,
      FALSE
    );

    // textbox for Activity Status
    $activityStatus = array('' => ts(' - select status - ')) + CRM_Core_PseudoConstant::activityStatus();

    $form->add('select', 'activity_status_id', ts('Activity Status'),
      $activityStatus,
      FALSE
    );

    // Activity Date range
    $form->addDate('start_date', ts('Activity date from'), FALSE, array('formatType' => 'custom'));
    $form->addDate('end_date', ts('Activity date through'), FALSE, array('formatType' => 'custom'));
    $form->add('button', 'include_activity_targets', ts('Include activity targets >>'), 'include_activity_targets');
    $form->add('text', 'activity_include', ts('Include activities'), array('style' => 'display: none'));
    $form->add('button', 'exclude_activity_targets', ts('Exclude activity targets >>'), 'exclude_activity_targets');
    $form->add('text', 'activity_exclude', ts('Exclude activities'), array('style' => 'display: none'));

    /**
     * if you are using the standard template, this array tells the template what elements
     * are part of the search criteria
     */
    $form->assign('elements', array('includeGroups', 'excludeGroups', 'andOr', 'includeTags', 'excludeTags', 'activity_subject', 'activity_type_id', 'activity_status_id', 'start_date', 'end_date', 'include_activity_targets', 'exclude_activity_targets', 'activity_include', 'activity_exclude'));
  }

  function all($offset = 0, $rowcount = 0, $sort = NULL,
    $includeContactIDs = FALSE, $justIDs = FALSE
  ) {
    if ($justIDs) {
      $selectClause = "DISTINCT(contact_a.id)  as contact_id";
    }
    else {
      $selectClause = "DISTINCT(contact_a.id)  as contact_id,
                         contact_a.contact_type as contact_type,
                         contact_a.sort_name    as sort_name";

      //distinguish column according to user selection
      if (($this->_includeGroups && !$this->_includeTags)) {
        unset($this->_columns['Tag Name']);
        $selectClause .= ", GROUP_CONCAT(DISTINCT group_names ORDER BY group_names ASC ) as gname";
      }
      elseif ($this->_includeTags && (!$this->_includeGroups)) {
        unset($this->_columns['Group Name']);
        $selectClause .= ", GROUP_CONCAT(DISTINCT tag_names  ORDER BY tag_names ASC ) as tname";
      }
      elseif (!empty($this->_includeTags) && !empty($this->_includeGroups)) {
        $selectClause .= ", GROUP_CONCAT(DISTINCT group_names ORDER BY group_names ASC ) as gname , GROUP_CONCAT(DISTINCT tag_names ORDER BY tag_names ASC ) as tname";
      }
      else {
        unset($this->_columns['Tag Name']);
        unset($this->_columns['Group Name']);
      }
    }

    $from = $this->from();

    $where = $this->where($includeContactIDs);

    $sql = " SELECT $selectClause $from WHERE  $where ";
    if (!$justIDs && !$this->_allSearch) {
      $sql .= " GROUP BY contact_id ";
    }

    // Define ORDER BY for query in $sort, with default value
    if (!$justIDs) {
      if (!empty($sort)) {
        if (is_string($sort)) {
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

    if ($offset >= 0 && $rowcount > 0) {
      $sql .= " LIMIT $offset, $rowcount ";
    }

    return $sql;
  }

  function from() {

    $iGroups = $xGroups = $iTags = $xTags = 0;

    //define table name
    $randomNum = md5(uniqid());
    $this->_tableName = "civicrm_temp_custom_{$randomNum}";

    //block for Group search
    $smartGroup = array();
    if ($this->_groups || $this->_allSearch) {
      $group = new CRM_Contact_DAO_Group();
      $group->is_active = 1;
      $group->find();
      while ($group->fetch()) {
        $allGroups[] = $group->id;
        if ($group->saved_search_id) {
          $smartGroup[$group->saved_search_id] = $group->id;
        }
      }
      $includedGroups = implode(',', $allGroups);

      if (!empty($this->_includeGroups)) {
        $iGroups = implode(',', $this->_includeGroups);
      }
      else {
        //if no group selected search for all groups
        $iGroups = NULL;
      }
      if (is_array($this->_excludeGroups)) {
        $xGroups = implode(',', $this->_excludeGroups);
      }
      else {
        $xGroups = 0;
      }

      $sql = "CREATE TEMPORARY TABLE Xg_{$this->_tableName} ( contact_id int primary key) ENGINE=HEAP";
      CRM_Core_DAO::executeQuery($sql);

      //used only when exclude group is selected
      if ($xGroups != 0) {
        $excludeGroup = "INSERT INTO  Xg_{$this->_tableName} ( contact_id )
                  SELECT  DISTINCT civicrm_group_contact.contact_id
                  FROM civicrm_group_contact, civicrm_contact                    
                  WHERE 
                     civicrm_contact.id = civicrm_group_contact.contact_id AND 
                     civicrm_group_contact.status = 'Added' AND
                     civicrm_group_contact.group_id IN( {$xGroups})";

        CRM_Core_DAO::executeQuery($excludeGroup);

        //search for smart group contacts
        foreach ($this->_excludeGroups as $keys => $values) {
          if (in_array($values, $smartGroup)) {
            $ssId = CRM_Utils_Array::key($values, $smartGroup);

            $smartSql = CRM_Contact_BAO_SavedSearch::contactIDsSQL($ssId);

            $smartSql = $smartSql . " AND contact_a.id NOT IN ( 
                              SELECT contact_id FROM civicrm_group_contact 
                              WHERE civicrm_group_contact.group_id = {$values} AND civicrm_group_contact.status = 'Removed')";

            $smartGroupQuery = " INSERT IGNORE INTO Xg_{$this->_tableName}(contact_id) $smartSql";

            CRM_Core_DAO::executeQuery($smartGroupQuery);
          }
        }
      }

      $sql = "CREATE TEMPORARY TABLE Ig_{$this->_tableName} ( id int PRIMARY KEY AUTO_INCREMENT,
                                                                   contact_id int,
                                                                   group_names varchar(64)) ENGINE=HEAP";

      CRM_Core_DAO::executeQuery($sql);

      if ($iGroups) {
        $includeGroup = "INSERT INTO Ig_{$this->_tableName} (contact_id, group_names)
                 SELECT              civicrm_contact.id as contact_id, civicrm_group.title as group_name
                 FROM                civicrm_contact
                    INNER JOIN       civicrm_group_contact
                            ON       civicrm_group_contact.contact_id = civicrm_contact.id
                    LEFT JOIN        civicrm_group
                            ON       civicrm_group_contact.group_id = civicrm_group.id";
      }
      else {
        $includeGroup = "INSERT INTO Ig_{$this->_tableName} (contact_id, group_names)
                 SELECT              civicrm_contact.id as contact_id, ''
                 FROM                civicrm_contact";
      }


      //used only when exclude group is selected
      if ($xGroups != 0) {
        $includeGroup .= " LEFT JOIN        Xg_{$this->_tableName}
                                          ON       civicrm_contact.id = Xg_{$this->_tableName}.contact_id";
      }

      if ($iGroups) {
        $includeGroup .= " WHERE           
                                     civicrm_group_contact.status = 'Added'  AND
                                     civicrm_group_contact.group_id IN($iGroups)";
      }
      else {
        $includeGroup .= " WHERE ( 1 ) ";
      }

      //used only when exclude group is selected
      if ($xGroups != 0) {
        $includeGroup .= " AND  Xg_{$this->_tableName}.contact_id IS null";
      }

      CRM_Core_DAO::executeQuery($includeGroup);

      //search for smart group contacts

      foreach ($this->_includeGroups as $keys => $values) {
        if (in_array($values, $smartGroup)) {

          $ssId = CRM_Utils_Array::key($values, $smartGroup);

          $smartSql = CRM_Contact_BAO_SavedSearch::contactIDsSQL($ssId);

          $smartSql .= " AND contact_a.id NOT IN ( 
                              SELECT contact_id FROM civicrm_group_contact
                              WHERE civicrm_group_contact.group_id = {$values} AND civicrm_group_contact.status = 'Removed')";

          //used only when exclude group is selected
          if ($xGroups != 0) {
            $smartSql .= " AND contact_a.id NOT IN (SELECT contact_id FROM  Xg_{$this->_tableName})";
          }

          $smartGroupQuery = " INSERT IGNORE INTO Ig_{$this->_tableName}(contact_id) 
                                     $smartSql";

          CRM_Core_DAO::executeQuery($smartGroupQuery);
          $insertGroupNameQuery = "UPDATE IGNORE Ig_{$this->_tableName}
                                         SET group_names = (SELECT title FROM civicrm_group
                                                            WHERE civicrm_group.id = $values)
                                         WHERE Ig_{$this->_tableName}.contact_id IS NOT NULL 
                                         AND Ig_{$this->_tableName}.group_names IS NULL";
          CRM_Core_DAO::executeQuery($insertGroupNameQuery);
        }
      }
    }
    //group contact search end here;

    //block for Tags search
    if ($this->_tags || $this->_allSearch) {
      //find all tags
      $tag = new CRM_Core_DAO_Tag();
      $tag->is_active = 1;
      $tag->find();
      while ($tag->fetch()) {
        $allTags[] = $tag->id;
      }
      $includedTags = implode(',', $allTags);

      if (!empty($this->_includeTags)) {
        $iTags = implode(',', $this->_includeTags);
      }
      else {
        //if no group selected search for all groups
        $iTags = NULL;
      }
      if (is_array($this->_excludeTags)) {
        $xTags = implode(',', $this->_excludeTags);
      }
      else {
        $xTags = 0;
      }

      $sql = "CREATE TEMPORARY TABLE Xt_{$this->_tableName} ( contact_id int primary key) ENGINE=HEAP";
      CRM_Core_DAO::executeQuery($sql);

      //used only when exclude tag is selected
      if ($xTags != 0) {
        $excludeTag = "INSERT INTO  Xt_{$this->_tableName} ( contact_id )
                  SELECT  DISTINCT civicrm_entity_tag.entity_id
                  FROM civicrm_entity_tag, civicrm_contact                    
                  WHERE 
                     civicrm_entity_tag.entity_table = 'civicrm_contact' AND
                     civicrm_contact.id = civicrm_entity_tag.entity_id AND 
                     civicrm_entity_tag.tag_id IN( {$xTags})";

        CRM_Core_DAO::executeQuery($excludeTag);
      }

      $sql = "CREATE TEMPORARY TABLE It_{$this->_tableName} ( id int PRIMARY KEY AUTO_INCREMENT,
                                                               contact_id int,
                                                               tag_names varchar(64)) ENGINE=HEAP";

      CRM_Core_DAO::executeQuery($sql);

      if ($iTags) {
        $includeTag = "INSERT INTO It_{$this->_tableName} (contact_id, tag_names)
                 SELECT              civicrm_contact.id as contact_id, civicrm_tag.name as tag_name
                 FROM                civicrm_contact
                    INNER JOIN       civicrm_entity_tag
                            ON       ( civicrm_entity_tag.entity_table = 'civicrm_contact' AND
                                       civicrm_entity_tag.entity_id = civicrm_contact.id )
                    LEFT JOIN        civicrm_tag
                            ON       civicrm_entity_tag.tag_id = civicrm_tag.id";
      }
      else {
        $includeTag = "INSERT INTO It_{$this->_tableName} (contact_id, tag_names)
                 SELECT              civicrm_contact.id as contact_id, ''
                 FROM                civicrm_contact";
      }

      //used only when exclude tag is selected
      if ($xTags != 0) {
        $includeTag .= " LEFT JOIN        Xt_{$this->_tableName}
                                       ON       civicrm_contact.id = Xt_{$this->_tableName}.contact_id";
      }
      if ($iTags) {
        $includeTag .= " WHERE   civicrm_entity_tag.tag_id IN($iTags)";
      }
      else {
        $includeTag .= " WHERE ( 1 ) ";
      }

      //used only when exclude tag is selected
      if ($xTags != 0) {
        $includeTag .= " AND  Xt_{$this->_tableName}.contact_id IS null";
      }

      CRM_Core_DAO::executeQuery($includeTag);
    }

    //block for Activities search
    if ($this->_activity_include || $this->_activity_exclude) {


      /*            if ( is_array( $this->_activity_exclude ) ) {
                $xActs = $this->_activity_exclude;
            } else {
                $xActs = 0;
            }
            
            if ( is_array( $this->_activity_include ) ) {
                $iActs = $this->_activity_include;
            } else {
                $iActs = null;
            } 
print $xActs . ' / ' . $iActs . ' / '; */

      //print 'activities '; print_r($this->_activity_include); print_r($this->_activity_exclude); die();

      $sql = "CREATE TEMPORARY TABLE Xa_{$this->_tableName} ( contact_id int primary key) ENGINE=HEAP";
      CRM_Core_DAO::executeQuery($sql);

      //used only when exclude tag is selected
      if ($this->_activity_exclude) {
        $xactwhere = array();
        foreach ($this->_activity_exclude as $xactid => $xactitem) {
          foreach ($xactitem as $crit) {
            if (substr($crit, 0, 19) == 'activity_type_id = ') {
              $xactwhere[$xactid][] = 'activity_type_id = ' . intval(substr($crit, 19));
            }
            elseif (substr($crit, 0, 21) == 'activity_status_id = ') {
              $xactwhere[$xactid][] = 'status_id = ' . intval(substr($crit, 21));
            }
            elseif (in_array(substr($crit, 0, 22), array(
              'activity_date_time >= ', 'activity_date_time <= '))) {
              $xactwhere[$xactid][] = substr($crit, 0, 22) . strftime('%F', strtotime(substr($crit, 22)));
            }
            elseif (substr($crit, 0, 13) == 'subject like ') {
              $xactwhere[$xactid][] = 'subject like "%' . mysql_real_escape_string(substr($crit, 13)) . '%"';
            }
          }

          $xactwhere[$xactid] = implode(' AND ', $xactwhere[$xactid]);
        }
        $xactwhere = '(' . implode(') OR (', $xactwhere) . ')';
        $excludeActivity = "INSERT INTO  Xa_{$this->_tableName} ( contact_id )
                  SELECT  DISTINCT civicrm_activity_target.target_contact_id
                  FROM civicrm_activity
                  LEFT JOIN civicrm_activity_target
                    ON civicrm_activity.id = civicrm_activity_target.activity_id                  
                  WHERE 
                     $xactwhere";
        CRM_Core_DAO::executeQuery($excludeActivity);
      }

      $sql = "CREATE TEMPORARY TABLE Ia_{$this->_tableName} ( id int PRIMARY KEY AUTO_INCREMENT,
                                                               contact_id varchar(64)) ENGINE=HEAP";

      CRM_Core_DAO::executeQuery($sql);

      if ($this->_activity_include) {
        $includeAct = "INSERT INTO Ia_{$this->_tableName} (contact_id)
                  SELECT  DISTINCT civicrm_activity_target.target_contact_id
                  FROM civicrm_activity
                  LEFT JOIN civicrm_activity_target
                    ON civicrm_activity.id = civicrm_activity_target.activity_id";
      }
      else {
        $includeAct = "INSERT INTO Ia_{$this->_tableName} (contact_id)
                 SELECT              civicrm_contact.id as contact_id
                 FROM                civicrm_contact";
      }

      //used only when exclude tag is selected
      if ($this->_activity_exclude) {
        $includeAct .= " LEFT JOIN        Xa_{$this->_tableName} ";
        if ($this->_activity_include) {
          $includeAct .= " ON       civicrm_activity_target.target_contact_id = Xa_{$this->_tableName}.contact_id";
        }
        else {
          $includeAct .= " ON       civicrm_contact.id = Xa_{$this->_tableName}.contact_id";
        }
      }
      $iactwhere = array();
      foreach ($this->_activity_include as $iactid => $iactitem) {
        foreach ($iactitem as $crit) {
          if (substr($crit, 0, 19) == 'activity_type_id = ') {
            $iactwhere[$iactid][] = 'activity_type_id = ' . intval(substr($crit, 19));
          }
          elseif (substr($crit, 0, 21) == 'activity_status_id = ') {
            $iactwhere[$iactid][] = 'status_id = ' . intval(substr($crit, 21));
          }
          elseif (in_array(substr($crit, 0, 22), array(
            'activity_date_time >= ', 'activity_date_time <= '))) {
            $iactwhere[$iactid][] = substr($crit, 0, 22) . strftime('%F', strtotime(substr($crit, 22)));
          }
          elseif (substr($crit, 0, 13) == 'subject like ') {
            $iactwhere[$iactid][] = 'subject like "%' . mysql_real_escape_string(substr($crit, 13)) . '%"';
          }
        }

        $iactwhere[$iactid] = implode(' AND ', $iactwhere[$iactid]);
      }
      if ($iactwhere) {
        $iactwhere = '(' . implode(') OR (', $iactwhere) . ')';
      }
      if ($iactwhere) {
        $includeAct .= " WHERE   $iactwhere";
      }
      else {
        $includeAct .= " WHERE ( 1 ) ";
      }

      //used only when exclude tag is selected
      if ($this->_activity_exclude) {
        $includeAct .= " AND  Xa_{$this->_tableName}.contact_id IS null";
      }

      CRM_Core_DAO::executeQuery($includeAct);
    }
    //end activities

    $from = " FROM civicrm_contact contact_a";

    $this->buildACLClause('contact_a');

    /*
         * check the situation and set booleans
         */

    $Ig = ($iGroups != 0);

    $It = ($iTags != 0);

    $Ia = ($this->_activity_include);

    $Xg = ($xGroups != 0);

    $Xt = ($xTags != 0);

    $Xa = ($this->_activity_exclude);
    //PICK UP FROM HERE
    if (!$this->_groups && !$this->_tags || !$this->_groups && !$this->_activities || !$this->_tags && !$this->_activities) {
      $this->_andOr = 1;
    }
    /*
         * Set from statement depending on array sel
         */

    $whereitems = array();
    foreach (array(
      'Ig', 'It', 'Ia') as $inc) {
      if ($this->_andOr == 1) {
        if ($$inc) {
          $from .= " INNER JOIN {$inc}_{$this->_tableName} temptable$inc ON (contact_a.id = temptable$inc.contact_id)";
        }
      }
      else {
        if ($$inc) {
          $from .= " LEFT JOIN {$inc}_{$this->_tableName} temptable$inc ON (contact_a.id = temptable$inc.contact_id)";
        }
      }
      if ($$inc) {
        $whereitems[] = "temptable$inc.contact_id IS NOT NULL";
      }
    }
    $this->_where = $whereitems ? "(" . implode(' OR ', $whereitems) . ')' : '(1)';
    foreach (array(
      'Xg', 'Xt', 'Xa') as $exc) {
      if ($$exc) {
        $from .= " LEFT JOIN {$exc}_{$this->_tableName} temptable$exc ON (contact_a.id = temptable$exc.contact_id)";
        $this->_where .= " AND temptable$exc.contact_id IS NULL";
      }
    }

    $from .= " LEFT JOIN civicrm_email ON ( contact_a.id = civicrm_email.contact_id AND ( civicrm_email.is_primary = 1 OR civicrm_email.is_bulkmail = 1 ) ) {$this->_aclFrom}";

    if ($this->_aclWhere) {
      $this->_where .= " AND {$this->_aclWhere} ";
    }

    return $from;
  }

  function where($includeContactIDs = FALSE) {

    if ($includeContactIDs) {
      $contactIDs = array();

      foreach ($this->_formValues as $id => $value) {
        if ($value &&
          substr($id, 0, CRM_Core_Form::CB_PREFIX_LEN) == CRM_Core_Form::CB_PREFIX
        ) {
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
  function count() {
    $sql = $this->all();

    $dao = CRM_Core_DAO::executeQuery($sql);
    return $dao->N;
  }

  function contactIDs($offset = 0, $rowcount = 0, $sort = NULL) {
    return $this->all($offset, $rowcount, $sort, FALSE, TRUE);
  }

  function &columns() {
    return $this->_columns;
  }

  function summary() {
    return NULL;
  }

  function templateFile() {
    return 'CRM/Contact/Form/Search/Custom/Group.tpl';
  }

  function setTitle($title) {
    if ($title) {
      CRM_Utils_System::setTitle($title);
    }
    else {
      CRM_Utils_System::setTitle(ts('Search'));
    }
  }

  function buildACLClause($tableAlias = 'contact') {
    list($this->_aclFrom, $this->_aclWhere) = CRM_Contact_BAO_Contact_Permission::cacheClause($tableAlias);
  }
}

