<?
require_once 'CRM/Event/Badge.php';

class CRM_Event_Badge_Simple extends CRM_Event_Badge {

   public function generateLabel($participant) {
     $txt = $this->event->name."\n".$participant['first_name']. " ".$participant['last_name'] ."\n". $participant['current_employer'];
     $this->pdf->MultiCell ($this->pdf->width, $this->pdf->lineHeight, $txt);
   }

}

?>
