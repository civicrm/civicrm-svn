<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.2                                                |
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
/*
* Copyright (C) 2010 Tech To The People
* Licensed to CiviCRM under the Academic Free License version 3.0.
*
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2010
 * $Id$
 *
 */

require_once ('CRM/Event/BAO/Event.php');
require_once ('CRM/Utils/PDF/Label.php');

/**
 * This class print the name badges for the participants
 * It isn't supposed to be called directly, but is the parent class of the classes in CRM/Event/Badges/XXX.php
 * 
 */
class CRM_Event_Badge {
    
     function __construct() {
        $this->format = '5160';
        //$this->format = CRM_Utils_PDF_Label::getFormat('5160');
     }

     /**
      * function to create the labels (pdf)
      *
      * @param   array    $participants
      * @return  null      
      * @access  public
      */
    public function run ( &$participants )
    {
        $eventID = $participants[0]['event_id'];
        $this->event= self::retrieveEvent ($eventID);
        //call function to create labels
        self::createLabels($participants);
        CRM_Utils_System::civiExit( 1 );
    }
    
   protected function retrieveEvent($eventID) {
       require_once 'CRM/Event/BAO/Event.php';
       $bao = new CRM_Event_BAO_Event ();
       if ($bao->get(array('id'=>$eventID))) {
          return $bao;
       }
       return false;
   }

   public function generateLabel($participant) {
     $txt = "{$this->event['title']}
{$participant['first_name']} {$participant['last_name']}
{$participant['current_employer']}";

     $this->pdf->MultiCell ($this->pdf->width, $this->pdf->lineHeight, $txt);
   }

   function pdfExtraFormat() {
   }

     /**
      * function to create labels (pdf)
      *
      * @param   array    $contactRows   assciated array of contact data
      * @param   string   $format   format in which labels needs to be printed
      *
      * @return  null      
      * @access  public
      */
    function createLabels(&$participants)
    {
        require_once 'CRM/Utils/String.php';
        
        $this->pdf = new CRM_Utils_PDF_Label($this->format,'mm');
        $this->pdfExtraFormat();
        $this->pdf->Open();
        $this->pdf->AddPage();
        $this->pdf->AddFont('DejaVu Sans', '', 'DejaVuSans.php');
        $this->pdf->SetFont('DejaVu Sans');
        $this->pdf->SetGenerator($this, "generateLabel");
       
        foreach ($participants as $participant) {
          $this->pdf->AddPdfLabel($participant);
        }
        $this->pdf->Output( $this->event->title.'.pdf', 'D' );
    }
    
}
