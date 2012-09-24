<?php

/**
 * This loads a smarty help template via ajax and returns as html
 */
class CRM_Core_Page_Help {

  function run() {
    if (!empty($_REQUEST['tpl'])) {
      $file = $_REQUEST['tpl'] . '.hlp';
      $args = $_REQUEST;
      unset($args['tpl'], $args['class_name'], $args['type']);
      $smarty = CRM_Core_Smarty::singleton();
      foreach ($args as $id => $arg) {
        $smarty->assign($id, $arg);
      }
      print $smarty->fetch($file);
    }
  }

}
