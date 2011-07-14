<?php

class civicrm_api3  {

  function __construct ($config = null) {
    if (isset ($config) &&isset($config ['conf_path'] )) {
      require_once ($config ['conf_path'] .'/civicrm.settings.php');
      require_once 'CRM/Core/Config.php';
      require_once 'api/api.php';
      require_once "api/v3/utils.php";
      $this->cfg= CRM_Core_Config::singleton();
      $this->init();
      $this->ping();
    } else {
      $this->cfg= CRM_Core_Config::singleton();
    }
  }

  public function __get($entity) {
    //TODO check if it's a valid entity
    $this->currentEntity = $entity;
    return $this;
  }

    public function __call($action, $params) {
      // TODO : check if its a valid action
        return $this->call ($this->currentEntity,$action,$params[0]);
    }

    /**  As of PHP 5.3.0  */
    public static function __callStatic($name, $arguments) {
        // Should we implement it ?
        echo "Calling static method '$name' "
             . implode(', ', $arguments). "\n";
    }
  
    function call ($entity,$action='Get',$params = array()) {
      $this->ping ();// necessary only when the caller runs a long time (eg a bot)
      if (is_int($params)) {
        $params = array ('id'=> $params);
      }
    if (!isset ($params['version']))
      $params['version'] = 3;
    return civicrm_api ($entity,$action,$params);
  }

  function ping () {
    global $_DB_DATAOBJECT;
    foreach ($_DB_DATAOBJECT['CONNECTIONS'] as &$c) {
      if (!$c->connection->ping()) {
        $c->connect($this->cfg->dsn);
        if (!$c->connection->ping()) {
          die ("we couldn't connect");
        }
      }

    }
  }

  function init () {
    CRM_Core_DAO::init( $this->cfg->dsn );
  }
}
