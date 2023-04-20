<?php declare(strict_types=1);

namespace XoopsModules\Xoopspoll;

/*
 You may not change or alter any portion of this comment or credits
 of supporting developers from this source code or any supporting source code
 which is considered copyrighted (c) material of the original comment or credit authors.
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*/

/**
 * XOOPS Poll Class Definitions
 *
 * @copyright ::  {@link https://xoops.org/ XOOPS Project}
 * @license   ::    {@link https://www.gnu.org/licenses/gpl-2.0.html GNU GPL 2.0 or later}
 * @subpackage:: class
 * @since     ::         1.40
 * @author    ::     zyspec <zyspec@yahoo.com>
 */

/**
 * Class PollHandler
 */
class PollHandler extends \XoopsPersistableObjectHandler
{
    /**
     * @var \XoopsModules\Xoopspoll\Helper
     */
    protected Helper $helper;

    /**
     * PollHandler::__construct()
     *
     * @param \XoopsModules\Xoopspoll\Helper|null $helper
     */
    public function __construct(\XoopsDatabase $db = null, Helper $helper = null)
    {
        $this->helper = $helper ?? Helper::getInstance();
        parent::__construct($db, 'xoopspoll_desc', Poll::class, 'poll_id', 'question');
    }

    /**
     * Update the Vote count from the log and polls
     * @param \XoopsObject $pollObj
     * @return int|false object ID or false
     */
    public function updateCount(\XoopsObject $pollObj)
    {
        $success = false;
        if ($pollObj instanceof Poll) {
            $pollId = $pollObj->getVar('poll_id');
            /** @var LogHandler $logHandler */
            $logHandler = $this->helper->getHandler('Log');
            $votes      = $logHandler->getTotalVotesByPollId($pollId);
            $voters     = $logHandler->getTotalVotersByPollId($pollId);
            $pollObj->setVar('votes', $votes);
            $pollObj->setVar('voters', $voters);
            $success = $this->insert($pollObj);
        }

        return $success;
    }

    /**
     * Mail the results of poll when expired
     * @param mixed|null $pollObj
     * @return array true|false indicating sendmail status
     */
    public function mailResults(mixed $pollObj = null): array
    {
        $criteria = new \CriteriaCompo();
        $criteria->add(new \Criteria('end_time', \time(), '<'));  // expired polls
        $criteria->add(new \Criteria('mail_status', Constants::POLL_NOT_MAILED, '=')); // email not previously sent
        if (!empty($pollObj) && ($pollObj instanceof Poll)) {
            $criteria->add(new \Criteria('poll_id', $pollObj->getVar('poll_id'), '='));
            $criteria->setLimit(1);
        }
        $pollObjs = &$this->getAll($criteria);
        $tplFile  = 'mail_results.tpl';
        $lang     = 'english';
        if (\file_exists($GLOBALS['xoops']->path('modules/xoopspoll/language/' . $GLOBALS['xoopsConfig']['language'] . '/mail_template/' . $tplFile))) {
            $lang = $GLOBALS['xoopsConfig']['language'];
        }
        \xoops_loadLanguage('main', 'xoopspoll', $lang);

        $ret = [];

        // setup mailer
        $xoopsMailer = \xoops_getMailer();
        $xoopsMailer->useMail();
        $xoopsMailer->setTemplateDir($GLOBALS['xoops']->path('modules/xoopspoll/language/' . $lang . '/mail_template/'));

        $xoopsMailer->setTemplate($tplFile);
        $xoopsMailer->assign('SITENAME', $GLOBALS['xoopsConfig']['sitename']);
        $xoopsMailer->assign('ADMINMAIL', $GLOBALS['xoopsConfig']['adminmail']);
        $xoopsMailer->assign('SITEURL', $GLOBALS['xoops']->url(''));
        $xoopsMailer->assign('MODULEURL', $GLOBALS['xoops']->url('modules/xoopspoll/'));
        $xoopsMailer->setFromEmail($GLOBALS['xoopsConfig']['adminmail']);
        $xoopsMailer->setFromName($GLOBALS['xoopsConfig']['sitename']);
        foreach ($pollObjs as $pollObject) {
            $pollValues = $pollObject->getValues();
            // get author info
            $author = new \XoopsUser($pollValues['user_id']);
            if (($author instanceof \XoopsUser) && ($author->uid() > 0)) {
                $xoopsMailer->setToUsers($author);
                // initialize variables
                $xoopsMailer->assign('POLL_QUESTION', $pollValues['question']);
                $xoopsMailer->assign('POLL_START', \formatTimestamp($pollValues['start_time'], 'l', $author->timezone()));
                $xoopsMailer->assign('POLL_END', \formatTimestamp($pollValues['end_time'], 'l', $author->timezone()));
                $xoopsMailer->assign('POLL_VOTES', $pollValues['votes']);
                $xoopsMailer->assign('POLL_VOTERS', $pollValues['voters']);
                $xoopsMailer->assign('POLL_ID', $pollValues['poll_id']);
                $xoopsMailer->setSubject(\sprintf(\_MD_XOOPSPOLL_YOURPOLLAT, $author->uname(), $GLOBALS['xoopsConfig']['sitename']));
                if ($xoopsMailer->send(false)) {
                    $pollObject->setVar('mail_status', Constants::POLL_MAILED);
                    $ret[] = $this->insert($pollObject);
                } else {
                    $ret[] = $xoopsMailer->getErrors(false); // return error array from mailer
                }
            }
        }

        return $ret;
    }
}
