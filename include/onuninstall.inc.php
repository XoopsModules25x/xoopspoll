<?php
/*
 You may not change or alter any portion of this comment or credits
 of supporting developers from this source code or any supporting source code
 which is considered copyrighted (c) material of the original comment or credit authors.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*/

/**
 * Xoopspoll uninstall functions.php
 *
 * @copyright:: {@link https://xoops.org/ XOOPS Project}
 * @license  ::   {@link http://www.fsf.org/copyleft/gpl.html GNU public license}
 * @package  ::   xoopspoll
 * @since    ::     1.40
 * @author   ::    zyspec <owners@zyspec.com>
 * @param  XoopsModule $module
 * @return bool
 */

use XoopsModules\Xoopspoll;
use XoopsModules\Newbb;

function xoops_module_pre_uninstall_xoopspoll(\XoopsModule $module)
{
    /* make sure that any polls associated with xoopspoll are cleared from newbb */
    /** @var XoopsModuleHandler $moduleHandler */
    $moduleHandler = xoops_getHandler('module');
    $newbbModule   = $moduleHandler->getByDirname('newbb');
    $success       = true;
    if (is_object($newbbModule) && $newbbModule->getVar('isactive')) {
        /** @var NewbbTopicHandler $topicHandler */
        $topicHandler = Newbb\Helper::getInstance()->getHandler('Topic');
        $criteria     = new \Criteria('topic_haspoll', 0, '>');
        $s1           = $topicHandler->updateAll('poll_id', 0, $criteria);  // clear any polls associated with forum topic
        $s2           = $topicHandler->updateAll('topic_haspoll', 0, $criteria); // clear haspoll indicator in forum
        $success      = $s1 && $s2;
    }

    return $success;
}

/**
 * @param  XoopsModule $module
 * @return bool
 */
function xoops_module_uninstall_xoopspoll(\XoopsModule $module)
{
    /* clear the voted cookie(s) for the admin user's machine when module is uninstalled */
    xoops_load('pollUtility', 'xoopspoll');
    $success = Xoopspoll\Utility::setVoteCookie('', null, time() - 3600);

    return $success;
}
