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
 * XOOPS Poll Administration
 * Routines to manage administration of CRUD and display of polls
 *
 * @copyright ::  {@link http://xoops.org/ XOOPS Project}
 * @license   ::    {@link http://www.fsf.org/copyleft/gpl.html GNU public license}
 * @package   ::    xoopspoll
 * @subpackage::    admin
 * @author    ::    Xoops Module Team
 * @since     ::    1.0
 * @version   ::    $Id: $
 *
 * @uses      xoops_load() to instantiate needed classes
 * @uses      XoopsFormloader
 * @uses      Xoopslists
 * @uses      CriteriaCompo
 * @uses      Criteria
 * @uses      xoops_getmodulehandler() to load this modules class handlers
 * @uses      ModuleAdmin class to display module administration page navigation
 * @uses      $GLOBALS['xoopsSecurity']::getTokenHTML() used for security on input of form data
 * @uses      $GLOBALS['xoops'] class::methods used to get general information about XOOPS
 * @uses      XoopsPageNav class to display page navigation links for multiple pages of data
 * @uses      xoops_template_clear_module_cache() function used to clear cache after data has been updated
 * @uses      redirect_header() function to send user to page after completing task(s)
 */

require_once __DIR__ . '/admin_header.php';
include_once $GLOBALS['xoops']->path('class/xoopsblock.php');

xoops_load('xoopsformloader');
xoops_load('xoopslists');
xoops_load('renderer', 'xoopspoll');
xoops_load('pollUtility', 'xoopspoll');
xoops_load('XoopsRequest');

$op = XoopsRequest::getCmd('op', XoopsRequest::getCmd('op', 'list', 'POST'), 'GET');
switch ($op) {

    case 'list':
    default:
        $limit = XoopsRequest::getInt('limit', XoopspollConstants::DEFAULT_POLL_PAGE_LIMIT);
        $start = XoopsRequest::getInt('start', 0);

        $pollHandler =& xoops_getmodulehandler('poll', 'xoopspoll');
        $criteria    = new CriteriaCompo();
        $criteria->setLimit($limit + 1);
        $criteria->setStart($start);
        $criteria->setSort('weight ASC, start_time');  // trick criteria to allow 2 sort criteria
        $criteria->setOrder('ASC');
        $pollObjs   = $pollHandler->getAll($criteria);
        $pollsCount = count($pollObjs);

        //    $GLOBALS['xoopsOption']['template_main'] = 'xoopspoll_list.html';
        xoops_cp_header();
        $admin_class = new ModuleAdmin();

        $xoopsTpl->assign('navigation', $admin_class->addNavigation('main.php'));
        $admin_class->addItemButton(_AM_XOOPSPOLL_CREATENEWPOLL, 'main.php' . '?op=add', $icon = 'add');
        $xoopsTpl->assign('addPollButton', $admin_class->renderButton());

        $renderedNav = '';

        if (is_array($pollObjs) && $pollsCount > 0) {
            /* if newbb forum module is loaded find poll/topic association */
            $module_handler =& xoops_gethandler('module');
            $newbbModule    =& $module_handler->getByDirname('newbb');
            if (($newbbModule instanceof XoopsModule) && $newbbModule->isactive()) {
                $topic_handler =& xoops_getmodulehandler('topic', 'newbb');
                $topicFields   = array('topic_id', 'topic_title', 'poll_id');
                $criteria      = new CriteriaCompo();
                $criteria->add(new Criteria('topic_haspoll', 0, '>'));
                $pollsWithTopics = array();
                $topicsWithPolls = $topic_handler->getAll($criteria, $topicFields, false);
                foreach ($topicsWithPolls as $pollTopics) {
                    $pollsWithTopics[$pollTopics['poll_id']] = array(
                        'topic_id'    => $pollTopics['topic_id'],
                        'topic_title' => $pollTopics['topic_title']);
                }
                if (!empty($pollsWithTopics)) {
                    $admin_class->addInfoBox(_AM_XOOPSPOLL_NEWBB_SUPPORT);
                    $admin_class->addInfoBoxLine(_AM_XOOPSPOLL_NEWBB_SUPPORT, "<img src='" . $pathIcon16 . "/forum.png' alt='" . _AM_XOOPSPOLL_NEWBB_SUPPORT . "' /> " . _AM_XOOPSPOLL_NEWBB_INTRO, null, null, 'information');
                    $newbbIntro = $admin_class->renderInfoBox();
                } else {
                    $newbbIntro = '';
                }
            } else {
                $pollsWithTopics = array();
                $newbbIntro      = '';
            }
            $xoopsTpl->assign('newbbIntro', $newbbIntro);
            $xoopsTpl->assign('securityToken', $GLOBALS['xoopsSecurity']->getTokenHTML());

            $pollItems = array();
            foreach ($pollObjs as $pollObj) {
                $pollVars = $pollObj->getValues();
                $id       = $pollVars['poll_id'];

                if (array_key_exists($id, $pollsWithTopics)) {
                    $topic_id    = $pollsWithTopics[$id]['topic_id'];
                    $topic_title = $pollsWithTopics[$id]['topic_title'];
                } else {
                    $topic_id    = 0;
                    $topic_title = '';
                }

                $checked = (XoopspollConstants::DISPLAY_POLL_IN_BLOCK === $pollVars['display']) ? " checked='checked'" : '';

                $xuCurrentTimestamp   = xoops_getUserTimestamp(time());
                $xuCurrentFormatted   = ucfirst(date(_MEDIUMDATESTRING, $xuCurrentTimestamp));
                $xuStartTimestamp     = xoops_getUserTimestamp($pollVars['start_time']);
                $xuStartFormattedTime = ucfirst(date(_MEDIUMDATESTRING, $xuStartTimestamp));
                $xuEndTimestamp       = xoops_getUserTimestamp($pollVars['end_time']);

                if ($xuEndTimestamp > $xuCurrentTimestamp) {
                    $end = ucfirst(date(_MEDIUMDATESTRING, $xuEndTimestamp)); // formatted output for current user
                } else {
                    $end = "<span class='red'>" . _AM_XOOPSPOLL_EXPIRED . '</span><br />' . "<a href='" . $_SERVER['PHP_SELF'] . "?op=restart&amp;poll_id={$id}'>" . _AM_XOOPSPOLL_RESTART . '</a>';
                }

                $pollItems[$id] = array(
                    'question'             => $pollVars['question'],
                    'id'                   => $id,
                    'weight'               => $pollVars['weight'],
                    'topic_id'             => $topic_id,
                    'topic_title'          => $topic_title,
                    'checked'              => $checked,
                    'voters'               => $pollVars['voters'],
                    'votes'                => $pollVars['votes'],
                    'xuStartFormattedTime' => $xuStartFormattedTime,
                    'end'                  => $end,
                    'buttons'              => array(
                        'edit'   => array(
                            'href' => $_SERVER['PHP_SELF'] . "?op=edit&amp;poll_id={$id}",
                            'file' => $pathIcon16 . '/edit.png',
                            'alt'  => _AM_XOOPSPOLL_EDITPOLL),
                        'clone'  => array(
                            'href' => $_SERVER['PHP_SELF'] . "?op=clone&amp;poll_id={$id}",
                            'file' => $pathIcon16 . '/editcopy.png',
                            'alt'  => _AM_XOOPSPOLL_CLONE),
                        'delete' => array(
                            'href' => $_SERVER['PHP_SELF'] . "?op=delete&amp;poll_id={$id}",
                            'file' => $pathIcon16 . '/delete.png',
                            'alt'  => _DELETE),
                        'log'    => array(
                            'href' => $_SERVER['PHP_SELF'] . "?op=log&amp;poll_id={$id}",
                            'file' => $pathIcon16 . '/search.png',
                            'alt'  => _AM_XOOPSPOLL_VIEWLOG)));
                if ($topic_id > 0) {
                    $pollItems[$id]['buttons']['forum'] = array(
                        'href' => $GLOBALS['xoops']->url('modules/newbb/viewtopic.php') . "?topic_id={$topic_id}",
                        'file' => $pathIcon16 . '/forum.png',
                        'alt'  => _AM_XOOPSPOLL_NEWBB_TOPIC . '&nbsp;' . htmlspecialchars($topic_title));
                }
            }
            xoops_load('pagenav');
            $pageNav     = new XoopsPageNav($pollsCount, $limit, $start);
            $renderedNav = $pageNav->renderNav();
        }

        $xoopsTpl->assign('pollItems', $pollItems);
        $xoopsTpl->assign('rendered_nav', $renderedNav);
        $xoopsTpl->assign('self', $_SERVER['PHP_SELF']);
        $xoopsTpl->display($GLOBALS['xoops']->path('modules/xoopspoll/templates/admin/xoopspoll_list.tpl'));
        include_once __DIR__ . '/admin_footer.php';
        exit();
        break;

    case 'edit':
    case 'add':
        $optHandler  =& xoops_getmodulehandler('option', 'xoopspoll');
        $pollHandler =& xoops_getmodulehandler('poll', 'xoopspoll');
        $pollId      = XoopsRequest::getInt('poll_id', 0);
        $pollObj     = $pollHandler->get($pollId); // will auto create object if poll_id=0

        // display the form
        xoops_cp_header();
        $admin_class = new ModuleAdmin();
        echo $admin_class->addNavigation('main.php');
        $pollObj->renderForm($_SERVER['PHP_SELF'], 'post');
        include_once __DIR__ . '/admin_footer.php';
        exit();
        break;

    case 'update':
        if (!$GLOBALS['xoopsSecurity']->check()) {
            redirect_header($_SERVER['PHP_SELF'], XoopspollConstants::REDIRECT_DELAY_MEDIUM, implode('<br />', $GLOBALS['xoopsSecurity']->getErrors()));
        }

        $optHandler  =& xoops_getmodulehandler('option', 'xoopspoll');
        $logHandler  =& xoops_getmodulehandler('log', 'xoopspoll');
        $pollHandler =& xoops_getmodulehandler('poll', 'xoopspoll');

        $pollId  = XoopsRequest::getInt('poll_id', 0, 'POST');
        $pollObj = $pollHandler->get($pollId);

        $notify = XoopsRequest::getInt('notify', XoopspollConstants::NOTIFICATION_ENABLED, 'POST');

        $currentTimestamp = time();
        $xuEndTimestamp   = strtotime(XoopsRequest::getString('xu_end_time', null, 'POST'));
        $endTimestamp     = (empty($xuEndTimestamp)) ? ($currentTimestamp + XoopspollConstants::DEFAULT_POLL_DURATION) : userTimeToServerTime($xuEndTimestamp);
        $xuStartTimestamp = strtotime(XoopsRequest::getString('xu_start_time', null, 'POST'));
        $startTimestamp   = (empty($xuStartTimestamp)) ? ($endTimestamp - XoopspollConstants::DEFAULT_POLL_DURATION) : userTimeToServerTime($xuStartTimestamp);

        //  don't allow changing start time if there are votes in the log
        if (($startTimestamp < $pollObj->getVar('start_time')) && ($logHandler->getTotalVotesByPollId($pollId) > 0)) {
            $startTimestamp = $pollObj->getVar('start_time'); //don't change start time
        }

        $pollVars = array(
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
        $pollObj->setVars($pollVars);
        $pollId = $pollHandler->insert($pollObj);
        if (!$pollId) {
            $err = $pollObj->getHtmlErrors();
            exit($err);
        }

        // now get the options
        $optionIdArray    = XoopsRequest::getArray('option_id', array(), 'POST');
        $optionIdArray    = array_map('intval', $optionIdArray);
        $optionTextArray  = XoopsRequest::getArray('option_text', array(), 'POST');
        $optionColorArray = XoopsRequest::getArray('option_color', array(), 'POST');

        foreach ($optionIdArray as $key => $oId) {
            if (!empty($oId) && ($optionObj = $optHandler->get($oId))) {
                // existing option object so need to update it
                $optionTextArray[$key] = trim($optionTextArray[$key]);
                if ('' === $optionTextArray[$key]) {
                    // want to delete this option
                    if (false !== $optHandler->delete($optionObj)) {
                        // now remove it from the log
                        $logHandler->deleteByOptionId($optionObj->getVar('option_id'));
                    }
                } else {
                    $optionObj->setVar('option_text', $optionTextArray[$key]);
                    $optionObj->setVar('option_color', $optionColorArray[$key]);
                    $optionObj->setVar('poll_id', $pollId);
                    $optHandler->insert($optionObj);
                }
            } else {
                // new option object
                $optionObj             = $optHandler->create();
                $optionTextArray[$key] = trim($optionTextArray[$key]);
                if ('' !== $optionTextArray[$key]) { // ignore if text is empty
                    $optionObj->setVar('option_text', $optionTextArray[$key]);
                    $optionObj->setVar('option_color', $optionColorArray[$key]);
                    $optionObj->setVar('poll_id', $pollId);
                    $optHandler->insert($optionObj);
                }
                unset($optionObj);
            }
        }

        unset($optHandler, $logHandler, $pollObj, $pollHandler, $pollId);
        // clear the template cache so changes take effect immediately
        include_once $GLOBALS['xoops']->path('class/template.php');
        xoops_template_clear_module_cache($GLOBALS['xoopsModule']->getVar('mid'));
        redirect_header($_SERVER['PHP_SELF'] . '?op=list', XoopspollConstants::REDIRECT_DELAY_SHORT, _AM_XOOPSPOLL_DBUPDATED);
        break;

    case 'delete':
        $pollId      = XoopsRequest::getInt('poll_id', 0);
        $pollHandler =& xoops_getmodulehandler('poll', 'xoopspoll');
        $pollObj     = $pollHandler->get($pollId);
        if (empty($pollObj) || !($pollObj instanceof XoopspollPoll)) {
            redirect_header($_SERVER['PHP_SELF'], XoopspollConstants::REDIRECT_DELAY_SHORT, implode('<br />', $pollHandler->getErrors()));
        }
        xoops_cp_header();
        $admin_class = new ModuleAdmin();
        echo $admin_class->addNavigation('main.php');
        xoops_confirm(array('op'      => 'delete_ok',
                            'poll_id' => $pollId), $_SERVER['PHP_SELF'], sprintf(_AM_XOOPSPOLL_RUSUREDEL, $myts->htmlSpecialChars($pollObj->getVar('question'))));
        include_once __DIR__ . '/admin_footer.php';
        //    xoops_cp_footer();
        exit();
        break;

    case 'delete_ok':
        if (!$GLOBALS['xoopsSecurity']->check()) {
            redirect_header($_SERVER['PHP_SELF'], XoopspollConstants::REDIRECT_DELAY_MEDIUM, implode('<br />', $GLOBALS['xoopsSecurity']->getErrors()));
        }
        $pollHandler =& xoops_getmodulehandler('poll', 'xoopspoll');
        $pollId      = XoopsRequest::getInt('poll_id', 0, 'POST');
        if ($pollHandler->deleteAll(new Criteria('poll_id', $pollId, '='))) {
            $optHandler =& xoops_getmodulehandler('option', 'xoopspoll');
            $optHandler->deleteAll(new Criteria('poll_id', $pollId));
            $logHandler =& xoops_getmodulehandler('log', 'xoopspoll');
            $logHandler->deleteByPollId($pollId);
            unset($pollHandler, $optHandler, $logHandler);
            // clear the template cache
            include_once $GLOBALS['xoops']->path('class/template.php');
            xoops_template_clear_module_cache($GLOBALS['xoopsModule']->getVar('mid'));
            // delete comments for this poll
            xoops_comment_delete($GLOBALS['xoopsModule']->getVar('mid'), $pollId);

            //now clear association with newbb topic if one exists
            $module_handler =& xoops_gethandler('module');
            $newbbModule    =& $module_handler->getByDirname('newbb');
            if (($newbbModule instanceof XoopsModule) && $newbbModule->isactive()) {
                $topic_handler =& xoops_getmodulehandler('topic', 'newbb');
                $criteria      = new CriteriaCompo();
                $criteria->add(new Criteria('poll_id', $pollId, '='));
                /* {@internal the order of the next 2 statements is important! */
                $topic_handler->updateAll('topic_haspoll', 0, $criteria); // clear poll association
                $topic_handler->updateAll('poll_id', 0, $criteria); // clear poll_id
                xoops_template_clear_module_cache($newbbModule->getVar('mid')); // clear newbb template cache
            }
        }
        redirect_header($_SERVER['PHP_SELF'], XoopspollConstants::REDIRECT_DELAY_SHORT, _AM_XOOPSPOLL_DBUPDATED);
        break;

    case 'restart':
        xoops_load('FormDateTimePicker', 'xoopspoll');
        $pollId      = XoopsRequest::getInt('poll_id', 0);
        $pollHandler =& xoops_getmodulehandler('poll', 'xoopspoll');
        $pollObj     = $pollHandler->get($pollId);
        $pollForm    = new XoopsThemeForm(_AM_XOOPSPOLL_RESTARTPOLL, 'poll_form', $_SERVER['PHP_SELF'], 'post', true);

        // setup times for forms
        $xuCurrentTimestamp = xoops_getUserTimestamp(time());
        $xuCurrentFormatted = ucfirst(date(_MEDIUMDATESTRING, $xuCurrentTimestamp));
        $xuStartTimestamp   = $xuCurrentTimestamp;
        $xuEndTimestamp     = $xuStartTimestamp + XoopspollConstants::DEFAULT_POLL_DURATION;

        $timeTray = new XoopsFormElementTray(_AM_XOOPSPOLL_POLL_TIMES, '&nbsp;&nbsp;', 'time_tray');

        //add start time to the form
        $startTimeText = new XoopspollFormDateTimePicker("<div class='bold'>" . _AM_XOOPSPOLL_START_TIME . '<br />' . "<span class='x-small'>" . _AM_XOOPSPOLL_FORMAT . '<br />' . sprintf(_AM_XOOPSPOLL_CURRENTTIME, $xuCurrentFormatted) . '</span></div>', 'xu_start_time', 20, $xuStartTimestamp);
        $timeTray->addElement($startTimeText, true);

        // add ending date to form
        $endTimeText = new XoopspollFormDateTimePicker("<div class='bold middle'>" . _AM_XOOPSPOLL_EXPIRATION . '</div>', 'xu_end_time', 20, $xuEndTimestamp);
        $timeTray->addElement($endTimeText, true);
        $pollForm->addElement($timeTray);

        $pollForm->addElement(new XoopsFormRadioYN(_AM_XOOPSPOLL_NOTIFY, 'notify', XoopspollConstants::POLL_MAILED));
        $pollForm->addElement(new XoopsFormRadioYN(_AM_XOOPSPOLL_RESET, 'reset', 0));
        $pollForm->addElement(new XoopsFormHidden('op', 'restart_ok'));
        $pollForm->addElement(new XoopsFormHidden('poll_id', $pollId));
        $pollForm->addElement(new XoopsFormButton('', 'poll_submit', _AM_XOOPSPOLL_RESTART, 'submit'));

        xoops_cp_header();
        $admin_class = new ModuleAdmin();
        echo $admin_class->addNavigation('main.php');
        $pollForm->display();
        include_once __DIR__ . '/admin_footer.php';
        exit();
        break;

    case 'restart_ok':
        if (!$GLOBALS['xoopsSecurity']->check()) {
            redirect_header($_SERVER['PHP_SELF'], XoopspollConstants::REDIRECT_DELAY_MEDIUM, implode('<br />', $GLOBALS['xoopsSecurity']->getErrors()));
        }
        $pollId = XoopsRequest::getInt('poll_id', 0, 'POST');
        if (empty($pollId)) {
            redirect_header($_SERVER['PHP_SELF'], XoopspollConstants::REDIRECT_DELAY_SHORT, _AM_XOOPSPOLL_ERROR_INVALID_POLLID);
        }

        $pollHandler =& xoops_getmodulehandler('poll', 'xoopspoll');
        $pollObj     = $pollHandler->get($pollId);

        $xuEndTimestamp   = strtotime(XoopsRequest::getString('xu_end_time', null, 'POST'));
        $xuStartTimestamp = strtotime(XoopsRequest::getString('xu_start_time', null, 'POST'));

        $endTimestamp   = (empty($xuEndTimestamp)) ? (time() + XoopspollConstants::DEFAULT_POLL_DURATION) : userTimeToServerTime($xuEndTimestamp);
        $startTimestamp = (empty($xuStartTimestamp)) ? ($xuEndTimestamp - XoopspollConstants::DEFAULT_POLL_DURATION) : userTimeToServerTime($xuStartTimestamp);
        $pollObj->setVar('end_time', $endTimestamp);
        $pollObj->setVar('start_time', $startTimestamp);

        $notify = XoopsRequest::getInt('notify', XoopspollConstants::NOTIFICATION_DISABLED, 'POST');
        if (XoopspollConstants::NOTIFICATION_ENABLED === $notify) {
            // if notify, set mail status to "not mailed"
            $pollObj->setVar('mail_status', XoopspollConstants::POLL_NOT_MAILED);
        } else {
            // if not notify, set mail status to already "mailed"
            $pollObj->setVar('mail_status', XoopspollConstants::POLL_MAILED);
        }
        // save the poll settings
        $pollHandler->insert($pollObj);

        $reset = XoopsRequest::getInt('reset', XoopspollConstants::DO_NOT_RESET_RESULTS, 'POST');
        if (XoopspollConstants::RESET_RESULTS === $reset) {
            // reset all logs
            $logHandler =& xoops_getmodulehandler('log', 'xoopspoll');
            $logHandler->deleteByPollId($pollId);
            unset($logHandler);
            $optHandler =& xoops_getmodulehandler('option', 'xoopspoll');
            $criteria   = new Criteria('poll_id', $pollId, '=');
            $optHandler->updateAll('option_count', 0, $criteria);
        }
        if (!$pollHandler->updateCount($pollObj)) {
            echo $pollObj->getHtmlErrors();
            exit();
        }
        include_once $GLOBALS['xoops']->path('class/template.php');
        xoops_template_clear_module_cache($GLOBALS['xoopsModule']->getVar('mid'));
        redirect_header($_SERVER['PHP_SELF'], XoopspollConstants::REDIRECT_DELAY_SHORT, _AM_XOOPSPOLL_DBUPDATED);
        break;

    case 'log':
        $pollId   = XoopsRequest::getInt('poll_id', 0);
        $limit    = XoopsRequest::getInt('limit', XoopspollConstants::DEFAULT_POLL_PAGE_LIMIT);
        $start    = XoopsRequest::getInt('start', 0);
        $orderby  = XoopsRequest::getString('orderby', 'time');
        $orderdir = XoopsRequest::getString('orderdir', 'ASC');

        if (empty($pollId)) {
            redirect_header($_SERVER['PHP_SELF'], XoopspollConstants::REDIRECT_DELAY_SHORT, _AM_XOOPSPOLL_ERROR_INVALID_POLLID);
        }

        $pollHandler  =& xoops_getmodulehandler('poll', 'xoopspoll');
        $pollObj      = $pollHandler->get($pollId);
        $expiredClass = ($pollObj->getVar('end_time') < time()) ? ' red' : '';
        xoops_cp_header();
        $admin_class = new ModuleAdmin();
        echo $admin_class->addNavigation('main.php');

        $xuEndTimestamp     = userTimeToServerTime($pollObj->getVar('end_time'));
        $xuEndFormattedTime = ucfirst(date(_MEDIUMDATESTRING, $xuEndTimestamp));

        /**
         * @todo need to move this html to a template and pass variables via xoopsTpl
         * {@internal show a brief description of the question we are focusing on}
         */
        echo "<h4 class='left'>" . _AM_XOOPSPOLL_LOGSLIST . "</h4>\n" . "<table class='outer bnone width100' style='padding: 0; margin: 0;'>\n" . "  <tr>\n" . "    <td>\n" . "      <table class='width100 bnone marg2 pad3'>\n" . "        <thead>\n" . "        <tr class='bg3'>\n" . "          <th class='center' nowrap>" . _AM_XOOPSPOLL_POLLQUESTION . "</th>\n" . "          <th class='center' nowrap>" . _AM_XOOPSPOLL_POLLDESC . "</th>\n" . '          <th nowrap>' . _AM_XOOPSPOLL_VOTERS . "</th>\n" . '          <th nowrap>' . _AM_XOOPSPOLL_VOTES . "</th>\n" . '          <th nowrap>' . _AM_XOOPSPOLL_EXPIRATION . "</th>\n" . "        </tr>\n" . "        </thead>\n" . "        <tfoot></tfoot>\n" . "        <tbody>\n" . "        <tr class='bg1'>\n" . "          <td class='center'>" . $pollObj->getVar('question') . "</td>\n" . "          <td class='center'>" . $pollObj->getVar('description') . "</td>\n" . "          <td class='center'>" . $pollObj->getVar('voters') . "</td>\n" . "          <td class='center'>" . $pollObj->getVar('votes') . "</td>\n" . "          <td class='center{$expiredClass}'>{$xuEndFormattedTime}</td>\n" . "        </tr>\n" . "        </tbody>\n" . "      </table>\n" . "    </td>\n" . "  </tr>\n" . "</table>\n";
        echo "<br />\n";

        if ($pollObj->getVar('votes')) {  // there are votes to show
            // show summary of results
            $optHandler =& xoops_getmodulehandler('option', 'xoopspoll');
            $criteria   = new CriteriaCompo();
            $criteria->add(new Criteria('poll_id', $pollId, '='));
            $criteria->setGroupby('option_id');
            $options = $optHandler->getAll($criteria, null, false);

            echo "<div class='center' style='margin-bottom: 2em;'>\n" . "<h4 class='left'>" . _AM_XOOPSPOLL_LOGSLIST . "</h4>\n" . "<table class='outer bnone width100' style='padding: 0; margin: 0;'>\n" . "<thead>\n" . "  <tr>\n" . "    <th class='width15'>" . _AM_XOOPSPOLL_OPTION . "</th>\n" . '    <th>' . _AM_XOOPSPOLL_LABEL . "</th>\n" . "    <th class='width15'>" . _AM_XOOPSPOLL_COUNT . "</th>\n" . "  </tr>\n" . "</thead>\n" . "<tfoot></tfoot>\n" . '<tbody>';

            $rowClass = 'even';
            $i        = 0;
            foreach ($options as $thisOption) {
                echo "  <tr class='{$rowClass}'><td class='center'>" . ++$i . "</td><td class='center'>{$thisOption['option_text']}</td><td class='center'>{$thisOption['option_count']}</td></tr>\n";
                $rowClass = ('odd' === $rowClass) ? 'even' : 'odd';
            }
            echo "</tbody>\n" . "</table>\n" . '</div>';

            // show logs
            echo "<h4 class='left'>" . _AM_XOOPSPOLL_POLLVOTERS . "</h4>\n";

            $logHandler =& xoops_getmodulehandler('log', 'xoopspoll');
            $criteria   = new CriteriaCompo();
            $criteria->add(new Criteria('poll_id', $pollId, '='));
            $logsCount = $logHandler->getCount($criteria);
            $criteria->setSort($orderby);
            $criteria->setOrder($orderdir);
            $criteria->setStart($start);
            $criteria->setLimit($limit);
            $logsArray = $logHandler->getAll($criteria);

            $arrowUp   = $pathIcon16 . '/up.gif';
            $arrowDown = $pathIcon16 . '/down.gif';
            $sorthref  = $_SERVER['PHP_SELF'] . "?op=log&amp;poll_id={$pollId}&amp;orderby=";
            $class     = 'even';

            if (is_array($logsArray) && $logsCount > 0) {
                echo "<table class='outer bnone width100' style='padding: 0; margin: 0;'>\n" . "  <tr>\n" . "    <td class='bg2'>\n" . "      <table class='width100 bnone pad3 marg2'>\n" . "        <thead>\n" . "        <tr class='bg3'>\n";

                $ipLabel    = (XoopspollConstants::LOOK_UP_HOST === $GLOBALS['xoopsModuleConfig']['look_up_host']) ? _AM_XOOPSPOLL_HOST_NAME : _AM_XOOPSPOLL_IP;
                $fieldArray = array(
                    array('order' => 'log_id', 'label' => _AM_XOOPSPOLL_LOGID),
                    array('order' => 'option_id', 'label' => _AM_XOOPSPOLL_OPTIONID),
                    array('order' => 'ip', 'label' => $ipLabel),
                    array('order' => 'user_id', 'label' => _AM_XOOPSPOLL_VOTER),
                    array('order' => 'time', 'label' => _AM_XOOPSPOLL_VOTETIME));

                foreach ($fieldArray as $field) {
                    echo "          <th nowrap>\n" . "            <a href='{$sorthref}{$field['order']}&amp;orderdir=ASC'><img src='{$arrowUp}' alt='' /></a>\n" . "            <a href='{$sorthref}{$field['order']}&amp;orderdir=DESC'><img src='{$arrowDown}' alt='' /></a>\n" . "            &nbsp;{$field['label']}\n" . "          </th>\n";
                }
                echo '        </tr>' . "        </thead>\n" . "        <tbody>\n";

                $optHandler =& xoops_getmodulehandler('option', 'xoopspoll');
                $luhConfig  = (XoopspollConstants::LOOK_UP_HOST === $GLOBALS['xoopsModuleConfig']['look_up_host']) ? true : false;
                foreach ($logsArray as $thisLog) {
                    $logVals  = $thisLog->getValues();
                    $option   = $optHandler->get($logVals['option_id']);
                    $remoteIp = ($luhConfig) ? XoopspollPollUtility::getHostByAddrWithCache($logVals['ip']) : $logVals['ip'];
                    echo "        <tr class='bg1'>\n" . "          <td class='{$class} center'>{$logVals['log_id']}</td>\n" . "          <td class='{$class}'>" . $option->getVar('option_text') . "</td>\n" . "          <td class='{$class} center'>{$remoteIp}</td>\n";

                    if (0 !== $logVals['user_id']) {
                        $user  = new XoopsUser($logVals['user_id']);
                        $uname = $user->getVar('uname');

                        $from_userid = $GLOBALS['xoopsUser']->getVar('uid');
                        $to_userid   = $user->getVar('uid');
                        $pmLink      = $GLOBALS['xoops']->buildUrl($GLOBALS['xoops']->path('pmlite.php', true), array('send'        => 1,
                                                                                                                      'from_userid' => $from_userid,
                                                                                                                      'to_userid'   => $to_userid));

                        echo "          <td class='{$class} center'>\n" . '            <a href=' . $GLOBALS['xoops']->url('/userinfo.php') . '?uid=' . $user->getVar('uid') . ">{$uname}</a>&nbsp;\n" . "            <a href='{$pmLink}' target='_blank'><img src='" . $pathIcon16 . "/mail_generic.png' alt='" . _AM_XOOPSPOLL_PM_VOTER . "' title='" . _AM_XOOPSPOLL_PM_VOTER . "' />\n" . "          </td>\n";
                    } else {
                        echo "          <td class='{$class} center'>{$GLOBALS['xoopsConfig']['anonymous']}</td>\n";
                    }
                    $xuLogTimestamp     = userTimeToServerTime($logVals['time']);
                    $xuLogFormattedTime = ucfirst(date(_DATESTRING, $xuLogTimestamp));

                    echo "          <td class='{$class} center'>{$xuLogFormattedTime}</td>\n" . "        </tr>\n";
                    $class = ('odd' === $class) ? 'even' : 'odd';
                }
                echo "        </tbody>\n" . "      </table>\n" . "    </td>\n" . "  </tr>\n" . "</table>\n";

                xoops_load('pagenav');
                $pageNav = new XoopsPageNav($logsCount, $limit, $start, 'start', "op=log&amp;poll_id={$pollId}");
                echo "<div class='right' style='margin: 2em auto;'>" . $pageNav->renderNav() . '</div>';
            }
        }

        //    echo "<div class='center' style='margin-bottom: 1em;'>[ <a href='" . $_SERVER['PHP_SELF'] . "?op=list'>" . _AM_XOOPSPOLL_RETURNLIST . "</a> ]</div>\n";
        //    echo "<div class='center' style='margin-bottom: 1em;'>[ <a href='" . $_SERVER['PHP_SELF'] . "?op=list'><img src='". $pathIcon16 ."/back.png' alt='" . _AM_XOOPSPOLL_RETURNLIST . "' title='" . _AM_XOOPSPOLL_RETURNLIST . "'>" . _AM_XOOPSPOLL_RETURNLIST . "</a> ]</div>\n";
        $admin_class->addItemButton(_AM_XOOPSPOLL_RETURNLIST, 'main.php' . '?op=list', $icon = '../16/back');
        echo $admin_class->renderButton('center');
        include_once __DIR__ . '/admin_footer.php';
        break;

    case 'quickupdate':

        $pollId = XoopsRequest::getArray('poll_id', array(), 'POST');
        $pollId = (array)$pollId;
        $pollId = array_map('intval', $pollId);

        $count = count($pollId);

        if ($count) {
            $pollHandler =& xoops_getmodulehandler('poll', 'xoopspoll');
            $criteria    = new CriteriaCompo();
            $idString    = '(' . implode(',', $pollId) . ')';
            $criteria->add(new Criteria('poll_id', $idString, 'IN'));
            $pollObjs = $pollHandler->getAll($criteria);

            // get display variables from form POST
            $display = XoopsRequest::getArray('display', array(), 'POST');
            $display = array_map('intval', (array)$display);
            $weight  = XoopsRequest::getArray('weight', array(), 'POST');
            $weight  = array_map('intval', (array)$weight);

            foreach ($pollObjs as $pollObj) {
                $thisId           = $pollObj->getVar('poll_id');
                $display[$thisId] = empty($display[$thisId]) ? XoopspollConstants::DO_NOT_DISPLAY_POLL_IN_BLOCK : XoopspollConstants::DISPLAY_POLL_IN_BLOCK;
                $weight[$thisId]  = empty($weight[$thisId]) ? XoopspollConstants::DEFAULT_WEIGHT : $weight[$thisId];
                if ($display[$thisId] !== $pollObj->getVar('display') || $weight[$thisId] !== $pollObj->getVar('weight')) {
                    $pollObj->setVars(array('display' => $display[$thisId], 'weight' => $weight[$thisId]));
                    $pollHandler->insert($pollObj);
                }
                unset($pollObj);
            }
            unset($pollObjs);
            include_once $GLOBALS['xoops']->path('class/template.php');
            xoops_template_clear_module_cache($GLOBALS['xoopsModule']->getVar('mid'));
            redirect_header($_SERVER['PHP_SELF'], XoopspollConstants::REDIRECT_DELAY_SHORT, _AM_XOOPSPOLL_DBUPDATED);
        } else {
            redirect_header($_SERVER['PHP_SELF'], XoopspollConstants::REDIRECT_DELAY_SHORT, _AM_XOOPSPOLL_NOTHING_HERE);
        }
        break;
    // added cloning capability in v 1.40
    case 'clone':
        $pollHandler =& xoops_getmodulehandler('poll', 'xoopspoll');
        $optHandler  =& xoops_getmodulehandler('option', 'xoopspoll');
        $pollId      = XoopsRequest::getInt('poll_id', 0);
        $pollObj     = $pollHandler->get($pollId);
        $origValues  = $pollObj->getValues();
        unset($origValues['poll_id']);
        $pollDuration = $origValues['end_time'] - $origValues['start_time'];
        $pollDuration = ($pollDuration > 0) ? $pollDuration : XoopspollConstants::DEFAULT_POLL_DURATION;
        $newValues    = array(
            'votes'       => 0,
            'voters'      => 0,
            'mail_status' => XoopspollConstants::POLL_NOT_MAILED,
            'question'    => $origValues['question'] . '(' . _AM_XOOPSPOLL_CLONE . ')',
            'start_time'  => time(),  //set the start time to now
            'end_time'    => time() + $pollDuration);
        $cloneValues  = array_merge($origValues, $newValues);
        $cloneObj     = $pollHandler->create();
        $cloneObj->setVars($cloneValues);
        $cloneId = $pollHandler->insert($cloneObj);

        // now set cloned options
        $optionObjs =& $optHandler->getAllByPollId($pollId);
        foreach ($optionObjs as $optionObj) {
            $cloneOptObj                 = $optHandler->create();
            $cloneValues                 = $optionObj->getValues();
            $cloneValues['option_id']    = 0;
            $cloneValues['poll_id']      = $cloneId;
            $cloneValues['option_count'] = 0;
            $cloneOptObj->setVars($cloneValues);
            $optId = $optHandler->insert($cloneOptObj);
            unset($cloneValues, $cloneOptObj);
        }
        unset($pollObj, $cloneObj, $origValues, $cloneValues, $newValues);
        redirect_header($_SERVER['PHP_SELF'] . "?poll_id={$cloneId}&amp;op=edit", XoopspollConstants::REDIRECT_DELAY_MEDIUM, _AM_XOOPSPOLL_CLONE_SUCCESS);
        break;
}
