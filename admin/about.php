<?php declare(strict_types=1);
/*
 You may not change or alter any portion of this comment or credits
 of supporting developers from this source code or any supporting source code
 which is considered copyrighted (c) material of the original comment or credit authors.
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*/

/**
 * XOOPS Poll module
 * Display module 'About' page in administration interface
 *
 * @copyright ::  {@link https://xoops.org/ XOOPS Project}
 * @license   ::    {@link https://www.gnu.org/licenses/gpl-2.0.html GNU GPL 2.0 or later}
 * @subpackage:: admin
 * @since     ::      1.40
 * @author    ::     Mage, Mamba
 *
 * @uses      Xmf\Module\Admin
 * @uses      Xmf\Module\Admin::displayNavigation to set the display page
 * @uses      Xmf\Module\Admin::displayAbout to display the page passing PAYPAL key to method
 */

use Xmf\Module\Admin;

require_once __DIR__ . '/admin_header.php';
xoops_cp_header();

/** @var Admin $adminObject */
$adminObject->displayNavigation(basename(__FILE__));
$adminObject::setPaypal('xoopsfoundation@gmail.com');
$adminObject->displayAbout(false);

require_once __DIR__ . '/admin_footer.php';
