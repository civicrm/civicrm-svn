<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 1.4                                                |
 +--------------------------------------------------------------------+
 | Copyright (c) 2005 Donald A. Lobo                                  |
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
 | Foundation at info[AT]socialsourcefoundation[DOT]org.  If you have |
 | questions about the Affero General Public License or the licensing |
 | of CiviCRM, see the Social Source Foundation CiviCRM license FAQ   |
 | at http://www.openngo.org/faqs/licensing.html                      |
 +--------------------------------------------------------------------+
*/


/**
 * Personal Information Form Page
 *
 * @package CRM
 * @author Donald A. Lobo <lobo@yahoo.com>
 * @copyright Donald A. Lobo (c) 2005
 * $Id$
 *
 */

require_once 'CRM/Quest/Form/App.php';
require_once 'CRM/Core/OptionGroup.php';

/**
 * This class generates form components for relationship
 * 
 */
class CRM_Quest_Form_App_Academic extends CRM_Quest_Form_App
{
    /**
     * This function sets the default values for the form. Relationship that in edit/view action
     * the default values are retrieved from the database
     * 
     * @access public
     * @return void
     */
    function setDefaultValues( ) 
    {
        $defaults = array( );
        return $defaults;
    }
    

    /**
     * Function to actually build the form
     *
     * @return void
     * @access public
     */
    public function buildQuickForm( ) 
    {
        $attributes = CRM_Core_DAO::getAttribute('CRM_Quest_DAO_Student' );
        
        // name of school
        $this->addElement('text', 'gpa',
                          ts( 'What is your GPA?' ),
                          $attributes['gpa'] );
        $this->addRule('gpa' , ts("Please enter GPA"),'required');

        $this->addYesNo( 'is_class_ranking',
                         ts( 'Does your school give class rankings?' ),null,true );

        $this->addElement('text', 'class_rank',
                          ts( 'If yes, what is your class rank?' ),
                          $attributes['class_rank']  );
        $this->addRule( "class_rank", ts('Number not valid.'), 'integer' );
        
        $this->addElement('text', 'class_num_students',
                          null,
                          $attributes['class_num_students']  );
        $this->addRule( "class_num_students", ts('Number not valid.'), 'integer' );

        $this->addSelect( 'class_rank_percent', ts( 'Percent class rank' ) );

        $this->addElement('textarea', 'gpa_explanation',
                          ts( 'Explain your GPA' ),
                          $attributes['gpa_explanation'] );

        $attributes = CRM_Core_DAO::getAttribute('CRM_Quest_DAO_Honor');
        for ( $i = 1; $i <= 5; $i++ ) {
            $this->addElement('text', 'description_' . $i,
                              ts( 'Honors' ),
                              $attributes['description'] );

            $this->addElement('date', 'award_date_' . $i,
                              null,
                              CRM_Core_SelectValues::date( 'custom', 5, 1, "M\001Y" ) );
        }
        
        parent::buildQuickForm( );
    }

    /**
     * process the form after the input has been submitted and validated
     *
     * @access public
     * @return void
     */
    public function postProcess() 
    {

        $honorParams = $params = $this->controller->exportValues( $this->_name );
        $values = $this->controller->exportValues( 'Personal' );
        $params = array_merge( $params,$values );
       
        $id = $this->get('id');
        $contact_id = $this->get('contact_id');
        //$ids = array('id'=>$id ,'contact_id' => $contact_id);
        $ids = array();
        $ids['id'] = $id;
        $ids['contact_id'] = $contact_id;

        require_once 'CRM/Quest/BAO/Student.php';
        $student = CRM_Quest_BAO_Student::create( $params, $ids);

        // to add honour records 
        $honors = array();
        foreach ( $honorParams as $key => $value ){
            $field = explode('_' , $key ) ;
            if ($field[0] == 'description') {
                $honors[$field[1]]['description'] = $value;
            } else if ( $field[0] == 'award' && $field[1] == 'date' ) {
                $honors[$field[2]]['award_date'] = $value;
            }
        }
        
        require_once 'CRM/Quest/BAO/Honor.php';
        $honorIds = $this->get( 'honorIds', $honorIds );
        foreach ( $honors as $key => $honor ) {
            $ids = array();
            if ( $honorIds[$key] ) {
                $ids['id'] = $honorIds[$key];
            }
            $honor['contact_id']     = $contact_id;
            $newHonor                = CRM_Quest_BAO_Honor::create( $honor,$ids );
            $honorIds[$key]          = $newHonor->id;
        }
        $this->set( 'honorIds', $honorIds);
        
    }//end of function

    /**
     * Return a descriptive name for the page, used in wizard header
     *
     * @return string
     * @access public
     */
    public function getTitle()
    {
        return ts('Academic Information');
    }
    
}

?>