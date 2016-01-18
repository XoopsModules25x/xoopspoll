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
 *  userlog module
 *
 * @copyright       XOOPS Project (http://xoops.org)
 * @license         GNU GPL 2 (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 * @package         newbb class plugin
 * @since           4.31
 * @author          irmtfan (irmtfan@yahoo.com)
 * @author          XOOPS Project <www.xoops.org> <www.xoops.ir>
 * @version         $Id: userlog.php 4.31 2013/05/08 16:25:04Z irmtfan $
 */

// defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');

class XoopspollUserlogPlugin extends Userlog_Module_Plugin_Abstract implements UserlogPluginInterface
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
        xoops_load('XoopsRequest');
        $poll_id = XoopsRequest::getInt('poll_id', 0);
        switch ($subscribe_from) {
            case 'index.php':
            case 'pollresults.php':
                return array('item_name' => 'poll_id', 'item_id' => $poll_id);
                break;
        }

        return false;
    }
}
