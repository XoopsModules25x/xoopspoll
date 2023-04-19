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

use Criteria;
use XoopsModules\Xoopspoll\{
    Helper
};
use XoopsObject;

/**
 * Class Option
 */
class Option extends XoopsObject
{
    private $option_id;
    private $poll_id;
    private $option_text;
    private $option_count;
    private $option_color;
    private $optHandler;

    /**
     * database connection object
     * @var \XoopsDatabasefactory
     */
    //    protected $db;
    /**
     * holds option object
     * @var Option
     */
    protected $option;
    /**
     * Option Handler to be used to manipulate poll options
     * @var OptionHandler
     */
    protected $optionHandler;
    // constructor

    /**
     * @param int|null|array $id poll id
     */
    public function __construct($id = null)
    {
        parent::__construct();
        //        $this->db = \XoopsDatabaseFactory::getDatabaseConnection();
        $this->initVar('option_id', \XOBJ_DTYPE_INT, null, false);
        $this->initVar('poll_id', \XOBJ_DTYPE_INT, null, true);
        $this->initVar('option_text', \XOBJ_DTYPE_TXTBOX, null, true, 255);
        $this->initVar('option_count', \XOBJ_DTYPE_INT, 0, false);
        $this->initVar('option_color', \XOBJ_DTYPE_OTHER, null, false);

        /**
         * {@internal The following is provided for backward compatibility with newbb/xforum}
         */
        $this->optHandler = $this->getStaticOptHandler();
        if (null !== $id) {
            if (\is_array($id)) {
                $this->option = $this->optHandler->create();
                $this->option->assignVars($id);
            } else {
                $this->option = $this->optHandler->get($id);
            }
        }
    }

    /**#@+
     * @deprecated since Xoopspoll 1.40, please @see OptionHandler & @see Option
     */

    /**
     * Stores object into the database
     * @return mixed
     * @uses       XoopsPersistableObjectHandler::insert
     * @deprecated since Xoopspoll 1.40, please @see XoopspollOptionHandler & @see XoopspollOption
     */
    public function store()
    {
        $trace = \debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        $GLOBALS['xoopsLogger']->addDeprecated(__CLASS__ . '::' . __METHOD__ . ' is deprecated since Xoopspoll 1.40, please use Poll and PollHandler classes instead.' . ". Called from {$trace[0]['file']}line {$trace[0]['line']}");

        return $this->optHandler->insert($this->option);
    }

    /**
     * Delete all the poll options for a specific poll
     * @param int $pid is used to delete all options by this id
     * @return mixed results of deleting objects from database
     * @uses XoopsPersistableObjectHandler::deleteAll
     */
    public function deleteByPollId($pid)
    {
        $trace = \debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        $GLOBALS['xoopsLogger']->addDeprecated(__CLASS__ . '::' . __METHOD__ . ' is deprecated since Xoopspoll 1.40, please use PollHandler::deleteAll instead.' . ". Called from {$trace[0]['file']}line {$trace[0]['line']}");
        $criteria = new Criteria('poll_id', (int)$pid, '=');

        return $this->optHandler->deleteAll($criteria);
    }

    /**
     * Get all options for a particular poll
     * @param int $pid
     * @return mixed   results of getting objects from database
     * @uses XoopsPersistableObjectHandler::getAll
     */
    public function getAllByPollId($pid)
    {
        $trace = \debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        $GLOBALS['xoopsLogger']->addDeprecated(__CLASS__ . '::' . __METHOD__ . ' is deprecated since Xoopspoll 1.40, please use PollHandler::getAll instead.' . ". Called from {$trace[0]['file']}line {$trace[0]['line']}");
        $criteria = new Criteria('poll_id', (int)$pid, '=');

        return $this->optHandler->getAll($criteria);
    }

    /**
     * Reset the poll's options vote count
     * @param int $pid
     * @return mixed results of the object(s) update
     * @uses XoopsPersistableObjectHandler::updateAll
     */
    public function resetCountByPollId($pid)
    {
        $trace = \debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        $GLOBALS['xoopsLogger']->addDeprecated(__CLASS__ . '::' . __METHOD__ . ' is deprecated since Xoopspoll 1.40, please use PollHandler::updateAll instead.' . ". Called from {$trace[0]['file']}line {$trace[0]['line']}");
        $soptHandler = $this->getStaticOptHandler();
        $criteria    = new Criteria('poll_id', (int)$pid, '=');

        return $soptHandler->updateAll('option_count', 0, $criteria);
    }

    /**
     * Get a static Option Handler to be used to manipulate poll options
     * @return mixed handler object returned on success, false on failure
     * @uses xoops_getModuleHandler
     */
    private function getStaticOptHandler()
    {
        static $optionHandler;

        if (!isset($optionHandler)) {
            $optionHandler = Helper::getInstance()->getHandler('Option');
        }

        return $optionHandler;
    }
    /**#@-*/
}
