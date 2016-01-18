<?php
/**
 * ****************************************************************************
 * Marquee - MODULE FOR XOOPS
 * Copyright (c) Hervé Thouzard (http://www.herve-thouzard.com)
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * ****************************************************************************
 *
 * @copyright ::   {@link http://www.herve-thouzard.com Hervé Thouzard}
 * @license   ::     {@link http://www.fsf.org/copyleft/gpl.html GNU public license}
 * @author    ::      Hervé Thouzard (http://www.herve-thouzard.com)
 * @package   ::     marquee
 * @subpackage::  plugins
 * @version   ::     $Id: $
 */
/**
 * Script to list the recent polls from the xoopspoll module version 1.40
 * @param $limit
 * @param $dateformat
 * @param $itemssize
 * @return array
 */
function b_marquee_xoopspoll($limit, $dateformat, $itemssize)
{
    include_once $GLOBALS['xoops']->path('modules/marquee/include/functions.php');
    $block        = array();
    $myts         =& MyTextSanitizer::getInstance();
    $poll_handler =& xoops_getmodulehandler('poll', 'xoopspoll');
    $criteria     = new CriteriaCompo();
    $criteria->add(new Criteria('start_time', time(), '<='));
    $criteria->add(new Criteria('end_time', time(), '>'));
    $criteria->setLimit((int)($limit));
    $criteria->setSort('start_time');
    $criteria->setOrder('DESC');
    $pollFields = array('poll_id', 'question', 'start_time', 'user_id');
    $pollObjs   = $poll_handler->getAll($criteria, $pollFields);
    foreach ($pollObjs as $pollObj) {
        $pollValues = $pollObj->getValues();
        $title      = $myts->htmlSpecialChars($pollValues['question']);
        if ((int)($itemssize) > 0) {
            $title = xoops_substr($title, 0, $itemssize + 3);
        }
        $xuStartTimestamp = xoops_getUserTimestamp($pollValues['start_time']);
        $block[]          = array(
            'date'     => formatTimestamp($xuStartTimestamp, $dateformat),
            'category' => '',
            'author'   => $pollValues['user_id'],
            'title'    => $title,
            'link'     => "<a href='" . $GLOBALS['xoops']->url('modules/xoopspoll/index.php') . "?poll_id={$pollValues['poll_id']}'>{$title}</a>");
        unset ($pollValues);
    }

    return $block;
}
