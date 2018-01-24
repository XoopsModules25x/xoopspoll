<?php
/*
               XOOPS - PHP Content Management System
                   Copyright (c) 2000-2016 XOOPS.org
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
 * Administration menu for the XoopsPoll Module
 *
 * @copyright ::  {@link https://xoops.org/ XOOPS Project}
 * @license   :: {@link http://www.fsf.org/copyleft/gpl.html GNU public license}
 * @package   :: xoopspoll
 * @subpackage:: admin
 * @since     :: 2.5.0
 * @author    :: XOOPS Module Team
 */

use XoopsModules\Xoopspoll;

// require_once __DIR__ . '/../class/Helper.php';
//require_once __DIR__ . '/../include/common.php';
$helper = Xoopspoll\Helper::getInstance();

$pathIcon32 = \Xmf\Module\Admin::menuIconPath('');
$pathModIcon32 = $helper->getModule()->getInfo('modicons32');


/**
 * Admin Menu
 */

$adminmenu[] = [
    'title' => _MI_XOOPSPOLL_HOME,
    'link'  => 'admin/index.php',
    'desc'  => _MI_XOOPSPOLL_HOMEDSC,
    'icon'  => "{$pathIcon32}" . '/home.png'
];

$adminmenu[] = [
    'title' => _MI_XOOPSPOLL_ADMENU1,
    'link'  => 'admin/main.php',
    'desc'  => _MI_XOOPSPOLL_ADMENU1DSC,
    'icon'  => "{$pathIcon32}" . '/poll.png'
];
$adminmenu[] = [
    'title' => _MI_XOOPSPOLL_ADMENU2,
    'link'  => 'admin/utility.php',
    'desc'  => _MI_XOOPSPOLL_ADMENU2DSC,
    'icon'  => "{$pathIcon32}" . '/wizard.png'
];
$adminmenu[] = [
    'title' => _MI_XOOPSPOLL_ADABOUT,
    'link'  => 'admin/about.php',
    'desc'  => _MI_XOOPSPOLL_ADABOUTDSC,
    'icon'  => "{$pathIcon32}" . '/about.png'
];
