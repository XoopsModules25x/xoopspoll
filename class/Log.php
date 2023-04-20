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
 * Log class for the XoopsPoll Module
 *
 * @copyright ::  {@link https://xoops.org/ XOOPS Project}
 * @license   ::  {@link https://www.gnu.org/licenses/gpl-2.0.html GNU GPL 2.0 or later}
 * @subpackage::  class
 * @since     ::  1.40
 * @author    ::  {@link https://www.myweb.ne.jp/ Kazumi Ono (AKA onokazu)}
 **/

use XoopsModules\Xoopspoll\{
    Helper
};

/**
 * Log() class definition for Log Objects
 * @author:: zyspec <zyspec@yahoo.com>
 * @uses  ::   xoops_getModuleHandler poll module handler for class use
 */
class Log extends \XoopsObject
{
    private int $log_id;
    private int $poll_id;
    private int $option_id;
    private string $ip;
    private int $user_id;
    private int $time;

    //  class Log extends \XoopsObject {
    //    var $db;

    /**
     * Constructor
     * @param int|null $id
     */
    public function __construct(int $id = null)
    {
        parent::__construct();
        $this->initVar('log_id', \XOBJ_DTYPE_INT, 0);
        $this->initVar('poll_id', \XOBJ_DTYPE_INT, null, true);
        $this->initVar('option_id', \XOBJ_DTYPE_INT, null, true);
        $this->initVar('ip', \XOBJ_DTYPE_OTHER, null);
        $this->initVar('user_id', \XOBJ_DTYPE_INT, 0);
        $this->initVar('time', \XOBJ_DTYPE_INT, null);
        if (!empty($id) && \is_array($id)) {
            $this->assignVars($id);
        }
    }

    /**
     * The following method is provided for backward compatibility with newbb
     * @param int $pid
     * @return mixed
     * @deprecated since Xoopspoll 1.40, please use LogHandler & Log
     */
    public static function deleteByPollId(int $pid): mixed
    {
        $trace = \debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        $GLOBALS['xoopsLogger']->addDeprecated(__CLASS__ . '::' . __FUNCTION__ . ' is deprecated since Xoopspoll 1.40, please use Log and LogHandler methods instead.' . " Called from {$trace[0]['file']}line {$trace[0]['line']}");
        /** @var LogHandler $slogHandler */
        $slogHandler = self::getStaticLogHandler();
        $criteria    = new \Criteria('poll_id', (int)$pid, '=');

        return $slogHandler->deleteAll($criteria);
    }

    /**
     * @param int $opt_id
     * @return mixed
     */
    public static function deleteByOptionId(int $opt_id): mixed
    {
        $trace = \debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        $GLOBALS['xoopsLogger']->addDeprecated(__CLASS__ . '::' . __FUNCTION__ . ' is deprecated since Xoopspoll 1.40, please use Log and LogHandler methods instead.' . " Called from {$trace[0]['file']}line {$trace[0]['line']}");

        /** @var LogHandler $slogHandler */
        $slogHandler = self::getStaticLogHandler();
        $criteria    = new \Criteria('option_id', (int)$opt_id, '=');

        return $slogHandler->deleteAll($criteria);
    }

    /**
     * @param int $pid
     * @param string $ip
     * @param int $uid
     * @return mixed
     */
    public static function hasVoted(int $pid, string $ip, int $uid): mixed
    {
        $trace = \debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        $GLOBALS['xoopsLogger']->addDeprecated(__CLASS__ . '::' . __FUNCTION__ . ' is deprecated since Xoopspoll 1.40, please use Log and LogHandler methods instead.' . " Called from {$trace[0]['file']}line {$trace[0]['line']}");

        /** @var LogHandler $slogHandler */
        $slogHandler = self::getStaticLogHandler();

        return $slogHandler->hasVoted($pid, $ip, $uid);
    }

    /**
     * @return bool
     */
    private static function getStaticLogHandler(): bool
    {
        static $logHandler;
        $trace = \debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        $GLOBALS['xoopsLogger']->addDeprecated(__CLASS__ . '::' . __FUNCTION__ . ' is deprecated since Xoopspoll 1.40, please use Log and LogHandler methods instead.' . " Called from {$trace[0]['file']}line {$trace[0]['line']}");

        if (!isset($logHandler)) {
            $logHandler = Helper::getInstance()->getHandler('Log');
        }

        return $logHandler;
    }
    /**#@-*/
}
