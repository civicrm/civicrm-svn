<?php

/**
 * This loads a smarty help file via ajax and returns as html
 */
class CRM_Core_Page_Help {

  function run() {
    $args = $_REQUEST;
    if (!empty($args['file'])) {
      $file = $args['file'] . '.hlp';
      unset($args['file'], $args['class_name'], $args['type']);
      $smarty = CRM_Core_Smarty::singleton();
      foreach ($args as $id => $arg) {
        $smarty->assign($id, $arg);
      }
      exit($smarty->fetch($file));
    }
  }

}
