<?php
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

/**
* Last script was not ended error
*
* @const HTML_JAVASCRIPT_ERROR_NOEND
*/
define('HTML_JAVASCRIPT_ERROR_NOEND', 501, true);



require_once('PEAR.php');

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
    
    /**
    * Constructor - creates a new HTML_Javascript object
    *
    * @access public
    */
    function HTML_Javascript()
    {
        $this->PEAR();
    }

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
            return $this->raiseError(HTML_JAVASCRIPT_ERROR_NOSTART);
        }
    }

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
                $ret = false;
                break;
        }

        return $ret;
    }

    /**
    * Starts a new script
    *
    * @access public
    * @return mixed a PEAR_Error if a script has been already started or a string (HTML tag <script language="javascript">)
    */
    function startScript()
    {
        if ($this->_started) {
            return $this->raiseError(HTML_JAVASCRIPT_ERROR_NOEND);
        } else {
            $this->_started = true;
            return "<script language=\"javascript\">\n";
        }
    }

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
        if ($this->_started) {
            if ($var) {
                return 'document.writeln('.$str.')'."\n";
            } else {
                return 'document.writeln("'.$str.'")'."\n";
            }
        } else {
            return $this->raiseError(HTML_JAVASCRIPT_ERROR_NOSTART);
        }
    }

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
        if ($this->_started) {
            if ($var) {
                return 'document.writeln('.$str.'+"<br />")'."\n";
            } else {
                return 'document.writeln("'.$str.'"+"<br />")'."\n";
            }
        } else {
            return $this->raiseError(HTML_JAVASCRIPT_ERROR_NOSTART);
        }
    }

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
        if ($this->_started) {
            if ($var) {
                return 'alert('.$str.')'."\n";
            } else {
                return 'alert("'.$str.'")'."\n";
            }
        } else {
            return $this->raiseError(HTML_JAVASCRIPT_ERROR_NOSTART);
        }
    }

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
        if ($this->_started) {
            if ($var) {
                return $assign.'=prompt('.$str.')'."\n";
            } else {
                return $assign.'=prompt("'.$str.'")'."\n";
            }
        } else {
            return $this->raiseError(HTML_JAVASCRIPT_ERROR_NOSTART);
        }
    }
}
