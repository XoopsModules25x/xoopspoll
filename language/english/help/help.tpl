<div id="help-template" class="outer">
    <h1 class="head">Help:
        <a class="ui-corner-all tooltip" href="<{$xoops_url}>/modules/xoopspoll/admin/index.php"
           title="Back to the administration of XOOPS Poll"> XOOPS Poll
            <img src="<{xoAdminIcons home.png}>"
                 alt="Back to the Administration of XOOPS Poll">
        </a></h1>

    <h4 class="odd">DESCRIPTION</h4>
    <div style="margin-top: 1em; margin-bottom: 1em;"><p>The XOOPS Poll module
        can be used to display interactive survey forms on your
        site. Each poll can display a question on which visitors can 'vote'. This
        can be a valuable way to collect feedback from your community. Polls can be
        configured to allow single or multiple choices and visitors can view the
        'results' to date.</p></div>

    <h4 class="odd">INSTALL/UNINSTALL</h4>
    <div style="margin-top: 1em; margin-bottom: 1em;">
        <p>No special measures are necessary, follow the standard installation process -
            extract the xoopspoll folder into the ../modules directory. Install the
            module through Admin -> System Module -> Modules.<br><br>
            Detailed instructions on installing modules are available in the
            <a href="https://www.gitbook.com/book/xoops/xoops-operations-guide/" target="_blank">XOOPS Operations Manual</a></p>
        <p>Additional integration with both the Marquee module and the SmartClone
            XOOPS modules are included in the extras folder. Copy these plugins to the
            corresponding folder in those modules to enable support for the XOOPS poll
            module. Additionally a patch has been created for the forum module (newbb)
            to support this version of XOOPS poll. Replace the existing forum module
            files with the files found in the ./extras/newbb folder.</p>
        <p>It is recommended that you delete the ./xoopspoll/extras and
            ./xoopspoll/test folders from your web server, they are not required for
            normal operation.</p></div>

    <h4 class="odd">OPERATING INSTRUCTIONS</h4>
    <div style="margin-top: 1em; margin-bottom: 1em;">
        <p>The XOOPS Poll module is very simple to configure and use. Basically you need to:</p>
        <ul>
            <li class='alignbottom'>Create one or more polls for people to vote on (Polls administration
                -> Polls)
            </li>
            <li class='alignbottom'>Display the Polls block somewhere on your website, this is not
                required since people can also access polls through the 'Polls' link in the main menu
                - but displaying the polls block in a prominent location will encourage people to vote)
            </li>
            <li class='alignbottom'>Ensure that relevant user groups have access rights to:
                <ul type='A'>
                    <li style='vertical-align: bottom;'>the Polls module and</li>
                    <li class='alignbottom'>the Polls block so that they can see it and vote. Detailed
                        instructions on configuring the access rights for user groups are
                        available in the <a href="https://www.gitbook.com/book/xoops/xoops-operations-guide/" target="_blank">XOOPS Operations
                            Manual</a></li>
                </ul>
            </li>
            <li class='alignbottom'>Most management functions for existing polls are found in Polls
                administration -> Polls.
            </li>
        </ul>
    </div>

    <h4 class="odd">General Information</h4>
    <div style="margin-top: 1em; margin-bottom: 1em;">
        <h5>eMail Results</h5>
        <p>XOOPS Poll uses XOOPS preload functionality to initiate sending email results. Once
            per user session the database will be checked to see if emails need to be sent to the
            poll author to let them know that the poll has expired (if they chose to be notified).
        </p>
        <h5>Poll Visibility</h5>
        <p>XOOPS Poll has several administrative options to control the visibility of poll results.
        <dl>
            <dt>never hide results</dt>
            <dd>- the results are always visible to users</dd>
            <dt>hide results until after voting</dt>
            <dd>- will hide the results from a user until they have voted</dd>
            <dt>hide results until poll expires</dt>
            <dd>- will hide the results from all users (whether they have voted or not) until the poll expires</dd>
            <dt>always hide results</dt>
            <dd>- users will not be able to see the results. Results are only visible to administrators in the admin panel</dd>
        </dl>
        </p>
        <h5>User Voting</h5>
        <p>There are several checks to eliminate users from being able to vote multiple times in a poll.
            If anonymous voting is allowed there are instances where malicious users can find ways to vote more
            than once per poll by circumventing the validation checks however the number of instances of this
            should be greatly reduced through the checks provided. Here is a summary of the checks performed
            by the module to reduce duplicate/invalid voting:
        <ul>
            <li>disallow voting on expired or inactive polls</li>
            <li>registered users can only vote once per poll</li>
            <li>unregistered voters can only vote once per poll, per computer (if anon voting is allowed)</li>
        </ul>
        </p>
    </div>
    <h4 class="odd">TUTORIAL</h4>

    <p class="even">
        Tutorial has been started, but we might need your help! Please check out the status of the tutorial <a href="https://www.gitbook.com/book/xoops/xoopspoll-tutorial/" target="_blank">here </a>.
        <br><br>To contribute to this Tutorial, <a href="https://github.com/XoopsDocs/xoopspoll-tutorial/" target="_blank">please fork it on GitHub</a>.
        <br> This document describes our <a href="https://www.gitbook.com/book/xoops/xoops-documentation-process/details/" target="_blank">Documentation Process</a> and it will help you to understand how to contribute.
        <br><br>
        There are more XOOPS Tutorials, so check them out in our <a href="https://www.gitbook.com/@xoops/" target="_blank">XOOPS Tutorial Repository on GitBook</a>.
    </p>


    <h4 class="odd">TRANSLATIONS</h4>
    <p class="even">Translations are on <a href="https://www.transifex.com/xoops/" target="_blank">Transifex</a> and in our <a href="https://github.com/XoopsLanguages/" target="_blank">XOOPS Languages Repository on GitHub</a>.</p>

    <h4 class="odd">SUPPORT</h4>
    <p class="even">If you have questions about this module and need help, you can visit our <a href="https://xoops.org/modules/newbb/viewforum.php?forum=28/" target="_blank">Support Forums on XOOPS Website</a></p>

    <h4 class="odd">DEVELOPMENT</h4>
    <p class="even">This module is Open Source and we would love your help in making it better! You can fork this module on <a href="https://github.com/XoopsModulesArchive/xoopspoll" target="_blank">GitHub</a><br><br>
        But there is more happening on GitHub:<br><br>
        - <a href="https://github.com/xoops" target="_blank">XOOPS Core</a> <br>
        - <a href="https://github.com/XoopsModules25x" target="_blank">XOOPS Modules</a><br>
        - <a href="https://github.com/XoopsThemes" target="_blank">XOOPS Themes</a><br><br>
        Go check it out, and <strong>GET INVOLVED</strong>

    </p>
</div>
