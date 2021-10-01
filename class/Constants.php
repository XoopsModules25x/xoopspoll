<?php

namespace XoopsModules\Xoopspoll;

/*
               XOOPS - PHP Content Management System
                   Copyright (c) 2000-2020 XOOPS.org
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
 * XoopsPoll module
 *
 * Class to define XOOPS Poll constant values. These constants are
 * used to make the code easier to read and to keep values in central
 * location if they need to be changed.  These should not normally need
 * to be modified. If they are to be modified it is recommended to change
 * the value(s) before module installation. Additionally, the module may not
 * work correctly if trying to upgrade if these values have been changed.
 *
 * @copyright::  {@link https://xoops.org/ XOOPS Project}
 * @license  ::  {@link https://www.gnu.org/licenses/gpl-2.0.html GNU Public License}
 * @author   ::  zyspec <zyspec@yahoo.com>
 * @package  ::  xoopspoll
 * @since    ::  1.40
 **/


interface Constants
{
    /**#@+
     * Constant definition
     */
    /**
     *  indicates a poll has not been emailed
     */
    public const POLL_NOT_MAILED = 0;
    /**
     *  indicates a poll has been emailed
     */
    public const POLL_MAILED = 1;
    /**
     *  indicates a poll should nto be emailed to voter
     */
    public const NOT_MAIL_POLL_TO_VOTER = 0;
    /**
     *  indicates poll should be emailed to voter
     */
    public const MAIL_POLL_TO_VOTER = 1;
    /**
     *  indicates admin should not be notified
     */
    public const NOTIFICATION_DISABLED = 0;
    /**
     *  indicates admin should be notified
     */
    public const NOTIFICATION_ENABLED = 1;
    /**
     *  do not reset poll results
     */
    public const DO_NOT_RESET_RESULTS = 0;
    /**
     *  reset poll results
     */
    public const RESET_RESULTS = 1;
    /**
     * default number of elements to show on a page
     */
    public const DEFAULT_POLL_PAGE_LIMIT = 30;
    /**
     * default amount of time for a poll to be active in seconds
     */
    public const DEFAULT_POLL_DURATION = 864000;
    /**
     * no delay XOOPS redirect delay (in seconds)
     */
    public const REDIRECT_DELAY_NONE = 0;
    /**
     * short XOOPS redirect delay (in seconds)
     */
    public const REDIRECT_DELAY_SHORT = 1;
    /**
     * medium XOOPS redirect delay (in seconds)
     */
    public const REDIRECT_DELAY_MEDIUM = 3;
    /**
     * long XOOPS redirect delay (in seconds)
     */
    public const REDIRECT_DELAY_LONG = 7;
    /**
     * additional blank poll options to be added in form
     */
    public const NUM_ADDTL_OPTIONS = 5;
    /**
     * poll results visibility option
     * {@internal Do NOT change these after module is installed}
     */
    public const HIDE_NEVER = 0;
    /**
     * poll results visibility option
     * {@internal Do NOT change these after module is installed}
     */
    public const HIDE_END = 1;
    /**
     * poll results visibility option
     * {@internal Do NOT change these after module is installed}
     */
    public const HIDE_VOTED = 2;
    /**
     * poll results visibility option
     * {@internal Do NOT change these after module is installed}
     */
    public const HIDE_ALWAYS = 3;
    /**
     * value indicates poll is displayed in block
     */
    public const DISPLAY_POLL_IN_BLOCK = 1;
    /**
     * value indicates poll is NOT displayed in block
     */
    public const DO_NOT_DISPLAY_POLL_IN_BLOCK = 0;
    /**
     * value indicates multiple selections are available in poll
     */
    public const MULTIPLE_SELECT_POLL = 1;
    /**
     * value indicates multiple selections are not available in poll
     */
    public const NOT_MULTIPLE_SELECT_POLL = 0;
    /**
     * value indicates unlimited selections allowed in multiple selection polls
     */
    public const MULTIPLE_SELECT_LIMITLESS = 0;
    /**
     * value indicates poll options are shown as select box
     */
    public const POLL_OPTIONS_SELECT = 0;
    /**
     * value indicates poll options are shown as list
     */
    public const POLL_OPTIONS_LIST = 1;
    /**
     * anonymous voting in poll allowed
     */
    public const ANONYMOUS_VOTING_ALLOWED = 1;
    /**
     * anonymous voting in poll not allowed
     */
    public const ANONYMOUS_VOTING_DISALLOWED = 0;
    /**
     * default poll weight for display order
     */
    public const DEFAULT_WEIGHT = 0;
    /**
     * do not look up host
     */
    public const DO_NOT_LOOK_UP_HOST = 0;
    /**
     * look up host
     */
    public const LOOK_UP_HOST = 1;
    /**
     * confirm not ok to take action
     */
    public const CONFIRM_NOT_OK = 0;
    /**
     * confirm ok to take action
     */
    public const CONFIRM_OK = 1;
    /**#@-*/
}
