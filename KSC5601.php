<?php
/**
 * Copyright (c) 2008, JoungKyun.Kim <http://oops.org>
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
 * @category   pear
 * @package    Character Set
 * @author     JoungKyun.Kim <http://oops.org>
 * @copyright  (c) 2009, JoungKyun.Kim
 * @license    Like BSD License
 * @version    CVS: $Id: KSC5601.php,v 1.4 2009-03-16 12:04:39 oops Exp $
 * @link       ftp://mirror.oops.org/pub/oops/php/pear
 * @since      File available since Release 0.1
 * $Id: KSC5601.php,v 1.4 2009-03-16 12:04:39 oops Exp $
 */

require_once 'KSC5601/Common.php';
define ('LOC',    'loc');
define ('UTF8',   'utf8');
define ('EUC-KR', 'euc-kr');
define ('UHC',    'cp949');
define ('CP949',  'cp949');
define ('UCS2',   'ucs-2be');
define ('NCR',    'ncr');

global $chk;
$chk = new KSC5601_Common;

if ( $chk->is_extfunc () === true )
	require_once 'KSC5601/KSC5601_ext.php';
else
	require_once 'KSC5601/KSC5601_pure.php';


/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: noet sw=4 ts=4 fdm=marker
 * vim<600: noet sw=4 ts=4
 */
?>
