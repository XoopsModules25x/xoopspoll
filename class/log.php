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
 * Log class for the XoopsPoll Module
 *
 * @copyright ::  {@link https://xoops.org/ XOOPS Project}
 * @license   ::  {@link http://www.fsf.org/copyleft/gpl.html GNU public license}
 * @package   ::  xoopspoll
 * @subpackage::  class
 * @since     ::  1.40
 * @author    ::  {@link http://www.myweb.ne.jp/ Kazumi Ono (AKA onokazu)}
 **/

use XoopsModules\Xoopspoll;

// defined('XOOPS_ROOT_PATH') || die('Restricted access');

/**
 *
 * Log() class definition for Log Objects
 * @author:: zyspec <owners@zyspec.com>
 * @uses  ::   xoops_getModuleHandler poll module handler for class use
 *
 */
class Log extends \XoopsObject
{
    //  class Log extends \XoopsObject {
    //    var $db;

    /**
     * Constructor
     * @param null $id
     */
    public function __construct($id = null)
    {
        parent::__construct();
        $this->initVar('log_id', XOBJ_DTYPE_INT, 0);
        $this->initVar('poll_id', XOBJ_DTYPE_INT, null, true);
        $this->initVar('option_id', XOBJ_DTYPE_INT, null, true);
        $this->initVar('ip', XOBJ_DTYPE_OTHER, null);
        $this->initVar('user_id', XOBJ_DTYPE_INT, 0);
        $this->initVar('time', XOBJ_DTYPE_INT, null);
        if (!empty($id) && is_array($id)) {
            $this->assignVars($id);
        }
    }

    /**
     * @param null $id
     */
    public function Log($id = null)
    {
        $this->__construct($id);
    }

    /**
     * The following method is provided for backward compatibility with newbb
     * @deprecated since Xoopspoll 1.40, please use LogHandler & Log
     * @param int $pid
     * @return mixed
     */
    public static function deleteByPollId($pid)
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        $GLOBALS['xoopsLogger']->addDeprecated(__CLASS__ . '::' . __FUNCTION__ . ' is deprecated since Xoopspoll 1.40, please use Log and LogHandler methods instead.' . " Called from {$trace[0]['file']}line {$trace[0]['line']}");
        $slogHandler = self::getStaticLogHandler();
        $criteria    = new \Criteria('poll_id', (int)$pid, '=');

        return $slogHandler->deleteAll($criteria);
    }

    /**
     * @param $opt_id
     * @return mixed
     */
    public static function deleteByOptionId($opt_id)
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        $GLOBALS['xoopsLogger']->addDeprecated(__CLASS__ . '::' . __FUNCTION__ . ' is deprecated since Xoopspoll 1.40, please use Log and LogHandler methods instead.' . " Called from {$trace[0]['file']}line {$trace[0]['line']}");

        $slogHandler = self::getStaticLogHandler();
        $criteria    = new \Criteria('option_id', (int)$opt_id, '=');

        return $slogHandler->deleteAll($criteria);
    }

    /**
     * @param $pid
     * @param $ip
     * @param $uid
     * @return mixed
     */
    public static function hasVoted($pid, $ip, $uid)
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        $GLOBALS['xoopsLogger']->addDeprecated(__CLASS__ . '::' . __FUNCTION__ . ' is deprecated since Xoopspoll 1.40, please use Log and LogHandler methods instead.' . " Called from {$trace[0]['file']}line {$trace[0]['line']}");

        $slogHandler = self::getStaticLogHandler();

        return $slogHandler->hasVoted($pid, $ip, $uid);
    }

    /**
     * @return bool
     */
    private static function getStaticLogHandler()
    {
        static $log_h;
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        $GLOBALS['xoopsLogger']->addDeprecated(__CLASS__ . '::' . __FUNCTION__ . ' is deprecated since Xoopspoll 1.40, please use Log and LogHandler methods instead.' . " Called from {$trace[0]['file']}line {$trace[0]['line']}");

        if (!isset($log_h)) {
            $log_h = Xoopspoll\Helper::getInstance()->getHandler('Log');
        }

        return $log_h;
    }
    /**#@-*/
}
