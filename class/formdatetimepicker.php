<?php namespace XoopsModules\Xoopspoll;

/**
 * Xoopspoll form timepicker
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright ::   &copy; {@link https://xoops.org/ XOOPS Project}
 * @license   ::     {@link http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU GPL 2}
 * @package   ::     xoopspoll
 * @subpackage::  class
 * @since     ::       1.40
 * @author    ::      TXMod Xoops (aka timgno) {@link http://www.txmodxoops.org/ TXMod Xoops}
 * @author    ::      zyspec <owners@zyspec.com>
 * @credits::     {@link http://www.trentrichardson.com Trent Richardson}
 */

// defined('XOOPS_ROOT_PATH') || die('Restricted access');

/**
 * Creates a text field with jquery ui calendar & time select popup
 */
class FormDateTimePicker extends \XoopsFormText
{
    /**
     *
     * Contains the maximum field size
     */
    const MAXSIZE = 25;

    /**
     *
     * Constructor to build FormDateTimePicker object
     * @param mixed $caption HTML description to display for the element
     * @param mixed $name    HTML element name (ie. name='$name')
     * @param mixed $size    size of field to display
     * @param mixed $value   timestamp of date/time to show
     */
    public function __construct($caption, $name, $size, $value)
    {
        $value = (!is_numeric($value) || (0 === (int)$value)) ? time($value) : (int)$value;
        $size  = (int)$size;
        $size  = ($size > 0 && $size <= self::MAXSIZE) ? $size : self::MAXSIZE;
        parent::__construct($caption, $name, $size, self::MAXSIZE, $value);
    }

    /**
     *
     * Generate the HTML <input> to display the date/time field
     * @return string HTML code used to display in a form
     */
    public function render()
    {
        static $included = false;

        $ele_name  = $this->getName();
        $ele_value = $this->getValue(true);
        //        if (is_string($ele_value)) {
        if (!is_numeric($ele_value)) {
            $display_value = $ele_value;
            $ele_value     = time();
        } else {
            //            $display_value = '';
            //            $display_value = formatTimestamp($ele_value, 'm');
            $display_value = ucfirst(date(_MEDIUMDATESTRING, $ele_value));
        }

        if (is_object($GLOBALS['xoTheme'])) {
            $moduleHandler = xoops_getHandler('module');
            $sys_module    = $moduleHandler->getByDirname('system');
            $configHandler = xoops_getHandler('config');
            $moduleConfig  = $configHandler->getConfigsByCat(0, $sys_module->getVar('mid'));
            $jq_theme_dir  = $moduleConfig['jquery_theme'];

            $GLOBALS['xoTheme']->addStylesheet($GLOBALS['xoops']->url("modules/system/css/ui/{$jq_theme_dir}/ui.all.css"));
            $GLOBALS['xoTheme']->addScript('browse.php?Frameworks/jquery/jquery.js');
            $GLOBALS['xoTheme']->addScript('browse.php?Frameworks/jquery/plugins/jquery.ui.js');
            $GLOBALS['xoTheme']->addScript('browse.php?modules/xoopspoll/assets/js/jquery-ui-timepicker-addon.js');
            $GLOBALS['xoTheme']->addScript('browse.php?modules/xoopspoll/assets/js/jquery-ui-sliderAccess.js');
            $GLOBALS['xoTheme']->addStylesheet($GLOBALS['xoops']->url('modules/xoopspoll/assets/css/datetimepicker.css'));

            if (!$included) {
                $included = true;
                xoops_loadLanguage('admin', 'xoopspoll');
                // setup regional date variables
                $reg_values = "closeText: '"
                              . _AM_XOOPSPOLL_DTP_CLOSETEXT
                              . "',"
                              . "prevText: '"
                              . _AM_XOOPSPOLL_DTP_PREVTEXT
                              . "',"
                              . "nextText: '"
                              . _AM_XOOPSPOLL_DTP_NEXTTEXT
                              . "',"
                              . "currentText: '"
                              . _AM_XOOPSPOLL_DTP_CURRENTTEXT
                              . "',"
                              . 'monthNames: ['
                              . _AM_XOOPSPOLL_DTP_MONTHNAMES
                              . '],'
                              . 'monthNamesShort: ['
                              . _AM_XOOPSPOLL_DTP_MONTHNAMESSHORT
                              . '],'
                              . 'dayNames: ['
                              . _AM_XOOPSPOLL_DTP_DAYNAMES
                              . '],'
                              . 'dayNamesShort: ['
                              . _AM_XOOPSPOLL_DTP_DAYNAMESSHORT
                              . '],'
                              . 'dayNamesMin: ['
                              . _AM_XOOPSPOLL_DTP_DAYNAMESMIN
                              . '],'
                              . "weekHeader: '"
                              . _AM_XOOPSPOLL_DTP_WEEKHEADER
                              . "',"
                              . "dateFormat: '"
                              . _AM_XOOPSPOLL_DTP_DATEFORMAT
                              . "',"
                              . "firstDay: '"
                              . _AM_XOOPSPOLL_DTP_FIRSTDAY
                              . "',"
                              . 'isRTL: '
                              . _AM_XOOPSPOLL_DTP_ISRTL
                              . ','
                              . 'showMonthAfterYear: '
                              . _AM_XOOPSPOLL_DTP_SHOWMONTHAFTERYEAR
                              . ','
                              . "yearSuffix: '"
                              . _AM_XOOPSPOLL_DTP_YEARSUFFIX
                              . "',";
                // set regional time variables
                $reg_values .= "timeOnlyTitle: '"
                               . _AM_XOOPSPOLL_DTP_TIMEONLYTITLE
                               . "',"
                               . "timeText: '"
                               . _AM_XOOPSPOLL_DTP_TIMETEXT
                               . "',"
                               . "hourText: '"
                               . _AM_XOOPSPOLL_DTP_HOURTEXT
                               . "',"
                               . "minuteText: '"
                               . _AM_XOOPSPOLL_DTP_MINUTETEXT
                               . "',"
                               . "secondText: '"
                               . _AM_XOOPSPOLL_DTP_SECONDTEXT
                               . "',"
                               . "millisecText: '"
                               . _AM_XOOPSPOLL_DTP_MILLISECTEXT
                               . "',"
                               . "timeFormat: '"
                               . _AM_XOOPSPOLL_DTP_TIMEFORMAT
                               . "',"
                               . 'ampm: false,'
                               . 'stepMinute: 5';

                $GLOBALS['xoTheme']->addScript('', '', '
                  $(function() {
                      $( ".datetimepicker" ).datetimepicker({
                          ' . $reg_values . '
                      });
                  });
        ');
            }
        }

        return "<input type='text' name='{$ele_name}' id='{$ele_name}' class='datetimepicker' size='" . $this->getSize() . "' maxlength='" . $this->getMaxlength() . "' value='{$display_value}'" . $this->getExtra() . '>';
    }
}
