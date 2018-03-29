<?php namespace XoopsModules\Xoopspoll;

/*
 You may not change or alter any portion of this comment or credits
 of supporting developers from this source code or any supporting source code
 which is considered copyrighted (c) material of the original comment or credit authors.
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*/

/**
 * XoopsPoll Option Class (xoopspolloption.php)
 * Description: XoopsPollOption thunking class for backward compatibility.  This class should not be used
 * except by legacy modules (for example CBB(newbb) or xForum.
 *
 * @copyright ::  {@link https://xoops.org/ XOOPS Project}
 * @license   ::    {@link http://www.fsf.org/copyleft/gpl.html GNU public license}
 * @package   ::    xoopspoll
 * @subpackage:: class
 * @since     ::         1.40
 * @author    ::     zyspec <owners@zyspec.com>
 */

$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
$GLOBALS['xoopsLogger']->addDeprecated('Usage of ./xoopspoll/class/xoopspolloption.php file is deprecated since Xoopspoll 1.40, please use Option and OptionHandler classes instead.' . ". Called from {$trace[0]['file']}line {$trace[0]['line']}");
xoops_load('log', 'xoopspoll');
