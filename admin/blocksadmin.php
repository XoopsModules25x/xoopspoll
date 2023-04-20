<?php declare(strict_types=1);

/**
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 *
 *
 * @category        Module
 * @author          XOOPS Development Team
 * @copyright       XOOPS Project
 * @link            https://xoops.org
 * @license         GNU GPL 2.0 or later (https://www.gnu.org/licenses/gpl-2.0.html)
 */

use Xmf\Module\Admin;
use Xmf\Request;
use XoopsModules\Xoopspoll\{
    Common\Blocksadmin,
    Common\BlockActionsHandler,
    Helper
};

/** @var Admin $adminObject */
/** @var Helper $helper */
require __DIR__ . '/admin_header.php';
xoops_cp_header();

$moduleDirName      = $helper->getDirname();
$moduleDirNameUpper = \mb_strtoupper($moduleDirName);

$xoopsModule = XoopsModule::getByDirname($moduleDirName);

if (!is_object($GLOBALS['xoopsUser']) || !is_object($xoopsModule)
    || !$GLOBALS['xoopsUser']->isAdmin($xoopsModule->mid())) {
    exit(constant('CO_' . $moduleDirNameUpper . '_' . 'ERROR403'));
}

/** @var \XoopsMySQLDatabase $xoopsDB */
$xoopsDB       = \XoopsDatabaseFactory::getDatabaseConnection();
$xoopsSecurity = new \XoopsSecurity();

$blocksadmin = new Blocksadmin($xoopsDB, $helper, $xoopsModule, $xoopsSecurity);

// Call the handleActions method
$op = Request::getCmd('op', 'list');

// Instantiate the BlockActionsHandler
$blockActionsHandler = new BlockActionsHandler($blocksadmin);

// Instantiate the Block DTO
$blockData = $blockActionsHandler->processPostData();
$blockData->op = $op;
$blockActionsHandler->handleActions($blockData);

    if ('order' === $op) {
    $blockActionsHandler->processOrderBlockAction($blockData);
    }

require __DIR__ . '/admin_footer.php';
