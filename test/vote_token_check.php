<?php declare(strict_types=1);
/**
 * Vote token check
 *
 * A vote posted without a valid token must not be counted; it must land on
 * the poll form with the choice kept. The same vote sent again with the token
 * of that form must be counted.
 *
 * Creates a temporary poll that allows anonymous voting and removes it again.
 * The module must be readable by anonymous visitors. Run from the site's
 * htdocs directory:
 *
 *   php modules/xoopspoll/test/vote_token_check.php
 *
 * @copyright  XOOPS Project (https://xoops.org)
 * @license    GNU GPL 2.0 or later (https://www.gnu.org/licenses/gpl-2.0.html)
 */

use XoopsModules\Xoopspoll\{
    Constants,
    Helper
};

if ('cli' !== \PHP_SAPI) {
    exit('This script runs from the command line only.');
}

require \dirname(__DIR__, 3) . '/mainfile.php';

$helper        = Helper::getInstance();
$pollHandler   = $helper->getHandler('Poll');
$optionHandler = $helper->getHandler('Option');
$logHandler    = $helper->getHandler('Log');

$cookieJar = \tempnam(\sys_get_temp_dir(), 'xpl');
$lastPage  = '';
$post      = static function (array $fields) use ($cookieJar, &$lastPage): string {
    $curl = \curl_init(XOOPS_URL . '/modules/xoopspoll/index.php');
    \curl_setopt_array($curl, [
        \CURLOPT_POST           => true,
        \CURLOPT_POSTFIELDS     => \http_build_query($fields),
        \CURLOPT_RETURNTRANSFER => true,
        \CURLOPT_FOLLOWLOCATION => true,
        \CURLOPT_COOKIEJAR      => $cookieJar,
        \CURLOPT_COOKIEFILE     => $cookieJar,
        \CURLOPT_TIMEOUT        => 30,
    ]);
    $body = \curl_exec($curl);
    \curl_close($curl);

    return $lastPage = \is_string($body) ? $body : '';
};

$failures = [];
$expect   = static function (bool $condition, string $what) use (&$failures, &$lastPage): void {
    echo ($condition ? '  ok    ' : '  FAIL  ') . $what . "\n";
    if (!$condition) {
        if ([] === $failures) {
            // the answer usually names the reason, e.g. no access for anonymous visitors
            $saved = \sys_get_temp_dir() . '/xoopspoll_vote_token_check.html';
            \file_put_contents($saved, $lastPage);
            echo "        the site's answer is saved in {$saved}\n";
        }
        $failures[] = $what;
    }
};

// removes the temporary poll with its options and votes; also clears what an
// interrupted earlier run left behind
$question = 'vote token check (temporary)';
$remove   = static function () use ($pollHandler, $optionHandler, $logHandler, $question): void {
    foreach ($pollHandler->getObjects(new \Criteria('question', $question)) as $leftover) {
        $criteria = new \Criteria('poll_id', (int)$leftover->getVar('poll_id'));
        $logHandler->deleteAll($criteria);
        $optionHandler->deleteAll($criteria);
        $pollHandler->delete($leftover, true);
    }
};
$remove();

$pollObj = $pollHandler->create();
$pollObj->setVars([
    'question'  => $question,
    'user_id'    => 1,
    'start_time' => \time() - 60,
    'end_time'   => \time() + 3600,
    'anonymous' => Constants::ANONYMOUS_VOTING_ALLOWED,
    'display'   => Constants::DO_NOT_DISPLAY_POLL_IN_BLOCK,
]);
if (!$pollHandler->insert($pollObj, true)) {
    echo "Could not create the temporary poll.\n";
    exit(1);
}
$pollId = (int)$pollObj->getVar('poll_id');

try {
    $optionIds = [];
    foreach (['first', 'second'] as $text) {
        $optionObj = $optionHandler->create();
        $optionObj->setVars(['poll_id' => $pollId, 'option_text' => $text, 'option_color' => 'aqua.gif']);
        $optionHandler->insert($optionObj, true);
        $optionIds[] = (int)$optionObj->getVar('option_id');
    }
    [$chosen, $other] = $optionIds;

    echo "Vote with a made-up token\n";
    $post(['poll_id' => $pollId, 'option_id' => $chosen, 'XOOPS_TOKEN_REQUEST' => 'made-up']);
    $expect(0 === $logHandler->getTotalVotesByPollId($pollId), 'the vote is not counted');

    echo "Vote without a token\n";
    $returnUrl = XOOPS_URL . '/modules/xoopspoll/pollresults.php?poll_id=' . $pollId . '&from=block';
    $page      = $post(['poll_id' => $pollId, 'option_id' => $chosen, 'url' => $returnUrl]);
    $expect(0 === $logHandler->getTotalVotesByPollId($pollId), 'the vote is not counted');
    $expect(
        false !== \strpos($page, 'name="url" value="' . \htmlspecialchars($returnUrl, \ENT_QUOTES | \ENT_HTML5) . '"'),
        'the poll form keeps the page to return to'
    );
    $expect(1 === \preg_match("/value='{$chosen}' checked/", $page), 'the poll form shows the choice selected');
    $expect(0 === \preg_match("/value='{$other}' checked/", $page), 'the other option is not selected');
    $hasToken = 1 === \preg_match('/name=["\']XOOPS_TOKEN_REQUEST["\'][^>]*value=["\']([^"\']+)/', $page, $match);
    $expect($hasToken, 'the poll form carries a token');
    \xoops_loadLanguage('main', 'xoopspoll');
    $expect(false !== \strpos($page, \_MD_XOOPSPOLL_CONFIRM_VOTE), 'it asks to confirm the vote');

    echo "Confirm the vote with the token of that form\n";
    $post(['poll_id' => $pollId, 'option_id' => $chosen, 'XOOPS_TOKEN_REQUEST' => $match[1] ?? '']);
    $expect(1 === $logHandler->getTotalVotesByPollId($pollId), 'the vote is counted once');
    $expect(1 === $logHandler->getTotalVotesByOptionId($chosen), 'it is counted for the chosen option');
} finally {
    $remove();
    \unlink($cookieJar);
}

echo [] === $failures ? "vote token check passed\n" : 'vote token check FAILED: ' . \count($failures) . " of the checks above\n";
exit([] === $failures ? 0 : 1);
