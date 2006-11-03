<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 1.6                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2006                                  |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the Affero General Public License Version 1,    |
 | March 2002.                                                        |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the Affero General Public License for more details.            |
 |                                                                    |
 | You should have received a copy of the Affero General Public       |
 | License along with this program; if not, contact the Social Source |
 | Foundation at info[AT]civicrm[DOT]org.  If you have questions       |
 | about the Affero General Public License or the licensing  of       |
 | of CiviCRM, see the Social Source Foundation CiviCRM license FAQ   |
 | http://www.civicrm.org/licensing/                                  |
 +--------------------------------------------------------------------+
*/


require_once 'CRM/Utils/String.php';
require_once 'CRM/Utils/Type.php';

require_once 'CRM/Import/Field.php';

abstract class CRM_Import_Parser {

    const
        MAX_ERRORS      = 250,
        MAX_WARNINGS    = 25,
        VALID           =  1,
        WARNING         =  2,
        ERROR           =  4,
        CONFLICT        =  8,
        STOP            = 16,
        DUPLICATE       = 32,
        MULTIPLE_DUPE   = 64,
        NO_MATCH        = 128;

    /**
     * various parser modes
     */
    const
        MODE_MAPFIELD = 1,
        MODE_PREVIEW  = 2,
        MODE_SUMMARY  = 4,
        MODE_IMPORT   = 8;

    /**
     * codes for duplicate record handling
     */
    const
        DUPLICATE_SKIP = 1,
        DUPLICATE_REPLACE = 2,
        DUPLICATE_UPDATE = 4,
        DUPLICATE_FILL = 8,
        DUPLICATE_NOCHECK = 16;

    /**
     * various Contact types
     */
    const
        CONTACT_INDIVIDUAL     = 1,
        CONTACT_HOUSEHOLD      = 2,
        CONTACT_ORGANIZATION   = 4;

    protected $_fileName;

    /**#@+
     * @access protected
     * @var integer
     */

    /**
     * imported file size
     */
    protected $_fileSize;

    /**
     * seperator being used
     */
    protected $_seperator;

    /**
     * total number of lines in file
     */
    protected $_lineCount;

    /**
     * total number of non empty lines
     */
    protected $_totalCount;

    /**
     * running total number of valid lines
     */
    protected $_validCount;

    /**
     * running total number of invalid rows
     */
    protected $_invalidRowCount;

    /**
     * maximum number of invalid rows to store
     */
    protected $_maxErrorCount;

    /**
     * array of error lines, bounded by MAX_ERROR
     */
    protected $_errors;

    /**
     * total number of conflict lines
     */
    protected $_conflictCount;

    /**
     * array of conflict lines
     */
    protected $_conflicts;

    /**
     * total number of duplicate (from database) lines
     */
    protected $_duplicateCount;

    /**
     * array of duplicate lines
     */
    protected $_duplicates;

    /**
     * running total number of warnings
     */
    protected $_warningCount;

    /**
     * running total number of un matched Conact
     */
    protected $_unMatchCount;

    /**
     * array of unmatched lines
     */
    protected $_unMatch;

    /**
     * maximum number of warnings to store
     */
    protected $_maxWarningCount = self::MAX_WARNINGS;

    /**
     * array of warning lines, bounded by MAX_WARNING
     */
    protected $_warnings;

    /**
     * array of all the fields that could potentially be part
     * of this import process
     * @var array
     */
    protected $_fields;

    /**
     * array of the fields that are actually part of the import process
     * the position in the array also dictates their position in the import
     * file
     * @var array
     */
    protected $_activeFields;

    /**
     * cache the count of active fields
     *
     * @var int
     */
    protected $_activeFieldCount;

    /**
     * maximum number of non-empty/comment lines to process
     *
     * @var int
     */
    protected $_maxLinesToProcess;

    /**
     * cache of preview rows
     *
     * @var array
     */
    protected $_rows;


    /**
     * filename of error data
     *
     * @var string
     */
    protected $_errorFileName;


    /**
     * filename of conflict data
     *
     * @var string
     */
    protected $_conflictFileName;


    /**
     * filename of duplicate data
     *
     * @var string
     */
    protected $_duplicateFileName;

    /**
     * filename of mismatch data
     *
     * @var string
     */
    protected $_misMatchFilemName;


    /**
     * contact type
     *
     * @var int
     */

    public $_contactType;
    

    function __construct() {
        $this->_maxLinesToProcess = 0;
        $this->_maxErrorCount = self::MAX_ERRORS;
    }

    abstract function init();

    function run( $fileName,
                  $seperator = ',',
                  &$mapper,
                  $skipColumnHeader = false,
                  $mode = self::MODE_PREVIEW,
                  $contactType = self::CONTACT_INDIVIDUAL,
                  $onDuplicate = self::DUPLICATE_SKIP,
                  $statusID = null, $totalRowCount = null ) {
        switch ($contactType) {
        case CRM_Import_Parser::CONTACT_INDIVIDUAL :
            $this->_contactType = 'Individual';
            break;
        case CRM_Import_Parser::CONTACT_HOUSEHOLD :
            $this->_contactType = 'Household';
            break;
        case CRM_Import_Parser::CONTACT_ORGANIZATION :
            $this->_contactType = 'Organization';
        }

        $this->init();
      
        $this->_seperator = $seperator;

        $fd = fopen( $fileName, "r" );
        if ( ! $fd ) {
            return false;
        }

        $this->_lineCount  = $this->_warningCount   = 0;
        $this->_invalidRowCount = $this->_validCount     = 0;
        $this->_totalCount = $this->_conflictCount = 0;
    
        $this->_errors   = array();
        $this->_warnings = array();
        $this->_conflicts = array();

        $this->_fileSize = number_format( filesize( $fileName ) / 1024.0, 2 );
        
        if ( $mode == self::MODE_MAPFIELD ) {
            $this->_rows = array( );
        } else {
            $this->_activeFieldCount = count( $this->_activeFields );
        }

        if ( $mode == self::MODE_IMPORT ) {
            //get the key of email field
            foreach($mapper as $key => $value) {
                if ( strtolower($value) == 'email' ) {
                    $emailKey = $key;
                    break;
                }
            }
        }

        if ( $statusID ) {
            $skip = 4;
            $config =& CRM_Core_Config::singleton( );
            $statusFile = "{$config->uploadDir}status_{$statusID}.txt";
            $status = "<div class='description'>&nbsp; No processing status reported yet.</div>";
            require_once 'Services/JSON.php';
            $json =& new Services_JSON( ); 
            $contents = $json->encode( array( 0, $status ) );
            file_put_contents( $statusFile, $contents );
            $startTimestamp = $currTimestamp = $prevTimestamp = time( );
        }
        while ( ! feof( $fd ) ) {
            $this->_lineCount++;

            $values = fgetcsv( $fd, 8192, $seperator );
            if ( ! $values ) {
                continue;
            }

            self::encloseScrub($values);

            // skip column header if we're not in mapfield mode
            if ( $mode != self::MODE_MAPFIELD && $skipColumnHeader ) {
                    $skipColumnHeader = false;
                    continue;
            }

            /* trim whitespace around the values */
            $empty = true;
            foreach ($values as $k => $v) {
                $values[$k] = trim($v, " .\t\r\n");
            }

            if ( CRM_Utils_System::isNull( $values ) ) {
                continue;
            }

            $this->_totalCount++;
            
            if ( $mode == self::MODE_MAPFIELD ) {
                $returnCode = $this->mapField( $values );
            } else if ( $mode == self::MODE_PREVIEW ) {
                $returnCode = $this->preview( $values );
            } else if ( $mode == self::MODE_SUMMARY ) {
                $returnCode = $this->summary( $values );
            } else if ( $mode == self::MODE_IMPORT ) {
                $returnCode = $this->import( $onDuplicate, $values );
                if ( $statusID && ( ( $this->_lineCount % $skip ) == 0 ) ) {
                    $currTimestamp = time( );
                    $totalTime = ( $currTimestamp - $startTimestamp );
                    $time = ( $currTimestamp - $prevTimestamp );
                    $recordsLeft = $totalRowCount - $this->_lineCount;
                    if ( $recordsLeft < 0 ) {
                        $recordsLeft = 0;
                    }
                    $estimatedTime = ( $recordsLeft / $skip ) * $time;
                    $processedPercent  = (int ) ( ( $this->_lineCount * 100 ) / $totalRowCount );
                    $status = "
<div class=\"description\">
&nbsp; <strong>{$this->_lineCount} of $totalRowCount - $estimatedTime seconds remaining</strong>
</div>
";
                    // ts('Your %1 contact record has been saved.', array(1 => $contact->contact_type_display))
                    require_once 'Services/JSON.php';
                    $json =& new Services_JSON( ); 
                    $contents = $json->encode( array( $processedPercent, $status ) );
                    file_put_contents( $statusFile, $contents );
                    $prevTimestamp = $currTimestamp;
                    sleep( 1 );
                }
            } else {
                $returnCode = self::ERROR;
            }

            // note that a line could be valid but still produce a warning
            if ( $returnCode & self::VALID ) {
                $this->_validCount++;
                if ( $mode == self::MODE_MAPFIELD ) {
                    $this->_rows[]           = $values;
                    $this->_activeFieldCount = max( $this->_activeFieldCount, count( $values ) );
                }
            }

            if ( $returnCode & self::WARNING ) {
                $this->_warningCount++;
                if ( $this->_warningCount < $this->_maxWarningCount ) {
                    $this->_warningCount[] = $line;
                }
            } 

            if ( $returnCode & self::ERROR ) {
                $this->_invalidRowCount++;
                if ( $this->_invalidRowCount < $this->_maxErrorCount ) {
                    array_unshift($values, $this->_lineCount);
                    $this->_errors[] = $values;
                }
            } 

            if ( $returnCode & self::CONFLICT ) {
                $this->_conflictCount++;
                array_unshift($values, $this->_lineCount);
                $this->_conflicts[] = $values;
            } 

             if ( $returnCode & self::NO_MATCH ) {
                $this->_unMatchCount++;
                array_unshift($values, $this->_lineCount);
                $this->_unMatch[] = $values;
            } 
            
            if ( $returnCode & self::DUPLICATE ) {
                if ( $returnCode & self::MULTIPLE_DUPE ) {
                    /* TODO: multi-dupes should be counted apart from singles
                     * on non-skip action */
                }
                $this->_duplicateCount++;
                array_unshift($values, $this->_lineCount);
                $this->_duplicates[] = $values;
                if ($onDuplicate != self::DUPLICATE_SKIP) {
                    $this->_validCount++;
                }
            }

            // we give the derived class a way of aborting the process
            // note that the return code could be multiple code or'ed together
            if ( $returnCode & self::STOP ) {
                break;
            }

            // if we are done processing the maxNumber of lines, break
            if ( $this->_maxLinesToProcess > 0 && $this->_validCount >= $this->_maxLinesToProcess ) {
                break;
            }
        }

        fclose( $fd );

        
        if ($mode == self::MODE_PREVIEW || $mode == self::MODE_IMPORT) {
            $customHeaders = $mapper;
            
            $customfields =& CRM_Core_BAO_CustomField::getFields($this->_contactType);
            foreach ($customHeaders as $key => $value) {
                if ($id = CRM_Core_BAO_CustomField::getKeyID($value)) {
                    $customHeaders[$key] = $customfields[$id][0];
                }
            }
            if ($this->_invalidRowCount) {
                // removed view url for invlaid contacts
                $headers = array_merge( array(  ts('Record Number'),
                                                ts('Reason')), 
                                        $customHeaders);
                $this->_errorFileName = $fileName . '.errors';
                self::exportCSV($this->_errorFileName, $headers, $this->_errors);
            }
            if ($this->_conflictCount) {
                $headers = array_merge( array(  ts('Record Number'),
                                                ts('Reason')), 
                                        $customHeaders);
                $this->_conflictFileName = $fileName . '.conflicts';
                self::exportCSV($this->_conflictFileName, $headers, $this->_conflicts);
            }
            if ($this->_duplicateCount) {
                $headers = array_merge( array(  ts('Record Number'), 
                                                ts('View Contact URL')),
                                        $customHeaders);

                $this->_duplicateFileName = $fileName . '.duplicates';
                self::exportCSV($this->_duplicateFileName, $headers, $this->_duplicates);
            }
            if ($this->_unMatchCount) {
                $headers = array_merge( array(  ts('Record Number'), 
                                                ts('Reason')),
                                        $customHeaders);

                $this->_misMatchFilemName = $fileName . '.mismatch';
                self::exportCSV($this->_misMatchFilemName, $headers,$this->_unMatch);
            }
            
        }
        //echo "$this->_totalCount,$this->_invalidRowCount,$this->_conflictCount,$this->_duplicateCount";
        return $this->fini();
    }

    abstract function mapField( &$values );
    abstract function preview( &$values );
    abstract function summary( &$values );
    abstract function import ( $onDuplicate, &$values );

    abstract function fini();

    /**
     * Given a list of the importable field keys that the user has selected
     * set the active fields array to this list
     *
     * @param array mapped array of values
     *
     * @return void
     * @access public
     */
    function setActiveFields( $fieldKeys ) {
        $this->_activeFieldCount = count( $fieldKeys );
        foreach ( $fieldKeys as $key ) {
            if ( empty( $this->_fields[$key] ) ) {
                $this->_activeFields[] =& new CRM_Import_Field( '', ts( '- do not import -' ) );
            } else {
                $this->_activeFields[] = clone( $this->_fields[$key] );
            }
        }
    }
    
    function setActiveFieldValues( $elements ) {
        $maxCount = count( $elements ) < $this->_activeFieldCount ? count( $elements ) : $this->_activeFieldCount;
        for ( $i = 0; $i < $maxCount; $i++ ) {
            $this->_activeFields[$i]->setValue( $elements[$i] );
        }

        // reset all the values that we did not have an equivalent import element
        for ( ; $i < $this->_activeFieldCount; $i++ ) {
            $this->_activeFields[$i]->resetValue();
        }

        // now validate the fields and return false if error
        $valid = self::VALID;
        for ( $i = 0; $i < $this->_activeFieldCount; $i++ ) {
            if ( ! $this->_activeFields[$i]->validate() ) {
                // no need to do any more validation
                $valid = self::ERROR;
                break;
            }
        }
        return $valid;
    }

    function setActiveFieldLocationTypes( $elements ) {
        for ($i = 0; $i < count( $elements ); $i++) {
            $this->_activeFields[$i]->_hasLocationType = $elements[$i];
        }
    }
    
    function setActiveFieldPhoneTypes( $elements ) {
        for ($i = 0; $i < count( $elements ); $i++) {
            $this->_activeFields[$i]->_phoneType = $elements[$i];
        }
    }

    function setActiveFieldRelated( $elements ) {
        for ($i = 0; $i < count( $elements ); $i++) {
            $this->_activeFields[$i]->_related = $elements[$i];
        }       
    }
    
    function setActiveFieldRelatedContactType( $elements ) {
        for ($i = 0; $i < count( $elements ); $i++) {
            $this->_activeFields[$i]->_relatedContactType = $elements[$i];
        }
    }
    
    function setActiveFieldRelatedContactDetails( $elements ) {
        for ($i = 0; $i < count( $elements ); $i++) {            
            $this->_activeFields[$i]->_relatedContactDetails = $elements[$i];
        }
    }
    
    function setActiveFieldRelatedContactLocType( $elements ) {
        for ($i = 0; $i < count( $elements ); $i++) {
            $this->_activeFields[$i]->_relatedContactLocType = $elements[$i];
        }
        
    }    
    
    function setActiveFieldRelatedContactPhoneType( $elements ) {
        for ($i = 0; $i < count( $elements ); $i++) {
            $this->_activeFields[$i]->_relatedContactPhoneType = $elements[$i];
        }        
    }

    /**
     * function to format the field values for input to the api
     *
     * @return array (reference ) associative array of name/value pairs
     * @access public
     */
    function &getActiveFieldParams( ) {
        $params = array( );
        for ( $i = 0; $i < $this->_activeFieldCount; $i++ ) {
            if ( isset( $this->_activeFields[$i]->_value ) ) {
                if (isset( $this->_activeFields[$i]->_hasLocationType)) {
                    if (! isset($params[$this->_activeFields[$i]->_name])) {
                        $params[$this->_activeFields[$i]->_name] = array();
                    }
                    
                    $value = array(
                        $this->_activeFields[$i]->_name => 
                                $this->_activeFields[$i]->_value,
                        'location_type_id' => 
                                $this->_activeFields[$i]->_hasLocationType);
                    
                    if (isset( $this->_activeFields[$i]->_phoneType)) {
                        $value['phone_type'] =
                            $this->_activeFields[$i]->_phoneType;
                    }
                    
                    $params[$this->_activeFields[$i]->_name][] = $value;
                }
                if (!isset($params[$this->_activeFields[$i]->_name])) {
                    if ( !isset($this->_activeFields[$i]->_related) ) {
                        $params[$this->_activeFields[$i]->_name] = $this->_activeFields[$i]->_value;
                    }
                }

                if ( isset($this->_activeFields[$i]->_related) && !empty($this->_activeFields[$i]->_value) ) {     
                    if (! isset($params[$this->_activeFields[$i]->_related])) {
                        $params[$this->_activeFields[$i]->_related] = array();
                    }
                    
                    if ( !isset($params[$this->_activeFields[$i]->_related]['contact_type']) && !empty($this->_activeFields[$i]->_relatedContactType) ) {
                        $params[$this->_activeFields[$i]->_related]['contact_type'] = $this->_activeFields[$i]->_relatedContactType;
                    }
                    
                    if ( isset($this->_activeFields[$i]->_relatedContactLocType)  && !empty($this->_activeFields[$i]->_value) )  {
                        
                        $params[$this->_activeFields[$i]->_related][$this->_activeFields[$i]->_relatedContactDetails] = array();
                        $value = array($this->_activeFields[$i]->_relatedContactDetails => $this->_activeFields[$i]->_value,
                                       'location_type_id' => $this->_activeFields[$i]->_relatedContactLocType);
                        
                        if (isset( $this->_activeFields[$i]->_relatedContactPhoneType)) {
                            $value['phone_type'] =  $this->_activeFields[$i]->_relatedContactPhoneType;
                        }

                        $params[$this->_activeFields[$i]->_related][$this->_activeFields[$i]->_relatedContactDetails][] = $value;
                    } else {
                        $params[$this->_activeFields[$i]->_related][$this->_activeFields[$i]->_relatedContactDetails] = 
                            $this->_activeFields[$i]->_value;                        
                    }
                }
            }
        }
        return $params;
    }

    function getSelectValues() {
        $values = array();
        foreach ($this->_fields as $name => $field ) {
            $values[$name] = $field->_title;
        }
        return $values;
    }

    function getSelectTypes() {
        $values = array();
        foreach ($this->_fields as $name => $field ) {
            $values[$name] = $field->_hasLocationType;
        }
        return $values;
    }

    function getHeaderPatterns() {
        $values = array();
        foreach ($this->_fields as $name => $field ) {
            $values[$name] = $field->_headerPattern;
        }
        return $values;
    }

    function getDataPatterns() {
        $values = array();
        foreach ($this->_fields as $name => $field ) {
            $values[$name] = $field->_dataPattern;
        }
        return $values;
    }

    function addField( $name, $title, $type = CRM_Utils_Type::T_INT,
                       $headerPattern = '//', $dataPattern = '//',
                       $hasLocationType = false) {
        $this->_fields[$name] =& new CRM_Import_Field($name, $title, $type, $headerPattern, $dataPattern, $hasLocationType);
        if ( empty( $name ) ) {
            $this->_fields['doNotImport'] =& new CRM_Import_Field($name, $title, $type, $headerPattern, $dataPattern, $hasLocationType);
        }
    }

    /**
     * setter function
     *
     * @param int $max 
     *
     * @return void
     * @access public
     */
    function setMaxLinesToProcess( $max ) {
        $this->_maxLinesToProcess = $max;
    }

    /**
     * Store parser values
     *
     * @param CRM_Core_Session $store 
     *
     * @return void
     * @access public
     */
    function set( $store, $mode = self::MODE_SUMMARY ) {
        $store->set( 'fileSize'   , $this->_fileSize          );
        $store->set( 'lineCount'  , $this->_lineCount         );
        $store->set( 'seperator'  , $this->_seperator         );
        $store->set( 'fields'     , $this->getSelectValues( ) );
        $store->set( 'fieldTypes' , $this->getSelectTypes( )  );
        
        $store->set( 'headerPatterns', $this->getHeaderPatterns( ) );
        $store->set( 'dataPatterns', $this->getDataPatterns( ) );
        $store->set( 'columnCount', $this->_activeFieldCount  );
        
        $store->set( 'totalRowCount'    , $this->_totalCount     );
        $store->set( 'validRowCount'    , $this->_validCount     );
        $store->set( 'invalidRowCount'  , $this->_invalidRowCount     );
        $store->set( 'conflictRowCount' , $this->_conflictCount );
        $store->set( 'unMatchCount'     , $this->_unMatchCount);
        
        switch ($this->_contactType) {
        case 'Individual':
            $store->set( 'contactType', CRM_Import_Parser::CONTACT_INDIVIDUAL );    
            break;
        case 'Household' :
            $store->set( 'contactType', CRM_Import_Parser::CONTACT_HOUSEHOLD );    
            break;
        case 'Organization':
            $store->set( 'contactType', CRM_Import_Parser::CONTACT_ORGANIZATION );    
        }
        
        if ($this->_invalidRowCount) {
            $store->set( 'errorsFileName', $this->_errorFileName );
        }
        if ($this->_conflictCount) {
            $store->set( 'conflictsFileName', $this->_conflictFileName );
        }
        if ( isset( $this->_rows ) && ! empty( $this->_rows ) ) {
            $store->set( 'dataValues', $this->_rows );
        }
        
        if ($this->_unMatchCount) {
            $store->set( 'mismatchFileName', $this->_misMatchFilemName);
        }
        
        if ($mode == self::MODE_IMPORT) {
            $store->set( 'duplicateRowCount', $this->_duplicateCount );
            if ($this->_duplicateCount) {
                $store->set( 'duplicatesFileName', $this->_duplicateFileName );
            }
           
        }
        //echo "$this->_totalCount,$this->_invalidRowCount,$this->_conflictCount,$this->_duplicateCount";
    }

    /**
     * Export data to a CSV file
     *
     * @param string $filename
     * @param array $header
     * @param data $data
     * @return void
     * @access public
     */
    static function exportCSV($fileName, $header, $data) {
        $output = array();
        $fd = fopen($fileName, 'w');

        foreach ($header as $key => $value) {
            $header[$key] = "\"$value\"";
        }
        $output[] = implode(',', $header);

        foreach ($data as $datum) {
            foreach ($datum as $key => $value) {
                $datum[$key] = "\"$value\"";
            }
            $output[] = implode(',', $datum);
        }
        fwrite($fd, implode("\n", $output));
        fclose($fd);
    }

    /** 
     * Remove single-quote enclosures from a value array (row)
     *
     * @param array $values
     * @param string $enclosure
     * @return void
     * @static
     * @access public
     */
    static function encloseScrub(&$values, $enclosure = "'") {
        if (empty($values)) 
            return;

        foreach ($values as $k => $v) {
            $values[$k] = preg_replace("/^$enclosure(.*)$enclosure$/", '$1', $v);
        }
    }

}

?>
