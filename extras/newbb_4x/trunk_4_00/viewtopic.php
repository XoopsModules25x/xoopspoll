<?php declare(strict_types=1);

/**
 * Newbb module
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       XOOPS Project (https://xoops.org)
 * @license         GNU GPL 2.0 or later (https://www.gnu.org/licenses/gpl-2.0.html)
 * @since           4.0
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 */

use XoopsModules\Newbb\Helper;

require __DIR__ . '/header.php';
require_once XOOPS_ROOT_PATH . '/modules/newbb/include/functions.read.php';
require_once XOOPS_ROOT_PATH . '/modules/newbb/include/functions.render.php';

/*
 * Build the page query
 */
$query_vars  = ['post_id', 'topic_id', 'status', 'order', 'start', 'move', 'mode', 'viewmode'];
$query_array = [];
foreach ($query_vars as $var) {
    if (empty($_GET[$var])) {
        continue;
    }
    $query_array[$var] = "{$var}={$_GET[$var]}";
}
$page_query = htmlspecialchars(implode('&', $query_array), ENT_QUOTES | ENT_HTML5);
unset($query_array);

$topic_id = isset($_GET['topic_id']) ? (int)$_GET['topic_id'] : 0;
$post_id  = !empty($_GET['post_id']) ? (int)$_GET['post_id'] : 0;
$forum_id = !empty($_GET['forum']) ? (int)$_GET['forum'] : 0;
$move     = isset($_GET['move']) ? mb_strtolower($_GET['move']) : '';
$start    = !empty($_GET['start']) ? (int)$_GET['start'] : 0;
$status   = (!empty($_GET['status'])
             && in_array($_GET['status'], ['active', 'pending', 'deleted'], true)) ? $_GET['status'] : '';
$mode     = !empty($_GET['mode']) ? (int)$_GET['mode'] : (!empty($status) ? 2 : 0);

if (!$topic_id && !$post_id) {
    $redirect = empty($forum_id) ? 'index.php' : "viewforum.php?forum={$forum_id}";
    redirect_header($redirect, 2, _MD_ERRORTOPIC);
}

/** @var NewbbTopicHandler $topicHandler */
$topicHandler = Helper::getInstance()->getHandler('Topic');
if (!empty($post_id)) {
    $topic_obj = $topicHandler->getByPost($post_id);
} elseif (!empty($move)) {
    $topic_obj = $topicHandler->getByMove($topic_id, ('prev' === $move) ? -1 : 1, $forum_id);
    $topic_id  = $topic_obj->getVar('topic_id');
} else {
    $topic_obj = $topicHandler->get($topic_id);
}
if (!is_object($topic_obj) || !$topic_id = $topic_obj->getVar('topic_id')) {
    $redirect = empty($forum_id) ? 'index.php' : "viewforum.php?forum={$forum_id}";
    redirect_header($redirect, 2, _MD_ERRORTOPIC);
}
$forum_id = $topic_obj->getVar('forum_id');
/** @var NewbbForumHandler $forumHandler */
$forumHandler = Helper::getInstance()->getHandler('Forum');
$forum_obj    = $forumHandler->get($forum_id);

$isadmin = newbb_isAdmin($forum_obj);

if (!$isadmin && $topic_obj->getVar('approved') < 0) {
    redirect_header('viewforum.php?forum=' . $forum_id, 2, _MD_NORIGHTTOVIEW);
}
if (!$forumHandler->getPermission($forum_obj)) {
    redirect_header('index.php', 2, _MD_NORIGHTTOACCESS);
}
/* Only admin has access to admin mode */
if (!$isadmin) {
    $status = '';
    $mode   = 0;
}
if ($mode) {
    $_GET['viewmode'] = 'flat';
}

if (!$topicHandler->getPermission($forum_obj, $topic_obj->getVar('topic_status'), 'view')) {
    redirect_header("viewforum.php?forum={$forum_id}", 2, _MD_NORIGHTTOVIEW);
}

$karmaHandler = Helper::getInstance()->getHandler('Karma');
$user_karma   = $karmaHandler->getUserKarma();

$valid_modes     = $xoopsModuleConfig['valid_viewmodes'];
$viewmode_cookie = newbb_getcookie('V');
if (isset($_GET['viewmode']) && in_array($_GET['viewmode'], $valid_modes, true)) {
    newbb_setcookie('V', $_GET['viewmode'], $forumCookie['expire']);
}
$viewmode = $_GET['viewmode'] ?? (!empty($viewmode_cookie) ? $viewmode_cookie : @$valid_modes[$xoopsModuleConfig['view_mode'] - 1]);
$viewmode = @in_array($viewmode, $valid_modes, true) ? $viewmode : $valid_modes[0];
$order    = (isset($_GET['order'])
             && in_array(mb_strtoupper($_GET['order']), ['DESC', 'ASC'], true)) ? $_GET['order'] : 'ASC';

$total_posts = $topicHandler->getPostCount($topic_obj, $status);

$xoopsLogger->startTime('XOOPS output module - topic - post');

if ('thread' === $viewmode) {
    $GLOBALS['xoopsOption']['template_main'] = 'newbb_viewtopic_thread.tpl';
    if (!empty($xoopsModuleConfig['posts_for_thread']) && $total_posts > $xoopsModuleConfig['posts_for_thread']) {
        redirect_header("viewtopic.php?topic_id={$topic_id}&amp;viewmode=flat", 2, _MD_EXCEEDTHREADVIEW);
    }
    $postsArray = $topicHandler->getAllPosts($topic_obj, $order, $total_posts, $start, 0, $status);
} else {
    $GLOBALS['xoopsOption']['template_main'] = 'newbb_viewtopic_flat.tpl';
    $postsArray                              = $topicHandler->getAllPosts($topic_obj, $order, $xoopsModuleConfig['posts_per_page'], $start, $post_id, $status);
}

$xoopsLogger->stopTime('XOOPS output module - topic - post');

$topic_obj->incrementCounter();
newbb_setRead('topic', $topic_id, $topic_obj->getVar('topic_last_post_id'));

if (!empty($xoopsModuleConfig['rss_enable'])) {
    $xoops_module_header .= '<link rel="alternate" type="application/rss+xml" title="' . $xoopsModule->getVar('name') . '-' . $forum_obj->getVar('forum_name') . '" href="' . XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . '/rss.php?f=' . $forum_obj->getVar('forum_id') . '">';
}
$xoops_pagetitle = $topic_obj->getVar('topic_title') . ' [' . $xoopsModule->getVar('name') . ' - ' . $forum_obj->getVar('forum_name') . ']';

$xoopsOption['xoops_pagetitle']     = $xoops_pagetitle;
$xoopsOption['xoops_module_header'] = $xoops_module_header;
require XOOPS_ROOT_PATH . '/header.php';

$xoopsLogger->startTime('XOOPS output module - topic');

$xoopsTpl->assign('xoops_pagetitle', $xoops_pagetitle);
$xoopsTpl->assign('xoops_module_header', $xoops_module_header);

if ($xoopsModuleConfig['wol_enabled']) {
    $onlineHandler = Helper::getInstance()->getHandler('Online');
    $onlineHandler->init($forum_obj, $topic_obj);
    $xoopsTpl->assign('online', $onlineHandler->showOnline());
}

$xoopsTpl->assign('parentforum', $forumHandler->getParents($forum_obj));

$xoopsTpl->assign(
    [
        'topic_title'    => '<a href="' . XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/viewtopic.php?topic_id=' . $topic_id . '">' . $topic_obj->getFullTitle() . '</a>',
        'forum_name'     => $forum_obj->getVar('forum_name'),
        'lang_nexttopic' => _MD_NEXTTOPIC,
        'lang_prevtopic' => _MD_PREVTOPIC,
    ]
);

$categoryHandler = xoops_getModuleHandler('category');
$category_obj    = $categoryHandler->get($forum_obj->getVar('cat_id'), ['cat_title']);
$xoopsTpl->assign('category', ['id' => $forum_obj->getVar('cat_id'), 'title' => $category_obj->getVar('cat_title')]);

$xoopsTpl->assign('topic_id', $topic_id);
$xoopsTpl->assign('forum_id', $forum_id);

$order_current = ('DESC' === $order) ? 'DESC' : 'ASC';
$xoopsTpl->assign('order_current', $order_current);

$t_new   = newbb_displayImage('t_new', _MD_POSTNEW);
$t_reply = newbb_displayImage('t_reply', _MD_REPLY);

if ($topicHandler->getPermission($forum_obj, $topic_obj->getVar('topic_status'), 'post')) {
    $xoopsTpl->assign('forum_post_or_register', "<a href=\"newtopic.php?forum={$forum_id}\">{$t_new}</a>");
} elseif (!empty($GLOBALS['xoopsModuleConfig']['show_reg'])) {
    if ($topic_obj->getVar('topic_status')) {
        $xoopsTpl->assign('forum_post_or_register', _MD_TOPICLOCKED);
    } elseif (!is_object($xoopsUser)) {
        $xoopsTpl->assign('forum_post_or_register', '<a href="' . XOOPS_URL . '/user.php?xoops_redirect=' . htmlspecialchars($xoopsRequestUri, ENT_QUOTES | ENT_HTML5) . '">' . _MD_REGTOPOST . '</a>');
    }
} else {
    $xoopsTpl->assign('forum_post_or_register', '');
}
if ($topicHandler->getPermission($forum_obj, $topic_obj->getVar('topic_status'), 'reply')) {
    $xoopsTpl->assign('forum_reply', "<a href=\"reply.php?topic_id={$topic_id}\">{$t_reply}</a>");
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
    //$user_criteria = "(" . implode(",", $userid_array) . ")";
    $users = $memberHandler->getUsers(new Criteria('uid', '(' . implode(',', $userid_array) . ')', 'IN'), true);
} else {
    $users = [];
}

$xoopsLogger->startTime('XOOPS output module - topic - user');

$xoopsLogger->startTime('XOOPS output module - topic - user - user');
$viewtopic_users = [];
if (count($userid_array) > 0) {
    require_once XOOPS_ROOT_PATH . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/class/user.php';
    $userHandler         = new \NewbbUserHandler($xoopsModuleConfig['groupbar_enabled'], $xoopsModuleConfig['wol_enabled']);
    $userHandler->users  = $users;
    $userHandler->online = $online;
    $viewtopic_users     = $userHandler->getUsers();
}
unset($users);
$xoopsLogger->stopTime('XOOPS output module - topic - user - user');

$xoopsLogger->stopTime('XOOPS output module - topic - user');

if ($xoopsModuleConfig['allow_require_reply'] && $require_reply) {
    if (!empty($xoopsModuleConfig['cache_enabled'])) {
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

$xoopsLogger->startTime('XOOPS output module - topic - assign');

if ('thread' === $viewmode) {
    if (!empty($post_id)) {
        $postHandler = Helper::getInstance()->getHandler('Post');
        $currentPost = $postHandler->get($post_id);

        if (!$isadmin && $currentPost->getVar('approved') < 0) {
            redirect_header('viewtopic.php?topic_id=' . $topic_id, 2, _MD_NORIGHTTOVIEW);
        }

        $top_pid = $topicHandler->getTopPostId($topic_id);
    } else {
        $currentPost = $topicHandler->getTopPost($topic_id);
        $top_pid     = $currentPost->getVar('post_id');
    }

    $xoopsTpl->append('topic_posts', $currentPost->showPost($isadmin));

    $postArray = $topicHandler->getPostTree($postsArray);
    if (count($postArray) > 0) {
        foreach ($postArray as $treeItem) {
            $topicHandler->showTreeItem($topic_obj, $treeItem);
            if ($treeItem['post_id'] === $post_id) {
                $treeItem['subject'] = '<strong>' . $treeItem['subject'] . '</strong>';
            }
            $xoopsTpl->append(
                'topic_trees',
                [
                    'post_id'     => $treeItem['post_id'],
                    'post_time'   => $treeItem['post_time'],
                    'post_image'  => $treeItem['icon'],
                    'post_title'  => $treeItem['subject'],
                    'post_prefix' => $treeItem['prefix'],
                    'poster'      => $treeItem['poster'],
                ]
            );
        }
        unset($postArray);
    }
} else {
    foreach ($postsArray as $eachpost) {
        $xoopsTpl->append('topic_posts', $eachpost->showPost($isadmin));
    }

    if ($total_posts > $xoopsModuleConfig['posts_per_page']) {
        require XOOPS_ROOT_PATH . '/class/pagenav.php';
        $nav = new XoopsPageNav($total_posts, $xoopsModuleConfig['posts_per_page'], $start, 'start', 'topic_id=' . $topic_id . '&amp;viewmode=' . $viewmode . '&amp;order=' . $order . '&amp;status=' . $status . '&amp;mode=' . $mode);
        $xoopsTpl->assign('forum_page_nav', $nav->renderNav(4));
    } else {
        $xoopsTpl->assign('forum_page_nav', '');
    }
}
unset($postsArray);
$xoopsLogger->stopTime('XOOPS output module - topic - assign');

$xoopsTpl->assign('topic_print_link', "print.php?form=1&amp;{$page_query}");

$admin_actions = [];

$ad_merge    = '';
$ad_move     = '';
$ad_delete   = '';
$ad_lock     = '';
$ad_unlock   = '';
$ad_sticky   = '';
$ad_unsticky = '';
$ad_digest   = '';
$ad_undigest = '';

$link_string             = XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/topicmanager.php?mode=%s&amp;topic_id=' . $topic_id;
$admin_actions['merge']  = [
    'link'  => sprintf($link_string, 'merge'),
    'name'  => _MD_MERGETOPIC,
    'image' => $ad_merge,
];
$admin_actions['move']   = [
    'link'  => sprintf($link_string, 'move'),
    'name'  => _MD_MOVETOPIC,
    'image' => $ad_move,
];
$admin_actions['delete'] = [
    'link'  => sprintf($link_string, 'delete'),
    'name'  => _MD_DELETETOPIC,
    'image' => $ad_delete,
];
if (!$topic_obj->getVar('topic_status')) {
    $admin_actions['lock'] = [
        'link'  => sprintf($link_string, 'lock'),
        'image' => $ad_lock,
        'name'  => _MD_LOCKTOPIC,
    ];
} else {
    $admin_actions['unlock'] = [
        'link'  => sprintf($link_string, 'unlock'),
        'image' => $ad_unlock,
        'name'  => _MD_UNLOCKTOPIC,
    ];
}
if (!$topic_obj->getVar('topic_sticky')) {
    $admin_actions['sticky'] = [
        'link'  => sprintf($link_string, 'sticky'),
        'image' => $ad_sticky,
        'name'  => _MD_STICKYTOPIC,
    ];
} else {
    $admin_actions['unsticky'] = [
        'link'  => sprintf($link_string, 'unsticky'),
        'image' => $ad_unsticky,
        'name'  => _MD_UNSTICKYTOPIC,
    ];
}
if (!$topic_obj->getVar('topic_digest')) {
    $admin_actions['digest'] = [
        'link'  => sprintf($link_string, 'digest'),
        'image' => $ad_digest,
        'name'  => _MD_DIGESTTOPIC,
    ];
} else {
    $admin_actions['undigest'] = [
        'link'  => sprintf($link_string, 'undigest'),
        'image' => $ad_undigest,
        'name'  => _MD_UNDIGESTTOPIC,
    ];
}
$xoopsTpl->assignByRef('admin_actions', $admin_actions);

$xoopsTpl->assign('viewer_level', $isadmin ? 2 : is_object($xoopsUser));

if ($xoopsModuleConfig['show_permissiontable']) {
    $permissionHandler = Helper::getInstance()->getHandler('Permission');
    $permission_table  = $permissionHandler->getPermissionTable($forum_obj, $topic_obj->getVar('topic_status'), $isadmin);
    $xoopsTpl->assignByRef('permission_table', $permission_table);
}

///////////////////////////////
// show Poll
/** @var XoopsModuleHandler $moduleHandler */
/** @var \XoopsModuleHandler $moduleHandler */
$moduleHandler = xoops_getHandler('module');
$xoopspoll     = $moduleHandler->getByDirname('xoopspoll');
if (($xoopspoll instanceof XoopsModule) && $xoopspoll->isactive()) {
    if (($topic_obj->getVar('topic_haspoll')
         && $topicHandler->getPermission($forum_obj, $topic_obj->getVar('topic_status'), 'vote'))
        || $topicHandler->getPermission($forum_obj, $topic_obj->getVar('topic_status'), 'addpoll')) {
        xoops_load('renderer', 'xoopspoll');
        xoops_loadLanguage('main', 'xoopspoll');
    }

    if ($topic_obj->getVar('topic_haspoll') && (0 !== $topic_obj->getVar('poll_id'))
        // double check to make sure it's a non-zero poll
        && $topicHandler->getPermission($forum_obj, $topic_obj->getVar('topic_status'), 'vote')) {
        $GLOBALS['xoopsTpl']->assign('topic_poll', 1);
        $GLOBALS['xoopsTpl']->assign('pollmodules', $pollmodules);
        $uid = ($GLOBALS['xoopsUser'] instanceof XoopsUser) ? $GLOBALS['xoopsUser']->getVar('uid') : 0;

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
            //check to see if user has voted, show form if not, otherwise get results for form
            $logHandler = xoops_getModuleHandler('log', 'xoopspoll');
            if ($poll_obj->isAllowedToVote()
                && (!$logHandler->hasVoted($poll_obj->getVar('poll_id'), xoops_getenv('REMOTE_ADDR'), $uid))) {
                $myTpl = new XoopsTpl();
                $renderer->assignForm($myTpl);
                $myTpl->assign('action', $GLOBALS['xoops']->url("modules/newbb/votepolls.php?topic_id={$topic_id}&amp;poll_id={$poll_id}"));
                $topic_pollform = $myTpl->fetch($GLOBALS['xoops']->path('modules/xoopspoll/templates/xoopspoll_view.tpl'));
                $GLOBALS['xoopsTpl']->assign('topic_pollform', $topic_pollform);
            } else {
                $GLOBALS['xoopsTpl']->assign('can_vote', false);
                $GLOBALS['xoopsTpl']->assign('topic_pollresult', $renderer->renderResults());
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

            $xoopsTpl->assignByRef('adminpoll_actions', $adminpoll_actions);
        }
    }
    if (isset($poll_obj)) {
        unset($poll_obj);
    }
}
$xoopsTpl->assign('up', newbb_displayImage('up', _MD_TOP));
$xoopsTpl->assign('rating_enable', $xoopsModuleConfig['rating_enabled']);
$xoopsTpl->assign('groupbar_enable', $xoopsModuleConfig['groupbar_enabled']);
$xoopsTpl->assign('anonymous_prefix', $xoopsModuleConfig['anonymous_prefix']);

$xoopsTpl->assign('previous', newbb_displayImage('previous'));
$xoopsTpl->assign('next', newbb_displayImage('next'));
$xoopsTpl->assign('down', newbb_displayImage('down'));
$xoopsTpl->assign('post_content', newbb_displayImage('post'));

if (!empty($xoopsModuleConfig['rating_enabled'])) {
    $xoopsTpl->assign('votes', $topic_obj->getVar('votes'));
    $rating = number_format($topic_obj->getVar('rating') / 2, 0);
    if ($rating < 1) {
        $rating_img = newbb_displayImage('blank');
    } else {
        $rating_img = newbb_displayImage('rate' . $rating);
    }
    $xoopsTpl->assign('rating_img', $rating_img);
    $xoopsTpl->assign('rate1', newbb_displayImage('rate1', _MD_RATE1));
    $xoopsTpl->assign('rate2', newbb_displayImage('rate2', _MD_RATE2));
    $xoopsTpl->assign('rate3', newbb_displayImage('rate3', _MD_RATE3));
    $xoopsTpl->assign('rate4', newbb_displayImage('rate4', _MD_RATE4));
    $xoopsTpl->assign('rate5', newbb_displayImage('rate5', _MD_RATE5));
}

// create jump box
if (!empty($xoopsModuleConfig['show_jump'])) {
    require_once XOOPS_ROOT_PATH . '/modules/newbb/include/functions.forum.php';
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
if ('thread' === $viewmode) {
    $viewmode_options[] = [
        'link'  => XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/viewtopic.php?viewmode=flat&amp;topic_id=' . $topic_id,
        'title' => _FLAT,
    ];
    $viewmode_options[] = [
        'link'  => XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/viewtopic.php?viewmode=compact&amp;topic_id=' . $topic_id,
        'title' => _MD_COMPACT,
    ];
} elseif ('compact' === $viewmode) {
    $viewmode_options[] = [
        'link'  => XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/viewtopic.php?viewmode=thread&amp;topic_id=' . $topic_id,
        'title' => _THREADED,
    ];
    $viewmode_options[] = [
        'link'  => XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/viewtopic.php?viewmode=flat&amp;order={$order_current}&amp;topic_id=' . $topic_id,
        'title' => _FLAT,
    ];
    if ('DESC' === $order) {
        $viewmode_options[] = [
            'link'  => XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/viewtopic.php?viewmode=compact&amp;order=ASC&amp;topic_id=' . $topic_id,
            'title' => _OLDESTFIRST,
        ];
    } else {
        $viewmode_options[] = [
            'link'  => XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/viewtopic.php?viewmode=compact&amp;order=DESC&amp;topic_id=' . $topic_id,
            'title' => _NEWESTFIRST,
        ];
    }
} else {
    $viewmode_options[] = [
        'link'  => XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/viewtopic.php?viewmode=thread&amp;topic_id=' . $topic_id,
        'title' => _THREADED,
    ];
    $viewmode_options[] = [
        'link'  => XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/viewtopic.php?viewmode=compact&amp;order={$order_current}&amp;topic_id=' . $topic_id,
        'title' => _MD_COMPACT,
    ];
    if ('DESC' === $order) {
        $viewmode_options[] = [
            'link'  => XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/viewtopic.php?viewmode=flat&amp;order=ASC&amp;status={$status}&amp;topic_id=' . $topic_id,
            'title' => _OLDESTFIRST,
        ];
    } else {
        $viewmode_options[] = [
            'link'  => XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/viewtopic.php?viewmode=flat&amp;order=DESC&amp;status={$status}&amp;topic_id=' . $topic_id,
            'title' => _NEWESTFIRST,
        ];
    }
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
$xoopsTpl->assign('topicstatus', $current_status);

$xoopsTpl->assign('mode', $mode);
$xoopsTpl->assign('status', $status);
$xoopsTpl->assign('viewmode_compact', ('compact' === $viewmode) ? 1 : 0);
$xoopsTpl->assignByRef('viewmode_options', $viewmode_options);
unset($viewmode_options);
$xoopsTpl->assign('menumode', $menumode);
$xoopsTpl->assign('menumode_other', $menumode_other);

$xoopsLogger->startTime('XOOPS output module - topic - quickreply');

if (!empty($xoopsModuleConfig['quickreply_enabled'])
    && $topicHandler->getPermission($forum_obj, $topic_obj->getVar('topic_status'), 'reply')) {
    require_once XOOPS_ROOT_PATH . '/class/xoopsform/formelement.php';
    require_once XOOPS_ROOT_PATH . '/class/xoopsform/formhidden.php';
    require_once XOOPS_ROOT_PATH . '/class/xoopsform/formhiddentoken.php';
    require_once XOOPS_ROOT_PATH . '/class/xoopsform/formbutton.php';
    require_once XOOPS_ROOT_PATH . '/class/xoopsform/themeform.php';
    require_once XOOPS_ROOT_PATH . '/class/xoopsform/formtextarea.php';
    if (!@require_once XOOPS_ROOT_PATH . '/class/xoopsform/formeditor.php') {
        require_once XOOPS_ROOT_PATH . '/Frameworks/compat/class/xoopsform/formeditor.php';
    }

    $xoopsLogger->startTime('XOOPS output module - topic - quickreply - form');
    $forum_form = new XoopsThemeForm(_MD_POSTREPLY, 'quick_reply', 'post.php', 'post', true);

    if (!is_object($xoopsUser)) {
        require_once XOOPS_ROOT_PATH . '/class/xoopsform/formpassword.php';
        require_once XOOPS_ROOT_PATH . '/class/xoopsform/formcheckbox.php';
        require_once XOOPS_ROOT_PATH . '/class/xoopsform/formtext.php';
        require_once XOOPS_ROOT_PATH . '/class/xoopsform/formelementtray.php';
        require_once XOOPS_ROOT_PATH . '/Frameworks/captcha/formcaptcha.php';
        /** @var \XoopsConfigHandler $configHandler */
        $configHandler = xoops_getHandler('config');
        $user_tray     = new XoopsFormElementTray(_MD_ACCOUNT);
        $user_tray->addElement(new XoopsFormText(_MD_NAME, 'uname', 26, 255));
        $user_tray->addElement(new XoopsFormPassword(_MD_PASSWORD, 'pass', 10, 32));
        $login_checkbox = new XoopsFormCheckBox('', 'login', 1);
        $login_checkbox->addOption(1, _MD_LOGIN);
        $user_tray->addElement($login_checkbox);
        $forum_form->addElement($user_tray);
        $captcha = new XoopsFormCaptcha('', 'topic_{$topic_id}_{$start}');
        $captcha->setConfig('mode', 'text');
        $forum_form->addElement($captcha);
    }

    $quickform                 = 'textarea';
    $editor_configs            = [];
    $editor_configs['caption'] = _MD_MESSAGEC;
    $editor_configs['name']    = 'message';
    $editor_configs['rows']    = 10;
    $editor_configs['cols']    = 60;
    $forum_form->addElement(new XoopsFormEditor(_MD_MESSAGEC, $quickform, $editor_configs, true), true);

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
    $forum_form->addElement(new XoopsFormHidden('viewmode', $viewmode));
    $forum_form->addElement(new XoopsFormHidden('order', $order));
    $forum_form->addElement(new XoopsFormHidden('start', $start));

    $forum_form->addElement(new XoopsFormHidden('notify', -1));
    $forum_form->addElement(new XoopsFormHidden('contents_submit', 1));

    $submit_button = new XoopsFormButton('', 'quick_submit', _SUBMIT, 'submit');
    $submit_button->setExtra('onclick="if (document.forms.quick_reply.message.value == \'RE\' || document.forms.quick_reply.message.value == \'\') { alert(\'' . _MD_QUICKREPLY_EMPTY . '\'); return false;} else { return true;}"');
    $forum_form->addElement($submit_button);

    $toggles = newbb_getcookie('G', true);
    $display = in_array('qr', $toggles, true) ? 'none;' : 'block;';
    $xoopsTpl->assign(
        'quickreply',
        [
            'show'    => 1,
            'display' => $display,
            'icon'    => newbb_displayImage('t_qr'),
            'form'    => $forum_form->render(),
        ]
    );
    unset($forum_form);
    $xoopsLogger->stopTime('XOOPS output module - topic - quickreply - form');
} else {
    $xoopsTpl->assign('quickreply', ['show' => 0]);
}
$xoopsLogger->stopTime('XOOPS output module - topic - quickreply');

$xoopsLogger->startTime('XOOPS output module - topic - tag');
if ($xoopsModuleConfig['do_tag'] && @require_once XOOPS_ROOT_PATH . '/modules/tag/include/tagbar.php') {
    $xoopsTpl->assign('tagbar', tagBar($topic_obj->getVar('topic_tags', 'n')));
}
$xoopsLogger->stopTime('XOOPS output module - topic - tag');

$xoopsLogger->startTime('XOOPS output module - topic - transfer');
if ($transferbar = @require XOOPS_ROOT_PATH . '/Frameworks/transfer/bar.transfer.php') {
    $xoopsTpl->assign('transfer', $transferbar);
}
$xoopsLogger->stopTime('XOOPS output module - topic - transfer');

$xoopsLogger->stopTime('XOOPS output module - topic');

require XOOPS_ROOT_PATH . '/footer.php';
