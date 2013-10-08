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
 * XoopsPoll module
 *
 * @copyright   The XOOPS Project http://sourceforge.net/projects/xoops/
 * @license     http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author      Michael Beck (aka Mamba)
 * @version     $Id$
 */

function xoops_module_update_xoopspoll(&$module, $oldversion = null)
{
    if ($oldversion < 130) {
        global $xoopsDB;

        $tbl = $xoopsDB->prefix('xoopspoll_desc');

        $sql
            = <<<__sql__
ALTER TABLE `{$tbl}` ADD `anonymous` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `multiple`;
__sql__;


        if (!$result = $xoopsDB->queryF($sql)) {
            return false;
        } else {
            return true;
        }
    }
}