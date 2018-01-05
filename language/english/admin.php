<?php
/**
 * XOOPS Poll Administration Language Definitions
 *
 * @package   ::    xoopspoll
 * @subpackage:: defines
 */
define('_AM_XOOPSPOLL_DBUPDATED', 'Database Updated Successfully!');
define('_AM_XOOPSPOLL_POLLCONF', 'Polls Configuration');
define('_AM_XOOPSPOLL_POLLSLIST', 'Polls List');
define('_AM_XOOPSPOLL_AUTHOR', 'Author of this poll');
define('_AM_XOOPSPOLL_DISPLAYBLOCK', 'Display in block?');
define('_AM_XOOPSPOLL_POLLQUESTION', 'Poll Question');
define('_AM_XOOPSPOLL_VOTERS', 'Total voters');
define('_AM_XOOPSPOLL_VOTES', 'Total votes');
define('_AM_XOOPSPOLL_EXPIRATION', 'Expiration');
define('_AM_XOOPSPOLL_EXPIRED', 'Expired');
define('_AM_XOOPSPOLL_VIEWLOG', 'View log');
define('_AM_XOOPSPOLL_CREATENEWPOLL', 'Create new poll');
define('_AM_XOOPSPOLL_POLLDESC', 'Poll Description');
define('_AM_XOOPSPOLL_DISPLAYORDER', 'Display order');
define('_AM_XOOPSPOLL_ALLOWMULTI', 'Allow multiple selection?');
define('_AM_XOOPSPOLL_ALLOWANONYMOUS', 'Allow anonymous voting?');
define('_AM_XOOPSPOLL_NOTIFY', 'Notify the poll author when expired?');
define('_AM_XOOPSPOLL_POLLOPTIONS', 'Options');
define('_AM_XOOPSPOLL_EDITPOLL', 'Edit poll');
define('_AM_XOOPSPOLL_FORMAT', 'Format: yyyy-mm-dd hh:mm');
define('_AM_XOOPSPOLL_CURRENTTIME', 'Current time is %s');
define('_AM_XOOPSPOLL_EXPIREDAT', 'Expired at %s');
define('_AM_XOOPSPOLL_RESTART', 'Restart this poll');
define('_AM_XOOPSPOLL_ADDMORE', 'Add more options');
define('_AM_XOOPSPOLL_RUSUREDEL', 'Are you sure you want to delete this poll and all its comments?');
define('_AM_XOOPSPOLL_RESTARTPOLL', 'Restart poll');
define('_AM_XOOPSPOLL_RESET', 'Reset all logs for this poll?');
define('_AM_XOOPSPOLL_ADDPOLL', 'Add Poll');

define('_AM_XOOPSPOLL_LOGSLIST', 'Log List');
define('_AM_XOOPSPOLL_RETURNLIST', 'Back to Polls List');
define('_AM_XOOPSPOLL_LOGID', 'Log ID');
define('_AM_XOOPSPOLL_OPTIONID', 'Picked Option');
define('_AM_XOOPSPOLL_IP', 'IP Address');
define('_AM_XOOPSPOLL_VOTER', 'Voter');
define('_AM_XOOPSPOLL_VOTETIME', 'Vote Time');

//1.32 / 1.33
define('_AM_XOOPSPOLL_HOST_NAME', 'Host Name');

// Text for Admin footer
//define('_AM_XOOPSPOLL_ADMIN_FOOTER', '<div class='center smallsmall italic pad5'>XOOPS Poll is maintained by the <a class='tooltip' rel='external' href='https://xoops.org/' title='Visit XOOPS Community'>XOOPS Community</a></div>');

define('_AM_XOOPSPOLL_ACTIONS', 'Actions');
define('_AM_XOOPSPOLL_POLLVOTERS', 'Voters participating in this Poll');

//1.33
define('_AM_XOOPSPOLL_ERROR_INVALID_POLLID', 'Invalid Poll ID, please try again.');

//1.40
define('_AM_XOOPSPOLL_ADMIN_MISSING', "<span style='color: red;'> ERROR: You must install the XOOPS Frameworks moduleadmin class.</span>");
define('_AM_XOOPSPOLL_RESULT_SUM', 'Results Summary');
define('_AM_XOOPSPOLL_OPTION', 'Option');
define('_AM_XOOPSPOLL_LABEL', 'Label');
define('_AM_XOOPSPOLL_COUNT', 'Count');
define('_AM_XOOPSPOLL_CLONE', 'Clone');
define('_AM_XOOPSPOLL_CLONE_SUCCESS', 'Clone of poll created successfully');
define('_AM_XOOPSPOLL_CLONE_FAIL', 'Unable to clone this poll');
define('_AM_XOOPSPOLL_START_TIME', 'Start time');
define('_AM_XOOPSPOLL_PM_VOTER', 'Private message voter');
define('_AM_XOOPSPOLL_ERROR_DBUPDATE', "<span style='color: red;'> Database could not be updated</span>");
define('_AM_XOOPSPOLL_HELPNOTUPDATED', 'Unable to update link text in help file');
define('_AM_XOOPSPOLL_ERROR_UPDATE', 'The module update script did not complete successfully.');
define('_AM_XOOPSPOLL_ERROR_COLUMN', 'Could not create column in database : ');
define('_AM_XOOPSPOLL_HELPNOTFOUND', '%s %s NOT found');
define('_AM_XOOPSPOLL_MULTI_LIMIT', 'How many options can the voter choose?');
define('_AM_XOOPSPOLL_MULTI_LIMIT_DESC', 'This is only needed if you allow multiple selections. Set to zero (0) for unlimited.');
define('_AM_XOOPSPOLL_NOTHING_HERE', 'There is nothing to update.');
define('_AM_XOOPSPOLL_PREFERENCES', 'PREFERENCES');
define('_AM_XOOPSPOLL_OPTION_SETTINGS', 'OPTION SETTINGS');
define('_AM_XOOPSPOLL_BLIND', 'Display poll results');
define('_AM_XOOPSPOLL_POLL_TIMES', 'Poll Times');
define('_AM_XOOPSPOLL_NOTIFY_VOTER', 'Notify voters after vote (Registered users only)?');
define('_AM_XOOPSPOLL_IMPORT_UMFRAGE', 'Import Polls from Umfrage module');
define('_AM_XOOPSPOLL_RUSUREUMFRAGE', 'Are you sure you want to import polls from Umfrage?');
define('_AM_XOOPSPOLL_UMFRAGE_INTRO', 'Import existing Umfrage polls into Xoopspoll.');
define('_AM_XOOPSPOLL_UMFRAGE_STEP1', 'Turn off XOOPS site.');
define('_AM_XOOPSPOLL_UMFRAGE_STEP2', 'Make sure the Umfrage module is installed and active.');
define('_AM_XOOPSPOLL_UMFRAGE_STEP3', 'Run the import script (select the button below).');
define('_AM_XOOPSPOLL_UMFRAGE_STEP4', 'Check settings for newly imported polls.');
define('_AM_XOOPSPOLL_UMFRAGE_STEP5', 'Turn on XOOPS site.');
define('_AM_XOOPSPOLL_NEWBB_SUPPORT', 'Newbb Support');
define('_AM_XOOPSPOLL_NEWBB_INTRO', 'Indicates poll is associated with a Newbb Topic');
define('_AM_XOOPSPOLL_NEWBB_TOPIC', 'Topic:');

// datetimepicker language strings
define('_AM_XOOPSPOLL_DTP_CLOSETEXT', 'Done');
define('_AM_XOOPSPOLL_DTP_PREVTEXT', 'Prev');
define('_AM_XOOPSPOLL_DTP_NEXTTEXT', 'Next');
define('_AM_XOOPSPOLL_DTP_CURRENTTEXT', 'Now');

// NOTE:  the following are each a SINGLE STRING THE PLACEMENT OF single and double quotes is IMPORTANT!
define('_AM_XOOPSPOLL_DTP_MONTHNAMES', "'January','February','March','April','May','June','July','August','September','October','November','December'");
define('_AM_XOOPSPOLL_DTP_MONTHNAMESSHORT', "'Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'");
define('_AM_XOOPSPOLL_DTP_DAYNAMES', "'Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'");
define('_AM_XOOPSPOLL_DTP_DAYNAMESSHORT', "'Sun','Mon','Tue','Wed','Thu','Fri','Sat'");
define('_AM_XOOPSPOLL_DTP_DAYNAMESMIN', "'Su','Mo','Tu','We','Th','Fr','Sa'");
// end NOTE
define('_AM_XOOPSPOLL_DTP_WEEKHEADER', 'Wk');
define('_AM_XOOPSPOLL_DTP_DATEFORMAT', 'yy/mm/dd');
define('_AM_XOOPSPOLL_DTP_TIMEFORMAT', 'hh:mm');
define('_AM_XOOPSPOLL_DTP_FIRSTDAY', 1);
define('_AM_XOOPSPOLL_DTP_ISRTL', 'false');  // can only be set to 'true' or 'false' (always use ENGLISH true/false)
define('_AM_XOOPSPOLL_DTP_SHOWMONTHAFTERYEAR', 'false');
define('_AM_XOOPSPOLL_DTP_YEARSUFFIX', null);
define('_AM_XOOPSPOLL_DTP_TIMEONLYTITLE', 'Choose Time');
define('_AM_XOOPSPOLL_DTP_TIMETEXT', 'Time');
define('_AM_XOOPSPOLL_DTP_HOURTEXT', 'Hour');
define('_AM_XOOPSPOLL_DTP_MINUTETEXT', 'Minute');
define('_AM_XOOPSPOLL_DTP_SECONDTEXT', 'Second');
define('_AM_XOOPSPOLL_DTP_MILLISECTEXT', 'Millisecond');

// Text for Admin footer
define('_AM_XOOPSPOLL_MAINTAINED_BY', 'XOOPS Poll is maintained by the');
define('_AM_XOOPSPOLL_MAINTAINTED_TITLE', 'Visit XOOPS Community');
define('_AM_XOOPSPOLL_MAINTAINTED_TEXT', 'XOOPS Community');

//install/upgrade
define('_AM_XOOPSPOLL_UPGRADE_FAILED', 'Database %s table update failed.');
define('_AM_XOOPSPOLL_LOG_FAILED', 'There was an error updating the logs for the \'%s\' poll.');
define('_AM_XOOPSPOLL_OPTION_FAILED', 'Could not create the \'%s\' option for the \'%s\' poll. %s');
define('_AM_XOOPSPOLL_QUESTION_FAILED', 'Failed to create the \'%s\' poll in the database. %s');
define('_AM_XOOPSPOLL_QUESTION_IMPORT_FAILED', 'The \'%s\' poll was not imported. %s');
define('_AM_XOOPSPOLL_UMFRAGE_FAILED', 'Please make sure umfrage is installed and active.');
define('_AM_XOOPSPOLL_IMPORT_FAILED', 'Import Error(s)');
define('_AM_XOOPSPOLL_IMPORT_SUCCESS', '(%d) polls successfully imported from Umfrage.');
