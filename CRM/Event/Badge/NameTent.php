<?php

require_once 'CRM/Event/Badge.php';

class CRM_Event_Badge_NameTent extends CRM_Event_Badge {

   function __construct() {
       parent::__construct();
       $this->format = array('name' => 'L7163', 'paper-size' => 'A4', 'metric' => 'mm', 'lMargin' => 10,
                                'tMargin' => 10, 'NX' => 1, 'NY' => 1, 'SpaceX' => 0, 'SpaceY' => 0,
                                'width' => 277, 'height' => 190, 'font-size' => 36);
   }

   function pdfExtraFormat() {
     $this->pdf->setPageFormat('A4', 'L');
   }

   protected function writeOneSide(&$participant) {
     $txt = $participant['first_name']. " ".$participant['last_name'];
     $this->pdf->SetXY(0,$this->pdf->height/2+20);
     $this->pdf->SetFontSize(54);
     $this->pdf->Write (0, $txt,null,null,'C');
     $this->pdf->SetXY(0,$this->pdf->height/2+50);
     $this->pdf->SetFontSize(36);
     $this->pdf->Write (0, $participant['current_employer'],null,null,'C');

   }

   public function generateLabel($participant) {
     $this->writeOneSide($participant);
     $this->pdf->StartTransform();
     $this->writeOneSide($participant);
     $this->pdf->Rotate(180,$this->pdf->width/2+$this->pdf->marginLeft,$this->pdf->height/2);
     $this->writeOneSide($participant);
     $this->pdf->StopTransform();
   }

}

