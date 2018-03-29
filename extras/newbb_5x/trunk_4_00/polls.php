<?php
/*
 * Newbb module
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

/**
 * Poll handling for Newbb
 *
 * @copyright       {@link https://xoops.org/ XOOPS Project}
 * @license         {@link http://www.fsf.org/copyleft/gpl.html GNU public license}
 * @package         newbb
 * @since           4.0
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 */

use Xmf\Request;
use XoopsModules\Xoopspoll;
use XoopsModules\Xoopspoll\Constants;
use XoopsModules\Newbb;

require_once $GLOBALS['xoops']->path('header.php');

require_once $GLOBALS['xoops']->path('class/xoopsformloader.php');
require_once $GLOBALS['xoops']->path('class/xoopslists.php');
require_once $GLOBALS['xoops']->path('class/xoopsblock.php');

// irmtfan correct the way and typo=addmor -> addmore
$op      = 'add';
$goodOps = [
    'add',
    'save',
    'edit',
    'update',
    'addmore',
    'savemore',
    'delete',
    'delete_ok',
    'restart',
    'restart_ok',
    'log'
];
$op      = Request::getString('op', 'add');
$op      = (!in_array($op, $goodOps)) ? 'add' : $op;

//$poll_id  = (isset($_GET['poll_id']))   ? (int)($_GET['poll_id'])   : 0;
//$poll_id  = (isset($_POST['poll_id']))  ? (int)($_POST['poll_id'])  : $poll_id;
$poll_id = Request::getInt('poll_id', Request::getInt('poll_id', 0, 'POST'), 'GET');
//$topic_id = (isset($_GET['topic_id']))  ? (int)($_GET['topic_id'])  : 0;
//$topic_id = (isset($_POST['topic_id'])) ? (int)($_POST['topic_id']) : $topic_id;
$topic_id = Request::getInt('topic_id', Request::getInt('topic_id', 0, 'POST'), 'GET');
/** @var XoopsModuleHandler $moduleHandler */
$moduleHandler = xoops_getHandler('module');
$xoopspoll     = $moduleHandler->getByDirname('xoopspoll');
if (is_object($xoopspoll) && $xoopspoll->getVar('isactive')) {
//    xoops_load('constants', 'xoopspoll');
//    xoops_load('pollUtility', 'xoopspoll');

    xoops_loadLanguage('admin', 'xoopspoll');
    $xpPollHandler = Xoopspoll\Helper::getInstance()->getHandler('Poll');
} else {
    redirect_header($_SERVER['HTTP_REFERER'], 2, _MD_POLLMODULE_ERROR);
}

/** @var Newbb\TopicHandler $topicHandler */
$topicHandler = Newbb\Helper::getInstance()->getHandler('Topic');
$topic_obj    = $topicHandler->get($topic_id);
if ($topic_obj instanceof Topic) {
    $forum_id = $topic_obj->getVar('forum_id');
} else {
    redirect_header('index.php', 2, _MD_POLLMODULE_ERROR . ': ' . _MD_FORUMNOEXIST);
}

/** @var Newbb\ForumHandler $forumHandler */
$forumHandler = Newbb\Helper::getInstance()->getHandler('Forum');
$forum_obj    = $forumHandler->get($forum_id);
if (!$forumHandler->getPermission($forum_obj)) {
    redirect_header('index.php', 2, _MD_NORIGHTTOACCESS);
}

if (!$topicHandler->getPermission($forum_obj, $topic_obj->getVar('topic_status'), 'view')) {
    redirect_header('viewforum.php?forum=' . $forum_obj->getVar('forum_id'), 2, _MD_NORIGHTTOVIEW);
}

if (!newbb_isAdmin($forum_obj)) {
    $perm = false;
    if ($topicHandler->getPermission($forum_obj, $topic_obj->getVar('topic_status'), 'addpoll')//&& $forum_obj->getVar('allow_polls') == 1 {
    ) {
        if (('add' === $op || 'save' === $op || 'update' === $op)
            && !$topic_obj->getVar('topic_haspoll')
            && ($GLOBALS['xoopsUser'] instanceof \XoopsUser)
            && ($GLOBALS['xoopsUser']->getVar('uid') === $topic_obj->getVar('topic_poster'))) {
            $perm = true;
        } elseif (!empty($poll_id) && ($GLOBALS['xoopsUser'] instanceof \XoopsUser)) {
            $poll_obj = $xpPollHandler->get($poll_id);
            if ($GLOBALS['xoopsUser']->getVar('uid') === $poll_obj->getVar('user_id')) {
                $perm = true;
            }
            unset($poll_obj);
        }
    }
    if (!$perm) {
        redirect_header("viewtopic.php?topic_id={$topic_id}", 2, _NOPERM);
    }
}

switch ($op) {
    case 'add':
    case 'edit':
        echo '<h4>' . _MD_POLL_EDITPOLL . "</h4>\n";
        $poll_obj = $xpPollHandler->get($poll_id); // will create poll if poll_id = 0 exist
        $poll_obj->renderForm($_SERVER['PHP_SELF'], 'post', ['topic_id' => $topic_id]);
        break;

    case 'save':
    case 'update':
        // check security token
        if (!$GLOBALS['xoopsSecurity']->check()) {
            redirect_header($_SERVER['PHP_SELF'], 2, implode('<br>', $GLOBALS['xoopsSecurity']->getErrors()));
        }

        /* make sure there's at least one option */
        $option_text   = isset($_POST['option_text']) ? $_POST['option_text'] : '';
        $option_string = is_array($option_text) ? implode('', $option_text) : $option_text;
        $option_string = trim($option_string);
        if (empty($option_string)) {
            // irmtfan - issue with javascript:history.go(-1)
            redirect_header($_SERVER['HTTP_REFERER'], 2, _MD_ERROROCCURED . ': ' . _MD_POLL_POLLOPTIONS . ' !');
        }

        $poll_obj     = $xpPollHandler->get($poll_id);
        $xpOptHandler = Xoopspoll\Helper::getInstance()->getHandler('Option');
        $xpLogHandler = Xoopspoll\Helper::getInstance()->getHandler('Log');

        $notify = Request::getInt('notify', Constants::NOTIFICATION_ENABLED, 'POST');

        $currentTimestamp = time();
        $xuEndTimestamp   = strtotime(Request::getString('xu_end_time', null, 'POST'));
        $endTimestamp     = empty($_POST['xu_end_time']) ? ($currentTimestamp + Constants::DEFAULT_POLL_DURATION) : userTimeToServerTime($xuEndTimestamp);
        $xuStartTimestamp = strtotime(Request::getString('xu_start_time', null, 'POST'));
        $startTimestamp   = empty($_POST['xu_start_time']) ? ($endTimestamp - Constants::DEFAULT_POLL_DURATION) : userTimeToServerTime($xuStartTimestamp);

        //  don't allow changing start time if there are votes in the log
        if (($startTimestamp < $poll_obj->getVar('start_time'))
            && ($xpLogHandler->getTotalVotesByPollId($poll_id) > 0)) {
            $startTimestamp = $poll_obj->getVar('start_time'); //don't change start time
        }

        $poll_vars = [
            'user_id'     => Request::getInt('user_id', $GLOBALS['xoopsUser']->uid(), 'POST'),
            'question'    => Request::getString('question', null, 'POST'),
            'description' => Request::getText('description', null, 'POST'),
            'mail_status' => (Constants::NOTIFICATION_ENABLED === $notify) ? Constants::POLL_NOT_MAILED : Constants::POLL_MAILED,
            'mail_voter'  => Request::getInt('mail_voter', Constants::NOT_MAIL_POLL_TO_VOTER, 'POST'),
            'start_time'  => $startTimestamp,
            'end_time'    => $endTimestamp,
            'display'     => Request::getInt('display', Constants::DO_NOT_DISPLAY_POLL_IN_BLOCK, 'POST'),
            'visibility'  => Request::getInt('visibility', Constants::HIDE_NEVER, 'POST'),
            'weight'      => Request::getInt('weight', Constants::DEFAULT_WEIGHT, 'POST'),
            'multiple'    => Request::getInt('multiple', Constants::NOT_MULTIPLE_SELECT_POLL, 'POST'),
            'multilimit'  => Request::getInt('multilimit', Constants::MULTIPLE_SELECT_LIMITLESS, 'POST'),
            'anonymous'   => Request::getInt('anonymous', Constants::ANONYMOUS_VOTING_DISALLOWED, 'POST')
        ];
        $poll_obj->setVars($poll_vars);
        $poll_id = $xpPollHandler->insert($poll_obj);
        if (!$poll_id) {
            $err = $poll_obj->getHtmlErrors();
            exit($err);
        }

        // now get the options
        $optionIdArray    = Request::getArray('option_id', [], 'POST');
        $optionIdArray    = array_map('intval', $optionIdArray);
        $optionTextArray  = Request::getArray('option_text', [], 'POST');
        $optionColorArray = Request::getArray('option_color', [], 'POST');

        foreach ($optionIdArray as $key => $oId) {
            if (!empty($oId) && ($option_obj = $xpOptHandler->get($oId))) {
                // existing option object so need to update it
                $optionTextArray[$key] = trim($optionTextArray[$key]);
                if ('' === $optionTextArray[$key]) {
                    // want to delete this option
                    if (false !== $xpOptHandler->delete($option_obj)) {
                        // now remove it from the log
                        $xpLogHandler->deleteByOptionId($option_obj->getVar('option_id'));
                        //update vote count in poll
                        $xpPollHandler->updateCount($poll_obj);
                    } else {
                        xoops_error($xpLogHandler->getHtmlErrors());
                        break;
                    }
                } else {
                    $option_obj->setVar('option_text', $optionTextArray[$key]);
                    $option_obj->setVar('option_color', $optionColorArray[$key]);
                    $option_obj->setVar('poll_id', $poll_id);
                    $xpOptHandler->insert($option_obj);
                }
            } else {
                // new option object
                $option_obj            = $xpOptHandler->create();
                $optionTextArray[$key] = trim($optionTextArray[$key]);
                if ('' !== $optionTextArray[$key]) { // ignore if text is empty
                    $option_obj->setVar('option_text', $optionTextArray[$key]);
                    $option_obj->setVar('option_color', $optionColorArray[$key]);
                    $option_obj->setVar('poll_id', $poll_id);
                    $xpOptHandler->insert($option_obj);
                }
                unset($option_obj);
            }
        }

        // clear the template cache so changes take effect immediately
        require_once $GLOBALS['xoops']->path('class/template.php');
        xoops_template_clear_module_cache($GLOBALS['xoopsModule']->getVar('mid'));
        xoops_template_clear_module_cache($xoopspoll->getVar('mid'));

        // update topic to indicate it has a poll
        $topic_obj->setVar('topic_haspoll', 1);
        $topic_obj->setVar('poll_id', $poll_obj->getVar('poll_id'));
        $success = $topicHandler->insert($topic_obj);
        if (!$success) {
            xoops_error($topicHandler->getHtmlErrors());
        } else {
            redirect_header("viewtopic.php?topic_id={$topic_id}", 2, _MD_POLL_DBUPDATED);
        }
        break;

    case 'addmore':
        $poll_obj     = $xpPollHandler->get($poll_id);
        $xpOptHandler = Xoopspoll\Helper::getInstance()->getHandler('Option');
        $question     = $poll_obj->getVar('question');
        unset($poll_obj);
        $poll_form = new \XoopsThemeForm(_MD_POLL_ADDMORE, 'poll_form', 'polls.php', 'post', true);
        $poll_form->addElement(new \XoopsFormLabel(_MD_POLL_POLLQUESTION, $question));
        $option_tray = $xpOptHandler->renderOptionFormTray($poll_id);
        $poll_form->addElement($option_tray);
        $poll_form->addElement(new \XoopsFormButtonTray('poll_submit', _SUBMIT, 'submit'));
        $poll_form->addElement(new \XoopsFormHidden('op', 'savemore'));
        $poll_form->addElement(new \XoopsFormHidden('topic_id', $topic_id));
        $poll_form->addElement(new \XoopsFormHidden('poll_id', $poll_id));

        echo '<h4>' . _MD_POLL_POLLCONF . "</h4>\n";
        $poll_form->display();
        break;

    case 'savemore':
        // check security token
        if (!$GLOBALS['xoopsSecurity']->check()) {
            redirect_header($_SERVER['PHP_SELF'], 2, implode('<br>', $GLOBALS['xoopsSecurity']->getErrors()));
        }

        $option_text   = isset($_POST['option_text']) ? $_POST['option_text'] : '';
        $option_string = is_array($option_text) ? implode('', $option_text) : $option_text;
        $option_string = trim($option_string);
        if (empty($option_string)) {
            // irmtfan - issue with javascript:history.go(-1)
            redirect_header($_SERVER['HTTP_REFERER'], 2, _MD_ERROROCCURED . ': ' . _MD_POLL_POLLOPTIONS . ' !');
        }

        $xpOptHandler = Xoopspoll\Helper::getInstance()->getHandler('Option');
        $i            = 0;
        $option_color = empty($_POST['option_color']) ? null : $_POST['option_color'];
        foreach ($option_text as $optxt) {
            $optxt = trim($optxt);
            if ('' !== $optxt) {
                $option_obj = $xpOptHandler->create();
                $option_obj->setVar('option_text', $optxt);
                $option_obj->setVar('poll_id', $poll_id);
                $option_obj->setVar('option_color', $option_color[$i]);
                $xpOptHandler->insert($option_obj);
                unset($option_obj);
            }
            ++$i;
        }
        require_once $GLOBALS['xoops']->path('class/template.php');
        xoops_template_clear_module_cache($GLOBALS['xoopsModule']->getVar('mid'));
        xoops_template_clear_module_cache($xoopspoll->getVar('mid'));
        redirect_header("polls.php?op=edit&amp;poll_id={$poll_id}&amp;topic_id={$topic_id}", 2, _MD_POLL_DBUPDATED);
        break;

    case 'delete':
        echo '<h4>' . _MD_POLL_POLLCONF . "</h4>\n";
        $poll_obj = $xpPollHandler->get($poll_id);
        xoops_confirm(['op' => 'delete_ok', 'topic_id' => $topic_id, 'poll_id' => $poll_id], 'polls.php', sprintf(_MD_POLL_RUSUREDEL, $poll_obj->getVar('question')));
        break;

    case 'delete_ok':
        // check security token
        if (!$GLOBALS['xoopsSecurity']->check()) {
            redirect_header($_SERVER['PHP_SELF'], 2, implode('<br>', $GLOBALS['xoopsSecurity']->getErrors()));
        }
        //try and delete the poll
        $poll_obj = $xpPollHandler->get($poll_id);
        $status   = $xpPollHandler->delete($poll_obj);
        if (false !== $status) {
            $xpOptHandler = Xoopspoll\Helper::getInstance()->getHandler('Option');
            $xpLogHandler = Xoopspoll\Helper::getInstance()->getHandler('Log');
            $xpOptHandler->deleteByPollId($poll_id);
            $xpLogHandler->deleteByPollId($poll_id);
        } else {
            $msg = $xpPollHandler->getHtmlErrors();
        }
        if (false !== $status) {
            require_once $GLOBALS['xoops']->path('class/template.php');
            xoops_template_clear_module_cache($xoopsModule->getVar('mid'));
            xoops_template_clear_module_cache($xoopspoll->getVar('mid'));
            // delete comments for this poll
            xoops_comment_delete($xoopsModule->getVar('mid'), $poll_id);

            $topic_obj->setVar('votes', 0); // not sure why we want to clear votes too... but I left it alone
            $topic_obj->setVar('topic_haspoll', 0);
            $topic_obj->setVar('poll_id', 0);
            $success = $topicHandler->insert($topic_obj);
            if (!$success) {
                xoops_error($topicHandler->getHtmlErrors());
                break;
            }
        } else {
            xoops_error($msg);
            break;
        }
        redirect_header("viewtopic.php?topic_id={$topic_id}", 1, _MD_POLL_DBUPDATED);
        break;

    case 'restart':
        $default_poll_duration = Constants::DEFAULT_POLL_DURATION;
        $poll_form             = new \XoopsThemeForm(_MD_POLL_RESTARTPOLL, 'poll_form', 'polls.php', 'post', true);
        $expire_text           = new \XoopsFormText(_MD_POLL_EXPIRATION . '<br><small>' . _MD_POLL_FORMAT . '<br>' . sprintf(_MD_POLL_CURRENTTIME, formatTimestamp(time(), _DATESTRING)) . '</small>', 'end_time', 20, 19, formatTimestamp(time() + $default_poll_duration, _DATESTRING));
        $poll_form->addElement($expire_text);
        $poll_form->addElement(new \XoopsFormRadioYN(_MD_POLL_NOTIFY, 'notify', 1));
        $poll_form->addElement(new \XoopsFormRadioYN(_MD_POLL_RESET, 'reset', 0));
        $poll_form->addElement(new \XoopsFormHidden('op', 'restart_ok'));
        $poll_form->addElement(new \XoopsFormHidden('topic_id', $topic_id));
        $poll_form->addElement(new \XoopsFormHidden('poll_id', $poll_id));
        $poll_form->addElement(new \XoopsFormButton('', 'poll_submit', _MD_POLL_RESTART, 'submit'));

        echo '<h4>' . _MD_POLL_POLLCONF . "</h4>\n";
        $poll_form->display();

        break;

    case 'restart_ok':
        // check security token
        if (!$GLOBALS['xoopsSecurity']->check()) {
            redirect_header($_SERVER['PHP_SELF'], 2, implode('<br>', $GLOBALS['xoopsSecurity']->getErrors()));
        }

        $poll_obj              = $xpPollHandler->get($poll_id);
        $default_poll_duration = Constants::DEFAULT_POLL_DURATION;
        $poll_mailed           = Constants::POLL_MAILED;
        $poll_not_mailed       = Constants::POLL_NOT_MAILED;

        $end_time = empty($_POST['end_time']) ? 0 : (int)$_POST['end_time'];
        if (!empty($end_time)) {
            $timezone = ($GLOBALS['xoopsUser'] instanceof \XoopsUser) ? $GLOBALS['xoopsUser']->getVar('timezone') : null;
            //        $poll_obj->setVar("end_time", userTimeToServerTime(strtotime($end_time), $timezone));
            //Hack by irmtfan
            $poll_obj->setVar('end_time', userTimeToServerTime(method_exists('XoopsLocal', 'strtotime') ? XoopsLocal::strtotime($end_time) : strtotime($end_time), $timezone));
        } else {
            $poll_obj->setVar('end_time', time() + $default_poll_duration);
        }
        if (!empty($_POST['notify']) && ($end_time > time())) {
            // if notify, set mail status to "not mailed"
            $poll_obj->setVar('mail_status', $poll_not_mailed);
        } else {
            // if not notify, set mail status to already "mailed"
            $poll_obj->setVar('mail_status', $poll_mailed);
        }

        if (!$xpPollHandler->insert($poll_obj)) {  // update the poll
            xoops_error($poll_obj->getHtmlErrors());
            exit();
        }
        if (!empty($_POST['reset'])) { // reset all vote/voter counters
            $xpLogHandler = Xoopspoll\Helper::getInstance()->getHandler('Log');
            $xpLogHandler->deleteByPollId($poll_id);
            $xpOptHandler = Xoopspoll\Helper::getInstance()->getHandler('Option');
            $xpOptHandler->resetCountByPollId($poll_id);
            $xpPollHandler->updateCount($poll_obj);
        }

        // clear the topic votes
        require_once $GLOBALS['xoops']->path('class/template.php');
        xoops_template_clear_module_cache($xoopsModule->getVar('mid'));
        xoops_template_clear_module_cache($xoopspoll->getVar('mid'));
        redirect_header("viewtopic.php?topic_id={$topic_id}", 1, _MD_POLL_DBUPDATED);
        break;

    case 'log':
        redirect_header($GLOBALS['xoops']->url("modules/xoopspoll/admin/main.php?op=log&amp;poll_id={$poll_id}"), 2, _MD_LOG_XOOPSPOLL_ADMIN_REDIRECT);
        break;
}

include $GLOBALS['xoops']->path('footer.php');
