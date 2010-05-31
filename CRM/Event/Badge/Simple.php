<?
require_once 'CRM/Event/Badge.php';
require_once 'CRM/Utils/Date.php';

class CRM_Event_Badge_Simple extends CRM_Event_Badge {

   public function generateLabel($participant) {
     $date = CRM_Utils_Date::customFormat($this->event->start_date, "%e %b");
     $this->pdf->SetFontSize(8);
     $y = $this->pdf->GetY();
     $x = $this->pdf->GetAbsX();
     $this->pdf->Cell ($this->pdf->width, $this->pdf->lineHeight, $this->event->title ,0,1,"L");
     $this->pdf->SetXY($x,$y);
     $this->pdf->Cell ($this->pdf->width, $this->pdf->lineHeight, $date ,0,2,"R");
     $this->pdf->SetFontSize(12);
     $this->pdf->SetXY($x,$this->pdf->GetY()+5);
     $this->pdf->Cell ($this->pdf->width, $this->pdf->lineHeight, $participant['first_name']. " ".$participant['last_name'] ,0,2,"C");
     $this->pdf->SetFontSize(10);
     $this->pdf->SetXY($x,$this->pdf->GetY()+2);
     $this->pdf->Cell ($this->pdf->width, $this->pdf->lineHeight, $participant['current_employer'] ,0,2,"C");
     //$this->pdf->MultiCell ($this->pdf->width, $this->pdf->lineHeight, $txt,1,"L");
   }

}

?>
