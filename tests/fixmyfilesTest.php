<?php
/**
 *    Running some basic test
 *    @category    TestCase
 *    @package    FixMyFiles
 *    @author        Thapelo Moeti
 *    @license    MIT (http://url.com/)
 *    @version    1.0.0
 */

require_once 'fixmyfiles.php';

/**
 *    main class
 */
class FixMyFilesTest extends PHPUnit_Framework_TestCase {

    /**
     *  To crate the necessary files needed to run tests
     */
    public static function setUpBeforeClass() {
        if( is_writable( '.' ) ) {
            mkdir( 'mock-files' );

            file_put_contents( 'mock-files/with-tabs.php', "This\tfile\n\tHas\n\t\tTabs" );
            
            file_put_contents( 'mock-files/with-spaces.php', "This    File\n    Has  Too\n\nMany    \n        Spaces");
            
            file_put_contents( 'mock-files/with-windows-endings.php', "This file must\nhave Windows\nline endings\n\n" );

            file_put_contents( 'mock-files/with-unix-endings.php', str_replace( "\n", "\r\n", "This file\nmust have Unix\nline endings\n\n" ) );
        }
    }

    /**
     *  To delete the test files
     */
    public static function tearDownAfterClass() {
        $files = scandir( 'mock-files' );
        
        foreach( $files as $file ) {
            if( in_array( $file, array( '.', '..' ) ) ) {
                continue;
            }

            unlink( 'mock-files' . DIRECTORY_SEPARATOR . $file );
        }

        rmdir( 'mock-files' );
    }

    /**
     *    Testing the constructor with no parameters
     */
    public function testInit() {
        $fixmyfiles = new FixMyFiles;
        
        $expectedCallList = array( 'toUnixEnding', 'tabToSpace' );

        $this->assertEquals( getcwd(), $fixmyfiles->path );
        $this->assertEquals( 4, $fixmyfiles->spaces );
        $this->assertEquals( $expectedCallList, $fixmyfiles->callList );
    }

    /**
     *    Testing the spaceToTab function
     */
    public function testSpaceToTab() {
        $spaceToTab = new ReflectionMethod( 'FixMyFiles', 'spaceToTab' );

        $spaceToTab->setAccessible( true );

        $expected = "\t";

        $this->assertEquals( $expected, $spaceToTab->invoke( new FixMyFiles, "    " ), "spaceToTab Failed!" );
    }

    /**
     *    Testing the tabToSpace function
     */
    public function testTabToSpace() {
        $tabToSpace = new ReflectionMethod( 'FixMyFiles', 'tabToSpace' );

        $tabToSpace->setAccessible( true );

        $expected = "    ";

        $this->assertEquals( $expected, $tabToSpace->invoke( new FixMyFiles, "\t" ), "tabToSpace Failed!" );
    }

    /**
     *  Testing the toUnixEnding function
     */
    public function testToUnixEndings() {
        $toUnixEnding = new ReflectionMethod( 'FixMyFiles', 'toUnixEnding' );

        $toUnixEnding->setAccessible( true );

        $expected = "\n\n\n";

        $this->assertEquals( $expected, $toUnixEnding->invoke( new FixMyFiles, "\r\n\n\r\n" ), 'toUnixEnding Failed!' );
    }

    /**
     *  Testing the toWindowsEnding function
     */
    public function testToWindowsEndings() {
        $toWindowsEnding = new ReflectionMethod( 'FixMyFiles', 'toWindowsEnding' );

        $toWindowsEnding->setAccessible( true );

        $expected = "\r\n\r\n\r\n\r\n";

        $this->assertEquals( $expected, $toWindowsEnding->invoke( new FixMyFiles, "\n\r\n\n\r\n" ), 'toWindowsEnding Failed!' );
    }

    /**
     *    Testing the setPath function
     */
    public function testSetPath() {
        $fixmyfiles = new FixMyFiles;

        $this->expectException( Exception::class );

        $this->assertTrue( $fixmyfiles->setPath( 'fixmyfiles.php' ) );
        $this->assertTrue( $fixmyfiles->setPath( 'mock-files' ) );
        $this->assertFalse( $fixmyfiles->setPath( 'invalid' ) );
    }

    /**
     *  Test the fix function. Defaults to tabs to spaces
     */
    public function testFix() {
        $expected = str_replace( "\t", "    ", "This\tfile\n\tHas\n\t\tTabs" );

        $f1_name = 'mock-files/with-tabs.php';
        $f1 = new FixMyFiles( $f1_name );
        $f1_fix = $f1->fix();
        $this->assertEquals( array( realpath( $f1_name ) ), $f1_fix, "F1 fix didn't return fixed filename" );
        $this->assertEquals( $expected, file_get_contents( $f1_name ), "F1 file content don't match" );
    }

    /**
     *  Test the fix function when converting spaces to tabs
     */
    public function testFixSpaceToTab() {
        $expected = str_replace( "    ", "\t", "This    File\n    Has  Too\n\nMany    \n        Spaces" );
        $filename = realpath( 'mock-files/with-spaces.php' );
        $options = array( 'path' => $filename, 'spaceToTab' => true );
        $fixmyfiles = new FixMyFiles( $options );
        $fixed = $fixmyfiles->fix();

        $this->assertEquals( array( $filename ), $fixed, "F2 fix didn't return fixed filename" );
        $this->assertEquals( $expected, file_get_contents( $filename ), "F2 file content don't match" );
    }

    /**
     *  Test the fix function when converting to windows line endings
     */
    public function testFixWindowsEnding() {
        $expected = str_replace( "\n", "\r\n", "This file must\nhave Windows\nline endings\n\n" );
        $filename = realpath( 'mock-files/with-windows-endings.php' );
        $options = array( 'path' => $filename, 'toWindowsEnding' => true );
        $fixmyfiles = new FixMyFiles( $options );
        $fixed = $fixmyfiles->fix();

        $this->assertEquals( array( $filename ), $fixed, "F3 fix didn't return fixed filename" );
        $this->assertEquals( $expected, file_get_contents( $filename ), "F3 file content don't match" );
    }

    /**
     *  Test the fix function when converting to unix line endings
     */
    public function testFixUnixEnding() {
        $expected = "This file\nmust have Unix\nline endings\n\n";
        $filename = realpath( 'mock-files/with-unix-endings.php' );
        $options = array( 'path' => $filename );
        $fixmyfiles = new FixMyFiles( $options );
        $fixed = $fixmyfiles->fix();

        $this->assertEquals( array( $filename ), $fixed, "F4 fix didn't return fixed filename" );
        $this->assertEquals( $expected, file_get_contents( $filename ), "F4 file content don't match" );
    }
}
