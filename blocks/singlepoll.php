<?php
/*
 You may not change or alter any portion of this comment or credits
 of supporting developers from this source code or any supporting source code
 which is considered copyrighted (c) material of the original comment or credit authors.
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

/**
 * XoopsPoll Single Poll Block Definition (clonable)
 *
 * @copyright ::  {@link https://xoops.org/ XOOPS Project}
 * @license   :: {@link http://www.fsf.org/copyleft/gpl.html GNU public license}
 * @package   :: xoopspoll
 * @subpackage:: blocks
 * @since     :: 1.40
 */

use XoopsModules\Xoopspoll;
use XoopsModules\Xoopspoll\Constants;
use XoopsModules\Newbb;

xoops_loadLanguage('main', 'xoopspoll');
xoops_load('pollUtility', 'xoopspoll');
/*
require_once $GLOBALS['xoops']->path( "modules"
                                    . "/xoopspoll"
                                    . "/class"
                                    . "/pollutility.php"
);
*/
/**
 *
 * Display a single XOOPS Polls in a block
 *
 * @access public
 * @global mixed $GLOBALS ['xoopsUser']
 * @uses   CriteriaCompo
 * @uses   Criteria
 * @uses   xoops_getUserTimestamp() function to convert time to user time
 * @uses   formatTimestamp() takes timestamp and converts to human readable format
 * @param        array    options contains settings for block display
 * @return array block keys and values to be used by block template
 */
function xoopspollBlockSinglepollShow($options)
{
    $block = [];

    $configHandler = xoops_getHandler('config');
    $pollHandler   = Xoopspoll\Helper::getInstance()->getHandler('Poll');
    /** @var XoopsModuleHandler $moduleHandler */
    $moduleHandler      = xoops_getHandler('module');
    $thisModule         = $moduleHandler->getByDirname('xoopspoll');
    $this_module_config = $configHandler->getConfigsByCat(0, $thisModule->getVar('mid'));

    /* if admin hasn't initialized block then we'll pick a poll for them
     * provided that one exists in the database
     */
    if (0 === $options[1]) {
        $criteria = null;
        /**
         * check to see if we want to include polls created with forum (newbb)
         */
        if (($thisModule instanceof XoopsModule) && $thisModule->isactive()
            && $this_module_config['hide_forum_polls']) {
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
                        $tstring  = '(' . implode(',', $tcriteria) . ')';
                        $criteria = new \Criteria('poll_id', $tstring, 'NOT IN');
                    }
                }
                unset($topicHandler, $tFields, $tArray);
            }
            unset($newbbModule);
        }

        if ($pollHandler->getCount($criteria) > 0) {
            $pollIdArray = $pollHandler->getIds();
            $thisId      = array_shift($pollIdArray);
            $pollObj     = $pollHandler->get($thisId);
        } else {
            return $block;
        }
    } else {
        $pollObj = $pollHandler->get((int)$options[1]);
    }

    if ($pollObj instanceof Poll) {
        if (!$pollObj->hasExpired() || (1 === $options[0])) {
            $block['langVote']        = _MD_XOOPSPOLL_VOTE;
            $block['langResults']     = _MD_XOOPSPOLL_RESULTS;
            $block['langExpires']     = _MB_XOOPSPOLL_WILLEXPIRE;
            $block['langExpired']     = _MB_XOOPSPOLL_HASEXPIRED;
            $block['langComments']    = _MB_XOOPSPOLL_COMMENTS;
            $block['langComment']     = _MB_XOOPSPOLL_COMMENT;
            $block['showResultsLink'] = $options[2];
            $block['asList']          = $options[3];
            $block['thisModuleDir']   = 'xoopspoll';
            $block['url']             = 'http' . ((!empty($_SERVER['HTTPS'])) ? 's' : '') . '://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
            $block['dispVotes']       = $this_module_config['disp_vote_nums'];

            $optHandler = Xoopspoll\Helper::getInstance()->getHandler('Option');

            $pollVars = $pollObj->getValues();
            $criteria = new \CriteriaCompo();
            $criteria->add(new \Criteria('poll_id', $pollVars['poll_id'], '='));
            $criteria->setSort('option_id');
            $optionsObjArray = $optHandler->getAll($criteria);
            //            $optionsObjArray = $optHandler->getAll($criteria, null, false);

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

            $totalVotes       = $pollVars['votes'];
            $logHandler       = Xoopspoll\Helper::getInstance()->getHandler('Log');
            $hasVoted         = $logHandler->hasVoted($pollVars['poll_id'], xoops_getenv('REMOTE_ADDR'), $uid) ? true : false;
            $canVote          = (!$hasVoted) && $pollObj->isAllowedToVote();
            $pollOptionsArray = [];
            foreach ($optionsObjArray as $optionObj) {
                $percent = ($totalVotes > 0) ? (100 * $optionObj->getVar('option_count') / $totalVotes) : 0;
                //                $percent = ($totalVotes > 0) ? ceil(100 * $optionObj->getVar('option_count') / $totalVotes) . '%' : '0%';
                /*@TODO::  Change block templates to use Smarty html_options to support this... then comment
                           out old $pollOptionsArray assignment
                $pollOptionsArray[] = array('options' => array($optionObj['option_id'] => $optionObj['option_text']),
                                              'count' => $optionObj['option_count'],
                                            'percent' => $percent,
                                              'color' => $optionObj['option_color']
                ); */
                $pollOptionsArray[] = [
                    'id'      => $optionObj->getVar('option_id'),
                    'text'    => $optionObj->getVar('option_text'),
                    'count'   => $optionObj->getVar('option_count'),
                    'percent' => sprintf(' %01.1f%%', $percent),
                    'color'   => $optionObj->getVar('option_color')
                ];
            }

            $xuEndTimestamp     = xoops_getUserTimestamp($pollObj->getVar('end_time'));
            $xuEndFormattedTime = ucfirst(date(_MEDIUMDATESTRING, $xuEndTimestamp));

            $isVisible = (true === $pollObj->isResultVisible()) ? true : false;

            $multiple   = $pollVars['multiple'] ? true : false;
            $multiLimit = (int)$pollVars['multilimit'];
            $lang_multi = '';
            if ($multiple && ($multiLimit > 0)) {
                $lang_multi = sprintf(_MB_XOOPSPOLL_MULTITEXT, $multiLimit);
            }

            $block['id']          = $pollVars['poll_id'];
            $block['visible']     = $isVisible;
            $block['question']    = $pollVars['question'];
            $block['multiple']    = $multiple;
            $block['lang_multi']  = $lang_multi;
            $block['optionType']  = $pollOptionType;
            $block['optionName']  = $pollOptionName;
            $block['options']     = $pollOptionsArray;
            $block['hasExpired']  = $pollObj->hasExpired();
            $block['votes']       = $pollVars['votes'];
            $block['hasVoted']    = $hasVoted;
            $block['canVote']     = $canVote;
            $block['totalVotes']  = sprintf(_MD_XOOPSPOLL_TOTALVOTES, $totalVotes);
            $block['endTime']     = $xuEndFormattedTime; // formatted output for current user
            $block['comments']    = $pollObj->getComments($pollVars['poll_id']);
            $block['commentMode'] = Xoopspoll\Utility::commentMode();

            unset($optionsObjArray, $pollOptionsArray, $pollObj, $pollVars, $timeArray);
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
function xoopspollBlockSinglepollEdit($options)
{
    /**
     * Options[]
     *            0 = show expired polls in block
     *            1 = poll id to show
     *              if hiding expired poll then the next non-expired poll
     *              will show if the selected poll is hidden
     *          2 = show results button in block
     *          3 = show options as list|select
     */

    // find out if want to show expired polls in block
    // (otherwise it will hide block once it expires)
    if (0 === $options[0]) {
        $chk0no  = ' checked';
        $chk0yes = '';
    } else {
        $chk0no  = '';
        $chk0yes = ' checked';
    }
    $form = "<table><tr><td class='width25 middle'>"
            . _MB_XOOPSPOLL_SHOW_EXP
            . ':</td><td>'
            . "<label class='middle' for='yes'>"
            . _YES
            . "</label>\n"
            . "<input type='radio' name='options[0]' value='1'{$chk0yes} id='yes'>\n"
            . "<label class='middle' style='margin-left: 2em;' for='no'>&nbsp;&nbsp;&nbsp;"
            . _NO
            . "</label>\n"
            . "<input type='radio' name='options[0]' value='0'{$chk0no} id='no'>\n"
            . "</td></tr>\n";

    $pollHandler = Xoopspoll\Helper::getInstance()->getHandler('Poll');
    $pollFields  = ['poll_id', 'start_time', 'end_time', 'question', 'weight'];
    $criteria    = new \CriteriaCompo();
    //    $criteria->add(new \Criteria('end_time', time(), '>'));
    $criteria->setOrder('ASC');
    $criteria->setSort('weight');
    /**
     * Note that you can select polls that have not started yet so they will automatically show
     * up in the block once they have started.  To only allow selection of active polls uncomment
     * the following line in the code - this could be made a module config option if desired
     */
    // $criteria->add(new \Criteria('start_time', time(), '<='));
    /**
     * now check to see if we want to hide polls that were created using newbb
     */
    $configHandler = xoops_getHandler('config');
    /** @var XoopsModuleHandler $moduleHandler */
    $moduleHandler      = xoops_getHandler('module');
    $thisModule         = $moduleHandler->getByDirname('xoopspoll');
    $this_module_config = $configHandler->getConfigsByCat(0, $thisModule->getVar('mid'));

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

    $allPollsArray = $pollHandler->getAll($criteria, $pollFields, false);

    // next get a list of all available polls for select box
    $form .= '<tr><td>' . _MB_XOOPSPOLL_POLLS . ":</td><td style='text-align: left; left-margin: 1em;'>\n";
    if (empty($allPollsArray)) {
        $form .= "<span class='errorMsg'>" . _MB_XOOPSPOLL_NONE_ACTIVE . '</span>';
    } else {
        $form .= "<select name='options[1]'>\n";
        foreach ($allPollsArray as $thisPoll) {
            $selected       = ($thisPoll['poll_id'] === $options[1]) ? ' selected' : '';
            $taggedQuestion = ($thisPoll['end_time'] < time()) ? $thisPoll['question'] . '**' : $thisPoll['question'];
            $form           .= "  <option value='" . $thisPoll['poll_id'] . "'{$selected}>" . $taggedQuestion . "</option>\n";
        }
        $form .= "</select>\n" . '&nbsp;** - ' . _MB_XOOPSPOLL_EXPIRED_INDICATOR . "\n";
    }
    if (0 === $options[2]) {
        $chk2no  = ' checked';
        $chk2yes = '';
    } else {
        $chk2no  = '';
        $chk2yes = ' checked';
    }
    $form .= "</td></tr>\n"
             . "<tr><td class='width25 middle'>"
             . _MB_XOOPSPOLL_SHOW_RESULT_LINK
             . ':</td><td>'
             . "<label class='middle' for='yesr'>"
             . _YES
             . "</label>\n"
             . "<input type='radio' name='options[2]' value='1'{$chk2yes} id='yesr'>\n"
             . "<label class='middle' style='margin-left: 2em;' for='nor'>&nbsp;&nbsp;&nbsp;"
             . _NO
             . "</label>\n"
             . "<input type='radio' name='options[2]' value='0'{$chk2no} id='nor'>\n"
             . "</td></tr>\n";

    /* find out if want to show options as a list or as a select box */
    if (Constants::POLL_OPTIONS_SELECT === $options[3]) {
        $chk3select = ' checked';
        $chk3list   = '';
    } else {
        $chk3select = '';
        $chk3list   = ' checked';
    }
    $form .= "<table><tr><td class='width25 middle'>"
             . _MB_XOOPSPOLL_SHOW_OPTIONS
             . ':</td><td>'
             . "<label class='middle' for='list'>"
             . _MB_XOOPSPOLL_LIST
             . "</label>\n"
             . "<input type='radio' name='options[3]' value='"
             . Constants::POLL_OPTIONS_LIST
             . "'{$chk3list} id='list'>\n"
             . "<label class='middle' style='margin-left: 2em;' for='select'>&nbsp;&nbsp;&nbsp;"
             . _MB_XOOPSPOLL_SELECT
             . "</label>\n"
             . "<input type='radio' name='options[3]' value='"
             . Constants::POLL_OPTIONS_SELECT
             . "'{$chk3select} id='select'>\n"
             . "</td></tr>\n"
             . "</table>\n";

    return $form;
}
