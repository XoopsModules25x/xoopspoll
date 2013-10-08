<?php
// $Id: modinfo.php 10518 2012-12-23 05:21:34Z beckmi $
// Module Info

// The name of this module
define("_MI_POLLS_NAME","XOOPS Poll");

// A brief description of this module
define("_MI_POLLS_DESC","Shows a poll/survey block");

// Names of blocks for this module (Not all module has blocks)
define("_MI_POLLS_BNAME1","Polls");

// Names of admin menu items
define("_MI_POLLS_ADMENU1","List Polls");
define("_MI_POLLS_ADMENU2","Add Poll");

//Module properties
define("_MI_POLL_LIMITBYIP","Restrict voting from the same IP");
define("_MI_POLL_LIMITBYIPD","");
define("_MI_POLL_LIMITBYUID","Restricting voting from the same User");
define("_MI_POLL_LIMITBYUIDD","");

// index.php
define("_MI_POLLS_HOME",                  "Home");
define("_MI_POLLS_ADMIN_ABOUT",                  "About");
define("_MI_POLLS_ADMIN_HELP",                 "Help");

//1.33
// lookup host
define("_MI_POLLS_LOOKUPHOST","Show hostname instead of IP address");
define("_MI_POLLS_LOOKUPHOSTDESC","List host names instead of IP addresses in viewing poll log. Since nslookup is used, It might take longer to show names.");