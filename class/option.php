<?php namespace XoopsModules\Xoopspoll;

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
 * Poll Option class for the XoopsPoll Module
 *
 * @copyright ::  {@link https://xoops.org/ XOOPS Project}
 * @license   ::  {@link http://www.fsf.org/copyleft/gpl.html GNU public license}
 * @package   ::  xoopspoll
 * @subpackage::  class
 * @since     ::  1.0
 * @author    ::  {@link http://www.myweb.ne.jp/ Kazumi Ono (AKA onokazu)}
 */

use XoopsModules\Xoopspoll;

// defined('XOOPS_ROOT_PATH') || die('Restricted access');

class Option extends \XoopsObject
{
    /**
     * database connection object
     * @var XoopsDatabasefactory
     */
    //    protected $db;
    /**
     * holds option object
     * @var Option
     */
    protected $option;
    /**
     * holds an option handler
     * @var OptionHandler
     */
    protected $optHandler;

    // constructor

    /**
     * @param null $id
     */
    public function __construct($id = null)
    {
        parent::__construct();
        xoops_load('constants', 'xoopspoll');
        //        $this->db = \XoopsDatabaseFactory::getDatabaseConnection();
        $this->initVar('option_id', XOBJ_DTYPE_INT, null, false);
        $this->initVar('poll_id', XOBJ_DTYPE_INT, null, true);
        $this->initVar('option_text', XOBJ_DTYPE_TXTBOX, null, true, 255);
        $this->initVar('option_count', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('option_color', XOBJ_DTYPE_OTHER, null, false);

        /**
         * {@internal The following is provided for backward compatibility with newbb/xforum}
         */
        $this->optHandler = $this->getStaticOptHandler();
        if (!empty($id)) {
            if (is_array($id)) {
                $this->option = $this->optHandler->create();
                $this->option->assignVars($id);
            } else {
                $this->option = $this->optHandler->get($id);
            }
        }
    }

    /**
     * @param null $id
     */
    public function Option($id = null)
    {
        $this->__construct($id);
    }
    /**#@+
     * The following method is provided for backward compatibility with newbb/xforum
     * @deprecated since Xoopspoll 1.40, please @see OptionHandler & @see Option
     */
    /**
     *
     * Stores object into the database
     * @uses XoopsPersistableObjectHandler::insert
     * @returns mixed
     */
    public function store()
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        $GLOBALS['xoopsLogger']->addDeprecated(__CLASS__ . '::' . __METHOD__ . ' is deprecated since Xoopspoll 1.40, please use Poll and PollHandler classes instead.' . ". Called from {$trace[0]['file']}line {$trace[0]['line']}");
        $soptHandler = $this->getStaticOptHandler();

        return $soptHandler->insert($this->option);
    }

    /**
     *
     * Delete all the poll options for a specific poll
     * @uses XoopsPersistableObjectHandler::deleteAll
     * @param  int $pid is used to delete all options by this id
     * @return mixed results of deleting objects from database
     */
    public function deleteByPollId($pid)
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        $GLOBALS['xoopsLogger']->addDeprecated(__CLASS__ . '::' . __METHOD__ . ' is deprecated since Xoopspoll 1.40, please use PollHandler::' . __METHOD__ . ' instead.' . ". Called from {$trace[0]['file']}line {$trace[0]['line']}");
        $soptHandler = $this->getStaticOptHandler();
        $criteria    = new \Criteria('poll_id', (int)$pid, '=');

        return $soptHandler->deleteAll($criteria);
    }

    /**
     *
     * Get all options for a particular poll
     * @uses XoopsPersistableObjectHandler::getAll
     * @param  unknown $pid
     * @return mixed   results of getting objects from database
     */
    public function getAllByPollId($pid)
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        $GLOBALS['xoopsLogger']->addDeprecated(__CLASS__ . '::' . __METHOD__ . ' is deprecated since Xoopspoll 1.40, please use PollHandler::' . __METHOD__ . ' instead.' . ". Called from {$trace[0]['file']}line {$trace[0]['line']}");
        $soptHandler = $this->getStaticOptHandler();
        $criteria    = new \Criteria('poll_id', (int)$pid, '=');

        return $soptHandler->getAll($criteria);
    }

    /**
     *
     * Reset the poll's options vote count
     * @param unknown_type $pid
     * @uses XoopsPersistableObjectHandler::updateAll
     * @return mixed results of the object(s) update
     */
    public function resetCountByPollId($pid)
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        $GLOBALS['xoopsLogger']->addDeprecated(__CLASS__ . '::' . __METHOD__ . ' is deprecated since Xoopspoll 1.40, please use PollHandler::' . __METHOD__ . ' instead.' . ". Called from {$trace[0]['file']}line {$trace[0]['line']}");
        $soptHandler = $this->getStaticOptHandler();
        $criteria    = new \Criteria('poll_id', (int)$pid, '=');

        return $soptHandler->updateAll('option_count', 0, $criteria);
    }

    /**
     *
     * Get a static Option Handler to be used to manipulate poll options
     * @uses xoops_getModuleHandler
     * @return mixed handler object returned on success, false on failure
     */
    private function getStaticOptHandler()
    {
        static $oHandler;

        if (!isset($oHandler)) {
            $oHandler = Xoopspoll\Helper::getInstance()->getHandler('Option');
        }

        return $oHandler;
    }
    /**#@-*/
}
