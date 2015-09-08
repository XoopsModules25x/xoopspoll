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
 * @copyright:: {@link http://xoops.org/ XOOPS Project}
 * @license  ::   {@link http://www.fsf.org/copyleft/gpl.html GNU public license}
 * @package  ::   xoopspoll
 * @since    ::     1.40
 * @author   ::    zyspec <owners@zyspec.com>
 * @version  ::   $Id: $
 */
// defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');
xoops_load('pollUtility', 'xoopspoll');

/**
 * @param $db
 * @param $fromTable
 * @param $toTable
 * @return bool
 */
function xoopspollChangeTableName(&$db, $fromTable, $toTable)
{
    $fromTable = addslashes($fromTable);
    $toTable   = addslashes($toTable);
    /*
        $fromThisTable = $db->prefix("{$fromTable}");
        $toThisTable = $db->prefix("{$toTable}");
    */
    $success = false;
    if (XoopspollPollUtility::dbTableExists($db, $fromTable) && !XoopspollPollUtility::dbTableExists($db, $toTable)) {
        $sql     = sprintf('ALTER TABLE ' . $db->prefix("{$fromTable}") . ' RENAME ' . $db->prefix('{$toTable}'));
        $success = $db->queryF($sql);
        if (!$success) {
            $modHandler      =& xoops_getmodulehandler('module');
            $xoopspollModule =& $modHandler->getByDirname('xoopspoll');
            $xoopspollModule->setErrors(sprintf(_AM_XOOPSPOLL_UPGRADE_FAILED, $fromTable));
        }
    }

    return $success;
}

/**
 * @param $module
 * @param $prev_version
 * @return bool
 */
function xoops_module_update_xoopspoll(&$module, &$prev_version)
{
    // referer check
    $success = false;
    $ref     = xoops_getenv('HTTP_REFERER');
    if (('' === $ref) || 0 === mb_strpos($ref, $GLOBALS['xoops']->url('modules/system/admin.php'))) {
        /* module specific part */
        require_once $GLOBALS['xoops']->path('modules/xoopspoll/include/oninstall.inc.php');

        $installedVersion = (int)($prev_version);
        xoops_loadLanguage('admin', 'xoopspoll');
        $db      =& XoopsDatabaseFactory::getDatabaseConnection();
        $success = true;
        if ($installedVersion < 140) {
            /* add column for poll anonymous which was created in versions prior
             * to 1.40 of xoopspoll but not automatically created
             */
            $result    = $db->queryF('SHOW COLUMNS FROM ' . $db->prefix('xoopspoll_desc') . " LIKE 'anonymous'");
            $foundAnon = $db->getRowsNum($result);
            if (empty($foundAnon)) {
                // column doesn't exist, so try and add it
                $success = $db->queryF('ALTER TABLE ' . $db->prefix('xoopspoll_desc') . ' ADD anonymous TINYINT( 1 ) DEFAULT 0 NOT null AFTER multiple');
                if (!$success) {
                    $module->setErrors(_AM_XOOPSPOLL_ERROR_COLUMN . 'anonymous');
                }
            }
            /* change description to TINYTEXT */
            if ($success) {
                $success = $db->queryF('ALTER TABLE ' . $db->prefix('xoopspoll_desc') . ' MODIFY description TINYTEXT NOT NULL');
                if (!$success) {
                    $module->setErrors(_AM_XOOPSPOLL_ERROR_COLUMN . 'description');
                }
            }
            /* */
            if ($success) {
                $success = $db->queryF('ALTER TABLE ' . $db->prefix('xoopspoll_desc') . " ADD multilimit TINYINT( 63 ) UNSIGNED DEFAULT '0' NOT null AFTER multiple");
                if (!$success) {
                    $module->setErrors(_AM_XOOPSPOLL_ERROR_COLUMN . 'multilimit');
                }
            }
            if ($success) {
                $success = $db->queryF('ALTER TABLE ' . $db->prefix('xoopspoll_desc') . " ADD mail_voter TINYINT( 1 ) UNSIGNED DEFAULT '0' NOT null AFTER mail_status");
                if (!$success) {
                    $module->setErrors(_AM_XOOPSPOLL_ERROR_COLUMN . 'mail_voter');
                }
            }
            if ($success) {
                $result   = $db->queryF('SHOW COLUMNS FROM ' . $db->prefix('xoopspoll_desc') . " LIKE 'visibility'");
                $foundCol = $db->getRowsNum($result);
                if (empty($foundCol)) {
                    // column doesn't exist, so try and add it
                    $success = $db->queryF('ALTER TABLE ' . $db->prefix('xoopspoll_desc') . " ADD visibility INT( 3 ) DEFAULT '0' NOT null AFTER display");
                    if (!$success) {
                        $module->setErrors(_AM_XOOPSPOLL_ERROR_COLUMN . 'visibility');
                    }
                }
            }
        }

        if ($success) {
            /* now reverse table names changes from 1.40 Beta  */
            $s1      = xoopspollChangeTableName($db, 'mod_xoopspoll_option', 'xoopspoll_option');
            $s2      = xoopspollChangeTableName($db, 'mod_xoopspoll_desc', 'xoopspoll_desc');
            $s3      = xoopspollChangeTableName($db, 'mod_xoopspoll_log', 'xoopspoll_log');
            $success = ($s1 && $s2 && $s3) ? true : false;
        }
    }

    return $success;
}
