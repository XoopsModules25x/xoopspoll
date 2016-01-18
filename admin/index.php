<?php
/*
               XOOPS - PHP Content Management System
                   Copyright (c) 2000 XOOPS.org
                      <http://xoops.org/>
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
 * @copyright::  {@link http://xoops.org/ XOOPS Project}
 * @license::    {@link http://www.fsf.org/copyleft/gpl.html GNU public license}
 * @package::    xoopspoll
 * @subpackage:: admin
 * @since::      1.32
 * @author::     XOOPS Module Team
 * @version::    $Id: index.php 11539 2013-05-13 20:56:06Z zyspec $
**/

require_once __DIR__ . '/admin_header.php';
xoops_cp_header();

$admin_class = new ModuleAdmin();

$pollHandler =& xoops_getmodulehandler('poll', 'xoopspoll');
$totalPolls  = $pollHandler->getCount();
$criteria    = new CriteriaCompo();
$criteria->add(new Criteria('start_time', time(), '<='));
$criteria->add(new Criteria('end_time', time(), '>'));
$totalActivePolls = $pollHandler->getCount($criteria);
$criteria         = new CriteriaCompo();
$criteria->add(new Criteria('start_time', time(), '>'));
$totalWaitingPolls = $pollHandler->getCount($criteria);
$totalExpiredPolls = $totalPolls - $totalActivePolls - $totalWaitingPolls;

$admin_class->addInfoBox(_MD_XOOPSPOLL_DASHBOARD) ;
$admin_class->addInfoBoxLine(_MD_XOOPSPOLL_DASHBOARD, "<span class='infolabel'>" ._MD_XOOPSPOLL_TOTALACTIVE . '</span>', $totalActivePolls, 'Green') ;
$admin_class->addInfoBoxLine(_MD_XOOPSPOLL_DASHBOARD, "<span class='infolabel'>" ._MD_XOOPSPOLL_TOTALWAITING . '</span>', $totalWaitingPolls, 'Green') ;
$admin_class->addInfoBoxLine(_MD_XOOPSPOLL_DASHBOARD, "<span class='infolabel'>" ._MD_XOOPSPOLL_TOTALEXPIRED . '</span>', $totalExpiredPolls, 'Red') ;
$admin_class->addInfoBoxLine(_MD_XOOPSPOLL_DASHBOARD, "<span class='infolabel'>" ._MD_XOOPSPOLL_TOTALPOLLS."</span><span class='infotext'>", $totalPolls . '</span>') ;

/* use templates just in case we want to easily modify display in the future */
$GLOBALS['xoopsTpl']->assign('navigation', $admin_class->addNavigation('index.php'));
$GLOBALS['xoopsTpl']->assign('renderindex', $admin_class->renderIndex());
$GLOBALS['xoopsTpl']->display($GLOBALS['xoops']->path('modules/xoopspoll/templates/admin/xoopspoll_index.tpl'));

require_once __DIR__ . '/admin_footer.php';
