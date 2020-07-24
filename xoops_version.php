<?php
/*
 * Xoops Poll Version Information
 *
 * Description: Version information and settings for the XoopsPoll Module
 *
 *  ------------------------------------------------------------------------
 *                XOOPS - PHP Content Management System
 *                    Copyright (c) 2000-2020 XOOPS.org
 *                       <https://xoops.org>
 *  ------------------------------------------------------------------------
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  You may not change or alter any portion of this comment or credits
 *  of supporting developers from this source code or any supporting
 *  source code which is considered copyrighted (c) material of the
 *  original comment or credit authors.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA
 *  ------------------------------------------------------------------------
 */

/**
 * @copyright::  {@link https://xoops.org/ XOOPS Project}
 * @license  ::  {@link http://www.fsf.org/copyleft/gpl.html GNU public license}
 * @package  ::  xoopspoll
 * @since    ::  1.0
 * @author   ::  {@link http://www.myweb.ne.jp Kazumi Ono}
 **/

require_once __DIR__ . '/preloads/autoloader.php';

$moduleDirName      = basename(__DIR__);
$moduleDirNameUpper = mb_strtoupper($moduleDirName);

// ------------------- Informations ------------------- //
$modversion = [
    'version'             => 2.01,
    'module_status'       => 'Beta 1',
    'release_date'        => '2020/07/21',
    'name'                => _MI_XOOPSPOLL_NAME,
    'description'         => _MI_XOOPSPOLL_DESC,
    'official'            => 0,     //1 indicates official XOOPS module supported by XOOPS Dev Team, 0 means 3rd party supported
    'author'              => 'Kazumi Ono, modified by Mazarin',
    'credits'             => 'XOOPS Development Team, Mamba, ZySpec',
    'author_mail'         => 'author-email',
    'author_website_url'  => 'https://xoops.org',
    'author_website_name' => 'XOOPS',
    'license'             => 'GPL 2.0 or later',
    'license_url'         => 'www.gnu.org/licenses/gpl-2.0.html/',
    // ------------------- Folders & Files -------------------
    'release_info'        => 'Changelog',
    'release_file'        => XOOPS_URL . "/modules/$moduleDirName/docs/changelog.txt",
    'manual'              => 'link to manual file',
    'manual_file'         => XOOPS_URL . "/modules/$moduleDirName/docs/install.txt",
    // images
    'image'               => 'assets/images/logoModule.png',
    'iconsmall'           => 'assets/images/iconsmall.png',
    'iconbig'             => 'assets/images/iconbig.png',
    'dirname'             => $moduleDirName,
    // Local path icons
    'modicons16'          => 'assets/images/icons/16',
    'modicons32'          => 'assets/images/icons/32',
    //About
    'demo_site_url'       => 'https://xoops.org',
    'demo_site_name'      => 'XOOPS Demo Site',
    'support_url'         => 'https://xoops.org/modules/newbb/viewforum.php?forum=28/',
    'support_name'        => 'Support Forum',
    'submit_bug'          => 'https://github.com/XoopsModules25x/' . $moduleDirName . '/issues',
    'module_website_url'  => 'www.xoops.org',
    'module_website_name' => 'XOOPS Project',
    // ------------------- Min Requirements -------------------
    'min_php'             => '7.1',
    'min_xoops'           => '2.5.10',
    'min_admin'           => '1.2',
    'min_db'              => ['mysql' => '5.5'],
    // ------------------- Admin Menu -------------------
    'system_menu'         => 1,
    'hasAdmin'            => 1,
    'adminindex'          => 'admin/index.php',
    'adminmenu'           => 'admin/menu.php',
    // ------------------- Main Menu -------------------
    'hasMain'             => 1,
    'sub'                 => [
        [
            'name' => _MI_XXXXX_VIEW_SEARCH,
            'url'  => 'index.php',
        ],
    ],

    // ------------------- Install/Update -------------------
    'onInstall'           => 'include/oninstall.php',
    'onUpdate'            => 'include/onupdate.php',
    //  'onUninstall'         => 'include/onuninstall.php',
    // -------------------  PayPal ---------------------------
    'paypal'              => [
        'business'      => 'xoopsfoundation@gmail.com',
        'item_name'     => 'Donation : ' . _MI_XXXXX_NAME,
        'amount'        => 0,
        'currency_code' => 'USD',
    ],
    // ------------------- Search ---------------------------
    'hasSearch'           => 1,
    'search'              => [
        'file' => 'include/search.inc.php',
        'func' => 'pedigree_search',
    ],
    // ------------------- Comments -------------------------
    'hasComments'         => 1,
    'comments'            => [
        'pageName'     => 'dog.php',
        'itemName'     => 'id',
        'callbackFile' => 'include/comment_functions.php',
        'callback'     => [
            'approve' => 'picture_comments_approve',
            'update'  => 'picture_comments_update',
        ],
    ],
    // ------------------- Mysql -----------------------------
    'sqlfile'             => ['mysql' => 'sql/mysql.sql'],
    // ------------------- Tables ----------------------------
    'tables'              => [
        $moduleDirName . '_' . 'option',
        $moduleDirName . '_' . 'desc',
        $moduleDirName . '_' . 'log',
    ],
];

// ------------------- Templates ------------------- //

$modversion['version']       = 2.00;
$modversion['module_status'] = 'Beta 1';
$modversion['release_date']  = '2018/01/12';
$modversion['name']          = _MI_XOOPSPOLL_NAME;
$modversion['description']   = _MI_XOOPSPOLL_DESC;
$modversion['author']        = 'Kazumi Ono, modified by Mazarin';
$modversion['credits']       = 'XOOPS Project, Mamba, ZySpec';
$modversion['help']          = 'page=help';
$modversion['license']       = 'GNU GPL 2.0';
$modversion['license_url']   = 'www.gnu.org/licenses/gpl-2.0.html';
$modversion['official']      = 1; // 0 = 3rd party module, 1 = supported by XOOPS Community
$modversion['image']         = 'assets/images/logoModule.png';
$modversion['dirname']       = basename(__DIR__);
$modversion['modicons16']    = 'assets/images/icons/16';
$modversion['modicons32']    = 'assets/images/icons/32';

$modversion['onInstall']           = 'include/oninstall.inc.php';
$modversion['onUpdate']            = 'include/onupdate.inc.php';
$modversion['onUninstall']         = 'include/onuninstall.inc.php';
$modversion['demo_site_url']       = '';
$modversion['demo_site_name']      = '';
$modversion['module_website_url']  = 'xoops.org';
$modversion['module_website_name'] = 'XOOPS';
$modversion['release_file']        = XOOPS_URL . '/modules/' . $modversion['dirname'] . '/docs/changelog.txt';
$modversion['author_website_url']  = 'http://www.myweb.ne.jp';
$modversion['author_website_name'] = 'Kazumi Ono';
$modversion['min_php']             = '7.2';
$modversion['min_xoops']           = '2.5.10';
$modversion['min_admin']           = '1.2';
$modversion['min_db']              = ['mysql' => '5.5'];

/**
 * Sql file (must contain sql generated by phpMyAdmin or phpPgAdmin)
 */
$modversion['sqlfile']['mysql'] = 'sql/mysql.sql';
/*
$modversion['sqlfile']['postgresql'] = 'sql'
                                     . '/pgsql.sql';
*/

/**
 * Tables created by sql file (without prefix!)
 */
$modversion['tables'] = [
    'xoopspoll_option',
    'xoopspoll_desc',
    'xoopspoll_log',
];

/**
 * Admin settings
 * 1 = has admin control panel | 0 = no
 */
$modversion['hasAdmin']   = 1;
$modversion['adminindex'] = 'admin/index.php';
$modversion['adminmenu']  = 'admin/menu.php';

/** Admin menu
 * 1 = display menu generated by system module | 0 = no
 */
$modversion['system_menu'] = 1;

/**
 * Template definitions
 */
$i                                          = 1;
$modversion['templates'][$i]['file']        = 'xoopspoll_index.tpl';
$modversion['templates'][$i]['description'] = _MI_XOOPSPOLL_INDEX_DSC;
++$i;
$modversion['templates'][$i]['file']        = 'xoopspoll_view.tpl';
$modversion['templates'][$i]['description'] = _MI_XOOPSPOLL_VIEW_DSC;
++$i;
$modversion['templates'][$i]['file']        = 'xoopspoll_results.tpl';
$modversion['templates'][$i]['description'] = _MI_XOOPSPOLL_RESULTS_DSC;
++$i;
$modversion['templates'][$i]['file']        = 'xoopspoll_results_renderer.tpl';
$modversion['templates'][$i]['description'] = _MI_XOOPSPOLL_RESULTS_REND_DSC;
++$i;
$modversion['templates'][$i]['file']        = 'admin/xoopspoll_index.tpl';
$modversion['templates'][$i]['description'] = _MI_XOOPSPOLL_ADMIN_INDEX_DSC;
++$i;
$modversion['templates'][$i]['file']        = 'admin/xoopspoll_list.tpl';
$modversion['templates'][$i]['description'] = _MI_XOOPSPOLL_ADMIN_LIST_DSC;
++$i;
$modversion['templates'][$i]['file']        = 'admin/xoopspoll_utility.tpl';
$modversion['templates'][$i]['description'] = _MI_XOOPSPOLL_ADMIN_UTIL_DSC;

/**
 * Search definitions
 * 1 = yes, module has search | 0 = no
 */
$modversion['hasSearch']      = 1;
$modversion['search']['file'] = 'include/search.inc.php';
$modversion['search']['func'] = 'xoopspoll_search';

/**#@+
 * Block template description
 */
$modversion['blocks'][1] = [
    'file'        => 'multipoll.php',
    'name'        => _MI_XOOPSPOLL_BNAME1,
    'description' => _MI_XOOPSPOLL_BNAME1DSC,
    'show_func'   => 'xoopspollBlockMultiShow',
    'edit_func'   => 'xoopspollBlockMultiEdit',
    'options'     => '1|1',
    'template'    => 'xoopspoll_block_poll.tpl',
];
$modversion['blocks'][2] = [
    'file'        => 'singlepoll.php',
    'name'        => _MI_XOOPSPOLL_BNAME2,
    'description' => _MI_XOOPSPOLL_BNAME2DSC,
    'show_func'   => 'xoopspollBlockSinglepollShow',
    'edit_func'   => 'xoopspollBlockSinglepollEdit',
    'options'     => '1|0|1|1',
    'template'    => 'xoopspoll_block_singlepoll.tpl',
];
/**#@-*/

/**#@+
 * Module Config option
 */
/**
 * Display host lookup to admin(1 = yes | 0 = no)
 * note: uses 3rd party resources to complete this request
 */
$modversion['config'][] = [
    'name'        => 'look_up_host',
    'title'       => '_MI_XOOPSPOLL_LOOKUPHOST',
    'description' => '_MI_XOOPSPOLL_LOOKUPHOSTDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => '0',
    'options'     => [],
];

/**
 * Display votes/voters to users (1 = yes | 0 = no)
 */
$modversion['config'][] = [
    'name'        => 'disp_vote_nums',
    'title'       => '_MI_XOOPSPOLL_DISPVOTE',
    'description' => '_MI_XOOPSPOLL_DISPVOTEDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => '1',
    'options'     => [],
];

/**
 * Hide polls created with forums in index, blocks, etc. (1 = yes | 0 = no)
 * polls still display in admin cpanel
 */
$modversion['config'][] = [
    'name'        => 'hide_forum_polls',
    'title'       => '_MI_XOOPSPOLL_HIDEFORUM_POLLS',
    'description' => '_MI_XOOPSPOLL_HIDEFORUM_POLLSDSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => '1',
    'options'     => [],
];

/**
 * Make Sample button visible?
 */
$modversion['config'][] = [
    'name'        => 'displaySampleButton',
    'title'       => 'CO_' . $moduleDirNameUpper . '_' . 'SHOW_SAMPLE_BUTTON',
    'description' => 'CO_' . $moduleDirNameUpper . '_' . 'SHOW_SAMPLE_BUTTON_DESC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
];

/**
 * Show Developer Tools?
 */
$modversion['config'][] = [
    'name'        => 'displayDeveloperTools',
    'title'       => 'CO_' . $moduleDirNameUpper . '_' . 'SHOW_DEV_TOOLS',
    'description' => 'CO_' . $moduleDirNameUpper . '_' . 'SHOW_DEV_TOOLS_DESC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0,
];

/**
 * Select the WYSIWYG Editor
 */
/*
xoops_load('XoopsEditorHandler');
$editorHandler = \XoopsEditorHandler::getInstance();
$editorList = array_flip($editorHandler->getList());

$modversion['config'][] = array(
    'name'        => 'useeditor',
    'title'       => '_MI_XOOPSPOLL_CHOOSEEDITOR',
    'description' => '_MI_XOOPSPOLL_CHOOSEEDITORDSC',
    'formtype'    => 'select',
    'valuetype'   => 'text',
    'options'     => $editorList,
    'default'     => 'dhtmltextarea');
*/

// default admin editor
xoops_load('XoopsEditorHandler');
$editorHandler = \XoopsEditorHandler::getInstance();
$editorList    = array_flip($editorHandler->getList());

$modversion['config'][] = [
    'name'        => 'editorAdmin',
    'title'       => '_MI_XOOPSPOLL_EDITOR_ADMIN',
    'description' => '_MI_XOOPSPOLL_EDITOR_ADMIN_DESC',
    'formtype'    => 'select',
    'valuetype'   => 'text',
    'default'     => 'dhtmltextarea',
    'options'     => $editorList,
];

/**
 * Module Comment definitions
 */
$modversion['hasComments']          = 1;
$modversion['comments']['pageName'] = 'pollresults.php';
$modversion['comments']['itemName'] = 'poll_id';
