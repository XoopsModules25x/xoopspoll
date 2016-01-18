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
 * @copyright       {@link http://xoops.org/ XOOPS Project}
 * @license         {@link http://www.fsf.org/copyleft/gpl.html GNU public license}
 * @package         newbb
 * @since           4.0
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 * @version         $Id: $
 */

include_once __DIR__ . '/header.php';

include_once $GLOBALS['xoops']->path('class/xoopsformloader.php');
include_once $GLOBALS['xoops']->path('class/xoopslists.php');
include_once $GLOBALS['xoops']->path('class/xoopsblock.php');
xoops_load('XoopsRequest');

// irmtfan correct the way and typo=addmor -> addmore
$op      = 'add';
$goodOps = array(
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
    'log');
$op      = XoopsRequest::getString('op', 'add');
$op      = (!in_array($op, $goodOps)) ? 'add' : $op;

//$poll_id  = (isset($_GET['poll_id']))   ? (int)($_GET['poll_id'])   : 0;
//$poll_id  = (isset($_POST['poll_id']))  ? (int)($_POST['poll_id'])  : $poll_id;
$poll_id = XoopsRequest::getInt('poll_id', XoopsRequest::getInt('poll_id', 0, 'POST'), 'GET');
//$topic_id = (isset($_GET['topic_id']))  ? (int)($_GET['topic_id'])  : 0;
//$topic_id = (isset($_POST['topic_id'])) ? (int)($_POST['topic_id']) : $topic_id;
$topic_id = XoopsRequest::getInt('topic_id', XoopsRequest::getInt('topic_id', 0, 'POST'), 'GET');

/** {@internal $pollmodules is initialized in ./header.php file} */
if ('xoopspoll' === $pollmodules) {
    xoops_load('constants', 'xoopspoll');
    xoops_load('pollUtility', 'xoopspoll');
    xoops_load('XoopsRequest');
    xoops_loadLanguage('admin', 'xoopspoll');
    $xpPollHandler =& xoops_getmodulehandler('poll', 'xoopspoll');
} else {
    //is this umfrage?
    if ('umfrage' === $pollmodules) {
        include $GLOBALS['xoops']->path('modules/umfrage/include/constants.php');
        include_once $GLOBALS['xoops']->path('modules/umfrage/class/umfrage.php');
        include_once $GLOBALS['xoops']->path('modules/umfrage/class/umfrageoption.php');
        include_once $GLOBALS['xoops']->path('modules/umfrage/class/umfragelog.php');
        include_once $GLOBALS['xoops']->path('modules/umfrage/class/umfragerenderer.php');
    } else {
        // irmtfan - issue with javascript:history.go(-1)
        redirect_header($_SERVER['HTTP_REFERER'], 2, _MD_POLLMODULE_ERROR);
    }
}

$topic_handler =& xoops_getmodulehandler('topic', 'newbb');
$topic_obj     =& $topic_handler->get($topic_id);
if ($topic_obj instanceof Topic) {
    $forum_id = $topic_obj->getVar('forum_id');
} else {
    redirect_header('index.php', 2, _MD_POLLMODULE_ERROR . ': ' . _MD_FORUMNOEXIST);
}

$forum_handler =& xoops_getmodulehandler('forum', 'newbb');
$forum_obj     =& $forum_handler->get($forum_id);
if (!$forum_handler->getPermission($forum_obj)) {
    redirect_header('index.php', 2, _MD_NORIGHTTOACCESS);
}

if (!$topic_handler->getPermission($forum_obj, $topic_obj->getVar('topic_status'), 'view')) {
    redirect_header('viewforum.php?forum=' . $forum_obj->getVar('forum_id'), 2, _MD_NORIGHTTOVIEW);
}

include $GLOBALS['xoops']->path('header.php');

if (!newbb_isAdmin($forum_obj)) {
    $perm = false;
    if ($topic_handler->getPermission($forum_obj, $topic_obj->getVar('topic_status'), 'addpoll')
        //&& $forum_obj->getVar('allow_polls') == 1 {
    ) {
        if (('add' === $op || 'save' === $op || 'update' === $op) && !$topic_obj->getVar('topic_haspoll') && ($GLOBALS['xoopsUser'] instanceof XoopsUser) && ($GLOBALS['xoopsUser']->getVar('uid') === $topic_obj->getVar('topic_poster'))) {
            $perm = true;
        } elseif (!empty($poll_id) && ($GLOBALS['xoopsUser'] instanceof XoopsUser)) {
            if ('xoopspoll' === $pollmodules) {
                $poll_obj = $xpPollHandler->get($poll_id);
            } else { //Umfrage
                $poll_obj = new Umfrage($poll_id);
            }
            if (($GLOBALS['xoopsUser']->getVar('uid') === $poll_obj->getVar('user_id'))) {
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
    /*
    case "add":
        if ("xoopspoll" == $pollmodules) {
            echo "<h4>" . _AM_XOOPSPOLL_POLLCONF . "</h4>\n";
            $poll_obj = $xpPollHandler->get($poll_id);
            $poll_obj->renderForm($_SERVER['PHP_SELF'], 'post', array("topic_id" => $topic_id));
        } else { // Umfrage
            $poll_form = new XoopsThemeForm(_MD_POLL_CREATNEWPOLL, "poll_form", "polls.php", "post", true);

            $question_text = new XoopsFormText(_MD_POLL_POLLQUESTION, "question", 50, 255);
            $poll_form->addElement($question_text, true);

            $desc_tarea = new XoopsFormTextarea(_MD_POLL_POLLDESC, "description");
            $poll_form->addElement($desc_tarea);

    //        $currenttime = formatTimestamp(time(), "Y-m-d H:i:s");
    //        $endtime = formatTimestamp(time() + 604800, "Y-m-d H:i:s");
            $currenttime = formatTimestamp(time(), _DATESTRING);
            $endtime = formatTimestamp(time() + (86400 * 10), _DATESTRING);

            $expire_text = new XoopsFormText(_MD_POLL_EXPIRATION . "<br /><small>" . _MD_POLL_FORMAT . "<br />" . sprintf(_MD_POLL_CURRENTTIME, $currenttime) . "</small>", "end_time", 30, 19, $endtime);
            $poll_form->addElement($expire_text);

            $weight_text = new XoopsFormText(_MD_POLL_DISPLAYORDER, "weight", 6, 5, 0);
            $poll_form->addElement($weight_text);

            $multi_yn = new XoopsFormRadioYN(_MD_POLL_ALLOWMULTI, "multiple", 0);
            $poll_form->addElement($multi_yn);

            $notify_yn = new XoopsFormRadioYN(_MD_POLL_NOTIFY, "notify", 1);
            $poll_form->addElement($notify_yn);

            $option_tray = new XoopsFormElementTray(_MD_POLL_POLLOPTIONS, "");
            $barcolor_array = XoopsLists::getImgListAsArray(XOOPS_ROOT_PATH . "/modules/{$pollmodules}/assets/images/colorbars/");
            for ($i = 0; $i < 10; ++$i) {
                $current_bar = ("blank.gif" != current($barcolor_array)) ? current($barcolor_array) : next($barcolor_array);
                $option_text = new XoopsFormText("", "option_text[]", 50, 255);
                $option_tray->addElement($option_text);
                $color_select = new XoopsFormSelect("", "option_color[{$i}]", $current_bar);
                $color_select->addOptionArray($barcolor_array);
                $color_select->setExtra("onchange='showImgSelected(\"option_color_image[{$i}]\", \"option_color[{$i}]\", \"modules/{$pollmodules}/assets/images/colorbars\", \"\", \"" . XOOPS_URL . "\")'");
                $color_label = new XoopsFormLabel("", "<img src='" . XOOPS_URL . "/modules/{$pollmodules}/assets/images/colorbars/{$current_bar}' name='option_color_image[{$i}]' id='option_color_image[{$i}]' width='30' class='alignbottom' height='15' alt='' /><br />");
                $option_tray->addElement($color_select);
                $option_tray->addElement($color_label);
                if (!next($barcolor_array)) {
                    reset($barcolor_array);
                }
                unset($color_select, $color_label);
            }
            $poll_form->addElement($option_tray);
            $poll_form->addElement(new XoopsFormButton("", "poll_submit", _SUBMIT, "submit"));
            $poll_form->addElement(new XoopsFormHidden("op", "save"));
            $poll_form->addElement(new XoopsFormHidden("topic_id", $topic_id));

            echo "<h4>" . _MD_POLL_POLLCONF . "</h4>\n";
            $poll_form->display();
        }
        break;

    case "save":
        // check security token
        if (!$GLOBALS['xoopsSecurity']->check()) {
            redirect_header($_SERVER['PHP_SELF'], 2, implode('<br />', $GLOBALS['xoopsSecurity']->getErrors()));
        }

        // make sure the question isn't empty
        if (empty($_POST['question'])) {
            redirect_header($_SERVER['HTTP_REFERER'], 2, _MD_ERROROCCURED . ': ' . _MD_POLL_POLLQUESTION . ' !');
        }

        // Check to see if any options are set
        $option_text = isset($_POST['option_text']) ? $_POST['option_text'] : "";
        $option_string = is_array($option_text) ? implode("", $option_text) : $option_text;
        $option_string = trim($option_string);
        if (empty($option_string)) {
            // irmtfan - issue with javascript:history.go(-1)
            redirect_header($_SERVER['HTTP_REFERER'], 2, _MD_ERROROCCURED . ': ' . _MD_POLL_POLLOPTIONS . ' !');
        }

        if ("xoopspoll" == $pollmodules) {
    //        $poll_obj = $xpPollHandler->create();
            $poll_obj = $xpPollHandler->get($poll_id); // will either get or create poll obj
            $default_poll_duration = XoopspollConstants::DEFAULT_POLL_DURATION;
            $poll_not_mailed       = XoopspollConstants::POLL_NOT_MAILED;
            $poll_mailed           = XoopspollConstants::POLL_MAILED;
            $display               = XoopspollConstants::DO_NOT_DISPLAY_POLL_IN_BLOCK;
        } else { // Umfrage
    //        $poll_obj = new Umfrage();
            if (empty($poll_id)) {  //if creating new poll
                $poll_obj = new Umfrage();
            } else { // updating current poll
                $poll_obj = new Umfrage($poll_id);
            }
            $default_poll_duration = (86400 * 10);
            $poll_not_mailed = POLL_NOTMAILED;
            $poll_mailed     = POLL_MAILED;
            $display         = 0;
        }

        $poll_obj->setVar("question", $_POST['question']);
        $description = (isset($_POST['description'])) ? htmlspecialchars($_POST['description']) : '';
        $poll_obj->setVar("description", $description);

        if (!empty($_POST['end_time'])) {
            $timezone = ($GLOBALS['xoopsUser'] instanceof XoopsUser) ? $GLOBALS['xoopsUser']->getVar("timezone") : null;
            //$poll_obj->setVar("end_time", userTimeToServerTime(strtotime($_POST['end_time']), $timezone));
            //Hack by irmtfan
            $poll_obj->setVar("end_time", userTimeToServerTime(method_exists('XoopsLocal', 'strtotime') ? XoopsLocal::strtotime($_POST['end_time']) : strtotime($_POST['end_time']), $timezone));
        } else {
            // if expiration date is not set, set it
            $poll_obj->setVar("end_time", time() + $default_poll_duration);
        }

        $weight   = isset($_POST['weight'])    ? (int)($_POST['weight']) : 0;
        $multiple = isset($_POST['multiple'])  ? (int)($_POST['multiple']) : 0;
        $notify   = (!empty($_POST["notify"])) ? $poll_not_mailed : $poll_mailed;
        $uid      = ($GLOBALS['xoopsUser'] instanceof XoopsUser) ? $GLOBALS['xoopsUser']->getVar("uid") : 0;

        $poll_obj->setVar("display",  $display);
        $poll_obj->setVar("weight", $weight);
        $poll_obj->setVar("multiple", $multiple);
        $poll_obj->setVar("mail_status", $notify);
        $poll_obj->setVar("user_id", $uid);

        if ('xoopspoll' == $pollmodules) {
            $poll_obj = $xpPollHandler->insert($poll_obj);
            $new_poll_id = ($poll_obj instanceof XoopspollPoll) ? $poll_obj->getVar('poll_id') : null;
        } else { // Umfrage
            $new_poll_id = $poll_obj->store();
        }
        $option_color = empty($_POST['option_color']) ? null : $_POST['option_color'];
        if (!empty($new_poll_id)) {
            $i = 0;
            foreach ($option_text as $optxt) {
                $optxt = trim($optxt);
                if ("" != $optxt) {
                    if ('xoopspoll' == $pollmodules) {
                        $xpOptHandler =& xoops_getmodulehandler('option', 'xoopspoll');
                        $option = $xpOptHandler->create();
                        $option->setVar("option_text", $optxt);
                        $option->setVar("option_color", $option_color[$i]);
                        $option->setVar("poll_id", $new_poll_id);
                        $xpOptHandler->insert($option);
                    } else { // Umfrage
                        $option = new UmfrageOption();
                        $option->setVar("option_text", $optxt);
                        $option->setVar("option_color", $option_color[$i]);
                        $option->setVar("poll_id", $new_poll_id);
                        $option->store();
                    }
                }
                ++$i;
            }
            // update topic to indicate it has a poll
            $topic_obj->setVar('topic_haspoll', 1);
            $topic_obj->setVar('poll_id', $new_poll_id);
            $success = $topic_handler->insert($topic_obj);
            if (!$success) {
                xoops_error($topic_handler->getHtmlErrors());
            }

    //        $sql = "UPDATE " . $GLOBALS['xoopsDB']->prefix("bb_topics") . " SET topic_haspoll = 1, poll_id = {$new_poll_id} WHERE topic_id = {$topic_id}";
    //        if (!$result = $GLOBALS['xoopsDB']->query($sql) ) {
    //            xoops_error($GLOBALS['xoopsDB']->error());
            }

            include_once $GLOBALS['xoops']->path("class/template.php");
            xoops_template_clear_module_cache($GLOBALS['xoopsModule']->getVar('mid'));
            xoops_template_clear_module_cache($xoopspoll->getVar('mid'));
        } else {
            xoops_error($poll_obj->getHtmlErrors());
            exit();
        }
        // irmtfan full URL
        redirect_header($GLOBALS['xoops']->url("modules/".$xoopsModule->getVar("dirname")."/viewtopic.php?topic_id={$topic_id}"), 1, _MD_POLL_DBUPDATED);
        break;
    */
    case 'add':
    case 'edit':
        if ('xoopspoll' === $pollmodules) {
            echo '<h4>' . _MD_POLL_EDITPOLL . "</h4>\n";
            $poll_obj = $xpPollHandler->get($poll_id); // will create poll if poll_id = 0 exist
            $poll_obj->renderForm($_SERVER['PHP_SELF'], 'post', array('topic_id' => $topic_id));
        } else { // Umfrage
            if (empty($poll_id)) {
                $poll_obj = new Umfrage();
            } else {
                $poll_obj = new Umfrage($poll_id);
            }
            $poll_form    = new XoopsThemeForm(_MD_POLL_EDITPOLL, 'poll_form', 'polls.php', 'post', true);
            $author_label = new XoopsFormLabel(_MD_POLL_AUTHOR, "<a href='" . XOOPS_URL . '/userinfo.php?uid=' . $poll_obj->getVar('user_id') . "'>" . newbb_getUnameFromId($poll_obj->getVar('user_id'), $GLOBALS['xoopsModuleConfig']['show_realname']) . '</a>');
            $poll_form->addElement($author_label);
            $question_text = new XoopsFormText(_MD_POLL_POLLQUESTION, 'question', 50, 255, $poll_obj->getVar('question', 'E'));
            $poll_form->addElement($question_text);
            $desc_tarea = new XoopsFormTextarea(_MD_POLL_POLLDESC, 'description', $poll_obj->getVar('description', 'E'));
            $poll_form->addElement($desc_tarea);
            //        $date = formatTimestamp($poll_obj->getVar("end_time"), "Y-m-d H:i:s");
            $date = formatTimestamp($poll_obj->getVar('end_time'), _DATESTRING);
            if (!$poll_obj->hasExpired()) {
                $expire_text = new XoopsFormText(_MD_POLL_EXPIRATION . '<br /><small>' . _MD_POLL_FORMAT . '<br />' . sprintf(_MD_POLL_CURRENTTIME, formatTimestamp(time(), 'Y-m-d H:i:s')) . '</small>', 'end_time', 20, 19, $date);
                $poll_form->addElement($expire_text);
            } else {
                // irmtfan full URL - add topic_id
                $restart_label = new XoopsFormLabel(_MD_POLL_EXPIRATION, sprintf(_MD_POLL_EXPIREDAT, $date) . "<br /><a href='" . XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . "/polls.php?op=restart&amp;poll_id={$poll_id}&amp;topic_id={$topic_id}'>" . _MD_POLL_RESTART . '</a>');
                $poll_form->addElement($restart_label);
            }
            $weight_text = new XoopsFormText(_MD_POLL_DISPLAYORDER, 'weight', 6, 5, $poll_obj->getVar('weight'));
            $poll_form->addElement($weight_text);
            $multi_yn = new XoopsFormRadioYN(_MD_POLL_ALLOWMULTI, 'multiple', $poll_obj->getVar('multiple'));
            $poll_form->addElement($multi_yn);
            $options_arr  =& UmfrageOption::getAllByPollId($poll_id);
            $notify_value = 1;
            if (0 !== $poll_obj->getVar('mail_status')) {
                $notify_value = 0;
            }
            $notify_yn = new XoopsFormRadioYN(_MD_POLL_NOTIFY, 'notify', $notify_value);
            $poll_form->addElement($notify_yn);
            $option_tray    = new XoopsFormElementTray(_MD_POLL_POLLOPTIONS, '');
            $barcolor_array = XoopsLists::getImgListAsArray($GLOBALS['xoops']->path("modules/{$pollmodules}/assets/images/colorbars/"));
            $i              = 0;
            foreach ($options_arr as $option) {
                $option_tray->addElement(new XoopsFormText('', 'option_text[]', 50, 255, $option->getVar('option_text')));
                $option_tray->addElement(new XoopsFormHidden('option_id[]', $option->getVar('option_id')));
                $color_select = new XoopsFormSelect('', "option_color[{$i}]", $option->getVar('option_color'));
                $color_select->addOptionArray($barcolor_array);
                $color_select->setExtra("onchange='showImgSelected(\"option_color_image[{$i}]\", \"option_color[" . $i . "]\", \"modules/{$pollmodules}/assets/images/colorbars\", \"\", \"" . XOOPS_URL . "\")'");
                $color_label = new XoopsFormLabel('', "<img src='" . $GLOBALS['xoops']->url("modules/{$pollmodules}/assets/images/colorbars/" . $option->getVar('option_color', 'E')) . "' name='option_color_image[{$i}]' id='option_color_image[{$i}]' class='alignbottom' width='30' height='15' alt='' /><br />");
                $option_tray->addElement($color_select);
                $option_tray->addElement($color_label);
                unset($color_select, $color_label);
                ++$i;
            }
            // irmtfan full URL
            $more_label = new XoopsFormLabel('', "<br /><a href='" . XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . "/polls.php?op=addmore&amp;poll_id={$poll_id}&amp;topic_id={$topic_id}'>" . _MD_POLL_ADDMORE . '</a>');
            $option_tray->addElement($more_label);
            $poll_form->addElement($option_tray);
            $poll_form->addElement(new XoopsFormHidden('op', 'update'));
            $poll_form->addElement(new XoopsFormHidden('topic_id', $topic_id));
            $poll_form->addElement(new XoopsFormHidden('poll_id', $poll_id));
            //        $poll_form->addElement(new XoopsFormButton("", "poll_submit", _SUBMIT, "submit"));
            $poll_form->addElement(new XoopsFormButtonTray('poll_submit', _SUBMIT, 'submit'));

            echo '<h4>' . _MD_POLL_POLLCONF . "</h4>\n";
            $poll_form->display();
        }
        break;

    case 'save':
    case 'update':
        // check security token
        if (!$GLOBALS['xoopsSecurity']->check()) {
            redirect_header($_SERVER['PHP_SELF'], 2, implode('<br />', $GLOBALS['xoopsSecurity']->getErrors()));
        }

        /* make sure there's at least one option */
        $option_text   = XoopsRequest::getString('option_text', '', 'POST');
        $option_string = is_array($option_text) ? implode('', $option_text) : $option_text;
        $option_string = trim($option_string);
        if (empty($option_string)) {
            // irmtfan - issue with javascript:history.go(-1)
            redirect_header($_SERVER['HTTP_REFERER'], 2, _MD_ERROROCCURED . ': ' . _MD_POLL_POLLOPTIONS . ' !');
        }

        if ('xoopspoll' === $pollmodules) {
            $poll_obj     = $xpPollHandler->get($poll_id);
            $xpOptHandler =& xoops_getmodulehandler('option', 'xoopspoll');
            $xpLogHandler =& xoops_getmodulehandler('log', 'xoopspoll');

            $notify = XoopsRequest::getInt('notify', XoopspollConstants::NOTIFICATION_ENABLED, 'POST');

            $currentTimestamp = time();
            $xuEndTimestamp   = strtotime(XoopsRequest::getString('xu_end_time', null, 'POST'));
            $endTimestamp     = (empty($xuEndTimestamp)) ? ($currentTimestamp + XoopspollConstants::DEFAULT_POLL_DURATION) : userTimeToServerTime($xuEndTimestamp);
            $xuStartTimestamp = strtotime(XoopsRequest::getString('xu_start_time', null, 'POST'));
            $startTimestamp   = (empty($xuStartTimestamp)) ? ($endTimestamp - XoopspollConstants::DEFAULT_POLL_DURATION) : userTimeToServerTime($xuStartTimestamp);

            //  don't allow changing start time if there are votes in the log
            if (($startTimestamp < $poll_obj->getVar('start_time')) && ($xpLogHandler->getTotalVotesByPollId($poll_id) > 0)) {
                $startTimestamp = $poll_obj->getVar('start_time'); //don't change start time
            }

            $poll_vars = array(
                'user_id'     => XoopsRequest::getInt('user_id', $GLOBALS['xoopsUser']->uid(), 'POST'),
                'question'    => XoopsRequest::getString('question', null, 'POST'),
                'description' => XoopsRequest::getText('description', null, 'POST'),
                'mail_status' => (XoopspollConstants::NOTIFICATION_ENABLED === $notify) ? XoopspollConstants::POLL_NOT_MAILED : XoopspollConstants::POLL_MAILED,
                'mail_voter'  => XoopsRequest::getInt('mail_voter', XoopspollConstants::NOT_MAIL_POLL_TO_VOTER, 'POST'),
                'start_time'  => $startTimestamp,
                'end_time'    => $endTimestamp,
                'display'     => XoopsRequest::getInt('display', XoopspollConstants::DO_NOT_DISPLAY_POLL_IN_BLOCK, 'POST'),
                'visibility'  => XoopsRequest::getInt('visibility', XoopspollConstants::HIDE_NEVER, 'POST'),
                'weight'      => XoopsRequest::getInt('weight', XoopspollConstants::DEFAULT_WEIGHT, 'POST'),
                'multiple'    => XoopsRequest::getInt('multiple', XoopspollConstants::NOT_MULTIPLE_SELECT_POLL, 'POST'),
                'multilimit'  => XoopsRequest::getInt('multilimit', XoopspollConstants::MULTIPLE_SELECT_LIMITLESS, 'POST'),
                'anonymous'   => XoopsRequest::getInt('anonymous', XoopspollConstants::ANONYMOUS_VOTING_DISALLOWED, 'POST'),);
            $poll_obj->setVars($poll_vars);
            $poll_id = $xpPollHandler->insert($poll_obj);
            if (!$poll_id) {
                $err = $poll_obj->getHtmlErrors();
                exit($err);
            }

            // now get the options
            $optionIdArray    = XoopsRequest::getArray('option_id', array(), 'POST');
            $optionIdArray    = array_map('intval', $optionIdArray);
            $optionTextArray  = XoopsRequest::getArray('option_text', array(), 'POST');
            $optionColorArray = XoopsRequest::getArray('option_color', array(), 'POST');

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
            //        include_once $GLOBALS['xoops']->path("class" . "/template.php");
            //        xoops_template_clear_module_cache($GLOBALS['xoopsModule']->getVar('mid'));
            //        xoops_template_clear_module_cache($xoopspoll->getVar('mid'));
            //        redirect_header("viewtopic.php?topic_id={$topic_id}", XoopspollConstants::REDIRECT_DELAY_SHORT, _MD_POLL_DBUPDATED);

        } else { // Umfrage
            $poll_obj = new Umfrage($poll_id);
            $poll_obj->setVar('question', @$_POST['question']);
            $poll_obj->setVar('description', @$_POST['description']);
            $end_time = XoopsRequest::getString('end_time', '', 'POST');
            if (!empty($end_time)) {
                $timezone = ($GLOBALS['xoopsUser'] instanceof XoopsUser) ? $GLOBALS['xoopsUser']->getVar('timezone') : null;
                //            $poll_obj->setVar("end_time", userTimeToServerTime(strtotime($end_time), $timezone));
                //Hack by Irmtfan
                $poll_obj->setVar('end_time', userTimeToServerTime(method_exists('XoopsLocal', 'strtotime') ? XoopsLocal::strtotime($end_time) : strtotime($end_time), $timezone));
            }
            $poll_obj->setVar('display', 0);
            $poll_obj->setVar('weight', (int)(@$_POST['weight']));
            $poll_obj->setVar('multiple', (int)(@$_POST['multiple']));
            if (!empty($_POST['notify']) && $end_time > time()) {
                // if notify, set mail status to 'not mailed'
                $poll_obj->setVar('mail_status', POLL_NOTMAILED);
            } else {
                // if not notify, set mail status to already "mailed"
                $poll_obj->setVar('mail_status', POLL_MAILED);
            }

            if (!$poll_obj->store()) {
                xoops_error($poll_obj->getHtmlErrors);
                break;
            }
            $i            = 0;
            $option_id    = (empty($_POST['option_id'])) ? null : $_POST['option_id'];
            $option_color = (empty($_POST['option_color'])) ? null : $_POST['option_color'];
            foreach ($option_id as $opid) {
                $option_obj      = new UmfrageOption($opid);
                $option_text[$i] = trim($option_text[$i]);
                if ($option_text[$i] !== '') {
                    $option_obj->setVar('option_text', $option_text[$i]);
                    $option_obj->setVar('option_color', $option_color[$i]);
                    $option_obj->store();
                } else {
                    if ($option_obj->delete() !== false) {
                        UmfrageLog::deleteByOptionId($option->getVar('option_id'));
                    }
                }
                ++$i;
            }
            $poll_obj->updateCount();
            //        include_once $GLOBALS['xoops']->path("class" . "/template.php");
            //        xoops_template_clear_module_cache($GLOBALS['xoopsModule']->getVar('mid'));
            //        redirect_header("viewtopic.php?topic_id={$topic_id}", 1, _MD_POLL_DBUPDATED);
        }

        // clear the template cache so changes take effect immediately
        include_once $GLOBALS['xoops']->path('class/template.php');
        xoops_template_clear_module_cache($GLOBALS['xoopsModule']->getVar('mid'));
        xoops_template_clear_module_cache($xoopspoll->getVar('mid'));

        // update topic to indicate it has a poll
        $topic_obj->setVar('topic_haspoll', 1);
        $topic_obj->setVar('poll_id', $poll_obj->getVar('poll_id'));
        $success = $topic_handler->insert($topic_obj);
        if (!$success) {
            xoops_error($topic_handler->getHtmlErrors());
        } else {
            redirect_header("viewtopic.php?topic_id={$topic_id}", 2, _MD_POLL_DBUPDATED);
        }
        break;

    case 'addmore':
        if ('xoopspoll' === $pollmodules) {
            $poll_obj     = $xpPollHandler->get($poll_id);
            $xpOptHandler =& xoops_getmodulehandler('option', 'xoopspoll');
        } else { // Umfrage
            $poll_obj = new Umfrage($poll_id);
        }
        $question = $poll_obj->getVar('question');
        unset($poll_obj);
        $poll_form = new XoopsThemeForm(_MD_POLL_ADDMORE, 'poll_form', 'polls.php', 'post', true);
        $poll_form->addElement(new XoopsFormLabel(_MD_POLL_POLLQUESTION, $question));
        if ('xoopspoll' === $pollmodules) {
            $option_tray = $xpOptHandler->renderOptionFormTray($poll_id);
        } else {
            $option_tray    = new XoopsFormElementTray(_MD_POLL_POLLOPTIONS, '');
            $barcolor_array = XoopsLists::getImgListAsArray($GLOBALS['xoops']->path("modules/{$pollmodules}/assets/images/colorbars/"));
            for ($i = 0; $i < 10; ++$i) {
                $current_bar = (current($barcolor_array) !== 'blank.gif') ? current($barcolor_array) : next($barcolor_array);
                $option_text = new XoopsFormText('', 'option_text[]', 50, 255);
                $option_tray->addElement($option_text);
                $color_select = new XoopsFormSelect('', "option_color[{$i}]", $current_bar);
                $color_select->addOptionArray($barcolor_array);
                $color_select->setExtra("onchange='showImgSelected(\"option_color_image[{$i}]\", \"option_color[{$i}]\", \"modules/{$pollmodules}/assets/images/colorbars\", \"\", \"" . XOOPS_URL . "\")'");
                $color_label = new XoopsFormLabel('', "<img src='" . $GLOBALS['xoops']->url("modules/{$pollmodules}/assets/images/colorbars/{$current_bar}") . "' name='option_color_image[{$i}]' id='option_color_image[{$i}]' class='alignbottom' width='30' height='15' alt='' /><br />");
                $option_tray->addElement($color_select);
                $option_tray->addElement($color_label);
                unset($color_select, $color_label, $option_text);
                if (!next($barcolor_array)) {
                    reset($barcolor_array);
                }
            }
        }
        $poll_form->addElement($option_tray);
        $poll_form->addElement(new XoopsFormButtonTray('poll_submit', _SUBMIT, 'submit'));
        //    $poll_form->addElement(new XoopsFormButton('', 'poll_submit', _SUBMIT, 'submit'));
        $poll_form->addElement(new XoopsFormHidden('op', 'savemore'));
        $poll_form->addElement(new XoopsFormHidden('topic_id', $topic_id));
        $poll_form->addElement(new XoopsFormHidden('poll_id', $poll_id));

        echo '<h4>' . _MD_POLL_POLLCONF . "</h4>\n";
        $poll_form->display();
        break;

    case 'savemore':
        // check security token
        if (!$GLOBALS['xoopsSecurity']->check()) {
            redirect_header($_SERVER['PHP_SELF'], 2, implode('<br />', $GLOBALS['xoopsSecurity']->getErrors()));
        }

        $option_text   = isset($_POST['option_text']) ? $_POST['option_text'] : '';
        $option_string = is_array($option_text) ? implode('', $option_text) : $option_text;
        $option_string = trim($option_string);
        if (empty($option_string)) {
            // irmtfan - issue with javascript:history.go(-1)
            redirect_header($_SERVER['HTTP_REFERER'], 2, _MD_ERROROCCURED . ': ' . _MD_POLL_POLLOPTIONS . ' !');
        }

        if ($pollmodules === 'xoopspoll') {
            $xpOptHandler =& xoops_getmodulehandler('option', 'xoopspoll');
        }
        $i            = 0;
        $option_color = (empty($_POST['option_color'])) ? null : $_POST['option_color'];
        foreach ($option_text as $optxt) {
            $optxt = trim($optxt);
            if ('' !== $optxt) {
                if ('xoopspoll' === $pollmodules) {
                    $option_obj = $xpOptHandler->create();
                    $option_obj->setVar('option_text', $optxt);
                    $option_obj->setVar('poll_id', $poll_id);
                    $option_obj->setVar('option_color', $option_color[$i]);
                    $xpOptHandler->insert($option_obj);
                } else { // Umfrage
                    $option_obj = new UmfrageOption();
                    $option_obj->setVar('option_text', $optxt);
                    $option_obj->setVar('poll_id', $poll_id);
                    $option_obj->setVar('option_color', $option_color[$i]);
                    $option_obj->store();
                }
                unset($option_obj);
            }
            ++$i;
        }
        include_once $GLOBALS['xoops']->path('class/template.php');
        xoops_template_clear_module_cache($GLOBALS['xoopsModule']->getVar('mid'));
        xoops_template_clear_module_cache($xoopspoll->getVar('mid'));
        redirect_header("polls.php?op=edit&amp;poll_id={$poll_id}&amp;topic_id={$topic_id}", 2, _MD_POLL_DBUPDATED);
        break;

    case 'delete':
        echo '<h4>' . _MD_POLL_POLLCONF . "</h4>\n";
        if ('xoopspoll' === $pollmodules) {
            $poll_obj = $xpPollHandler->get($poll_id);
        } else {
            $poll_obj = new Umfrage($poll_id);
        }
        xoops_confirm(array('op' => 'delete_ok', 'topic_id' => $topic_id, 'poll_id' => $poll_id), 'polls.php', sprintf(_MD_POLL_RUSUREDEL, $poll_obj->getVar('question')));
        break;

    case 'delete_ok':
        // check security token
        if (!$GLOBALS['xoopsSecurity']->check()) {
            redirect_header($_SERVER['PHP_SELF'], 2, implode('<br />', $GLOBALS['xoopsSecurity']->getErrors()));
        }
        //try and delete the poll
        if ('xoopspoll' === $pollmodules) {
            $poll_obj = $xpPollHandler->get($poll_id);
            $status   = $xpPollHandler->delete($poll_obj);
            if (false !== $status) {
                $xpOptHandler =& xoops_getmodulehandler('option', 'xoopspoll');
                $xpLogHandler =& xoops_getmodulehandler('log', 'xoopspoll');
                $xpOptHandler->deleteByPollId($poll_id);
                $xpLogHandler->deleteByPollId($poll_id);
            } else {
                $msg = $xpPollHandler->getHtmlErrors();
            }
        } else {
            $poll_obj = new Umfrage($poll_id);
            $status   = $poll_obj->delete();
            if (false !== $status) {
                UmfrageOption::deleteByPollId($poll_id);
                UmfrageLog::deleteByPollId($poll_id);
            } else {
                $msg = $poll_obj->getHtmlErrors();
            }
        }
        if (false !== $status) {
            include_once $GLOBALS['xoops']->path('class/template.php');
            xoops_template_clear_module_cache($xoopsModule->getVar('mid'));
            xoops_template_clear_module_cache($xoopspoll->getVar('mid'));
            // delete comments for this poll
            xoops_comment_delete($xoopsModule->getVar('mid'), $poll_id);

            $topic_obj->setVar('votes', 0); // not sure why we want to clear votes too... but I left it alone
            $topic_obj->setVar('topic_haspoll', 0);
            $topic_obj->setVar('poll_id', 0);
            $success = $topic_handler->insert($topic_obj);
            if (!$success) {
                xoops_error($topic_handler->getHtmlErrors());
                break;
            }
            /*
                    $sql = "UPDATE " . $xoopsDB->prefix("bb_topics") . " SET votes = 0, topic_haspoll = 0, poll_id = 0 WHERE topic_id = {$topic_id}";
                    if ( !$result = $xoopsDB->query($sql) ) {
                        //xoops_error($xoopsDB->error());
                    }
            */
        } else {
            xoops_error($msg);
            break;
        }
        redirect_header("viewtopic.php?topic_id={$topic_id}", 1, _MD_POLL_DBUPDATED);
        break;

    case 'restart':
        if ('xoopspoll' === $pollmodules) {
            $default_poll_duration = XoopspollConstants::DEFAULT_POLL_DURATION;
        } else { // Umfrage
            $default_poll_duration = (86400 * 10);
        }
        $poll_form = new XoopsThemeForm(_MD_POLL_RESTARTPOLL, 'poll_form', 'polls.php', 'post', true);
        //    $expire_text = new XoopsFormText(_MD_POLL_EXPIRATION . "<br /><small>" . _MD_POLL_FORMAT . "<br />" . sprintf(_MD_POLL_CURRENTTIME, formatTimestamp(time(), "Y-m-d H:i:s")) . "</small>", "end_time", 20, 19, formatTimestamp(time() + 604800, "Y-m-d H:i:s"));
        $expire_text = new XoopsFormText(_MD_POLL_EXPIRATION . '<br /><small>' . _MD_POLL_FORMAT . '<br />' . sprintf(_MD_POLL_CURRENTTIME, formatTimestamp(time(), _DATESTRING)) . '</small>', 'end_time', 20, 19, formatTimestamp(time() + $default_poll_duration, _DATESTRING));
        $poll_form->addElement($expire_text);
        $poll_form->addElement(new XoopsFormRadioYN(_MD_POLL_NOTIFY, 'notify', 1));
        $poll_form->addElement(new XoopsFormRadioYN(_MD_POLL_RESET, 'reset', 0));
        $poll_form->addElement(new XoopsFormHidden('op', 'restart_ok'));
        $poll_form->addElement(new XoopsFormHidden('topic_id', $topic_id));
        $poll_form->addElement(new XoopsFormHidden('poll_id', $poll_id));
        $poll_form->addElement(new XoopsFormButton('', 'poll_submit', _MD_POLL_RESTART, 'submit'));

        echo '<h4>' . _MD_POLL_POLLCONF . "</h4>\n";
        $poll_form->display();

        break;

    case 'restart_ok':
        // check security token
        if (!$GLOBALS['xoopsSecurity']->check()) {
            redirect_header($_SERVER['PHP_SELF'], 2, implode('<br />', $GLOBALS['xoopsSecurity']->getErrors()));
        }

        if ('xoopspoll' === $pollmodules) {
            $poll_obj              = $xpPollHandler->get($poll_id);
            $default_poll_duration = XoopspollConstants::DEFAULT_POLL_DURATION;
            $poll_mailed           = XoopspollConstants::POLL_MAILED;
            $poll_not_mailed       = XoopspollConstants::POLL_NOT_MAILED;
        } else { // Umfrage
            $poll_obj              = new Umfrage($poll_id);
            $default_poll_duration = (86400 * 10);
            $poll_not_mailed       = POLL_NOTMAILED;
            $poll_mailed           = POLL_MAILED;
        }

        $end_time = (empty($_POST['end_time'])) ? 0 : (int)($_POST['end_time']);
        if (!empty($end_time)) {
            $timezone = ($GLOBALS['xoopsUser'] instanceof XoopsUser) ? $GLOBALS['xoopsUser']->getVar('timezone') : null;
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

        if ('xoopspoll' === $pollmodules) {
            if (!$xpPollHandler->insert($poll_obj)) {  // update the poll
                xoops_error($poll_obj->getHtmlErrors());
                exit();
            }
            if (!empty($_POST['reset'])) { // reset all vote/voter counters
                $xpLogHandler =& xoops_getmodulehandler('log', 'xoopspoll');
                $xpLogHandler->deleteByPollId($poll_id);
                $xpOptHandler =& xoops_getmodulehandler('option', 'xoopspoll');
                $xpOptHandler->resetCountByPollId($poll_id);
                $xpPollHandler->updateCount($poll_obj);
            }
        } else {
            if (!$poll_obj->store()) { // update the poll
                xoops_error($poll_obj->getHtmlErrors());
                exit();
            }
            if (!empty($_POST['reset'])) { // reset all logs
                UmfrageLog::deleteByPollId($poll_id);
                UmfrageOption::resetCountByPollId($poll_id);
                $poll_obj->updateCount();
            }
        }

        // clear the topic votes
        /*
            $topic_obj->setVar('votes', 0);
            $success = $topic_handler->insert($topic_obj);
            if (!$success) {
                xoops_error($topic_handler->getHtmlErrors());
                break;
            }
        */
        include_once $GLOBALS['xoops']->path('class/template.php');
        xoops_template_clear_module_cache($xoopsModule->getVar('mid'));
        xoops_template_clear_module_cache($xoopspoll->getVar('mid'));
        redirect_header("viewtopic.php?topic_id={$topic_id}", 1, _MD_POLL_DBUPDATED);
        break;

    case 'log':
        if ('xoopspoll' === $pollmodules) {
            redirect_header($GLOBALS['xoops']->url("modules/xoopspoll/admin/main.php?op=log&amp;poll_id={$poll_id}"), 2, _MD_LOG_XOOPSPOLL_ADMIN_REDIRECT);
        } else {
            echo '<h4>' . _MD_POLL_POLLCONF . "</h4>\n" . '<br />' . _MD_VIEW_LOG . "\n" . '<br />' . _MD_LOG_NOT_IMPLEMENTED . "\n";
        }
        break;
}

// irmtfan move to footer.php
include_once __DIR__ . '/footer.php';
include $GLOBALS['xoops']->path('footer.php');
