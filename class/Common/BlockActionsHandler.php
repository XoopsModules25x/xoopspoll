<?php declare(strict_types=1);

namespace XoopsModules\Xoopspoll\Common;

/**
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 *
 *
 * @category        Module
 * @author          XOOPS Development Team
 * @copyright       XOOPS Project
 * @link            https://xoops.org
 * @license         GNU GPL 2.0 or later (https://www.gnu.org/licenses/gpl-2.0.html)
 */

use Xmf\Module\Admin;
use Xmf\Request;
use XoopsModules\Xoopspoll\{
    Helper
};

/**
 * class BlockActionsHandler
 */
class BlockActionsHandler
{
    private $blocksadmin;
    private $helper;
    private $request;

    /**
     * @param $blocksadmin
     */
    public function __construct($blocksadmin)
    {
        $this->blocksadmin = $blocksadmin;
        $this->helper = Helper::getInstance();
    }

    public function processPostData(): BlockData
    {
        $blockData                 = new BlockData();
        $blockData->ok             = Request::getInt('ok', 0, 'POST');
        $blockData->confirm_submit = Request::getCmd('confirm_submit', '', 'POST');
        $blockData->submit         = Request::getString('submit', '', 'POST');
        $blockData->bside          = Request::getString('bside', '0', 'POST');
        $blockData->bweight        = Request::getString('bweight', '0', 'POST');
        $blockData->bvisible       = Request::getString('bvisible', '0', 'POST');
        $blockData->bmodule        = Request::getArray('bmodule', [], 'POST');
        $blockData->btitle         = Request::getString('btitle', '', 'POST');
        $blockData->bcachetime     = Request::getString('bcachetime', '0', 'POST');
        $blockData->groups         = Request::getArray('groups', [], 'POST');
        $blockData->options        = Request::getArray('options', [], 'POST');
        $blockData->submitblock    = Request::getString('submitblock', '', 'POST');
        $blockData->fct            = Request::getString('fct', '', 'POST');
        $blockData->title          = Request::getString('title', '', 'POST');
        $blockData->side           = Request::getString('side', '0', 'POST');
        $blockData->weight         = Request::getString('weight', '0', 'POST');
        $blockData->visible        = Request::getString('visible', '0', 'POST');

//        $blockData->oldTitle      = [];
//        $blockData->oldSide       = [];
//        $blockData->oldWeight     = [];
//        $blockData->oldVisible    = [];
//        $blockData->oldGroups     = [];
//        $blockData->oldBcachetime = [];
//        $blockData->oldBmodule    = [];

        return $blockData;
    }

    public function handleActions(BlockData $blockData)
    {
        $moduleDirNameUpper = \mb_strtoupper(Helper::getInstance()->getDirname());
        $bid = Request::getInt('bid', 0);

        switch ($blockData->op) {
            case 'order':
                $blockData->bid        = Request::getArray('bid', []);
                $blockData->titleArray = Request::getArray('title', [], 'POST');
                // Fill in other properties from the request
                $this->processOrderBlockAction($blockData);
                break;

            case 'clone':
                $this->blocksadmin->cloneBlock($bid);
                break;

            case 'delete':
                if (1 === $blockData->ok) {
                    $this->blocksadmin->deleteBlock($bid);
                } else {
                    xoops_confirm(['ok' => 1, 'op' => 'delete', 'bid' => $bid], 'blocksadmin.php', constant('CO_' . $moduleDirNameUpper . '_' . 'DELETE_BLOCK_CONFIRM'), constant('CO_' . $moduleDirNameUpper . '_' . 'CONFIRM'), true);
                    xoops_cp_footer();
                }
                break;

            case 'edit':
                if ($bid > 0) {
                    $this->blocksadmin->editBlock($bid);
                } else {
                    $this->helper->redirect('admin/blocksadmin.php', 3, _AM_BLOCK_EDIT_ID_ERROR);
                }
                break;

            case 'edit_ok':
                $this->blocksadmin->updateBlock($bid, $blockData->btitle, $blockData->bside, $blockData->bweight, $blockData->bvisible, $blockData->bcachetime, $blockData->bmodule, $blockData->options, $blockData->groups);
                break;

            case 'clone_ok':
                $this->blocksadmin->isBlockCloned($bid, $blockData->bside, $blockData->bweight, $blockData->bvisible, $blockData->bcachetime, $blockData->bmodule, $blockData->options, $blockData->groups, true);
                break;

            case 'list':
                //        xoops_cp_header();
                $this->blocksadmin->listBlocks();
                break;

            default:
                // Add the code to handle the default operation here
                break;
        }
    }

    public function processOrderBlockAction(BlockData $blockData)
    {
        $blockData->bid           = Request::getArray('bid', []);
        $blockData->oldtitle      = Request::getArray('oldtitle', [], 'POST');
        $blockData->oldside       = Request::getArray('oldside', [], 'POST');
        $blockData->oldweight     = Request::getArray('oldweight', [], 'POST');
        $blockData->oldvisible    = Request::getArray('oldvisible', [], 'POST');
        $blockData->oldgroups     = Request::getArray('oldgroups', [], 'POST');
        $blockData->oldbcachetime = Request::getArray('oldcachetime', [], 'POST');
        $blockData->oldbmodule    = Request::getArray('oldbmodule', [], 'POST');
        $blockData->title         = Request::getArray('title', [], 'POST');
        $blockData->weight        = Request::getArray('weight', [], 'POST');
        $blockData->visible       = Request::getArray('visible', [], 'POST');
        $blockData->side          = Request::getArray('side', [], 'POST');
        $blockData->bcachetime    = Request::getArray('bcachetime', [], 'POST');
        $blockData->groups        = Request::getArray('groups', [], 'POST');
        $blockData->bmodule       = Request::getArray('bmodule', [], 'POST');


        $this->blocksadmin->orderBlock(
            $blockData->bid,
            $blockData->oldtitle,
            $blockData->oldside,
            $blockData->oldweight,
            $blockData->oldvisible,
            $blockData->oldgroups,
            $blockData->oldbcachetime,
            $blockData->oldbmodule,
            $blockData->title,
            $blockData->weight,
            $blockData->visible,
            $blockData->side,
            $blockData->bcachetime,
            $blockData->groups,
            $blockData->bmodule
        );
    }


    //for testing mocking

    /**
     * @param $request
     * @return void
     */
    public function setRequest($request): void
    {
        $this->request = $request;
    }

}
