<?php declare(strict_types=1);

/**
 * Newbb module
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       XOOPS Project (https://xoops.org)
 * @license         GNU GPL 2.0 or later (https://www.gnu.org/licenses/gpl-2.0.html)
 * @since           4.0
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 */

use XoopsModules\Newbb;
use XoopsModules\Xoopspoll;

/**
 * Class post
 */
class Post extends \XoopsObject
{
    private $post_id;
    private $topic_id;
    private $forum_id;
    private $post_time;
    private $poster_ip;
    private $poster_name;
    private $subject;
    private $pid;
    private $dohtml;
    private $dosmiley;
    private $doxcode;
    private $doimage;
    private $dobr;
    private $uid;
    private $icon;
    private $attachsig;
    private $approved;
    private $post_karma;
    private $require_reply;
    private $attachment;
    private $post_text;
    private $post_edit;

    public $attachment_array = [];

    /**
     * Post constructor.
     */
    public function __construct()
    {
        //$this->ArtObject("bb_posts");
        $this->initVar('post_id', XOBJ_DTYPE_INT);
        $this->initVar('topic_id', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('forum_id', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('post_time', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('poster_ip', XOBJ_DTYPE_INT, 0);
        $this->initVar('poster_name', XOBJ_DTYPE_TXTBOX, '');
        $this->initVar('subject', XOBJ_DTYPE_TXTBOX, '', true);
        $this->initVar('pid', XOBJ_DTYPE_INT, 0);
        $this->initVar('dohtml', XOBJ_DTYPE_INT, 0);
        $this->initVar('dosmiley', XOBJ_DTYPE_INT, 1);
        $this->initVar('doxcode', XOBJ_DTYPE_INT, 1);
        $this->initVar('doimage', XOBJ_DTYPE_INT, 1);
        $this->initVar('dobr', XOBJ_DTYPE_INT, 1);
        $this->initVar('uid', XOBJ_DTYPE_INT, 1);
        $this->initVar('icon', XOBJ_DTYPE_TXTBOX, '');
        $this->initVar('attachsig', XOBJ_DTYPE_INT, 0);
        $this->initVar('approved', XOBJ_DTYPE_INT, 1);
        $this->initVar('post_karma', XOBJ_DTYPE_INT, 0);
        $this->initVar('require_reply', XOBJ_DTYPE_INT, 0);
        $this->initVar('attachment', XOBJ_DTYPE_TXTAREA, '');
        $this->initVar('post_text', XOBJ_DTYPE_TXTAREA, '');
        $this->initVar('post_edit', XOBJ_DTYPE_TXTAREA, '');
    }

    // ////////////////////////////////////////////////////////////////////////////////////
    // attachment functions    TODO: there should be a file/attachment management class

    /**
     * @return array|mixed|null
     */
    public function getAttachment()
    {
        if (count($this->attachment_array)) {
            return $this->attachment_array;
        }
        $attachment = $this->getVar('attachment');
        if (empty($attachment)) {
            $this->attachment_array = null;
        } else {
            $this->attachment_array = @unserialize(base64_decode($attachment, true));
        }

        return $this->attachment_array;
    }

    /**
     * @param $attach_key
     * @return bool
     */
    public function incrementDownload($attach_key)
    {
        if (!$attach_key) {
            return false;
        }
        $this->attachment_array[(string)$attach_key]['num_download']++;

        return $this->attachment_array[(string)$attach_key]['num_download'];
    }

    /**
     * @return bool
     */
    public function saveAttachment()
    {
        $attachment_save = '';
        if ($this->attachment_array && is_array($this->attachment_array)) {
            $attachment_save = base64_encode(serialize($this->attachment_array));
        }
        $this->setVar('attachment', $attachment_save);
        $sql = 'UPDATE ' . $GLOBALS['xoopsDB']->prefix('bb_posts') . ' SET attachment=' . $GLOBALS['xoopsDB']->quoteString($attachment_save) . ' WHERE post_id = ' . $this->getVar('post_id');
        if (!$result = $GLOBALS['xoopsDB']->queryF($sql)) {
            //xoops_error($GLOBALS['xoopsDB']->error());
            return false;
        }

        return true;
    }

    /**
     * @param null $attach_array
     * @return bool
     */
    public function deleteAttachment($attach_array = null)
    {
        /** @var Xoopspoll\Helper $helper */
        $helper = Xoopspoll\Helper::getInstance();

        $attach_old = $this->getAttachment();
        if (!is_array($attach_old) || count($attach_old) < 1) {
            return true;
        }
        $this->attachment_array = [];

        if (null === $attach_array) {
            $attach_array = array_keys($attach_old);
        } // to delete all!
        if (!is_array($attach_array)) {
            $attach_array = [$attach_array];
        }

        foreach ($attach_old as $key => $attach) {
            if (in_array($key, $attach_array, true)) {
                @unlink(XOOPS_ROOT_PATH . '/' . $helper->getConfig('dir_attachments') . '/' . $attach['name_saved']);
                @unlink(XOOPS_ROOT_PATH . '/' . $helper->getConfig('dir_attachments') . '/thumbs/' . $attach['name_saved']); // delete thumbnails
                continue;
            }
            $this->attachment_array[$key] = $attach;
        }
        $attachment_save = '';
        if ($this->attachment_array && is_array($this->attachment_array)) {
            $attachment_save = base64_encode(serialize($this->attachment_array));
        }
        $this->setVar('attachment', $attachment_save);

        return true;
    }

    /**
     * @param string $name_saved
     * @param string $name_display
     * @param string $mimetype
     * @param int    $num_download
     * @return bool
     */
    public function setAttachment($name_saved = '', $name_display = '', $mimetype = '', $num_download = 0)
    {
        static $counter = 0;
        $this->attachment_array = $this->getAttachment();
        if ($name_saved) {
            $key                          = (string)(time() + $counter++);
            $this->attachment_array[$key] = [
                'name_saved'   => $name_saved,
                'name_display' => $name_display ?? $name_saved,
                'mimetype'     => $mimetype,
                'num_download' => isset($num_download) ? (int)$num_download : 0,
            ];
        }
        $attachment_save = null;
        if (is_array($this->attachment_array)) {
            $attachment_save = base64_encode(serialize($this->attachment_array));
        }
        $this->setVar('attachment', $attachment_save);

        return true;
    }

    /**
     * TODO: refactor
     * @param bool $asSource
     * @return string
     */
    public function displayAttachment($asSource = false)
    {
        global $xoopsModule;
        /** @var Xoopspoll\Helper $helper */
        $helper = Xoopspoll\Helper::getInstance();

        $post_attachment = '';
        $attachments     = $this->getAttachment();
        if ($attachments && is_array($attachments)) {
            $iconHandler = newbb_getIconHandler();
            $mime_path   = $iconHandler->getPath('mime');
            require_once XOOPS_ROOT_PATH . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/include/functions.image.php';
            $image_extensions = ['jpg', 'jpeg', 'gif', 'png', 'bmp']; // need improve !!!
            $post_attachment  .= '<br><strong>' . _MD_ATTACHMENT . '</strong>:';
            $post_attachment  .= '<br><hr size="1" noshade="noshade"><br>';
            foreach ($attachments as $key => $att) {
                $file_extension = ltrim(mb_strrchr($att['name_saved'], '.'), '.');
                $filetype       = $file_extension;
                if (file_exists(XOOPS_ROOT_PATH . '/' . $mime_path . '/' . $filetype . '.gif')) {
                    $icon_filetype = XOOPS_URL . '/' . $mime_path . '/' . $filetype . '.gif';
                } else {
                    $icon_filetype = XOOPS_URL . '/' . $mime_path . '/unknown.gif';
                }
                $file_size = @filesize(XOOPS_ROOT_PATH . '/' . $helper->getConfig('dir_attachments') . '/' . $att['name_saved']);
                $file_size = number_format($file_size / 1024, 2) . ' KB';
                if (in_array(mb_strtolower($file_extension), $image_extensions, true) && $helper->getConfig('media_allowed')) {
                    $post_attachment .= '<br><img src="' . $icon_filetype . '" alt="' . $filetype . '"><strong>&nbsp; ' . $att['name_display'] . '</strong> <small>(' . $file_size . ')</small>';
                    $post_attachment .= '<br>' . newbb_attachmentImage($att['name_saved']);
                    $isDisplayed     = true;
                } else {
                    $post_attachment .= '<a href="'
                                        . XOOPS_URL
                                        . '/modules/'
                                        . $xoopsModule->getVar('dirname', 'n')
                                        . '/dl_attachment.php?attachid='
                                        . $key
                                        . '&amp;post_id='
                                        . $this->getVar('post_id')
                                        . '"> <img src="'
                                        . $icon_filetype
                                        . '" alt="'
                                        . $filetype
                                        . '"> '
                                        . $att['name_display']
                                        . '</a> '
                                        . _MD_FILESIZE
                                        . ': '
                                        . $file_size
                                        . '; '
                                        . _MD_HITS
                                        . ': '
                                        . $att['num_download'];
                }
                $post_attachment .= '<br>';
            }
        }

        return $post_attachment;
    }

    // attachment functions
    // ////////////////////////////////////////////////////////////////////////////////////

    /**
     * @param string $poster_name
     * @return bool
     */
    public function setPostEdit($poster_name = '')
    {
        global $xoopsUser;
        /** @var Xoopspoll\Helper $helper */
        $helper = Xoopspoll\Helper::getInstance();

        if (empty($helper->getConfig('recordedit_timelimit'))
            || (time() - $this->getVar('post_time')) < $helper->getConfig('recordedit_timelimit') * 60
            || $this->getVar('approved') < 1) {
            return true;
        }
        if (is_object($xoopsUser) && $xoopsUser->isActive()) {
            if ($helper->getConfig('show_realname') && $xoopsUser->getVar('name')) {
                $edit_user = $xoopsUser->getVar('name');
            } else {
                $edit_user = $xoopsUser->getVar('uname');
            }
        }
        $post_edit              = [];
        $post_edit['edit_user'] = $edit_user; // The proper way is to store uid instead of name. However, to save queries when displaying, the current way is ok.
        $post_edit['edit_time'] = time();

        $post_edits = $this->getVar('post_edit');
        if (!empty($post_edits)) {
            $post_edits = unserialize(base64_decode($post_edits, true));
        }
        if (!is_array($post_edits)) {
            $post_edits = [];
        }
        $post_edits[] = $post_edit;
        $post_edit    = base64_encode(serialize($post_edits));
        unset($post_edits);
        $this->setVar('post_edit', $post_edit);

        return true;
    }

    /**
     * @return bool|string
     */
    public function displayPostEdit()
    {
        global $myts;
        /** @var Xoopspoll\Helper $helper */
        $helper = Xoopspoll\Helper::getInstance();

        if (empty($helper->getConfig('recordedit_timelimit'))) {
            return false;
        }

        $post_edit  = '';
        $post_edits = $this->getVar('post_edit');
        if (!empty($post_edits)) {
            $post_edits = unserialize(base64_decode($post_edits, true));
        }
        if (!isset($post_edits) || !is_array($post_edits)) {
            $post_edits = [];
        }
        if ($post_edits && is_array($post_edits)) {
            foreach ($post_edits as $postedit) {
                $edit_time = (int)$postedit['edit_time'];
                $edit_user = ($postedit['edit_user']);
                $post_edit .= _MD_EDITEDBY . ' ' . $edit_user . ' ' . _MD_ON . ' ' . formatTimestamp($edit_time) . '<br>';
            }
        }

        return $post_edit;
    }

    /**
     * @return array
     */
    public function &getPostBody()
    {
        global $xoopsConfig, $xoopsUser, $myts;
        /** @var Xoopspoll\Helper $helper */
        $helper = Xoopspoll\Helper::getInstance();

        require_once XOOPS_ROOT_PATH . '/modules/newbb/include/functions.user.php';
        require_once XOOPS_ROOT_PATH . '/modules/newbb/include/functions.render.php';

        $uid          = is_object($xoopsUser) ? $xoopsUser->getVar('uid') : 0;
        $karmaHandler = Newbb\Helper::getInstance()->getHandler('Karma');
        $user_karma   = $karmaHandler->getUserKarma();

        $post               = [];
        $post['attachment'] = false;
        $post_text          = &newbb_displayTarea($this->vars['post_text']['value'], $this->getVar('dohtml'), $this->getVar('dosmiley'), $this->getVar('doxcode'), $this->getVar('doimage'), $this->getVar('dobr'));
        if (newbb_isAdmin($this->getVar('forum_id')) || $this->checkIdentity()) {
            $post['text'] = $post_text . '<br>' . $this->displayAttachment();
        } elseif ($helper->getConfig('enable_karma') && $this->getVar('post_karma') > $user_karma) {
            $post['text'] = sprintf(_MD_KARMA_REQUIREMENT, $user_karma, $this->getVar('post_karma'));
        } elseif ($helper->getConfig('allow_require_reply') && $this->getVar('require_reply')
                  && (!$uid
                      || !isset($viewtopic_users[$uid]))) {
            $post['text'] = _MD_REPLY_REQUIREMENT;
        } else {
            $post['text'] = $post_text . '<br>' . $this->displayAttachment();
        }
        /** @var \XoopsMemberHandler $memberHandler */
        $memberHandler = xoops_getHandler('member');
        $eachposter    = $memberHandler->getUser($this->getVar('uid'));
        if (is_object($eachposter) && $eachposter->isActive()) {
            if ($helper->getConfig('show_realname') && $eachposter->getVar('name')) {
                $post['author'] = $eachposter->getVar('name');
            } else {
                $post['author'] = $eachposter->getVar('uname');
            }
            unset($eachposter);
        } else {
            $post['author'] = $this->getVar('poster_name') ?: $xoopsConfig['anonymous'];
        }

        $post['subject'] = newbb_htmlspecialchars($this->vars['subject']['value']);

        $post['date'] = $this->getVar('post_time');

        return $post;
    }

    /**
     * @return bool
     */
    public function isTopic()
    {
        return !$this->getVar('pid');
    }

    /**
     * @param string $action_tag
     * @return bool
     */
    public function checkTimelimit($action_tag = 'edit_timelimit')
    {
        require_once XOOPS_ROOT_PATH . '/modules/newbb/include/functions.config.php';
        $newbb_config = newbb_loadConfig();
        if (empty($newbb_config['edit_timelimit'])) {
            return true;
        }

        return ($this->getVar('post_time') > time() - $newbb_config[$action_tag] * 60);
    }

    /**
     * @param int $uid
     * @return bool
     */
    public function checkIdentity($uid = -1)
    {
        global $xoopsUser;

        $uid = ($uid > -1) ? $uid : (is_object($xoopsUser) ? $xoopsUser->getVar('uid') : 0);
        if ($this->getVar('uid') > 0) {
            $user_ok = $uid == $this->getVar('uid');
        } else {
            static $user_ip;
            if (!isset($user_ip)) {
                $user_ip = newbb_getIP();
            }
            $user_ok = $user_ip == $this->getVar('poster_ip');
        }

        return $user_ok;
    }

    // TODO: cleaning up and merge with post hanldings in viewpost.php

    /**
     * @param $isadmin
     * @return array
     */
    public function showPost($isadmin)
    {
        global $xoopsConfig, $xoopsModule, $xoopsUser, $myts;
        /** @var Xoopspoll\Helper $helper */
        $helper = Xoopspoll\Helper::getInstance();

        global $forumUrl, $forumImage;
        global $viewtopic_users, $viewtopic_posters, $forum_obj, $topic_obj, $online, $user_karma, $viewmode, $order, $start, $total_posts, $topic_status;
        static $post_NO = 0;
        static $name_anonymous;

        if (!isset($name_anonymous)) {
            $name_anonymous = htmlspecialchars($GLOBALS['xoopsConfig']['anonymous'], ENT_QUOTES | ENT_HTML5);
        }

        require_once XOOPS_ROOT_PATH . '/modules/newbb/include/functions.time.php';
        require_once XOOPS_ROOT_PATH . '/modules/newbb/include/functions.render.php';

        $post_id  = $this->getVar('post_id');
        $topic_id = $this->getVar('topic_id');
        $forum_id = $this->getVar('forum_id');

        $query_vars              = ['status', 'order', 'start', 'mode', 'viewmode'];
        $query_array             = [];
        $query_array['topic_id'] = "topic_id={$topic_id}";
        foreach ($query_vars as $var) {
            if (!empty($_GET[$var])) {
                $query_array[$var] = "{$var}={$_GET[$var]}";
            }
        }
        $page_query = htmlspecialchars(implode('&', array_values($query_array)), ENT_QUOTES | ENT_HTML5);

        $uid = is_object($xoopsUser) ? $xoopsUser->getVar('uid') : 0;

        ++$post_NO;
        if ('desc' === mb_strtolower($order)) {
            $post_no = $total_posts - ($start + $post_NO) + 1;
        } else {
            $post_no = $start + $post_NO;
        }

        if ($isadmin || $this->checkIdentity()) {
            $post_text       = $this->getVar('post_text');
            $post_attachment = $this->displayAttachment();
        } elseif ($helper->getConfig('enable_karma') && $this->getVar('post_karma') > $user_karma) {
            $post_text       = "<div class='karma'>" . sprintf(_MD_KARMA_REQUIREMENT, $user_karma, $this->getVar('post_karma')) . '</div>';
            $post_attachment = '';
        } elseif ($helper->getConfig('allow_require_reply') && $this->getVar('require_reply')
                  && (!$uid
                      || !in_array($uid, $viewtopic_posters, true))) {
            $post_text       = "<div class='karma'>" . _MD_REPLY_REQUIREMENT . '</div>';
            $post_attachment = '';
        } else {
            $post_text       = $this->getVar('post_text');
            $post_attachment = $this->displayAttachment();
        }
        if (isset($viewtopic_users[$this->getVar('uid')])) {
            $poster = $viewtopic_users[$this->getVar('uid')];
        } else {
            $name   = ($post_name = $this->getVar('poster_name')) ? $post_name : $name_anonymous;
            $poster = [
                'poster_uid' => 0,
                'name'       => $name,
                'link'       => $name,
            ];
        }

        $posticon = $this->getVar('icon');
        if ($posticon) {
            $post_image = '<a name="' . $post_id . '"><img src="' . XOOPS_URL . '/images/subject/' . $posticon . '" alt=""></a>';
        } else {
            $post_image = '<a name="' . $post_id . '"><img src="' . XOOPS_URL . '/images/icons/posticon.gif" alt=""></a>';
        }

        $thread_buttons = [];

        if ($GLOBALS['xoopsModuleConfig']['enable_permcheck']) {
            /** @var Newbb\TopicHandler $topicHandler */
            $topicHandler = Newbb\Helper::getInstance()->getHandler('Topic');
            $topic_status = $topic_obj->getVar('topic_status');
            if ($topicHandler->getPermission($forum_id, $topic_status, 'edit')) {
                $edit_ok = ($isadmin || ($this->checkIdentity() && $this->checkTimelimit('edit_timelimit')));

                if ($edit_ok) {
                    $thread_buttons['edit']['image'] = newbb_displayImage('p_edit', _EDIT);
                    $thread_buttons['edit']['link']  = "edit.php?{$page_query}";
                    $thread_buttons['edit']['name']  = _EDIT;
                }
            }

            if ($topicHandler->getPermission($forum_id, $topic_status, 'delete')) {
                $delete_ok = ($isadmin || ($this->checkIdentity() && $this->checkTimelimit('delete_timelimit')));

                if ($delete_ok) {
                    $thread_buttons['delete']['image'] = newbb_displayImage('p_delete', _DELETE);
                    $thread_buttons['delete']['link']  = "delete.php?{$page_query}";
                    $thread_buttons['delete']['name']  = _DELETE;
                }
            }
            if ($topicHandler->getPermission($forum_id, $topic_status, 'reply')) {
                $thread_buttons['reply']['image'] = newbb_displayImage('p_reply', _MD_REPLY);
                $thread_buttons['reply']['link']  = "reply.php?{$page_query}";
                $thread_buttons['reply']['name']  = _MD_REPLY;

                $thread_buttons['quote']['image'] = newbb_displayImage('p_quote', _MD_QUOTE);
                $thread_buttons['quote']['link']  = "reply.php?{$page_query}&amp;quotedac=1";
                $thread_buttons['quote']['name']  = _MD_QUOTE;
            }
        } else {
            $thread_buttons['edit']['image'] = newbb_displayImage('p_edit', _EDIT);
            $thread_buttons['edit']['link']  = "edit.php?{$page_query}";
            $thread_buttons['edit']['name']  = _EDIT;

            $thread_buttons['delete']['image'] = newbb_displayImage('p_delete', _DELETE);
            $thread_buttons['delete']['link']  = "delete.php?{$page_query}";
            $thread_buttons['delete']['name']  = _DELETE;

            $thread_buttons['reply']['image'] = newbb_displayImage('p_reply', _MD_REPLY);
            $thread_buttons['reply']['link']  = "reply.php?{$page_query}";
            $thread_buttons['reply']['name']  = _MD_REPLY;
        }

        if (!$isadmin && $helper->getConfig('reportmod_enabled')) {
            $thread_buttons['report']['image'] = newbb_displayImage('p_report', _MD_REPORT);
            $thread_buttons['report']['link']  = "report.php?{$page_query}";
            $thread_buttons['report']['name']  = _MD_REPORT;
        }

        $thread_action = [];

        $post = [
            'post_id'         => $post_id,
            'post_parent_id'  => $this->getVar('pid'),
            'post_date'       => newbb_formatTimestamp($this->getVar('post_time')),
            'post_image'      => $post_image,
            'post_title'      => $this->getVar('subject'),
            'post_text'       => $post_text,
            'post_attachment' => $post_attachment,
            'post_edit'       => $this->displayPostEdit(),
            'post_no'         => $post_no,
            'post_signature'  => $this->getVar('attachsig') ? @$poster['signature'] : '',
            'poster_ip'       => ($isadmin && $helper->getConfig('show_ip')) ? long2ip($this->getVar('poster_ip')) : '',
            'thread_action'   => $thread_action,
            'thread_buttons'  => $thread_buttons,
            'poster'          => $poster,
        ];

        unset($thread_buttons, $eachposter);

        return $post;
    }
}

/**
 * Class PostHandler
 */
class PostHandler extends \XoopsPersistableObjectHandler
{
    /**
     * @param null|\XoopsDatabase $db
     */
    public function __construct(\XoopsDatabase $db = null)
    {
        parent::__construct($db, 'bb_posts', 'Post', 'post_id', 'subject');
    }

    /**
     * @param mixed|null $id
     * @param null       $fields
     * @return null|\XoopsObject
     */
    public function get($id = null, $fields = null)
    {
        $id    = (int)$id;
        $post  = null;
        $sql   = 'SELECT p.*, t.* FROM ' . $this->db->prefix('bb_posts') . ' p LEFT JOIN ' . $this->db->prefix('bb_posts_text') . ' t ON p.post_id=t.post_id WHERE p.post_id=' . $id;
        $array = $this->db->fetchArray($this->db->query($sql));
        if ($array) {
            $post = $this->create(false);
            $post->assignVars($array);
        }

        return $post;
    }

    /**
     * @param int $topic_id
     * @param int $limit
     * @param int $approved
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
        if ($result) {
            while (false !== ($myrow = $this->db->fetchArray($result))) {
                $post = $this->create(false);
                $post->assignVars($myrow);

                $ret[$myrow['post_id']] = $post;
                unset($post);
            }
        }

        return $ret;
    }

    /**
     * @param $post
     * @return mixed
     */
    public function getPostForPDF($post)
    {
        return $post->getPostBody(true);
    }

    /**
     * @param $post
     * @return mixed
     */
    public function getPostForPrint($post)
    {
        return $post->getPostBody();
    }

    /**
     * @param \XoopsObject  $post
     * @param bool  $force
     * @return bool
     */
    public function approve(&$post, $force = false)
    {
        if (empty($post)) {
            return false;
        }
        if (is_numeric($post)) {
            $post = $this->get($post);
        } else {
            $post->unsetNew();
        }
        $post_id     = $post->getVar('post_id');
        $wasApproved = $post->getVar('approved');
        if (empty($force) && $wasApproved) {
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
            /** @var \XoopsMemberHandler $memberHandler */
            $memberHandler = xoops_getHandler('member');
            $poster        = $memberHandler->getUser($post->getVar('uid'));
            if (is_object($poster) && $post->getVar('uid') == $poster->getVar('uid')) {
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
     * @param \XoopsObject $post
     * @param bool $force
     * @return bool
     */
    public function insert(\XoopsObject $post, $force = true)
    {
        global $xoopsUser;

        // Set the post time
        // The time should be "publish" time. To be adjusted later
        if (!$post->getVar('post_time')) {
            $post->setVar('post_time', time());
        }

        /** @var Newbb\TopicHandler $topicHandler */
        $topicHandler = Newbb\Helper::getInstance()->getHandler('Topic');
        // Verify the topic ID
        $topic_id = $post->getVar('topic_id');
        if ($topic_id) {
            $topic_obj = $topicHandler->get($topic_id);
            // Invalid topic OR the topic is no approved and the post is not top post
            if (!$topic_obj//    || (!$post->isTopic() && $topic_obj->getVar("approved") < 1)
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
            }
            $text_obj->setVar('post_id', $post_id);
            if (!$textHandler->insert($text_obj, $force)) {
                $this->delete($post);
                $post->setErrors('post text insert error');

                return false;
            }
            if ($post->getVar('approved') > 0) {
                $this->approve($post, true);
            }
            $post->setVar('post_id', $post_id);
        } else {
            if ($post->isTopic()) {
                if ($post->getVar('subject') != $topic_obj->getVar('topic_title')) {
                    $topic_obj->setVar('topic_title', $post->getVar('subject', 'n'));
                }
                if ($post->getVar('approved') != $topic_obj->getVar('approved')) {
                    $topic_obj->setVar('approved', $post->getVar('approved'));
                }
                if (!$result = $topicHandler->insert($topic_obj, $force)) {
                    $post->setErrors('update topic error');

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
                return false;
            }
            if (!$textHandler->insert($text_obj, $force)) {
                $post->setErrors('update post text error');

                return false;
            }
        }

        return $post->getVar('post_id');
    }

    /**
     * @param \XoopsObject $post
     * @param bool $isDeleteOne
     * @param bool $force
     * @return bool
     */
    public function delete(\XoopsObject $post, $isDeleteOne = true, $force = false)
    {
        if (!is_object($post) || 0 == $post->getVar('post_id')) {
            return false;
        }
        if ($isDeleteOne) {
            if ($post->isTopic()) {
                $criteria = new \CriteriaCompo(new \Criteria('topic_id', $post->getVar('topic_id')));
                $criteria->add(new \Criteria('approved', 1));
                $criteria->add(new \Criteria('pid', 0, '>'));
                if ($this->getPostCount($criteria) > 0) {
                    return false;
                }
            }

            return $this->_delete($post, $force);
        }
        require_once XOOPS_ROOT_PATH . '/class/xoopstree.php';
        $mytree = new \XoopsTree($this->db->prefix('bb_posts'), 'post_id', 'pid');
        $arr    = $mytree->getAllChild($post->getVar('post_id'));
        for ($i = 0, $iMax = count($arr); $i < $iMax; ++$i) {
            $childpost = $this->create(false);
            $childpost->assignVars($arr[$i]);
            $this->_delete($childpost, $force);
            unset($childpost);
        }
        $this->_delete($post, $force);

        return true;
    }

    /**
     * @param       $post
     * @param bool  $force
     * @return bool
     */
    public function _delete($post, $force = false)
    {
        global $xoopsModule;

        if (!is_object($post) || 0 == $post->getVar('post_id')) {
            return false;
        }

        /* Set active post as deleted */
        if ($post->getVar('approved') > 0 && empty($force)) {
            $sql = 'UPDATE ' . $this->db->prefix('bb_posts') . ' SET approved = -1 WHERE post_id = ' . $post->getVar('post_id');
            if (!$result = $this->db->queryF($sql)) {
            }
            /* delete pending post directly */
        } else {
            $sql = sprintf('DELETE FROM `%s` WHERE post_id = %u', $this->db->prefix('bb_posts'), $post->getVar('post_id'));
            if (!$result = $this->db->queryF($sql)) {
                $post->setErrors('delte post error: ' . $sql);

                return false;
            }
            $post->deleteAttachment();

            $sql = sprintf('DELETE FROM `%s` WHERE post_id = %u', $this->db->prefix('bb_posts_text'), $post->getVar('post_id'));
            if (!$result = $this->db->queryF($sql)) {
                $post->setErrors('Could not remove post text: ' . $sql);

                return false;
            }
        }

        if ($post->isTopic()) {
            /** @var Newbb\TopicHandler $topicHandler */
            $topicHandler = Newbb\Helper::getInstance()->getHandler('Topic');
            $topic_obj    = $topicHandler->get($post->getVar('topic_id'));
            if ($topic_obj instanceof Newbb\Topic) {
                if (($topic_obj->getVar('approved') > 0) && empty($force)) {
                    $topiccount_toupdate = 1;
                    $topic_obj->setVar('approved', -1);
                    $topicHandler->insert($topic_obj);
                    xoops_notification_deletebyitem($xoopsModule->getVar('mid'), 'thread', $post->getVar('topic_id'));
                } else {
                    if ($topic_obj->getVar('approved') > 0) {
                        xoops_notification_deletebyitem($xoopsModule->getVar('mid'), 'thread', $post->getVar('topic_id'));
                    }
                    $poll_id = $topic_obj->getVar('poll_id');
                    if ($poll_id > 0) {
                        /** @var \XoopsModuleHandler $moduleHandler */
                        $moduleHandler      = xoops_getHandler('module');
                        $poll_moduleHandler = $moduleHandler->getByDirname('xoopspoll');
                        if (($poll_moduleHandler instanceof \XoopsModuleHandler) && $poll_moduleHandler->isactive()) {
                            $pollHandler = Xoopspoll\Helper::getInstance()->getHandler('Poll');
                            if (false !== $pollHandler->deleteAll(new \Criteria('poll_id', $poll_id, '='))) {
                                $optionHandler = Xoopspoll\Helper::getInstance()->getHandler('Option');
                                $optionHandler->deleteAll(new \Criteria('poll_id', $poll_id, '='));
                                $logHandler = Xoopspoll\Helper::getInstance()->getHandler('Log');
                                $logHandler->deleteAll(new \Criteria('poll_id', $poll_id, '='));
                                xoops_comment_delete($GLOBALS['xoopsModule']->getVar('mid'), $poll_id);
                            }
                        }
                    }
                }

                $sql = sprintf('DELETE FROM `%s` WHERE topic_id = %u', $this->db->prefix('bb_topics'), $post->getVar('topic_id'));
                if (!$result = $this->db->queryF($sql)) {
                    //xoops_error($this->db->error());
                }
                $sql = sprintf('DELETE FROM `%s` WHERE topic_id = %u', $this->db->prefix('bb_votedata'), $post->getVar('topic_id'));
                if (!$result = $this->db->queryF($sql)) {
                    //xoops_error($this->db->error());
                }
            }
        } else {
            $sql = 'UPDATE ' . $this->db->prefix('bb_topics') . ' t
                            LEFT JOIN ' . $this->db->prefix('bb_posts') . ' p ON p.topic_id = t.topic_id
                            SET t.topic_last_post_id = p.post_id
                            WHERE t.topic_last_post_id = ' . $post->getVar('post_id') . '
                                    AND p.post_id = (SELECT MAX(post_id) FROM ' . $this->db->prefix('bb_posts') . ' WHERE topic_id=t.topic_id)';
            if (!$result = $this->db->queryF($sql)) {
            }
        }

        $postcount_toupdate = $post->getVar('approved');

        if ($postcount_toupdate > 0) {
            // Update user stats
            if ($post->getVar('uid') > 0) {
                /** @var \XoopsMemberHandler $memberHandler */
                $memberHandler = xoops_getHandler('member');
                $poster        = $memberHandler->getUser($post->getVar('uid'));
                if (is_object($poster) && $post->getVar('uid') == $poster->getVar('uid')) {
                    $poster->setVar('posts', $poster->getVar('posts') - 1);
                    $res = $memberHandler->insertUser($poster, true);
                    unset($poster);
                }
            }

            $sql = 'UPDATE ' . $this->db->prefix('bb_posts') . ' SET pid = ' . $post->getVar('pid') . ' WHERE pid=' . $post->getVar('post_id');
            if (!$result = $this->db->queryF($sql)) {
                //xoops_error($this->db->error());
            }
        }

        return true;
    }

    /**
     * @param null $criteria
     * @return int
     */
    public function getPostCount($criteria = null)
    {
        return parent::getCount($criteria);
    }

    /*
     * TODO: combining viewtopic.php
     */

    /**
     * @param null $criteria
     * @param int  $limit
     * @param int  $start
     * @param null $join
     * @return array
     */
    public function &getPostsByLimit($criteria = null, $limit = 1, $start = 0, $join = null)
    {
        $ret = [];
        $sql = 'SELECT p.*, t.* ' . ' FROM ' . $this->db->prefix('bb_posts') . ' AS p' . ' LEFT JOIN ' . $this->db->prefix('bb_posts_text') . ' AS t ON t.post_id = p.post_id';
        if (!empty($join)) {
            $sql .= $join;
        }
        if (\is_object($criteria) && \is_subclass_of($criteria, \CriteriaElement::class)) {
            $sql .= ' ' . $criteria->renderWhere();
            if ('' != $criteria->getSort()) {
                $sql .= ' ORDER BY ' . $criteria->getSort() . ' ' . $criteria->getOrder();
            }
        }
        $result = $this->db->query($sql, (int)$limit, (int)$start);
        if ($result) {
            while (false !== ($myrow = $this->db->fetchArray($result))) {
                $post = $this->create(false);
                $post->assignVars($myrow);
                $ret[$myrow['post_id']] = $post;
                unset($post);
            }
        }

        return $ret;
    }

    /**
     * clean orphan items from database
     *
     * @return bool true on success
     */
    public function cleanOrphan()
    {
        global $xoopsDB;
        $this->deleteAll(new \Criteria('post_time', 0), true, true);
        parent::cleanOrphan($this->db->prefix('bb_topics'), 'topic_id');
        parent::cleanOrphan($this->db->prefix('bb_posts_text'), 'post_id');

        /* for MySQL 4.1+ */
        if (version_compare(mysqli_get_server_info($xoopsDB->conn), '4.1.0', 'ge')):
            $sql = 'DELETE FROM ' . $this->db->prefix('bb_posts_text') . " WHERE (post_id NOT IN ( SELECT DISTINCT post_id FROM {$this->table}) )";
        else:
            // for 4.0+

            $sql = 'DELETE ' . $this->db->prefix('bb_posts_text') . ' FROM ' . $this->db->prefix('bb_posts_text') . " LEFT JOIN {$this->table} AS aa ON " . $this->db->prefix('bb_posts_text') . '.post_id = aa.post_id ' . ' WHERE (aa.post_id IS NULL)';

            // Alternative for 4.1+
            /*
            $sql =     "DELETE bb FROM ".$this->db->prefix("bb_posts_text")." AS bb".
                    " LEFT JOIN ".$this->table." AS aa ON bb.post_id = aa.post_id ".
                    " WHERE (aa.post_id IS NULL)";
            */ endif;
        if (!$result = $this->db->queryF($sql)) {
            //xoops_error($this->db->error());
            return false;
        }

        return true;
    }

    /**
     * clean expired objects from database
     *
     * @param int $expire time limit for expiration
     * @return bool true on success
     */
    public function cleanExpires($expire = 0)
    {
        $crit_expire = new \CriteriaCompo(new \Criteria('approved', 0, '<='));
        //if (!empty($expire)) {
        $crit_expire->add(new \Criteria('post_time', time() - (int)$expire, '<'));

        //}
        return $this->deleteAll($crit_expire, true/*, true*/);
    }
}
