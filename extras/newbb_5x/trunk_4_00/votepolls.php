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

use XoopsModules\Xoopspoll;
use XoopsModules\Xoopspoll\Constants;
use XoopsModules\Newbb;

require_once __DIR__ . '/header.php';

$poll_id  = isset($_GET['poll_id']) ? (int)$_GET['poll_id'] : 0;
$poll_id  = isset($_POST['poll_id']) ? (int)$_POST['poll_id'] : $poll_id;
$topic_id = isset($_GET['topic_id']) ? (int)$_GET['topic_id'] : 0;
$topic_id = isset($_POST['topic_id']) ? (int)$_POST['topic_id'] : $topic_id;
$forum    = isset($_GET['forum']) ? (int)$_GET['forum'] : 0;
$forum    = isset($_POST['forum']) ? (int)$_POST['forum'] : $forum;

/** @var Newbb\TopicHandler $topicHandler */
$topicHandler = Newbb\Helper::getInstance()->getHandler('Topic');
$topic_obj    = $topicHandler->get($topic_id);
if (!$topicHandler->getPermission($topic_obj->getVar('forum_id'), $topic_obj->getVar('topic_status'), 'vote')) {
    // irmtfan - issue with javascript:history.go(-1)
    redirect_header($_SERVER['HTTP_REFERER'], 2, _NOPERM);
}

if (empty($_POST['option_id'])) {
    // irmtfan - add error message - simple url
    redirect_header("viewtopic.php?topic_id={$topic_id}", 1, _MD_POLL_NOOPTION);
}

/** @var XoopsModuleHandler $moduleHandler */
$moduleHandler = xoops_getHandler('module');
$xoopspoll     = $moduleHandler->getByDirname('xoopspoll');

if (($xoopspoll instanceof XoopsModule) && $xoopspoll->isactive()) {
    /* xoopspoll module installed & active */
    xoops_load('constants', 'xoopspoll');
    xoops_loadLanguage('main', 'xoopspoll');
    $xpPollHandler = Xoopspoll\Helper::getInstance()->getHandler('Poll');
    $xpLogHandler  = Xoopspoll\Helper::getInstance()->getHandler('Log');
} else {
    //no active poll module found
    redirect_header($_SERVER['HTTP_REFERER'], 2, _MD_POLLMODULE_ERROR);
}

$mail_author = false;
$pollObj     = $xpPollHandler->get($poll_id);
if ($pollObj instanceof Poll) {
    if ($pollObj->getVar('multiple')) {
        $optionId = $_POST['option_id'];
        $optionId = (array)$optionId; // type cast to make sure it's an array
        $optionId = array_map('intval', $optionId); // make sure values are integers
    } else {
        $optionId = $_POST['option_id'];
    }
    if (!$pollObj->hasExpired()) {
        $msg = _MD_XOOPSPOLL_MUSTLOGIN;
        //@todo:: add $url to all redirects
        //        $url = $GLOBALS['xoops']->buildUrl("index.php", array('poll_id' => $poll_id));
        if ($pollObj->isAllowedToVote()) {
            $thisVoter     = (!empty($GLOBALS['xoopsUser'])
                              && ($GLOBALS['xoopsUser'] instanceof \XoopsUser)) ? $GLOBALS['xoopsUser']->getVar('uid') : null;
            $votedThisPoll = $xpLogHandler->hasVoted($poll_id, xoops_getenv('REMOTE_ADDR'), $thisVoter);
            if (!$votedThisPoll) {
                /* user that hasn't voted before in this poll or module preferences allow it */
                $voteTime = time();
                if ($pollObj->vote($optionId, xoops_getenv('REMOTE_ADDR'), $voteTime)) {
                    if (!$xpPollHandler->updateCount($pollObj)) { // update the count and save in db
                        echo $pollObj->getHtmlErrors();
                        exit();
                    }
                    $msg = _MD_XOOPSPOLL_THANKSFORVOTE;
                } else {
                    /* there was a problem registering the vote */
                    redirect_header($GLOBALS['xoops']->buildUrl('index.php', ['poll_id' => $poll_id]), Constants::REDIRECT_DELAY_MEDIUM, _MD_XOOPSPOLL_VOTE_ERROR);
                }
            } else {
                $msg = _MD_XOOPSPOLL_ALREADYVOTED;
            }
            /* set anon user vote (and the time they voted) */
            if (!$GLOBALS['xoopsUser'] instanceof \XoopsUser) {
//                xoops_load('pollUtility', 'xoopspoll');
                Xoopspoll\Utility::setVoteCookie($poll_id, $voteTime, 0);
            }
        } else {
            $msg = _MD_XOOPSPOLL_CANNOTVOTE;
        }
    } else {
        /* poll has expired so just show the results */
        $msg = _MD_XOOPSPOLL_SORRYEXPIRED;
    }
} else {
    $msg = _MD_XOOPSPOLL_ERROR_INVALID_POLLID;
}
if (null !== $url) {
    redirect_header($url, Constants::REDIRECT_DELAY_MEDIUM, $msg);
} else {
    redirect_header($GLOBALS['xoops']->buildUrl('viewtopic.php', ['topic_id' => $topic_id]), Constants::REDIRECT_DELAY_MEDIUM, $msg);
}
// irmtfan - simple url
redirect_header("viewtopic.php?topic_id={$topic_id}", 1, $msg);
