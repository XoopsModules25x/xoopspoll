<?php namespace XoopsModules\Xoopspoll;

/*
 Description: Poll Class Definition

 You may not change or alter any portion of this comment or credits
 of supporting developers from this source code or any supporting source code
 which is considered copyrighted (c) material of the original comment or credit authors.
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*/
/**
 *  XoopsPoll Utility Class Elements
 *
 * @copyright ::  {@link https://xoops.org/ XOOPS Project}
 * @license   ::    {@link http://www.fsf.org/copyleft/gpl.html GNU public license}
 * @package   ::    xoopspoll
 * @subpackage:: class
 * @since     ::      1.40
 * @access::     public
 */

use XoopsModules\Xoopspoll;
use XoopsModules\Xoopspoll\Constants;

// defined('XOOPS_ROOT_PATH') || die('Restricted access');

class PollUtility
{
    /**
     *
     * gets the name of the host located at a specific ip address
     * @param  string $ipAddr the ip address of the host
     * @return string host name
     */
    public static function getHostByAddrWithCache(&$ipAddr)
    {
        static $ipArray = [];
        $retVal  =& $ipAddr;
        $options = ['flags' => FILTER_FLAG_NO_PRIV_RANGE, FILTER_FLAG_NO_RES_RANGE];
        if (filter_var($ipAddr, FILTER_VALIDATE_IP, $options)) {
            if (array_key_exists($ipAddr, $ipArray)) {
                $retVal = $ipArray[$ipAddr];
            } else {
                $hostAddr = gethostbyaddr($ipAddr);
                if ($hostAddr === $ipAddr) {
                    $retVal =& $ipAddr;
                } else {
                    $ipArray[$ipAddr] = htmlspecialchars($hostAddr, ENT_QUOTES | ENT_HTML5);
                    $retVal           = $ipArray[$ipAddr];
                }
            }
        }

        return $retVal;
    }

    /**
     * Returns the global comment mode for this module
     * @static
     */
    public static function commentMode()
    {
        static $mConfig;
        if (null === $mConfig) {
            $mHandler = xoops_getHandler('module');
            $mod      = $mHandler->getByDirname('xoopspoll');
            $cHandler = xoops_getHandler('config');
            $mConfig  = $cHandler->getConfigsByCat(0, $mod->getVar('mid'));
        }

        return $mConfig['com_rule'];
    }

    /**
     *
     * Creates a visibility array from module default values
     * @return array visibility options available for a poll
     */
    public static function getVisibilityArray()
    {
        /**
         * {@internal Do NOT add/delete from $visOptions after the module has been installed}
         */
        static $visOptions = [];
        if (empty($visOptions)) {
            xoops_loadLanguage('main', 'xoopspoll');
            xoops_load('constants', 'xoopspoll');
            $visOptions = [
                Constants::HIDE_NEVER  => _MD_XOOPSPOLL_HIDE_NEVER,
                Constants::HIDE_END    => _MD_XOOPSPOLL_HIDE_END,
                Constants::HIDE_VOTED  => _MD_XOOPSPOLL_HIDE_VOTED,
                Constants::HIDE_ALWAYS => _MD_XOOPSPOLL_HIDE_ALWAYS
            ];
        }

        return $visOptions;
    }

    /**
     * Retrieves vote cookie from client system
     * The cookie name is based on the module directory. If module is
     * installed in default location then cookie name is 'voted_polls'
     * for backward compatibility. Otherwise cookie is named
     * '<dirname>_voted_polls' to allow for module to be cloned using
     * smartClone module.
     * @param  string $cookieBaseName
     * @return array  contains cookie for polls, empty array if not found
     */
    public static function getVoteCookie($cookieBaseName = null)
    {
//        $cookieBaseName = null === $cookieBaseName ? 'voted_polls': $cookieBaseName;
        if (null === $cookieBaseName) {
            $cookieBaseName ='voted_polls';
        }
        $pollDir = basename(dirname(__DIR__));
        if ('xoopspoll' === $pollDir) {
            $pollCookie = !empty($_COOKIE[$cookieBaseName]) ? $_COOKIE[$cookieBaseName] : [];
        } else {
            $pollCookie = !empty($_COOKIE["{$pollDir}_{$cookieBaseName}"]) ? $_COOKIE["{$pollDir}_{$cookieBaseName}"] : [];
        }

        return $pollCookie;
    }

    /**
     * Sets vote cookie on client system
     * The cookie name is based on the module directory. If module is
     * installed in default location then cookie name is 'voted_polls'
     * for backward compatibility. Otherwise cookie is named
     * '<dirname>_voted_polls' to allow for module to be cloned using
     * smartClone module.
     * @param  int|string   $index          array index to set in cookie
     * @param  unknown_type $value          data to store in cookie
     * @param  int          $expires        time when cookie expires
     * @param  string       $cookieBaseName name of cookie (without directory prefix)
     * @return bool         success in setting cookie
     */
    public static function setVoteCookie($index, $value, $expires = 0, $cookieBaseName = 'voted_polls')
    {
        $pollDir = basename(dirname(__DIR__));
        $success = false;
        // do a little sanity check on $index and $cookieBaseName
        if (!is_bool($index) && !empty($cookieBaseName)) {
            if ('xoopspoll' === $pollDir) {
                $cookieName = $cookieBaseName;
            } else {
                $cookieName = $pollDir . '_' . $cookieBaseName;
            }
            $iVal = (string)$index;
            if (!empty($iVal)) {
                if ($success = setcookie($cookieName[$index], $value, (int)$expires)) {
                    $clientCookie = self::getVoteCookie();
                    if (!array_key_exists($index, $clientCookie) || $clientCookie[$index] !== $value) {
                        $success = false;
                    }
                }
            } else {  //clear the cookie
                if ($success = setcookie($cookieName, '', (int)$expires)) {
                    $clientCookie = self::getVoteCookie();
                    if (!empty($clientCookie)) {
                        $success = false;
                    }
                }
            }
        }

        return $success;
    }

    /**
     * Sets vote cookie on client system
     * The cookie name is based on the module directory. If module is
     * installed in default location then cookie name is 'voted_polls'
     * for backward compatibility. Otherwise cookie is named
     * '<dirname>_voted_polls' to allow for module to be cloned using
     * smartClone module.
     * @param XoopsDatabase $db
     * @param               $tablename
     * @return bool success in setting cookie
     * @internal param int|string $index array index to set in cookie
     * @internal param unknown_type $value data to store in cookie
     * @internal param int $expires time when cookie expires
     * @internal param string $cookieBaseName name of cookie (without directory prefix)
     */
    public static function dbTableExists(\XoopsDatabase $db, $tablename)
    {
        $tablename = addslashes($tablename);
        $mytable   = $db->prefix((string)($tablename));
        $result    = $db->queryF("SHOW TABLES LIKE '{$mytable}'");
        $found     = $db->getRowsNum($result);

        return empty($found) ? false : true;
    }
}
