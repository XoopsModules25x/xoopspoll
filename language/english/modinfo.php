<?php
/**
 * Module Info Language Definitions
 *
 * @package xoopspoll
 */

/**
 * The name of this module
 */
define('_MI_XOOPSPOLL_NAME', 'XOOPS Polls');

/**
 * A brief description of this module
 */
define('_MI_XOOPSPOLL_DESC', 'The XOOPS Poll module can be used to display interactive survey forms on the site. ');

/**#@+
 * Name and description of block for this module
 */
define('_MI_XOOPSPOLL_BNAME1', 'Polls');
define('_MI_XOOPSPOLL_BNAME1DSC', 'Shows unlimited number of polls/surveys');
define('_MI_XOOPSPOLL_BNAME1A', 'Polls using HTML select');
define('_MI_XOOPSPOLL_BNAME1ADSC', 'Show unlimited number of polls/surveys using HTML select');
define('_MI_XOOPSPOLL_BNAME2', 'Single Poll');
define('_MI_XOOPSPOLL_BNAME2DSC', 'Shows a single poll');
/**#@-*/

/**#@+
 * Module properties
 */
//define('_MI_XOOPSPOLL_LIMITBYIP', 'Prevent voting twice by checking IP address.');
//define('_MI_XOOPSPOLL_LIMITBYIPDSC', 'The user IP address is checked to prevent voting twice from the same IP. Please notice that if there are two different users using the same public IP, the last one will not be able to vote.');
//define('_MI_XOOPSPOLL_LIMITBYUID', 'Prevent voting twice from the same user');
//define('_MI_XOOPSPOLL_LIMITBYUIDDSC', 'Check the logged in user ID to prevent them from voting more than once.');
define('_MI_XOOPSPOLL_LOOKUPHOST', 'Display host name instead of IP address in the Administration Log');
define('_MI_XOOPSPOLL_LOOKUPHOSTDSC', 'List host names instead of IP addresses in viewing poll log. Since nslookup is used, It might take longer to show names.');
define('_MI_XOOPSPOLL_DISPVOTE', 'Display number of poll votes and voters to users.');
define('_MI_XOOPSPOLL_DISPVOTEDSC', 'This will show/hide the total number of votes and voters in a poll to users in pages and blocks. Numbers are always shown in Admin panel.');
//define('_MI_XOOPSPOLL_CHOOSEEDITOR', 'Choose text editor to use:');
//define('_MI_XOOPSPOLL_CHOOSEEDITORDSC', 'This is the editor to be used when entering descriptions.');
define('_MI_XOOPSPOLL_HIDEFORUM_POLLS', 'Hide polls created in a forum from polls module and blocks');
define('_MI_XOOPSPOLL_HIDEFORUM_POLLSDSC', 'If Yes, polls created from a forum (newbb) are hidden in the polls module.<br>Set to Yes if forum module is not installed.');
/**#@-*/

/**#@+
 * Template description
 */
define('_MI_XOOPSPOLL_INDEX_DSC', 'Module index template');
define('_MI_XOOPSPOLL_VIEW_DSC', 'Display poll template');
define('_MI_XOOPSPOLL_RESULTS_DSC', 'Display results template');
define('_MI_XOOPSPOLL_RESULTS_REND_DSC', 'Display results rendering template');
define('_MI_XOOPSPOLL_ADMIN_INDEX_DSC', 'Administration template to display module information');
define('_MI_XOOPSPOLL_ADMIN_LIST_DSC', 'Administration template to list link information');
define('_MI_XOOPSPOLL_ADMIN_UTIL_DSC', 'Administration template for module utilities');
//define('_MI_XOOPSPOLL_HELP_DSC', 'Template to display module help page');
/**#@-*/

/**#@+
 * index.php definition
 */
define('_MI_XOOPSPOLL_HOME', 'Home');
define('_MI_XOOPSPOLL_HOMEDSC', 'Module Administration Home');
define('_MI_XOOPSPOLL_ADMENU1', 'Polls');
define('_MI_XOOPSPOLL_ADMENU1DSC', 'List/Edit/Delete Polls Administration');
define('_MI_XOOPSPOLL_ADMENU2', 'Utilities');
define('_MI_XOOPSPOLL_ADMENU2DSC', 'Module Helpers');
define('_MI_XOOPSPOLL_ADABOUT', 'About');
define('_MI_XOOPSPOLL_ADABOUTDSC', 'Learn more about the XOOPS Poll Module');
//define('_MI_XOOPSPOLL_ADMIN_HELP', 'Help');
/**#@-*/
