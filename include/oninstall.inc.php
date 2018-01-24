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
 * Xoopspoll install functions.php
 *
 * @copyright:: {@link https://xoops.org/ XOOPS Project}
 * @license  ::   {@link http://www.fsf.org/copyleft/gpl.html GNU public license}
 * @package  ::   xoopspoll
 * @since    ::     1.40
 * @author   ::    zyspec <owners@zyspec.com>
 * @param  \XoopsModule $module
 * @return bool
 */

function xoops_module_pre_install_xoopspoll(\XoopsModule $module)
{
    //    $db = \XoopsDatabaseFactory::getDatabaseConnection();
    $retVal = true;

    return $retVal;
}

/**
 * @param  \XoopsModule $module
 * @return bool
 */
function xoops_module_install_xoopspoll(\XoopsModule $module)
{
    $retVal = true;

    return $retVal;
}
