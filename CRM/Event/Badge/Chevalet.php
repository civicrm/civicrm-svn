<?php

require_once 'CRM/Event/Badge.php';

class CRM_Event_Badge_Chevalet extends CRM_Event_Badge {

   function __construct() {
       parent::__construct();
       $this->format = array('name' => 'L7163', 'paper-size' => 'A4', 'metric' => 'mm', 'lMargin' => 5,
                                'tMargin' => 15, 'NX' => 1, 'NY' => 2, 'SpaceX' => 2.5, 'SpaceY' => 0,
                                'width' => 280, 'height' => 100, 'font-size' => 18);
   }

   public function generateLabel($participant) {
     $txt = $this->event['name']."\n".$participant['first_name']. " ".$participant['last_name'] ."\n". $participant['current_employer'];
     $this->pdf->MultiCell ($this->pdf->width, $this->pdf->lineHeight, $txt);
   }

}

