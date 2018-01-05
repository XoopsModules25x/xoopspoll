<div class='center' style='margin: 3px auto;'>
    <table class='floatcenter1' style='margin: 1px auto;'>
        <thead>
        <tr>
            <th colspan='2'><{$poll.question}></th>
        </tr>
        </thead>
        <tr>
            <td class='head' colspan='2'><{$poll.description}></td>
        </tr>
        <tr>
            <td class='head right italic x-small' colspan='2'><{$poll.end_text}></td>
        </tr>
        <{if '' != $visible_msg}>
            <tr>
                <td class='even center bold' colspan='2'><{$visible_msg}></td>
            </tr>
        <{else}>
            <{  foreach item=option from=$poll.options}>
            <tr>
                <td class='even left width40 pad5'><{$option.text}></td>
                <td class='odd left'><{$option.image}> <{$option.percent}></td>
            </tr>
            <{ /foreach}>
            <{ if $disp_votes}>
            <tr>
                <td class='foot right pad10'><{$poll.totalVotes}></td>
                <td class='foot left pad10'><{$poll.totalVoters}></td>
            </tr>
            <tr class='foot positop bottom'>
                <td colspan='2'><{$poll.vote}></td>
            </tr>
            <{  /if}>
        <{/if}>
        <{*    <tr><td class='even' colspan='2'><input type='button' value='<{$smarty.const._BACK}>' onclick='javascript:history.go(-1)'></td></tr> *}>
        <{if !empty($back_link)}>
            <tr>
                <td class='even' colspan='2'><a href='<{$back_link}>'><img class='alignmiddle' src='<{$back_link_icon}>'
                                                                           alt='<{$back_text}>'
                                                                           title='<{$back_text}>'> <{$back_text}></a>
                </td>
            </tr>
        <{/if}>
    </table>
</div>
