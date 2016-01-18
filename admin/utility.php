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
 * Administration menu for the XoopsPoll Module
 *
 * @copyright::  {@link http://xoops.org/ XOOPS Project}
 * @license   :: {@link http://www.fsf.org/copyleft/gpl.html GNU public license}
 * @package   :: xoopspoll
 * @subpackage:: admin
 * @since     :: 1.40
 * @author    :: XOOPS Module Team
 * @version   :: $Id: $
 */

require_once __DIR__ . '/admin_header.php';
xoops_load('XoopsRequest');
xoops_load('pollUtility', 'xoopspoll');

$op = XoopsRequest::getString('op', 'list');
switch ($op) {
    case 'list':
    default:
        xoops_cp_header();
        $admin_class = new ModuleAdmin();

        $GLOBALS['xoopsTpl']->assign('navigation', $admin_class->addNavigation('utility.php'));

        $admin_class->addItemButton(_AM_XOOPSPOLL_IMPORT_UMFRAGE, 'utility.php' . '?op=umfrage', $icon = 'download');
        $GLOBALS['xoopsTpl']->assign('addPollButton', $admin_class->renderButton('left'));

        $GLOBALS['xoopsTpl']->assign('navigation', $admin_class->addNavigation('index.php'));

        $GLOBALS['xoopsTpl']->assign('umfrageIntro', _AM_XOOPSPOLL_UMFRAGE_INTRO);
        $GLOBALS['xoopsTpl']->display($GLOBALS['xoops']->path('modules/xoopspoll/templates/admin/xoopspoll_utility.tpl'));

        require_once __DIR__ . '/admin_header.php';
        break;

    /* Import data from umfrage */
    case 'umfrage':
        $ok = XoopsRequest::getString('ok', XoopspollConstants::CONFIRM_NOT_OK, 'POST');
        if ($ok) {
            if (!$GLOBALS['xoopsSecurity']->check()) {
                redirect_header($_SERVER['PHP_SELF'], XoopspollConstants::REDIRECT_DELAY_MEDIUM, implode('<br />', $GLOBALS['xoopsSecurity']->getErrors()));
            }
            // first check to see if umfrage module is installed and active
            $moduleHandler =& xoops_gethandler('module');
            $umModule      =& $moduleHandler->getByDirname('umfrage');
            try {
                if (false !== $umModule && $umModule->isactive()) {
                    // make sure the umfrage database tables exist
                    $configHandler  =& xoops_gethandler('config');
                    $umModuleConfig =& $configHandler->getConfigsByCat(0, $umModule->getVar('mid'));
                    $success        = false;
                    $umTables       = $umModule->getInfo('tables');
                    foreach ($umTables as $umTable) {
                        $s = XoopspollPollUtility::dbTableExists($GLOBALS['xoopsDB'], $umTable);
                        if (!$s) {
                            throw new Exception("Could not find the umfrage db table ({$umTable})");
                        }
                    }

                    //setup poll objects for both umfrage and xoopspoll
                    require_once $GLOBALS['xoops']->path('modules/umfrage/class/umfrage.php');
                    require_once $GLOBALS['xoops']->path('modules/umfrage/class/umfrageoption.php');
                    require_once $GLOBALS['xoops']->path('modules/umfrage/class/umfragelog.php');

                    $xpHandler    =& xoops_getmodulehandler('poll', 'xoopspoll');
                    $xpOptHandler =& xoops_getmodulehandler('option', 'xoopspoll');
                    $xpLogHandler =& xoops_getmodulehandler('log', 'xoopspoll');

                    // maps umfrage_desc : polltype to xoopspoll_desc : visibility
                    $typeToVisMap = array(
                        1 => XoopspollConstants::HIDE_NEVER,
                        2 => XoopspollConstants::HIDE_ALWAYS,
                        3 => XoopspollConstants::HIDE_VOTED,);

                    $err                = array();
                    $umContainer        = new Umfrage();
                    $umOptContainer     = new UmfrageOption();
                    $umLogContainer     = new UmfrageLog();
                    $allUmfragePollObjs = $umContainer->getAll();
                    foreach ($allUmfragePollObjs as $umPollObj) {
                        // make sure we don't have this question already (pretty strict comparison)
                        $criteria = new CriteriaCompo();
                        $criteria->add(new Criteria('question', trim($umPollObj->getVar('question')), '='));
                        $criteria->setLimit(1);
                        $pollExists = $xpHandler->getCount($criteria);
                        if (0 === $pollExists) {
                            // set the visibility for the poll
                            if (array_key_exists((int)($umPollObj->getVar('polltype')), $typeToVisMap)) {
                                $visibility = $typeToVisMap[$umPollObj->getVar('polltype')];
                            } else {
                                $visibility = XoopspollConstants::HIDE_END;
                            }
                            // save the poll into Xoopspoll database
                            $xpValues = array(
                                'question'    => $umPollObj->getVar('question'),
                                'description' => $umPollObj->getVar('description'),
                                'user_id'     => $umPollObj->getVar('user_id'),
                                'start_time'  => $umPollObj->getVar('start_time'),
                                'end_time'    => $umPollObj->getVar('end_time'),
                                'votes'       => (int)($umPollObj->getVar('votes')),
                                'voters'      => (int)($umPollObj->getVar('voters')),
                                'multiple'    => $umPollObj->getVar('multiple'),
                                'multilimit'  => $umPollObj->getVar('multilimit'),
                                'display'     => $umPollObj->getVar('display'),
                                'visibility'  => $visibility,
                                'weight'      => $umPollObj->getVar('weight'),
                                'mail_status' => $umPollObj->getVar('mail_status'),
                                'mail_voter'  => $umPollObj->getVar('mail_voter'));
                            $xpObj    = $xpHandler->create();
                            $xpObj->setVars($xpValues);
                            $newXpId = $xpHandler->insert($xpObj);

                            if ($newXpId) {
                                $optionIdMap = array();
                                /* get the options for this poll and insert them */
                                $umOptObjs = $umOptContainer->getAllByPollId($umPollObj->getVar('poll_id'));
                                if (!$umOptObjs) {
                                    throw new Exception('Could not find options for the ' . $umPollObj->getVar('question') . ' poll.');
                                }
                                foreach ($umOptObjs as $umOptObj) {
                                    $optValues = array(
                                        'poll_id'      => $newXpId,
                                        'option_text'  => $umOptObj->getVar('option_text'),
                                        'option_count' => $umOptObj->getVar('option_count'),
                                        'option_color' => $umOptObj->getVar('option_color'));
                                    $xpOptObj  = $xpOptHandler->create();
                                    $xpOptObj->setVars($optValues);
                                    $newXpOptId = $xpOptHandler->insert($xpOptObj);

                                    if ($newXpOptId) {
                                        $newOptId               = $newXpOptId;
                                        $oldOptId               = $umOptObj->getVar('option_id');
                                        $optionIdMap[$oldOptId] = $newOptId;
                                    } else {
                                        throw new Exception(sprintf(_AM_XOOPSPOLL_OPTION_FAILED, $umOptObj->getVar('option_text'), $umPollObj->getVar('question'), '<br />' . $xpOptObj->getHtmlErrors()));
                                    }
                                }
                                // now update the log for this poll
                                $allUmfrageLogObjs = $umLogContainer->getAllByPollId($umPollObj->getVar('poll_id'));
                                foreach ($allUmfrageLogObjs as $umLogObj) {
                                    $logValues = array(
                                        'poll_id'   => $newXpId,
                                        'option_id' => $optionIdMap[$umLogObj->getVar('option_id')],
                                        'ip'        => $umLogObj->getVar('ip'),
                                        'user_id'   => $umLogObj->getVar('user_id'),
                                        'time'      => $umLogObj->getVar('time'));
                                    $xpLogObj  = $xpLogHandler->create();
                                    $xpLogObj->setVars($logValues);
                                    $newLogId = $xpLogHandler->insert($xpLogObj);
                                    if (!$newLogId) {
                                        throw new Exception(sprintf(_AM_XOOPSPOLL_LOG_FAILED, $umPollObj->getVar('question') . '<br />' . $xpLogObj->getHtmlErrors()));
                                    }
                                }
                                unset($optionIdMap, $umOptObjs, $allUmfrageLogObjs);
                            } else {
                                throw new Exception(sprintf(_AM_XOOPSPOLL_QUESTION_FAILED, $umPollObj->getVar('question'), '<br />' . $xpObj->getHtmlErrors()));
                            }
                        } else {
                            throw new Exception(sprintf(_AM_XOOPSPOLL_QUESTION_IMPORT_FAILED, $umPollObj->getVar('question'), '<br />' . $umPollObj->getHtmlErrors()));
                        }
                        unset($criteria, $umOptObjs);
                    }
                    redirect_header('index.php', XoopspollConstants::REDIRECT_DELAY_MEDIUM, sprintf(_AM_XOOPSPOLL_IMPORT_SUCCESS, (int)(count($allUmfragePollObjs))));
                } else {
                    throw new Exception(_AM_XOOPSPOLL_UMFRAGE_FAILED);
                }
            } catch (Exception $e) {
                xoops_cp_header();
                $admin_class = new ModuleAdmin();
                echo $admin_class->addNavigation('utility.php');
                echo "<div class='floatcenter1'>" . xoops_error($e->getMessage(), _AM_XOOPSPOLL_IMPORT_FAILED) . "</div>\n";
                include_once __DIR__ . '/admin_footer.php';
                exit();
            }
        } else {
            xoops_cp_header();
            $admin_class = new ModuleAdmin();
            echo $admin_class->addNavigation('utility.php');
            xoops_confirm(array('op' => 'umfrage', 'ok' => 1), $_SERVER['PHP_SELF'], _AM_XOOPSPOLL_RUSUREUMFRAGE);
            include_once __DIR__ . '/admin_footer.php';
            exit();
        }
        break;
}
include_once __DIR__ . '/admin_footer.php';
