<?php

function civicrm_setup( $filesDirectory ) {
    global $crmPath, $sqlPath, $pkgPath, $tplPath;
    global $compileDir;

    $pkgPath = $crmPath . DIRECTORY_SEPARATOR . 'packages';
    set_include_path( $crmPath . PATH_SEPARATOR .
                      $pkgPath . PATH_SEPARATOR .
                      get_include_path( ) );

    $sqlPath = $crmPath . DIRECTORY_SEPARATOR . 'sql';
    $tplPath = $crmPath . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'CRM' . DIRECTORY_SEPARATOR . 'common' . DIRECTORY_SEPARATOR;

    $scratchDir   = $filesDirectory . DIRECTORY_SEPARATOR . 'civicrm';
    if ( ! is_dir( $scratchDir ) ) {
        mkdir( $scratchDir, 0777 );
    }
    
    $compileDir        = $scratchDir . DIRECTORY_SEPARATOR . 'templates_c' . DIRECTORY_SEPARATOR;
    if ( ! is_dir( $compileDir ) ) {
        mkdir( $compileDir, 0777 );
    }
    $compileDir = addslashes( $compileDir );
}

function civicrm_write_file( $name, &$buffer ) {
    $fd  = fopen( $name, "w" );
    if ( ! $fd ) {
        die( "Cannot open $name" );

    }
    fputs( $fd, $buffer );
    fclose( $fd );
}

function civicrm_main( &$config ) {
    global $sqlPath, $crmPath, $installType;

    if ( $installType == 'drupal' ) {
        global $cmsPath;
        civicrm_setup( $cmsPath . DIRECTORY_SEPARATOR . 'files' );
    } elseif ( $installType == 'standalone' ) {
        civicrm_setup( $crmPath . DIRECTORY_SEPARATOR . 'standalone' . DIRECTORY_SEPARATOR . 'files' );
    }

    $dsn = "mysql://{$config['mysql']['username']}:{$config['mysql']['password']}@{$config['mysql']['server']}/{$config['mysql']['database']}?new_link=true";

    civicrm_source( $dsn, $sqlPath . DIRECTORY_SEPARATOR . 'civicrm.mysql'   );

    if ( isset( $config['loadGenerated'] ) &&
         $config['loadGenerated'] ) {
        civicrm_source( $dsn, $sqlPath . DIRECTORY_SEPARATOR . 'civicrm_generated.mysql', true );
    } else {
        civicrm_source( $dsn, $sqlPath . DIRECTORY_SEPARATOR . 'civicrm_data.mysql' );
    }
    
    // generate backend settings file
    if ( $installType == 'drupal' ) {
        $configFile =
            $cmsPath  . DIRECTORY_SEPARATOR .
            'sites'   . DIRECTORY_SEPARATOR .
            'default' . DIRECTORY_SEPARATOR .
            'civicrm.settings.php';
    } elseif ( $installType == 'standalone' ) {
        $configFile =
            $crmPath     . DIRECTORY_SEPARATOR .
            'standalone' . DIRECTORY_SEPARATOR .
            'civicrm.settings.php';
    }

    $string = civicrm_config( $config );
    civicrm_write_file( $configFile,
                        $string );
}

function civicrm_source( $dsn, $fileName, $lineMode = false ) {
    global $crmPath;

    require_once 'DB.php';

    $db  =& DB::connect( $dsn );
    if ( PEAR::isError( $db ) ) {
        die( "Cannot open $dsn: " . $db->getMessage( ) );
    }

    if ( ! $lineMode ) {
        $string = file_get_contents( $fileName );
        
        //get rid of comments starting with # and --
        $string = ereg_replace("\n#[^\n]*\n", "\n", $string );
        $string = ereg_replace("\n\-\-[^\n]*\n", "\n", $string );
        
        $queries  = explode( ';', $string );
        foreach ( $queries as $query ) {
            $query = trim( $query );
            if ( ! empty( $query ) ) {
                $res =& $db->query( $query );
                if ( PEAR::isError( $res ) ) {
                    die( "Cannot execute $query: " . $res->getMessage( ) );
                }
            }
        }
    } else {
        $fd = fopen( $fileName, "r" );
        while ( $string = fgets( $fd ) ) {
            $string = ereg_replace("\n#[^\n]*\n", "\n", $string );
            $string = ereg_replace("\n\-\-[^\n]*\n", "\n", $string );
            $string = trim( $string );
            if ( ! empty( $string ) ) {
                $res =& $db->query( $string );
                if ( PEAR::isError( $res ) ) {
                    die( "Cannot execute $string: " . $res->getMessage( ) );
                }
            }
        }
    }

}

function civicrm_config( &$config ) {
    global $crmPath, $comPath;
    global $compileDir;
    global $tplPath;
    global $installType;

    $params = array(
                    'crmRoot' => $crmPath,
                    'templateCompileDir' => $compileDir,
                    'frontEnd' => 0,
                    'dbUser' => $config['mysql']['username'],
                    'dbPass' => $config['mysql']['password'],
                    'dbHost' => $config['mysql']['server'],
                    'dbName' => $config['mysql']['database'],
                    );
    
    if ( $installType == 'drupal' ) {
        $params['cms']        = 'Drupal';
        $params['cmsVersion'] = '5.2';
        $params['usersTable'] = 'users';
        $params['baseURL']    = civicrm_cms_base( );
        $params['CMSdbUser']  = $config['drupal']['username'];
        $params['CMSdbPass']  = $config['drupal']['password'];
        $params['CMSdbHost']  = $config['drupal']['server'];
        $params['CMSdbName']  = $config['drupal']['database'];
    } elseif ( $installType == 'standalone' ) {
        $filesDir = $crmPath . DIRECTORY_SEPARATOR . 'standalone' . DIRECTORY_SEPARATOR . 'files';

        $params['cms']            = 'Standalone';
        $params['cmsVersion']     = '';
        $params['usersTable']     = '';
        $params['cmsURLVar']      = 'q';
        $params['uploadDir']      = $filesDir . DIRECTORY_SEPARATOR . 'upload';
        $params['imageUploadDir'] = '';
        $params['imageUploadURL'] = '';
        $params['customFileUploadDir'] = $filesDir . DIRECTORY_SEPARATOR . 'custom';
        $params['baseURL']        = civicrm_cms_base( )  . 'standalone/';
        $params['resourceURL']    = civicrm_cms_base( );
    }

    $str = file_get_contents( $tplPath . 'civicrm.settings.php.sample.tpl' );
    foreach ( $params as $key => $value ) { 
        $str = str_replace( '%%' . $key . '%%', $value, $str ); 
    } 
    return trim( $str );
}

function civicrm_cms_base( ) {
    global $installType;

    // for drupal
    $numPrevious = 6;

    // for standalone
    if ( $installType == 'standalone' ) {
        $numPrevious = 2;
    }

    $url = 'http://' . $_SERVER['HTTP_HOST'];

    $baseURL = $_SERVER['SCRIPT_NAME'];

    for ( $i = 1; $i <= $numPrevious; $i++ ) {
        $baseURL = dirname( $baseURL );
    }

    // remove the last directory separator string from the directory
    if ( substr( $baseURL, -1, 1 ) == DIRECTORY_SEPARATOR ) {
        $baseURL = substr( $baseURL, 0, -1 );
    }

    // also convert all DIRECTORY_SEPARATOR to the forward slash for windoze
    $baseURL = str_replace( DIRECTORY_SEPARATOR, '/', $baseURL );

    if ( $baseURL != '/' ) {
        $baseURL .= '/';
    }

    return $url . $baseURL;
}

function civicrm_home_url( ) {
    $drupalURL = civicrm_cms_base( );
    return $drupalURL . 'index.php?q=civicrm';
}



