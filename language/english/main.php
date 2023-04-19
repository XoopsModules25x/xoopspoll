<?php declare(strict_types=1);
/**
 * Main Definitions
 *
 * @subpackage:: defines
 */

/**
 *    File Name pollresults.php
 */
define('_MD_XOOPSPOLL_TOTALVOTES', 'Total Votes: %s');
define('_MD_XOOPSPOLL_TOTALVOTERS', 'Total Voters: %s');

/**
 * File Name index.php
 */
define('_MD_XOOPSPOLL_POLLSLIST', 'Polls List');
define('_MD_XOOPSPOLL_ALREADYVOTED', 'Sorry, you have already voted once.');
define('_MD_XOOPSPOLL_THANKSFORVOTE', 'Thanks for your vote!');
define('_MD_XOOPSPOLL_SORRYEXPIRED', 'Sorry, but the poll has expired.');
define('_MD_XOOPSPOLL_YOURPOLLAT', '%s, your poll at %s'); // 1st %s is user name, 2nd %s is site name
define('_MD_XOOPSPOLL_PREV', 'Previous');
define('_MD_XOOPSPOLL_NEXT', 'Next');
define('_MD_XOOPSPOLL_POLLQUESTION', 'Poll Question');
define('_MD_XOOPSPOLL_VOTERS', 'Total voters');
define('_MD_XOOPSPOLL_VOTES', 'Total votes');
define('_MD_XOOPSPOLL_EXPIRATION', 'Expiration');
define('_MD_XOOPSPOLL_EXPIRED', 'Expired');
define('_MD_XOOPSPOLL_MUSTLOGIN', 'Sorry, but you must log in to vote in this poll.');

/**
 * File Name xoopspollrenderer.php
 */
// %s represents date
define('_MD_XOOPSPOLL_HIDE_ENDSAT', 'Ends at %s');
define('_MD_XOOPSPOLL_HIDE_ENDEDAT', 'Ended at %s');
define('_MD_XOOPSPOLL_STARTSAT', 'Starts at %s');
define('_MD_XOOPSPOLL_VOTE', 'Vote');
define('_MD_XOOPSPOLL_RESULTS', 'Results');

// 1.32

/**
 * File name /admin/index.php
 */
define('_MD_XOOPSPOLL_DASHBOARD', 'Xoops Polls Dashboard');
define('_MD_XOOPSPOLL_TOTALPOLLS', 'Total Polls: <strong>%s</strong> ');
define('_MD_XOOPSPOLL_TOTALACTIVE', 'Active Polls: <strong>%s</strong> ');
define('_MD_XOOPSPOLL_TOTALWAITING', 'Polls not Started: <strong>%s</strong> ');
define('_MD_XOOPSPOLL_TOTALEXPIRED', 'Expired Polls: <strong>%s</strong> ');

// 1.40
define('_MD_XOOPSPOLL_VOTE_NOW', 'Click here to vote now!');
define('_MD_XOOPSPOLL_ERROR_INVALID_POLLID', 'Invalid Poll ID, please try again.');
define('_MD_XOOPSPOLL_CANNOTVOTE', 'Sorry, but you are not allowed to vote in this poll.');
define('_MD_XOOPSPOLL_HIDE_NEVER', 'never hide results');
define('_MD_XOOPSPOLL_HIDE_ALWAYS', 'always hide results');
define('_MD_XOOPSPOLL_HIDE_VOTED', 'hide results until after voting');
define('_MD_XOOPSPOLL_HIDE_END', 'hide results until poll expires');
define('_MD_XOOPSPOLL_HIDE_ALWAYS_MSG', 'The results of this poll are private and are not visible.');
define('_MD_XOOPSPOLL_HIDE_VOTED_MSG', 'The results of this poll are only visible after you have voted.');
define('_MD_XOOPSPOLL_HIDE_END_MSG', 'The results of this poll are only visible after the poll has ended.');
define('_MD_XOOPSPOLL_YOURVOTEAT', '%s, your vote at %s'); // 1st %s is user name, 2nd %s is site name
define('_MD_XOOPSPOLL_VOTE_ERROR', 'There was a problem registering your vote. Please try again.');
define('_MD_XOOPSPOLL_MULTITEXT', 'Please select a max. of %d items');
define('_MD_XOOPSPOLL_OBSCURED', 'Hidden');

//Mail Voter
define('_MD_XOOPSPOLL_ENDED_AT', 'The poll ended on %s.');
define('_MD_XOOPSPOLL_ENDS_ON', 'Voting in the poll ends on %s.');
define('_MD_XOOPSPOLL_SEE_AT', 'You can see the results of the poll at:');
define('_MD_XOOPSPOLL_SEE_AFTER', 'You will be able to see the results of the vote once it ends at:');

define('_MD_XOOPSPOLL_ERROR_OPTIONS_MISSING', 'You need to add some Options for the Poll');

//Umfrage

define('_PL_TOTALVOTES', 'Total Votes: %s');
define('_PL_TOTALVOTERS', 'Total Voters: %s');

//%%%%%%	File Name index.php 	%%%%%
define('_PL_POLLSLIST', 'Polls List');
define('_PL_LOGINTOVOTE', 'You must login to vote.');
define('_PL_VOTEOVERLIMIT', 'Sorry, you chose too many options. You can choose up to %s options.');
define('_PL_ALREADYVOTED', 'Sorry, you have already voted once.');
define('_PL_ALREADYVOTED2', 'You have already voted');
define('_PL_THANKSFORVOTE', 'Thanks for your vote!');
define('_PL_SORRYEXPIRED', 'Sorry, but the poll has expired.');
define('_PL_YOURPOLLAT', '%s, your poll at %s'); // 1st %s is user name, 2nd %s is site name
define('_PL_YOURVOTEAT', '%s, your vote at %s'); // 1st %s is user name, 2nd %s is site name
define('_PL_PREV', 'Previous');
define('_PL_NEXT', 'Next');
define('_PL_POLLQUESTION', 'Poll Question');
define('_PL_VOTERS', 'Total voters');
define('_PL_VOTES', 'Total votes');
define('_PL_EXPIRATION', 'Expiration');
define('_PL_EXPIRED', 'Expired');
define('_PL_EXPIREDON', 'Poll expired on');
define('_PL_ONLYREGISTERED', 'Voting only for logged in users');

//%%%%%%	File Name umfragerenderer.php 	%%%%%
// %s represents date
define('_PL_ENDSAT', 'Ends at %s');
define('_PL_ENDEDAT', 'Ended at %s');
define('_PL_VOTE', 'Vote!');
define('_PL_RESULTS', 'Final Results');
define('_PL_STANDINGS', 'Current Standings');
define('_PL_SHOW_ELECTIONSMODE', 'Cannot view results while in Election mode.');
define('_PL_MAILBODY1', 'The result of the poll is:');
define('_PL_MAILBODY2', ' votes for: ');
define('_PL_MAILBODY3', 'There were %d votes');
define('_PL_HALFBLIND', 'This is a blind vote where the result of the vote is shown after the voting deadline');
define('_PL_FULLBLIND', 'This is a blind vote where the result is not shown in public.');
