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
 * @copyright:: {@link http://xoops.org/ XOOPS Project}
 * @license  ::   {@link http://www.fsf.org/copyleft/gpl.html GNU public license}
 * @package  ::   xoopspoll
 * @since    ::     1.40
 * @author   ::    zyspec <owners@zyspec.com>
 * @version  ::   $Id: $
 * @param $module
 * @return bool
 */

function xoops_module_pre_uninstall_xoopspoll(&$module)
{
    /* make sure that any polls associated with xoopspoll are cleared from newbb */
    $module_handler = &xoops_gethandler('module');
    $newbbModule    = &$module_handler->getByDirname('newbb');
    $success        = true;
    if (is_object($newbbModule) && $newbbModule->getVar('isactive')) {
        $topic_handler = & xoops_getmodulehandler('topic', 'newbb');
        $criteria      = new Criteria('topic_haspoll', 0, '>');
        $s1            = $topic_handler->updateAll('poll_id', 0, $criteria);  // clear any polls associated with forum topic
        $s2            = $topic_handler->updateAll('topic_haspoll', 0, $criteria); // clear haspoll indicator in forum
        $success       = $s1 && $s2;
    }

    return $success;
}

/**
 * @param $module
 * @return bool
 */
function xoops_module_uninstall_xoopspoll(&$module)
{
    /* clear the voted cookie(s) for the admin user's machine when module is uninstalled */
    xoops_load('pollUtility', 'xoopspoll');
    $success = XoopspollPollUtility::setVoteCookie('', null, (time() - 3600));

    return $success;
}
