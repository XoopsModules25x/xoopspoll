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
 *  userlog module
 *
 * @copyright       XOOPS Project (https://xoops.org)
 * @license         GNU GPL 2 (https://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 * @since           4.31
 * @author          irmtfan (irmtfan@yahoo.com)
 * @author          XOOPS Project <www.xoops.org> <www.xoops.ir>
 */
// defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');

class Userlog extends \Userlog_Module_Plugin_Abstract implements \UserlogPluginInterface
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
     * @return array|bool $item["item_name"] name of the item, $item["item_id"] id of the item
     */
    public function item(string $subscribe_from)
    {
        xoops_load('XoopsRequest');
        $poll_id = XoopsRequest::getInt('poll_id', 0);
        switch ($subscribe_from) {
            case 'index.php':
            case 'pollresults.php':
                return ['item_name' => 'poll_id', 'item_id' => $poll_id];
        }

        return false;
    }
}
