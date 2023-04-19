<?php declare(strict_types=1);
/*
               XOOPS - PHP Content Management System
                   Copyright (c) 2000-2020 XOOPS.org
                      <https://xoops.org>
 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 You may not change or alter any portion of this comment or credits
 of supporting developers from this source code or any supporting
 source code which is considered copyrighted (c) material of the
 original comment or credit authors.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA
*/

/**
 * XOOPS Poll module
 * Administration index to display module information and admin links
 *
 * @copyright ::  {@link https://xoops.org/ XOOPS Project}
 * @license   ::    {@link https://www.gnu.org/licenses/gpl-2.0.html GNU GPL 2.0 or later}
 * @subpackage:: admin
 * @since     ::      1.32
 * @author    ::     XOOPS Module Team
 **/

use Xmf\Module\Admin;
use Xmf\Request;
use XoopsModules\Xoopspoll\{
    Common\TestdataButtons,
    Helper,
    Utility
};

/** @var Admin $adminObject */
/** @var Helper $helper */
/** @var Utility $utility */
require_once __DIR__ . '/admin_header.php';
xoops_cp_header();

/** @var Helper $helper */
$adminObject = Admin::getInstance();

$pollHandler = Helper::getInstance()->getHandler('Poll');
$totalPolls  = $pollHandler->getCount();
$criteria    = new \CriteriaCompo();
$criteria->add(new \Criteria('start_time', time(), '<='));
$criteria->add(new \Criteria('end_time', time(), '>'));
$totalActivePolls = $pollHandler->getCount($criteria);
$criteria         = new \CriteriaCompo();
$criteria->add(new \Criteria('start_time', time(), '>'));
$totalWaitingPolls = $pollHandler->getCount($criteria);
$totalExpiredPolls = $totalPolls - $totalActivePolls - $totalWaitingPolls;

$adminObject->addInfoBox(_MD_XOOPSPOLL_DASHBOARD);
$adminObject->addInfoBoxLine(sprintf("<span class='infolabel'>" . _MD_XOOPSPOLL_TOTALACTIVE . '</span>', $totalActivePolls), '', 'Green');
$adminObject->addInfoBoxLine(sprintf("<span class='infolabel'>" . _MD_XOOPSPOLL_TOTALWAITING . '</span>', $totalWaitingPolls), '', 'Green');
$adminObject->addInfoBoxLine(sprintf("<span class='infolabel'>" . _MD_XOOPSPOLL_TOTALEXPIRED . '</span>', $totalExpiredPolls), '', 'Red');
$adminObject->addInfoBoxLine(sprintf("<span class='infolabel'>" . _MD_XOOPSPOLL_TOTALPOLLS . "</span><span class='infotext'>", $totalPolls . '</span>'), '');

/* use templates just in case we want to easily modify display in the future */
//$GLOBALS['xoopsTpl']->assign('navigation', $adminObject->displayNavigation(basename(__FILE__)));
//$GLOBALS['xoopsTpl']->assign('renderindex', $adminObject->displayIndex());
//$GLOBALS['xoopsTpl']->display($GLOBALS['xoops']->path('modules/xoopspoll/templates/admin/xoopspoll_index.tpl'));

$adminObject->displayNavigation(basename(__FILE__));

//------------- Test Data Buttons ----------------------------
if ($helper->getConfig('displaySampleButton')) {
    TestdataButtons::loadButtonConfig($adminObject);
    $adminObject->displayButton('left', '');
}
$op = Request::getString('op', 0, 'GET');
switch ($op) {
    case 'hide_buttons':
        TestdataButtons::hideButtons();
        break;
    case 'show_buttons':
        TestdataButtons::showButtons();
        break;
}
//------------- End Test Data Buttons ----------------------------

$adminObject->displayIndex();
echo $utility::getServerStats();

//codeDump(__FILE__);
require_once __DIR__ . '/admin_footer.php';
