<?php

/**
* Invalid variable error
*
* @const HTML_JAVASCRIPT_ERROR_INVVAR
*/
define('HTML_JAVASCRIPT_ERROR_INVVAR', 502, true);

require_once('PEAR.php');

/**
* A class for converting PHP variables into JavaScript variables
*
* Usage example:
*
* $js = new HTML_Javascript_Convert()
* $a = array('foo','bar','buz',1,2,3);
* $b = $js->convertArray($a, 'arr', true);
*
* TODO:
* -Add support for multidimensional arrays in convertArray()
*
* @author Tal Peer <tal@php.net>
* @package HTML_Javascript
* @version 0.9
* @access public
*/
class HTML_Javascript_Convert extends PEAR
{
    /**
    * Constructor - creates a new HTML_Javascript_Convert object
    *
    */
    function HTML_Javscript_Convert()
    {
        $this->PEAR();
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
}