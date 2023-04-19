<?php declare(strict_types=1);

namespace XoopsModules\Xoopspoll;

/*
               XOOPS - PHP Content Management System
                   Copyright (c) 2000-2020 XOOPS.org
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
 * Poll Renderer class for the XoopsPoll Module
 *
 * @copyright ::  {@link https://xoops.org/ XOOPS Project}
 * @license   ::  {@link https://www.gnu.org/licenses/gpl-2.0.html GNU GPL 2.0 or later}
 * @subpackage::  admin
 * @since     ::  1.0
 * @author    ::  {@link https://www.myweb.ne.jp/ Kazumi Ono (AKA onokazu)}
 */

use Xmf\Request;

Helper::getInstance()->loadLanguage('main');

/**
 * Class Renderer
 */
class Renderer
{
    // XoopsPoll class object
    protected $pollObj;
    protected $pollHandler;
    protected $optionHandler;
    protected $logHandler;
    protected $helper;
    // constructor(s)

    /**
     * @param null $poll
     * @param null $helper
     */
    public function __construct($poll = null, $helper = null)
    {
        $this->helper = $helper ?? Helper::getInstance();
        // setup handlers
        $this->pollHandler   = $this->helper->getHandler('Poll');
        $this->optionHandler = $this->helper->getHandler('Option');
        $this->logHandler    = $this->helper->getHandler('Log');

        if ($poll instanceof Poll) {
            $this->pollObj = $poll;
        } elseif (!empty($poll) && ((int)$poll > 0)) {
            $this->pollObj = $this->pollHandler->get((int)$poll);
        } else {
            $this->pollObj = $this->pollHandler->create();
        }
    }

    /**
     * create html form to display poll
     * @return string html form for display
     */
    public function renderForm(): string
    {
        $myTpl = new \XoopsTpl();
        $this->assignForm($myTpl);  // get the poll information

        //        return $myTpl->fetch($GLOBALS['xoops']->path('modules/xoopspoll/templates/xoopspoll_view.tpl'));
        return $myTpl->fetch($this->helper->path('templates/xoopspoll_view.tpl'));
    }

    /**
     * assigns form values to template for display
     * @var    \XoopsTpl
     */
    public function assignForm(\XoopsTpl $tpl): void
    {
        $myts       = \MyTextSanitizer::getInstance();
        $optionObjs = $this->optionHandler->getAllByPollId($this->pollObj->getVar('poll_id'));

        if (empty($optionObjs)) {
            /* there was a problem with missing Options */
            //            redirect_header(Request::getString('HTTP_REFERER', '', 'SERVER'), Constants::REDIRECT_DELAY_MEDIUM, _MD_XOOPSPOLL_ERROR_OPTIONS_MISSING);
        }

        if (Constants::MULTIPLE_SELECT_POLL === $this->pollObj->getVar('multiple')) {
            $optionType = 'checkbox';
            $optionName = 'option_id[]';
        } else {
            $optionType = 'radio';
            $optionName = 'option_id';
        }
        foreach ($optionObjs as $optionObj) {
            $options[] = [
                'input' => "<input type='{$optionType}' " . "name='{$optionName}' " . "value='" . $optionObj->getVar('option_id') . "'>",
                'text'  => $optionObj->getVar('option_text'),
            ];
        }
        $uid      = (isset($GLOBALS['xoopsUser'])
                     && \is_object($GLOBALS['xoopsUser'])) ? $GLOBALS['xoopsUser']->getVar('uid') : 0;
        $can_vote = false;
        if ($this->pollObj->isAllowedToVote()
            && (!$this->logHandler->hasVoted($this->pollObj->getVar('poll_id'), \xoops_getenv('REMOTE_ADDR'), $uid))) {
            $can_vote = true;
        }
        /*
                $tpl->assign('poll', array(
                                       'question'     => htmlspecialchars($this->pollObj->getVar('question')),
                                       'pollId'       => $this->pollObj->getVar('poll_id'),
                                       'viewresults'  => $GLOBALS['xoops']->url("modules/xoopspoll/pollresults.php") . "?poll_id=" . $this->pollObj->getVar('poll_id'),
                                       'options'      => $options,
                                       'description'  => $myts->displayTarea($myts->undoHtmlSpecialChars($this->pollObj->getVar('description')), 1))
                );
        */
        $tpl->assign([
                         'poll'         => [
                             'question'    => \htmlspecialchars($this->pollObj->getVar('question'), \ENT_QUOTES | \ENT_HTML5),
                             'pollId'      => $this->pollObj->getVar('poll_id'),
                             'viewresults' => $GLOBALS['xoops']->url('modules/xoopspoll/pollresults.php') . '?poll_id=' . $this->pollObj->getVar('poll_id'),
                             'options'     => $options ?? [],
                             'description' => $myts->displayTarea($myts->undoHtmlSpecialChars($this->pollObj->getVar('description')), 1),
                         ],
                         'can_vote'     => $can_vote,
                         'action'       => $GLOBALS['xoops']->url('modules/xoopspoll/index.php'),
                         'lang_vote'    => \_MD_XOOPSPOLL_VOTE,
                         'lang_results' => \_MD_XOOPSPOLL_RESULTS,
                     ]);
    }

    /**
     * display html results to screen (echo)
     */
    public function renderResults()
    {
        $myTpl = new \XoopsTpl();
        $this->assignResults($myTpl);  // get the poll information

        //        return $myTpl->fetch($GLOBALS['xoops']->path('modules/xoopspoll/templates/xoopspoll_results_renderer.tpl'));
        return $myTpl->fetch($this->helper->path('templates/xoopspoll_results_renderer.tpl'));
    }

    /**
     * assigns form results to template
     * @var    \XoopsTpl tpl
     */
    public function assignResults(\XoopsTpl $tpl): void
    {
        $myts             = \MyTextSanitizer::getInstance();
        $xuEndTimestamp   = \xoops_getUserTimestamp($this->pollObj->getVar('end_time'));
        $xuEndFormatted   = \ucfirst(\date(_MEDIUMDATESTRING, (int)$xuEndTimestamp));
        $xuStartTimestamp = \xoops_getUserTimestamp($this->pollObj->getVar('start_time'));
        $xuStartFormatted = \ucfirst(\date(_MEDIUMDATESTRING, (int)$xuStartTimestamp));

        //        $logHandler =  $this->helper->getHandler('Log');
        $criteria = new \CriteriaCompo();
        $criteria->add(new \Criteria('poll_id', $this->pollObj->getVar('poll_id'), '='));
        $criteria->setSort('option_id');
        $optObjsArray = $this->optionHandler->getAll($criteria);
        $total        = $this->pollObj->getVar('votes');
        $i            = 0;
        foreach ($optObjsArray as $optObj) {
            $optionVars = $optObj->getValues();
            $percent    = ($total > 0) ? (100 * $optionVars['option_count'] / $total) : 0;
            if ($percent > 0) {
                $width                = (int)($percent * 2);
                $options[$i]['image'] = "<img src='" . $GLOBALS['xoops']->url("modules/xoopspoll/assets/images/colorbars/{$optionVars['option_color']}'") . " style='height: 14px; width: {$width}px; vertical-align: middle;' alt='" . (int)$percent . "%'>";
            } else {
                $options[$i]['image'] = '';
            }

            /* setup module config handler - required since this is called by newbb too */
            /** @var \XoopsModuleHandler $moduleHandler */
            $moduleHandler = \xoops_getHandler('module');
            $configHandler = \xoops_getHandler('config');
            $xp_module     = $moduleHandler->getByDirname('xoopspoll');
            $module_id     = $xp_module->getVar('mid');
            $xp_config     = $configHandler->getConfigsByCat(0, $module_id);

            if ($xp_config['disp_vote_nums']) {
                $options[$i]['percent'] = \sprintf(' %01.1f%% (%d)', $percent, $optionVars['option_count']);
            } else {
                $options[$i]['percent'] = \sprintf(' %01.1f%%', $percent);
            }
            $options[$i]['text']  = $optionVars['option_text'];
            $options[$i]['total'] = $optionVars['option_count'];
            $options[$i]['value'] = (int)$percent;
            ++$i;
            unset($optionVars);
        }
        $uid  = (isset($GLOBALS['xoopsUser'])
                 && \is_object($GLOBALS['xoopsUser'])) ? $GLOBALS['xoopsUser']->getVar('uid') : 0;
        $vote = null;
        if (!$this->pollObj->hasExpired() && $this->pollObj->isAllowedToVote()
            && !$this->logHandler->hasVoted($this->pollObj->getVar('poll_id'), \xoops_getenv('REMOTE_ADDR'), $uid)) {
            $vote = "<a href='" . $GLOBALS['xoops']->url('modules/xoopspoll/index.php') . '?poll_id=' . $this->pollObj->getVar('poll_id') . "'>" . \_MD_XOOPSPOLL_VOTE_NOW . '</a>';
        }
        if ($xp_config['disp_vote_nums']) {
            $totalVotes  = \sprintf(\_MD_XOOPSPOLL_TOTALVOTES, $total);
            $totalVoters = \sprintf(\_MD_XOOPSPOLL_TOTALVOTERS, $this->pollObj->getVar('voters'));
        } else {
            $totalVotes = $totalVoters = '';
        }

        $tpl->assign('poll', [
            'question'    => \htmlspecialchars($this->pollObj->getVar('question'), \ENT_QUOTES | \ENT_HTML5),
            'end_text'    => $xuEndFormatted,
            'start_text'  => $xuStartFormatted,
            'totalVotes'  => $totalVotes,
            'totalVoters' => $totalVoters,
            'vote'        => $vote,
            'options'     => $options,
            'description' => $this->pollObj->getVar('description'), //allow html
        ]);
    }
}
