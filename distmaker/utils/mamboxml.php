<?php

if( isset( $GLOBALS['_SERVER']['DM_SOURCEDIR'] ) ) {
    $sourceCheckoutDir = $GLOBALS['_SERVER']['DM_SOURCEDIR'];
} else {
    $sourceCheckoutDir = $argv[1];
}
$sourceCheckoutDirLength = strlen( $sourceCheckoutDir );

if( isset( $GLOBALS['_SERVER']['DM_TMPDIR'] ) ) {
    $targetDir = $GLOBALS['_SERVER']['DM_TMPDIR'] . '/com_civicrm/civicrm';
} else {
    $targetDir = $argv[2];
}
$targetDirLength = strlen( $targetDir );

require_once "$sourceCheckoutDir/civicrm.settings.php";
require_once 'Smarty/Smarty.class.php';

$path = array( 'CRM', 'api', 'bin', 'css', 'i', 'js', 'l10n', 'sql', 'templates', 'mambo', 'packages' );
$files = array( 'license.txt' => 1,
                'affero_gpl.txt' => 1,
                'civicrm-version.txt' => 1, 
                'gpl.txt' => 1, 
                'README.txt' => 1, 
                'civicrm.default.php' => 1 );
foreach ( $path as $v ) {
    $rootDir = "$targetDir/$v";
    walkDirectory( new DirectoryIterator( $rootDir ), $files, $targetDirLength );
}
generateMamboConfig( $files );

/**
 * This function creates destination directory
 *
 * @param $dir directory name to be created
 * @param $peram mode for that directory
 *
 */
function createDir( $dir, $perm = 0755 ) {
    if ( ! is_dir( $dir ) ) {
        echo "Outdir: $dir\n";
        mkdir( $dir, $perm, true );
    }
}

function generateMamboConfig( &$files ) {
    global $targetDir, $sourceCheckoutDir;

    $smarty =& new Smarty( );
    $smarty->template_dir = $sourceCheckoutDir . '/xml/templates';
    $smarty->compile_dir  = '/tmp/templates_c';
    createDir( $smarty->compile_dir );

    $smarty->assign( 'files', array_keys( $files ) );
    $xml = $smarty->fetch( 'mambo.tpl' );
    
    $output = $targetDir . '/mambo/civicrm.xml';
    $fd = fopen( $output, "w" );
    fputs( $fd, $xml );
    fclose( $fd );
    
}

function walkDirectory( $iter, &$files, $length ) {
    while ($iter->valid()) {
        $node = $iter->current();
        
        $path = $node->getPathname( );
        $name = $node->getFilename( );
        if ( $node->isDir( )      && 
             $node->isReadable( ) &&
             ! $node->isDot( )    &&
             $name != '.svn' ) {
            walkDirectory(new DirectoryIterator( $path ), $files, $length);
        } else if ( $node->isFile( ) ) {
            if ( substr( $name, -1, 1 ) != '~' && substr( $name, 0, 1 ) != '#' ) {
                $files[ substr( $path, $length + 1 ) ] = 1;
            }
        }
        
        $iter->next( );
    }
}
?>
