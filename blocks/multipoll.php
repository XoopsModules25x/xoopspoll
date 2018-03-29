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
 * XoopsPoll Display Multi-poll Block
 *
 * @copyright ::   {@link https://xoops.org/ XOOPS Project}
 * @license   ::  {@link http://www.fsf.org/copyleft/gpl.html GNU public license}
 * @package   ::  xoopspoll
 * @subpackage::  blocks
 * @since     ::  1.0
 *
 **/

use XoopsModules\Xoopspoll;
use XoopsModules\Xoopspoll\Constants;
use XoopsModules\Newbb;

xoops_loadLanguage('main', 'xoopspoll');
/*
require_once $GLOBALS['xoops']->path( "modules"
                                    . "/xoopspoll"
                                    . "/class"
                                    . "/pollutility.php"
);
*/
xoops_load('pollUtility', 'xoopspoll');
xoops_load('constants', 'xoopspoll');

/**
 *
 * Display XOOPS polls in a block
 *
 * @access public
 * @global mixed $GLOBALS ['xoopsUser']
 * @uses   CriteriaCompo
 * @uses   Criteria
 * @param  array $options block options array
 * @return array block keys and values to be used by block template
 */
function xoopspollBlockMultiShow($options)
{
    $block = [];

    /** @var XoopsModuleHandler $moduleHandler */
    $moduleHandler      = xoops_getHandler('module');
    $thisModule         = $moduleHandler->getByDirname('xoopspoll');
    $configHandler      = xoops_getHandler('config');
    $this_module_config = $configHandler->getConfigsByCat(0, $thisModule->getVar('mid'));

    $pollHandler = Xoopspoll\Helper::getInstance()->getHandler('Poll');
    $criteria    = new \CriteriaCompo();
    $criteria->add(new \Criteria('display', Constants::DISPLAY_POLL_IN_BLOCK, '='));
    $criteria->add(new \Criteria('start_time', time(), '<='));
    if (0 === $options[1]) {
        $criteria->add(new \Criteria('end_time', time(), '>='));
    }

    /**
     * now check to see if we want to hide polls that were created using newbb
     */
    if (($thisModule instanceof XoopsModule) && $thisModule->isactive() && $this_module_config['hide_forum_polls']) {
        $newbbModule = $moduleHandler->getByDirname('newbb');
        if ($newbbModule instanceof XoopsModule && $newbbModule->isactive()) {
            /** @var NewbbTopicHandler $topicHandler */
            $topicHandler = Newbb\Helper::getInstance()->getHandler('Topic');
            $tFields      = ['topic_id', 'poll_id'];
            $tArray       =& $topicHandler->getAll(new \Criteria('topic_haspoll', 0, '>'), $tFields, false);
            if (!empty($tArray)) {
                $tcriteria = [];
                foreach ($tArray as $t) {
                    $tcriteria[] = $t['poll_id'];
                }
                if (!empty($tcriteria)) {
                    $tstring = '(' . implode(',', $tcriteria) . ')';
                    $criteria->add(new \Criteria('poll_id', $tstring, 'NOT IN'));
                }
            }
            unset($topicHandler, $tFields, $tArray);
        }
        unset($newbbModule);
    }

    $criteria->setSort('weight ASC, end_time');  // trick criteria to allow 2 sort criteria
    $criteria->setOrder('DESC');
    $pollObjs = $pollHandler->getAll($criteria);
    $count    = count($pollObjs);
    if ($count) {
        $block['langVote']      = _MD_XOOPSPOLL_VOTE;
        $block['langResults']   = _MD_XOOPSPOLL_RESULTS;
        $block['langExpires']   = _MB_XOOPSPOLL_WILLEXPIRE;
        $block['langExpired']   = _MB_XOOPSPOLL_HASEXPIRED;
        $block['langComments']  = _MB_XOOPSPOLL_COMMENTS;
        $block['langComment']   = _MB_XOOPSPOLL_COMMENT;
        $block['url']           = 'http' . ((!empty($_SERVER['HTTPS'])) ? 's' : '') . '://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
        $block['dispVotes']     = $this_module_config['disp_vote_nums'];
        $block['thisModuleDir'] = 'xoopspoll';
        $block['asList']        = $options[0];

        $optHandler = Xoopspoll\Helper::getInstance()->getHandler('Option');
        $logHandler = Xoopspoll\Helper::getInstance()->getHandler('Log');

        foreach ($pollObjs as $pollObj) {
            $criteria = new \CriteriaCompo();
            $pollVars = $pollObj->getValues();
            $criteria->add(new \Criteria('poll_id', $pollVars['poll_id'], '='));
            $criteria->setSort('option_id');
            $pollOptionObjs = $optHandler->getAll($criteria);
            if (Constants::MULTIPLE_SELECT_POLL === $pollVars['multiple']) {
                $pollOptionType = 'checkbox';
                $pollOptionName = 'option_id[]';
            } else {
                $pollOptionType = 'radio';
                $pollOptionName = 'option_id';
            }

            $uid = 0;
            if (isset($GLOBALS['xoopsUser']) && ($GLOBALS['xoopsUser'] instanceof \XoopsUser)) {
                $uid = $GLOBALS['xoopsUser']->getVar('uid');
            }

            $totalVotes = $pollVars['votes'];
            $hasVoted   = $logHandler->hasVoted($pollVars['poll_id'], xoops_getenv('REMOTE_ADDR'), $uid) ? true : false;
            $canVote    = (!$hasVoted) && $pollObj->isAllowedToVote();
            foreach ($pollOptionObjs as $pollOptionObj) {
                $optionObjVars = $pollOptionObj->getValues();
                $percent       = ($totalVotes > 0) ? (100 * $optionObjVars['option_count'] / $totalVotes) : 0;
                //                $percent = ($totalVotes > 0) ? (int)(100 * $optionObjVars['option_count'] / $totalVotes) . '%' : '0%';
                $pollOptionArray[] = [
                    'id'      => $optionObjVars['option_id'],
                    'text'    => $optionObjVars['option_text'],
                    'count'   => $optionObjVars['option_count'],
                    'percent' => sprintf(' %01.1f%%', $percent),
                    'color'   => $optionObjVars['option_color']
                ];
            }
            unset($pollOptionObjs, $optionObjVars);
            $xuEndTimestamp     = xoops_getUserTimestamp($pollObj->getVar('end_time'));
            $xuEndFormattedTime = ucfirst(date(_MEDIUMDATESTRING, $xuEndTimestamp));

            $isVisible  = (true === $pollObj->isResultVisible()) ? true : false;
            $multiple   = $pollVars['multiple'] ? true : false;
            $multiLimit = (int)$pollVars['multilimit'];
            $lang_multi = '';
            if ($multiple && ($multiLimit > 0)) {
                $lang_multi = sprintf(_MB_XOOPSPOLL_MULTITEXT, $multiLimit);
            }

            $poll             = [
                'id'          => $pollVars['poll_id'],
                'visible'     => $isVisible,
                'question'    => $pollVars['question'],
                'multiple'    => $multiple,
                'lang_multi'  => $lang_multi,
                'optionType'  => $pollOptionType,
                'optionName'  => $pollOptionName,
                'options'     => $pollOptionArray,
                'hasExpired'  => $pollObj->hasExpired(),
                'canVote'     => $canVote,
                'votes'       => $pollVars['votes'],
                'hasVoted'    => $hasVoted,
                'totalVotes'  => sprintf(_MD_XOOPSPOLL_TOTALVOTES, $totalVotes),
                'comments'    => $pollObj->getComments($pollVars['poll_id']),
                'endTime'     => $xuEndFormattedTime,
                'commentMode' => Xoopspoll\Utility::commentMode()
            ];
            $block['polls'][] = $poll;
            unset($pollOptionArray, $poll, $pollVars);
        }
    }

    return $block;
}

/**
 *
 * Display a form to edit poll block display option
 *
 * @access public
 * @global mixed $GLOBALS ['xoopsUser']
 * @uses   xoops_getModuleHandler() function to get class handler for this modules class(es)
 * @param        array    options contains settings for block display (init in xoopsversion.php and saved in db)
 * @return string HTML form for display by block admin
 */
function xoopspollBlockMultiEdit($options)
{
    /**
     * Options[]
     *        [0]    0|1 = show as option|select
     *        [1]    0|1 show expired polls in block
     *
     */

    // find out if want to show expired polls in block
    // (otherwise it will hide block once it expires)
    if (0 === $options[1]) {
        $chk0no  = ' checked';
        $chk0yes = '';
    } else {
        $chk0no  = '';
        $chk0yes = ' checked';
    }
    $form = "<table>\n"
            . "  <tr>\n"
            . "    <td class='width25 middle'>"
            . _MB_XOOPSPOLL_SHOW_EXP
            . ":</td>\n"
            . "    <td>\n"
            . "      <label class='middle' for='yes'>"
            . _YES
            . "</label>\n"
            . "      <input type='radio' name='options[1]' value='1'{$chk0yes} id='yes'>\n"
            . "      <label class='middle' style='margin-left: 2em;' for='no'>&nbsp;&nbsp;&nbsp;"
            . _NO
            . "</label>\n"
            . "      <input type='radio' name='options[1]' value='0'{$chk0no} id='no'>\n"
            . "    </td>\n"
            . "  </tr>\n";

    // find out if want to show options as a lists or as a select boxes
    if (Constants::POLL_OPTIONS_SELECT === $options[0]) {
        $chk0select = ' checked';
        $chk0list   = '';
    } else {
        $chk0select = '';
        $chk0list   = ' checked';
    }
    $form .= "  <tr>\n"
             . "    <td class='width25 middle'>"
             . _MB_XOOPSPOLL_SHOW_OPTIONS
             . ":</td>\n"
             . "    <td>\n"
             . "      <label class='middle' for='list'>"
             . _MB_XOOPSPOLL_LIST
             . "</label>\n"
             . "      <input type='radio' name='options[0]' value='"
             . Constants::POLL_OPTIONS_LIST
             . "'{$chk0list} id='list'>\n"
             . "      <label class='middle' style='margin-left: 2em;' for='select'>&nbsp;&nbsp;&nbsp;"
             . _MB_XOOPSPOLL_SELECT
             . "</label>\n"
             . "      <input type='radio' name='options[0]' value='"
             . Constants::POLL_OPTIONS_SELECT
             . "'{$chk0select} id='select'>\n"
             . "    </td>\n"
             . "  </tr>\n"
             . "</table>\n";

    return $form;
}
