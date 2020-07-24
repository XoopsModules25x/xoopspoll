<h2><{$lang_pollslist}></h2>

<table class='width100 outer marg2'>
    <thead>
    <tr>
        <th><{$lang_pollquestion}></th>
        <{ if $disp_votes}>
        <th class='center'><{$lang_pollvoters}></th>
        <th class='center'><{$lang_votes}></th>
        <{ /if}>
        <th class='center'><{$lang_expiration}></th>
        <th>&nbsp;</th>
    </tr>
    </thead>
    <tfoot></tfoot>
    <tbody>
    <!-- start polls item loop -->
    <{section name=i loop=$polls}>
        <tr class='<{cycle values="odd,even"}>'>
            <{ if $polls[i].canVote}>
            <td><a href='index.php?poll_id=<{$polls[i].pollId}>'><{$polls[i].pollQuestion}></a></td>
            <{ else}>
            <td><{$polls[i].pollQuestion}></td>
            <{ /if}>
            <{ if $disp_votes}>
            <{ if $polls[i].visible }>
            <td class='center'><{$polls[i].pollVoters}></td>
            <td class='center'><{$polls[i].pollVotes}></td>
            <{ else}>
            <td class='center'><img class='alignmiddle' src='<{$obscured_icon}>' alt='<{$lang_obscured_alt}>'
                                    title='<{$lang_obscured_title}>'></td>
            <td class='center'><img class='alignmiddle' src='<{$obscured_icon}>' alt='<{$lang_obscured_alt}>'
                                    title='<{$lang_obscured_title}>'></td>
            <{*    <td colspan='2'>&nbsp;</td> *}>
            <{ /if}>
            <{ /if}>
            <{ if $polls[i].hasEnded}>
            <td class='center red'><{$polls[i].pollEnd}></td>
            <{ else}>
            <td class='center'><{$polls[i].pollEnd}></td>
            <{ /if}>
            <{ if $polls[i].visible && ($polls[i].pollVotes > 0) }>
            <td class='right'><a href='pollresults.php?poll_id=<{$polls[i].pollId}>'><img class='alignmiddle'
                                                                                          src='<{$results_link_icon}>'
                                                                                          alt='<{$lang_results}>'
                                                                                          title='<{$lang_results}>'> <{$lang_results}>
                </a></td>
            <{*    <td class='right'><a href='pollresults.php?poll_id=<{$polls[i].pollId}>'><{$lang_results}></a></td> *}>
            <{ else}>
            <td>&nbsp;</td>
            <{ /if}>
        </tr>
    <{/section}>
    <!-- end polls item loop -->
    </tbody>
</table>
