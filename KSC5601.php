<?php
/**
 *
 * KSC5601 / UTF8 문자셋 간의 변환 및 관리를 위한 기능을 제공
 *
 * KSC5601 pear package 는 UHC <-> UTF8 또는 UHC <-> UCS2 간의 문자셋
 * 변환을 지원을 한다. 또한 NCR code 변환을 지원하여, 웹상에서 KSX1001
 * 범위밖의 표현하지 못하는 한글 문자를 NCR code 로 출력이 가능하도록
 * 지원을 한다.
 *
 * Copyright (c) 2009, JoungKyun.Kim <http://oops.org>
 * 
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *     * Neither the name of the authors nor the names of its contributors
 *       may be used to endorse or promote products derived from this software
 *       without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS
 * BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   Charset
 * @package    KSC5601
 * @author     JoungKyun.Kim <http://oops.org>
 * @copyright  (c) 2009, JoungKyun.Kim
 * @license    Like BSD License
 * @version    CVS: $Id: KSC5601.php,v 1.6 2009-03-16 17:15:17 oops Exp $
 * @link       ftp://mirror.oops.org/pub/oops/php/pear/KSC5601
 * @since      File available since Release 0.1
 */

/**
 * PHP iconv/mbstring 확장 모듈 지원 여부를 확인 하기 위한 Check KSC5601_Common class
 */
require_once 'KSC5601/Common.php';

/**#@+
 * 지원하는 문자셋 상수
 */
/*
 * Local charset string
 */
define ('LOC',    'loc');
/**
 * UTF8 charset string
 */
define ('UTF8',   'utf8');
/**
 * EUC-KR charset string
 */
define ('EUC-KR', 'euc-kr');
/**
 * CP949 Alias
 */
define ('UHC',    'cp949');
/**
 * CP949 charset string
 */
define ('CP949',  'cp949');
/**
 * UCS2 big endian charset string
 */
define ('UCS2',   'ucs-2be');
/*
 * Numeric Code Reference string
 */
define ('NCR',    'ncr');
/**#@-*/

global $chk;

$chk = new KSC5601_Common;

if ( $chk->is_extfunc () === true ) {
	/**
	 * php iconv / mbstring 확장이 지원될 경우, iconv/mbsting API 를 이용하기 위한 KSC5601 class
	 */
	require_once 'KSC5601/KSC5601_ext.php';
} else {
	/**
	 * php iconv / mbstring 확장이 지원 되지 않을 경우, pure php API 를 이용하기 위한 KSC5601 class
	 */
	require_once 'KSC5601/KSC5601_pure.php';
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
