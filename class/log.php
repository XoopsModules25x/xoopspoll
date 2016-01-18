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
 * Log class for the XoopsPoll Module
 *
 * @copyright ::  {@link http://xoops.org/ XOOPS Project}
 * @license   ::  {@link http://www.fsf.org/copyleft/gpl.html GNU public license}
 * @package   ::  xoopspoll
 * @subpackage::  class
 * @since     ::  1.40
 * @author    ::  {@link http://www.myweb.ne.jp/ Kazumi Ono (AKA onokazu)}
 * @version   ::  $Id: $
 **/

// defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');
/**
 *
 * XoopspollLog() class definition for Log Objects
 * @author:: zyspec <owners@zyspec.com>
 * @uses  ::   xoops_getmodulehandler poll module handler for class use
 *
 */
class XoopspollLog extends XoopsObject
{
    //  class XoopspollLog extends XoopsObject {
    //    var $db;

    /**
     * Constructor
     * @param null $id
     */
    public function __construct(&$id = null)
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
    public function XoopspollLog(&$id = null)
    {
        $this->__construct($id);
    }

    /**#@+
     * The following method is provided for backward compatibility with cbb/xforum
     * @deprecated since Xoopspoll 1.40, please use XoopspollLogHandler & XoopspollLog
     * @param $pid
     * @return
     */
    public static function deleteByPollId(&$pid)
    {
        $GLOBALS['xoopsLogger']->addDeprecated(__CLASS__ . '::' . __FUNCTION__ . ' is deprecated since Xoopspoll 1.40, please use XoopspollLog and XoopspollLogHandler methods instead.');
        $slogHandler = self::getStaticLogHandler();
        $criteria    = new Criteria('poll_id', (int)($pid), '=');

        return $slogHandler->deleteAll($criteria);
    }

    /**
     * @param $opt_id
     * @return mixed
     */
    public static function deleteByOptionId(&$opt_id)
    {
        $GLOBALS['xoopsLogger']->addDeprecated(__CLASS__ . '::' . __FUNCTION__ . ' is deprecated since Xoopspoll 1.40, please use XoopspollLog and XoopspollLogHandler methods instead.');
        $slogHandler = self::getStaticLogHandler();
        $criteria    = new Criteria('option_id', (int)($opt_id), '=');

        return $slogHandler->deleteAll($criteria);
    }

    /**
     * @param $pid
     * @param $ip
     * @param $uid
     * @return mixed
     */
    public static function hasVoted(&$pid, &$ip, &$uid)
    {
        $GLOBALS['xoopsLogger']->addDeprecated(__CLASS__ . '::' . __FUNCTION__ . ' is deprecated since Xoopspoll 1.40, please use XoopspollLog and XoopspollLogHandler methods instead.');
        $slogHandler = self::getStaticLogHandler();

        return $slogHandler->hasVoted($pid, $ip, $uid);
    }

    /**
     * @return bool
     */
    private function getStaticLogHandler()
    {
        static $log_h;

        $GLOBALS['xoopsLogger']->addDeprecated(__CLASS__ . '::' . __FUNCTION__ . ' is deprecated since Xoopspoll 1.40, please use XoopspollLog and XoopspollLogHandler methods instead.');
        if (!isset($log_h)) {
            $log_h =& xoops_getmodulehandler('log', 'xoopspoll');
        }

        return $log_h;
    }
    /**#@-*/
}

/**
 * Class XoopspollLogHandler
 */
class XoopspollLogHandler extends XoopsPersistableObjectHandler

    //class XoopspollLogHandler extends XoopsPersistableObjectHandler
{
    /**
     * XoopspollLogHandler::__construct()
     *
     * @param mixed $db
     **/
    public function __construct(&$db)
    {
        parent::__construct($db, 'xoopspoll_log', 'XoopspollLog', 'log_id');
    }

    /**
     * XoopspollLogHandler::XoopspollLogHandler()
     *
     * @param mixed $db
     **/
    public function XoopspollLogHandler(&$db)
    {
        $this->__construct($db);
    }

    /**
     *
     * Delete all log entries by Option ID
     * @param  int $option_id
     * @return bool $success
     */
    public function deleteByOptionId($option_id)
    {
        $criteria = new Criteria('option_id', $option_id, '=');
        $success  = ($this->deleteAll($criteria)) ? true : false;

        return $success;
    }

    /**
     *
     * Delete all log entries by Poll ID
     * @uses CriteriaCompo
     * @param  int $pid
     * @return bool $success
     */
    public function deleteByPollId($pid)
    {
        $criteria = new Criteria('poll_id', (int)($pid), '=');
        $success  = ($this->deleteAll($criteria)) ? true : false;

        return $success;
    }

    /**
     *
     * Gets all log entries by Poll ID
     * @uses CriteriaCompo
     * @param  int    $pid
     * @param  string $sortby  sort all results by this field
     * @param  string $orderby sort order (ASC, DESC)
     * @return bool   $success
     */
    public function getAllByPollId($pid, $sortby = 'time', $orderby = 'ASC')
    {
        $ret      = array();
        $criteria = new CriteriaCompo();
        $criteria->add(new Criteria('poll_id', (int)($pid), '='));
        $criteria->setSort($sortby);
        $criteria->setOrder($orderby);
        $ret = & $this->getAll($criteria);

        return $ret;
    }

    /**
     *
     * Get the total number of votes by the Poll ID
     * @uses CriteriaCompo
     * @param  int $pid
     * @return int
     */
    public function getTotalVotesByPollId($pid)
    {
        $criteria = new Criteria('poll_id', (int)($pid), '=');
        $numVotes = $this->getCount($criteria);

        return $numVotes;
    }

    /**
     *
     * Get the total number of voters for a specific Poll
     * @uses CriteriaCompo
     * @param  int $pid
     * @return int
     */
    public function getTotalVotersByPollId($pid)
    {
        $criteria = new CriteriaCompo();
        $criteria->add(new Criteria('poll_id', (int)($pid), '='));
        $criteria->setGroupby('ip');
        $voterGrps = $this->getCount($criteria);
        $numVoters = count($voterGrps);

        return $numVoters;
    }

    /**
     * Get the total number of votes for an option
     * @uses CriteriaCompo
     * @param  int $option_id
     * @return int
     */
    public function getTotalVotesByOptionId($option_id)
    {
        $criteria = new Criteria('option_id', (int)($option_id), '=');
        $votes    = $this->getCount($criteria);

        return $votes;
    }

    /**
     * hasVoted indicates if user (logged in or not) has voted in a poll
     * @uses $_COOKIE
     * @param  int    $pid of the poll the check
     * @param  string $ip  the ip address for this voter
     * @param  int    $uid the XOOPS user id of this voter (0 for anon)
     * @return bool
     */
    public function hasVoted($pid, $ip, $uid = 0)
    {
        $uid   = (int)($uid);
        $pid   = (int)($pid);
        $voted = true;
        xoops_load('pollUtility', 'xoopspoll');
        $voted_polls = XoopspollPollUtility::getVoteCookie();
        //        $voted_polls = array();  //TESTING HACK TO BYPASS COOKIES
        $pollHandler =& xoops_getmodulehandler('poll', 'xoopspoll');
        if ($pollObj = $pollHandler->get($pid)) {
            $pollStarttime = $pollObj->getVar('start_time');
            $criteria      = new CriteriaCompo();
            $criteria->add(new Criteria('poll_id', $pid, '='));
            if ($uid > 0) {
                /**
                 *  {@internal check to see if vote was from before poll was started
                 *  and if so allow voting. This allows voting if poll is restarted
                 *  with new start date or if module is uninstalled and re-installed.}
                 */
                $criteria->add(new Criteria('user_id', $uid, '='));
                $criteria->add(new Criteria('time', (int)($pollStarttime), '>='));
                $vCount = $this->getCount($criteria);
                $voted  = ($vCount > 0) ? true : false;
            } elseif (!empty($ip) && filter_var($ip, FILTER_VALIDATE_IP)) {
                $criteria->add(new Criteria('ip', $ip, '='));
                $criteria->add(new Criteria('time', (int)($pollStarttime), '>='));
                $criteria->add(new Criteria('user_id', 0, '='));
                $vCount = $this->getCount($criteria);
                $voted  = ($vCount > 0) ? true : false;
            } else {
                /* Check cookie to see if someone from this system has voted before */
                if ((array_key_exists($pid, $voted_polls)) && ((int)($voted_polls[$pid]) >= $pollStarttime)) {
                    $criteria = new CriteriaCompo();
                    $criteria->add(new Criteria('poll_id', $pid, '='));
                    $criteria->add(new Criteria('time', (int)($pollStarttime), '>='));
                    $vCount = $this->getCount($criteria);
                    $voted  = ($vCount > 0) ? true : false;
                } else {
                    $voted = false;
                }
            }
        }

        return $voted;
    }
}
