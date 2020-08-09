<?php
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
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @package         newbb
 * @since           4.0
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 */

use Xmf\Request;
use XoopsModules\Newbb;
use XoopsModules\Xoopspoll;

require_once __DIR__ . '/header.php';

/** @var Xoopspoll\Helper $helper */
$helper = Xoopspoll\Helper::getInstance();

if (Request::hasVar('submit', 'POST')) {
    foreach (['forum', 'topic_id', 'newforum', 'newtopic'] as $getint) {
        ${$getint} = (int)(@$_POST[$getint]);
    }
} else {
    foreach (['forum', 'topic_id'] as $getint) {
        ${$getint} = (int)(@$_GET[$getint]);
    }
}

if (!$topic_id) {
    $redirect = empty($forum_id) ? 'index.php' : "viewforum.php?forum={$forum}";
    redirect_header($redirect, 2, _MD_ERRORTOPIC);
}

/** @var Newbb\TopicHandler $topicHandler */
$topicHandler = Newbb\Helper::getInstance()->getHandler('Topic');
$forum        = $topicHandler->get($topic_id, 'forum_id');
$forum_new    = !empty($newtopic) ? $topicHandler->get($newtopic, 'forum_id') : 0;

/** @var Newbb\ForumHandler $forumHandler */
$forumHandler = Newbb\Helper::getInstance()->getHandler('Forum');
if (!$forumHandler->getPermission($forum, 'moderate')
    || (!empty($forum_new)
        && !$forumHandler->getPermission($forum_new, 'reply'))// The forum for the topic to be merged to
    || (!empty($newforum) && !$forumHandler->getPermission($newforum, 'post')) // The forum to be moved to
) {
    redirect_header("viewtopic.php?forum={$forum}&amp;topic_id={$topic_id}", 2, _NOPERM);
}

if ($helper->getConfig('wol_enabled')) {
    $onlineHandler = Newbb\Helper::getInstance()->getHandler('Online');
    $onlineHandler->init($forum);
}

$action_array = ['merge', 'delete', 'move', 'lock', 'unlock', 'sticky', 'unsticky', 'digest', 'undigest'];
foreach ($action_array as $_action) {
    $action[$_action] = [
        'name'   => $_action,
        'desc'   => constant(mb_strtoupper("_MD_DESC_{$_action}")),
        'submit' => constant(mb_strtoupper("_MD_{$_action}")),
        'sql'    => "topic_{$_action}=1",
        'msg'    => constant(mb_strtoupper("_MD_TOPIC{$_action}")),
    ];
}
$action['lock']['sql']     = 'topic_status = 1';
$action['unlock']['sql']   = 'topic_status = 0';
$action['unsticky']['sql'] = 'topic_sticky = 0';
$action['undigest']['sql'] = 'topic_digest = 0';
$action['digest']['sql']   = 'topic_digest = 1, digest_time = ' . time();

// Disable cache
$xoopsConfig['module_cache'][$xoopsModule->getVar('mid')] = 0;
require_once XOOPS_ROOT_PATH . '/header.php';

if (Request::hasVar('submit', 'POST')) {
    $mode = $_POST['mode'];
    if ('delete' === $mode) {
        $topic_obj = $topicHandler->get($topic_id);
        $topicHandler->delete($topic_obj);
        $forumHandler->synchronization($forum);

        $topic_obj->loadFilters('delete');
        echo $action[$mode]['msg'] . "<p><a href='viewforum.php?forum={$forum}'>" . _MD_RETURNTOTHEFORUM . "</a></p><p><a href='index.php'>" . _MD_RETURNFORUMINDEX . '</a></p>';
    } elseif ('merge' === $mode) {
        $postHandler = Newbb\Helper::getInstance()->getHandler('Post');

        $topic_obj    = $topicHandler->get($topic_id);
        $newtopic_obj = $topicHandler->get($newtopic);
        /* return false if destination topic is newer or not existing */
        if ($newtopic > $topic_id || !is_object($newtopic_obj)) {
            redirect_header(Request::getString('HTTP_REFERER', '', 'SERVER'), 2, _MD_ERROR);
        }

        $criteria_topic = new \Criteria('topic_id', $topic_id);
        $criteria       = new \CriteriaCompo($criteria_topic);
        $criteria->add(new \Criteria('pid', 0));
        $postHandler->updateAll('pid', $topicHandler->getTopPostId($newtopic), $criteria, true);
        $postHandler->updateAll('topic_id', $newtopic, $criteria_topic, true);

        $topic_views       = $topic_obj->getVar('topic_views') + $newtopic_obj->getVar('topic_views');
        $criteria_newtopic = new \Criteria('topic_id', $newtopic);
        $topicHandler->updateAll('topic_views', $topic_views, $criteria_newtopic, true);

        $topicHandler->synchronization($newtopic);

        $poll_id = $topicHandler->get($topic_id, 'poll_id');

        if ($poll_id > 0) {
            /** @var \XoopsModuleHandler $moduleHandler */
            $moduleHandler = xoops_getHandler('module');
            $pollModule    = $moduleHandler->getByDirname('xoopspoll');
            if (($pollModule instanceof \XoopsModule) && $pollModule->isactive()) {
                $xpPollHandler = Xoopspoll\Helper::getInstance()->getHandler('Poll');
                $poll          = $xpPollHandler->get($poll_id);
                if (false !== $xpPollHandler->delete($poll)) {
                    $xpOptHandler = Xoopspoll\Helper::getInstance()->getHandler('Option');
                    $xpLogHandler = Xoopspoll\Helper::getInstance()->getHandler('Log');
                    $xpOptHandler->deleteByPollId($poll_id);
                    $xpLogHandler->deleteByPollId($poll_id);
                    xoops_comment_delete($xoopsModule->getVar('mid'), $poll_id);
                }
            }
        }

        $sql    = sprintf('DELETE FROM `%s` WHERE topic_id = %u', $xoopsDB->prefix('bb_topics'), $topic_id);
        $result = $xoopsDB->queryF($sql);

        $sql    = sprintf('DELETE FROM `%s` WHERE topic_id = %u', $xoopsDB->prefix('bb_votedata'), $topic_id);
        $result = $xoopsDB->queryF($sql);

        $sql    = sprintf('UPDATE `%s` SET forum_topics = forum_topics-1 WHERE forum_id = %u', $xoopsDB->prefix('bb_forums'), $forum);
        $result = $xoopsDB->queryF($sql);

        $topic_obj->loadFilters('delete');
        $newtopic_obj->loadFilters('update');

        echo $action[$mode]['msg'] . "<p><a href='viewtopic.php?topic_id={$newtopic}'>" . _MD_VIEWTHETOPIC . '</a></p>' . "<p><a href='viewforum.php?forum={$forum}'>" . _MD_RETURNTOTHEFORUM . '</a></p>' . "<p><a href='index.php'>" . _MD_RETURNFORUMINDEX . '</a></p>';
    } elseif ('move' === $mode) {
        if ($newforum > 0) {
            $topic_obj = $topicHandler->get($topic_id);
            $topic_obj->loadFilters('update');
            $topic_obj->setVar('forum_id', $newforum, true);
            $topicHandler->insert($topic_obj, true);
            $topic_obj->loadFilters('update');

            $sql = sprintf('UPDATE `%s` SET forum_id = %u WHERE topic_id = %u', $xoopsDB->prefix('bb_posts'), $newforum, $topic_id);
            if (!$r = $xoopsDB->query($sql)) {
                return false;
            }
            $forumHandler->synchronization($newforum);
            $forumHandler->synchronization($forum);
            echo $action[$mode]['msg'] . "<p><a href='viewtopic.php?topic_id={$topic_id}&amp;forum={$newforum}'>" . _MD_GOTONEWFORUM . "</a></p><p><a href='index.php'>" . _MD_RETURNFORUMINDEX . '</a></p>';
        } else {
            redirect_header(Request::getString('HTTP_REFERER', '', 'SERVER'), 2, _MD_ERRORFORUM);
        }
    } else {
        $sql = sprintf('UPDATE `%s` SET ' . $action[$mode]['sql'] . ' WHERE topic_id = %u', $xoopsDB->prefix('bb_topics'), $topic_id);
        if (!$r = $xoopsDB->query($sql)) {
            redirect_header("viewtopic.php?forum={$forum}&amp;topic_id={$topic_id}&amp;order={$order}&amp;viewmode={$viewmode}", 2, _MD_ERROR_BACK . '<br>sql:' . $sql);
        }
        if ('digest' === $mode && $xoopsDB->getAffectedRows()) {
            $topic_obj    = $topicHandler->get($topic_id);
            $statsHandler = Newbb\Helper::getInstance()->getHandler('Stats');
            $statsHandler->update($topic_obj->getVar('forum_id'), 'digest');
            $userstatsHandler = Newbb\Helper::getInstance()->getHandler('Userstats');
            $user_stat        = $userstatsHandler->get($topic_obj->getVar('topic_poster'));
            if ($user_stat) {
                $user_stat->setVar('user_digests', $user_stat->getVar('user_digests') + 1);
                $userstatsHandler->insert($user_stat);
            }
        }
        echo $action[$mode]['msg'] . "<p><a href='viewtopic.php?topic_id={$topic_id}&amp;forum={$forum}'>" . _MD_VIEWTHETOPIC . "</a></p><p><a href='viewforum.php?forum={$forum}'>" . _MD_RETURNFORUMINDEX . '</a></p>';
    }
} else {  // No submit
    $mode = $_GET['mode'];
    echo "<form action='" . $_SERVER['SCRIPT_NAME'] . "' method='post'>";
    echo $GLOBALS['xoopsSecurity']->getTokenHTML();
    echo "<table border='0' cellpadding='1' cellspacing='0' align='center' width='95%'>";
    echo "<tr><td class='bg2'>";
    echo "<table border='0' cellpadding='1' cellspacing='1' width='100%'>";
    echo "<tr class='bg3' align='left'>";
    echo "<td colspan='2' align='center'>" . $action[$mode]['desc'] . '</td></tr>';

    if ('move' === $mode) {
        echo '<tr><td class="bg3">' . _MD_MOVETOPICTO . '</td><td class="bg1">';
        $box = '<select name="newforum" size="1">';

        $categoryHandler = Newbb\Helper::getInstance()->getHandler('Category');
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
        echo _MD_TOPIC . " ID-{$topic_id} -> ID: <input name='newtopic' value=''>";
        echo '</td></tr>';
    }
    echo '<tr class="bg3"><td colspan="2" align="center">';
    echo "<input type='hidden' name='mode' value='" . $action[$mode]['name'] . "'>";
    echo "<input type='hidden' name='topic_id' value='" . $topic_id . "'>";
    echo "<input type='hidden' name='forum' value='" . $forum . "'>";
    echo "<input type='submit' name='submit' value='" . $action[$mode]['submit'] . "'>";
    echo '</td></tr></form></table></td></tr></table>';
}
require_once XOOPS_ROOT_PATH . '/footer.php';
