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
 * @copyright ::  {@link https://xoops.org/ XOOPS Project}
 * @license   ::    {@link http://www.fsf.org/copyleft/gpl.html GNU public license}
 * @package   ::    xoopspoll
 * @subpackage:: admin
 * @since     ::      1.32
 * @author    ::     XOOPS Module Team
 *
 * @uses      $GLOBALS['xoops']::path read folder information about XOOPS
 * @uses      $GLOBALS['xoopsModule'] reads module specific information
 * @uses      xoops_load() method to dynamically load class for use
 * @uses      xoops_getHandler() function to load the module handler
 * @uses      xoops_loadLanguage() loads the module language defines
 * @uses      MyTextSanitizer
 */

/** {@internal the following 2 file includes aren't
 * required as they are done by cp_header.php}
 */

use XoopsModules\Xoopspoll;

require_once __DIR__ . '/../../../include/cp_header.php';

$moduleDirName = basename(dirname(__DIR__));
$helper = Xoopspoll\Helper::getInstance();
$adminObject = \Xmf\Module\Admin::getInstance();

$pathIcon16    = \Xmf\Module\Admin::iconUrl('', 16);
$pathIcon32    = \Xmf\Module\Admin::iconUrl('', 32);
$pathModIcon32 = $helper->getModule()->getInfo('modicons32');

// Load language files
$helper->loadLanguage('admin');
$helper->loadLanguage('modinfo');
$helper->loadLanguage('main');

$myts = \MyTextSanitizer::getInstance();

if (!isset($GLOBALS['xoopsTpl']) || !($GLOBALS['xoopsTpl'] instanceof XoopsTpl)) {
    require_once $GLOBALS['xoops']->path('class/template.php');
    $xoopsTpl = new \XoopsTpl();
}
