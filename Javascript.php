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
// | Author: Tal Peer <tal@php.net>                                       |
// |                                                                      |
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

/**
* Invalid variable error
*
* @const HTML_JAVASCRIPT_ERROR_INVVAR
*/
define('HTML_JAVASCRIPT_ERROR_INVVAR', 502, true);

require_once('PEAR.php');

/**
* A class for converting PHP variables into Javascript variables
*
* Usage example:
*
* echo "<html><head><title>lala</title></head><body>";
* $js = new HTML_Javascript();
* echo $js->startScript();
* echo $js->convertString("Hello,\n My name is tal peer", 'foo', true);
* echo $js->writeLine('foo',true);
* $a = array('foo','bar','foobarism',1,98);
* echo $js->convertArray($a, 'bar', true);
* echo $js->writeLine('bar[0]', true);
* echo $js->writeLine('bar[3]', true);
* echo $js->endScript();
* echo "</body></html>";
*
* TODO:
* - Add support for multidimensional arrays in convertArray()
* - Add more JS operations (prompt, functions, interaction with forms)
* - Cleanup the API
*
* @author Tal Peer <tal@php.net>
* @package HTML
* @version 0.9-dev
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
    * Used to terminate escape characters in strings, as javascript doesn't allow them
    *
    * @param string $str the string to be processed
    * @return mixed the processed string
    * @access public
    */
    function escapeString($str)
    {
        $js_escape = array(
            "\r" => '\r',
            "\n" => '\n',
            "\t" => '\t',
            "'" => "\\'", 
            '\\' => '\\\\'
        );
        
        return strtr($str,$js_escape);
    }
    
    /**
    * Converts  a PHP string into a JS string
    *
    * @access public
    * @param  string  $str     the string to convert
    * @param  string  $varname the variable name to declare
    * @param  boolean $global  if true, the JS var will be global
    * @return mixed   a PEAR_Error if no script was started or the converted string
    */
    function convertString($str, $varname, $global = false)
    {
        if (!$this->_started) {
            return $this->raiseError(HTML_JAVASCRIPT_ERROR_NOSTART);
        }
        
        $var = '';
        $str = $this->escapeString($str);
        if($global) {
            $var = 'var ';
        }
        
        $var .= $varname.' = "'.$str.'"';
        return $var."\n";
    }
    
    /**
    * Converts  a PHP variable into a JS variable
    * Note: you can safely provide strings, arrays or booleans as arguments for this function
    *
    * @access public
    * @param  mixed   $var     the variable to convert
    * @param  string  $varname the variable name to declare
    * @param  boolean $global  if true, the JS var will be global
    * @return mixed   a PEAR_Error if no script was started or the converted variable
    */
    function convertVar($var, $varname, $global = false)
    {
        if (!$this->_started) {
            return $this->raiseError(HTML_JAVASCRIPT_ERROR_NOSTART);
        }
        
        if (!(is_string($var) OR is_array($var) OR is_bool($var))) {
            $ret = '';
            if ($global) {
                $ret = 'var ';
            }
            
            $ret .= $varname.' = '.$var;
            return $ret."\n";
        } else {
            if (is_array($var)) {
                return $this->convertArray($var, $varname, $global);
            } elseif (is_string($var)) {
                return $this->convertString($var, $varname, $global);
            } elseif (is_bool($var)) {
                return $this->convertBoolean($var, $varname, $global);
            } else {
                $this->raiseError(HTML_JAVASCRIPT_ERROR_INVVAR);
            }
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
            case HTML_JAVASCRIPT_ERROR_INVVAR:
                $ret = PEAR::raiseError('Invalid variable', HTML_JAVASCRIPT_ERROR_INVVAR);
                break;
            default:
                $ret = false;
                break;
        }
        
        return $ret;
    }

    /**
    * Converts a PHP boolean variable into a JS boolean variable
    *
    * @access public
    * @param  boolean $bool    the boolean variable
    * @param  string  $varname the variable name to declare
    * @param  boolean $global  set to true to make the JS variable global
    * @return mixed   a PEAR_Error on error or a string  with the declaration
    */
    function convertBoolean($bool, $varname, $global = false)
    {
        if (!$this->_started) {
            return $this->raiseError(HTML_JAVASCRIPT_ERROR_NOSTART);
        }
        
        $var = '';
        if($global) {
            $var = 'var ';
        }
        
        $var .= $varname.' = ';
        if ($bool) {
            $var .= 'true';
        } else {
            $var .= 'false';
        }
        return $var."\n";
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
    * Converts  a PHP array into a JS array
    * Note: support for multi-dimensional arrays is not yet implemented, DO NOT use them
    *
    * @access public
    * @param  string  $arr     the array to convert
    * @param  string  $varname the variable name to declare
    * @param  boolean $global  if true, the JS var will be global
    * @return mixed   a PEAR_Error if no script was started or the converted array
    */
    function convertArray($arr, $varname, $global = false)
    {
        if (!$this->_started) {
            return $this->raiseError(HTML_JAVASCRIPT_ERROR_NOSTART);
        }
        
        $var = '';
        if ($global) {
            $var = 'var ';
        }
        
        $var .= $varname.' = Array(';
        foreach ($arr as $key => $cell) {
            if ($key != 0) {
                $var .= ',';
            }
            if (is_string($cell)) {
                $cell = $this->escapeString($cell);
                
                $var .= '"'.$cell.'"';
            } else {
                $var .= $cell;
            }
            
        }
        $var .= ')';
        return $var."\n";
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
}
