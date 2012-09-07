<?php
/**
 * For PHP internal iconv/mbstring support on KSC5601 package
 *
 * Check iconv or mbstring support, and auto select iconv or mbstring api.
 * If support both iconv and mbstring extensions, first iconv.
 *
 * @category   Charset
 * @package    KSC5601
 * @subpackage KSC5601_Common
 * @author     JoungKyun.Kim <http://oops.org>
 * @copyright  (c) 2009, JoungKyun.Kim
 * @license    BSD License
 * @version    $Id$
 * @link       http://pear.oops.org/package/KSC5601
 * @filesource
 */

/**
 * For PHP internal iconv/mbstring support
 * 
 * This class support check of using iconv/mbstring method and iconv/mbstring
 * wrapper method.
 *
 * @package KSC5601
 */
class KSC5601_Common
{
	// {{{ function is_iconv ()
	/**
	 * Check to enable iconv extension on this session.
	 *
	 * @access public
	 * @return boolean
	 * @param void
	 * @see extension_loaded
	 */
	function is_iconv () {
		return extension_loaded ('iconv');
	}
	// }}}

	// {{{ function is_mbstring ()
	/**
	 * Check to enable mbstring extension on this session.
	 *
	 * @access public
	 * @return boolean
	 * @param void
	 * @see extension_loaded
	 */
	function is_mbstring () {
		return extension_loaded ('mbstring');
	}
	// }}}

	// {{{ function is_extfunc ()
	/**
	 * Check to enable iconv or mbstring extension on this session.
	 *
	 * @access public
	 * @return boolean If support to iconv or mbstring, return true
	 * @param void
	 */
	function is_extfunc () {
		if ( $this->is_iconv () === true || $this->is_mbstring () === true )
			return true;
		return false;
	}
	// }}}

	// {{{ function extfunc ($from, $to, $str)
	/**
	 * iconv/mbstring wrapper function
	 *
	 * If enable iconv, use iconv. If disable iconv and enable mbstring,
	 * use mbstring. If disable both iconv and mbstring, return false
	 * @access public
	 * @return false|string Return false when don't support both iconv and mbstring
	 * @param string $from  From charset
	 * @param string $to    To charset
	 * @param string $str   Given strings.
	 */
	function extfunc ($from, $to, $str) {
		if ( $this->is_iconv () === true )
			return iconv ($from, $to, $str);

		if ( $this->is_mbstring () === true )
			return mb_convert_encoding ($str, $to, $from);

		return false;
	}
	// }}}
}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: noet sw=4 ts=4 fdm=marker
 * vim<600: noet sw=4 ts=4
 */
?>
