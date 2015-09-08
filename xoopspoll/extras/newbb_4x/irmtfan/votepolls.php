<?php
// $Id: votepolls.php,v 1.3 2005/10/19 17:20:28 phppp Exp $
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                    Copyright (c) 2000 XOOPS.org                           //
//                       <http://xoops.org/>                             //
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

require_once __DIR__ . '/header.php';
xoops_load('XoopsRequest');

$poll_id  = (isset($_GET['poll_id'])) ? (int)($_GET['poll_id']) : 0;
$poll_id  = (isset($_POST['poll_id'])) ? (int)($_POST['poll_id']) : $poll_id;
$topic_id = (isset($_GET['topic_id'])) ? (int)($_GET['topic_id']) : 0;
$topic_id = (isset($_POST['topic_id'])) ? (int)($_POST['topic_id']) : $topic_id;
$forum    = (isset($_GET['forum'])) ? (int)($_GET['forum']) : 0;
$forum    = (isset($_POST['forum'])) ? (int)($_POST['forum']) : $forum;

$topic_handler =& xoops_getmodulehandler('topic', 'newbb');
$topic_obj     =& $topic_handler->get($topic_id);
if (!$topic_handler->getPermission($topic_obj->getVar('forum_id'), $topic_obj->getVar('topic_status'), 'vote')) {
    // irmtfan - issue with javascript:history.go(-1)
    redirect_header($_SERVER['HTTP_REFERER'], 2, _NOPERM);
}

if (empty($_POST['option_id'])) {
    // irmtfan - add error message - simple url
    redirect_header("viewtopic.php?topic_id={$topic_id}", 1, _MD_POLL_NOOPTION);
}

if (($xoopspoll instanceof XoopsModule) && $xoopspoll->isactive()) {
    if ('xoopspoll' === $pollmodules) {
        /* xoopspoll module installed & active */
        $pollmodul = 'xoopspoll';
        xoops_load('constants', 'xoopspoll');
        xoops_loadLanguage('main', 'xoopspoll');
        $xpPollHandler =& xoops_getmodulehandler('poll', 'xoopspoll');
        $xpLogHandler  =& xoops_getmodulehandler('log', 'xoopspoll');
    } else { // Umfrage
        $pollmodul = 'umfrage';
        include_once XOOPS_ROOT_PATH . '/modules/umfrage/include/constants.php';
        include_once XOOPS_ROOT_PATH . '/modules/umfrage/class/umfrage.php';
        include_once XOOPS_ROOT_PATH . '/modules/umfrage/class/umfrageoption.php';
        include_once XOOPS_ROOT_PATH . '/modules/umfrage/class/umfragelog.php';
        include_once XOOPS_ROOT_PATH . '/modules/umfrage/class/umfragerenderer.php';
    }
} else {
    //no active poll module found
    redirect_header($_SERVER['HTTP_REFERER'], 2, _MD_POLLMODULE_ERROR);
}

$mail_author = false;
if ('xoopspoll' === $pollmodules) {
    $pollObj = $xpPollHandler->get($poll_id);
    if ($pollObj instanceof XoopspollPoll) {
        if ($pollObj->getVar('multiple')) {
            $optionId = XoopsRequest::getInt('option_id', 0, 'POST');
            $optionId = (array)$optionId; // type cast to make sure it's an array
            $optionId = array_map('intval', $optionId); // make sure values are integers
        } else {
            $optionId = $_POST['option_id'];
        }
        if (!$pollObj->hasExpired()) {
            $msg = _MD_XOOPSPOLL_MUSTLOGIN;
            //@todo:: add $url to all redirects
            //            $url = $GLOBALS['xoops']->buildUrl("index.php", array('poll_id' => $poll_id));
            if ($pollObj->isAllowedToVote()) {
                $thisVoter     = (!empty($GLOBALS['xoopsUser']) && ($GLOBALS['xoopsUser'] instanceof XoopsUser)) ? $GLOBALS['xoopsUser']->getVar('uid') : null;
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
                        redirect_header($GLOBALS['xoops']->buildUrl('index.php', array('poll_id' => $poll_id)), XoopspollConstants::REDIRECT_DELAY_MEDIUM, _MD_XOOPSPOLL_VOTE_ERROR);
                    }
                } else {
                    $msg = _MD_XOOPSPOLL_ALREADYVOTED;
                }
                /* set anon user vote (and the time they voted) */
                if (!$GLOBALS['xoopsUser'] instanceof XoopsUser) {
                    xoops_load('pollUtility', 'xoopspoll');
                    XoopspollPollUtility::setVoteCookie($poll_id, $voteTime, 0);
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
        redirect_header($url, XoopspollConstants::REDIRECT_DELAY_MEDIUM, $msg);
    } else {
        /*
                redirect_header($GLOBALS['xoops']->buildUrl("pollresults.php", array('poll_id' => $poll_id)),
                                                       XoopspollConstants::REDIRECT_DELAY_MEDIUM,
                                                       $msg);
        */
        redirect_header($GLOBALS['xoops']->buildUrl('viewtopic.php', array('topic_id' => $topic_id)), XoopspollConstants::REDIRECT_DELAY_MEDIUM, $msg);
    }
} else { //Umfrage
    $poll = new Umfrage($poll_id);
    if (is_object($xoopsUser)) {
        if (UmfrageLog::hasVoted($poll_id, $_SERVER['REMOTE_ADDR'], $xoopsUser->getVar('uid'))) {
            $msg = _PL_ALREADYVOTED;
            setcookie("bb_polls[$poll_id]", 1);
        } else {
            // irmtfan save ip to db
            $poll->vote($_POST['option_id'], $_SERVER['REMOTE_ADDR'], $xoopsUser->getVar('uid'));
            $poll->updateCount();
            $msg = _PL_THANKSFORVOTE;
            setcookie("bb_polls[$poll_id]", 1);
        }
    } else {
        if (UmfrageLog::hasVoted($poll_id, $_SERVER['REMOTE_ADDR'])) {
            $msg = _PL_ALREADYVOTED;
            setcookie("bb_polls[$poll_id]", 1);
        } else {
            $poll->vote($_POST['option_id'], $_SERVER['REMOTE_ADDR']);
            $poll->updateCount();
            $msg = _PL_THANKSFORVOTE;
            setcookie("bb_polls[$poll_id]", 1);
        }
    }
}
// irmtfan - simple url
redirect_header("viewtopic.php?topic_id={$topic_id}", 1, $msg);
