<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
/**
* A class for converting PHP variables into JavaScript variables
*
* Usage example:
*
* $js = new HTML_Javascript_Convert()
* $a = array('foo','bar','buz',1,2,3);
* $b = $js->convertArray($a, 'arr', true);
* or
* echo HTML_Javascript_Convert::convertArray($a);
*
* @author Tal Peer <tal@php.net>
* @package HTML_Javascript
* @version 0.9
* @access public
*/

/**
* Invalid variable error
*
* @const HTML_JAVASCRIPT_ERROR_INVVAR
*/
define('HTML_JAVASCRIPT_ERROR_INVVAR', 502, true);

require_once('PEAR.php');

class HTML_Javascript_Convert extends PEAR
{
    // {{{ HTML_Javscript_Convert
    /**
    * Constructor - creates a new HTML_Javascript_Convert object
    *
    */
    function HTML_Javscript_Convert()
    {
        $this->PEAR();
    }// }}} HTML_Javscript_Convert


    // {{{ escapeString
    /**
    * Used to terminate escape characters in strings, as javascript doesn't allow them
    *
    * @param string $str the string to be processed
    * @return mixed the processed string
    * @access public
    */
    function escapeString($str)
    {
        return addslashes($str);
    }// }}} escapeString


    // {{{ convertVar
    /**
    * Converts  a PHP variable into a JS variable
    * you can safely provide strings, arrays or booleans as arguments for this function
    *
    * @access public
    * @param  mixed   $var     the variable to convert
    * @param  string  $varname the variable name to declare
    * @param  boolean $global  if true, the JS var will be global
    * @return mixed   a PEAR_Error if no script was started or the converted variable
    */
    function convertVar($var, $varname, $global = false)
    {
        $var_type    = gettype($var);
        switch ( $var_type ) {
            case 'boolean':
                return HTML_Javascript_Convert::convertBoolean($var, $varname, $global);
                break;
            case 'integer':
                $ret = '';
                if ($global) {
                    $ret = 'var ';
                }
                $ret .= $varname.' = '.$var;
                return $ret."\n";
                break;
            case 'double':
                $ret = '';
                if ($global) {
                    $ret = 'var ';
                }
                $ret .= $varname.' = '.$var;
                return $ret."\n";
                break;
            case 'string':
                return HTML_Javascript_Convert::convertString($var, $varname, $global);
                break;
            case 'array':
                return HTML_Javascript_Convert::convertArray($var, $varname, $global);
                break;
            default:
                return HTML_Javascript_Convert::raiseError('Unsupported variable type', HTML_JAVASCRIPT_ERROR_INVVAR );
                break;
        }
    }// }}} convertVar


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
            case HTML_JAVASCRIPT_ERROR_INVVAR:
                $ret = PEAR::raiseError('Invalid variable', HTML_JAVASCRIPT_ERROR_INVVAR);
                break;
            default:
                $ret = PEAR::raiseError('Unknown Error', HTML_JAVASCRIPT_ERROR_INVVAR);
                break;
        }
        return $ret;
    }// }}} raiseError


    // {{{ convertString
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
        $var = '';
        if($global) {
            $var = 'var ';
        }
        $str = HTML_Javascript_Convert::escapeString($str);
        $var .= $varname.' = "'.$str.'"';
        return $var."\n";
    }// }}} convertString


    // {{{ convertBoolean
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
        $var = '';
        if($global) {
            $var = 'var ';
        }
        $var    .= $varname.' = ';
        $var    .= $bool?'true':'false';
        return $var."\n";
    }// }}} convertBoolean


    // {{{ convertArray()
    /**
    * Converts  a PHP array into a JS array, supports of multu-dimensional array.
    * Keeps keys as they are (associative arrays).
    *
    * @access public
    * @param  string  $arr     the array to convert
    * @param  string  $varname the variable name to declare
    * @param  boolean $global  if true, the JS var will be global
    * @param  int     $level   Not public, used for recursive calls
    * @return mixed   a PEAR_Error if no script was started or the converted array
    */
    function convertArray($arr, $varname, $global = false, $level=0)
    {
        $var = '';
        if ($global) {
            $var = 'var ';
        }
        if ( is_array($arr) ){
            $length = sizeof($arr);
            $var    .= $varname . ' = Array('. $length .")\n";
            foreach ( $arr as $key=>$cell ){
                $jskey  = is_int($key)?$key:'"' . $key . '"';
                if ( is_array( $cell ) ){
                    $level++;
                    $var    .= HTML_Javascript_Convert::convertArray( $cell,'tmp'.$level,$global,$level );
                    $var    .= $varname . "[$jskey] = tmp$level\n";
                    $var    .= "tmp$level = null\n";
                } else {
                    $value  = is_string($cell)?'"' . HTML_Javascript_Convert::escapeString($cell) . '"':$cell;
                    $var    .= $varname . "[$jskey] = $value\n";
                }
            }
            return $var;
        } else {
            return PEAR::raiseError('Invalid variable type, array expected', HTML_JAVASCRIPT_ERROR_INVVAR);
        }
    } // }}} convertArray
}
