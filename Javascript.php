<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2002 The PHP Group                                |
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
// |          Pierre-Alain Joye <paj@pearfr.org>                                                            |
// +----------------------------------------------------------------------+


//Error codes

/**
* No script started error
*
* @const HTML_JAVASCRIPT_ERROR_NOSTART
*/
define('HTML_JAVASCRIPT_ERROR_NOSTART', 500, true);
define('HTML_JAVASCRIPT_ERROR_UNKNOWN', 599, true);
/**
* Last script was not ended error
*
* @const HTML_JAVASCRIPT_ERROR_NOEND
*/
define('HTML_JAVASCRIPT_ERROR_NOEND', 501, true);

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
* - Add more JS operations (prompt, functions, interaction with forms)
* - API cleanups...
*
* @author Tal Peer <tal@php.net>
* @author Pierre-Alain Joye <paj@pearfr.org>
* @package HTML_Javascript
* @version 0.9
* @access public
*/
class HTML_Javascript extends PEAR
{
    /**
    * Used to determaine if a script has been started
    *
    * @var boolean $_started
    * @access private
    */
    var $_started = false;

    // {{{ HTML_Javascript
    /**
    * Constructor - creates a new HTML_Javascript object
    *
    * @access public
    */
    function HTML_Javascript()
    {
        $this->PEAR();
    }// }}} HTML_Javascript


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
            default:
                return HTML_Javascript_Convert::raiseError('Unknown Error', HTML_JAVASCRIPT_ERROR_UNKNOWN);
                break;
                break;
        }

        return $ret;
    }// }}} raiseError


    // {{{ startScript
    /**
    * Starts a new script
    *
    * @param  string  $version Version of Javascript to used (default is 1.3)
    * @access public
    * @return mixed a PEAR_Error if a script has been already started or a string (HTML tag <script language="javascript">)
    */
    function startScript( $version='1.3' )
    {
        $this->_started = true;
        return "<script language=\"javascript$version\">\n";
    } // }}} startScript


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
            return "</script>\n";
        } else {
            return HTML_Javascript::raiseError(HTML_JAVASCRIPT_ERROR_NOSTART);
        }
    } // }}} endScript


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
            return 'document.writeln('.$str.')'."\n";
        } else {
            return 'document.writeln("'.HTML_Javascript_Convert::escapeString($str).'")'."\n";
        }
    }// }}} write


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
            return 'document.writeln('.$str.'+"<br />")'."\n";
        } else {
            return 'document.writeln("'.HTML_Javascript_Convert::escapeString($str).'"+"<br />")'."\n";
        }
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
        return $alert.')'."\n";
    } // {{{ alert


    // {{{ prompt
    /**
    * Opens a propmt (input box)
    *
    * @param  string $str    the string that will appear in the prompt
    * @param  string $assign the JS var that the input will be assigned to
    * @param  string $var    wether $str is a JS var or not
    * @return mixed  PEAR_Error or the processed string
    */
    function prompt($str, $assign, $var = false)
    {
        if ($var) {
            $prompt = 'prompt('.$str.')'."\n";
        } else {

            $prompt = 'prompt("'.HTML_Javascript_Convert::escapeString($str).'")'."\n";
        }
        return $assign .' = ' . $prompt;
    }// }}} prompt
}
