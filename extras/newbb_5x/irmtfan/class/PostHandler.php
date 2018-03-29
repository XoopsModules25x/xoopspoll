<?php namespace XoopsModules\Newbb;

/*
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

/**
 * @copyright    {@link https://xoops.org/ XOOPS Project}
 * @license      {@link http://www.gnu.org/licenses/gpl-2.0.html GNU GPL 2 or later}
 * @package
 * @since
 * @author       XOOPS Development Team, phppp (D.J., infomax@gmail.com)
 */

use XoopsModules\Xoopspoll;
use XoopsModules\Newbb;

// defined('XOOPS_ROOT_PATH') || die('Restricted access');

defined('NEWBB_FUNCTIONS_INI') || include XOOPS_ROOT_PATH . '/modules/newbb/include/functions.ini.php';
//newbb_load_object();


/**
 * Class PostHandler
 */
//class PostHandler extends ArtObjectHandler
class PostHandler extends \XoopsPersistableObjectHandler
{
    /**
     * @param null|\XoopsDatabase $db
     */
    public function __construct(\XoopsDatabase $db)
    {
        parent::__construct($db, 'bb_posts', Post::class, 'post_id', 'subject');
    }


    /**
     * @param  mixed|null $id
     * @param null        $fields
     * @return null|\XoopsObject
     */
    public function get($id = null, $fields = null)
    {
        $id   = (int)$id;
        $post = null;
        $sql  = 'SELECT p.*, t.* FROM ' . $this->db->prefix('bb_posts') . ' p LEFT JOIN ' . $this->db->prefix('bb_posts_text') . ' t ON p.post_id=t.post_id WHERE p.post_id=' . $id;
        if ($array = $this->db->fetchArray($this->db->query($sql))) {
            $post = $this->create(false);
            $post->assignVars($array);
        }

        return $post;
    }

    /**
     * @param  int $topic_id
     * @param  int $limit
     * @param  int $approved
     * @return array
     */
    public function &getByLimit($topic_id, $limit, $approved = 1)
    {
        $sql    = 'SELECT p.*, t.*, tp.topic_status FROM '
                  . $this->db->prefix('bb_posts')
                  . ' p LEFT JOIN '
                  . $this->db->prefix('bb_posts_text')
                  . ' t ON p.post_id=t.post_id LEFT JOIN '
                  . $this->db->prefix('bb_topics')
                  . ' tp ON tp.topic_id=p.topic_id WHERE p.topic_id='
                  . $topic_id
                  . ' AND p.approved ='
                  . $approved
                  . ' ORDER BY p.post_time DESC';
        $result = $this->db->query($sql, $limit, 0);
        $ret    = [];
        while (false !== ($myrow = $this->db->fetchArray($result))) {
            $post = $this->create(false);
            $post->assignVars($myrow);

            $ret[$myrow['post_id']] = $post;
            unset($post);
        }

        return $ret;
    }

    /**
     * @param $post
     * @return mixed
     */
    public function getPostForPDF(&$post)
    {
        return $post->getPostBody(true);
    }

    /**
     * @param $post
     * @return mixed
     */
    public function getPostForPrint(&$post)
    {
        return $post->getPostBody();
    }

    /**
     * @param       $post
     * @param  bool $force
     * @return bool
     */
    public function approve(&$post, $force = false)
    {
        if (empty($post)) {
            return false;
        }
        if (is_numeric($post)) {
            $post = $this->get($post);
        }
        $post_id = $post->getVar('post_id');

        $wasApproved = $post->getVar('approved');
        // irmtfan approve post if the approved = 0 (pending) or -1 (deleted)
        if (empty($force) && $wasApproved > 0) {
            return true;
        }
        $post->setVar('approved', 1);
        $this->insert($post, true);

        /** @var Newbb\TopicHandler $topicHandler */
        $topicHandler = Newbb\Helper::getInstance()->getHandler('Topic');
        $topic_obj    = $topicHandler->get($post->getVar('topic_id'));
        if ($topic_obj->getVar('topic_last_post_id') < $post->getVar('post_id')) {
            $topic_obj->setVar('topic_last_post_id', $post->getVar('post_id'));
        }
        if ($post->isTopic()) {
            $topic_obj->setVar('approved', 1);
        } else {
            $topic_obj->setVar('topic_replies', $topic_obj->getVar('topic_replies') + 1);
        }
        $topicHandler->insert($topic_obj, true);

        /** @var Newbb\ForumHandler $forumHandler */
        $forumHandler = Newbb\Helper::getInstance()->getHandler('Forum');
        $forum_obj    = $forumHandler->get($post->getVar('forum_id'));
        if ($forum_obj->getVar('forum_last_post_id') < $post->getVar('post_id')) {
            $forum_obj->setVar('forum_last_post_id', $post->getVar('post_id'));
        }
        $forum_obj->setVar('forum_posts', $forum_obj->getVar('forum_posts') + 1);
        if ($post->isTopic()) {
            $forum_obj->setVar('forum_topics', $forum_obj->getVar('forum_topics') + 1);
        }
        $forumHandler->insert($forum_obj, true);

        // Update user stats
        if ($post->getVar('uid') > 0) {
            $memberHandler = xoops_getHandler('member');
            $poster        = $memberHandler->getUser($post->getVar('uid'));
            if (is_object($poster) && $post->getVar('uid') === $poster->getVar('uid')) {
                $poster->setVar('posts', $poster->getVar('posts') + 1);
                $res = $memberHandler->insertUser($poster, true);
                unset($poster);
            }
        }

        // Update forum stats
        $statsHandler = Newbb\Helper::getInstance()->getHandler('Stats');
        $statsHandler->update($post->getVar('forum_id'), 'post');
        if ($post->isTopic()) {
            $statsHandler->update($post->getVar('forum_id'), 'topic');
        }

        return true;
    }

    /**
     * @param  \XoopsObject $post
     * @param  bool        $force
     * @return bool
     */
    public function insert(\XoopsObject $post, $force = true)
    {
        // Set the post time
        // The time should be 'publish' time. To be adjusted later
        if (!$post->getVar('post_time')) {
            $post->setVar('post_time', time());
        }

        /** @var Newbb\TopicHandler $topicHandler */
        $topicHandler = Newbb\Helper::getInstance()->getHandler('Topic');
        // Verify the topic ID
        if ($topic_id = $post->getVar('topic_id')) {
            $topic_obj = $topicHandler->get($topic_id);
            // Invalid topic OR the topic is no approved and the post is not top post
            if (!$topic_obj//            || (!$post->isTopic() && $topic_obj->getVar("approved") < 1)
            ) {
                return false;
            }
        }
        if (empty($topic_id)) {
            $post->setVar('topic_id', 0);
            $post->setVar('pid', 0);
            $post->setNew();
            $topic_obj = $topicHandler->create();
        }
        $textHandler    = Newbb\Helper::getInstance()->getHandler('Text');
        $post_text_vars = ['post_text', 'post_edit', 'dohtml', 'doxcode', 'dosmiley', 'doimage', 'dobr'];
        if ($post->isNew()) {
            if (!$topic_id = $post->getVar('topic_id')) {
                $topic_obj->setVar('topic_title', $post->getVar('subject', 'n'));
                $topic_obj->setVar('topic_poster', $post->getVar('uid'));
                $topic_obj->setVar('forum_id', $post->getVar('forum_id'));
                $topic_obj->setVar('topic_time', $post->getVar('post_time'));
                $topic_obj->setVar('poster_name', $post->getVar('poster_name'), true);
                $topic_obj->setVar('approved', $post->getVar('approved'), true);

                if (!$topic_id = $topicHandler->insert($topic_obj, $force)) {
                    $post->deleteAttachment();
                    $post->setErrors('insert topic error');

                    //xoops_error($topic_obj->getErrors());
                    return false;
                }
                $post->setVar('topic_id', $topic_id);

                $pid = 0;
                $post->setVar('pid', 0);
            } elseif (!$post->getVar('pid')) {
                $pid = $topicHandler->getTopPostId($topic_id);
                $post->setVar('pid', $pid);
            }

            $text_obj = $textHandler->create();
            foreach ($post_text_vars as $key) {
                $text_obj->vars[$key] = $post->vars[$key];
            }
            $post->destroyVars($post_text_vars);
            if (!$post_id = parent::insert($post, $force)) {
                return false;
            } else {
                $post->unsetNew();
            }
            $text_obj->setVar('post_id', $post_id);
            if (!$textHandler->insert($text_obj, $force)) {
                $this->delete($post);
                $post->setErrors('post text insert error');

                //xoops_error($text_obj->getErrors());
                return false;
            }
            if ($post->getVar('approved') > 0) {
                $this->approve($post, true);
            }
            $post->setVar('post_id', $post_id);
        } else {
            if ($post->isTopic()) {
                if ($post->getVar('subject') !== $topic_obj->getVar('topic_title')) {
                    $topic_obj->setVar('topic_title', $post->getVar('subject', 'n'));
                }
                if ($post->getVar('approved') !== $topic_obj->getVar('approved')) {
                    $topic_obj->setVar('approved', $post->getVar('approved'));
                }
                $topic_obj->setDirty();
                if (!$result = $topicHandler->insert($topic_obj, $force)) {
                    $post->setErrors('update topic error');

                    //                    xoops_error($topic_obj->getErrors());
                    return false;
                }
            }
            $text_obj = $textHandler->get($post->getVar('post_id'));
            $text_obj->setDirty();
            foreach ($post_text_vars as $key) {
                $text_obj->vars[$key] = $post->vars[$key];
            }
            $post->destroyVars($post_text_vars);
            if (!$post_id = parent::insert($post, $force)) {
                //                xoops_error($post->getErrors());
                return false;
            } else {
                $post->unsetNew();
            }
            if (!$textHandler->insert($text_obj, $force)) {
                $post->setErrors('update post text error');

                //                xoops_error($text_obj->getErrors());
                return false;
            }
        }

        return $post->getVar('post_id');
    }

    /**
     * @param  XoopsObject $post
     * @param  bool        $isDeleteOne
     * @param  bool        $force
     * @return bool
     */
    public function delete($post, $isDeleteOne = true, $force = false)
    {
        $retVal = false;
        if (($post instanceof Post) && ($post->getVar('post_id') > 0)) {
            if ($isDeleteOne) {
                if ($post->isTopic()) {
                    $criteria = new \CriteriaCompo(new \Criteria('topic_id', $post->getVar('topic_id')));
                    $criteria->add(new \Criteria('approved', 1));
                    $criteria->add(new \Criteria('pid', 0, '>'));
                    if (!$this->getPostCount($criteria) > 0) {
                        $retVal = $this->_delete($post, $force);
                    }
                } else {
                    $retVal = $this->_delete($post, $force);
                }
            } else { // want to delete multiple posts
                //@TODO: test replacement of XoopsTree with XoopsObjectTree
                require_once $GLOBALS['xoops']->path('class/tree.php');
                // get tree with this object as the root
                $myObjTree = new \XoopsObjectTree($this->getAll(), 'post_id', 'pid', $post->getVar('post_id'));
                $arr       = $myObjtree->getAllChild(); // get all children of this object
                /*
                                require_once $GLOBALS['xoops']->path("class/xoopstree.php");
                                $mytree = new \XoopsTree($this->db->prefix("bb_posts"), "post_id", "pid");
                                $arr = $mytree->getAllChild($post->getVar('post_id'));
                */
                // irmtfan - delete children in a reverse order
                $success = true;
                for ($i = count($arr) - 1; $i >= 0; $i--) {
                    $childpost = $this->create(false);
                    $childpost->assignVars($arr[$i]);
                    $thisSuccess = $this->_delete($childpost, $force);
                    $success     = $success && $thisSuccess;
                    unset($childpost);
                }
                if ($success) {
                    // if we successfully deleted all children then try and delete this post
                    $retVal = $this->_delete($post, $force);
                } else {
                    // did not successfully delete all children so don't delete this post
                    $retVal = false;
                }
            }
        }

        return $retVal;
    }

    /**
     * @param       $post
     * @param  bool $force
     * @return bool
     */
    private function _delete(&$post, $force = false)
    {
        if ((!$post instanceof Post) || (0 === $post->getVar('post_id'))) {
            return false;
        }

        /* Set active post as deleted */
        if (($post->getVar('approved') > 0) && empty($force)) {
            $sql = 'UPDATE ' . $this->db->prefix('bb_posts') . ' SET approved = -1 WHERE post_id = ' . $post->getVar('post_id');
            if (!$result = $this->db->queryF($sql)) {
                //@TODO: add error check here
            }
        } else { /* delete pending post directly */
            $sql = sprintf('DELETE FROM %s WHERE post_id = %u', $this->db->prefix('bb_posts'), $post->getVar('post_id'));
            if (!$result = $this->db->queryF($sql)) {
                $post->setErrors('delte post error: ' . $sql);

                return false;
            }
            $post->deleteAttachment();

            $sql = sprintf('DELETE FROM %s WHERE post_id = %u', $this->db->prefix('bb_posts_text'), $post->getVar('post_id'));
            if (!$result = $this->db->queryF($sql)) {
                $post->setErrors('Could not remove post text: ' . $sql);

                return false;
            }
        }

        if ($post->isTopic()) {
            /** @var Newbb\TopicHandler $topicHandler */
            $topicHandler = Newbb\Helper::getInstance()->getHandler('Topic');
            $topic_obj    = $topicHandler->get($post->getVar('topic_id'));
            if (is_object($topic_obj) && $topic_obj->getVar('approved') > 0 && empty($force)) {
                $topiccount_toupdate = 1;
                $topic_obj->setVar('approved', -1);
                $topicHandler->insert($topic_obj);
                xoops_notification_deletebyitem($GLOBALS['xoopsModule']->getVar('mid'), 'thread', $post->getVar('topic_id'));
            } else {
                if (is_object($topic_obj)) {
                    if ($topic_obj->getVar('approved') > 0) {
                        xoops_notification_deletebyitem($GLOBALS['xoopsModule']->getVar('mid'), 'thread', $post->getVar('topic_id'));
                    }

                    $poll_id = $topic_obj->getVar('poll_id');
                    /** @var XoopsModuleHandler $moduleHandler */
                    $moduleHandler = xoops_getHandler('module');
                    if ($poll_id > 0) {
                        $poll_moduleHandler = $moduleHandler->getByDirname('xoopspoll');
                        if (($poll_moduleHandler instanceof XoopsModuleHandler) && $poll_moduleHandler->isactive()) {
                            $pollHandler = Xoopspoll\Helper::getInstance()->getHandler('Poll');
                            if (false !== $pollHandler->deleteAll(new \Criteria('poll_id', $poll_id, '='))) {
                                $optionHandler = Xoopspoll\Helper::getInstance()->getHandler('Option');
                                $optionHandler->deleteAll(new \Criteria('poll_id', $poll_id, '='));
                                $logHandler = Xoopspoll\Helper::getInstance()->getHandler('Log');
                                $logHandler->deleteAll(new \Criteria('poll_id', $poll_id, '='));
                                xoops_comment_delete($GLOBALS['xoopsModule']->getVar('mid'), $poll_id);
                            }
                        } else {
                            $poll_moduleHandler = $moduleHandler->getByDirname('umfrage');
                            if (($poll_moduleHandler instanceof XoopsModuleHandler)
                                && $poll_moduleHandler->isactive()) {
                                require_once $GLOBALS['xoops']->path('modules/umfrage/class/umfrage.php');
                                require_once $GLOBALS['xoops']->path('modules/umfrage/class/umfrageoption.php');
                                require_once $GLOBALS['xoops']->path('modules/umfrage/class/umfragelog.php');
                                require_once $GLOBALS['xoops']->path('modules/umfrage/class/umfragerenderer.php');

                                $poll = new Umfrage($poll_id);
                                if (false !== $poll->delete()) {
                                    UmfrageOption::deleteByPollId($poll_id);
                                    UmfrageLog::deleteByPollId($poll_id);
                                    xoops_comment_delete($GLOBALS['xoopsModule']->getVar('mid'), $poll_id);
                                }
                            }
                        }
                    }
                }

                $sql = sprintf('DELETE FROM %s WHERE topic_id = %u', $this->db->prefix('bb_topics'), $post->getVar('topic_id'));
                if (!$result = $this->db->queryF($sql)) {
                    //                  xoops_error($this->db->error());
                }
                $sql = sprintf('DELETE FROM %s WHERE topic_id = %u', $this->db->prefix('bb_votedata'), $post->getVar('topic_id'));
                if (!$result = $this->db->queryF($sql)) {
                    //                  xoops_error($this->db->error());
                }
            }
        } else {
            $sql = 'UPDATE '
                   . $this->db->prefix('bb_topics')
                   . ' t '
                   . 'LEFT JOIN '
                   . $this->db->prefix('bb_posts')
                   . ' p ON p.topic_id = t.topic_id '
                   . 'SET t.topic_last_post_id = p.post_id '
                   . 'WHERE t.topic_last_post_id = '
                   . $post->getVar('post_id')
                   . ' '
                   . 'AND p.post_id = (SELECT MAX(post_id) FROM '
                   . $this->db->prefix('bb_posts')
                   . ' '
                   . 'WHERE topic_id=t.topic_id)';
            if (!$result = $this->db->queryF($sql)) {
                //@TODO: add error checking here
            }
        }

        $postcount_toupdate = $post->getVar('approved');

        if ($postcount_toupdate > 0) {
            // Update user stats
            if ($post->getVar('uid') > 0) {
                $memberHandler = xoops_getHandler('member');
                $poster        = $memberHandler->getUser($post->getVar('uid'));
                if (is_object($poster) && $post->getVar('uid') === $poster->getVar('uid')) {
                    $poster->setVar('posts', $poster->getVar('posts') - 1);
                    $res = $memberHandler->insertUser($poster, true);
                    unset($poster);
                }
            }
            // irmtfan - just update the pid for approved posts when the post is not topic (pid=0)
            if (!$post->isTopic()) {
                $sql = 'UPDATE ' . $this->db->prefix('bb_posts') . ' SET pid = ' . $post->getVar('pid') . ' WHERE approved=1 AND pid=' . $post->getVar('post_id');
                if (!$result = $this->db->queryF($sql)) {
                    //                  xoops_error($this->db->error());
                }
            }
        }

        return true;
    }

    // START irmtfan enhance getPostCount when there is join (read_mode = 2)

    /**
     * @param  null $criteria
     * @param  null $join
     * @return int|null
     */
    public function getPostCount($criteria = null, $join = null)
    {
        // If not join get the count from XOOPS/class/model/stats as before
        if (empty($join)) {
            return parent::getCount($criteria);
        }

        $sql = 'SELECT COUNT(*) AS count' . ' ' . 'FROM ' . $this->db->prefix('bb_posts') . ' AS p' . ' ' . 'LEFT JOIN ' . $this->db->prefix('bb_posts_text') . ' ' . 'AS t ON t.post_id = p.post_id';
        // LEFT JOIN
        $sql .= $join;
        // WHERE
        if (isset($criteria) && is_subclass_of($criteria, 'CriteriaElement')) {
            $sql .= ' ' . $criteria->renderWhere();
        }
        if (!$result = $this->db->query($sql)) {
            //            xoops_error($this->db->error().'<br>'.$sql);
            return null;
        }
        $myrow = $this->db->fetchArray($result);
        $count = $myrow['count'];

        return $count;
    }
    // END irmtfan enhance getPostCount when there is join (read_mode = 2)

    /*
     *@TODO: combining viewtopic.php
     */
    /**
     * @param  null $criteria
     * @param  int  $limit
     * @param  int  $start
     * @param  null $join
     * @return array
     */
    public function &getPostsByLimit($criteria = null, $limit = 1, $start = 0, $join = null)
    {
        $ret = [];
        $sql = 'SELECT p.*, t.* ' . 'FROM ' . $this->db->prefix('bb_posts') . ' AS p ' . 'LEFT JOIN ' . $this->db->prefix('bb_posts_text') . ' AS t ON t.post_id = p.post_id';
        if (!empty($join)) {
            $sql .= (' ' === substr($join, 0, 1)) ? $join : ' ' . $join;
        }
        if (isset($criteria) && is_subclass_of($criteria, 'CriteriaElement')) {
            $sql .= ' ' . $criteria->renderWhere();
            if ('' !== $criteria->getSort()) {
                $sql .= ' ORDER BY ' . $criteria->getSort() . ' ' . $criteria->getOrder();
            }
        }
        $result = $this->db->query($sql, (int)$limit, (int)$start);
        if (!$result) {
            //            xoops_error($this->db->error());
            return $ret;
        }
        while (false !== ($myrow = $this->db->fetchArray($result))) {
            $post = $this->create(false);
            $post->assignVars($myrow);
            $ret[$myrow['post_id']] = $post;
            unset($post);
        }

        return $ret;
    }

    /**
     * @return bool
     */
    public function synchronization()
    {
        //      $this->cleanOrphan();
        return true;
    }

    /**
     * clean orphan items from database
     *
     * @return bool true on success
     */
    public function cleanOrphan()
    {
        $this->deleteAll(new \Criteria('post_time', 0), true, true);
        parent::cleanOrphan($this->db->prefix('bb_topics'), 'topic_id');
        parent::cleanOrphan($this->db->prefix('bb_posts_text'), 'post_id');

        if ($this->mysql_major_version() >= 4) { /* for MySQL 4.1+ */
            $sql = 'DELETE FROM ' . $this->db->prefix('bb_posts_text') . ' ' . 'WHERE (post_id NOT IN ( SELECT DISTINCT post_id FROM ' . $this->table . ') )';
        } else { /* for 4.0+ */
            /* */
            $sql = 'DELETE ' . $this->db->prefix('bb_posts_text') . ' FROM ' . $this->db->prefix('bb_posts_text') . ' ' . 'LEFT JOIN ' . $this->table . ' AS aa ON ' . $this->db->prefix('bb_posts_text') . '.post_id = aa.post_id ' . ' ' . 'WHERE (aa.post_id IS NULL)';
            /* */
            // Alternative for 4.1+
            /*
            $sql = "DELETE bb FROM ".$this->db->prefix("bb_posts_text")." AS bb" . " "
                       . "LEFT JOIN ".$this->table." AS aa ON bb.post_id = aa.post_id " . " "
                       . "WHERE (aa.post_id IS NULL)";
            */
        }
        if (!$result = $this->db->queryF($sql)) {
            //            xoops_error($this->db->error());
            return false;
        }

        return true;
    }

    /**
     * clean expired objects from database
     *
     * @param  int $expire time limit for expiration
     * @return bool true on success
     */
    public function cleanExpires($expire = 0)
    {
        // irmtfan if 0 no cleanup look include/plugin.php
        if (!func_num_args()) {
            $newbbConfig = newbb_load_config();
            $expire      = isset($newbbConfig['pending_expire']) ? (int)$newbbConfig['pending_expire'] : 7;
            $expire      = $expire * 24 * 3600; // days to seconds
        }
        if (empty($expire)) {
            return false;
        }
        $crit_expire = new \CriteriaCompo(new \Criteria('approved', 0, '<='));
        //        if (!empty($expire)) {
        $crit_expire->add(new \Criteria('post_time', time() - (int)$expire, '<'));

        //        }
        return $this->deleteAll($crit_expire, true/*, true*/);
    }
}
