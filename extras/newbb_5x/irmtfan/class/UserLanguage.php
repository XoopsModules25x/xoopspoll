<?php namespace XoopsModules\Newbb;

/**
 * User Language Class (moved from 'main' language file)
 * @package   ::    newbb
 * @subpackage:: class
 */

use XoopsModules\Newbb;

//require_once $GLOBALS['xoops']->path('modules/newbb/class/user.php');

/**
 *
 * Allows setting for user information
 * If you have a customized userbar, define it here.
 *
 */
class UserLanguage extends Newbb\User
{

    /**
     * UserLanguage constructor.
     * @param $user
     */
    public function __construct($user)
    {
        parent::__construct($user);
    }

    /**
     * @return array|null
     */
    public function getUserbar()
    {
        global $xoopsModuleConfig, $xoopsUser, $isadmin;
        if (empty($GLOBALS['xoopsModuleConfig']['userbar_enabled'])) {
            return null;
        }
        $user      = $this->user;
        $userbar   = [];
        $userbar[] = [
            'link' => $GLOBALS['xoops']->url('userinfo.php?uid=' . $user->getVar('uid')),
            'name' => PROFILE
        ];
        if (is_object($xoopsUser)) {
            $userbar[] = [
                'link' => "javascript:void openWithSelfMain('" . XOOPS_URL . '/pmlite.php?send2=1&amp;to_userid=' . $user->getVar('uid') . "','pmlite', 450, 380);",
                'name' => _MD_PM
            ];
        }
        if ($user->getVar('user_viewemail') || $isadmin) {
            $userbar[] = [
                'link' => "javascript:void window.open('mailto:" . $user->getVar('email') . "','new');",
                'name' => _MD_EMAIL
            ];
        }
        if ($user->getVar('url')) {
            $userbar[] = [
                'link' => "javascript:void window.open('" . $user->getVar('url') . "','new');",
                'name' => _MD_WWW
            ];
        }
        if ($user->getVar('user_icq')) {
            $userbar[] = [
                'link' => "javascript:void window.open('http://wwp.icq.com/scripts/search.dll?to=" . $user->getVar('user_icq') . "','new');",
                'name' => _MD_ICQ
            ];
        }
        if ($user->getVar('user_aim')) {
            $userbar[] = [
                'link' => "javascript:void window.open('aim:goim?screenname=" . $user->getVar('user_aim') . '&amp;message=Hi+' . $user->getVar('user_aim') . '+Are+you+there?' . "','new');",
                'name' => _MD_AIM
            ];
        }
        if ($user->getVar('user_yim')) {
            $userbar[] = [
                'link' => "javascript:void window.open('http://edit.yahoo.com/config/send_webmesg?.target=" . $user->getVar('user_yim') . '&.src=pg' . "','new');",
                'name' => _MD_YIM
            ];
        }
        if ($user->getVar('user_msnm')) {
            $userbar[] = [
                'link' => "javascript:void window.open('http://members.msn.com?mem=" . $user->getVar('user_msnm') . "','new');",
                'name' => _MD_MSNM
            ];
        }

        return $userbar;
    }
}
