<?php
/*
               XOOPS - PHP Content Management System
                   Copyright (c) 2000-2016 XOOPS.org
                      <https://xoops.org>
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
 * @copyright ::  {@link https://xoops.org/ XOOPS Project}
 * @license   ::    {@link http://www.fsf.org/copyleft/gpl.html GNU public license}
 * @package   ::    xoopspoll
 * @subpackage::    admin
 * @author    ::    Xoops Module Team
 * @since     ::    1.0
 *
 * @uses      xoops_load() to instantiate needed classes
 * @uses      XoopsFormloader
 * @uses      Xoopslists
 * @uses      CriteriaCompo
 * @uses      Criteria
 * @uses      xoops_getModuleHandler() to load this modules class handlers
 * @uses      ModuleAdmin class to display module administration page navigation
 * @uses      $GLOBALS['xoopsSecurity']::getTokenHTML() used for security on input of form data
 * @uses      $GLOBALS['xoops'] class::methods used to get general information about XOOPS
 * @uses      XoopsPageNav class to display page navigation links for multiple pages of data
 * @uses      xoops_template_clear_module_cache() function used to clear cache after data has been updated
 * @uses      redirect_header() function to send user to page after completing task(s)
 */

use Xmf\Request;
use XoopsModules\Xoopspoll;
use XoopsModules\Xoopspoll\Constants;
use XoopsModules\Newbb;

require_once __DIR__ . '/admin_header.php';
require_once $GLOBALS['xoops']->path('class/xoopsblock.php');

xoops_load('xoopsformloader');
xoops_load('xoopslists');
xoops_load('renderer', 'xoopspoll');
xoops_load('pollUtility', 'xoopspoll');

$op = Request::getCmd('op', Request::getCmd('op', 'list', 'POST'), 'GET');
switch ($op) {

    case 'list':
    default:
        $limit = Request::getInt('limit', Constants::DEFAULT_POLL_PAGE_LIMIT);
        $start = Request::getInt('start', 0);

        /** @var \XoopsPersistableObjectHandler $pollHandler */
        $pollHandler = Xoopspoll\Helper::getInstance()->getHandler('Poll');
        $criteria    = new \CriteriaCompo();
        $criteria->setLimit($limit + 1);
        $criteria->setStart($start);
        $criteria->setSort('weight ASC, start_time');  // trick criteria to allow 2 sort criteria
        $criteria->setOrder('ASC');
        $pollObjs   = $pollHandler->getAll($criteria);
        $pollsCount = count($pollObjs);

        //    $GLOBALS['xoopsOption']['template_main'] = 'xoopspoll_list.html';
        xoops_cp_header();
        $adminObject = \Xmf\Module\Admin::getInstance();

        $xoopsTpl->assign('navigation', $adminObject->displayNavigation(basename(__FILE__)));
        $adminObject->addItemButton(_AM_XOOPSPOLL_CREATENEWPOLL, 'main.php' . '?op=add', $icon = 'add');
        $xoopsTpl->assign('addPollButton', $adminObject->displayButton('left'));

        $renderedNav = '';

        if (is_array($pollObjs) && $pollsCount > 0) {
            /* if newbb forum module is loaded find poll/topic association */
            /** @var XoopsModuleHandler $moduleHandler */
            $moduleHandler = xoops_getHandler('module');
            $newbbModule   = $moduleHandler->getByDirname('newbb');
            if (($newbbModule instanceof XoopsModule) && $newbbModule->isactive()) {
                /** @var NewbbTopicHandler $topicHandler */
                $topicHandler = Newbb\Helper::getInstance()->getHandler('Topic');
                $topicFields  = ['topic_id', 'topic_title', 'poll_id'];
                $criteria     = new \CriteriaCompo();
                $criteria->add(new \Criteria('topic_haspoll', 0, '>'));
                $pollsWithTopics = [];
                $topicsWithPolls =& $topicHandler->getAll($criteria, $topicFields, false);
                foreach ($topicsWithPolls as $pollTopics) {
                    $pollsWithTopics[$pollTopics['poll_id']] = [
                        'topic_id'    => $pollTopics['topic_id'],
                        'topic_title' => $pollTopics['topic_title']
                    ];
                }
                if (!empty($pollsWithTopics)) {
                    $adminObject->addInfoBox(_AM_XOOPSPOLL_NEWBB_SUPPORT);
                    $adminObject->addInfoBoxLine(sprintf("<img src='" . $pathIcon16 . "/forum.png' alt='" . _AM_XOOPSPOLL_NEWBB_SUPPORT . "'> " . _AM_XOOPSPOLL_NEWBB_INTRO, null, null, 'information'), '');
                    $newbbIntro = $adminObject->renderInfoBox();
                } else {
                    $newbbIntro = '';
                }
            } else {
                $pollsWithTopics = [];
                $newbbIntro      = '';
            }
            $xoopsTpl->assign('newbbIntro', $newbbIntro);
            //            $xoopsTpl->assign('securityToken', $GLOBALS['xoopsSecurity']->getTokenHTML()); //mb

            $pollItems = [];
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

                $checked = (Constants::DISPLAY_POLL_IN_BLOCK === $pollVars['display']) ? ' checked' : '';

                $xuCurrentTimestamp   = xoops_getUserTimestamp(time());
                $xuCurrentFormatted   = ucfirst(date(_MEDIUMDATESTRING, $xuCurrentTimestamp));
                $xuStartTimestamp     = xoops_getUserTimestamp($pollVars['start_time']);
                $xuStartFormattedTime = ucfirst(date(_MEDIUMDATESTRING, $xuStartTimestamp));
                $xuEndTimestamp       = xoops_getUserTimestamp($pollVars['end_time']);

                if ($xuEndTimestamp > $xuCurrentTimestamp) {
                    $end = ucfirst(date(_MEDIUMDATESTRING, $xuEndTimestamp)); // formatted output for current user
                } else {
                    $end = "<span class='red'>" . _AM_XOOPSPOLL_EXPIRED . '</span><br>' . "<a href='" . $_SERVER['PHP_SELF'] . "?op=restart&amp;poll_id={$id}'>" . _AM_XOOPSPOLL_RESTART . '</a>';
                }

                $pollItems[$id] = [
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
                    'buttons'              => [
                        'edit'   => [
                            'href' => $_SERVER['PHP_SELF'] . "?op=edit&amp;poll_id={$id}",
                            'file' => $pathIcon16 . '/edit.png',
                            'alt'  => _AM_XOOPSPOLL_EDITPOLL
                        ],
                        'clone'  => [
                            'href' => $_SERVER['PHP_SELF'] . "?op=clone&amp;poll_id={$id}",
                            'file' => $pathIcon16 . '/editcopy.png',
                            'alt'  => _AM_XOOPSPOLL_CLONE
                        ],
                        'delete' => [
                            'href' => $_SERVER['PHP_SELF'] . "?op=delete&amp;poll_id={$id}",
                            'file' => $pathIcon16 . '/delete.png',
                            'alt'  => _DELETE
                        ],
                        'log'    => [
                            'href' => $_SERVER['PHP_SELF'] . "?op=log&amp;poll_id={$id}",
                            'file' => $pathIcon16 . '/search.png',
                            'alt'  => _AM_XOOPSPOLL_VIEWLOG
                        ]
                    ]
                ];
                if ($topic_id > 0) {
                    $pollItems[$id]['buttons']['forum'] = [
                        'href' => $GLOBALS['xoops']->url('modules/newbb/viewtopic.php') . "?topic_id={$topic_id}",
                        'file' => $pathIcon16 . '/forum.png',
                        'alt'  => _AM_XOOPSPOLL_NEWBB_TOPIC . '&nbsp;' . htmlspecialchars($topic_title, ENT_QUOTES | ENT_HTML5)
                    ];
                }
            }
            xoops_load('pagenav');
            $pageNav     = new \XoopsPageNav($pollsCount, $limit, $start);
            $renderedNav = $pageNav->renderNav();
        }

        $xoopsTpl->assign('pollItems', $pollItems);
        $xoopsTpl->assign('rendered_nav', $renderedNav);
        $xoopsTpl->assign('self', $_SERVER['PHP_SELF']);
        $xoopsTpl->display($GLOBALS['xoops']->path('modules/xoopspoll/templates/admin/xoopspoll_list.tpl'));
        require_once __DIR__ . '/admin_footer.php';
        exit();
        break;

    case 'edit':
    case 'add':
        $optHandler  = Xoopspoll\Helper::getInstance()->getHandler('Option');
        $pollHandler = Xoopspoll\Helper::getInstance()->getHandler('Poll');
        $pollId      = Request::getInt('poll_id', 0);
        $pollObj     = $pollHandler->get($pollId); // will auto create object if poll_id=0

        // display the form
        xoops_cp_header();
        $adminObject = \Xmf\Module\Admin::getInstance();
        $adminObject->displayNavigation(basename(__FILE__));
        $pollObj->renderForm($_SERVER['PHP_SELF'], 'post');
        require_once __DIR__ . '/admin_footer.php';
        exit();
        break;

    case 'update':
        if (!$GLOBALS['xoopsSecurity']->check()) {
            redirect_header($_SERVER['PHP_SELF'], Constants::REDIRECT_DELAY_MEDIUM, implode('<br>', $GLOBALS['xoopsSecurity']->getErrors()));
        }

        $optHandler  = Xoopspoll\Helper::getInstance()->getHandler('Option');
        $logHandler  = Xoopspoll\Helper::getInstance()->getHandler('Log');
        $pollHandler = Xoopspoll\Helper::getInstance()->getHandler('Poll');

        $pollId  = Request::getInt('poll_id', 0, 'POST');
        $pollObj = $pollHandler->get($pollId);

        $notify = Request::getInt('notify', Constants::NOTIFICATION_ENABLED, 'POST');

        $currentTimestamp = time();
        $xuEndTimestamp   = strtotime(Request::getString('xu_end_time', null, 'POST'));
        $endTimestamp     = empty($xuEndTimestamp) ? ($currentTimestamp + Constants::DEFAULT_POLL_DURATION) : userTimeToServerTime($xuEndTimestamp);
        $xuStartTimestamp = strtotime(Request::getString('xu_start_time', null, 'POST'));
        $startTimestamp   = empty($xuStartTimestamp) ? ($endTimestamp - Constants::DEFAULT_POLL_DURATION) : userTimeToServerTime($xuStartTimestamp);

        //  don't allow changing start time if there are votes in the log
        if (($startTimestamp < $pollObj->getVar('start_time')) && ($logHandler->getTotalVotesByPollId($pollId) > 0)) {
            $startTimestamp = $pollObj->getVar('start_time'); //don't change start time
        }

        $pollVars = [
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
        $pollObj->setVars($pollVars);
        $pollId = $pollHandler->insert($pollObj);
        if (!$pollId) {
            $err = $pollObj->getHtmlErrors();
            exit($err);
        }

        // now get the options
        $optionIdArray    = Request::getArray('option_id', [], 'POST');
        $optionIdArray    = array_map('intval', $optionIdArray);
        $optionTextArray  = Request::getArray('option_text', [], 'POST');
        $optionColorArray = Request::getArray('option_color', [], 'POST');

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
        require_once $GLOBALS['xoops']->path('class/template.php');
        xoops_template_clear_module_cache($GLOBALS['xoopsModule']->getVar('mid'));
        redirect_header($_SERVER['PHP_SELF'] . '?op=list', Constants::REDIRECT_DELAY_SHORT, _AM_XOOPSPOLL_DBUPDATED);
        break;

    case 'delete':
        $pollId      = Request::getInt('poll_id', 0);
        $pollHandler = Xoopspoll\Helper::getInstance()->getHandler('Poll');
        $pollObj     = $pollHandler->get($pollId);
        if (empty($pollObj) || !($pollObj instanceof Poll)) {
            redirect_header($_SERVER['PHP_SELF'], Constants::REDIRECT_DELAY_SHORT, implode('<br>', $pollHandler->getErrors()));
        }
        xoops_cp_header();
        $adminObject = \Xmf\Module\Admin::getInstance();
        $adminObject->displayNavigation(basename(__FILE__));
        xoops_confirm([
                          'op'      => 'delete_ok',
                          'poll_id' => $pollId
                      ], $_SERVER['PHP_SELF'], sprintf(_AM_XOOPSPOLL_RUSUREDEL, $myts->htmlSpecialChars($pollObj->getVar('question'))));
        require_once __DIR__ . '/admin_footer.php';
        //    xoops_cp_footer();
        exit();
        break;

    case 'delete_ok':
        if (!$GLOBALS['xoopsSecurity']->check()) {
            redirect_header($_SERVER['PHP_SELF'], Constants::REDIRECT_DELAY_MEDIUM, implode('<br>', $GLOBALS['xoopsSecurity']->getErrors()));
        }
        $pollHandler = Xoopspoll\Helper::getInstance()->getHandler('Poll');
        $pollId      = Request::getInt('poll_id', 0, 'POST');
        if ($pollHandler->deleteAll(new \Criteria('poll_id', $pollId, '='))) {
            $optHandler = Xoopspoll\Helper::getInstance()->getHandler('Option');
            $optHandler->deleteAll(new \Criteria('poll_id', $pollId));
            $logHandler = Xoopspoll\Helper::getInstance()->getHandler('Log');
            $logHandler->deleteByPollId($pollId);
            unset($pollHandler, $optHandler, $logHandler);
            // clear the template cache
            require_once $GLOBALS['xoops']->path('class/template.php');
            xoops_template_clear_module_cache($GLOBALS['xoopsModule']->getVar('mid'));
            // delete comments for this poll
            xoops_comment_delete($GLOBALS['xoopsModule']->getVar('mid'), $pollId);

            //now clear association with newbb topic if one exists
            /** @var XoopsModuleHandler $moduleHandler */
            $moduleHandler = xoops_getHandler('module');
            $newbbModule   = $moduleHandler->getByDirname('newbb');
            if (($newbbModule instanceof XoopsModule) && $newbbModule->isactive()) {
                /** @var NewbbTopicHandler $topicHandler */
                $topicHandler = Newbb\Helper::getInstance()->getHandler('Topic');
                $criteria     = new \CriteriaCompo();
                $criteria->add(new \Criteria('poll_id', $pollId, '='));
                /* {@internal the order of the next 2 statements is important! */
                $topicHandler->updateAll('topic_haspoll', 0, $criteria); // clear poll association
                $topicHandler->updateAll('poll_id', 0, $criteria); // clear poll_id
                xoops_template_clear_module_cache($newbbModule->getVar('mid')); // clear newbb template cache
            }
        }
        redirect_header($_SERVER['PHP_SELF'], Constants::REDIRECT_DELAY_SHORT, _AM_XOOPSPOLL_DBUPDATED);
        break;

    case 'restart':
//        xoops_load('FormDateTimePicker', 'xoopspoll');
        $pollId      = Request::getInt('poll_id', 0);
        $pollHandler = Xoopspoll\Helper::getInstance()->getHandler('Poll');
        $pollObj     = $pollHandler->get($pollId);
        $pollForm    = new \XoopsThemeForm(_AM_XOOPSPOLL_RESTARTPOLL, 'poll_form', $_SERVER['PHP_SELF'], 'post', true);

        // setup times for forms
        $xuCurrentTimestamp = xoops_getUserTimestamp(time());
        $xuCurrentFormatted = ucfirst(date(_MEDIUMDATESTRING, $xuCurrentTimestamp));
        $xuStartTimestamp   = $xuCurrentTimestamp;
        $xuEndTimestamp     = $xuStartTimestamp + Constants::DEFAULT_POLL_DURATION;

        $timeTray = new \XoopsFormElementTray(_AM_XOOPSPOLL_POLL_TIMES, '&nbsp;&nbsp;', 'time_tray');

        //add start time to the form
        $startTimeText = new \Xoopspoll\FormDateTimePicker("<div class='bold'>" . _AM_XOOPSPOLL_START_TIME . '<br>' . "<span class='x-small'>" . _AM_XOOPSPOLL_FORMAT . '<br>' . sprintf(_AM_XOOPSPOLL_CURRENTTIME, $xuCurrentFormatted) . '</span></div>', 'xu_start_time', 20, $xuStartTimestamp);
        $timeTray->addElement($startTimeText, true);

        // add ending date to form
        $endTimeText = new \Xoopspoll\FormDateTimePicker("<div class='bold middle'>" . _AM_XOOPSPOLL_EXPIRATION . '</div>', 'xu_end_time', 20, $xuEndTimestamp);
        $timeTray->addElement($endTimeText, true);
        $pollForm->addElement($timeTray);

        $pollForm->addElement(new \XoopsFormRadioYN(_AM_XOOPSPOLL_NOTIFY, 'notify', Constants::POLL_MAILED));
        $pollForm->addElement(new \XoopsFormRadioYN(_AM_XOOPSPOLL_RESET, 'reset', 0));
        $pollForm->addElement(new \XoopsFormHidden('op', 'restart_ok'));
        $pollForm->addElement(new \XoopsFormHidden('poll_id', $pollId));
        $pollForm->addElement(new \XoopsFormButton('', 'poll_submit', _AM_XOOPSPOLL_RESTART, 'submit'));

        xoops_cp_header();
        $adminObject = \Xmf\Module\Admin::getInstance();
        $adminObject->displayNavigation(basename(__FILE__));
        $pollForm->display();
        require_once __DIR__ . '/admin_footer.php';
        exit();
        break;

    case 'restart_ok':
        if (!$GLOBALS['xoopsSecurity']->check()) {
            redirect_header($_SERVER['PHP_SELF'], Constants::REDIRECT_DELAY_MEDIUM, implode('<br>', $GLOBALS['xoopsSecurity']->getErrors()));
        }
        $pollId = Request::getInt('poll_id', 0, 'POST');
        if (empty($pollId)) {
            redirect_header($_SERVER['PHP_SELF'], Constants::REDIRECT_DELAY_SHORT, _AM_XOOPSPOLL_ERROR_INVALID_POLLID);
        }

        $pollHandler = Xoopspoll\Helper::getInstance()->getHandler('Poll');
        $pollObj     = $pollHandler->get($pollId);

        $xuEndTimestamp   = strtotime(Request::getString('xu_end_time', null, 'POST'));
        $xuStartTimestamp = strtotime(Request::getString('xu_start_time', null, 'POST'));

        $endTimestamp   = empty($xuEndTimestamp) ? (time() + Constants::DEFAULT_POLL_DURATION) : userTimeToServerTime($xuEndTimestamp);
        $startTimestamp = empty($xuStartTimestamp) ? ($xuEndTimestamp - Constants::DEFAULT_POLL_DURATION) : userTimeToServerTime($xuStartTimestamp);
        $pollObj->setVar('end_time', $endTimestamp);
        $pollObj->setVar('start_time', $startTimestamp);

        $notify = Request::getInt('notify', Constants::NOTIFICATION_DISABLED, 'POST');
        if (Constants::NOTIFICATION_ENABLED === $notify) {
            // if notify, set mail status to "not mailed"
            $pollObj->setVar('mail_status', Constants::POLL_NOT_MAILED);
        } else {
            // if not notify, set mail status to already "mailed"
            $pollObj->setVar('mail_status', Constants::POLL_MAILED);
        }
        // save the poll settings
        $pollHandler->insert($pollObj);

        $reset = Request::getInt('reset', Constants::DO_NOT_RESET_RESULTS, 'POST');
        if (Constants::RESET_RESULTS === $reset) {
            // reset all logs
            $logHandler = Xoopspoll\Helper::getInstance()->getHandler('Log');
            $logHandler->deleteByPollId($pollId);
            unset($logHandler);
            $optHandler = Xoopspoll\Helper::getInstance()->getHandler('Option');
            $criteria   = new \Criteria('poll_id', $pollId, '=');
            $optHandler->updateAll('option_count', 0, $criteria);
        }
        if (!$pollHandler->updateCount($pollObj)) {
            echo $pollObj->getHtmlErrors();
            exit();
        }
        require_once $GLOBALS['xoops']->path('class/template.php');
        xoops_template_clear_module_cache($GLOBALS['xoopsModule']->getVar('mid'));
        redirect_header($_SERVER['PHP_SELF'], Constants::REDIRECT_DELAY_SHORT, _AM_XOOPSPOLL_DBUPDATED);
        break;

    case 'log':
        $pollId   = Request::getInt('poll_id', 0);
        $limit    = Request::getInt('limit', Constants::DEFAULT_POLL_PAGE_LIMIT);
        $start    = Request::getInt('start', 0);
        $orderby  = Request::getString('orderby', 'time');
        $orderdir = Request::getString('orderdir', 'ASC');

        if (empty($pollId)) {
            redirect_header($_SERVER['PHP_SELF'], Constants::REDIRECT_DELAY_SHORT, _AM_XOOPSPOLL_ERROR_INVALID_POLLID);
        }

        $pollHandler  = Xoopspoll\Helper::getInstance()->getHandler('Poll');
        $pollObj      = $pollHandler->get($pollId);
        $expiredClass = ($pollObj->getVar('end_time') < time()) ? ' red' : '';
        xoops_cp_header();
        $adminObject = \Xmf\Module\Admin::getInstance();
        $adminObject->displayNavigation(basename(__FILE__));

        $xuEndTimestamp     = userTimeToServerTime($pollObj->getVar('end_time'));
        $xuEndFormattedTime = ucfirst(date(_MEDIUMDATESTRING, $xuEndTimestamp));

        /**
         * @todo need to move this html to a template and pass variables via xoopsTpl
         * {@internal show a brief description of the question we are focusing on}
         */
        echo "<h4 class='left'>"
             . _AM_XOOPSPOLL_LOGSLIST
             . "</h4>\n"
             . "<table class='outer bnone width100' style='padding: 0; margin: 0;'>\n"
             . "  <tr>\n"
             . "    <td>\n"
             . "      <table class='width100 bnone marg2 pad3'>\n"
             . "        <thead>\n"
             . "        <tr class='bg3'>\n"
             . "          <th class='center' nowrap>"
             . _AM_XOOPSPOLL_POLLQUESTION
             . "</th>\n"
             . "          <th class='center' nowrap>"
             . _AM_XOOPSPOLL_POLLDESC
             . "</th>\n"
             . '          <th nowrap>'
             . _AM_XOOPSPOLL_VOTERS
             . "</th>\n"
             . '          <th nowrap>'
             . _AM_XOOPSPOLL_VOTES
             . "</th>\n"
             . '          <th nowrap>'
             . _AM_XOOPSPOLL_EXPIRATION
             . "</th>\n"
             . "        </tr>\n"
             . "        </thead>\n"
             . "        <tfoot></tfoot>\n"
             . "        <tbody>\n"
             . "        <tr class='bg1'>\n"
             . "          <td class='center'>"
             . $pollObj->getVar('question')
             . "</td>\n"
             . "          <td class='center'>"
             . $pollObj->getVar('description')
             . "</td>\n"
             . "          <td class='center'>"
             . $pollObj->getVar('voters')
             . "</td>\n"
             . "          <td class='center'>"
             . $pollObj->getVar('votes')
             . "</td>\n"
             . "          <td class='center{$expiredClass}'>{$xuEndFormattedTime}</td>\n"
             . "        </tr>\n"
             . "        </tbody>\n"
             . "      </table>\n"
             . "    </td>\n"
             . "  </tr>\n"
             . "</table>\n";
        echo "<br>\n";

        if ($pollObj->getVar('votes')) {  // there are votes to show
            // show summary of results
            $optHandler = Xoopspoll\Helper::getInstance()->getHandler('Option');
            $criteria   = new \CriteriaCompo();
            $criteria->add(new \Criteria('poll_id', $pollId, '='));
            $criteria->setGroupBy('option_id');
            $options = $optHandler->getAll($criteria, null, false);

            echo "<div class='center' style='margin-bottom: 2em;'>\n"
                 . "<h4 class='left'>"
                 . _AM_XOOPSPOLL_LOGSLIST
                 . "</h4>\n"
                 . "<table class='outer bnone width100' style='padding: 0; margin: 0;'>\n"
                 . "<thead>\n"
                 . "  <tr>\n"
                 . "    <th class='width15'>"
                 . _AM_XOOPSPOLL_OPTION
                 . "</th>\n"
                 . '    <th>'
                 . _AM_XOOPSPOLL_LABEL
                 . "</th>\n"
                 . "    <th class='width15'>"
                 . _AM_XOOPSPOLL_COUNT
                 . "</th>\n"
                 . "  </tr>\n"
                 . "</thead>\n"
                 . "<tfoot></tfoot>\n"
                 . '<tbody>';

            $rowClass = 'even';
            $i        = 0;
            foreach ($options as $thisOption) {
                echo "  <tr class='{$rowClass}'><td class='center'>" . ++$i . "</td><td class='center'>{$thisOption['option_text']}</td><td class='center'>{$thisOption['option_count']}</td></tr>\n";
                $rowClass = ('odd' === $rowClass) ? 'even' : 'odd';
            }
            echo "</tbody>\n" . "</table>\n" . '</div>';

            // show logs
            echo "<h4 class='left'>" . _AM_XOOPSPOLL_POLLVOTERS . "</h4>\n";

            $logHandler = Xoopspoll\Helper::getInstance()->getHandler('Log');
            $criteria   = new \CriteriaCompo();
            $criteria->add(new \Criteria('poll_id', $pollId, '='));
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

                $ipLabel    = (Constants::LOOK_UP_HOST === $GLOBALS['xoopsModuleConfig']['look_up_host']) ? _AM_XOOPSPOLL_HOST_NAME : _AM_XOOPSPOLL_IP;
                $fieldArray = [
                    ['order' => 'log_id', 'label' => _AM_XOOPSPOLL_LOGID],
                    ['order' => 'option_id', 'label' => _AM_XOOPSPOLL_OPTIONID],
                    ['order' => 'ip', 'label' => $ipLabel],
                    ['order' => 'user_id', 'label' => _AM_XOOPSPOLL_VOTER],
                    ['order' => 'time', 'label' => _AM_XOOPSPOLL_VOTETIME]
                ];

                foreach ($fieldArray as $field) {
                    echo "          <th nowrap>\n"
                         . "            <a href='{$sorthref}{$field['order']}&amp;orderdir=ASC'><img src='{$arrowUp}' alt=''></a>\n"
                         . "            <a href='{$sorthref}{$field['order']}&amp;orderdir=DESC'><img src='{$arrowDown}' alt=''></a>\n"
                         . "            &nbsp;{$field['label']}\n"
                         . "          </th>\n";
                }
                echo '        </tr>' . "        </thead>\n" . "        <tbody>\n";

                $optHandler = Xoopspoll\Helper::getInstance()->getHandler('Option');
                $luhConfig  = (Constants::LOOK_UP_HOST === $GLOBALS['xoopsModuleConfig']['look_up_host']) ? true : false;
                foreach ($logsArray as $thisLog) {
                    $logVals  = $thisLog->getValues();
                    $option   = $optHandler->get($logVals['option_id']);
                    $remoteIp = $luhConfig ? Xoopspoll\Utility::getHostByAddrWithCache($logVals['ip']) : $logVals['ip'];
                    echo "        <tr class='bg1'>\n" . "          <td class='{$class} center'>{$logVals['log_id']}</td>\n" . "          <td class='{$class}'>" . $option->getVar('option_text') . "</td>\n" . "          <td class='{$class} center'>{$remoteIp}</td>\n";

                    if (0 !== $logVals['user_id']) {
                        $user  = new \XoopsUser($logVals['user_id']);
                        $uname = $user->getVar('uname');

                        $from_userid = $GLOBALS['xoopsUser']->getVar('uid');
                        $to_userid   = $user->getVar('uid');
                        $pmLink      = $GLOBALS['xoops']->buildUrl($GLOBALS['xoops']->path('pmlite.php', true), [
                            'send'        => 1,
                            'from_userid' => $from_userid,
                            'to_userid'   => $to_userid
                        ]);

                        echo "          <td class='{$class} center'>\n"
                             . '            <a href='
                             . $GLOBALS['xoops']->url('/userinfo.php')
                             . '?uid='
                             . $user->getVar('uid')
                             . ">{$uname}</a>&nbsp;\n"
                             . "            <a href='{$pmLink}' target='_blank'><img src='"
                             . $pathIcon16
                             . "/mail_generic.png' alt='"
                             . _AM_XOOPSPOLL_PM_VOTER
                             . "' title='"
                             . _AM_XOOPSPOLL_PM_VOTER
                             . "'>\n"
                             . "          </td>\n";
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
                $pageNav = new \XoopsPageNav($logsCount, $limit, $start, 'start', "op=log&amp;poll_id={$pollId}");
                echo "<div class='right' style='margin: 2em auto;'>" . $pageNav->renderNav() . '</div>';
            }
        }

        //    echo "<div class='center' style='margin-bottom: 1em;'>[ <a href='" . $_SERVER['PHP_SELF'] . "?op=list'>" . _AM_XOOPSPOLL_RETURNLIST . "</a> ]</div>\n";
        //    echo "<div class='center' style='margin-bottom: 1em;'>[ <a href='" . $_SERVER['PHP_SELF'] . "?op=list'><img src='". $pathIcon16 ."/back.png' alt='" . _AM_XOOPSPOLL_RETURNLIST . "' title='" . _AM_XOOPSPOLL_RETURNLIST . "'>" . _AM_XOOPSPOLL_RETURNLIST . "</a> ]</div>\n";
        $adminObject->addItemButton(_AM_XOOPSPOLL_RETURNLIST, 'main.php' . '?op=list', $icon = '../16/back');
        $adminObject->displayButton('center');
        require_once __DIR__ . '/admin_footer.php';
        break;

    case 'quickupdate':

        $pollId = Request::getArray('poll_id', [], 'POST');
        $pollId = $pollId;
        $pollId = array_map('intval', $pollId);

        $count = count($pollId);

        if ($count) {
            $pollHandler = Xoopspoll\Helper::getInstance()->getHandler('Poll');
            $criteria    = new \CriteriaCompo();
            $idString    = '(' . implode(',', $pollId) . ')';
            $criteria->add(new \Criteria('poll_id', $idString, 'IN'));
            $pollObjs = $pollHandler->getAll($criteria);

            // get display variables from form POST
            $display = Request::getArray('display', [], 'POST');
            $display = array_map('intval', $display);
            $weight  = Request::getArray('weight', [], 'POST');
            $weight  = array_map('intval', $weight);

            foreach ($pollObjs as $pollObj) {
                $thisId           = $pollObj->getVar('poll_id');
                $display[$thisId] = empty($display[$thisId]) ? Constants::DO_NOT_DISPLAY_POLL_IN_BLOCK : Constants::DISPLAY_POLL_IN_BLOCK;
                $weight[$thisId]  = empty($weight[$thisId]) ? Constants::DEFAULT_WEIGHT : $weight[$thisId];
                if ($display[$thisId] !== $pollObj->getVar('display') || $weight[$thisId] !== $pollObj->getVar('weight')) {
                    $pollObj->setVars(['display' => $display[$thisId], 'weight' => $weight[$thisId]]);
                    $pollHandler->insert($pollObj);
                }
                unset($pollObj);
            }
            unset($pollObjs);
            require_once $GLOBALS['xoops']->path('class/template.php');
            xoops_template_clear_module_cache($GLOBALS['xoopsModule']->getVar('mid'));
            redirect_header($_SERVER['PHP_SELF'], Constants::REDIRECT_DELAY_SHORT, _AM_XOOPSPOLL_DBUPDATED);
        } else {
            redirect_header($_SERVER['PHP_SELF'], Constants::REDIRECT_DELAY_SHORT, _AM_XOOPSPOLL_NOTHING_HERE);
        }
        break;
    // added cloning capability in v 1.40
    case 'clone':
        $pollHandler = Xoopspoll\Helper::getInstance()->getHandler('Poll');
        $optHandler  = Xoopspoll\Helper::getInstance()->getHandler('Option');
        $pollId      = Request::getInt('poll_id', 0);
        $pollObj     = $pollHandler->get($pollId);
        $origValues  = $pollObj->getValues();
        unset($origValues['poll_id']);
        $pollDuration = $origValues['end_time'] - $origValues['start_time'];
        $pollDuration = ($pollDuration > 0) ? $pollDuration : Constants::DEFAULT_POLL_DURATION;
        $newValues    = [
            'votes'       => 0,
            'voters'      => 0,
            'mail_status' => Constants::POLL_NOT_MAILED,
            'question'    => $origValues['question'] . '(' . _AM_XOOPSPOLL_CLONE . ')',
            'start_time'  => time(),  //set the start time to now
            'end_time'    => time() + $pollDuration
        ];
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
        redirect_header($_SERVER['PHP_SELF'] . "?poll_id={$cloneId}&amp;op=edit", Constants::REDIRECT_DELAY_MEDIUM, _AM_XOOPSPOLL_CLONE_SUCCESS);
        break;
}
