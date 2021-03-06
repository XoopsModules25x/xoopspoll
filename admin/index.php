<?php
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
 * @license   ::    {@link http://www.fsf.org/copyleft/gpl.html GNU public license}
 * @package   ::    xoopspoll
 * @subpackage:: admin
 * @since     ::      1.32
 * @author    ::     XOOPS Module Team
 **/

use Xmf\Request;
use XoopsModules\Xoopspoll\{
    Common,
    Helper
};

require_once __DIR__ . '/admin_header.php';
xoops_cp_header();

/** @var Helper $helper */

$adminObject = \Xmf\Module\Admin::getInstance();

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

//------------- Test Data ----------------------------

if ($helper->getConfig('displaySampleButton')) {
    $yamlFile            = dirname(__DIR__) . '/config/admin.yml';
    $config              = loadAdminConfig($yamlFile);
    $displaySampleButton = $config['displaySampleButton'];

    if (1 == $displaySampleButton) {
        xoops_loadLanguage('admin/modulesadmin', 'system');
        require_once dirname(__DIR__) . '/testdata/index.php';

        $adminObject->addItemButton(constant('CO_' . $moduleDirNameUpper . '_' . 'ADD_SAMPLEDATA'), '__DIR__ . /../../testdata/index.php?op=load', 'add');
        $adminObject->addItemButton(constant('CO_' . $moduleDirNameUpper . '_' . 'SAVE_SAMPLEDATA'), '__DIR__ . /../../testdata/index.php?op=save', 'add');
        //    $adminObject->addItemButton(constant('CO_' . $moduleDirNameUpper . '_' . 'EXPORT_SCHEMA'), '__DIR__ . /../../testdata/index.php?op=exportschema', 'add');
        $adminObject->addItemButton(constant('CO_' . $moduleDirNameUpper . '_' . 'HIDE_SAMPLEDATA_BUTTONS'), '?op=hide_buttons', 'delete');
    } else {
        $adminObject->addItemButton(constant('CO_' . $moduleDirNameUpper . '_' . 'SHOW_SAMPLEDATA_BUTTONS'), '?op=show_buttons', 'add');
        $displaySampleButton = $config['displaySampleButton'];
    }
    $adminObject->displayButton('left', '');
}

//------------- End Test Data ----------------------------

$adminObject->displayIndex();

/**
 * @param $yamlFile
 * @return array|bool
 */
function loadAdminConfig($yamlFile)
{
    $config = \Xmf\Yaml::readWrapped($yamlFile); // work with phpmyadmin YAML dumps
    return $config;
}

/**
 * @param $yamlFile
 */
function hideButtons($yamlFile)
{
    $app['displaySampleButton'] = 0;
    \Xmf\Yaml::save($app, $yamlFile);
    redirect_header('index.php', 0, '');
}

/**
 * @param $yamlFile
 */
function showButtons($yamlFile)
{
    $app['displaySampleButton'] = 1;
    \Xmf\Yaml::save($app, $yamlFile);
    redirect_header('index.php', 0, '');
}

$op = Request::getString('op', 0, 'GET');

switch ($op) {
    case 'hide_buttons':
        hideButtons($yamlFile);
        break;
    case 'show_buttons':
        showButtons($yamlFile);
        break;
}

echo $utility::getServerStats();

require __DIR__ . '/admin_footer.php';
