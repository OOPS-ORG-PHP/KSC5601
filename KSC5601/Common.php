<?php
/**
 *
 * KSC5601 패키지에서 PHP iconv / mbstring 확장을 사용하기 위한 클래스
 *
 * iconv / mbstring 지원 여부를 확인할 수 있고, 지원시에 iconv 나 mbstring
 * 중 지원되는 것을 자동으로 선택할 수 있도록 제공. iconv 와 mbstring 이
 * 동시에 지원될 경우, iconv 에게 우선권이 있음
 *
 * @category   Charset
 * @package    KSC5601_ext
 * @author     JoungKyun.Kim <http://oops.org>
 * @copyright  (c) 2009, JoungKyun.Kim
 * @license    Like BSD License
 * @version    CVS: $Id: Common.php,v 1.2 2009-03-16 16:48:53 oops Exp $
 * @link       ftp://mirror.oops.org/pub/oops/php/pear/KSC5601
 */

/**
 * For PHP internal iconv/mbstring support
 * 
 * This class support check of using iconv/mbstring method and iconv/mbstring
 * wrapper method.
 * @category   Charset
 * @package    KSC5601_ext
 * @author     JoungKyun.Kim <http://oops.org>
 * @copyright  (c) 2009, JoungKyun.Kim
 * @license    Like BSD License
 * @version    Release:
 */
class KSC5601_Common
{
	/**
	 * Check to enable iconv extension on this session.
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function is_iconv () {
		return extension_loaded ('iconv');
	}

	/**
	 * Check to enable mbstring extension on this session.
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function is_mbstring () {
		return extension_loaded ('mbstring');
	}

	/**
	 * Check to enable iconv or mbstring extension on this session.
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function is_extfunc () {
		if ( $this->is_iconv () === true || $this->is_mbstring () === true )
			return true;
		return false;
	}

	/**
	 * iconv/mbstring wrapper function
	 *
	 * If enable iconv, use iconv. If disable iconv and enable mbstring,
	 * use mbstring. If disable both iconv and mbstring, return false
	 * @access public
	 * @param string $from original charset
	 * @param string $to   converting charset
	 * @param string $str  source string
	 * @return false|string
	 */
	function extfunc ($from, $to, $str) {
		if ( $this->is_iconv () === true )
			return iconv ($from, $to, $str);

		if ( $this->is_mbstring () === true )
			return mb_convert_encoding ($str, $to, $from);

		return false;
	}
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
