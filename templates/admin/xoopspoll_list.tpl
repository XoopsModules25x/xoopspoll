<div>
    <{$navigation}>
</div>
<div>
    <{$addPollButton}>
    <{$newbbIntro}>
    <form action='<{$self}>' method='post'>
        <{securityToken}>
        <table class='outer width100 bnone pad3 marg2'>
            <thead>
            <tr>
                <th class='center'><{$smarty.const._AM_XOOPSPOLL_DISPLAYBLOCK}></th>
                <th class='left'><{$smarty.const._AM_XOOPSPOLL_DISPLAYORDER}></th>
                <th class='left'><{$smarty.const._AM_XOOPSPOLL_POLLQUESTION}></th>
                <th class='center'><{$smarty.const._AM_XOOPSPOLL_VOTERS}></th>
                <th class='center'><{$smarty.const._AM_XOOPSPOLL_VOTES}></th>
                <th class='center'><{$smarty.const._AM_XOOPSPOLL_START_TIME}></th>
                <th class='center'><{$smarty.const._AM_XOOPSPOLL_EXPIRATION}></th>
                <th class='center'><{$smarty.const._AM_XOOPSPOLL_ACTIONS}></th>
            </tr>
            </thead>
            <tfoot>
            <tr class='right bg3'>
                <td class='center' colspan='2'>
                    <input type='hidden' name='op' value='quickupdate'>
                    <input type='submit' value='<{$smarty.const._SUBMIT}>'>
                </td>
                <td colspan='6'>&nbsp;</td>
            </tr>
            </tfoot>
            <tbody>
            <{foreach item=pollItem from=$pollItems }>
                <tr class='<{cycle values="odd,even"}>'>
                    <td class='center'>
                        <input type='hidden' name='poll_id[<{$pollItem.id}>]' value='<{$pollItem.id}>'>
                        <input type='checkbox' name='display[<{$pollItem.id}>]' value='1'<{$pollItem.checked}>>
                    </td>
                    <td>
                        <input type='text' name='weight[<{$pollItem.id}>]' value='<{$pollItem.weight}>' size='6'
                               maxlength='5'>
                    </td>

                    <td>
                        <{ if ("" != $pollItem.topic_title)}>
                        <{ html_image file=$pollItem.buttons.forum.file href=$pollItem.buttons.forum.href
                        alt=$pollItem.buttons.forum.alt title=$pollItem.buttons.forum.alt}>&nbsp;
                        <{ /if}>
                        <{ $pollItem.question}>
                    </td>

                    <{*      <td><{$pollItem.question}></td> *}>
                    <td class='center'><{$pollItem.voters}></td>
                    <td class='center'><{$pollItem.votes}></td>
                    <td class='center'><{$pollItem.xuStartFormattedTime}></td>
                    <td class='center'><{$pollItem.end}></td>
                    <td class='center'>
                        <{ html_image file=$pollItem.buttons.edit.file href=$pollItem.buttons.edit.href
                        alt=$pollItem.buttons.edit.alt title=$pollItem.buttons.edit.alt}>
                        <{ html_image file=$pollItem.buttons.clone.file href=$pollItem.buttons.clone.href
                        alt=$pollItem.buttons.clone.alt title=$pollItem.buttons.clone.alt}>
                        <{ html_image file=$pollItem.buttons.delete.file href=$pollItem.buttons.delete.href
                        alt=$pollItem.buttons.delete.alt title=$pollItem.buttons.delete.alt}>
                        <{ html_image file=$pollItem.buttons.log.file href=$pollItem.buttons.log.href
                        alt=$pollItem.buttons.log.alt title=$pollItem.buttons.log.alt}>
                        <{*
                                <a href='" . $_SERVER['PHP_SELF'] . "?op=edit&amp;poll_id={$id}'>
                                  <img src='" . $pathIcon16 . DIRECTORY_SEPARATOR . "edit.png' alt='" . _AM_XOOPSPOLL_EDITPOLL . "' title='" . _AM_XOOPSPOLL_EDITPOLL . "'>
                                </a>
                                <a href='" . $_SERVER['PHP_SELF'] . "?op=clone&amp;poll_id={$id}'>
                                  <img src='" . $pathIcon16 . DIRECTORY_SEPARATOR . "editcopy.png' alt='" . _AM_XOOPSPOLL_CLONE . "' title='" . _AM_XOOPSPOLL_CLONE."'>
                                </a>
                                <a href='" . $_SERVER['PHP_SELF'] . "?op=delete&amp;poll_id={$id}'>
                                  <img src='" . $pathIcon16 . DIRECTORY_SEPARATOR . "delete.png' alt='" . _DELETE . "' title='" . _DELETE . "'>
                                </a>
                                <a href='" . $_SERVER['PHP_SELF'] . "?op=log&amp;poll_id={$id}'>
                                  <img src='" . $pathIcon16 . DIRECTORY_SEPARATOR . "search.png' alt='" . _AM_XOOPSPOLL_VIEWLOG . "' title='" . _AM_XOOPSPOLL_VIEWLOG."'>\n"
                                </a>
                        *}>
                    </td>
                </tr>
            <{/foreach}>
            <{* $body *}>
            </tbody>
        </table>
    </form>
    <{if !empty($rendered_nav)}>
        <div class='right floatcenter1'><{$rendered_nav}></div>
    <{/if}>
</div>
