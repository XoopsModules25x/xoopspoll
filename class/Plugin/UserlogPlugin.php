<?php namespace XoopsModules\Xoopspoll\Plugin;

/*
 You may not change or alter any portion of this comment or credits
 of supporting developers from this source code or any supporting source code
 which is considered copyrighted (c) material of the original comment or credit authors.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*/

/**
 *  userlog module
 *
 * @copyright       XOOPS Project (https://xoops.org)
 * @license         GNU GPL 2 (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 * @package         newbb class plugin
 * @since           4.31
 * @author          irmtfan (irmtfan@yahoo.com)
 * @author          XOOPS Project <www.xoops.org> <www.xoops.ir>
 */

use Xmf\Request;
use XoopsModules\Userlog;

// defined('XOOPS_ROOT_PATH') || die('Restricted access');

/**
 * Class UserlogPlugin
 * @package XoopsModules\Xoopspoll\Plugin
 */
class UserlogPlugin extends Userlog\Plugin\PluginAbstract implements Userlog\Plugin\PluginInterface
{
    /**
     * @param string $subscribe_from Name of the script
     *
     * 'name' => 'thread';
     * 'title' => _MI_NEWBB_THREAD_NOTIFY;
     * 'description' => _MI_NEWBB_THREAD_NOTIFYDSC;
     * 'subscribe_from' => 'viewtopic.php';
     * 'item_name' => 'topic_id';
     * 'allow_bookmark' => 1;
     *
     * @return array $item["item_name"] name of the item, $item["item_id"] id of the item
     */
    public function item($subscribe_from)
    {
        $poll_id = Request::getInt('poll_id', 0);
        switch ($subscribe_from) {
            case 'index.php':
            case 'pollresults.php':
                return ['item_name' => 'poll_id', 'item_id' => $poll_id];
                break;
        }

        return false;
    }
}
