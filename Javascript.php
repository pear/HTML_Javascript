<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Tal Peer <tal@php.net>                                      |
// |          Pierre-Alain Joye <paj@pearfr.org>                          |
// +----------------------------------------------------------------------+
// $Id$

//Error codes

/**
* No script started error
*
* @const HTML_JAVASCRIPT_ERROR_NOSTART
*/
define('HTML_JAVASCRIPT_ERROR_NOSTART', 500, true);

/**
* Unknown error
*
* @const HTML_JAVASCRIPT_ERROR_UNKOWN
*/
define('HTML_JAVASCRIPT_ERROR_UNKNOWN', 599, true);

/**
* Last script was not ended error
*
* @const HTML_JAVASCRIPT_ERROR_NOEND
*/
define('HTML_JAVASCRIPT_ERROR_NOEND', 501, true);

/**
* No file was specified for setOutputMode()
*
* @const HTML_JAVASCRIPT_ERROR_NOFILE
*/
define('HTML_JAVASCRIPT_ERROR_NOFILE', 505, true);

//Output modes
/**
* Just return the results (default mode)
*
* @const HTML_JAVASCRIPT_OUTPUT_RETURN
*/
define('HTML_JAVASCRIPT_OUTPUT_RETURN', 0);

/**
* Echo (print) the results directly to browser
*
* @const HTML_JAVASCRIPT_OUTPUT_ECHO
*/
define('HTML_JAVASCRIPT_OUTPUT_ECHO', 1);

/**
* Print the results to a file
*
* @const HTML_JAVASCRIPT_OUTPUT_FILE
*/
define('HTML_JAVASCRIPT_OUTPUT_FILE', 2);

require_once('PEAR.php');
require_once('HTML/Javascript/Convert.php');

/**
* A class for performing basic JavaScript operations
*
* Usage example:
*
* echo "<html><head><title>lala</title></head><body>";
* $js = new HTML_Javascript();
* echo $js->startScript();
* echo $js->writeLine('foo',false);
* echo $js->writeLine('bar[0]', true);
* echo $js->writeLine('bar[3]', true);
* echo $js->endScript();
* echo "</body></html>";
*
* TODO:
* - Error handler
* - Validation mechanism
* - Rollovers
* - Importation from external files
* - Themed popups
*
* @author  Tal Peer <tal@php.net>
* @author  Pierre-Alain Joye <paj@pearfr.org>
* @package HTML_Javascript
* @version 1.0.0
* @access  public
*/
class HTML_Javascript extends PEAR
{
    /**
    * Used to determaine if a script has been started
    *
    * @var    boolean $_started
    * @access private
    */
    var $_started = false;

    /**
    * The output mode specified for the script
    *
    * @var    integer $_mode
    * @access private
    */
    var $_mode = HTML_JAVASCRIPT_OUTPUT_RETURN;

    /**
    * The file to direct the output to
    *
    * @var    string $_file
    * @access private
    */
    var $_file = '';

    // {{{ HTML_Javascript
    /**
    * Constructor - creates a new HTML_Javascript object
    *
    * @access public
    */
    function HTML_Javascript()
    {
        $this->PEAR();
    }

    // }}} HTML_Javascript
    // {{{ setOutputMode

    /**
    * Set the output mode for the script
    *
    * @param  integer $mode the chosen output mode, can be either HTML_JAVASCRIPT_OUTPUT_RETURN, HTML_JAVASCRIPT_OUTPUT_ECHO or HTML_JAVASCRIPT_OUTPUT_FILE
    * @param  string  $file the path to the file (if $mode is HTML_JAVASCRIPT_OUTPUT_FILE)
    * @access public
    * @return mixed   PEAR_Error or true
    */
    function setOutputMode($mode = HTML_JAVASCRIPT_OUTPUT_RETURN, $file = NULL)
    {
        if($mode == HTML_JAVASCRIPT_OUTPUT_FILE ) {
            if(isset($file)) {
                $this->_file = $file;
            } else {
                $this->raiseError(HTML_JAVASCRIPT_ERROR_NOFILE);
            }
        }
        $this->_mode = $mode;
        return true;
    }

    // }}} setOutputMode
    // {{{ raiseError

    /**
    * A custom error handler
    *
    * @access public
    * @param  integer $code the error code
    * @return mixed   false if the error code is invalid, or a PEAR_Error otherwise
    */
    function raiseError($code)
    {
        $ret = null;

        switch ($code) {
            case HTML_JAVASCRIPT_ERROR_NOSTART:
                $ret = PEAR::raiseError('No script started', HTML_JAVASCRIPT_ERROR_NOSTART);
                break;
            case HTML_JAVASCRIPT_ERROR_NOEND:
                $ret = PEAR::raiseError('Last script was not ended', HTML_JAVASCRIPT_ERROR_NOEND);
                break;
            case HTML_JAVASCRIPT_ERROR_NOFILE:
                $ret = PEAR::raiseError('A filename must be specified for setoutputMode()', HTML_JAVASCRIPT_ERROR_NOFILE);
                break;
            default:
                return HTML_Javascript_Convert::raiseError('Unknown Error', HTML_JAVASCRIPT_ERROR_UNKNOWN);
                break;
        }

        return $ret;
    }

    // }}} raiseError
    // {{{ startScript

    /**
    * Starts a new script
    *
    * @param  bool   $defer whether to wait for the whole page to load before starting the script or no
    * @access public
    * @return mixed  a PEAR_Error if a script has been already started or a string (HTML tag <script>)
    */
    function startScript($defer = true)
    {
        $this->_started = true;
        $s      = $defer ? 'defer="defer"' : '';
        $ret    = "<script type=\"text/javascript\" ".$s.">\n";
        return $ret;
    }

    // }}} startScript
    // {{{ endScript

    /**
    * Used to end the script (</script>)
    *
    * @return mixed PEAR_Error if no script has been started or the end tag for the script
    * @access public
    */
    function endScript()
    {
        if ($this->_started) {
            $this->_started = false;
            $ret =  "</script>\n";
        } else {
            $ret =  HTML_Javascript::raiseError(HTML_JAVASCRIPT_ERROR_NOSTART);
        }
        return $ret;
    }

    // }}} endScript
    //{{{ _out

    /**
    * Checks the output mode and acts according to it
    *
    * @param  string  $str the string returned from the calling function
    * @return mixed   depends on the output mode, $str if it's HTML_JAVASCRIPT_OUTPUT_RETURN, true otherwise
    * @access private
    */
    function _out($str)
    {
        static $fp;
        if( isset($this) ){
            $mode = $this->_mode;
            $file = $this->_file;
        } else {
            return $str;
        }
        switch($mode) {
            case HTML_JAVASCRIPT_OUTPUT_RETURN: {
                return $str;
                break;
            }

            case HTML_JAVASCRIPT_OUTPUT_ECHO: {
                echo $str;
                return true;
                break;
            }

            case HTML_JAVASCRIPT_OUTPUT_FILE: {
                $fp = fopen($file, 'ab');
                fwrite($fp, $str);
                return true;
                break;
            }
            default: {
                PEAR::raiseError('Invalid output mode');
                break;
            }
        }
    }

    // }}} _out
    // {{{ write
    /**
    * A wrapper for document.writeln
    *
    * @access public
    * @param  string  $str the string to output
    * @param  boolean $var set to true if $str is a variable name
    * @return mixed   PEAR_Error if no script was started or the processed string
    */
    function write($str, $var = false)
    {
        if ($var) {
            $ret = HTML_Javascript::_out('document.writeln('.$str.')'."\n");
        } else {
            $ret = HTML_Javascript::_out('document.writeln("'.HTML_Javascript_Convert::escapeString($str).'")'."\n");
        }
        return $ret;
    }

    // }}} write
    // {{{ writeLine

    /**
    * A wrapper for document.writeln with an addtional <br /> tag
    *
    * @access public
    * @param  string  $str the string to output
    * @param  boolean $var set to true if $str is a variable name
    * @return mixed   PEAR_Error if no script was started or the processed string
    */
    function writeLine($str, $var = false)
    {
        if ($var) {
            $ret = HTML_Javascript::_out('document.writeln('.$str.'+"<br />")'."\n");
        } else {
            $ret = HTML_Javascript::_out('document.writeln("'.HTML_Javascript_Convert::escapeString($str).'"+"<br />")'."\n");
        }
        return $ret;
    }// }}} writeLine


    // {{{ alert
    /**
    * A wrapper for alert
    *
    * @access public
    * @param  string  $str the string to output
    * @param  boolean $var set to true if $str is a variable name
    * @return mixed   PEAR_Error if no script was started or the processed string
    */
    function alert($str, $var = false)
    {
        $alert  = 'alert(';
        $alert  .= $var?$str:'"' . HTML_Javascript_Convert::escapeString($str) . '"';
        $ret = HTML_Javascript::_out($alert.')'."\n");
        return $ret;
    }

    // {{{ alert
    // {{{ prompt

    /**
    * Opens a propmt (input box)
    *
    * @param  string $str     the string that will appear in the prompt
    * @param  string $assign  the JS var that the input will be assigned to
    * @paeam  string $default the default value
    * @param  string $var     wether $str is a JS var or not
    * @return mixed  PEAR_Error or the processed string
    */
    function prompt($str, $assign, $default = '', $var = false)
    {
        if ($var) {
            $prompt = 'prompt('.$str.', "'.$default.')"'."\n";
        } else {

            $prompt = 'prompt("'.HTML_Javascript_Convert::escapeString($str).'", "'.$default.'")'."\n";
        }
        $ret = HTML_Javascript::_out($assign .' = ' . $prompt);
        return $ret;
    }

    // }}} prompt
    // {{{ popup

    /**
    * A method for easy generation of popup windows
    *
    * @param  string $assign the JS var to assign the window to
    * @param  string $file   the file that will appear in the new window
    * @paeam  string $title  the title of the new window
    * @param  int    $width  the width of the window
    * @param  int    $height the height of the window
    * @param  mixed  $attr   an array containing the attributes for the new window, each cell can contain either the ints 1/0 or the strings 'yes'/'no'. the order of attributes: resizable, scrollbars, menubar, toolbar, status, location. can be also a boolean, and then all the attributes are set to yes or no, according to the boolean value.
    * @param  int   $top    the distance from the top, in pixels.
    * @param  int   $left   the distance from the left, in pixels.
    * @return mixed PEAR_Error on error or the processed string.
    */
    function popup($assign, $file, $title, $width, $height, $attr, $top = 300, $left = 300)
    {
        if(!is_array($attr)) {
            if(!is_bool($attr)) {
                PEAR::raiseError('$attr should be either an array or a boolean');
            } else {
                if($attr) {
                    $attr = array('yes', 'yes', 'yes', 'yes', 'yes', 'yes', $top, $left);
                } else {
                    $attr = array('no', 'no', 'no', 'no', 'no', 'no', $top, $height);
                }
            }
        }
        $ret = HTML_Javascript::_out($assign . "= window.open(\"$file\", \"$title\", \"width=$width, height=$height, resizable=$attr[0], scrollbars=$attr[1], menubar=$attr[2], toolbar=$attr[3], status=$attr[4], location=$attr[5], top=$attr[6], left=$attr[7]\")\n");
        return $ret;
    }

    // }}} popup
    // {{{ popupWrite

    /**
    * Creates a new popup window containing a string. Inside the popup windows
    * you can access the opener window with the opener var.
    *
    * @param  string $assign the JS variable to assign the window to
    * @param  string $str    the string that will appear in the new window (HTML tags would be parsed by the browser, of course)
    * @param  string $title  the title of the window
    * @param  int    $width  the width of the window
    * @param  int    $height the height of the window
    * @param  mixed  $attr   see popup()
    * @param  int    $top    distance from the top (in pixels
    * @param  int    $left   distance from the left (in pixels)
    * @see    popup()
    * @return the processed string
    */
    function popupWrite($assign, $str, $title, $width, $height, $attr, $top = 300, $left = 300)
    {
        static  $cnt_popup;
        $str        = HTML_Javascript_Convert::escapeString($str);
        $assign     = strlen($assign)==0?'pearpopup'.$cnt_popup++:$assign;

        if($attr) {
            $attr = array('yes', 'yes', 'yes', 'yes', 'yes', 'yes', $top, $left);
        } else {
            $attr = array('no', 'no', 'no', 'no', 'no', 'no', $top, $height);
        }

        $windows = $assign . "= window.open(\"\", \"$title\", \"width=$width, height=$height, resizable=$attr[0], scrollbars=$attr[1], menubar=$attr[2], toolbar=$attr[3], status=$attr[4], location=$attr[5], top=$attr[6], left=$attr[7]\")\n";

        $windows    .= "
                        if ($assign){
                            $assign.focus();
                            $assign.document.open();
                            $assign.document.write('$str');
                            $assign.document.close();
                            if ($assign.opener == null) $assign.opener = self;
                        }
                      ";

        $ret = HTML_Javascript::_out($windows);
        return $ret;
    }

    // }}} popupWrite
    // {{{ confirm

    /**
    * Creates a box with yes and no buttons
    *
    * @param  string $assign the JS variable to assign the confirmation box to
    * @param  string $str    the string that will appear in the confirmation box
    * @param  bool   $var    whether $str is a JS var or not
    * @return string the processed string
    */
    function confirm($assign, $str, $var = false)
    {
        if($var) {
            $confirm = 'confirm(' . $str . ')' . "\n";
        } else {
            $confirm = 'confirm("' . HTML_Javascript_Convert::escapeString($str) . '")' . "\n";
        }
        $ret = HTML_Javascript::_out($assign . ' = ' . $confirm);
        return $ret;
    }
    // }}} confirm
}
?>