<?php
/**
 *
 * KSC5601 �� php iconv / mbstring Ȯ���� �̿��Ͽ� ó���ϱ� ����
 * KSC5601 Class
 *
 * @category   Charset
 * @package    KSC5601_ext
 * @author     JoungKyun.Kim <http://oops.org>
 * @copyright  (c) 2009, JoungKyun.Kim
 * @license    Like BSD License
 * @version    CVS: $Id: KSC5601_ext.php,v 1.3 2009-03-17 09:33:24 oops Exp $
 * @link       ftp://mirror.oops.org/pub/oops/php/pear/KSC5601
 */

/**#@+
 * ���� extension mode ���� ����
 */
/*
 * ���� iconv / mbstring ��� ��� ����
 */
define ('EXTMODE',    true);
/**#@-*/


/**
 * UTF8 �� üũ�ϱ� ���� KCS5601::is_utf8 method ���� API include
 */
require_once 'KSC5601/UTF8.php';

/**
 * KSC5601 �� UTF-8 ���� ���ڼ� ��ȯ �� ������ ���� ��� ����
 *
 * @category	Charset
 * @package		KSC5601_ext
 * @author		JoungKyun.Kim <http://oops.org>
 * @copyright	2009 (c) JoungKyun.Kim
 * @license		BSD License
 * @version		Release:
 */
Class KSC5601
{
	private $obj;
	private $out_ksx1001 = false;

	function __construct () {
		$this->obj  = $GLOBALS['chk'];
	}

	/**
	 * KSX1001 ���� ���� �ѱ��� ó��
	 * @access	public
	 * @param	boolean	$flag
	 * 	<ol>
	 * 		<li>true : UTF8 decode �ÿ� KSX1001 �������� �ѱ��� NCR ó�� �Ѵ�.</li>
	 * 		<li>true : NCR encode �ÿ� KSX1001 ������ �ѱ۸� NCR ó�� �Ѵ�.</li>
	 * 		<li>false : �ƹ��ϵ� ���� �ʴ´�.</li>
	 * 	</ol>
	 * @return	void
	 */
	function out_of_ksx1001 ($flag = false) {
		$this->out_ksx1001 = $flag;
	}

	/**
	 * �־��� ���ڿ��� utf8 ���� üũ
	 * @param   string  $string     �˻��� ���ڿ�
	 * @return  boolean utf8 �� ��� true �� ��ȯ
	 * @access  public
	 */
	function is_utf8 ($string) {
		return KSC5601_UTF8::is_utf8 ($string);
	}

	/**
	 * UHC <-> UTF8 ���ڼ� ��ȯ
	 * @access	public
	 * @param	string	$string	��ȯ�� ���ڿ�
	 * 		������ ���� ������ �⺻���� UTF8 �� ���ڼ��� ��ȯ��.
	 * 		UTF8 �� �ƴ� ��� UHC(CP949) �� ��ȯ ��. KSC5601::out_of_ksx1001
	 * 		�� true �� ���, ���ڵ� �ÿ� KSX1001 ���� ���� �ѱ��� NCR ó��
	 * 		��. @see KSC5601::out_of_ksx1001
	 * @param	string	$to     ���ڵ�(UTF8)/���ڵ�(UHC) [�⺻: UTF8]
	 * @return  string
	 */
	function utf8 ($string, $to = UTF8) {
		if ( $to === UTF8 )
			$string = $this->ncr ($string, UHC);

		if ( preg_match ('/^utf[-]?8$/i', $to) ) {
			$to = UTF8;
			$from = UHC;
		} else {
			$to = UHC;
			$from = UTF8;
		}

		$r = $this->obj->extfunc ($from, $to, $string);

		if ( $to == UHC && $this->out_ksx1001 === true )
			$r = $this->ncr ($r);

		return $r;
	}

	/**
	 * UHC <-> UCS2 ���ڼ� ��ȯ
	 * @access	public
	 * @param	string	$string	��ȯ�� ���ڿ�
	 * 		������ ���� ������ �⺻���� UCS2 hexical �� ���ڼ��� ��ȯ��.
	 * 		UCS2 �� �ƴ� ��� UCS2 hexical �� UHC(CP949) �� ��ȯ ��.
	 * @param	string	$to     ���ڵ�(UCS2)/���ڵ�(UHC) [�⺻: UCS2]
	 * @param	boolean $asc    true ���, ��� ���ڸ� UCS2 hexical �� ��ȯ
	 *                          false �� ��� KSX1001 ���� ���� �ѱ۸� UCS hexical �� ��ȯ
	 *                          ���ڵ� �ÿ��� ������� ����
	 *                          �⺻�� false
	 * @return  string
	 */
	function ucs2 ($string, $to = UCS2, $asc = false) {
		if ( preg_match ('/ucs[-]?2(be|le)?/i', $to) ) {
			/* to ucs2 */
			return $this->ucs2enc ($string, $asc);
		} else {
			/* to UHC */
			return $this->ucs2dec ($string);
		}
	}

	private function ucs2enc ($string, $asc = false) {
		$string = $this->obj->extfunc (UHC, UCS2, $string);
		$l = strlen ($string);

		for ( $i=0; $i<$l; $i++ ) {
			if ( ord ($string[$i]) == 0 ) {
				/* ascii area */
				$r .= ( $asc === false ) ?
					$string[$i+1] :
					'U+' . KSC5601_Stream::chr2hex ($string[$i+1], false);
			} else {
				$r .= 'U+' .
					KSC5601_Stream::chr2hex ($string[$i], false) .
					KSC5601_Stream::chr2hex ($string[$i+1], false);
			}
			$i++;
		}

		return $r;
	}

	private function ucs2dec ($string) {
		$s = preg_replace ('/0x([a-z0-9]{2,4})/i', 'U+\\1', trim ($string));
		$r = preg_replace_callback ('/U\+([[:alnum:]]{2})([[:alnum:]]{2})?/',
				create_function ('$matches', "
					if ( \$matches[2] )
						\$r = chr (hexdec (\$matches[1])) . chr (hexdec (\$matches[2]));
					else
						\$r = chr (0) . chr (hexdec (\$matches[1]));

					if ( extension_loaded ('iconv') )
						return iconv (UCS2, UHC, \$r);
					else if ( extension_loaded ('mbstring') )
						return mb_convert_encoding (\$r, UHC, UCS2);
					return false;
				"),
				$s
			);

		return $r;
	}

	/**
	 * UHC <-> NCR (Numeric Code Reference) ���ڼ� ��ȯ
	 * @access	public
	 * @param	string	$string	��ȯ�� ���ڿ�
	 * 	<p>
	 * 		������ ���� ������ �⺻���� NCR code �� ���ڼ��� ��ȯ��.
	 * 		NCR �� �ƴ� ��� NCR code �� UHC(CP949) �� ��ȯ ��.
	 * 	</p>
	 * @param	string	$to     ���ڵ�(NCR)/���ڵ�(UHC) [�⺻: NCR]
	 * @param	boolean $enc    true ���, ��� ���ڸ� NCR code �� ��ȯ
	 *                          false �� ��� KSC5601::out_of_ksx1001 �� ������ true �� ���
	 *                          KSX1001 ���� ���� �ѱ۸� NCR �� ��ȯ�ϸ�, false �� ��� UHC
	 *                          ��� ������ NCR�� ��ȯ. ���ڵ� �ÿ��� ������� ����
	 *                          �⺻�� false
	 * @return  string
	 */
	function ncr ($string, $to = NCR, $enc = false) {
		if ( $to == NCR ) {
			/* to ucs2 */
			return $this->ncr2enc ($string, $enc);
		} else {
			/* to UHC */
			return $this->ncr2dec ($string);
		}
	}

	private function ncr2enc ($string, $enc = false) {
		if ( $enc === false ) {
			$l = strlen ($string);

			for ( $i=0; $i<$l; $i++ ) {
				$c1 = ord ($string[$i]);
				if ( ! ($c1 & 0x80) ) {
					$r .= $string[$i];
					continue;
				}
				$i++;

				if ( $this->out_ksx1001 === true ) {
					if ( KSC5601_Stream::is_out_of_ksx1001 ($string[$i-1], $string[$i]) ) {
						$u = $this->obj->extfunc (UHC, UCS2, $string[$i-1] . $string[$i]);
						$r .= '&#x' .
							KSC5601_Stream::chr2hex ($u[0], false) .
							KSC5601_Stream::chr2hex ($u[1], false) . ';';
					} else
						$r .= $string[$i-1] . $string[$i];
				} else {
					$u = $this->obj->extfunc (UHC, UCS2, $string[$i-1] . $string[$i]);
					$r .= '&#x' .
						KSC5601_Stream::chr2hex ($u[0], false) .
						KSC5601_Stream::chr2hex ($u[1], false) . ';';
				}
			}
			return $r;
		}

		$string = $this->obj->extfunc (UHC, UCS2, $string);
		$l = strlen ($string);

		for ( $i=0; $i<$l; $i++ ) {
			if ( ord ($string[$i]) == 0 ) {
				 $r .= '&#x' . KSC5601_Stream::chr2hex ($string[$i+1], false) . ';';
			} else {
				$r .= '&#x' .
					KSC5601_Stream::chr2hex ($string[$i], false) .
					KSC5601_Stream::chr2hex ($string[$i+1], false) . ';';
			}
			$i++;
		}

		return $r;
	}

	private function ncr2dec ($string) {
		$r = preg_replace_callback (
				'/&#([[:alnum:]]+);/',
				create_function ('$m', "
					\$m[1] = ( \$m[1][0] == 'x' ) ?  substr (\$m[1], 1) : dechex (\$m[1]);

					if ( strlen (\$m[1]) % 2 )
						\$m[1] = '0' . \$m[1];

					preg_match ('/^([[:alnum:]]{2})([[:alnum:]]{2})?$/', \$m[1], \$matches);

					\$n = chr (hexdec (\$matches[1]));
					if ( \$matches[2] ) {
						\$n .= chr (hexdec (\$matches[2]));

						if ( extension_loaded ('iconv') )
							return iconv ('ucs-2be', 'uhc', \$n);
						else if ( extension_loaded ('mbstring') )
							return mb_convert_encoding (\$n, 'uhc', 'ucs-2be');

						return false;
					}

					/* little endian */
					\$n .= chr (0);

					if ( extension_loaded ('iconv') )
						return iconv ('ucs-2', 'uhc', \$n);
					else if ( extension_loaded ('mbstring') )
						return mb_convert_encoding (\$n, 'uhc', 'ucs-2');
					return false;
				"),
				$string
			);

		return $r;
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
