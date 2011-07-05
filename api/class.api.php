<?

class civicrmAPI3  {

  function __construct ($config == null) {
    $this->cfg= CRM_Core_Config::singleton();
    if (isset ($config) &&isset($config ['conf_path'] )) {
      require_once ($config ['conf_path'] .'/civicrm.settings.php');
      require_once 'CRM/Core/Config.php';
      require_once 'api/api.php';
      require_once "api/v3/utils.php";
      $this->init();
      $this->ping();
    }
  }

  function get ($entity,$params=array()){
    return $this->call ($entity,'Get',$params);
  }
  function create ($entity,$params=array()){
    return $this->call ($entity,'Create',$params);
  }
  function delete ($entity,$params=array()){
    return $this->call ($entity,'Delete',$params);
  }

  function call ($entity,$action='Get',$params = array()) {
    $this->ping ();// necessary only when the caller runs a long time (eg a bot)
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
