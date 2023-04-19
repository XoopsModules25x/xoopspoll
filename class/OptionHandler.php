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
 * Poll Option class for the XoopsPoll Module
 *
 * @copyright ::  {@link https://xoops.org/ XOOPS Project}
 * @license   ::  {@link https://www.gnu.org/licenses/gpl-2.0.html GNU GPL 2.0 or later}
 * @subpackage::  class
 * @since     ::  1.0
 * @author    ::  {@link https://www.myweb.ne.jp/ Kazumi Ono (AKA onokazu)}
 */

use XoopsModules\Xoopspoll\{
    Helper
};

/**
 * Class OptionHandler
 */
class OptionHandler extends \XoopsPersistableObjectHandler
{
    /**
     * @var \XoopsModules\Xoopspoll\Helper
     */
    protected $helper;

    /**
     * PollOptionHandler::__construct()
     *
     * @param null|\XoopsDatabase                 $db
     * @param \XoopsModules\Xoopspoll\Helper|null $helper
     */
    public function __construct(\XoopsDatabase $db = null, Helper $helper = null)
    {
        $this->helper = $helper ?? Helper::getInstance();

        parent::__construct($db, 'xoopspoll_option', Option::class, 'option_id', 'option_text');
    }

    /**
     * Update the option vote count for an Option Object
     * @param mixed $optionObj is an option object to update
     * @return mixed results @see XoopsPersistibleObjectHandler
     * @uses xoops_getModuleHandler
     * @uses XoopsPersistableObjectHandler::insert
     */
    public function updateCount($optionObj)
    {
        $status = false;
        static $logHandler;
        if ($optionObj instanceof Option) {
            $option_id = $optionObj->getVar('option_id');
            if (!isset($logHandler)) {
                $logHandler = $this->helper::getInstance()->getHandler('Log');
            }
            $votes = $logHandler->getTotalVotesByOptionId($option_id);
            $optionObj->setVar('option_count', $votes);
            $status = $this->insert($optionObj);
        }

        return $status;
    }

    /**
     * Gets all options for poll ID
     *
     * @param int    $pid
     * @param string $sortby
     * @param string $orderby
     * @return array  an array of Option objects
     * @uses CriteriaCompo
     * @uses XoopsPersistableObjectHandler::deleteAll
     */
    public function getAllByPollId($pid = 0, $sortby = 'option_id', $orderby = 'ASC'): array
    {
        //        $criteria = new \CriteriaCompo();
        $criteria = new \Criteria('poll_id', (int)$pid, '=');
        if (!empty($sortby)) {
            $criteria->setSort($sortby);
        }
        if (!empty($orderby)) {
            $criteria->setOrder($orderby);
        }
        $optionObjs = &$this->getAll($criteria);
        if (empty($optionObjs)) {
            $optionObjs = [];
        }

        return $optionObjs;
    }

    /**
     * Deletes the option for selected poll
     *
     * @param int $pid
     * @return bool $success
     * @uses XoopsPersistableObjectHandler::deleteAll
     * @uses Criteria
     */
    public function deleteByPollId($pid = 0): bool
    {
        $success = $this->deleteAll(new \Criteria('poll_id', (int)$pid, '='));

        return $success;
    }

    /**
     * Reset the vote counts for the options for selected poll
     *
     * @param int $pid
     * @return bool $success
     * @uses XoopsPersistableObjectHandler::updateAll
     * @uses Criteria
     */
    public function resetCountByPollId($pid = 0): bool
    {
        $success = $this->updateAll('option_count', 0, new \Criteria('poll_id', (int)$pid, '='));

        return $success;
    }

    /**
     * Generates a HTML select box with options
     * @param mixed $pid the select box is created for this poll id
     * @return \XoopsFormElementTray html select box
     */
    public function renderOptionFormTray($pid = 0): \XoopsFormElementTray
    {
        \xoops_load('xoopsformloader');
        $pid            = (int)$pid;
        $barcolor_array = \XoopsLists::getImgListAsArray($GLOBALS['xoops']->path('modules/xoopspoll/assets/images/colorbars/'));

        /**
         * get all the options for this poll & add some blank options to allow adding more
         */
        if (0 === $pid) {
            $newOpts = (2 * Constants::NUM_ADDTL_OPTIONS);
        } else {
            $optionObjs = $this->getAllByPollId($pid);
            $newOpts    = Constants::NUM_ADDTL_OPTIONS;
        }
        $thisBarColorArray = $barcolor_array;
        unset($thisBarColorArray['blank.gif']);
        for ($i = 0; $i < $newOpts; ++$i) {
            $thisObj    = $this->create();
            $currentBar = \array_rand($thisBarColorArray);
            unset($thisBarColorArray[$currentBar]);
            $thisObj->setVar('option_color', $currentBar);
            $optionObjs[] = $thisObj;
            if (empty($thisBarColorArray)) {
                $thisBarColorArray = $barcolor_array;
                unset($thisBarColorArray['blank.gif']);
            }
            unset($thisObj);
        }
        /**
         * add the options to the form
         */
        $optionTray = new \XoopsFormElementTray(\_AM_XOOPSPOLL_POLLOPTIONS, '');
        $i          = 0;
        foreach ($optionObjs as $optObj) {
            $colorSelect = new \XoopsFormSelect('', "option_color[{$i}]", $optObj->getVar('option_color'));
            $colorSelect->addOptionArray($barcolor_array);
            $colorSelect->setExtra("onchange='showImgSelected(\"option_color_image[{$i}]\", \"option_color[{$i}]\", \"modules/xoopspoll/assets/images/colorbars\", \"\", \"" . $GLOBALS['xoops']->url('') . "\")'");
            $colorLabel = new \XoopsFormLabel(
                '',
                "<img src='" . $GLOBALS['xoops']->url('modules/xoopspoll' . '/assets/images/colorbars/' . $optObj->getVar('option_color')) . "'" . " name='option_color_image[{$i}]'" . " id='option_color_image[{$i}]'" . " style='width: 30px; height: 15px;'" . " class='alignmiddle'" . " alt=''><br>"
            );

            $optionTray->addElement(new \XoopsFormText('', "option_text[{$i}]", 50, 255, $optObj->getVar('option_text')));
            $optionTray->addElement(new \XoopsFormHidden("option_id[{$i}]", $optObj->getVar('option_id')));
            $optionTray->addElement($colorSelect);
            $optionTray->addElement($colorLabel);
            unset($colorSelect, $colorLabel);
            ++$i;
        }

        return $optionTray;
    }
}
