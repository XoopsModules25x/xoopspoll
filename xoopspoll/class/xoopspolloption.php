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
 * XoopsPoll Option Class (xoopspolloption.php)
 * Description: XoopsPollOption thunking class for backward compatibility.  This class should not be used
 * except by legacy modules (for example CBB(newbb) or xForum.
 *
 * @copyright ::  {@link http://xoops.org/ XOOPS Project}
 * @license   ::    {@link http://www.fsf.org/copyleft/gpl.html GNU public license}
 * @package   ::    xoopspoll
 * @subpackage:: class
 * @since     ::         1.40
 * @author    ::     zyspec <owners@zyspec.com>
 * @version   ::    $Id: $
 */

$GLOBALS['xoopsLogger']->addDeprecated('Usage of ./xoopspoll/class/xoopspolloption.php file is deprecated since Xoopspoll 1.40, please use XoopspollOption and XoopspollOptionHandler classes instead.');
xoops_load('log', 'xoopspoll');
