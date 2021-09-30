<?php
//
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                  Copyright (c) 2000-2016 XOOPS.org                        //
//                       <http://xoops.org>                             //
//  ------------------------------------------------------------------------ //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License as published by     //
//  the Free Software Foundation; either version 2 of the License, or        //
//  (at your option) any later version.                                      //
//                                                                           //
//  You may not change or alter any portion of this comment or credits       //
//  of supporting developers from this source code or any supporting         //
//  source code which is considered copyrighted (c) material of the          //
//  original comment or credit authors.                                      //
//                                                                           //
//  This program is distributed in the hope that it will be useful,          //
//  but WITHOUT ANY WARRANTY; without even the implied warranty of           //
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
//  GNU General Public License for more details.                             //
//                                                                           //
//  You should have received a copy of the GNU General Public License        //
//  along with this program; if not, write to the Free Software              //
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
//  ------------------------------------------------------------------------ //
//  Author: phppp (D.J., infomax@gmail.com)                                  //
//  URL: http://xoopsforge.com, http://xoops.org.cn                          //
//  Project: Article Project                                                 //
//  ------------------------------------------------------------------------ //
use XoopsModules\Newbb\Helper;

require_once __DIR__ . '/header.php';

if (isset($_POST['submit'])) {
    foreach (['forum', 'newforum', 'newtopic'] as $getint) {
        ${$getint} = isset($_POST[$getint]) ? (int)$_POST[$getint] : 0;
    }
    $topic_id = [];
    if (isset($_POST['topic_id']) && !is_array($_POST['topic_id'])) {
        $topic_id = [$topic_id];
    } else {
        $topic_id = $_POST['topic_id'];
    }
    foreach ($topic_id as $getint) {
        ${$getint} = (int)(@$_POST[$getint]);
    }
} else {
    foreach (['forum', 'topic_id'] as $getint) {
        ${$getint} = (int)(@$_GET[$getint]);
    }
}

if (!$topic_id) {
    $redirect = empty($forum_id) ? 'index.php' : "viewforum.php?forum={$forum}";
    $redirect = $GLOBALS['xoops']->url("modules/newbb/{$redirect}");
    redirect_header($redirect, 2, _MD_ERRORTOPIC);
}
/** @var NewbbTopicHandler $topicHandler */
$topicHandler = Helper::getInstance()->getHandler('Topic');
/** @var NewbbForumHandler $forumHandler */
$forumHandler = Helper::getInstance()->getHandler('Forum');

if (!$forum) {
    $topic_obj = $topicHandler->get((int)$topic_id);
    if (is_object($topic_obj)) {
        $forum = $topic_obj->getVar('forum_id');
    } else {
        $redirect = XOOPS_URL . '/modules/newbb/viewtopic.php?topic_id=' . $topic_id;
        redirect_header($redirect, 2, _MD_FORUMNOEXIST);
    }
    unset($topic_obj);
}

if ($xoopsModuleConfig['wol_enabled']) {
    $onlineHandler = Helper::getInstance()->getHandler('Online');
    $onlineHandler->init($forum);
}
// irmtfan add restore to viewtopic
$action_array = [
    'merge',
    'delete',
    'restore',
    'move',
    'lock',
    'unlock',
    'sticky',
    'unsticky',
    'digest',
    'undigest',
];
foreach ($action_array as $_action) {
    $action[$_action] = [
        'name'   => $_action,
        'desc'   => constant(strtoupper("_MD_DESC_{$_action}")),
        'submit' => constant(strtoupper("_MD_{$_action}")),
        'sql'    => "topic_{$_action}=1",
        'msg'    => constant(strtoupper("_MD_TOPIC{$_action}")),
    ];
}
$action['lock']['sql']     = 'topic_status = 1';
$action['unlock']['sql']   = 'topic_status = 0';
$action['unsticky']['sql'] = 'topic_sticky = 0';
$action['undigest']['sql'] = 'topic_digest = 0';
$action['digest']['sql']   = 'topic_digest = 1, digest_time = ' . time();

// Disable cache
$xoopsConfig['module_cache'][$xoopsModule->getVar('mid')] = 0;
// irmtfan include header.php after defining $xoopsOption['template_main']
require_once XOOPS_ROOT_PATH . '/header.php';

if (isset($_POST['submit'])) {
    $mode = $_POST['mode'];

    if ('delete' === $mode) {
        foreach ($topic_id as $tid) {
            $topic_obj = $topicHandler->get($tid);
            $topicHandler->delete($topic_obj, false);
            // irmtfan - sync topic after delete
            $topicHandler->synchronization($topic_obj);
            $forumHandler->synchronization($forum);
            //            $topic_obj->loadFilters("delete");
            //            sync($topic_id, "topic");
            //            xoops_notification_deletebyitem ($xoopsModule->getVar('mid'), 'thread', $topic_id);
        }
        // irmtfan full URL
        echo $action[$mode]['msg'] . "<p><a href='" . XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . "/viewforum.php?forum=$forum'>" . _MD_RETURNTOTHEFORUM . "</a></p><p><a href='index.php'>" . _MD_RETURNFORUMINDEX . '</a></p>';
    } elseif ('restore' === $mode) {
        // /** @var NewbbTopicHandler $topicHandler */
        $forums     = [];
        $topics_obj = $topicHandler->getAll(new Criteria('topic_id', '(' . implode(',', $topic_id) . ')', 'IN'));
        foreach (array_keys($topics_obj) as $id) {
            $topic_obj =& $topics_obj[$id];
            $topicHandler->approve($topic_obj);
            $topicHandler->synchronization($topic_obj);
            $forums[$topic_obj->getVar('forum_id')] = 1;
        }
        //irmtfan remove - no need to approve posts manually - see class/post.php approve function
        $criteria_forum = new Criteria('forum_id', '(' . implode(',', array_keys($forums)) . ')', 'IN');
        $forums_obj     = $forumHandler->getAll($criteria_forum);
        foreach (array_keys($forums_obj) as $id) {
            $forumHandler->synchronization($forums_obj[$id]);
        }
        unset($topics_obj, $forums_obj);
        // irmtfan add restore to viewtopic
        $restoretopic_id = $topic_obj->getVar('topic_id');
        // irmtfan / missing in URL
        echo $action[$mode]['msg']
             . "<p><a href='"
             . XOOPS_URL
             . '/modules/'
             . $xoopsModule->getVar('dirname')
             . "/viewtopic.php?topic_id=$restoretopic_id'>"
             . _MD_VIEWTHETOPIC
             . '</a></p>'
             . "<p><a href='"
             . XOOPS_URL
             . '/modules/'
             . $xoopsModule->getVar('dirname')
             . "/viewforum.php?forum=$forum'>"
             . _MD_RETURNTOTHEFORUM
             . '</a></p>'
             . "<p><a href='index.php'>"
             . _MD_RETURNFORUMINDEX
             . '</a></p>';
    } elseif ('merge' === $mode) {
        $pollmodul = null;
        /** @var XoopsModuleHandler $moduleHandler */
        /** @var \XoopsModuleHandler $moduleHandler */
        $moduleHandler = xoops_getHandler('module');
        $pollModule    = $moduleHandler->getByDirname('xoopspoll');
        if (($pollModule instanceof XoopsModule) && $pollModule->isactive()) {
            $pollmodul    = 'xoopspoll';
            $xpOptHandler = xoops_getModuleHandler('option', 'xoopspoll');
            $xpLogHandler = xoops_getModuleHandler('log', 'xoopspoll');
        } else {
            //Umfrage
            $pollModule = $moduleHandler->getByDirname('umfrage');
            if (($pollModule instanceof XoopsModule) && $pollModule->isactive()) {
                $pollmodul = 'umfrage';
            }
        }

        $postHandler = Helper::getInstance()->getHandler('Post');
        foreach ($topic_id as $tid) {
            $topic_obj    = $topicHandler->get($tid);
            $newtopic_obj = $topicHandler->get($newtopic);
            /* return false if destination topic is newer or not existing */
            /*
                        if ($newtopic>$tid || !is_object($newtopic_obj)) {
                            redirect_header("javascript:history.go(-1)", 2, _MD_ERROR);
                        }
            */
            $criteria_topic = new Criteria('topic_id', $tid);
            $criteria       = new CriteriaCompo($criteria_topic);
            $criteria->add(new Criteria('pid', 0));
            $postHandler->updateAll('pid', $topicHandler->getTopPostId($newtopic), $criteria, true);
            $postHandler->updateAll('topic_id', $newtopic, $criteria_topic, true);

            $topic_views       = $topic_obj->getVar('topic_views') + $newtopic_obj->getVar('topic_views');
            $criteria_newtopic = new Criteria('topic_id', $newtopic);
            $topicHandler->updateAll('topic_views', $topic_views, $criteria_newtopic, true);

            $topicHandler->synchronization($newtopic);

            $poll_id = $topicHandler->get($tid, 'poll_id');
            if ($poll_id > 0) {
                if ('xoopspoll' === $pollmodul) {
                    $xpPollHandler = xoops_getModuleHandler('poll', 'xoopspoll');
                    $poll          = $xpPollHandler->get($poll_id);
                    if (false !== $xpPollHandler->delete($poll)) {
                        $xpOptHandler->deleteByPollId($poll_id);
                        $xpLogHandler->deleteByPollId($poll_id);
                        xoops_comment_delete($xoopsModule->getVar('mid'), $poll_id);
                    }
                } elseif ('umfrage' === $pollmodul) {
                    require_once $GLOBALS['xoops']->path('modules/umfrage/class/umfrage.php');
                    require_once $GLOBALS['xoops']->path('modules/umfrage/class/umfrageoption.php');
                    require_once $GLOBALS['xoops']->path('modules/umfrage/class/umfragelog.php');
                    require_once $GLOBALS['xoops']->path('modules/umfrage/class/umfragerenderer.php');

                    $poll = new Umfrage($poll_id);
                    if ($poll->delete() !== false) {
                        (new UmfrageOption())->deleteByPollId($poll_id);
                        (new UmfrageLog())->deleteByPollId($poll_id);
                        xoops_comment_delete($xoopsModule->getVar('mid'), $poll_id);
                    }
                }
            }

            $sql    = sprintf('DELETE FROM %s WHERE topic_id = %u', $xoopsDB->prefix('bb_topics'), $tid);
            $result = $xoopsDB->queryF($sql);

            $sql    = sprintf('DELETE FROM %s WHERE topic_id = %u', $xoopsDB->prefix('bb_votedata'), $tid);
            $result = $xoopsDB->queryF($sql);

            $sql    = sprintf('UPDATE %s SET forum_topics = forum_topics-1 WHERE forum_id = %u', $xoopsDB->prefix('bb_forums'), $forum);
            $result = $xoopsDB->queryF($sql);

            $topic_obj->loadFilters('delete');
            $newtopic_obj->loadFilters('update');
        }
        // irmtfan full URL
        echo $action[$mode]['msg']
             . "<p><a href='"
             . XOOPS_URL
             . '/modules/'
             . $xoopsModule->getVar('dirname')
             . "/viewtopic.php?topic_id={$newtopic}'>"
             . _MD_VIEWTHETOPIC
             . '</a></p>'
             . "<p><a href='"
             . XOOPS_URL
             . '/modules/'
             . $xoopsModule->getVar('dirname')
             . "/viewforum.php?forum={$forum}'>"
             . _MD_RETURNTOTHEFORUM
             . '</a></p>'
             . "<p><a href='"
             . XOOPS_URL
             . '/modules/'
             . $xoopsModule->getVar('dirname')
             . "/index.php'>"
             . _MD_RETURNFORUMINDEX
             . '</a></p>';
    } elseif ('move' === $mode) {
        if ($newforum > 0) {
            $topic_id  = $topic_id[0];
            $topic_obj = $topicHandler->get($topic_id);
            $topic_obj->loadFilters('update');
            $topic_obj->setVar('forum_id', $newforum, true);
            $topicHandler->insert($topic_obj, true);
            $topic_obj->loadFilters('update');

            $sql = sprintf('UPDATE %s SET forum_id = %u WHERE topic_id = %u', $xoopsDB->prefix('bb_posts'), $newforum, $topic_id);
            if (!$r = $xoopsDB->query($sql)) {
                return false;
            }
            $forumHandler->synchronization($forum);
            $forumHandler->synchronization($newforum);
            // irmtfan full URL
            echo $action[$mode]['msg'] . "<p><a href='" . XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . "/viewtopic.php?topic_id=$topic_id&amp;forum=$newforum'>" . _MD_GOTONEWFORUM . "</a></p><p><a href='" . XOOPS_URL . "/modules/newbb/index.php'>" . _MD_RETURNFORUMINDEX . '</a></p>';
        } else {
            // irmtfan - issue with javascript:history.go(-1)
            redirect_header(\Xmf\Request::getString('HTTP_REFERER', '', 'SERVER'), 2, _MD_ERRORFORUM);
        }
    } else {
        $topic_id  = $topic_id[0];
        $forum     = $topicHandler->get($topic_id, 'forum_id');
        $forum_new = !empty($newtopic) ? $topicHandler->get($newtopic, 'forum_id') : 0;

        if (!$forumHandler->getPermission($forum, 'moderate')
            || (!empty($forum_new)
                && !$forumHandler->getPermission($forum_new, 'reply'))
            // The forum for the topic to be merged to
            || (!empty($newforum) && !$forumHandler->getPermission($newforum, 'post')) // The forum to be moved to
        ) {
            redirect_header(XOOPS_URL . "/modules/newbb/viewtopic.php?forum=$forum&amp;topic_id=$topic_id", 2, _NOPERM);
        }

        if (!empty($action[$mode]['sql'])) {
            $sql = sprintf('UPDATE %s SET ' . $action[$mode]['sql'] . ' WHERE topic_id = %u', $xoopsDB->prefix('bb_topics'), $topic_id);
            if (!$r = $xoopsDB->query($sql)) {
                redirect_header(XOOPS_URL . "/modules/newbb/viewtopic.php?forum=$forum&amp;topic_id=$topic_id&amp;order=$order&amp;viewmode=$viewmode", 2, _MD_ERROR_BACK . '<br>sql:' . $sql);
            }
        } else {
            redirect_header(XOOPS_URL . "/modules/newbb/viewtopic.php?forum=$forum&amp;topic_id=$topic_id", 2, _MD_ERROR_BACK);
        }
        if ('digest' === $mode && $xoopsDB->getAffectedRows()) {
            $topic_obj    = $topicHandler->get($topic_id);
            $statsHandler = Helper::getInstance()->getHandler('Stats');
            $statsHandler->update($topic_obj->getVar('forum_id'), 'digest');
            $userstatsHandler = Helper::getInstance()->getHandler('Userstats');
            if ($user_stat = $userstatsHandler->get($topic_obj->getVar('topic_poster'))) {
                $z = $user_stat->getVar('user_digests') + 1;
                $user_stat->setVar('user_digests', (int)$z);
                $userstatsHandler->insert($user_stat);
            }
        }
        // irmtfan full URL
        echo $action[$mode]['msg']
             . "<p><a href='"
             . XOOPS_URL
             . '/modules/'
             . $xoopsModule->getVar('dirname')
             . "/viewtopic.php?topic_id=$topic_id&amp;forum=$forum'>"
             . _MD_VIEWTHETOPIC
             . "</a></p><p><a href='"
             . XOOPS_URL
             . "/modules/newbb/viewforum.php?forum=$forum'>"
             . _MD_RETURNFORUMINDEX
             . '</a></p>';
    }
} else {  // No submit
    $mode = $_GET['mode'];
    echo "<form action='" . $_SERVER['PHP_SELF'] . "' method='post'>";
    echo "<table border='0' cellpadding='1' cellspacing='0' align='center' width='95%'>";
    echo "<tr><td class='bg2'>";
    echo "<table border='0' cellpadding='1' cellspacing='1' width='100%'>";
    echo "<tr class='bg3' align='left'>";
    echo "<td colspan='2' align='center'>" . $action[$mode]['desc'] . '</td></tr>';

    if ($mode === 'move') {
        echo '<tr><td class="bg3">' . _MD_MOVETOPICTO . '</td><td class="bg1">';
        $box = '<select name="newforum" size="1">';

        $categoryHandler = Helper::getInstance()->getHandler('Category');
        $categories      = $categoryHandler->getByPermission('access');
        $forums          = $forumHandler->getForumsByCategory(array_keys($categories), 'post', false);

        if (count($categories) > 0 && count($forums) > 0) {
            foreach (array_keys($forums) as $key) {
                $box .= "<option value='-1'>[" . $categories[$key]->getVar('cat_title') . ']</option>';
                foreach ($forums[$key] as $forumid => $_forum) {
                    $box .= "<option value='" . $forumid . "'>-- " . $_forum['title'] . '</option>';
                    if (!isset($_forum['sub'])) {
                        continue;
                    }
                    foreach (array_keys($_forum['sub']) as $fid) {
                        $box .= "<option value='" . $fid . "'>---- " . $_forum['sub'][$fid]['title'] . '</option>';
                    }
                }
            }
        } else {
            $box .= "<option value='-1'>" . _MD_NOFORUMINDB . '</option>';
        }
        unset($forums, $categories);

        echo $box;
        echo '</select></td></tr>';
    }
    if ('merge' === $mode) {
        echo '<tr><td class="bg3">' . _MD_MERGETOPICTO . '</td><td class="bg1">';
        echo _MD_TOPIC . "ID-$topic_id -> ID: <input name='newtopic' value=''>";
        echo '</td></tr>';
    }
    echo '<tr class="bg3"><td colspan="2" align="center">';
    echo "<input type='hidden' name='mode' value='" . $action[$mode]['name'] . "'>";
    echo "<input type='hidden' name='topic_id' value='" . $topic_id . "'>";
    echo "<input type='hidden' name='forum' value='" . $forum . "'>";
    echo "<input type='submit' name='submit' value='" . $action[$mode]['submit'] . "'>";
    echo '</td></tr></form></table></td></tr></table>';
}
// irmtfan move to footer.php
require_once __DIR__ . '/footer.php';
include $GLOBALS['xoops']->path('footer.php');
