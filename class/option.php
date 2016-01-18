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
 * Poll Option class for the XoopsPoll Module
 *
 * @copyright ::  {@link http://xoops.org/ XOOPS Project}
 * @license   ::  {@link http://www.fsf.org/copyleft/gpl.html GNU public license}
 * @package   ::  xoopspoll
 * @subpackage::  class
 * @since     ::  1.0
 * @author    ::  {@link http://www.myweb.ne.jp/ Kazumi Ono (AKA onokazu)}
 * @version   ::  $Id: $
 */
// defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');

class XoopspollOption extends XoopsObject
{
    /**
     * database connection object
     * @var XoopsDatabasefactory
     */
    //    protected $db;
    /**
     * holds option object
     * @var XoopspollOption
     */
    protected $option;
    /**
     * holds an option handler
     * @var XoopspollOptionHandler
     */
    protected $optHandler;

    // constructor
    /**
     * @param null $id
     */
    public function __construct(&$id = null)
    {
        parent::__construct();
        xoops_load('constants', 'xoopspoll');
        //        $this->db =& XoopsDatabaseFactory::getDatabaseConnection();
        $this->initVar('option_id', XOBJ_DTYPE_INT, null, false);
        $this->initVar('poll_id', XOBJ_DTYPE_INT, null, true);
        $this->initVar('option_text', XOBJ_DTYPE_TXTBOX, null, true, 255);
        $this->initVar('option_count', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('option_color', XOBJ_DTYPE_OTHER, null, false);

        /**
         * {@internal The following is provided for backward compatibility with newbb/xforum}
         */
        $this->optHandler = self::getStaticOptHandler();
        if (!empty($id)) {
            if (is_array($id)) {
                $this->option = & $this->optHandler->create();
                $this->option->assignVars($id);
            } else {
                $this->option = & $this->optHandler->get($id);
            }
        }
    }

    /**
     * @param null $id
     */
    public function XoopspollOption(&$id = null)
    {
        $this->__construct($id);
    }
    /**#@+
     * The following method is provided for backward compatibility with newbb/xforum
     * @deprecated since Xoopspoll 1.40, please @see XoopspollOptionHandler & @see XoopspollOption
     */
    /**
     *
     * Stores object into the database
     * @uses XoopsPersistableObjectHandler::insert
     * @returns mixed
     */
    public function store()
    {
        $GLOBALS['xoopsLogger']->addDeprecated(__CLASS__ . '::' . __METHOD__ . ' is deprecated since Xoopspoll 1.40, please use XoopspollPoll and XoopspollPollHandler classes instead.');
        $soptHandler = self::getStaticOptHandler();

        return $soptHandler->insert($this->option);
    }

    /**
     *
     * Delete all the poll options for a specific poll
     * @uses XoopsPersistableObjectHandler::deleteAll
     * @param  int $pid is used to delete all options by this id
     * @return mixed results of deleting objects from database
     */
    public function deleteByPollId(&$pid)
    {
        $GLOBALS['xoopsLogger']->addDeprecated(__CLASS__ . '::' . __METHOD__ . ' is deprecated since Xoopspoll 1.40, please use XoopspollPollHandler::' . __METHOD__ . ' instead.');
        $soptHandler = self::getStaticOptHandler();
        $criteria    = new Criteria('poll_id', (int)$pid, '=');

        return $soptHandler->deleteAll($criteria);
    }

    /**
     *
     * Get all options for a particular poll
     * @uses XoopsPersistableObjectHandler::getAll
     * @param  unknown $pid
     * @return mixed   results of getting objects from database
     */
    public function getAllByPollId(&$pid)
    {
        $GLOBALS['xoopsLogger']->addDeprecated(__CLASS__ . '::' . __METHOD__ . ' is deprecated since Xoopspoll 1.40, please use XoopspollPollHandler::' . __METHOD__ . ' instead.');
        $soptHandler = self::getStaticOptHandler();
        $criteria    = new Criteria('poll_id', (int)$pid, '=');

        return $soptHandler->getAll($criteria);
    }

    /**
     *
     * Reset the poll's options vote count
     * @param  unknown_type $pid
     * @uses XoopsPersistableObjectHandler::updateAll
     * @return mixed        results of the object(s) update
     */
    public function resetCountByPollId(&$pid)
    {
        $GLOBALS['xoopsLogger']->addDeprecated(__CLASS__ . '::' . __METHOD__ . ' is deprecated since Xoopspoll 1.40, please use XoopspollPollHandler::' . __METHOD__ . ' instead.');
        $soptHandler = self::getStaticOptHandler();
        $criteria    = new Criteria('poll_id', (int)$pid, '=');

        return $soptHandler->updateAll('option_count', 0, $criteria);
    }

    /**
     *
     * Get a static Option Handler to be used to manipulate poll options
     * @uses xoops_getmodulehandler
     * @return mixed handler object returned on success, false on failure
     */
    private function getStaticOptHandler()
    {
        static $oHandler;

        if (!isset($oHandler)) {
            $oHandler =& xoops_getmodulehandler('option', 'xoopspoll');
        }

        return $oHandler;
    }
    /**#@-*/
}

/**
 * Class XoopspollOptionHandler
 */
class XoopspollOptionHandler extends XoopsPersistableObjectHandler
{
    /**
     * XoopspollPollOptionHandler::__construct()
     *
     * @param mixed $db
     **/
    public function __construct(&$db)
    {
        xoops_load('constants', 'xoopspoll');
        parent::__construct($db, 'xoopspoll_option', 'XoopspollOption', 'option_id', 'option_text');
    }

    /**
     * XoopspollOptionHandler::XoopspollOptionHandler()
     *
     * @param mixed $db
     **/
    public function XoopspollOptionHandler(&$db)
    {
        $this->__construct($db);
    }

    /**
     *
     * Update the option vote count for a Option Object
     * @uses xoops_getmodulehandler
     * @uses XoopsPersistableObjectHandler::insert
     * @param  mixed $optionObj is an option object to update
     * @return mixed results @see XoopsPersistibleObjectHandler
     */
    public function updateCount(&$optionObj)
    {
        $status = false;
        static $logHandler;
        if ($optionObj instanceof XoopspollOption) {
            $option_id = $optionObj->getVar('option_id');
            if (!isset($logHandler)) {
                $logHandler =& xoops_getmodulehandler('log', 'xoopspoll');
            }
            $votes = $logHandler->getTotalVotesByOptionId($option_id);
            $optionObj->setVar('option_count', $votes);
            $status = $this->insert($optionObj);
        }

        return $status;
    }

    /**
     *
     * Gets all options for poll ID
     *
     * @param  int    $pid
     * @param  string $sortby
     * @param  string $orderby
     * @return array  an array of Option objects
     * @uses CriteriaCompo
     * @uses XoopsPersistableObjectHandler::deleteAll
     */
    public function getAllByPollId($pid = 0, $sortby = 'option_id', $orderby = 'ASC')
    {
        $criteria = new CriteriaCompo();
        $criteria = new Criteria('poll_id', (int)($pid), '=');
        if (!empty($sortby)) {
            $criteria->setSort($sortby);
        }
        if (!empty($orderby)) {
            $criteria->setOrder($orderby);
        }
        $optionObjs = & $this->getAll($criteria);
        if (empty($optionObjs)) {
            $optionObjs = array();
        }

        return $optionObjs;
    }

    /**
     *
     * Deletes the option for selected poll
     *
     * @param  int $pid
     * @uses Criteria
     * @uses XoopsPersistableObjectHandler::deleteAll
     * @return bool $success
     */
    public function deleteByPollId($pid = 0)
    {
        $success = $this->deleteAll(new Criteria('poll_id', (int)($pid), '='));

        return $success;
    }

    /**
     *
     * Reset the vote counts for the options for selected poll
     *
     * @param  int $pid
     * @uses Criteria
     * @uses XoopsPersistableObjectHandler::updateAll
     * @return bool $success
     */
    public function resetCountByPollId($pid = 0)
    {
        $success = $this->updateAll('option_count', 0, new Criteria('poll_id', (int)($pid), '='));

        return $success;
    }

    /**
     *
     * Generates an html select box with options
     * @param  mixed $pid the select box is created for this poll id
     * @return string html select box
     */
    public function renderOptionFormTray($pid = 0)
    {
        xoops_load('xoopsformloader');
        $pid            = (int)($pid);
        $barcolor_array =& XoopsLists::getImgListAsArray($GLOBALS['xoops']->path('modules/xoopspoll/assets/images/colorbars/'));

        /**
         * get all the options for this poll & add some blank options to allow adding more
         */
        if (0 === $pid) {
            $newOpts = (2 * XoopspollConstants::NUM_ADDTL_OPTIONS);
        } else {
            $optionObjs = self::getAllByPollId($pid);
            $newOpts    = XoopspollConstants::NUM_ADDTL_OPTIONS;
        }
        $thisBarColorArray = $barcolor_array;
        unset($thisBarColorArray['blank.gif']);
        for ($i = 0; ($i < $newOpts); ++$i) {
            $thisObj    = $this->create();
            $currentBar = array_rand($thisBarColorArray);
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
        $optionTray = new XoopsFormElementTray(_AM_XOOPSPOLL_POLLOPTIONS, '');
        $i          = 0;
        foreach ($optionObjs as $optObj) {
            $colorSelect = new XoopsFormSelect('', "option_color[{$i}]", $optObj->getVar('option_color'));
            $colorSelect->addOptionArray($barcolor_array);
            $colorSelect->setExtra("onchange='showImgSelected(\"option_color_image[{$i}]\", \"option_color[{$i}]\", \"modules/xoopspoll/assets/images/colorbars\", \"\", \"" . $GLOBALS['xoops']->url('') . "\")'");
            $colorLabel = new XoopsFormLabel('', "<img src='" . $GLOBALS['xoops']->url('modules/xoopspoll' . '/assets/images/colorbars/' . $optObj->getVar('option_color')) . "'" . " name='option_color_image[{$i}]'" . " id='option_color_image[{$i}]'" . " style='width: 30px; height: 15px;'" . " class='alignmiddle'" . " alt='' /><br />");

            $optionTray->addElement(new XoopsFormText('', "option_text[{$i}]", 50, 255, $optObj->getVar('option_text')));
            $optionTray->addElement(new XoopsFormHidden("option_id[{$i}]", $optObj->getVar('option_id')));
            $optionTray->addElement($colorSelect);
            $optionTray->addElement($colorLabel);
            unset($colorSelect, $colorLabel);
            ++$i;
        }

        return $optionTray;
    }
}
