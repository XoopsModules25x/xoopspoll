<?php

namespace XoopsModules\Newbb;

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
 * @license      {@link https://www.gnu.org/licenses/gpl-2.0.html GNU GPL 2 or later}
 * @author      XOOPS Development Team, phppp (D.J., infomax@gmail.com)
 */

use Xmf\Request;
use XoopsModules\Newbb;

\defined('NEWBB_FUNCTIONS_INI') || require XOOPS_ROOT_PATH . '/modules/newbb/include/functions.ini.php';
newbb_load_object();

/**
 * Class Post
 */
class Post extends \XoopsObject
{
    //class Post extends \XoopsObject {
    public $attachment_array = [];

    /**
     * Post constructor.
     */
    public function __construct()
    {
        parent::__construct('bb_posts');
        $this->initVar('post_id', \XOBJ_DTYPE_INT);
        $this->initVar('topic_id', \XOBJ_DTYPE_INT, 0, true);
        $this->initVar('forum_id', \XOBJ_DTYPE_INT, 0, true);
        $this->initVar('post_time', \XOBJ_DTYPE_INT, 0, true);
        $this->initVar('poster_ip', \XOBJ_DTYPE_INT, 0);
        $this->initVar('poster_name', \XOBJ_DTYPE_TXTBOX, '');
        $this->initVar('subject', \XOBJ_DTYPE_TXTBOX, '', true);
        $this->initVar('pid', \XOBJ_DTYPE_INT, 0);
        $this->initVar('dohtml', \XOBJ_DTYPE_INT, 0);
        $this->initVar('dosmiley', \XOBJ_DTYPE_INT, 1);
        $this->initVar('doxcode', \XOBJ_DTYPE_INT, 1);
        $this->initVar('doimage', \XOBJ_DTYPE_INT, 1);
        $this->initVar('dobr', \XOBJ_DTYPE_INT, 1);
        $this->initVar('uid', \XOBJ_DTYPE_INT, 1);
        $this->initVar('icon', \XOBJ_DTYPE_TXTBOX, '');
        $this->initVar('attachsig', \XOBJ_DTYPE_INT, 0);
        $this->initVar('approved', \XOBJ_DTYPE_INT, 1);
        $this->initVar('post_karma', \XOBJ_DTYPE_INT, 0);
        $this->initVar('require_reply', \XOBJ_DTYPE_INT, 0);
        $this->initVar('attachment', \XOBJ_DTYPE_TXTAREA, '');
        $this->initVar('post_text', \XOBJ_DTYPE_TXTAREA, '');
        $this->initVar('post_edit', \XOBJ_DTYPE_TXTAREA, '');
    }

    // ////////////////////////////////////////////////////////////////////////////////////
    // attachment functions    TODO: there should be a file/attachment management class

    /**
     * @return array|mixed|null
     */
    public function getAttachment()
    {
        if (\count($this->attachment_array)) {
            return $this->attachment_array;
        }
        $attachment = $this->getVar('attachment');
        if (empty($attachment)) {
            $this->attachment_array = null;
        } else {
            $this->attachment_array = @\unserialize(\base64_decode($attachment, true));
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
        if ($this->attachment_array && \is_array($this->attachment_array)) {
            $attachment_save = \base64_encode(\serialize($this->attachment_array));
        }
        $this->setVar('attachment', $attachment_save);
        $sql = 'UPDATE ' . $GLOBALS['xoopsDB']->prefix('bb_posts') . ' SET attachment=' . $GLOBALS['xoopsDB']->quoteString($attachment_save) . ' WHERE post_id = ' . $this->getVar('post_id');
        if (!$result = $GLOBALS['xoopsDB']->queryF($sql)) {
            //xoops_error($GLOBALS["xoopsDB"]->error());
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
        $attach_old = $this->getAttachment();
        if (!\is_array($attach_old) || \count($attach_old) < 1) {
            return true;
        }
        $this->attachment_array = [];

        if (null === $attach_array) {
            $attach_array = \array_keys($attach_old);
        } // to delete all!
        if (!\is_array($attach_array)) {
            $attach_array = [$attach_array];
        }

        foreach ($attach_old as $key => $attach) {
            if (\in_array($key, $attach_array)) {
                @\unlink(XOOPS_ROOT_PATH . '/' . $GLOBALS['xoopsModuleConfig']['dir_attachments'] . '/' . $attach['name_saved']);
                @\unlink(XOOPS_ROOT_PATH . '/' . $GLOBALS['xoopsModuleConfig']['dir_attachments'] . '/thumbs/' . $attach['name_saved']); // delete thumbnails
                continue;
            }
            $this->attachment_array[$key] = $attach;
        }
        $attachment_save = '';
        if ($this->attachment_array && \is_array($this->attachment_array)) {
            $attachment_save = \base64_encode(\serialize($this->attachment_array));
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
            $key                          = (string)(\time() + $counter++);
            $this->attachment_array[$key] = [
                'name_saved'   => $name_saved,
                'name_display' => isset($name_display) ? $name_display : $name_saved,
                'mimetype'     => $mimetype,
                'num_download' => isset($num_download) ? (int)$num_download : 0,
            ];
        }
        $attachment_save = null;
        if (\is_array($this->attachment_array)) {
            $attachment_save = \base64_encode(\serialize($this->attachment_array));
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
        $post_attachment = '';
        $attachments     = $this->getAttachment();
        if ($attachments && \is_array($attachments)) {
            $iconHandler = newbb_getIconHandler();
            $mime_path   = $iconHandler->getPath('mime');
            require_once $GLOBALS['xoops']->path('modules/' . $GLOBALS['xoopsModule']->getVar('dirname', 'n') . '/include/functions.image.php');
            $image_extensions = ['jpg', 'jpeg', 'gif', 'png', 'bmp']; // need improve !!!
            $post_attachment  .= '<br><strong>' . _MD_ATTACHMENT . '</strong>:';
            $post_attachment  .= "<div style='margin: 1em 0; border-top: 1px solid;'></div>\n";
            //            $post_attachment .= '<br><hr style="height: 1px;" noshade="noshade"><br>';
            foreach ($attachments as $key => $att) {
                $file_extension = \ltrim(mb_strrchr($att['name_saved'], '.'), '.');
                $filetype       = $file_extension;
                if (\file_exists($GLOBALS['xoops']->path("{$mime_path}/{$filetype}.gif"))) {
                    $icon_filetype = $GLOBALS['xoops']->url("{$mime_path}/{$filetype}.gif");
                } else {
                    $icon_filetype = $GLOBALS['xoops']->url("{$mime_path}/unknown.gif");
                }
                $file_size = @\filesize($GLOBALS['xoops']->path($GLOBALS['xoopsModuleConfig']['dir_attachments'] . '/' . $att['name_saved']));
                $file_size = \number_format($file_size / 1024, 2) . ' KB';
                if ($GLOBALS['xoopsModuleConfig']['media_allowed']
                    && \in_array(mb_strtolower($file_extension), $image_extensions)) {
                    $post_attachment .= '<br><img src="' . $icon_filetype . '" alt="' . $filetype . '"><strong>&nbsp; ' . $att['name_display'] . '</strong> <small>(' . $file_size . ')</small>';
                    $post_attachment .= '<br>' . newbb_attachmentImage($att['name_saved']);
                    $isDisplayed     = true;
                } else {
                    if (empty($GLOBALS['xoopsModuleConfig']['show_userattach'])) {
                        $post_attachment .= "<a href='"
                                            . $GLOBALS['xoops']->url('/modules/' . $GLOBALS['xoopsModule']->getVar('dirname', 'n') . "/dl_attachment.php?attachid={$key}&amp;post_id=" . $this->getVar('post_id'))
                                            . "'> <img src='{$icon_filetype}' alt='{$filetype}'> {$att['name_display']}</a> "
                                            . _MD_FILESIZE
                                            . ": {$file_size}; "
                                            . _MD_HITS
                                            . ": {$att['num_download']}";
                    } elseif (($GLOBALS['xoopsUser'] instanceof \XoopsUser) && $GLOBALS['xoopsUser']->uid() > 0
                              && $GLOBALS['xoopsUser']->isActive()) {
                        $post_attachment .= "<a href='"
                                            . $GLOBALS['xoops']->url('/modules/' . $GLOBALS['xoopsModule']->getVar('dirname', 'n') . "/dl_attachment.php?attachid={$key}&amp;post_id=" . $this->getVar('post_id'))
                                            . "'> <img src='"
                                            . $icon_filetype
                                            . "' alt='{$filetype}'> {$att['name_display']}</a> "
                                            . _MD_FILESIZE
                                            . ": {$file_size}; "
                                            . _MD_HITS
                                            . ": {$att['num_download']}";
                    } else {
                        $post_attachment .= _MD_NEWBB_SEENOTGUEST;
                    }
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
     * @param string $post_editmsg
     * @return bool
     */
    public function setPostEdit($poster_name = '', $post_editmsg = '')
    {
        if ($this->getVar('approved') < 1
            || empty($GLOBALS['xoopsModuleConfig']['recordedit_timelimit'])
            || (\time() - $this->getVar('post_time')) < $GLOBALS['xoopsModuleConfig']['recordedit_timelimit'] * 60) {
            return true;
        }
        if (($GLOBALS['xoopsUser'] instanceof \XoopsUser) && $GLOBALS['xoopsUser']->isActive()) {
            if ($GLOBALS['xoopsModuleConfig']['show_realname'] && $GLOBALS['xoopsUser']->getVar('name')) {
                $edit_user = $GLOBALS['xoopsUser']->getVar('name');
            } else {
                $edit_user = $GLOBALS['xoopsUser']->getVar('uname');
            }
        }
        $post_edit              = [];
        $post_edit['edit_user'] = $edit_user; // The proper way is to store uid instead of name. However, to save queries when displaying, the current way is ok.
        $post_edit['edit_time'] = \time();
        $post_edit['edit_msg']  = $post_editmsg;

        $post_edits = $this->getVar('post_edit');
        if (!empty($post_edits)) {
            $post_edits = \unserialize(\base64_decode($post_edits, true));
        }
        if (!\is_array($post_edits)) {
            $post_edits = [];
        }
        $post_edits[] = $post_edit;
        $post_edit    = \base64_encode(\serialize($post_edits));
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

        if (empty($GLOBALS['xoopsModuleConfig']['recordedit_timelimit'])) {
            return false;
        }

        $post_edit  = '';
        $post_edits = $this->getVar('post_edit');
        if (!empty($post_edits)) {
            $post_edits = \unserialize(\base64_decode($post_edits, true));
        }
        if (!isset($post_edits) || !\is_array($post_edits)) {
            $post_edits = [];
        }
        if ($post_edits && \is_array($post_edits)) {
            foreach ($post_edits as $postedit) {
                $edit_time = (int)$postedit['edit_time'];
                $edit_user = ($postedit['edit_user']);
                $edit_msg  = !empty($postedit['edit_msg']) ? ($postedit['edit_msg']) : '';
                // Start irmtfan add option to do only the latest edit when do_latestedit=0 (Alfred)
                if (empty($GLOBALS['xoopsModuleConfig']['do_latestedit'])) {
                    $post_edit = '';
                }
                // End irmtfan add option to do only the latest edit when do_latestedit=0 (Alfred)
                // START hacked by irmtfan
                // display/save all edit records.
                $post_edit .= _MD_EDITEDBY . ' ' . $edit_user . ' ' . _MD_ON . ' ' . newbb_formatTimestamp($edit_time) . '<br>';
                // if reason is not empty
                if ('' !== $edit_msg) {
                    $post_edit .= \_MD_EDITEDMSG . ' ' . $edit_msg . '<br>';
                }
                // START hacked by irmtfan
            }
        }

        return $post_edit;
    }

    /**
     * @return array
     */
    public function &getPostBody()
    {
        global $myts;
        $GLOBALS['xoopsModuleConfig'] = newbb_load_config(); // irmtfan  load all newbb configs - newbb config in blocks activated in some modules like profile
        //        mod_loadFunctions('user', 'newbb');
        //        mod_loadFunctions('render', 'newbb');
        require_once \dirname(__DIR__) . '/include/functions.user.php';
        require_once \dirname(__DIR__) . '/include/functions.render.php';

        $uid          = ($GLOBALS['xoopsUser'] instanceof \XoopsUser) ? $GLOBALS['xoopsUser']->getVar('uid') : 0;
        $karmaHandler = Newbb\Helper::getInstance()->getHandler('Karma');
        $user_karma   = $karmaHandler->getUserKarma();

        $post               = [];
        $post['attachment'] = false;
        $post_text          = &newbb_displayTarea($this->vars['post_text']['value'], $this->getVar('dohtml'), $this->getVar('dosmiley'), $this->getVar('doxcode'), $this->getVar('doimage'), $this->getVar('dobr'));
        if (newbb_isAdmin($this->getVar('forum_id')) || $this->checkIdentity()) {
            $post['text'] = $post_text . '<br>' . $this->displayAttachment();
        } elseif ($GLOBALS['xoopsModuleConfig']['enable_karma'] && $this->getVar('post_karma') > $user_karma) {
            $post['text'] = \sprintf(_MD_KARMA_REQUIREMENT, $user_karma, $this->getVar('post_karma'));
        } elseif ($GLOBALS['xoopsModuleConfig']['allow_require_reply'] && $this->getVar('require_reply')
                  && (!$uid || !isset($viewtopic_users[$uid]))) {
            $post['text'] = _MD_REPLY_REQUIREMENT;
        } else {
            $post['text'] = $post_text . '<br>' . $this->displayAttachment();
        }
        $memberHandler = \xoops_getHandler('member');
        $eachposter    = $memberHandler->getUser($this->getVar('uid'));
        if (\is_object($eachposter) && $eachposter->isActive()) {
            if ($GLOBALS['xoopsModuleConfig']['show_realname'] && $eachposter->getVar('name')) {
                $post['author'] = $eachposter->getVar('name');
            } else {
                $post['author'] = $eachposter->getVar('uname');
            }
            unset($eachposter);
        } else {
            $post['author'] = $this->getVar('poster_name') ?: $GLOBALS['xoopsConfig']['anonymous'];
        }

        $post['subject'] = newbb_htmlspecialchars($this->vars['subject']['value']);
        $post['date']    = $this->getVar('post_time');

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
        $newbb_config = newbb_load_config();
        if (empty($newbb_config['edit_timelimit'])) {
            return true;
        }

        return ($this->getVar('post_time') > \time() - $newbb_config[$action_tag] * 60);
    }

    /**
     * @param int $uid
     * @return bool
     */
    public function checkIdentity($uid = -1)
    {
        //        $uid = ($uid > -1) ? $uid : (($GLOBALS['xoopsUser'] instanceof \XoopsUser) ? $GLOBALS['xoopsUser']->getVar('uid') : 0);
        if ($uid < 0 && $GLOBALS['xoopsUser'] instanceof \XoopsUser) {
            $uid = $GLOBALS['xoopsUser']->getVar('uid');
        } else {
            $uid = 0;
        }
        if ($this->getVar('uid') > 0) {
            $user_ok = $uid === $this->getVar('uid');
        } else {
            static $user_ip;
            if (!isset($user_ip)) {
                $user_ip = \XoopsUserUtility::getIP();
            }
            $user_ok = $user_ip === $this->getVar('poster_ip');
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
        global $myts;
        global $forumUrl, $forumImage;
        global $viewtopic_users, $viewtopic_posters, $forum_obj, $topic_obj, $online, $user_karma, $viewmode, $order, $start, $total_posts, $topic_status;
        static $post_NO = 0;
        static $name_anonymous;

        if (!isset($name_anonymous)) {
            $name_anonymous = htmlspecialchars($GLOBALS['xoopsConfig']['anonymous']);
        }

        //        mod_loadFunctions('time', 'newbb');
        //        mod_loadFunctions('render', 'newbb');
        //        mod_loadFunctions('text', 'newbb'); // irmtfan add text functions
        require_once \dirname(__DIR__) . '/include/functions.time.php';
        require_once \dirname(__DIR__) . '/include/functions.render.php';
        require_once \dirname(__DIR__) . '/include/functions.text.php';

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
        $page_query = \htmlspecialchars(\implode('&', \array_values($query_array)), \ENT_QUOTES | \ENT_HTML5);

        $uid = ($GLOBALS['xoopsUser'] instanceof \XoopsUser) ? $GLOBALS['xoopsUser']->getVar('uid') : 0;

        ++$post_NO;
        if ('desc' === mb_strtolower($order)) {
            $post_no = $total_posts - ($start + $post_NO) + 1;
        } else {
            $post_no = $start + $post_NO;
        }

        if ($isadmin || $this->checkIdentity()) {
            $post_text       = $this->getVar('post_text');
            $post_attachment = $this->displayAttachment();
        } elseif ($GLOBALS['xoopsModuleConfig']['enable_karma'] && $this->getVar('post_karma') > $user_karma) {
            $post_text       = "<div class='karma'>" . \sprintf(_MD_KARMA_REQUIREMENT, $user_karma, $this->getVar('post_karma')) . '</div>';
            $post_attachment = '';
        } elseif ($GLOBALS['xoopsModuleConfig']['allow_require_reply'] && $this->getVar('require_reply')
                  && (!$uid
                      || !\in_array($uid, $viewtopic_posters))) {
            $post_text       = "<div class='karma'>" . _MD_REPLY_REQUIREMENT . "</div>\n";
            $post_attachment = '';
        } else {
            $post_text       = $this->getVar('post_text');
            $post_attachment = $this->displayAttachment();
        }
        // START irmtfan add highlight feature
        // Hightlighting searched words
        $post_title = $this->getVar('subject');
        if (!empty($_GET['keywords']) && Request::hasVar('keywords', 'GET')) {
            $keywords   = htmlspecialchars(\trim(\urldecode($_GET['keywords'])));
            $post_text  = \newbb_highlightText($post_text, $keywords);
            $post_title = \newbb_highlightText($post_title, $keywords);
        }
        // END irmtfan add highlight feature
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
            $post_image = "<a name='{$post_id}'><img src='" . $GLOBALS['xoops']->url("images/subject/{$posticon}") . "' alt=''></a>";
        } else {
            $post_image = "<a name='{$post_id}'><img src='" . $GLOBALS['xoops']->url('images/icons/posticon.gif') . "' alt=''></a>";
        }

        $thread_buttons = [];
        $mod_buttons    = [];

        if (($this->getVar('uid') > 0)
            && $isadmin
            && (($GLOBALS['xoopsUser'] instanceof \XoopsUser)
                && $GLOBALS['xoopsUser']->getVar('uid') !== $this->getVar('uid'))) {
            $mod_buttons['bann']['image']    = newbb_displayImage('p_bann', _MD_SUSPEND_MANAGEMENT);
            $mod_buttons['bann']['link']     = $GLOBALS['xoops']->url('modules/' . $GLOBALS['xoopsModule']->getVar('dirname') . "/moderate.php?forum={$forum_id}&amp;fuid=" . $this->getVar('uid'));
            $mod_buttons['bann']['name']     = _MD_SUSPEND_MANAGEMENT;
            $thread_buttons['bann']['image'] = newbb_displayImage('p_bann', _MD_SUSPEND_MANAGEMENT);
            $thread_buttons['bann']['link']  = $GLOBALS['xoops']->url('modules/' . $GLOBALS['xoopsModule']->getVar('dirname') . "/moderate.php?forum={$forum_id}&amp;fuid=" . $this->getVar('uid'));
            $thread_buttons['bann']['name']  = _MD_SUSPEND_MANAGEMENT;
        }

        if ($GLOBALS['xoopsModuleConfig']['enable_permcheck']) {
            /** @var Newbb\TopicHandler $topicHandler */
            $topicHandler = Newbb\Helper::getInstance()->getHandler('Topic');
            $topic_status = $topic_obj->getVar('topic_status');
            if ($topicHandler->getPermission($forum_id, $topic_status, 'edit')) {
                $edit_ok = ($isadmin || ($this->checkIdentity() && $this->checkTimelimit('edit_timelimit')));
                if ($edit_ok) {
                    $thread_buttons['edit']['image'] = newbb_displayImage('p_edit', _EDIT);
                    $thread_buttons['edit']['link']  = $GLOBALS['xoops']->url('modules/' . $GLOBALS['xoopsModule']->getVar('dirname') . "/edit.php?{$page_query}");
                    $thread_buttons['edit']['name']  = _EDIT;
                    $mod_buttons['edit']['image']    = newbb_displayImage('p_edit', _EDIT);
                    $mod_buttons['edit']['link']     = $GLOBALS['xoops']->url('modules/' . $GLOBALS['xoopsModule']->getVar('dirname') . "/edit.php?{$page_query}");
                    $mod_buttons['edit']['name']     = _EDIT;
                }
            }

            if ($topicHandler->getPermission($forum_id, $topic_status, 'delete')) {
                $delete_ok = ($isadmin || ($this->checkIdentity() && $this->checkTimelimit('delete_timelimit')));

                if ($delete_ok) {
                    $thread_buttons['delete']['image'] = newbb_displayImage('p_delete', _DELETE);
                    $thread_buttons['delete']['link']  = $GLOBALS['xoops']->url('modules/' . $GLOBALS['xoopsModule']->getVar('dirname') . "/delete.php?{$page_query}");
                    $thread_buttons['delete']['name']  = _DELETE;
                    $mod_buttons['delete']['image']    = newbb_displayImage('p_delete', _DELETE);
                    $mod_buttons['delete']['link']     = $GLOBALS['xoops']->url('modules/' . $GLOBALS['xoopsModule']->getVar('dirname') . "/delete.php?{$page_query}");
                    $mod_buttons['delete']['name']     = _DELETE;
                }
            }
            if ($topicHandler->getPermission($forum_id, $topic_status, 'reply')) {
                $thread_buttons['reply']['image'] = newbb_displayImage('p_reply', _MD_REPLY);
                $thread_buttons['reply']['link']  = $GLOBALS['xoops']->url('modules/' . $GLOBALS['xoopsModule']->getVar('dirname') . "/reply.php?{$page_query}");
                $thread_buttons['reply']['name']  = _MD_REPLY;

                $thread_buttons['quote']['image'] = newbb_displayImage('p_quote', _MD_QUOTE);
                $thread_buttons['quote']['link']  = $GLOBALS['xoops']->url('modules/' . $GLOBALS['xoopsModule']->getVar('dirname') . "/reply.php?{$page_query}&amp;quotedac=1");
                $thread_buttons['quote']['name']  = _MD_QUOTE;
            }
        } else {
            $mod_buttons['edit']['image'] = newbb_displayImage('p_edit', _EDIT);
            $mod_buttons['edit']['link']  = $GLOBALS['xoops']->url('modules/' . $GLOBALS['xoopsModule']->getVar('dirname') . "/edit.php?{$page_query}");
            $mod_buttons['edit']['name']  = _EDIT;

            $mod_buttons['delete']['image'] = newbb_displayImage('p_delete', _DELETE);
            $mod_buttons['delete']['link']  = $GLOBALS['xoops']->url('modules/' . $GLOBALS['xoopsModule']->getVar('dirname') . "/delete.php?{$page_query}");
            $mod_buttons['delete']['name']  = _DELETE;

            $thread_buttons['reply']['image'] = newbb_displayImage('p_reply', _MD_REPLY);
            $thread_buttons['reply']['link']  = $GLOBALS['xoops']->url('modules/' . $GLOBALS['xoopsModule']->getVar('dirname') . "/reply.php?{$page_query}");
            $thread_buttons['reply']['name']  = _MD_REPLY;
        }

        if (!$isadmin && $GLOBALS['xoopsModuleConfig']['reportmod_enabled']) {
            $thread_buttons['report']['image'] = newbb_displayImage('p_report', _MD_REPORT);
            $thread_buttons['report']['link']  = $GLOBALS['xoops']->url('modules/' . $GLOBALS['xoopsModule']->getVar('dirname') . "/report.php?{$page_query}");
            $thread_buttons['report']['name']  = _MD_REPORT;
        }

        $thread_action = [];
        // irmtfan add pdf permission
        if ($topicHandler->getPermission($forum_id, $topic_status, 'pdf')
            && \file_exists($GLOBALS['xoops']->path('Frameworks/tcpdf/tcpdf.php'))) {
            $thread_action['pdf']['image']  = newbb_displayImage('pdf', _MD_PDF);
            $thread_action['pdf']['link']   = $GLOBALS['xoops']->url('modules/newbb/makepdf.php?type=post&amp;pageid=0');
            $thread_action['pdf']['name']   = _MD_PDF;
            $thread_action['pdf']['target'] = '_blank';
        }
        // irmtfan add print permission
        if ($topicHandler->getPermission($forum_id, $topic_status, 'print')) {
            $thread_action['print']['image']  = newbb_displayImage('printer', _MD_PRINT);
            $thread_action['print']['link']   = $GLOBALS['xoops']->url("modules/newbb/print.php?form=2&amp;forum={$forum_id}&amp;topic_id={$topic_id}");
            $thread_action['print']['name']   = _MD_PRINT;
            $thread_action['print']['target'] = '_blank';
        }

        if ($GLOBALS['xoopsModuleConfig']['show_sociallinks']) {
            $full_title  = $this->getVar('subject');
            $clean_title = \preg_replace('/[^A-Za-z0-9-]+/', '+', $this->getVar('subject'));
            $full_link   = $GLOBALS['xoops']->url("modules/newbb/viewtopic.php?post_id={$post_id}");

            $thread_action['social_twitter']['image']  = newbb_displayImage('twitter', \_MD_SHARE_TWITTER);
            $thread_action['social_twitter']['link']   = "http://twitter.com/share?text={$clean_title}&amp;url={$full_link}";
            $thread_action['social_twitter']['name']   = \_MD_SHARE_TWITTER;
            $thread_action['social_twitter']['target'] = '_blank';

            $thread_action['social_facebook']['image']  = newbb_displayImage('facebook', \_MD_SHARE_FACEBOOK);
            $thread_action['social_facebook']['link']   = "http://www.facebook.com/sharer.php?u={$full_link}";
            $thread_action['social_facebook']['name']   = \_MD_SHARE_FACEBOOK;
            $thread_action['social_facebook']['target'] = '_blank';

            $thread_action['social_gplus']['image']  = newbb_displayImage('googleplus', \_MD_SHARE_GOOGLEPLUS);
            $thread_action['social_gplus']['link']   = "https://plusone.google.com/_/+1/confirm?hl=en&url={$full_link}";
            $thread_action['social_gplus']['name']   = \_MD_SHARE_GOOGLEPLUS;
            $thread_action['social_gplus']['target'] = '_blank';

            $thread_action['social_linkedin']['image']  = newbb_displayImage('linkedin', \_MD_SHARE_LINKEDIN);
            $thread_action['social_linkedin']['link']   = "http://www.linkedin.com/shareArticle?mini=true&amp;title={$full_title}&amp;url={$full_link}";
            $thread_action['social_linkedin']['name']   = \_MD_SHARE_LINKEDIN;
            $thread_action['social_linkedin']['target'] = '_blank';

            $thread_action['social_delicious']['image']  = newbb_displayImage('delicious', \_MD_SHARE_DELICIOUS);
            $thread_action['social_delicious']['link']   = "http://del.icio.us/post?title={$full_title}&amp;url={$full_link}";
            $thread_action['social_delicious']['name']   = \_MD_SHARE_DELICIOUS;
            $thread_action['social_delicious']['target'] = '_blank';

            $thread_action['social_digg']['image']  = newbb_displayImage('digg', \_MD_SHARE_DIGG);
            $thread_action['social_digg']['link']   = "http://digg.com/submit?phase=2&amp;title={$full_title}&amp;url={$full_link}";
            $thread_action['social_digg']['name']   = \_MD_SHARE_DIGG;
            $thread_action['social_digg']['target'] = '_blank';

            $thread_action['social_reddit']['image']  = newbb_displayImage('reddit', \_MD_SHARE_REDDIT);
            $thread_action['social_reddit']['link']   = "http://reddit.com/submit?title={$full_title}&amp;url={$full_link}";
            $thread_action['social_reddit']['name']   = \_MD_SHARE_REDDIT;
            $thread_action['social_reddit']['target'] = '_blank';

            $thread_action['social_wong']['image']  = newbb_displayImage('wong', \_MD_SHARE_MRWONG);
            $thread_action['social_wong']['link']   = "http://www.mister-wong.de/index.php?action=addurl&bm_url=$full_link}";
            $thread_action['social_wong']['name']   = \_MD_SHARE_MRWONG;
            $thread_action['social_wong']['target'] = '_blank';
        }

        $post = [
            'post_id'         => $post_id,
            'post_parent_id'  => $this->getVar('pid'),
            'post_date'       => newbb_formatTimestamp($this->getVar('post_time')),
            'post_image'      => $post_image,
            'post_title'      => $post_title,        // irmtfan $post_title to add highlight keywords
            'post_text'       => $post_text,
            'post_attachment' => $post_attachment,
            'post_edit'       => $this->displayPostEdit(),
            'post_no'         => $post_no,
            'post_signature'  => $this->getVar('attachsig') ? @$poster['signature'] : '',
            'poster_ip'       => ($isadmin
                                  && $GLOBALS['xoopsModuleConfig']['show_ip']) ? \long2ip($this->getVar('poster_ip')) : '',
            'thread_action'   => $thread_action,
            'thread_buttons'  => $thread_buttons,
            'mod_buttons'     => $mod_buttons,
            'poster'          => $poster,
            'post_permalink'  => "<a href='" . $GLOBALS['xoops']->url('/modules/' . $GLOBALS['xoopsModule']->getVar('dirname') . "/viewtopic.php?post_id={$post_id}") . "'></a>",
        ];

        unset($thread_buttons, $mod_buttons, $eachposter);

        return $post;
    }
}
