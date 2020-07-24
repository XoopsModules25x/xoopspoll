<{foreach item=poll from=$block.polls}>
    <form style="margin-top: 1px;" action="<{$xoops_url}>/modules/<{$block.thisModuleDir}>/index.php" method="post">
        <table class='outer' style='margin: 1px;'>
            <thead>
            <tr>
                <th class='center' colspan='2'>
                    <input type='hidden' name='poll_id' value='<{$poll.id}>'>
                    <{$poll.question}>
                </th>
            </tr>
            </thead>

            <{if ($poll.visible && ($poll.hasExpired || (!$poll.hasExpired && (!$poll.canVote || $poll.hasVoted))))}>
                <{* Show Results *}>
                <{foreach item=option from=$poll.options}>
                    <tr class='<{cycle values='even,odd'}>'>
                        <td class='width30 left'><{$option.text}></td>
                        <td class='left' style='margin-left: 1em;'>
                            <{$option.percent}><{if $block.dispVotes}> (<{$option.count}>)<{/if}><br>
                            <div class='width90'><img
                                        src='<{$xoops_url}>/modules/xoopspoll/assets/images/colorbars/<{$option.color}>'
                                        style='height: 14px; width: <{$option.percent}>;' alt=''></div>
                        </td>
                    </tr>
                <{/foreach}>
            <{elseif (!$poll.hasExpired && $poll.canVote)}>
                <{* Show input form *}>
                <{  if $block.asList}>
                <{foreach item=option from=$poll.options}>
                    <tr class='<{cycle values='even,odd'}>'>
                        <td class='center'><input type='<{$poll.optionType}>' name='<{$poll.optionName}>'
                                                  value='<{$option.id}>'></td>
                        <td class='left'><{$option.text}></td>
                    </tr>
                <{/foreach}>
                <{if ("" != $poll.lang_multi)}>
                    <tr class='<{cycle values="even,odd"}>'>
                        <td colspan='2' class='center smallsmall'><{$poll.lang_multi}></td>
                    </tr>
                <{/if}>
                <{  else}>
                <tr>
                    <td class='even center' colspan='2'>
                        <{*     <{html_options name=$poll.optionName"<{if $poll.multiple}> multiple=$poll.multiple <{/if}>" options=$poll.options}> *}>
                        <select name='<{$poll.optionName}>'<{if $poll.multiple}> multiple='multiple'<{/if}>>
                            <{ foreach item=option from=$poll.options}>
                            <option value='<{$option.id}>'><{$option.text}></option>
                            <{ /foreach}>
                            <{ if ("" != $poll.lang_multi)}>
                            <div class='floatcenter1 smallsmall'><{$poll.lang_multi}></div>
                            <{ /if}>
                        </select>
                    </td>
                </tr>
                <{  /if}>
            <{else}>
                <{* Show hidden msg *}>
                <tr class='even'>
                    <td colspan='2' class='center width100'><{$smarty.const._MB_XOOPSPOLL_RESULTS_HIDDEN}></td>
                </tr>
            <{/if}>
            <{* <{if ($poll.hasExpired OR $poll.hasVoted>0)}>
              <tr>
               <td class='foot center' colspan='2'><input type='button' value='<{$block.langResults}>' onclick="location='<{$xoops_url}>/modules/<{$block.thisModuleDir}>/pollresults.php?poll_id=<{$poll.id}>'"></td>
               </tr>
            <{else}> *}>
            <{if (!$poll.hasExpired && !$poll.hasVoted)}>
                <tr>
                    <td class='foot center' colspan='2'>
                        <input type='hidden' name='url' value='<{$block.url}>'>
                        <input type='submit' value='<{$block.langVote}>'>&nbsp;
                        <{ if $poll.votes}>
                        <input type='button' value='<{$block.langResults}>'
                               onclick="location='<{$xoops_url}>/modules/<{$block.thisModuleDir}>/pollresults.php?poll_id=<{$poll.id}>'">
                        <{ /if}>
                    </td>
                </tr>
            <{/if}>
            <{if $block.dispVotes && $poll.visible}>
                <tr>
                    <td class='foot center' colspan='2'><{$poll.totalVotes}></td>
                </tr>
            <{/if}>
            <tfoot>
            <tr>
                <{if ($poll.hasExpired)}>
                    <td class='foot center' colspan='2'><{$block.langExpired}> <{$poll.endTime}></td>
                <{else}>
                    <td class='foot center' colspan='2'><{$block.langExpires}> <{$poll.endTime}></td>
                <{/if}>
            </tr>
            <{if ($poll.commentMode > 0)}>
                <tr>
                    <td class='foot center' colspan='2'>
                        <{ if (1 == $poll.comments)}>
                        <a href='<{$xoops_url}>/modules/xoopspoll/pollresults.php?poll_id=<{$poll.id}>'><{$poll.comments}> <{$block.langComment}></a>
                        <{ elseif ($poll.comments > 1)}>
                        <a href='<{$xoops_url}>/modules/xoopspoll/pollresults.php?poll_id=<{$poll.id}>'><{$poll.comments}> <{$block.langComments}></a>
                        <{ else}>
                        <a href='<{$xoops_url}>/modules/xoopspoll/pollresults.php?poll_id=<{$poll.id}>'><{$block.langComments}>
                            ?</a>
                        <{ /if}>
                    </td>
                </tr>
            <{/if}>
            </tfoot>
        </table>
    </form>
<{/foreach}>
