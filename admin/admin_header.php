<?php
/*
 You may not change or alter any portion of this comment or credits
 of supporting developers from this source code or any supporting source code
 which is considered copyrighted (c) material of the original comment or credit authors.
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*/
/**
 * XOOPS Poll module
 *
 * @copyright::  {@link http://xoops.org/ XOOPS Project}
 * @license::    {@link http://www.fsf.org/copyleft/gpl.html GNU public license}
 * @package::    xoopspoll
 * @subpackage:: admin
 * @since::      1.32
 * @author::     XOOPS Module Team
 * @version::    $Id $
 *
 * @uses $GLOBALS['xoops']::path read folder information about XOOPS
 * @uses $GLOBALS['xoopsModule'] reads module specific information
 * @uses xoops_load() method to dynamically load class for use
 * @uses xoops_gethandler() function to load the module handler
 * @uses xoops_loadLanguage() loads the module language defines
 * @uses MyTextSanitizer
 */

/** {@internal the following 2 file includes aren't
 * required as they are done by cp_header.php}
 */

$path = dirname(dirname(dirname(__DIR__)));
require_once $path . '/include/cp_header.php';

/** Load language files
 * {@internal the following load is not needed, it's done in ./include/cp_header.php} */
//xoops_loadLanguage('admin', 'xoopspoll');
xoops_loadLanguage('modinfo', 'xoopspoll');
xoops_loadLanguage('main', 'xoopspoll');
xoops_load('constants', 'xoopspoll');

$pathIcon16      = '../' . $GLOBALS['xoopsModule']->getInfo('icons16');
$pathIcon32      = '../' . $GLOBALS['xoopsModule']->getInfo('icons32');
$pathModuleAdmin = $GLOBALS['xoopsModule']->getInfo('dirmoduleadmin');

// technically this isn't needed if only supporting XOOPS >= 2.5.5+
//if (file_exists($GLOBALS['xoops']->path($pathModuleAdmin . "/moduleadmin.php"))) {
include_once $GLOBALS['xoops']->path($pathModuleAdmin . '/moduleadmin.php');
//} else {
//    redirect_header($GLOBALS['xoops']->path('admin.php'), XoopspollConstants::REDIRECT_DELAY_LONG, _AM_XOOPSPOLL_ADMIN_MISSING, false);
//}

$myts =& MyTextSanitizer::getInstance();

/*
if (!isset($GLOBALS['xoopsTpl']) || !$GLOBALS['xoopsTpl'] instanceof XoopsTpl) {
    include_once $GLOBALS['xoops']->path("class" . "/template.php");
    $GLOBALS['xoopsTpl'] = new XoopsTpl();
}
*/
