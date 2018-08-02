<?php
/**
 *    PHP Fix My Files
 *    @category    FileFormatter
 *    @package    FixMyFiles
 *    @author        Thapelo Moeti
 *    @license    MIT (http://url.com/)
 *    @version    1.0.0
 */

require_once 'fixmyfiles.php';

/**
 *    To strip away anytype of options
 *
 *    @param    array    $options   options to filter
 *    @return   array    $_options  filtered options
 */
function removeOptions( $options ) {
    $_options = array();

    foreach( $options as $a ) {
        if( strpos( $a, '-' ) !== 0 )
            $_options[] = $a;
    }

    return $_options;
}

$shortopt = '';
$longopt  = array( 'spaces:', 'tab-to-space', 'unix-to-windows', 'ignore-spacing', 'ignore-endings' );

$options = getopt( $shortopt, $longopt );

//    Options we are going pass to the main class
$_options = array();

// Let the user know we are running
echo "Running Fix My Files v0.1\n";

// Are we converting tabs to spaces?
if( isset( $options[ 'tab-to-space' ] ) ) {
    $_options[ 'tabToSpace' ] = true;
}

// What about line endings?
if( isset( $options[ 'unix-to-windows' ] ) ) {
    $_options[ 'toWindowsEnding' ] = true;
}

// Setting the number of spaces
if( isset( $options[ 'spaces' ] ) ) {
    $_options[ 'spaces' ] = intval( $options[ 'spaces' ] );
}

/* because the first arg always points to this file */
array_shift( $argv );

// Lets avoid duplications
$argv = array_unique( $argv );

$files = array();
    
$_argv = removeOptions( $argv );

// Let's list all the files
if( $_argv ) {

    foreach( $_argv as $filename ) {
        echo "\nPassed Path: {$filename}\n";

        $_options[ 'path' ] = $filename;

        $fixmyfiles = new FixMyFiles( $_options );
        $fixed = $fixmyfiles->fix();

        echo "\n--Fixed: ", count( $fixed );
    }
} else {
    $fixmyfiles = new FixMyFiles( $_options );
    $fixed = $fixmyfiles->fix();

    echo "\n--Fixed: ", count( $fixed );
}

echo "\n\nEnd of Fix My Files Script";
