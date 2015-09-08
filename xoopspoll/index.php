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
 * XOOPS Poll main index page
 *
 * @copyright::  {@link http://xoops.org XOOPS Project}
 * @license  ::    {@link http://www.fsf.org/copyleft/gpl.html GNU public license}
 * @package  ::    xoopspoll
 * @since    ::      1.0
 * @version  ::    $Id $
 *
 * @uses     xoops_load() method used to load classes
 * @uses     CriteriaCompo
 * @uses     Criteria
 * @uses     mixed $GLOBALS['xoops']::path gets XOOPS directory information
 * @uses     string $GLOBALS['xoops']::url gets XOOPS URL/URI information
 * @uses     mixed $GLOBALS['xoopsUser'] gets information about the currently logged in user
 * @uses     xoops_getenv() function to retrieve XOOPS environment variables
 * @uses     xoops_getUserTimestamp() function to convert time to user timestamp
 * @uses     formatTimestamp() function to convert timestamp to human readable form
 * @uses     xoops_getmodulehandler() to load handler for this module's class(es)
 * @uses     redirect_header() function used to send user to another location after completing task(s)
 */

include_once dirname(dirname(__DIR__)) . '/mainfile.php';

xoops_load('constants', 'xoopspoll');
xoops_load('renderer', 'xoopspoll');
xoops_load('XoopsRequest');

$myts        =& MyTextSanitizer::getInstance();
$pollHandler =& xoops_getmodulehandler('poll', 'xoopspoll');
$logHandler  =& xoops_getmodulehandler('log', 'xoopspoll');

$pollId = XoopsRequest::getInt('poll_id', 0);
$url    = XoopsRequest::getString('url', '');

if (empty($pollId)) {
    $GLOBALS['xoopsOption']['template_main'] = 'xoopspoll_index.tpl';
    include $GLOBALS['xoops']->path('header.php');
    $GLOBALS['xoopsTpl']->assign(array(
                                     'lang_pollslist'      => _MD_XOOPSPOLL_POLLSLIST,
                                     'lang_pollquestion'   => _MD_XOOPSPOLL_POLLQUESTION,
                                     'lang_pollvoters'     => _MD_XOOPSPOLL_VOTERS,
                                     'lang_votes'          => _MD_XOOPSPOLL_VOTES,
                                     'lang_expiration'     => _MD_XOOPSPOLL_EXPIRATION,
                                     'lang_results'        => _MD_XOOPSPOLL_RESULTS,
                                     'lang_mustlogin'      => _MD_XOOPSPOLL_MUSTLOGIN,
                                     'disp_votes'          => $GLOBALS['xoopsModuleConfig']['disp_vote_nums'],
                                     'results_link_icon'   => $GLOBALS['xoopsModule']->getInfo('icons16') . '/open12.gif',
                                     'obscured_icon'       => $GLOBALS['xoops']->url('modules/xoopspoll/assets/images/icons/obscured.png'),
                                     'lang_obscured_alt'   => _MD_XOOPSPOLL_OBSCURED,
                                     'lang_obscured_title' => _MD_XOOPSPOLL_OBSCURED));

    /* get polls to display on this page */
    $limit    = XoopsRequest::getInt('limit', XoopspollConstants::DEFAULT_POLL_PAGE_LIMIT);
    $start    = XoopsRequest::getInt('start', 0);
    $criteria = new CriteriaCompo();
    $criteria->add(new Criteria('start_time', time(), '<='));  // only display polls that have started

    /* check to see if forum module is installed and
     * exclude polls created from a forum
     */
    if ($GLOBALS['xoopsModuleConfig']['hide_forum_polls']) {
        $module_handler =& xoops_gethandler('module');
        $newbbModule    =& $module_handler->getByDirname('newbb');
        if ($newbbModule instanceof XoopsModule && $newbbModule->isactive()) {
            $topic_handler = & xoops_getmodulehandler('topic', 'newbb');
            $tFields       = array('topic_id', 'poll_id');
            $tArray        = $topic_handler->getAll(new Criteria('topic_haspoll', 0, '>'), $tFields, false);
            if (!empty($tArray)) {
                $tcriteria = array();
                foreach ($tArray as $t) {
                    $tcriteria[] = $t['poll_id'];
                }
                if (!empty($tcriteria)) {
                    $tstring = '(' . implode(',', $tcriteria) . ')';
                    $criteria->add(new Criteria('poll_id', $tstring, 'NOT IN'));
                }
            }
            unset($topic_handler, $tFields, $tArray);
        }
        unset($newbbModule);
    }
    $criteria->setLimit($limit);
    $criteria->setStart($start);
    $criteria->setSort('weight ASC, end_time');  // trick criteria to allow 2 sort criteria
    $criteria->setOrder('DESC');
    $pollObjs = $pollHandler->getAll($criteria);

    foreach ($pollObjs as $pollObj) {
        $polls                 = array();
        $id                    = $pollObj->getVar('poll_id');
        $polls['pollId']       = $id;
        $polls['pollQuestion'] = $pollObj->getVar('question');

        if ($pollObj->getVar('end_time') > time()) {
            $polls['hasEnded'] = false;
            $polls['pollEnd']  = formatTimestamp($pollObj->getVar('end_time'), 'm');
            $uid               = (($GLOBALS['xoopsUser'] instanceof XoopsUser) && ($GLOBALS['xoopsUser']->getVar('uid') > 0)) ? $GLOBALS['xoopsUser']->getVar('uid') : 0;
            /**
             * {@internal DEBUG CODE
             * echo "<br />ID[{$id}] IP[" . xoops_getenv('REMOTE_ADDR') . "] UID[{$uid}]<br />";
             * $vp = (!empty($_COOKIE['voted_polls'])) ? $_COOKIE['voted_polls'] : array();
             * $cook = (!array_key_exists($id, $vp)) ? "NO COOKIE KEY" : "FOUND COOKIE KEY";
             * $cv = (!$pollObj->isAllowedToVote()) ? "Not ALLOWED" :  "ALLOWED";
             * $lv = ($logHandler->hasVoted($id, xoops_getenv('REMOTE_ADDR'), $uid)) ? "HAS VOTED" : "HAS NOT VOTED";
             * if (!$pollObj->isAllowedToVote() || ($logHandler->hasVoted($id, xoops_getenv('REMOTE_ADDR'), $uid))) {
             * echo "NO: {$cv} {$lv} {$cook}<br />\n";
             * } else {
             * echo "YES: {$cv} {$lv} {$cook}<br />\n";
             * }
             * } */
            if (!$pollObj->isAllowedToVote() || ($logHandler->hasVoted($id, xoops_getenv('REMOTE_ADDR'), $uid))) {
                $polls['canVote'] = false;
            } else {
                $polls['canVote'] = true;
            }
        } else {
            /* poll has ended */
            $polls['hasEnded'] = true;
            $polls['pollEnd']  = _MD_XOOPSPOLL_EXPIRED;
            $polls['canVote']  = false; /* force so user can't vote */
        }
        $polls['pollVoters'] = (int)($pollObj->getVar('voters'));
        $polls['pollVotes']  = (int)($pollObj->getVar('votes'));
        $polls['visible']    = (true === $pollObj->isResultVisible()) ? true : false;
        $GLOBALS['xoopsTpl']->append('polls', $polls);
    }
    unset($pollObjs);
    include $GLOBALS['xoops']->path('footer.php');
} elseif (!empty($_POST['option_id'])) {
    /* user just tried to vote */
    //    $option_id   = XoopsRequest::getInt('option_id', 0, 'POST');
    $mail_author = false;
    $pollObj     = $pollHandler->get($pollId);
    if ($pollObj instanceof XoopspollPoll) {
        if ($pollObj->getVar('multiple')) {
            $optionId = XoopsRequest::getArray('option_id', array(), 'POST');
            $optionId = (array)$optionId; // type cast to make sure it's an array
            $optionId = array_map('intval', $optionId); // make sure values are integers
        } else {
            $optionId = XoopsRequest::getInt('option_id', 0, 'POST');
        }
        if (!$pollObj->hasExpired()) {
            $msg = _MD_XOOPSPOLL_MUSTLOGIN;
            //@todo:: add $url to all redirects
            //            $url = $GLOBALS['xoops']->buildUrl("index.php", array('poll_id' => $pollId));
            if ($pollObj->isAllowedToVote()) {
                $thisVoter     = (!empty($GLOBALS['xoopsUser']) && ($GLOBALS['xoopsUser'] instanceof XoopsUser)) ? $GLOBALS['xoopsUser']->getVar('uid') : null;
                $votedThisPoll = $logHandler->hasVoted($pollId, xoops_getenv('REMOTE_ADDR'), $thisVoter);
                if (!$votedThisPoll) {
                    /* user that hasn't voted before in this poll or module preferences allow it */
                    $voteTime = time();
                    if ($pollObj->vote($optionId, xoops_getenv('REMOTE_ADDR'), $voteTime)) {
                        if (!$pollHandler->updateCount($pollObj)) { // update the count and save in db
                            echo $pollObj->getHtmlErrors();
                            exit();
                        }
                        $msg = _MD_XOOPSPOLL_THANKSFORVOTE;
                    } else {
                        /* there was a problem registering the vote */
                        redirect_header($GLOBALS['xoops']->buildUrl('index.php', array('poll_id' => $pollId)), XoopspollConstants::REDIRECT_DELAY_MEDIUM, _MD_XOOPSPOLL_VOTE_ERROR);
                    }
                } else {
                    $msg = _MD_XOOPSPOLL_ALREADYVOTED;
                }
                /* set anon user vote (and the time they voted) */
                if (!$GLOBALS['xoopsUser'] instanceof XoopsUser) {
                    xoops_load('pollUtility', 'xoopspoll');
                    XoopspollPollUtility::setVoteCookie($pollId, $voteTime, 0);
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
    if ('' !== $url) {
        redirect_header($url, XoopspollConstants::REDIRECT_DELAY_MEDIUM, $msg);
    } else {
        redirect_header($GLOBALS['xoops']->buildUrl('pollresults.php', array('poll_id' => $pollId)), XoopspollConstants::REDIRECT_DELAY_MEDIUM, $msg);
    }
} else {
    $pollObj = $pollHandler->get($pollId);
    if ($pollObj->hasExpired()) {
        redirect_header($GLOBALS['xoops']->buildUrl('pollresults.php', array('poll_id' => $pollId)), XoopspollConstants::REDIRECT_DELAY_SHORT, _MD_XOOPSPOLL_SORRYEXPIRED);
    }
    $GLOBALS['xoopsOption']['template_main'] = 'xoopspoll_view.tpl';
    include $GLOBALS['xoops']->path('header.php');

    $renderer = new XoopspollRenderer($pollObj);
    $renderer->assignForm($GLOBALS['xoopsTpl']);

    $voteCount = $logHandler->getTotalVotesByPollId($pollId);

    $canVote    = false;
    $lang_multi = '';
    if ($pollObj->isAllowedToVote()) {
        $thisVoter  = (!empty($GLOBALS['xoopsUser']) && ($GLOBALS['xoopsUser'] instanceof XoopsUser)) ? $GLOBALS['xoopsUser']->getVar('uid') : null;
        $canVote    = ($logHandler->hasVoted($pollId, xoops_getenv('REMOTE_ADDR'), $thisVoter)) ? false : true;
        $multiple   = ($pollObj->getVar('multiple')) ? true : false;
        $multiLimit = (int)($pollObj->getVar('multilimit'));
        if ($multiple && ($multiLimit > 0)) {
            $lang_multi = sprintf(_MD_XOOPSPOLL_MULTITEXT, $multiLimit);
        }
    }

    $GLOBALS['xoopsTpl']->assign(array(
                                     'voteCount'    => $voteCount,
                                     'lang_vote'    => _MD_XOOPSPOLL_VOTE,
                                     'lang_results' => _MD_XOOPSPOLL_RESULTS,
                                     'disp_votes'   => $GLOBALS['xoopsModuleConfig']['disp_vote_nums'],
                                     'can_vote'     => $canVote,
                                     'lang_multi'   => $lang_multi));
    include $GLOBALS['xoops']->path('footer.php');
}
