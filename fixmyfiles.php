<?php
/**
 *    PHP Fix My Files
 *    @category    FileFormatter
 *    @package    FixMyFiles
 *    @author        Thapelo Moeti
 *    @license    MIT (http://url.com/)
 *    @version    1.0.0
 */

// 1. get file content
// 2. for each 4 spaces, replace with tab
// 3. for each windows line-ending replace with unix style
// 4. remove spaces at the end of the line


/**
 *    The FixMyFiles main class
 */
class FixMyFiles {
    
    /**
     *    The filename / directory to work with
     *
     *    @var array
     */
    private $_callList;
    
    /**
     *    Files to process
     *
     *    @var array
     */
    private $_files;
    
    /**
     *    Accepted file types to process
     *
     *    @var array
     */
    private $_fileTypes;
    
    /**
     *    The filename / directory to work with
     *
     *    @var string
     */
    private $_path;
        
    /**
     *    Number of spaces used to replace a tab
     *
     *    @var int
     */
    private $_spaces;

    /**
    *    Used to replace tabs to spaces
    *
    *    @param    mixed    $options    A string with a path, or an array with options
    */
    public function __construct( $options = null ) {
        if( is_string( $options ) )
            $options = array( 'path' => $options );
       
        if( ! is_array( $options ) )
            $options = array();
        
        $this->_fileTypes = array( 'php', 'html', 'js', 'css' );

        if( isset( $options[ 'fileTypes' ] ) && is_array( $options[ 'fileTypes' ] ) ) {
            $this->_fileTypes = $options[ 'fileTypes' ];
        }
        
        if( ! isset( $options[ 'path' ] ) )
            $options[ 'path' ] = getcwd();
        
        $realpath = realpath( $options[ 'path' ] );

        if( $realpath ) {
            $this->_path = $realpath;
            $this->_loadPath();
        } else {
            throw new Exception( "Invalid Path: {$options['path']}" );
        }

        $this->_callList = array();
        
        if( isset( $options[ 'toWindowsEnding' ] ) && $options[ 'toWindowsEnding' ] ) {
            $this->_callList[] = 'toWindowsEnding';
        } else {
            $this->_callList[] = 'toUnixEnding';
        }
        
        if( isset( $options[ 'spaceToTab' ] ) && $options[ 'spaceToTab' ] ) {
            $this->_callList[] = 'spaceToTab';
        } else {
            $this->_callList[] = 'tabToSpace';
        }

        if( isset( $options[ 'spaces' ] ) && is_int( $options[ 'spaces' ] ) ) {
            $this->_spaces = $options[ 'spaces' ];
        } else {
            $this->_spaces = 4;
        }
    }

    /**
     *    Runs the fuctions to fix the files
     *
     *    @return    array    $fixed    list of all files fixed
     */
    public function fix() {
        $fixed = array();

        foreach( $this->_files as $file ) {
            if( ! is_writable( $file ) )
                continue;

            $content = file_get_contents( $file );

            foreach( $this->_callList as $func )
                $content = call_user_func( array( $this, $func ), $content );

            if( file_put_contents( $file, $content ) ) {
                $fixed[] = $file;
            }
        }

        return $fixed;
    }

    /**
     *    Gets a property of the object. Because they are private
     *
     *    @param    string    $property    the name of the property to get
     *    @return    mixed    $value        null if property isn't set, or the value of the property
     */
    public function __get( $property ) {
        $property = "_{$property}";

        if( isset( $this->$property ) ) {
            return $this->$property;
        } else {
            trigger_error( "", E_USER_ERROR );
        }
    }

    /**
     *    Set new path (file/directory)
     *
     *    @param    string    $path    the pathname
     *    @return    bool    $value    true on success, false on failiure
     */
    public function setPath( $path ) {
        $realpath = realpath( $path );

        if( $realpath ) {
            $this->_path = $realpath;
            $this->_loadPath();

            return true;
        } else {
            throw new Exception( "setPath - Invalid Path: {$path}" );

            return false;
        }    
    }

    /**
    *    Used to replace tabs to spaces
    *
    *    @param    string    $text    The text to be formatted
    *    @param    int        $spaces    The number of spaces to use
    *    @return    string    $text    The formatted text
    */
    protected function tabToSpace( $text, $spaces = null ) {
        if( ! is_int( $spaces ) || $spaces < 1 )
            $spaces = $this->_spaces;

        $_spaces = str_repeat( " ", $spaces );

        $text = str_replace( "\t", $_spaces, $text );

        return $text;
    }

    /**
    *    Used to replace spaces to tab
    *
    *    @param    string    $text    The text to be formatted
    *    @param    int        $spaces    The number of spaces to use
    *    @return    string    $text    The formatted text
    */
    protected function spaceToTab( $text, $spaces = null ) {
        if( ! is_int( $spaces ) || $spaces < 1 )
            $spaces = $this->_spaces;

        $_spaces = str_repeat( " ", $spaces );

        $text = str_replace( $_spaces, "\t", $text );
        
        return $text;
    }

    /**
    *    Used to replace windows space to unix style
    *
    *    @param    string    $text    The text to be formatted
    *    @return    string    $text    The formatted text
    */
    protected function toUnixEnding( $text ) {
        $text = str_replace( "\r\n", "\n", $text );

        return $text;
    }

    /**
    *    Used to replace unix style to windows space
    *
    *    @param    string    $text    The text to be formatted
    *    @return    string    $text    The formatted text
    */
    protected function toWindowsEnding( $text ) {
        $text = str_replace( "\r", '', $text );
        $text = str_replace( "\n", "\r\n", $text );

        return $text;
    }

    /**
    *    gets the files of a certain directory
    *
    *    @return    array    $files        array with all the filenames
    */
    private function _loadPath() {
        $files = array();

        $filename = $this->_path;

        if( is_dir( $filename ) ) {
            $_temp_files = scandir( $filename );

            foreach( $_temp_files as $file ) {
                if( in_array( $file, array( ".", ".." ) ) )
                    continue;
                
                $options = get_object_vars( $this );
                
                $_options = array();

                foreach( $options as $k => $v ) {
                    $_options[ substr( $k, 1 ) ] = $v; // stripping the underscore
                }

                $_options[ 'path' ] = $filename . DIRECTORY_SEPARATOR . $file;
                
                $_temp = new FixMyFiles( $_options );

                $files = array_merge( $files, $_temp->files );
            }
        } else {
            $file_extension = pathinfo( $filename, PATHINFO_EXTENSION );

            if( in_array( $file_extension, $this->_fileTypes ) )
                $files[] =  $filename;
        }

        $this->_files = $files;

        return $files;
    }
}
