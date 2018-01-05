<{if !empty($block) }>
    <form style="margin-top: 1px;" action="<{$xoops_url}>/modules/xoopspoll/index.php" method="post">
        <table class='outer' style='margin-top: 1px;'>
            <thead>
            <tr>
                <th class='center' colspan='2'>
                    <input type='hidden' name='poll_id' value='<{$block.id}>'>
                    <{$block.question}>
                </th>
            </tr>
            </thead>
            <{if ($block.visible && ($block.hasExpired || (!$block.hasExpired && (!$block.canVote || $block.hasVoted))))}>
                <{* Show results *}>
                <{  foreach item=option from=$block.options}>
                <tr class='<{cycle values="even,odd"}>'>
                    <td class='width30 left'><{$option.text}></td>
                    <td class='left' style='margin-left: 1em;'>
                        <{$option.percent}><{if $block.dispVotes}> (<{$option.count}>)<{/if}><br>
                        <div class='width90'><img
                                    src='<{$xoops_url}>/modules/xoopspoll/assets/images/colorbars/<{$option.color}>'
                                    style='height: 14px; width: <{$option.percent}>;'
                                    alt='<{$option.percent}>'></div>
                    </td>
                </tr>
                <{  /foreach}>
            <{elseif (!$block.hasExpired && $block.canVote)}>
                <{* Show input form *}>
                <{  if $block.asList}>
                <{  foreach item=option from=$block.options}>
                <tr class='<{cycle values="even,odd"}>'>
                    <td class='center'><input type='<{$block.optionType}>' name='<{$block.optionName}>'
                                              value='<{$option.id}>'></td>
                    <td class='left'><{$option.text}></td>
                </tr>
                <{ /foreach}>
                <{ if ("" != $block.lang_multi)}>
                <tr class='<{cycle values="even,odd"}>'>
                    <td colspan='2' class='center smallsmall'><{$block.lang_multi}></td>
                </tr>
                <{ /if}>
                <{ else}>
                <tr class='<{cycle values="even,odd"}>'>
                    <td class='center' colspan='2'>
                        <select name='<{$block.optionName}>'<{if ($block.multiple)}> multiple<{/if}>>
                            <{ foreach item=option from=$block.options}>
                            <option value='<{$option.id}>'><{$option.text}></option>
                            <{ /foreach}>
                        </select>
                        <{ if ("" != $block.lang_multi)}>
                        <div class='floatcenter1 smallsmall'><{$block.lang_multi}></div>
                        <{ /if}>
                    </td>
                </tr>
                <{ /if}>
                <tr>
                    <td class='foot center' colspan='2'>
                        <input type='hidden' name='url' value='<{$block.url}>'>
                        <input type='submit' value='<{$block.langVote}>'>&nbsp;
                        <{ if $block.showResultsLink && $block.visible && $block.votes}>
                        <input type='button' value='<{$block.langResults}>'
                               onclick="location='<{$xoops_url}>/modules/xoopspoll/pollresults.php?poll_id=<{$block.id}>'">
                        <{ /if}>
                    </td>
                </tr>
            <{else}>
                <{* Show hidden msg *}>
                <tr class='even'>
                    <td colspan='2' class='center width100'><{$smarty.const._MB_XOOPSPOLL_RESULTS_HIDDEN}></td>
                </tr>
            <{/if}>

            <{ if $block.dispVotes && $block.visible}>
            <tr>
                <td class='foot center' colspan='2'><{$block.totalVotes}></td>
            </tr>
            <{ /if}>
            <tfoot>
            <tr>
                <{ if ($block.hasExpired)}>
                <td class='foot center' colspan='2'><{$block.langExpired}> <{$block.endTime}></td>
                <{ else}>
                <td class='foot center' colspan='2'><{$block.langExpires}> <{$block.endTime}></td>
                <{ /if}>
            </tr>
            <{ if ($block.commentMode > 0)}>
            <tr>
                <td class='foot center' colspan='2'>
                    <{ if ($block.comments == 1)}>
                    <a href='<{$xoops_url}>/modules/xoopspoll/pollresults.php?poll_id=<{$poll.id}>'><{$block.comments}> <{$block.langComment}></a>
                    <{ elseif ($block.comments > 1)}>
                    <a href='<{$xoops_url}>/modules/xoopspoll/pollresults.php?poll_id=<{$poll.id}>'><{$block.comments}> <{$block.langComments}></a>
                    <{ else}>
                    <a href='<{$xoops_url}>/modules/xoopspoll/pollresults.php?poll_id=<{$poll.id}>'><{$block.langComments}>
                        ?</a>
                    <{ /if}>
                </td>
            </tr>
            <{ /if}>
            </tfoot>
        </table>
    </form>
<{/if}>
