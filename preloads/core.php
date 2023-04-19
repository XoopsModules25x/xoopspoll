<?php declare(strict_types=1);
/*
                XOOPS - PHP Content Management System
                    Copyright (c) 2012 XOOPS.org
                       <https://xoops.org>
  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  You may not change or alter any portion of this comment or credits
  of supporting developers from this source code or any supporting
  source code which is considered copyrighted (c) material of the
  original comment or credit authors.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA
*/

/**
 *  XOOPS Poll Module results mailer preload
 *
 * @copyright ::  {@link https://xoops.org XOOPS Project}
 * @license   ::    {@link https://www.gnu.org/licenses/gpl-2.0.html GNU GPL 2.0 or later}
 * @subpackage:: preloads
 * @since     ::      1.4
 * @author    ::     zyspec <zyspec@yahoo.com>
 **/

use XoopsModules\Xoopspoll\Helper;

/**
 * Class XoopspollCorePreload
 */
class XoopspollCorePreload extends \XoopsPreloadItem
{
    // to add PSR-4 autoloader
    /**
     * @param $args
     */
    public static function eventCoreIncludeCommonEnd($args): void
    {
        require __DIR__ . '/autoloader.php';
    }

    /**
     * plugin class for Xoops preload for index page start
     * @param $args
     */
    public static function eventCoreIndexStart($args): void
    {
        // check once per user session if expired poll email has been sent
        if (empty($_SESSION['pollChecked'])) {
            $pollHandler = Helper::getInstance()->getHandler('Poll');
            $pollHandler->mailResults();  //send the results of any polls that have ended
            unset($pollHandler);
            $_SESSION['pollChecked'] = 1;
        }
    }
}
