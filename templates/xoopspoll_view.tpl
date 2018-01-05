<form action='<{$action}>' method='post'>
    <{securityToken}><{*//mb*}>
    <table class='outer center width90' style='margin: auto;'>
        <thead>
        <tr>
            <th class='center' colspan='2'><{$poll.question}></th>
        </tr>
        <tr>
            <td class='head center' colspan='2'>
                <{$poll.description}>
            </td>
        </tr>
        </thead>
        <tbody>
        <{foreach item=option from=$poll.options}>
            <tr class='<{cycle values="odd,even"}>'>
                <td class='right width40' style='padding: 0 1em;'><{$option.input}></td>
                <td class='left'><{$option.text}></td>
            </tr>
        <{/foreach}>
        </tbody>
        <tfoot>
        <{if "" != $lang_multi}>
            <tr>
                <td class='center foot' colspan='2'><{$lang_multi}></td>
            </tr>
        <{/if}>
        <tr>
            <td class='center foot' colspan='2'>
                <input type="hidden" name="poll_id" value="<{$poll.pollId}>">
                <{ if $can_vote}>
                <input type='submit' value='<{$lang_vote}>'>&nbsp;
                <{ /if}>
                <{ if ($voteCount > 0)}>
                <input type='button' value='<{$lang_results}>' onclick="location='<{$poll.viewresults}>'">
                <{ /if}>
            </td>
        </tr>
        </tfoot>
    </table>
</form>
