<?php
/*------------------------------------------------------------------------
                XOOPS - PHP Content Management System
                    Copyright (c) 2000-2016 XOOPS.org
                       <http://xoops.org>
  ------------------------------------------------------------------------
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
  ------------------------------------------------------------------------
  Author: phppp (D.J., infomax@gmail.com)
  URL: http://xoopsforge.com, http://xoops.org.cn
  Project: Article Project
  ------------------------------------------------------------------------
*/

/**
 * View Forum Topic with poll support
 *
 * @copyright::  {@link http://xoops.org/ XOOPS Project}
 * @license  ::    {@link http://www.fsf.org/copyleft/gpl.html GNU public license}
 * @package  ::    newbb
 * @author   ::     phppp (D.J.) <infomax@gmail.com>
 */

// irmtfan enhance include
use XoopsModules\Newbb\Helper;

require_once __DIR__ . '/header.php';
$xoopsLogger->startTime('newBB_viewtopic');
mod_loadFunctions('read', 'newbb');
mod_loadFunctions('render', 'newbb');
xoops_loadLanguage('user'); // irmtfan add last_login

/*Build the page query*/
$query_vars  = ['post_id', 'topic_id', 'status', 'order', 'start', 'move', 'mode'];
$query_array = [];
foreach ($query_vars as $var) {
    if (!empty($_GET[$var])) {
        $query_array[$var] = "{$var}={$_GET[$var]}";
    }
}
$page_query = htmlspecialchars(implode('&', $query_array), ENT_QUOTES | ENT_HTML5);
unset($query_array);

$forum_id = !empty($_GET['forum']) ? (int)$_GET['forum'] : 0;
$read     = (!empty($_GET['read']) && 'new' == $_GET['read']) ? $_GET['read'] : '';
$topic_id = isset($_GET['topic_id']) ? (int)$_GET['topic_id'] : 0;
$post_id  = !empty($_GET['post_id']) ? (int)$_GET['post_id'] : 0;
$move     = isset($_GET['move']) ? strtolower($_GET['move']) : '';
$start    = !empty($_GET['start']) ? (int)$_GET['start'] : 0;
$status   = (!empty($_GET['status'])
             && in_array($_GET['status'], ['active', 'pending', 'deleted'])) ? $_GET['status'] : '';
$mode     = !empty($_GET['mode']) ? (int)$_GET['mode'] : (!empty($status) ? 2 : 0);
$order    = (!empty($_GET['order']) && in_array($_GET['order'], ['ASC', 'DESC'])) ? $_GET['order'] : '';

if ('' === $order) {
    if (($xoopsUser instanceof XoopsUser) && $xoopsUser->isActive()) {
        $order = (1 === $xoopsUser->getVar('uorder')) ? 'DESC' : 'ASC';
    } else {
        $order = (1 === $xoopsConfig['com_order']) ? 'DESC' : 'ASC';
    }
}

if (!$topic_id && !$post_id) {
    $redirect = empty($forum_id) ? XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . '/index.php' : XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . "/viewforum.php?forum={$forum_id}";
    redirect_header($redirect, 2, _MD_ERRORTOPIC);
}
/** @var NewbbTopicHandler $topicHandler */
$topicHandler = Helper::getInstance()->getHandler('Topic');
if (!empty($post_id)) {
    $topic_obj = $topicHandler->getByPost($post_id);
    $topic_id  = $topic_obj->getVar('topic_id');
} elseif (!empty($move)) {
    $topic_obj = $topicHandler->getByMove($topic_id, ('prev' === $move) ? -1 : 1, $forum_id);
    $topic_id  = $topic_obj->getVar('topic_id');
} else {
    $topic_obj = $topicHandler->get($topic_id);
}

if ((!$topic_obj instanceof \Topic) || !$topic_id = $topic_obj->getVar('topic_id')) {
    $redirect = empty($forum_id) ? XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . '/index.php' : XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . "/viewforum.php?forum={$forum_id}";
    redirect_header($redirect, 2, _MD_ERRORTOPIC);
}
$forum_id = $topic_obj->getVar('forum_id');
/** @var NewbbForumHandler $forumHandler */
$forumHandler = Helper::getInstance()->getHandler('Forum');
$forum_obj    = $forumHandler->get($forum_id);

$isadmin = newbb_isAdmin($forum_obj);

if ((!$isadmin && $topic_obj->getVar('approved') < 0) || (!$forumHandler->getPermission($forum_obj))
    || (!$topicHandler->getPermission($forum_obj, $topic_obj->getVar('topic_status'), 'view'))) {
    redirect_header(XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . '/viewforum.php?forum=' . $forum_id, 2, _MD_NORIGHTTOVIEW);
}

// START irmtfan - find if topic is read or unread - for all users (member and anon)
$topic_is_unread = true;
/* if $GLOBALS['xoopsModuleConfig']["read_mode"] == 0 ||
 * never read && $GLOBALS['xoopsModuleConfig']["read_mode"] == 1 ||
 * never read && $GLOBALS['xoopsModuleConfig']["read_mode"] == 2 ||
 * => $topic_last_post_time_or_id_read = NULL
 * if !$xoopsUser && $GLOBALS['xoopsModuleConfig']["read_mode"] == 2
 * => $topic_last_post_time_or_id_read = false
 * if !$xoopsUser && $GLOBALS['xoopsModuleConfig']["read_mode"] == 1
 * => $topic_last_post_time_or_id_read = lastview(newbb_IP{ip}LT)
*/
$topic_last_post_time_or_id_read = newbb_getRead('topic', $topic_id);
if (!empty($topic_last_post_time_or_id_read)) {
    if (1 === $GLOBALS['xoopsModuleConfig']['read_mode']) {
        $postHandler     = Helper::getInstance()->getHandler('Post');
        $post_obj        = $postHandler->get($topic_obj->getVar('topic_last_post_id'));
        $topic_is_unread = ($topic_last_post_time_or_id_read < $post_obj->getVar('post_time'));
    }
    if (2 === $GLOBALS['xoopsModuleConfig']['read_mode']) {
        $topic_is_unread = ($topic_last_post_time_or_id_read < $topic_obj->getVar('topic_last_post_id'));
        // hack jump to last post read if post_id is empty - is there any better way?
        if (empty($post_id) && !empty($GLOBALS['xoopsModuleConfig']['jump_to_topic_last_post_read_enabled'])
            && $topic_is_unread) {
            header('Location: ' . $_SERVER['REQUEST_URI'] . '&post_id=' . $topic_last_post_time_or_id_read);
        }
    }
}
// END irmtfan - find if topic is read or unread - for all users (member and anon)

/* Only admin has access to admin mode */
if (!$isadmin) {
    $status = '';
    $mode   = 0;
}

if (!empty($GLOBALS['xoopsModuleConfig']['enable_karma'])) {
    $karmaHandler = Helper::getInstance()->getHandler('Karma');
    $user_karma   = $karmaHandler->getUserKarma();
}

//$viewmode = "flat";

$total_posts = $topicHandler->getPostCount($topic_obj, $status);
$postsArray  = $topicHandler->getAllPosts($topic_obj, $order, $GLOBALS['xoopsModuleConfig']['posts_per_page'], $start, $post_id, $status);

//irmtfan - increment topic_views only if the topic is unread
if ($topic_is_unread) {
    $topic_obj->incrementCounter();
}
newbb_setRead('topic', $topic_id, $topic_obj->getVar('topic_last_post_id'));

$GLOBALS['xoopsOption']['template_main'] = 'newbb_viewtopic.tpl';
// irmtfan remove and move to footer.php
//$xoopsOption['xoops_module_header']= $xoops_module_header;
// irmtfan include header.php after defining $xoopsOption['template_main']
require_once $GLOBALS['xoops']->path('header.php');
//$xoopsTpl->assign('xoops_module_header', $xoops_module_header);
// irmtfan new method
if (!empty($GLOBALS['xoopsModuleConfig']['rss_enable'])) {
    $xoopsTpl->assign(
        'xoops_module_header',
        '
        <link rel="alternate" type="application/rss+xml" title="' . $xoopsModule->getVar('name') . '-' . $forum_obj->getVar('forum_name') . '" href="' . XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . '/rss.php?f=' . $forum_obj->getVar('forum_id') . '">
        ' . @$xoopsTpl->get_template_vars('xoops_module_header')
    );
}

if ($GLOBALS['xoopsModuleConfig']['wol_enabled']) {
    $onlineHandler = Helper::getInstance()->getHandler('Online');
    $onlineHandler->init($forum_obj, $topic_obj);
    $xoopsTpl->assign('online', $onlineHandler->showOnline());
}
$xoopsTpl->assign('parentforum', $forumHandler->getParents($forum_obj));
// irmtfan - remove icon_path and use newbb_displayImage
$xoopsTpl->assign('anonym_avatar', newbb_displayImage('anonym'));

// START irmtfan improve infobox
$infobox         = [];
$infobox['show'] = (int)$GLOBALS['xoopsModuleConfig']['show_infobox']; //4.05
// irmtfan removed then define after array
//$xoopsTpl->assign('infobox', $infobox); //4.05
$iconHandler = newbb_getIconHandler(); // can be use in the follwing codes in this file

if ($infobox['show'] > 0) {
    // irmtfan - remove icon_path and use newbb_displayImage
    $infobox['icon'] = [
        'expand'   => $iconHandler->getImageSource('less'),
        'collapse' => $iconHandler->getImageSource('more'),
    ];
    if (1 === $infobox['show']) {
        $infobox['style'] = 'none';        //irmtfan move semicolon
        $infobox['alt']   = _MD_NEWBB_SEEUSERDATA;
        $infobox['src']   = 'more';
    } else {
        $infobox['style'] = 'block';        //irmtfan move semicolon
        $infobox['alt']   = _MD_NEWBB_HIDEUSERDATA;
        $infobox['src']   = 'less';
    }
    $infobox['displayImage'] = newbb_displayImage($infobox['src'], $infobox['alt']);
}
$xoopsTpl->assign('infobox', $infobox);
// END irmtfan improve infobox

$categoryHandler = xoops_getModuleHandler('category');
$category_obj    = $categoryHandler->get($forum_obj->getVar('cat_id'), ['cat_title']);

$xoopsTpl->assign(
    [
        'topic_title'    => '<a href="' . XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/viewtopic.php?topic_id=' . $topic_id . '">' . $topic_obj->getFullTitle() . '</a>',
        'forum_name'     => $forum_obj->getVar('forum_name'),
        'lang_nexttopic' => _MD_NEXTTOPIC,
        'lang_prevtopic' => _MD_PREVTOPIC,
        'topic_status'   => $topic_obj->getVar('topic_status'),
        'category'       => [
            'id'    => $forum_obj->getVar('cat_id'),
            'title' => $category_obj->getVar('cat_title'),
        ],
        'post_id'        => $post_id,
        'topic_id'       => $topic_id,
        'forum_id'       => $forum_id,
        'order_current'  => ('DESC' === $order) ? 'DESC' : 'ASC',
    ]
);

$t_new   = newbb_displayImage('t_new', _MD_POSTNEW);
$t_reply = newbb_displayImage('t_reply', _MD_REPLY);
// irmtfan show topic status if show reg is 0 and revise forum_post_or_register
if ($topicHandler->getPermission($forum_obj, $topic_obj->getVar('topic_status'), 'post')) {
    $xoopsTpl->assign('forum_post', '<a href="' . XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/newtopic.php?forum=' . $forum_id . '">' . $t_new . '</a>');
} else {
    if ($topic_obj->getVar('topic_status')) {
        $xoopsTpl->assign('topic_lock', _MD_TOPICLOCKED);
    }
    if (!empty($GLOBALS['xoopsModuleConfig']['show_reg']) && !is_object($xoopsUser)) {
        $xoopsTpl->assign('forum_register', '<a href="' . XOOPS_URL . '/user.php?xoops_redirect=' . htmlspecialchars($xoopsRequestUri, ENT_QUOTES | ENT_HTML5) . '">' . _MD_REGTOPOST . '</a>');
    }
}
// irmtfan for backward compatibility assign forum_post_or_register smarty again.
$xoopsTpl->assign('forum_post_or_register', @$xoopsTpl->get_template_vars('forum_post') . @$xoopsTpl->get_template_vars('forum_register') . @$xoopsTpl->get_template_vars('topic_lock'));

if ($topicHandler->getPermission($forum_obj, $topic_obj->getVar('topic_status'), 'reply')) {
    $xoopsTpl->assign('forum_reply', '<a href="' . XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/reply.php?topic_id=' . $topic_id . '">' . $t_reply . '</a>');
}

$poster_array  = [];
$require_reply = false;
foreach ($postsArray as $eachpost) {
    if ($eachpost->getVar('uid') > 0) {
        $poster_array[$eachpost->getVar('uid')] = 1;
    }
    if ($eachpost->getVar('require_reply') > 0) {
        $require_reply = true;
    }
}

$userid_array = [];
$online       = [];
if (count($poster_array) > 0) {
    /** @var \XoopsMemberHandler $memberHandler */
    $memberHandler = xoops_getHandler('member');
    $userid_array  = array_keys($poster_array);
    $user_criteria = '(' . implode(',', $userid_array) . ')';
    $users         = $memberHandler->getUsers(new Criteria('uid', $user_criteria, 'IN'), true);
} else {
    $users = [];
}

$viewtopic_users = [];
if (count($userid_array) > 0) {
    require $GLOBALS['xoops']->path('modules/' . $xoopsModule->getVar('dirname', 'n') . '/class/user.php');
    $userHandler         = new \NewbbUserHandler($GLOBALS['xoopsModuleConfig']['groupbar_enabled'], $GLOBALS['xoopsModuleConfig']['wol_enabled']);
    $userHandler->users  = $users;
    $userHandler->online = $online;
    $viewtopic_users     = $userHandler->getUsers();
}
unset($users);

if ($GLOBALS['xoopsModuleConfig']['allow_require_reply'] && $require_reply) {
    if (!empty($GLOBALS['xoopsModuleConfig']['cache_enabled'])) {
        $viewtopic_posters = newbb_getsession('t' . $topic_id, true);
        if (!is_array($viewtopic_posters) || 0 === count($viewtopic_posters)) {
            $viewtopic_posters = $topicHandler->getAllPosters($topic_obj);
            newbb_setsession('t' . $topic_id, $viewtopic_posters);
        }
    } else {
        $viewtopic_posters = $topicHandler->getAllPosters($topic_obj);
    }
} else {
    $viewtopic_posters = [];
}

if ($GLOBALS['xoopsModuleConfig']['show_advertising']) {
    $post_werbung = [
        'post_id'         => 0,
        'post_parent_id'  => 0,
        'post_date'       => 0,
        'post_image'      => '',
        'post_title'      => '',
        'post_text'       => '<div style="text-align: center;vertical-align: middle;"><br>' . xoops_getbanner() . '</div>',
        'post_attachment' => '',
        'post_edit'       => 0,
        'post_no'         => 0,
        'post_signature'  => _MD_ADVERTISING_BLOCK,
        'poster_ip'       => '',
        'thread_action'   => '',
        'thread_buttons'  => '',
        'mod_buttons'     => '',
        'poster'          => [
            'uid'        => -1,
            'link'       => _MD_ADVERTISING_USER,
            'avatar'     => 'avatars/blank.gif',
            'regdate'    => 0,
            'last_login' => 0,
            'rank'       => ['title' => ''],
        ],
        // irmtfan add last_login
        'post_permalink'  => '',
    ];
}

$i = 0;
foreach ($postsArray as $eachpost) {
    if ($GLOBALS['xoopsModuleConfig']['show_advertising']) {
        if (2 === $i) {
            $xoopsTpl->append('topic_posts', $post_werbung);
        }
        ++$i;
    }
    $xoopsTpl->append('topic_posts', $eachpost->showPost($isadmin));
}

if ($total_posts > $GLOBALS['xoopsModuleConfig']['posts_per_page']) {
    include $GLOBALS['xoops']->path('class/pagenav.php');
    $nav = new XoopsPageNav($total_posts, $GLOBALS['xoopsModuleConfig']['posts_per_page'], $start, 'start', 'topic_id=' . $topic_id . '&amp;order=' . $order . '&amp;status=' . $status . '&amp;mode=' . $mode);
    //    if (isset($GLOBALS['xoopsModuleConfig']['do_rewrite']) && $GLOBALS['xoopsModuleConfig']['do_rewrite'] == 1) $nav->url = XOOPS_URL . $nav->url;
    if ('select' === $GLOBALS['xoopsModuleConfig']['pagenav_display']) {
        $navi = $nav->renderSelect();
    } elseif ('bild' === $GLOBALS['xoopsModuleConfig']['pagenav_display']) {
        $navi = $nav->renderImageNav(4);
    } else {
        $navi = $nav->renderNav(4);
    }
    $xoopsTpl->assign('forum_page_nav', $navi);
} else {
    $xoopsTpl->assign('forum_page_nav', '');
}

if (empty($post_id)) {
    $first   = array_keys($postsArray);
    $post_id = (!empty($first[0])) ? $first[0] : 0;
}
if (!empty($postsArray[$post_id])) {
    $xoops_pagetitle = $postsArray[$post_id]->getVar('subject') . ' [' . $forum_obj->getVar('forum_name') . ']';
    $xoopsTpl->assign('xoops_pagetitle', $xoops_pagetitle);
    $xoopsOption['xoops_pagetitle'] = $xoops_pagetitle;
    $kw                             = array_unique(explode(' ', strip_tags($postsArray[$post_id]->getVar('post_text')), 150));
    asort($kw);
    $kwort = '';
    $z     = 0;
    foreach ($kw as $k) {
        if (strlen(trim($k)) > 5 && $z < 30) {
            $kwort .= trim($k) . ' ';
            ++$z;
        }
    }
    $xoTheme->addMeta('meta', 'keywords', $kwort);
    $xoTheme->addMeta('meta', 'description', substr(strip_tags($postsArray[$post_id]->getVar('post_text')), 0, 120));
}
unset($postsArray);

$xoopsTpl->assign('topic_print_link', "print.php?form=1&amp;{$page_query}");

$admin_actions = [];
$ad_merge      = '';
$ad_move       = '';
$ad_delete     = '';
// irmtfan add restore to viewtopic
$ad_restore  = '';
$ad_lock     = '';
$ad_unlock   = '';
$ad_sticky   = '';
$ad_unsticky = '';
$ad_digest   = '';
$ad_undigest = '';

// START irmtfan add restore to viewtopic
if ($topic_obj->getVar('approved') > 0) { // if the topic is active
    $admin_actions['merge']  = [
        'link'  => XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/topicmanager.php?mode=merge&amp;topic_id=' . $topic_id,
        'name'  => _MD_MERGETOPIC,
        'image' => $ad_merge,
    ];
    $admin_actions['move']   = [
        'link'  => XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/topicmanager.php?mode=move&amp;topic_id=' . $topic_id,
        'name'  => _MD_MOVETOPIC,
        'image' => $ad_move,
    ];
    $admin_actions['delete'] = [
        'link'  => XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/topicmanager.php?mode=delete&amp;topic_id=' . $topic_id,
        'name'  => _MD_DELETETOPIC,
        'image' => $ad_delete,
    ];
    if (!$topic_obj->getVar('topic_status')) {
        $admin_actions['lock'] = [
            'link'  => XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/topicmanager.php?mode=lock&amp;topic_id=' . $topic_id,
            'image' => $ad_lock,
            'name'  => _MD_LOCKTOPIC,
        ];
    } else {
        $admin_actions['unlock'] = [
            'link'  => XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/topicmanager.php?mode=unlock&amp;topic_id=' . $topic_id,
            'image' => $ad_unlock,
            'name'  => _MD_UNLOCKTOPIC,
        ];
    }
    if (!$topic_obj->getVar('topic_sticky')) {
        $admin_actions['sticky'] = [
            'link'  => XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/topicmanager.php?mode=sticky&amp;topic_id=' . $topic_id,
            'image' => $ad_sticky,
            'name'  => _MD_STICKYTOPIC,
        ];
    } else {
        $admin_actions['unsticky'] = [
            'link'  => XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/topicmanager.php?mode=unsticky&amp;topic_id=' . $topic_id,
            'image' => $ad_unsticky,
            'name'  => _MD_UNSTICKYTOPIC,
        ];
    }
    if (!$topic_obj->getVar('topic_digest')) {
        $admin_actions['digest'] = [
            'link'  => XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/topicmanager.php?mode=digest&amp;topic_id=' . $topic_id,
            'image' => $ad_digest,
            'name'  => _MD_DIGESTTOPIC,
        ];
    } else {
        $admin_actions['undigest'] = [
            'link'  => XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/topicmanager.php?mode=undigest&amp;topic_id=' . $topic_id,
            'image' => $ad_undigest,
            'name'  => _MD_UNDIGESTTOPIC,
        ];
    }
} else { // if the topic is pending/deleted then restore/approve
    $admin_actions['restore'] = [
        'link'  => XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/topicmanager.php?mode=restore&amp;topic_id=' . $topic_id,
        'name'  => _MD_RESTORETOPIC,
        'image' => $ad_restore,
    ];
}
// END irmtfan add restore to viewtopic

$xoopsTpl->assign_by_ref('admin_actions', $admin_actions);
$xoopsTpl->assign('viewer_level', (int)($isadmin ? 2 : is_object($xoopsUser)));

if ($GLOBALS['xoopsModuleConfig']['show_permissiontable']) {
    $permissionHandler = Helper::getInstance()->getHandler('Permission');
    $permission_table  = $permissionHandler->getPermissionTable($forum_obj, $topic_obj->getVar('topic_status'), $isadmin);
    $xoopsTpl->assign_by_ref('permission_table', $permission_table);
}

// Show poll
if ($pollmodules) {
    /** {@internal pollmodules set in header.php} */
    if (($topic_obj->getVar('topic_haspoll')
         && $topicHandler->getPermission($forum_obj, $topic_obj->getVar('topic_status'), 'vote'))
        || $topicHandler->getPermission($forum_obj, $topic_obj->getVar('topic_status'), 'addpoll')) {
        if ('xoopspoll' === $pollmodules) {
            xoops_load('renderer', 'xoopspoll');
            xoops_loadLanguage('main', 'xoopspoll');
        } else {
            @require_once $GLOBALS['xoops']->path('modules/umfrage/class/umfrage.php');
            @require_once $GLOBALS['xoops']->path('modules/umfrage/class/umfrageoption.php');
            @require_once $GLOBALS['xoops']->path('modules/umfrage/class/umfragelog.php');
            @require_once $GLOBALS['xoops']->path('modules/umfrage/class/umfragerenderer.php');
        }
    }

    if ($topic_obj->getVar('topic_haspoll') && (0 !== $topic_obj->getVar('poll_id'))
        // double check to make sure it's a non-zero poll
        && $topicHandler->getPermission($forum_obj, $topic_obj->getVar('topic_status'), 'vote')) {
        $GLOBALS['xoopsTpl']->assign('topic_poll', 1);
        $GLOBALS['xoopsTpl']->assign('pollmodules', $pollmodules);
        $uid = ($GLOBALS['xoopsUser'] instanceof XoopsUser) ? $GLOBALS['xoopsUser']->getVar('uid') : 0;
        if ('xoopspoll' === $pollmodules) {
            $xpollHandler = xoops_getModuleHandler('poll', 'xoopspoll');
            $poll_obj     = $xpollHandler->get($topic_obj->getVar('poll_id'));
            if (!empty($poll_obj) && $poll_obj instanceof \XoopspollPoll) {
                /* check to see if user has rights to view the results */
                $vis_return = $poll_obj->isResultVisible();
                $isVisible  = (true === $vis_return) ? true : false;
                $visibleMsg = $isVisible ? '' : $vis_return;

                /* setup the module config handler */
                /** @var \XoopsConfigHandler $configHandler */
                $configHandler = xoops_getHandler('config');
                $xp_config     = $configHandler->getConfigsByCat(0, $xoopspoll->getVar('mid'));

                $GLOBALS['xoopsTpl']->assign(
                    [
                        'is_visible'      => $isVisible,
                        'visible_message' => $visibleMsg,
                        'disp_votes'      => $xp_config['disp_vote_nums'],
                        'lang_vote'       => _MD_XOOPSPOLL_VOTE,
                        'lang_results'    => _MD_XOOPSPOLL_RESULTS,
                        'back_link'       => '',
                    ]
                );
                $renderer = new \XoopspollRenderer($poll_obj);
                //                $renderer->assignResults($GLOBALS['xoopsTpl']);
                // check to see if user has voted, show form if not, otherwise get results for form

                $logHandler = xoops_getModuleHandler('log', 'xoopspoll');
                if ($poll_obj->isAllowedToVote()
                    && (!$logHandler->hasVoted($poll_obj->getVar('poll_id'), xoops_getenv('REMOTE_ADDR'), $uid))) {
                    /*
                                        $renderer->assignForm($GLOBALS['xoopsTpl']);
                                      $GLOBALS['xoopsTpl']->assign('topic_pollresult', null);
                    */
                    //                    $GLOBALS['xoopsTpl']->assign('can_vote', true);
                    //                    $GLOBALS['xoopsTpl']->assign('topic_pollform', $renderer->renderForm());
                    $myTpl = new XoopsTpl();
                    $renderer->assignForm($myTpl);
                    $myTpl->assign('action', $GLOBALS['xoops']->url("modules/newbb/votepolls.php?topic_id={$topic_id}&amp;poll_id={$poll_id}"));
                    $topic_pollform = $myTpl->fetch($GLOBALS['xoops']->path('modules/xoopspoll/templates/xoopspoll_view.tpl'));
                    $GLOBALS['xoopsTpl']->assign('topic_pollform', $topic_pollform);
                } else {
                    //                    $renderer->assignResults($GLOBALS['xoopsTpl']);
                    $GLOBALS['xoopsTpl']->assign('can_vote', false);
                    $GLOBALS['xoopsTpl']->assign('topic_pollresult', $renderer->renderResults());
                }
            }
        } else { //Umfrage
            $poll_obj = new \Umfrage($topic_obj->getVar('poll_id'));
            $hasEnded = $poll_obj->getVar('end_time') < time() ? true : false;
            $renderer = new \UmfrageRenderer($poll);
            $xoopsTpl->assign('lang_alreadyvoted2', _PL_ALREADYVOTED2);
            $xoopsTpl->assign('has_ended', $hasEnded);
            $xoopsTpl->assign('polltype', $poll_obj->getVar('polltype'));
            switch ($poll_obj->getVar('polltype')) {
                case 1: //always visible
                    $visibleMsg = '';
                    $isVisible  = true;
                    break;
                case 2: //not visible
                    $visibleMsg = _PL_FULLBLIND;
                    $isVisible  = false;
                    break;
                case 3:  //visible after end of poll
                    $visibleMsg = _PL_HALFBLIND;
                    $isVisible  = $hasEnded;
                    break;
            }

            $hasvoted = 0;
            if ($uid > 0) {
                if ((new UmfrageLog())->hasVoted($topic_obj->getVar('poll_id'), xoops_getenv('REMOTE_ADDR'), $uid)) {
                    $hasvoted = 1;
                }
            } else {
                $hasvoted = 1;  //does not allow anon voting
            }

            $xoopsTpl->assign(
                [
                    'is_visible'      => $isVisible,
                    'visible_message' => $visibleMsg,
                    'hasVoted'        => $hasvoted,
                    'lang_vote'       => _PL_VOTE,
                    'lang_results'    => ($poll_obj->getVar('end_time') < time()) ? _PL_RESULTS : _PL_STANDINGS,
                ]
            );

            if ((new UmfrageLog())->hasVoted($topic_obj->getVar('poll_id'), $_SERVER['REMOTE_ADDR'], $uid)
                || $poll_obj->getVar('end_time') < time()) {
                $renderer->assignResults($xoopsTpl);
                //pollresults($forumtopic->getVar('poll_id'));
                $xoopsTpl->assign('topic_pollresult', 1);
                setcookie('bb_polls[' . $topic_obj->getVar('poll_id') . ']', 1);
            } else {
                $renderer->assignForm($xoopsTpl);
                //pollview($forumtopic->getVar('poll_id'));
                setcookie('bb_polls[' . $topic_obj->getVar('poll_id') . ']', 1);
            }
        }
    }

    if ($topicHandler->getPermission($forum_obj, $topic_obj->getVar('topic_status'), 'addpoll')) {
        if (!$topic_obj->getVar('topic_haspoll')) {
            if (($xoopsUser instanceof XoopsUser) && $xoopsUser->getVar('uid') === $topic_obj->getVar('topic_poster')) {
                $t_poll = newbb_displayImage('t_poll', _MD_ADDPOLL);
                $xoopsTpl->assign('forum_addpoll', '<a href="' . XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . "/polls.php?op=add&amp;topic_id={$topic_id}\">{$t_poll}</a>");
            }
        } elseif ($isadmin
                  || (is_object($poll) && ($xoopsUser instanceof XoopsUser)
                      && $xoopsUser->getVar('uid') === $poll_obj->getVar('user_id'))) {
            $poll_edit    = '';
            $poll_delete  = '';
            $poll_restart = '';

            $adminpoll_actions                = [];
            $adminpoll_actions['editpoll']    = [
                'link'  => XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/polls.php?op=edit&amp;poll_id=' . $topic_obj->getVar('poll_id') . '&amp;topic_id=' . $topic_id,
                'image' => $poll_edit,
                'name'  => _MD_EDITPOLL,
            ];
            $adminpoll_actions['deletepoll']  = [
                'link'  => XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/polls.php?op=delete&amp;poll_id=' . $topic_obj->getVar('poll_id') . '&amp;topic_id=' . $topic_id,
                'image' => $poll_delete,
                'name'  => _MD_DELETEPOLL,
            ];
            $adminpoll_actions['restartpoll'] = [
                'link'  => XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/polls.php?op=restart&amp;poll_id=' . $topic_obj->getVar('poll_id') . '&amp;topic_id=' . $topic_id . '&amp;forum=' . $forum_id,
                'image' => $poll_restart,
                'name'  => _MD_RESTARTPOLL,
            ];

            $xoopsTpl->assign_by_ref('adminpoll_actions', $adminpoll_actions);
        }
    }
    if (isset($poll_obj)) {
        unset($poll_obj);
    }
}
$xoopsTpl->assign(
    [
        'p_up'             => newbb_displayImage('up', _MD_TOP),
        'rating_enable'    => $GLOBALS['xoopsModuleConfig']['rating_enabled'],
        'groupbar_enable'  => $GLOBALS['xoopsModuleConfig']['groupbar_enabled'],
        'anonymous_prefix' => $GLOBALS['xoopsModuleConfig']['anonymous_prefix'],
        // irmtfan add alt for prev next and down icons.
        'previous'         => newbb_displayImage('previous', _MD_PREVTOPIC),
        'next'             => newbb_displayImage('next', _MD_NEXTTOPIC),
        'down'             => newbb_displayImage('down', _MD_BOTTOM),
        'post_content'     => newbb_displayImage('post'),
    ]
);

if (!empty($GLOBALS['xoopsModuleConfig']['rating_enabled'])) {
    $xoopsTpl->assign('votes', $topic_obj->getVar('votes'));
    $rating = number_format($topic_obj->getVar('rating') / 2, 0);
    if ($rating < 1) {
        $rating_img = newbb_displayImage('blank');
    } else {
        // irmtfan - add alt key for rating
        $rating_img = newbb_displayImage('rate' . $rating, constant('_MD_RATE' . $rating));
    }
    $xoopsTpl->assign(
        [
            'rating_img' => $rating_img,
            'rate1'      => newbb_displayImage('rate1', _MD_RATE1),
            'rate2'      => newbb_displayImage('rate2', _MD_RATE2),
            'rate3'      => newbb_displayImage('rate3', _MD_RATE3),
            'rate4'      => newbb_displayImage('rate4', _MD_RATE4),
            'rate5'      => newbb_displayImage('rate5', _MD_RATE5),
        ]
    );
}

// create jump box
if (!empty($GLOBALS['xoopsModuleConfig']['show_jump'])) {
    mod_loadFunctions('forum', 'newbb');
    $xoopsTpl->assign('forum_jumpbox', newbb_make_jumpbox($forum_id));
}

$xoopsTpl->assign(
    [
        'lang_forum_index' => sprintf(_MD_FORUMINDEX, htmlspecialchars($xoopsConfig['sitename'], ENT_QUOTES)),
        'lang_from'        => _MD_FROM,
        'lang_joined'      => _MD_JOINED,
        'lang_posts'       => _MD_POSTS,
        'lang_poster'      => _MD_POSTER,
        'lang_thread'      => _MD_THREAD,
        'lang_edit'        => _EDIT,
        'lang_delete'      => _DELETE,
        'lang_reply'       => _REPLY,
        'lang_postedon'    => _MD_POSTEDON,
        'lang_groups'      => _MD_GROUPS,
    ]
);

$viewmode_options = [];
if ('DESC' === $order) {
    $viewmode_options[] = [
        'link'  => $GLOBALS['xoops']->url('modules/' . $xoopsModule->getVar('dirname', 'n') . "/viewtopic.php?order=ASC&amp;status={$status}&amp;topic_id={$topic_id}"),
        'title' => _OLDESTFIRST,
    ];
} else {
    $viewmode_options[] = [
        'link'  => $GLOBALS['xoops']->url('modules/' . $xoopsModule->getVar('dirname', 'n') . "/viewtopic.php?order=DESC&amp;status=$status&amp;topic_id={$topic_id}"),
        'title' => _NEWESTFIRST,
    ];
}

switch ($status) {
    case 'active':
        $current_status = '[' . _MD_TYPE_ADMIN . ']';
        break;
    case 'pending':
        $current_status = '[' . _MD_TYPE_PENDING . ']';
        break;
    case 'deleted':
        $current_status = '[' . _MD_TYPE_DELETED . ']';
        break;
    default:
        $current_status = '';
        break;
}
$xoopsTpl->assign(
    [
        'topicstatus'      => $current_status,
        'mode'             => $mode,
        'status'           => $status,
        'viewmode_options' => $viewmode_options,
        'menumode'         => $menumode,
        'menumode_other'   => $menumode_other,
    ]
);
//$xoopsTpl->assign('viewmode_compact', ($viewmode=="compact")?1:0);
// changed to assign, assign_by_ref not supported under PHP 5.x
//$xoopsTpl->assign_by_ref('viewmode_options', $viewmode_options);
//unset($viewmode_options);

// START irmtfan add verifyUser to quick reply
//check banning
$moderateHandler = Helper::getInstance()->getHandler('Moderate');
if (!empty($GLOBALS['xoopsModuleConfig']['quickreply_enabled'])
    && $topicHandler->getPermission($forum_obj, $topic_obj->getVar('topic_status'), 'reply')
    && !$moderateHandler->verifyUser(-1, '', $forum_obj->getVar('forum_id'))) {
    // END irmtfan add verifyUser to quick reply
    $forum_form = new XoopsThemeForm(_MD_POSTREPLY, 'quick_reply', $GLOBALS['xoops']->url('modules/' . $xoopsModule->getVar('dirname', 'n') . '/post.php'), 'post', true);
    if (!$xoopsUser instanceof XoopsUser) {
        ///** @var \XoopsConfigHandler $configHandler */
        $configHandler = xoops_getHandler('config');
        $user_tray     = new XoopsFormElementTray(_MD_ACCOUNT);
        $user_tray->addElement(new XoopsFormText(_MD_NAME, 'uname', 26, 255));
        $user_tray->addElement(new XoopsFormPassword(_MD_PASSWORD, 'pass', 10, 32));
        $login_checkbox = new XoopsFormCheckBox('', 'login', 1);
        $login_checkbox->addOption(1, _MD_LOGIN);
        $user_tray->addElement($login_checkbox);
        $forum_form->addElement($user_tray);
        $captcha = new XoopsFormCaptcha('', "topic_{$topic_id}_{$start}");
        $captcha->setConfig('mode', 'text');
        $forum_form->addElement($captcha);
    }

    //    $quickform = ( !empty($GLOBALS['xoopsModuleConfig']["editor_default"]) ) ? $GLOBALS['xoopsModuleConfig']["editor_default"] : "textarea";
    $quickform      = (!empty($GLOBALS['xoopsModuleConfig']['editor_quick_default'])) ? $GLOBALS['xoopsModuleConfig']['editor_quick_default'] : 'textarea';
    $editor_configs = [];
    //    $editor_configs ["value"]  = $message;
    $editor_configs ['name']   = 'message';
    $editor_configs ['rows']   = empty($GLOBALS['xoopsModuleConfig']['editor_rows']) ? 10 : $GLOBALS['xoopsModuleConfig']['editor_rows'];
    $editor_configs ['cols']   = empty($GLOBALS['xoopsModuleConfig']['editor_cols']) ? 30 : $GLOBALS['xoopsModuleConfig']['editor_cols'];
    $editor_configs ['width']  = empty($GLOBALS['xoopsModuleConfig']['editor_width']) ? '100%' : $GLOBALS['xoopsModuleConfig']['editor_width'];
    $editor_configs ['height'] = empty($GLOBALS['xoopsModuleConfig']['editor_height']) ? '400px' : $GLOBALS['xoopsModuleConfig']['editor_height'];
    $_editor                   = new XoopsFormEditor(_MD_MESSAGEC, $quickform, $editor_configs, true);
    $forum_form->addElement($_editor, true);

    $forum_form->addElement(new XoopsFormHidden('dohtml', 0));
    $forum_form->addElement(new XoopsFormHidden('dosmiley', 1));
    $forum_form->addElement(new XoopsFormHidden('doxcode', 1));
    $forum_form->addElement(new XoopsFormHidden('dobr', 1));
    $forum_form->addElement(new XoopsFormHidden('attachsig', 1));

    $forum_form->addElement(new XoopsFormHidden('isreply', 1));

    $forum_form->addElement(new XoopsFormHidden('subject', _MD_RE . ': ' . $topic_obj->getVar('topic_title', 'e')));
    $forum_form->addElement(new XoopsFormHidden('pid', empty($post_id) ? $topicHandler->getTopPostId($topic_id) : $post_id));
    $forum_form->addElement(new XoopsFormHidden('topic_id', $topic_id));
    $forum_form->addElement(new XoopsFormHidden('forum', $forum_id));
    //    $forum_form->addElement(new XoopsFormHidden('viewmode', $viewmode));
    $forum_form->addElement(new XoopsFormHidden('order', $order));
    $forum_form->addElement(new XoopsFormHidden('start', $start));

    $forum_form->addElement(new XoopsFormHidden('notify', -1));
    $forum_form->addElement(new XoopsFormHidden('contents_submit', 1));

    $submit_button = new XoopsFormButton('', 'quick_submit', _SUBMIT, 'submit');
    $submit_button->setExtra('onclick="if (document.forms.quick_reply.message.value == \'RE\' || document.forms.quick_reply.message.value == \'\') { alert(\'' . _MD_QUICKREPLY_EMPTY . '\'); return false;} else { return true;}"');
    $forum_form->addElement($submit_button);

    $toggles = newbb_getcookie('G', true);
    // START irmtfan improve quickreply smarty variable - add alt key to quick reply button - change $display to $style for more comprehension - add toggle $quickreply['expand']
    $quickreply           = [];
    $qr_collapse          = 't_qr';
    $qr_expand            = 't_qr_expand'; // change this
    $quickreply['icon']   = [
        'expand'   => $iconHandler->getImageSource($qr_expand),
        'collapse' => $iconHandler->getImageSource($qr_collapse),
    ];
    $quickreply['show']   = 1; // = !empty($GLOBALS['xoopsModuleConfig']['quickreply_enabled']
    $quickreply['expand'] = (count($toggles) > 0) ? (in_array('qr', $toggles) ? false : true) : true;
    if ($quickreply['expand']) {
        $quickreply['style']     = 'block';        //irmtfan move semicolon
        $quickreply_icon_display = $qr_expand;
        $quickreply_alt          = _MD_NEWBB_HIDE . ' ' . _MD_QUICKREPLY;
    } else {
        $quickreply['style']     = 'none';        //irmtfan move semicolon
        $quickreply_icon_display = $qr_collapse;
        $quickreply_alt          = _MD_NEWBB_SEE . ' ' . _MD_QUICKREPLY;
    }
    $quickreply['displayImage'] = newbb_displayImage($quickreply_icon_display, $quickreply_alt);
    $quickreply['form']         = $forum_form->render();
    $xoopsTpl->assign('quickreply', $quickreply);
    // END irmtfan improve quickreply smarty variable
    unset($forum_form);
} else {
    $xoopsTpl->assign('quickreply', ['show' => 0]);
}

if ($GLOBALS['xoopsModuleConfig']['do_tag']) {
    // now make sure tag module exists and is active
    /** @var XoopsModuleHandler $moduleHandler */
    /** @var \XoopsModuleHandler $moduleHandler */
    $moduleHandler = xoops_getHandler('module');
    $tagModule     = $moduleHandler->getByDirname('tag');
    if ($tagModule instanceof XoopsModule && $tagModule->isactive()) {
        @require_once $GLOBALS['xoops']->path('modules/tag/include/tagbar.php');
        $xoopsTpl->assign('tagbar', tagBar($topic_obj->getVar('topic_tags', 'n')));
    } else {
        $xoopsTpl->assign('tagbar', '');
    }
} else {
    $xoopsTpl->assign('tagbar', '');
}
// irmtfan move to footer.php
require_once __DIR__ . '/footer.php';
include $GLOBALS['xoops']->path('footer.php');
$xoopsLogger->stopTime('newBB_viewtopic');
