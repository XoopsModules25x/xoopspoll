<?php
// $Id: xoopspoll.php 10518 2012-12-23 05:21:34Z beckmi $
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                    Copyright (c) 2000 XOOPS.org                           //
//                       <http://www.xoops.org/>                             //
//  ------------------------------------------------------------------------ //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License as published by     //
//  the Free Software Foundation; either version 2 of the License, or        //
//  (at your option) any later version.                                      //
//                                                                           //
//  You may not change or alter any portion of this comment or credits       //
//  of supporting developers from this source code or any supporting         //
//  source code which is considered copyrighted (c) material of the          //
//  original comment or credit authors.                                      //
//                                                                           //
//  This program is distributed in the hope that it will be useful,          //
//  but WITHOUT ANY WARRANTY; without even the implied warranty of           //
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
//  GNU General Public License for more details.                             //
//                                                                           //
//  You should have received a copy of the GNU General Public License        //
//  along with this program; if not, write to the Free Software              //
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
//  ------------------------------------------------------------------------ //

include_once XOOPS_ROOT_PATH.'/modules/xoopspoll/class/xoopspoll.php';
include_once XOOPS_ROOT_PATH.'/modules/xoopspoll/class/xoopspolloption.php';
include_once XOOPS_ROOT_PATH.'/modules/xoopspoll/language/'.$xoopsConfig['language'].'/main.php';
include_once XOOPS_ROOT_PATH."/modules/xoopspoll/class/xoopspolllog.php";

function b_xoopspoll_show()
{
	global $xoopsUser;
	$block = array();
	$polls =& XoopsPoll::getAll(array('display=1'), true, 'weight ASC, end_time DESC');
	$count = count($polls);
	$block['lang_vote'] = _PL_VOTE;
	$block['lang_results'] = _PL_RESULTS;
	$block['lang_expires'] = _PL_WILLEXPIRE;
	$block['lang_expired'] = _PL_HASEXPIRED;
	$block['lang_comments'] = _PL_COMMENTS;
	$block['lang_comment'] = _PL_COMMENT;
	$block['url'] = "http" . ((!empty($_SERVER['HTTPS'])) ? "s" : "") . "://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];

	for ($i = 0; $i < $count; $i++) {
		$options_arr =& XoopsPollOption::getAllByPollId($polls[$i]->getVar('poll_id'));
		$option_type = 'radio';
		$option_name = 'option_id';
		if ($polls[$i]->getVar('multiple') == 1) {
			$option_type = 'checkbox';
			$option_name .= '[]';
		}
				
		$totalVotes=$polls[$i]->getVar('votes');
		$uid = is_object($xoopsUser) ? $xoopsUser->getVar('uid') : 0;
		if ( XoopsPollLog::hasVoted($polls[$i]->getVar('poll_id'), xoops_getenv('REMOTE_ADDR'),$uid)){
			$hasVoted=1;
		}else{
			$hasVoted=0;
		}
	
		foreach ($options_arr as $option) {
		if ($totalVotes>0) {
			$percent = intval(100 * $option->getVar("option_count") / $totalVotes).'%';
		} else {	
			$percent = '0'.'%';
		};
			$options[] = array('id' => $option->getVar('option_id'), 'text' => $option->getVar('option_text'), 'count' => $option->getVar('option_count'), 'percent'=>$percent, 'color'=>$option->getVar('option_color'));
		}

		$poll = array('id' => $polls[$i]->getVar('poll_id'), 'question' => $polls[$i]->getVar('question'), 'option_type' => $option_type, 'option_name' => $option_name, 'options' => $options,'has_expired'=>$polls[$i]->hasExpired(), 'votes' => $polls[$i]->getVar('votes'), 'has_voted'=>$hasVoted, 'totalVotes' => sprintf(_PL_TOTALVOTES, $totalVotes), 'comments' => XoopsPoll::getcomments($polls[$i]->getVar('poll_id')), 'end_time'=>formatTimeStamp($polls[$i]->getVar('end_time'), "m"), 'comment_mode'=> XoopsPollLog::commentMode());
		$block['polls'][] =& $poll;
		unset($options);
		unset($poll);
		
	}
    return $block;
}
?>