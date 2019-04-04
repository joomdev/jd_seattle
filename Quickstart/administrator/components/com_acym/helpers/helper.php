<?php
/**
 * @package	AcyMailing for Joomla
 * @version	6.1.2
 * @author	acyba.com
 * @copyright	(C) 2009-2019 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><?php

define('ACYM_NAME', 'AcyMailing');
define('ACYM_DBPREFIX', '#__acym_');
define('ACYM_LANGUAGE_FILE', 'com_acym');
define('ACYM_ACYWEBSITE', 'https://www.acyba.com/');
define('ACYM_UPDATEMEURL', ACYM_ACYWEBSITE.'index.php?option=com_updateme&ctrl=');
define('ACYM_SPAMURL', ACYM_UPDATEMEURL.'spamsystem&task=');
define('ACYM_HELPURL', ACYM_UPDATEMEURL.'doc&component='.ACYM_NAME.'&page=');
define('ACYM_REDIRECT', ACYM_UPDATEMEURL.'redirect&page=');
define('ACYM_UPDATEURL', ACYM_UPDATEMEURL.'update&task=');


if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}
include_once(rtrim(dirname(__DIR__), DS).DS.'library'.DS.strtolower('Joomla.php'));

define('ACYM_LIVE', rtrim(acym_rootURI(), '/').'/');

if (is_callable("date_default_timezone_set")) {
    date_default_timezone_set(@date_default_timezone_get());
}

function acym_dateField($name, $value = '')
{
    $result = '<div class="date_rs_selection_popup">';

    $result .= '<div class="grid-x">';
    $result .= acym_switchFilter(
        array(
            'relative' => acym_translation('ACYM_RELATIVE_DATE'),
            'specific' => acym_translation('ACYM_SPECIFIC_DATE'),
        ),
        'relative',
        'switch_'.$name,
        'date_rs_selection'
    );
    $result .= '</div>';

    $result .= '<div class="date_rs_selection_choice date_rs_selection_relative grid-x grid-margin-x">';
    $result .= '<div class="cell small-2"><input type="number" class="relativenumber" value="0"></div>';
    $result .= '<div class="cell small-5">'.acym_select(
            array(
                '60' => acym_translation('ACYM_MINUTES'),
                '3600' => acym_translation('ACYM_HOUR'),
                '86400' => acym_translation('ACYM_DAY'),
            ),
            'relative_'.$name,
            null,
            'class="acym__select relativetype"'
        ).'</div>';
    $result .= '<div class="cell small-5">'.acym_select(
            array(
                '-' => acym_translation('ACYM_BEFORE'),
                '+' => acym_translation('ACYM_AFTER'),
            ),
            'relativewhen_'.$name,
            null,
            'class="acym__select relativewhen"'
        ).'</div>';
    $result .= '</div>';

    $result .= '<div class="date_rs_selection_choice date_rs_selection_specific grid-x" style="display: none;">';
    $result .= '<div class="cell auto"></div><div class="cell shrink"><input type="text" name="specific_'.acym_escape($name).'" class="acy_date_picker" readonly></div><div class="cell auto"></div>';
    $result .= '</div>';

    $result .= '<div class="cell grid-x"><div class="cell auto"></div><button type="button" class="cell medium-4 button acym__button__set__time" data-close>'.acym_translation('ACYM_SET').'</button></div></div>';

    $id = preg_replace('#[^a-z0-9_]#i', '', $name);
    if (is_numeric($value)) {
        $shownValue = str_replace(
            array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'),
            array(substr(acym_translation('ACYM_JANUARY'), 0, 3), substr(acym_translation('ACYM_FEBRUARY'), 0, 3), substr(acym_translation('ACYM_MARCH'), 0, 3), substr(acym_translation('ACYM_APRIL'), 0, 3), substr(acym_translation('ACYM_MAY'), 0, 3), substr(acym_translation('ACYM_JUNE'), 0, 3), substr(acym_translation('ACYM_JULY'), 0, 3), substr(acym_translation('ACYM_AUGUST'), 0, 3), substr(acym_translation('ACYM_SEPTEMBER'), 0, 3), substr(acym_translation('ACYM_OCTOBER'), 0, 3), substr(acym_translation('ACYM_NOVEMBER'), 0, 3), substr(acym_translation('ACYM_DECEMBER'), 0, 3)),
            date('d F Y H:i', $value)
        );
    } else {
        $shownValue = $value;
    }
    $result = '<input data-rs="'.$id.'" type="hidden" name="'.acym_escape($name).'" value="'.acym_escape($value).'">'.acym_modal(
            '<input data-open="'.$id.'" class="rs_date_field" type="text" value="'.$shownValue.'" readonly>',
            $result,
            $id,
            '',
            '',
            false,
            false
        );

    return $result;
}

function acym_escape($text)
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

function acydump($arg, $magic = false)
{
    ob_start();
    var_dump($arg);
    $result = ob_get_clean();

    if ($magic) {
        file_put_contents(
            ACYM_ROOT.'acydebug.txt',
            $result,
            FILE_APPEND
        );
    } else {
        echo '<pre style="margin-left: 220px;">'.$result.'</pre>';
    }
}

function acym_teasing($text)
{
    echo '<div class="acym__hide__teasing"></div>
          <div class="acym__ribbon__label acym__color__white acym__background-color__blue"><span>'.$text.'</span></div>
          ';
}

function acym_line_chart($id = '', $dataMonth, $dataDay, $dataHour)
{
    acym_initializeChart();

    $month = array();
    $openMonth = array();
    $clickMonth = array();

    foreach ($dataMonth as $key => $data) {
        $month[] = '"'.$key.'"';
        $openMonth[] = '"'.$data['open'].'"';
        $clickMonth[] = '"'.$data['click'].'"';
    }

    $day = array();
    $openDay = array();
    $clickDay = array();

    foreach ($dataDay as $key => $data) {
        $day[] = '"'.$key.'"';
        $openDay[] = '"'.$data['open'].'"';
        $clickDay[] = '"'.$data['click'].'"';
    }

    $hour = array();
    $openHour = array();
    $clickHour = array();

    foreach ($dataHour as $key => $data) {
        $hour[] = '"'.$key.'"';
        $openHour[] = '"'.$data['open'].'"';
        $clickHour[] = '"'.$data['click'].'"';
    }

    $idCanvas = 'acy_canvas_rand_id'.rand(1000, 9000);
    $idLegend = 'acy_legend_rand_id'.rand(1000, 9000);
    $return = '';

    $nbDataDay = count($dataDay);
    $nbDataHour = count($dataHour);
    $selectedChartHour = "";
    $selectedChartDay = "";
    $selectedChartMonth = "";

    if ($nbDataHour < 49) {
        $selectedChartHour = "selected__choose_by";
        $displayed = $hour;
        $clickDisplayed = $clickHour;
        $openDisplayed = $openHour;
    } else if ($nbDataDay < 63) {
        $selectedChartDay = "selected__choose_by";
        $displayed = $day;
        $clickDisplayed = $clickDay;
        $openDisplayed = $openDay;
    } else {
        $selectedChartMonth = "selected__choose_by";
        $displayed = $month;
        $clickDisplayed = $clickMonth;
        $openDisplayed = $openMonth;
    }


    $return .= '<div class="acym__chart__line__container" id="'.$id.'">
                    <div class="acym__chart__line__choose__by">
                        <p class="acym__chart__line__choose__by__one '.$selectedChartMonth.'" onclick="acymChartLineUpdate(this, \'month\')">'.acym_translation('ACYM_BY_MONTH').'</p>
                        <p class="acym__chart__line__choose__by__one '.$selectedChartDay.'" onclick="acymChartLineUpdate(this, \'day\')">'.acym_translation('ACYM_BY_DAY').'</p>
                        <p class="acym__chart__line__choose__by__one '.$selectedChartHour.'" onclick="acymChartLineUpdate(this, \'hour\')">'.acym_translation('ACYM_BY_HOUR').'</p>
                    </div>
                    <div class="acym__chart__line__legend" id="'.$idLegend.'"></div>
                    <canvas id="'.$idCanvas.'" height="400" width="400"></canvas>
                </div>';

    $return .= '<script>
                    var ctx = document.getElementById("'.$idCanvas.'").getContext("2d");
                    
                    var gradientBlue = ctx.createLinearGradient(0, 0, 0, 400);
                    gradientBlue.addColorStop(0, "rgba(128,182,244,0.5)"); 
                    gradientBlue.addColorStop(0.5, "rgba(128,182,244,0.25)"); 
                    gradientBlue.addColorStop(1, "rgba(128,182,244,0)"); 
                    
                    var gradientRed = ctx.createLinearGradient(0, 0, 0, 400);
                    gradientRed.addColorStop(0., "rgba(255,82,89,0.5)"); 
                    gradientRed.addColorStop(0.5, "rgba(255,82,89,0.25)"); 
                    gradientRed.addColorStop(1, "rgba(255,82,89,0)"); 
                    
                    var config = {
                        type: "line",
                        data: {
                            labels: ["'.acym_translation('ACYM_SENT').'", '.implode(',', $displayed).'],
                            datasets: [{ //We place the open before, because there are less than the clicks
                                label: "'.acym_translation('ACYM_CLICK').'",
                                data: ["0", '.implode(',', $clickDisplayed).'],
                                borderColor: "#00a4ff",
                                fill: true,
                                backgroundColor: gradientBlue,
                                borderWidth: 3,
                                pointBackgroundColor: "#ffffff",
                                pointRadius: 5,
                            },{
                                label: "'.acym_translation('ACYM_OPEN').'",
                                data: ["0", '.implode(',', $openDisplayed).'],
                                borderColor: "#ff5259",
                                fill: true,
                                backgroundColor: gradientRed,
                                borderWidth: 3,
                                pointBackgroundColor: "#ffffff",
                                pointRadius: 5,
                            },]
                        }, options: {
                            responsive: true,
                             legend: { //We make custom legends
                                display: false,
                             }, 
                            tooltips: { //on hover the dot
                                backgroundColor: "#fff",
                                borderWidth: 2,
                                borderColor: "#303e46",
                                titleFontSize: 16,
                                titleFontColor: "#303e46",
                                bodyFontColor: "#303e46",
                                bodyFontSize: 14,
                                displayColors: false
                            },
                            maintainAspectRatio: false, //to fit in the div
                            scales: {
                                yAxes: [{
                                    gridLines: {
                                        display: false
                                    },
                                    ticks: { //label on the axesY
                                        display: true,
                                        fontColor: "#0a0a0a"
                                    }
                                }],
                                xAxes: [{
                                    gridLines: {
                                        display: false
                                    },
                                    ticks: { //label on the axesX
                                        display: true,
                                        fontSize: 14,
                                        fontColor: "#0a0a0a"
                                    }
                                }],
                            },
                            legendCallback: function(chart) { //custom legends
                                var text = [];
                                for (var i = 0; i < chart.data.datasets.length; i++) {
                                  if (chart.data.datasets[i].label) {
                                    text.push(\'<div onclick="updateDataset(event, \'+ chart.legend.legendItems[i].datasetIndex + \', this)" class="acym_chart_line_labels"><i class="fa fa-circle" style="color: \' + chart.data.datasets[i].borderColor + \'"></i><span>\' + chart.data.datasets[i].label+\'</span></div>\');

                                  }
                                }
                                return text.join("");
                            },
                        }
                    };
                    var chart = new Chart(ctx, config);
                    document.getElementById("'.$idLegend.'").innerHTML = (chart.generateLegend());
                    updateDataset = function(e, datasetIndex, element) { //hide and show dataset for the custom legends
                        element = element.children[1];
                        var index = datasetIndex;
                        var ci = e.view.chart;
                        var meta = ci.getDatasetMeta(index);
                        
                        meta.hidden = meta.hidden === null? !ci.data.datasets[index].hidden : null;
                        
                        if(element.style.textDecoration == "line-through"){
                            element.style.textDecoration = "none";
                        }else{
                            element.style.textDecoration = "line-through";
                        }
                        
                        ci.update();
                    };
                    acymChartLineUpdate = function(elem, by){
                    	var chartLineLabels = document.getElementsByClassName("acym_chart_line_labels");
                    	for	(var i = 0; i < chartLineLabels.length; i++){
                    		chartLineLabels[i].getElementsByTagName("span")[0].style.textDecoration = "none";
                    	}
                        if(by == "month"){
                            var labels = ["'.acym_translation('ACYM_SENT').'", '.implode(',', $month).'];
                            var dataOpen = ["0", '.implode(',', $openMonth).'];
                            var dataClick = ["0", '.implode(',', $clickMonth).'];
                        }else if(by == "day"){
                            var labels = ["'.acym_translation('ACYM_SENT').'", '.implode(',', $day).'];
                            var dataOpen = ["0", '.implode(',', $openDay).'];
                            var dataClick = ["0", '.implode(',', $clickDay).'];
                        }else if(by == "hour"){
                            var labels = ["'.acym_translation('ACYM_SENT').'", '.implode(',', $hour).'];
                            var dataOpen = ["0", '.implode(',', $openHour).'];
                            var dataClick = ["0", '.implode(',', $clickHour).'];
                        }
                        chart.config.data.labels = labels,
                        chart.config.data.datasets = [{ //We place the open before, because there are less than the clicks
                                label: "'.acym_translation('ACYM_CLICK').'",
                                data: dataClick,
                                borderColor: "#00a4ff",
                                fill: true,
                                backgroundColor: gradientBlue,
                                borderWidth: 3,
                                pointBackgroundColor: "#ffffff",
                                pointRadius: 5,
                            },{
                                label: "'.acym_translation('ACYM_OPEN').'",
                                data: dataOpen,
                                borderColor: "#ff5259",
                                fill: true,
                                backgroundColor: gradientRed,
                                borderWidth: 3,
                                pointBackgroundColor: "#ffffff",
                                pointRadius: 5,
                            }
                        ];
                        chart.update();
                        var allChooseBy = document.getElementsByClassName("acym__chart__line__choose__by__one");
                        for(var i = 0; i < allChooseBy.length;i++){
                            allChooseBy[i].classList.remove("selected__choose_by");
                        }
                        elem.classList.add("selected__choose_by");
                    }
                </script>';

    return $return;
}

function acym_initializeChart()
{
    static $loaded = false;

    if (!$loaded) {
        acym_addScript(false, ACYM_JS.'libraries/chart.min.js?v='.filemtime(ACYM_MEDIA.'js'.DS.'libraries'.DS.'chart.min.js'), 'text/javascript', false, false, true);
        $loaded = true;
    }
}

function acym_round_chart($id, $pourcentage, $type = '', $class = '', $topLabel = '', $bottomLabel = '', $colorChart = '')
{
    if ($pourcentage != 0 && empty($pourcentage)) {
        return;
    }

    acym_initializeChart();

    if (empty($id)) {
        $id = 'acy_round_chart_rand_id'.rand(1000, 9000);
    }

    $green = '#3dea91';
    $red = '#ff5259';
    $orange = '#ffab15';
    $defaultColor = '#00a4ff';

    $isFixColor = false;
    $isInverted = false;

    switch ($type) {
        case 'click':
            $valueHigh = 5;
            $valueLow = 1;
            break;
        case 'open':
            $valueHigh = 30;
            $valueLow = 18;
            break;
        case 'delivery':
            $valueHigh = 90;
            $valueLow = 70;
            break;
        case 'fail':
            $valueHigh = 30;
            $valueLow = 10;
            $isInverted = true;
            break;
        default:
            $isFixColor = true;
    }

    if ($isFixColor) {
        $color = !empty($colorChart) ? $colorChart : $defaultColor;
    } else {
        if ($pourcentage >= $valueHigh) {
            $color = $isInverted ? $red : $green;
        } else if ($pourcentage < $valueHigh && $pourcentage >= $valueLow) {
            $color = $orange;
        } else if ($pourcentage < $valueLow) {
            $color = $isInverted ? $green : $red;
        } else {
            $color = $defaultColor;
        }
    }

    $idCanvas = 'acy_canvas_rand_id'.rand(1000, 9000);

    $return = '<div class="'.$class.' acym__chart__doughnut text-center">
                        <p class="text-center acym__chart__doughnut__container__top-label">'.$topLabel.'</p>
                        <div class="acym__chart__doughnut__container" id="'.$id.'">
                            <canvas id="'.$idCanvas.'" width="200" height="200"></canvas>
                        </div>
                        <p class="acym__chart__doughnut__container__bottom-label text-center">'.$bottomLabel.'</p>
                </div>';
    $return .= '<script>
            Chart.pluginService.register({
                beforeDraw: function(chart){
                    if(chart.config.options.elements.center){
                        var ctx = chart.chart.ctx;
        
                        var centerConfig = chart.config.options.elements.center;
                        var fontStyle = centerConfig.fontStyle || "Arial";
                        var txt = centerConfig.text;
                        var color = centerConfig.color || "#000";
                        ctx.textAlign = "center";
                        ctx.textBaseline = "middle";
                        var centerX = ((chart.chartArea.left + chart.chartArea.right) / 2);
                        var centerY = ((chart.chartArea.top + chart.chartArea.bottom) / 2);
                        ctx.font = "15px " + fontStyle;
                        ctx.fillStyle = color;
        
                        ctx.fillText(txt, centerX, centerY);
                    }
                }
            });
            var ctx = document.getElementById("'.$idCanvas.'").getContext("2d");
            var config = {
                type: "doughnut", data: {
                    datasets: [{
                        data: ['.$pourcentage.', (100 - '.$pourcentage.')], //Data of chart
                         backgroundColor: ["'.$color.'", "#f1f1f1"], //Two color of chart
                         borderWidth: 0 //no border
                    }]
                }, options: {
                    responsive: true,
                     legend: {
                        display: false,
                     }, 
                    elements: {
                        center: {
                            text: "'.$pourcentage.'%", color: "#363636", 
                            fontStyle: "Poppins", 
                            sidePadding: 70 
                        }
                    }, 
                    cutoutPercentage: 90, //thickness donut
                    tooltips: {
                        enabled: false //disable the tooltips on hover
                    }
                }
            };
            var chart = new Chart(ctx, config);
        </script>';


    return $return;
}

function acym_getEmailRegex($secureJS = false, $forceRegex = false)
{
    $config = acym_config();
    if ($forceRegex || $config->get('special_chars', 0) == 0) {
        $regex = '[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+)*\@([a-z0-9-]+\.)+[a-z0-9]{2,20}';
    } else {
        $regex = '.+\@(.+\.)+.{2,20}';
    }

    if ($secureJS) {
        $regex = str_replace(array('"', "'"), array('\"', "\'"), $regex);
    }

    return $regex;
}

function acym_isValidEmail($email, $extended = false)
{
    if (empty($email) || !is_string($email)) {
        return false;
    }

    if (!preg_match('/^'.acym_getEmailRegex().'$/i', $email)) {
        return false;
    }

    if (!$extended) {
        return true;
    }


    $config = acym_config();

    if ($config->get('email_checkdomain', false) && function_exists('getmxrr')) {
        $domain = substr($email, strrpos($email, '@') + 1);
        $mxhosts = array();
        $checkDomain = getmxrr($domain, $mxhosts);
        if (!empty($mxhosts) && strpos($mxhosts[0], 'hostnamedoesnotexist')) {
            array_shift($mxhosts);
        }
        if (!$checkDomain || empty($mxhosts)) {
            $dns = @dns_get_record($domain, DNS_A);
            $domainChanged = true;
            foreach ($dns as $oneRes) {
                if (strtolower($oneRes['host']) == strtolower($domain)) {
                    $domainChanged = false;
                }
            }
            if (empty($dns) || $domainChanged) {
                return false;
            }
        }
    }

    $object = new stdClass();
    $object->IP = acym_getIP();
    $object->emailAddress = $email;

    if ($config->get('email_iptimecheck', 0)) {
        $lapseTime = time() - 7200;
        $nbUsers = acym_loadResult('SELECT COUNT(*) FROM #__acym_user WHERE creation_date > '.intval($lapseTime).' AND ip = '.acym_escapeDB($object->IP));
        if ($nbUsers >= 3) {
            return false;
        }
    }

    return true;
}

function acym_getIP()
{
    $ip = '';
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) && strlen($_SERVER['HTTP_X_FORWARDED_FOR']) > 6) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } elseif (!empty($_SERVER['HTTP_CLIENT_IP']) && strlen($_SERVER['HTTP_CLIENT_IP']) > 6) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['REMOTE_ADDR']) && strlen($_SERVER['REMOTE_ADDR']) > 6) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }

    return strip_tags($ip);
}

function acym_radio($options, $name, $selected = null, $id = null, $attributes = array(), $objValue = 'value', $objText = 'text', $useIncrement = false)
{
    $id = preg_replace(
        '#[^a-zA-Z0-9_]+#mi',
        '_',
        str_replace(
            array('[', ']'),
            array('_', ''),
            empty($id) ? $name : $id
        )
    );

    $attributes['type'] = 'radio';
    $attributes['name'] = $name;

    $return = '<div class="acym_radio_group">';
    $k = 0;
    foreach ($options as $value => $label) {
        if (is_object($label)) {
            if (!empty($label->class)) {
                $attributes['class'] = $label->class;
            }

            $value = $label->$objValue;
            $label = $label->$objText;
        }

        $currentId = $useIncrement ? $id.$k : $id.$value;

        $attributes['value'] = $value;
        $attributes['id'] = $currentId;

        $return .= '<i data-radio="'.$currentId.'" class="material-icons acym_radio_checked">radio_button_checked</i>';
        $return .= '<i data-radio="'.$currentId.'" class="material-icons acym_radio_unchecked">radio_button_unchecked</i>';
        $return .= '<input';
        foreach ($attributes as $attribute => $val) {
            $return .= ' '.$attribute.'="'.acym_escape($val).'"';
        }
        $return .= ($value == $selected ? ' checked="checked"' : '').' />';
        $return .= '<label for="'.$currentId.'" id="'.$currentId.'-lbl">'.$label.'</label>';
        $k++;
    }
    $return .= '</div>';

    return $return;
}

function acym_convertPHPToMomentFormat($format)
{
    $replacements = array(
        'd' => 'DD',
        'D' => 'ddd',
        'j' => 'D',
        'l' => 'dddd',
        'N' => 'E',
        'S' => 'o',
        'w' => 'e',
        'z' => 'DDD',
        'W' => 'W',
        'F' => 'MMMM',
        'm' => 'MM',
        'M' => 'MMM',
        'n' => 'M',
        't' => '', // no equivalent
        'L' => '', // no equivalent
        'o' => 'YYYY',
        'Y' => 'YYYY',
        'y' => 'YY',
        'a' => 'a',
        'A' => 'A',
        'B' => '', // no equivalent
        'g' => 'h',
        'G' => 'H',
        'h' => 'hh',
        'H' => 'HH',
        'i' => 'mm',
        's' => 'ss',
        'u' => 'SSS',
        'e' => 'zz', // deprecated since version 1.6.0 of moment.js
        'I' => '', // no equivalent
        'O' => '', // no equivalent
        'P' => '', // no equivalent
        'T' => '', // no equivalent
        'Z' => '', // no equivalent
        'c' => '', // no equivalent
        'r' => '', // no equivalent
        'U' => 'X',
    );
    $momentFormat = strtr($format, $replacements);

    return $momentFormat;
}

function acym_boolean($name, $selected = null, $id = null, $attributes = array(), $yes = 'ACYM_YES', $no = 'ACYM_NO')
{
    $options = array(
        '1' => acym_translation($yes),
        '0' => acym_translation($no),
    );

    return acym_radio(
        $options,
        $name,
        $selected ? 1 : 0,
        $id,
        $attributes
    );
}

function acym_select(
    $data,
    $name,
    $selected = null,
    $attribs = null,
    $optKey = 'value',
    $optText = 'text',
    $idtag = false,
    $translate = false
) {
    $dropdown = '<select id="'.str_replace(
            array('[', ']', ' '),
            '',
            empty($idtag) ? $name : $idtag
        ).'" name="'.$name.'" '.(empty($attribs) ? '' : $attribs).'>';

    foreach ($data as $key => $oneOption) {
        $disabled = false;
        if (is_object($oneOption)) {
            $value = $oneOption->$optKey;
            $text = $oneOption->$optText;
            if (isset($oneOption->disable)) {
                $disabled = $oneOption->disable;
            }
        } else {
            $value = $key;
            $text = $oneOption;
        }

        if ($translate) {
            $text = acym_translation($text);
        }

        if (strtolower($value) == '<optgroup>') {
            $dropdown .= '<optgroup label="'.acym_escape($text).'">';
        } elseif (strtolower($value) == '</optgroup>') {
            $dropdown .= '</optgroup>';
        } else {
            $cleanValue = acym_escape($value);
            $cleanText = acym_escape($text);
            $dropdown .= '<option value="'.$cleanValue.'"'.($value == $selected ? ' selected="selected"' : '').($disabled ? ' disabled="disabled"' : '').'>'.$cleanText.'</option>';
        }
    }

    $dropdown .= '</select>';

    return $dropdown;
}

function acym_modal($button, $data, $id = null, $attributesModal = '', $attributesButton = '', $isButton = true, $isLarge = true)
{
    if (empty($id)) {
        $id = 'acymodal_'.rand(1000, 9000);
    }

    $modal = $isButton ? '<button type="button" data-open="'.$id.'" '.$attributesButton.'>'.$button.'</button>' : $button;
    $modal .= '<div class="reveal" '.($isLarge ? 'data-reveal-larger' : '').' id="'.$id.'" '.$attributesModal.' data-reveal>';
    $modal .= $data;
    $modal .= '<button class="close-button" data-close aria-label="Close reveal" type="button">';
    $modal .= '<span aria-hidden="true">&times;</span>';
    $modal .= '</button ></div>';

    return $modal;
}

function acym_modal_include($button, $file, $id, $data, $attributes = '')
{
    if (empty($id)) {
        $id = 'acymodal_'.rand(1000, 9000);
    }

    $modal = '<div data-open="'.acym_escape($id).'">'.$button;
    $modal .= '<div class="reveal" id="'.acym_escape($id).'" '.$attributes.' data-reveal>';
    ob_start();
    include($file);
    $modal .= ob_get_clean();
    $modal .= '<button type="button" class="close-button" data-close aria-label="Close reveal">';
    $modal .= '<span aria-hidden="true">&times;</span>';
    $modal .= '</button></div></div>';

    return $modal;
}

function acym_modal_pagination_users($button, $class, $textButton = null, $id = null, $attributes = '', $hiddenUsers = '', $task = '')
{
    $searchField = acym_filterSearch('', 'modal_search_users', 'ACYM_SEARCH_A_USER_NAME');


    $data = '
            <input type="hidden" name="show_selected" value="false" id="modal__pagination__users__show-information">
            <input type="hidden" name="users_selected" id="acym__modal__users-selected" value="">
            <input type="hidden" name="users_hidden" id="acym__modal__users-hidden" value=\''.$hiddenUsers.'\'>
            <input type="hidden" id="modal__pagination__users__search__input">
            <input type="hidden" name="form_task" id="acym__modal__users__form-task" value="'.$task.'">
            <div class="cell grid-x">
                <h4 class="cell text-center acym__modal__pagination__users__title">
                    '.acym_translation('ACYM_CHOOSE_USERS').'</h4>
            </div>  
              <div class="cell grid-x modal__pagination__users__search">
                  '.$searchField.'
              </div>
               <div class="cell text-center" id="modal__pagination__users__search__spinner" style="display: none">
                <i class="fa fa-circle-o-notch fa-spin"></i>
               </div>
              <div class="cell medium-6 modal__pagination__show">
                <a href="#" class="acym__color__blue modal__pagination__users__show-selected modal__pagination__users__show-button selected">
                    '.acym_translation('ACYM_SHOW_SELECTED_USERS').'</a>
                <a href="#" class="acym__color__blue modal__pagination__users__show-all modal__pagination__users__show-button">
                    '.acym_translation('ACYM_SHOW_ALL_USERS').'</a>
              </div>
              <div class="cell grid-x modal__pagination__users__listing">
                  <div class="cell modal__pagination__users__listing__in-form">                  
                  </div>
              </div>';

    $data .= '<div class="cell grid-x"><div class="cell medium-auto"></div><div class="cell medium-shrink"><button type="button" text-empty="'.acym_translation('ACYM_PLEASE_SELECT_USER').'" class="cell button primary" id="modal__pagination__users__confirm">'.$textButton.'</button></div><div class="cell medium-auto"></div></div>';

    $attributesButton = 'class="modal__pagination__users__button-open button '.$class.'" '.$attributes;

    return acym_modal($button, $data, $id, "", $attributesButton);
}

function acym_modal_pagination_lists($button, $class, $textButton = null, $id = null, $attributes = '', $isModal = true, $inputEventId = "", $checkedLists = "[]", $needDisplaySubscribers = false, $attributesModal = '')
{
    $searchField = acym_filterSearch('', 'modal_search_lists', 'ACYM_SEARCH_A_LIST_NAME');

    $data = "";

    if (!empty($inputEventId)) {
        $data .= '<input type="hidden" id="'.$inputEventId.'">';
    }
    if ($needDisplaySubscribers) {
        $data .= '<input type="hidden" id="modal__pagination__need__display__sub">';
    }

    $data .= '<div class="cell grid-x" '.$attributesModal.'>
            <input type="hidden" name="show_selected" value="false" id="modal__pagination__show-information">
            <input type="hidden" id="modal__pagination__search__lists">
            <input type="hidden" name="lists_selected" id="acym__modal__lists-selected" value=\''.$checkedLists.'\'>
            <div class="cell grid-x">
                <h4 class="cell text-center acym__modal__pagination__title">'.acym_translation('ACYM_CHOOSE_LISTS').'</h4>
            </div>    
            <div class="cell grid-x modal__pagination__search">
                '.$searchField.'
            </div>
            <div class="cell text-center" id="modal__pagination__search__spinner" style="display: none">
                <i class="fa fa-circle-o-notch fa-spin"></i>
            </div>
            <div class="cell medium-6 modal__pagination__show">
                <a href="#" class="acym__color__blue modal__pagination__show-selected modal__pagination__show-button selected">'.acym_translation('ACYM_SHOW_SELECTED_LISTS').'</a>
                <a href="#" class="acym__color__blue modal__pagination__show-all modal__pagination__show-button">'.acym_translation('ACYM_SHOW_ALL_LISTS').'</a>
            </div>
            <div class="cell grid-x modal__pagination__listing__lists">
                <div class="cell modal__pagination__listing__lists__in-form"></div>
            </div>
            </div>';

    if ($isModal) {
        $data .= '<div class="cell grid-x"><div class="cell medium-auto"></div><div class="cell medium-shrink"><button type="button" text-empty="'.acym_translation('ACYM_PLEASE_SELECT_LIST').'" class="button primary" id="modal__pagination__confirm">'.$textButton.'</button></div><div class="cell medium-auto"></div></div>';
        $attributesButton = 'class="modal__pagination__button-open button '.$class.'" '.$attributes;

        return acym_modal($button, $data, $id, "", $attributesButton);
    } else {
        return $data;
    }
}

function acym_modal_pagination_lists_import($button, $class, $textButtonRight, $id = null, $attributes = '', $classButton = 'acym__users__import__generic__import__button', $severalButton = false)
{
    if (empty($id)) {
        $id = 'acymodal_pagination_lists_'.rand(1000, 9000);
    }

    $listsPerPage = acym_getCMSConfig('list_limit', 20);
    $ajaxURL = acym_prepareAjaxURL('lists').'&task=setAjaxListing&listsPerPage='.$listsPerPage;

    $searchField = acym_filterSearch('', 'modal_search_lists', 'ACYM_SEARCH_A_LIST_NAME');

    $data = '
            <input type="hidden" name="show_selected" value="false" id="modal__pagination__show-information">
            <input type="hidden" name="lists_selected" id="acym__modal__lists-selected" value="">
            <input type="hidden" name="ajaxURL" id="modal_ajaxURL" value="'.$ajaxURL.'">
            <input type="hidden" id="modal__pagination__search__lists">
            <div class="cell grid-x">
                <h4 class="cell text-center acym__modal__pagination__title">
                    '.acym_translation('ACYM_CHOOSE_LISTS').'</h4>
                </div>  
                <div class="cell grid-x modal__pagination__search">
                  '.$searchField.'
              </div>
               <div class="cell text-center" id="modal__pagination__search__spinner" style="display: none">
                    <i class="fa fa-circle-o-notch fa-spin"></i>
                </div>
                <div class="cell medium-6 modal__pagination__show">
                <a href="#" class="acym__color__blue modal__pagination__show-selected modal__pagination__show-button selected">
                    '.acym_translation('ACYM_SHOW_SELECTED_LISTS').'</a>
                <a href="#" class="acym__color__blue modal__pagination__show-all modal__pagination__show-button">
                    '.acym_translation('ACYM_SHOW_ALL_LISTS').'</a>
                </div>
              <div class="cell grid-x modal__pagination__listing__lists">
                  <div class="cell modal__pagination__listing__lists__in-form">                  
                  </div>
              </div>
              <div id="acym__modal__lists__import__create-area">
                 <input id="modal__pagination__create__list" class="input-group-field" type="text" name="new_list_modal" placeholder="'.acym_translation('ACYM_CREATE_NEW_LIST').'">
                 <i class="fa fa-close" id="acym__modal__lists_import__clear"></i>
              </div>
              <div class="cell grid-margin-x large-up-2 small-up-1" id="modal__pagination__listing__button-submit">   
                <button type="button" class="cell button '.$classButton.'" id="lists">'.$textButtonRight.'</button>
              </div>';

    $modal = $severalButton ? '' : '<button type="button" data-open="'.$id.'" class="modal__pagination__button-open button '.$class.'" '.$attributes.'>'.$button.'</button>';
    $modal .= '<div class="reveal grid-x" id="'.$id.'" data-reveal>';
    $modal .= $data;
    $modal .= '<button class="close-button" data-close aria-label="Close reveal" type="button">';
    $modal .= '<span aria-hidden = "true" >&times;</span>';
    $modal .= '</button ></div>';

    return $modal;
}

function acym_generateCountryNumber($name, $defaultvalue = '')
{
    $flagPosition = array();
    $flagPosition['93'] = array('x' => -48, 'y' => 0);
    $flagPosition['355'] = array('x' => -96, 'y' => 0);
    $flagPosition['213'] = array('x' => -160, 'y' => -33);
    $flagPosition['1684'] = array('x' => -176, 'y' => 0);
    $flagPosition['376'] = array('x' => -16, 'y' => 0);
    $flagPosition['244'] = array('x' => -144, 'y' => 0);
    $flagPosition['1264'] = array('x' => -80, 'y' => 0);
    $flagPosition['672'] = array('x' => 0, 'y' => -176); //antartica
    $flagPosition['1268'] = array('x' => -64, 'y' => 0);
    $flagPosition['54'] = array('x' => -160, 'y' => 0);
    $flagPosition['374'] = array('x' => -112, 'y' => 0);
    $flagPosition['297'] = array('x' => -224, 'y' => 0);
    $flagPosition['247'] = array('x' => -16, 'y' => -176); //ascenscion island
    $flagPosition['61'] = array('x' => -208, 'y' => 0);
    $flagPosition['43'] = array('x' => -192, 'y' => 0);
    $flagPosition['994'] = array('x' => -240, 'y' => 0);
    $flagPosition['1242'] = array('x' => -208, 'y' => -11);
    $flagPosition['973'] = array('x' => -96, 'y' => -11);
    $flagPosition['880'] = array('x' => -32, 'y' => -11);
    $flagPosition['1246'] = array('x' => -16, 'y' => -11);
    $flagPosition['375'] = array('x' => -16, 'y' => -22);
    $flagPosition['32'] = array('x' => -48, 'y' => -11);
    $flagPosition['501'] = array('x' => -32, 'y' => -22);
    $flagPosition['229'] = array('x' => -128, 'y' => -11);
    $flagPosition['1441'] = array('x' => -144, 'y' => -11);
    $flagPosition['975'] = array('x' => -224, 'y' => -11);
    $flagPosition['591'] = array('x' => -176, 'y' => -11);
    $flagPosition['387'] = array('x' => 0, 'y' => -11);
    $flagPosition['267'] = array('x' => 0, 'y' => -22);
    $flagPosition['55'] = array('x' => -192, 'y' => -11);
    $flagPosition['1284'] = array('x' => -240, 'y' => -154);
    $flagPosition['673'] = array('x' => -160, 'y' => -11);
    $flagPosition['359'] = array('x' => -80, 'y' => -11);
    $flagPosition['226'] = array('x' => -64, 'y' => -11);
    $flagPosition['257'] = array('x' => -112, 'y' => -11);
    $flagPosition['855'] = array('x' => -64, 'y' => -77);
    $flagPosition['237'] = array('x' => -192, 'y' => -22);
    $flagPosition['1'] = array('x' => -48, 'y' => -22);
    $flagPosition['238'] = array('x' => -16, 'y' => -33);
    $flagPosition['1345'] = array('x' => -192, 'y' => -77);
    $flagPosition['236'] = array('x' => -96, 'y' => -22);
    $flagPosition['235'] = array('x' => -112, 'y' => -143);
    $flagPosition['56'] = array('x' => -176, 'y' => -22);
    $flagPosition['86'] = array('x' => -208, 'y' => -22);
    $flagPosition['6724'] = array('x' => -32, 'y' => -176); //christmas island
    $flagPosition['6722'] = array('x' => -48, 'y' => -176); //coco keeling island
    $flagPosition['57'] = array('x' => -224, 'y' => -22);
    $flagPosition['269'] = array('x' => -96, 'y' => -77);
    $flagPosition['243'] = array('x' => -80, 'y' => -22);
    $flagPosition['242'] = array('x' => -112, 'y' => -22);
    $flagPosition['682'] = array('x' => -160, 'y' => -22);
    $flagPosition['506'] = array('x' => -240, 'y' => -22);
    $flagPosition['225'] = array('x' => -144, 'y' => -22);
    $flagPosition['385'] = array('x' => 0, 'y' => -66);
    $flagPosition['53'] = array('x' => 0, 'y' => -33);
    $flagPosition['357'] = array('x' => -48, 'y' => -33);
    $flagPosition['420'] = array('x' => -64, 'y' => -33);
    $flagPosition['45'] = array('x' => -112, 'y' => -33);
    $flagPosition['253'] = array('x' => -96, 'y' => -33);
    $flagPosition['1767'] = array('x' => -128, 'y' => -33);
    $flagPosition['1809'] = array('x' => -144, 'y' => -33);
    $flagPosition['593'] = array('x' => -176, 'y' => -33);
    $flagPosition['20'] = array('x' => -208, 'y' => -33);
    $flagPosition['503'] = array('x' => -32, 'y' => -143);
    $flagPosition['240'] = array('x' => -96, 'y' => -55);
    $flagPosition['291'] = array('x' => 0, 'y' => -44);
    $flagPosition['372'] = array('x' => -192, 'y' => -33);
    $flagPosition['251'] = array('x' => -32, 'y' => -44);
    $flagPosition['500'] = array('x' => -96, 'y' => -44);
    $flagPosition['298'] = array('x' => -128, 'y' => -44);
    $flagPosition['679'] = array('x' => -80, 'y' => -44);
    $flagPosition['358'] = array('x' => -64, 'y' => -44);
    $flagPosition['33'] = array('x' => -144, 'y' => -44);
    $flagPosition['596'] = array('x' => -80, 'y' => -99);
    $flagPosition['594'] = array('x' => -128, 'y' => -176); //french guiana
    $flagPosition['689'] = array('x' => -224, 'y' => -110);
    $flagPosition['241'] = array('x' => -160, 'y' => -44);
    $flagPosition['220'] = array('x' => -48, 'y' => -55);
    $flagPosition['995'] = array('x' => -208, 'y' => -44);
    $flagPosition['49'] = array('x' => -80, 'y' => -33);
    $flagPosition['233'] = array('x' => 0, 'y' => -55);
    $flagPosition['350'] = array('x' => -16, 'y' => -55);
    $flagPosition['30'] = array('x' => -112, 'y' => -55);
    $flagPosition['299'] = array('x' => -32, 'y' => -55);
    $flagPosition['1473'] = array('x' => -192, 'y' => -44);
    $flagPosition['590'] = array('x' => -80, 'y' => -55);
    $flagPosition['1671'] = array('x' => -160, 'y' => -55);
    $flagPosition['502'] = array('x' => -144, 'y' => -55);
    $flagPosition['224'] = array('x' => -64, 'y' => -55);
    $flagPosition['245'] = array('x' => -176, 'y' => -55);
    $flagPosition['592'] = array('x' => -192, 'y' => -55);
    $flagPosition['509'] = array('x' => -16, 'y' => -66);
    $flagPosition['504'] = array('x' => -240, 'y' => -55);
    $flagPosition['852'] = array('x' => -208, 'y' => -55);
    $flagPosition['36'] = array('x' => -32, 'y' => -66);
    $flagPosition['354'] = array('x' => -192, 'y' => -66);
    $flagPosition['91'] = array('x' => -128, 'y' => -66);
    $flagPosition['62'] = array('x' => -64, 'y' => -66);
    $flagPosition['964'] = array('x' => -160, 'y' => -66);
    $flagPosition['98'] = array('x' => -176, 'y' => -66);
    $flagPosition['353'] = array('x' => -80, 'y' => -66);
    $flagPosition['972'] = array('x' => -96, 'y' => -66);
    $flagPosition['39'] = array('x' => -208, 'y' => -66);
    $flagPosition['1876'] = array('x' => -240, 'y' => -66);
    $flagPosition['81'] = array('x' => -16, 'y' => -77);
    $flagPosition['962'] = array('x' => 0, 'y' => -77);
    $flagPosition['254'] = array('x' => -32, 'y' => -77);
    $flagPosition['686'] = array('x' => -80, 'y' => -77);
    $flagPosition['3774'] = array('x' => -64, 'y' => -176); //kosovo
    $flagPosition['965'] = array('x' => -176, 'y' => -77);
    $flagPosition['996'] = array('x' => -48, 'y' => -77);
    $flagPosition['856'] = array('x' => -224, 'y' => -77);
    $flagPosition['371'] = array('x' => -112, 'y' => -88);
    $flagPosition['961'] = array('x' => -240, 'y' => -77);
    $flagPosition['266'] = array('x' => -64, 'y' => -88);
    $flagPosition['231'] = array('x' => -48, 'y' => -88);
    $flagPosition['218'] = array('x' => -128, 'y' => -88);
    $flagPosition['423'] = array('x' => -16, 'y' => -88);
    $flagPosition['370'] = array('x' => -80, 'y' => -88);
    $flagPosition['352'] = array('x' => -96, 'y' => -88);
    $flagPosition['853'] = array('x' => -48, 'y' => -99);
    $flagPosition['389'] = array('x' => -240, 'y' => -88);
    $flagPosition['261'] = array('x' => -208, 'y' => -88);
    $flagPosition['265'] = array('x' => -176, 'y' => -99);
    $flagPosition['60'] = array('x' => -208, 'y' => -99);
    $flagPosition['960'] = array('x' => -160, 'y' => -99);
    $flagPosition['223'] = array('x' => 0, 'y' => -99);
    $flagPosition['356'] = array('x' => -128, 'y' => -99);
    $flagPosition['692'] = array('x' => -224, 'y' => -88);
    $flagPosition['222'] = array('x' => -96, 'y' => -99);
    $flagPosition['230'] = array('x' => -144, 'y' => -99);
    $flagPosition['52'] = array('x' => -192, 'y' => -99);
    $flagPosition['691'] = array('x' => -112, 'y' => -44);
    $flagPosition['373'] = array('x' => -176, 'y' => -88);
    $flagPosition['377'] = array('x' => -160, 'y' => -88);
    $flagPosition['976'] = array('x' => -32, 'y' => -99);
    $flagPosition['382'] = array('x' => -192, 'y' => -88);
    $flagPosition['1664'] = array('x' => -112, 'y' => -99);
    $flagPosition['212'] = array('x' => -144, 'y' => -88);
    $flagPosition['258'] = array('x' => -224, 'y' => -99);
    $flagPosition['95'] = array('x' => -16, 'y' => -99);
    $flagPosition['264'] = array('x' => -240, 'y' => -99);
    $flagPosition['674'] = array('x' => -128, 'y' => -110);
    $flagPosition['977'] = array('x' => -112, 'y' => -110);
    $flagPosition['31'] = array('x' => -80, 'y' => -110);
    $flagPosition['599'] = array('x' => -128, 'y' => 0);
    $flagPosition['687'] = array('x' => 0, 'y' => -110);
    $flagPosition['64'] = array('x' => -160, 'y' => -110);
    $flagPosition['505'] = array('x' => -64, 'y' => -110);
    $flagPosition['227'] = array('x' => -16, 'y' => -110);
    $flagPosition['234'] = array('x' => -48, 'y' => -110);
    $flagPosition['683'] = array('x' => -144, 'y' => -110);
    $flagPosition['6723'] = array('x' => -32, 'y' => -110);
    $flagPosition['850'] = array('x' => -128, 'y' => -77);
    $flagPosition['47'] = array('x' => -96, 'y' => -110);
    $flagPosition['968'] = array('x' => -176, 'y' => -110);
    $flagPosition['92'] = array('x' => -16, 'y' => -121);
    $flagPosition['680'] = array('x' => -80, 'y' => -176); //palau
    $flagPosition['970'] = array('x' => -96, 'y' => -121);
    $flagPosition['507'] = array('x' => -192, 'y' => -110);
    $flagPosition['675'] = array('x' => -240, 'y' => -110);
    $flagPosition['595'] = array('x' => -144, 'y' => -121);
    $flagPosition['51'] = array('x' => -208, 'y' => -110);
    $flagPosition['63'] = array('x' => 0, 'y' => -121);
    $flagPosition['48'] = array('x' => -32, 'y' => -121);
    $flagPosition['351'] = array('x' => -112, 'y' => -121);
    $flagPosition['1787'] = array('x' => -80, 'y' => -121);
    $flagPosition['974'] = array('x' => -160, 'y' => -121);
    $flagPosition['262'] = array('x' => -144, 'y' => -176); //reunion island
    $flagPosition['40'] = array('x' => -192, 'y' => -121);
    $flagPosition['7'] = array('x' => -224, 'y' => -121);
    $flagPosition['250'] = array('x' => -240, 'y' => -121);
    $flagPosition['1670'] = array('x' => -96, 'y' => -176); //marianne
    $flagPosition['378'] = array('x' => -176, 'y' => -132);
    $flagPosition['239'] = array('x' => -16, 'y' => -143);
    $flagPosition['966'] = array('x' => 0, 'y' => -132);
    $flagPosition['221'] = array('x' => -192, 'y' => -132);
    $flagPosition['381'] = array('x' => -208, 'y' => -121);
    $flagPosition['248'] = array('x' => -32, 'y' => -132);
    $flagPosition['232'] = array('x' => -160, 'y' => -132);
    $flagPosition['65'] = array('x' => -96, 'y' => -132);
    $flagPosition['421'] = array('x' => -144, 'y' => -132);
    $flagPosition['386'] = array('x' => -128, 'y' => -132);
    $flagPosition['677'] = array('x' => -16, 'y' => -132);
    $flagPosition['252'] = array('x' => -208, 'y' => -132);
    $flagPosition['685'] = array('x' => -112, 'y' => -176); //somoa
    $flagPosition['27'] = array('x' => -128, 'y' => -165);
    $flagPosition['82'] = array('x' => -144, 'y' => -77);
    $flagPosition['34'] = array('x' => -16, 'y' => -44);
    $flagPosition['94'] = array('x' => -32, 'y' => -88);
    $flagPosition['290'] = array('x' => -112, 'y' => -132);
    $flagPosition['1869'] = array('x' => -112, 'y' => -77);
    $flagPosition['1758'] = array('x' => 0, 'y' => -88);
    $flagPosition['508'] = array('x' => -48, 'y' => -121);
    $flagPosition['1784'] = array('x' => -208, 'y' => -154);
    $flagPosition['249'] = array('x' => -64, 'y' => -132);
    $flagPosition['597'] = array('x' => -240, 'y' => -132);
    $flagPosition['268'] = array('x' => -80, 'y' => -143);
    $flagPosition['46'] = array('x' => -80, 'y' => -132);
    $flagPosition['41'] = array('x' => -128, 'y' => -22);
    $flagPosition['963'] = array('x' => -64, 'y' => -143);
    $flagPosition['886'] = array('x' => -64, 'y' => -154);
    $flagPosition['992'] = array('x' => -176, 'y' => -143);
    $flagPosition['255'] = array('x' => -80, 'y' => -154);
    $flagPosition['66'] = array('x' => -160, 'y' => -143);
    $flagPosition['228'] = array('x' => -144, 'y' => -143);
    $flagPosition['690'] = array('x' => -192, 'y' => -143);
    $flagPosition['676'] = array('x' => 0, 'y' => -154);
    $flagPosition['1868'] = array('x' => -32, 'y' => -154);
    $flagPosition['216'] = array('x' => -240, 'y' => -143);
    $flagPosition['90'] = array('x' => -16, 'y' => -154);
    $flagPosition['993'] = array('x' => -224, 'y' => -143);
    $flagPosition['1649'] = array('x' => -96, 'y' => -143);
    $flagPosition['688'] = array('x' => -48, 'y' => -154);
    $flagPosition['256'] = array('x' => -112, 'y' => -154);
    $flagPosition['380'] = array('x' => -96, 'y' => -154);
    $flagPosition['971'] = array('x' => -32, 'y' => 0);
    $flagPosition['44'] = array('x' => -176, 'y' => -44);
    $flagPosition['598'] = array('x' => -160, 'y' => -154);
    $flagPosition['1 '] = array('x' => -144, 'y' => -154);
    $flagPosition['998'] = array('x' => -176, 'y' => -154);
    $flagPosition['678'] = array('x' => -32, 'y' => -165);
    $flagPosition['3966'] = array('x' => -192, 'y' => -154);
    $flagPosition['58'] = array('x' => -224, 'y' => -154);
    $flagPosition['84'] = array('x' => -16, 'y' => -165);
    $flagPosition['1340'] = array('x' => 0, 'y' => -165);
    $flagPosition['681'] = array('x' => -64, 'y' => -165);
    $flagPosition['967'] = array('x' => -96, 'y' => -165);
    $flagPosition['260'] = array('x' => -160, 'y' => -165);
    $flagPosition['263'] = array('x' => -176, 'y' => -165);
    $flagPosition[''] = array('x' => -160, 'y' => -176);


    $country = array();
    $country['93'] = 'Afghanistan';
    $country['355'] = 'Albania';
    $country['213'] = 'Algeria';
    $country['1684'] = 'American Samoa';
    $country['376'] = 'Andorra';
    $country['244'] = 'Angola';
    $country['1264'] = 'Anguilla';
    $country['672'] = 'Antarctica';
    $country['1268'] = 'Antigua & Barbuda';
    $country['54'] = 'Argentina';
    $country['374'] = 'Armenia';
    $country['297'] = 'Aruba';
    $country['247'] = 'Ascension Island';
    $country['61'] = 'Australia';
    $country['43'] = 'Austria';
    $country['994'] = 'Azerbaijan';
    $country['1242'] = 'Bahamas';
    $country['973'] = 'Bahrain';
    $country['880'] = 'Bangladesh';
    $country['1246'] = 'Barbados';
    $country['375'] = 'Belarus';
    $country['32'] = 'Belgium';
    $country['501'] = 'Belize';
    $country['229'] = 'Benin';
    $country['1441'] = 'Bermuda';
    $country['975'] = 'Bhutan';
    $country['591'] = 'Bolivia';
    $country['387'] = 'Bosnia/Herzegovina';
    $country['267'] = 'Botswana';
    $country['55'] = 'Brazil';
    $country['1284'] = 'British Virgin Islands';
    $country['673'] = 'Brunei';
    $country['359'] = 'Bulgaria';
    $country['226'] = 'Burkina Faso';
    $country['257'] = 'Burundi';
    $country['855'] = 'Cambodia';
    $country['237'] = 'Cameroon';
    $country['1'] = 'Canada/USA';
    $country['238'] = 'Cape Verde Islands';
    $country['1345'] = 'Cayman Islands';
    $country['236'] = 'Central African Republic';
    $country['235'] = 'Chad Republic';
    $country['56'] = 'Chile';
    $country['86'] = 'China';
    $country['6724'] = 'Christmas Island';
    $country['6722'] = 'Cocos Keeling Island';
    $country['57'] = 'Colombia';
    $country['269'] = 'Comoros';
    $country['243'] = 'Congo Democratic Republic';
    $country['242'] = 'Congo, Republic of';
    $country['682'] = 'Cook Islands';
    $country['506'] = 'Costa Rica';
    $country['225'] = 'Cote D\'Ivoire';
    $country['385'] = 'Croatia';
    $country['53'] = 'Cuba';
    $country['357'] = 'Cyprus';
    $country['420'] = 'Czech Republic';
    $country['45'] = 'Denmark';
    $country['253'] = 'Djibouti';
    $country['1767'] = 'Dominica';
    $country['1809'] = 'Dominican Republic';
    $country['593'] = 'Ecuador';
    $country['20'] = 'Egypt';
    $country['503'] = 'El Salvador';
    $country['240'] = 'Equatorial Guinea';
    $country['291'] = 'Eritrea';
    $country['372'] = 'Estonia';
    $country['251'] = 'Ethiopia';
    $country['500'] = 'Falkland Islands';
    $country['298'] = 'Faroe Island';
    $country['679'] = 'Fiji Islands';
    $country['358'] = 'Finland';
    $country['33'] = 'France';
    $country['596'] = 'French Antilles/Martinique';
    $country['594'] = 'French Guiana';
    $country['689'] = 'French Polynesia';
    $country['241'] = 'Gabon Republic';
    $country['220'] = 'Gambia';
    $country['995'] = 'Georgia';
    $country['49'] = 'Germany';
    $country['233'] = 'Ghana';
    $country['350'] = 'Gibraltar';
    $country['30'] = 'Greece';
    $country['299'] = 'Greenland';
    $country['1473'] = 'Grenada';
    $country['590'] = 'Guadeloupe';
    $country['1671'] = 'Guam';
    $country['502'] = 'Guatemala';
    $country['224'] = 'Guinea Republic';
    $country['245'] = 'Guinea-Bissau';
    $country['592'] = 'Guyana';
    $country['509'] = 'Haiti';
    $country['504'] = 'Honduras';
    $country['852'] = 'Hong Kong';
    $country['36'] = 'Hungary';
    $country['354'] = 'Iceland';
    $country['91'] = 'India';
    $country['62'] = 'Indonesia';
    $country['964'] = 'Iraq';
    $country['98'] = 'Iran';
    $country['353'] = 'Ireland';
    $country['972'] = 'Israel';
    $country['39'] = 'Italy';
    $country['1876'] = 'Jamaica';
    $country['81'] = 'Japan';
    $country['962'] = 'Jordan';
    $country['254'] = 'Kenya';
    $country['686'] = 'Kiribati';
    $country['3774'] = 'Kosovo';
    $country['965'] = 'Kuwait';
    $country['996'] = 'Kyrgyzstan';
    $country['856'] = 'Laos';
    $country['371'] = 'Latvia';
    $country['961'] = 'Lebanon';
    $country['266'] = 'Lesotho';
    $country['231'] = 'Liberia';
    $country['218'] = 'Libya';
    $country['423'] = 'Liechtenstein';
    $country['370'] = 'Lithuania';
    $country['352'] = 'Luxembourg';
    $country['853'] = 'Macau';
    $country['389'] = 'Macedonia';
    $country['261'] = 'Madagascar';
    $country['265'] = 'Malawi';
    $country['60'] = 'Malaysia';
    $country['960'] = 'Maldives';
    $country['223'] = 'Mali Republic';
    $country['356'] = 'Malta';
    $country['692'] = 'Marshall Islands';
    $country['222'] = 'Mauritania';
    $country['230'] = 'Mauritius';
    $country['52'] = 'Mexico';
    $country['691'] = 'Micronesia';
    $country['373'] = 'Moldova';
    $country['377'] = 'Monaco';
    $country['976'] = 'Mongolia';
    $country['382'] = 'Montenegro';
    $country['1664'] = 'Montserrat';
    $country['212'] = 'Morocco';
    $country['258'] = 'Mozambique';
    $country['95'] = 'Myanmar (Burma)';
    $country['264'] = 'Namibia';
    $country['674'] = 'Nauru';
    $country['977'] = 'Nepal';
    $country['31'] = 'Netherlands';
    $country['599'] = 'Netherlands Antilles';
    $country['687'] = 'New Caledonia';
    $country['64'] = 'New Zealand';
    $country['505'] = 'Nicaragua';
    $country['227'] = 'Niger Republic';
    $country['234'] = 'Nigeria';
    $country['683'] = 'Niue Island';
    $country['6723'] = 'Norfolk';
    $country['850'] = 'North Korea';
    $country['47'] = 'Norway';
    $country['968'] = 'Oman Dem Republic';
    $country['92'] = 'Pakistan';
    $country['680'] = 'Palau Republic';
    $country['970'] = 'Palestine';
    $country['507'] = 'Panama';
    $country['675'] = 'Papua New Guinea';
    $country['595'] = 'Paraguay';
    $country['51'] = 'Peru';
    $country['63'] = 'Philippines';
    $country['48'] = 'Poland';
    $country['351'] = 'Portugal';
    $country['1787'] = 'Puerto Rico';
    $country['974'] = 'Qatar';
    $country['262'] = 'Reunion Island';
    $country['40'] = 'Romania';
    $country['7'] = 'Russia';
    $country['250'] = 'Rwanda Republic';
    $country['1670'] = 'Saipan/Mariannas';
    $country['378'] = 'San Marino';
    $country['239'] = 'Sao Tome/Principe';
    $country['966'] = 'Saudi Arabia';
    $country['221'] = 'Senegal';
    $country['381'] = 'Serbia';
    $country['248'] = 'Seychelles Island';
    $country['232'] = 'Sierra Leone';
    $country['65'] = 'Singapore';
    $country['421'] = 'Slovakia';
    $country['386'] = 'Slovenia';
    $country['677'] = 'Solomon Islands';
    $country['252'] = 'Somalia Republic';
    $country['685'] = 'Somoa';
    $country['27'] = 'South Africa';
    $country['82'] = 'South Korea';
    $country['34'] = 'Spain';
    $country['94'] = 'Sri Lanka';
    $country['290'] = 'St. Helena';
    $country['1869'] = 'St. Kitts';
    $country['1758'] = 'St. Lucia';
    $country['508'] = 'St. Pierre';
    $country['1784'] = 'St. Vincent';
    $country['249'] = 'Sudan';
    $country['597'] = 'Suriname';
    $country['268'] = 'Swaziland';
    $country['46'] = 'Sweden';
    $country['41'] = 'Switzerland';
    $country['963'] = 'Syria';
    $country['886'] = 'Taiwan';
    $country['992'] = 'Tajikistan';
    $country['255'] = 'Tanzania';
    $country['66'] = 'Thailand';
    $country['228'] = 'Togo Republic';
    $country['690'] = 'Tokelau';
    $country['676'] = 'Tonga Islands';
    $country['1868'] = 'Trinidad & Tobago';
    $country['216'] = 'Tunisia';
    $country['90'] = 'Turkey';
    $country['993'] = 'Turkmenistan';
    $country['1649'] = 'Turks & Caicos Island';
    $country['688'] = 'Tuvalu';
    $country['256'] = 'Uganda';
    $country['380'] = 'Ukraine';
    $country['971'] = 'United Arab Emirates';
    $country['44'] = 'United Kingdom';
    $country['598'] = 'Uruguay';
    $country['1 '] = 'USA/Canada';
    $country['998'] = 'Uzbekistan';
    $country['678'] = 'Vanuatu';
    $country['3966'] = 'Vatican City';
    $country['58'] = 'Venezuela';
    $country['84'] = 'Vietnam';
    $country['1340'] = 'Virgin Islands (US)';
    $country['681'] = 'Wallis/Futuna Islands';
    $country['967'] = 'Yemen Arab Republic';
    $country['260'] = 'Zambia';
    $country['263'] = 'Zimbabwe';
    $country[''] = acym_translation('ACYM_PHONE_NOCOUNTRY');

    $countryCodeForSelect = array();

    foreach ($country as $key => $one) {
        $countryCodeForSelect[$key] = $one.' +'.$key;
    }

    return acym_select($countryCodeForSelect, $name, empty($defaultvalue) ? '' : $defaultvalue, 'class="acym__select__country"', 'value', 'text');
}

function acym_displayDateFormat($format, $name = 'date', $default = '14/06/1997', $attributes = '')
{
    $attributes = empty($attributes) ? 'class="acym__custom__fields__select__form "' : $attributes;
    $default = empty($default) ? '14/06/1997' : $default;
    $return = '<div class="cell grid-x grid-margin-x">';
    $days = array();
    for ($i = 1; $i != 31; $i++) {
        $days[$i < 10 ? '0'.$i : $i] = $i < 10 ? '0'.$i : $i;
    }
    $month = array(
        '01' => acym_translation('ACYM_JANUARY'),
        '02' => acym_translation('ACYM_FEBRUARY'),
        '03' => acym_translation('ACYM_MARCH'),
        '04' => acym_translation('ACYM_APRIL'),
        '05' => acym_translation('ACYM_MAY'),
        '06' => acym_translation('ACYM_JUNE'),
        '07' => acym_translation('ACYM_JULY'),
        '08' => acym_translation('ACYM_AUGUST'),
        '09' => acym_translation('ACYM_SEPTEMBER'),
        '10' => acym_translation('ACYM_OCTOBER'),
        '11' => acym_translation('ACYM_NOVEMBER'),
        '12' => acym_translation('ACYM_DECEMBER'),
    );
    $year = array();
    for ($i = 1900; $i <= acym_date('now', 'Y'); $i++) {
        $year[$i] = $i;
    }
    $formatToDisplay = explode('%', $format);
    $defaultDate = explode('/', $default);

    $i = 0;
    unset($formatToDisplay[0]);
    foreach ($formatToDisplay as $one) {
        if ($one == 'd') {
            $return .= '<div class="medium-3 cell">'.acym_select($days, $name, $defaultDate[$i], $attributes, 'value', 'text').'</div>';
        }
        if ($one == 'm') {
            $return .= '<div class="medium-5 cell">'.acym_select($month, $name, $defaultDate[$i], $attributes, 'value', 'text').'</div>';
        }
        if ($one == 'y') {
            $return .= '<div class="medium-4 cell">'.acym_select($year, $name, $defaultDate[$i], $attributes, 'value', 'text').'</div>';
        }
        $i++;
    }

    $return .= '</div>';

    return $return;
}

function acym_selectMultiple($data, $name, $selected = array(), $attribs = array(), $optValue = "value", $optText = "text", $translate = false)
{
    if (substr($name, -2) !== '[]') {
        $name .= "[]";
    }

    $dropdown = "<select name=".$name;
    foreach ($attribs as $attribKey => $attribValue) {
        $dropdown .= ' '.$attribKey.'="'.addslashes($attribValue).'"';
    }
    $dropdown .= ' multiple="multiple">';

    foreach ($data as $oneDataKey => $oneDataValue) {
        $disabled = '';

        if (is_object($oneDataValue)) {
            $value = $oneDataValue->$optValue;
            $text = $oneDataValue->$optText;

            if (!empty($oneDataValue->disable)) {
                $disabled = ' disabled="disabled"';
            }
        } else {
            $value = $oneDataKey;
            $text = $oneDataValue;
        }

        if ($translate) {
            $text = acym_translation($text);
        }

        if (strtolower($value) == '<optgroup>') {
            $dropdown .= '<optgroup label="'.acym_escape($text).'">';
        } elseif (strtolower($value) == '</optgroup>') {
            $dropdown .= '</optgroup>';
        } else {
            $text = acym_escape($text);
            $value = acym_escape($value);
            $dropdown .= '<option value="'.$value.'"'.(in_array($value, $selected) ? ' selected="selected"' : '').$disabled.'>'.$text.'</option>';
        }
    }

    $dropdown .= '</select>';

    return $dropdown;
}

function acym_selectOption($value, $text = '', $optKey = 'value', $optText = 'text', $disable = false)
{
    $option = new stdClass();
    $option->$optKey = $value;
    $option->$optText = $text;
    $option->disable = $disable;

    return $option;
}

function acym_gridID($rowNum, $recId, $checkedOut = false, $name = 'cid', $stub = 'cb')
{
    return '<input type="checkbox" id="'.$stub.$rowNum.'" name="'.$name.'[]" value="'.$recId.'" onclick="acym.isChecked(this);">';
}

function acym_level($level)
{
    $config = acym_config();
    if ($config->get($config->get('level'), 0) >= $level) {
        return true;
    }

    return false;
}

function acym_navigationTabs()
{
    if (acym_isNoTemplate() || !acym_isAdmin() || !ACYM_J40) {
        return;
    }

    $pages = array(
        'configuration' => array(
            'ACYM_CONFIGURATION' => array('ctrl' => 'cpanel', 'task' => ''),
            'EXTRA_FIELDS' => array('ctrl' => 'fields', 'task' => ''),
            'BOUNCE_HANDLING' => array('ctrl' => 'bounces', 'task' => ''),
        ),
    );

    $ctrl = acym_getVar('cmd', 'ctrl');
    $task = acym_getVar('cmd', 'task');

    $page = str_replace(ACYM_COMPONENT.'_', '', acym_getVar('cmd', 'page', ''));

    if (empty($page)) {
        foreach ($pages as $mainCtrl => $siblings) {
            foreach ($siblings as $oneSibling) {
                if ($oneSibling['ctrl'] == $ctrl) {
                    $page = $mainCtrl;
                    break;
                }
            }

            if (!empty($page)) {
                break;
            }
        }
    }
    if (empty($pages[$page])) {
        return;
    }

    $navigationTabs = array();
    foreach ($pages[$page] as $text => $oneCtrl) {
        $active = false;

        if ($oneCtrl['ctrl'] == $ctrl && (empty($oneCtrl['task']) || $oneCtrl['task'] == $task || (empty($task) && $oneCtrl['task'] == 'listing'))) {
            $active = true;
        }

        $navigationTabs[] = '<li'.($active ? ' class="active"' : '').'><a href="'.acym_completeLink(
                $oneCtrl['ctrl']
            ).(empty($oneCtrl['task']) ? '' : '&task='.$oneCtrl['task']).'">'.acym_translation($text).'</a></li>';
    }

    echo '<div class="acytabsystem"><ul class="acynavigationtabs nav nav-tabs">'.implode(
            '',
            $navigationTabs
        ).'</ul></div>';
}

function acym_getDate($time = 0, $format = '%d %B %Y %H:%M')
{
    if (empty($time)) {
        return '';
    }

    if (is_numeric($format)) {
        $format = acym_translation('ACYM_DATE_FORMAT_LC'.$format);
    }

    $format = str_replace(
        array('%A', '%d', '%B', '%m', '%Y', '%y', '%H', '%M', '%S', '%a', '%I', '%p', '%w'),
        array('l', 'd', 'F', 'm', 'Y', 'y', 'H', 'i', 's', 'D', 'h', 'a', 'w'),
        $format
    );

    try {
        return acym_date($time, $format, false);
    } catch (Exception $e) {
        return date($format, $time);
    }
}

function acym_isRobot()
{
    if (empty($_SERVER)) {
        return false;
    }
    if (!empty($_SERVER['HTTP_USER_AGENT']) && strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'spambayes') !== false) {
        return true;
    }
    if (!empty($_SERVER['REMOTE_ADDR']) && version_compare($_SERVER['REMOTE_ADDR'], '64.235.144.0', '>=') && version_compare($_SERVER['REMOTE_ADDR'], '64.235.159.255', '<=')) {
        return true;
    }

    return false;
}

function acym_isAllowed($allowedGroups, $groups = null)
{
    if ($allowedGroups == 'all') {
        return true;
    }
    if ($allowedGroups == 'none') {
        return false;
    }
    if (!is_array($allowedGroups)) {
        $allowedGroups = explode(',', trim($allowedGroups, ','));
    }

    $currentUserid = acym_currentUserId();
    if (empty($currentUserid) && empty($groups) && in_array('nonloggedin', $allowedGroups)) {
        return true;
    }

    if (empty($groups) && empty($currentUserid)) {
        return false;
    }
    if (empty($groups)) {
        $groups = acym_getGroupsByUser($currentUserid, false);
    }

    if (!is_array($groups)) {
        $groups = array($groups);
    }
    $inter = array_intersect($groups, $allowedGroups);
    if (empty($inter)) {
        return false;
    }

    return true;
}

function acym_getFunctionsEmailCheck($controllButtons = array(), $bounce = false)
{
    $addressCheck = '!emailAddress.match(/^'.acym_getEmailRegex(true).'((,|;)'.acym_getEmailRegex(
            true
        ).')*$/i)';

    $return = '<script language="javascript" type="text/javascript">
				function validateEmail(emailAddress, fieldName){
					if(emailAddress.length > 0 && emailAddress.indexOf("{") == -1 && '.$addressCheck.'){
						alert("Wrong email address supplied for the " + fieldName + " field: " + emailAddress);
						return false;
					}
					return true;
				}';

    if (!empty($controllButtons)) {
        foreach ($controllButtons as &$oneField) {
            $oneField = 'pressbutton == \''.$oneField.'\'';
        }

        $return .= '
		document.addEventListener("DOMContentLoaded", function(){
			acym.submitbutton = function(pressbutton){
				if('.implode(' || ', $controllButtons).'){
					var emailVars = ["fromemail","replyemail"'.($bounce ? ',"bounceemail"' : '').'];
					var val = "";
					for(var key in emailVars){
						if(isNaN(key)) continue;
						val = document.getElementById(emailVars[key]).value;
						if(!validateEmail(val, emailVars[key])){
							return;
						}
					}
				}
				acym.submitform(pressbutton,document.adminForm);
			};
		});';
    }

    $return .= '
				</script>';

    return $return;
}

function acym_loadLanguage()
{
    acym_loadLanguageFile(ACYM_LANGUAGE_FILE, ACYM_ROOT, null, true);
    acym_loadLanguageFile(ACYM_LANGUAGE_FILE.'_custom', ACYM_ROOT, null, true);
}

function acym_createDir($dir, $report = true, $secured = false)
{
    if (is_dir($dir)) {
        return true;
    }

    $indexhtml = '<html><body bgcolor="#FFFFFF"></body></html>';

    try {
        $status = acym_createFolder($dir);
    } catch (Exception $e) {
        $status = false;
    }

    if (!$status) {
        if ($report) {
            acym_display('Could not create the directory '.$dir, 'error');
        }

        return false;
    }

    try {
        $status = acym_writeFile($dir.DS.'index.html', $indexhtml);
    } catch (Exception $e) {
        $status = false;
    }

    if (!$status) {
        if ($report) {
            acym_display('Could not create the file '.$dir.DS.'index.html', 'error');
        }
    }

    if ($secured) {
        try {
            $htaccess = 'Order deny,allow'."\r\n".'Deny from all';
            $status = acym_writeFile($dir.DS.'.htaccess', $htaccess);
        } catch (Exception $e) {
            $status = false;
        }

        if (!$status) {
            if ($report) {
                acym_display('Could not create the file '.$dir.DS.'.htaccess', 'error');
            }
        }
    }

    return $status;
}

function acym_getUpgradeLink($tolevel)
{
    $config = acym_config();

    return ' <a class="acyupgradelink" href="'.ACYM_REDIRECT.'upgrade-acym-'.$config->get(
            'level'
        ).'-to-'.$tolevel.'" target="_blank">'.acym_translation('ONLY_FROM_'.strtoupper($tolevel)).'</a>';
}

function acym_replaceDate($mydate)
{

    if (strpos($mydate, '{time}') === false) {
        return $mydate;
    }

    $mydate = str_replace('{time}', time(), $mydate);
    $operators = array('+', '-');
    foreach ($operators as $oneOperator) {
        if (strpos($mydate, $oneOperator) === false) {
            continue;
        }
        $dateArray = explode($oneOperator, $mydate);
        if ($oneOperator == '+') {
            $mydate = trim(intval($dateArray[0])) + trim(intval($dateArray[1]));
        } elseif ($oneOperator == '-') {
            $mydate = trim(intval($dateArray[0])) - trim(intval($dateArray[1]));
        }
    }

    return $mydate;
}

function acym_generateKey($length)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randstring = '';
    $max = strlen($characters) - 1;
    for ($i = 0; $i < $length; $i++) {
        $randstring .= $characters[mt_rand(0, $max)];
    }

    return $randstring;
}

function acym_absoluteURL($text)
{
    static $mainurl = '';
    if (empty($mainurl)) {
        $urls = parse_url(ACYM_LIVE);
        if (!empty($urls['path'])) {
            $mainurl = substr(ACYM_LIVE, 0, strrpos(ACYM_LIVE, $urls['path'])).'/';
        } else {
            $mainurl = ACYM_LIVE;
        }
    }

    $text = str_replace(
        array(
            'href="../undefined/',
            'href="../../undefined/',
            'href="../../../undefined//',
            'href="undefined/',
            ACYM_LIVE.'http://',
            ACYM_LIVE.'https://',
        ),
        array('href="'.$mainurl, 'href="'.$mainurl, 'href="'.$mainurl, 'href="'.ACYM_LIVE, 'http://', 'https://'),
        $text
    );
    $text = preg_replace('#href="(/?administrator)?/({|%7B)#Ui', 'href="$2', $text);

    $text = preg_replace('#href="http:/([^/])#Ui', 'href="http://$1', $text);

    $text = preg_replace(
        '#href="'.preg_quote(str_replace(array('http://', 'https://'), '', $mainurl), '#').'#Ui',
        'href="'.$mainurl,
        $text
    );

    $replace = array();
    $replaceBy = array();
    if ($mainurl !== ACYM_LIVE) {

        $replace[] = '#(href|src|action|background)[ ]*=[ ]*\"(?!(\{|%7B|\[|\#|\\\\|[a-z]{3,15}:|/))(?:\.\./)#i';
        $replaceBy[] = '$1="'.substr(ACYM_LIVE, 0, strrpos(rtrim(ACYM_LIVE, '/'), '/') + 1);


        $subfolder = substr(ACYM_LIVE, strrpos(rtrim(ACYM_LIVE, '/'), '/'));
        $replace[] = '#(href|src|action|background)[ ]*=[ ]*\"'.preg_quote($subfolder, '#').'(\{|%7B)#i';
        $replaceBy[] = '$1="$2';
    }
    $replace[] = '#(href|src|action|background)[ ]*=[ ]*\"(?!(\{|%7B|\[|\#|\\\\|[a-z]{3,15}:|/))(?:\.\./|\./)?#i';
    $replaceBy[] = '$1="'.ACYM_LIVE;
    $replace[] = '#(href|src|action|background)[ ]*=[ ]*\"(?!(\{|%7B|\[|\#|\\\\|[a-z]{3,15}:))/#i';
    $replaceBy[] = '$1="'.$mainurl;

    $replace[] = '#((background-image|background)[ ]*:[ ]*url\(\'?"?(?!(\\\\|[a-z]{3,15}:|/|\'|"))(?:\.\./|\./)?)#i';
    $replaceBy[] = '$1'.ACYM_LIVE;

    return preg_replace($replace, $replaceBy, $text);
}

function acym_mainURL(&$link)
{
    static $mainurl = '';
    static $otherarguments = false;
    if (empty($mainurl)) {
        $urls = parse_url(ACYM_LIVE);
        if (isset($urls['path']) && strlen($urls['path']) > 0) {
            $mainurl = substr(ACYM_LIVE, 0, strrpos(ACYM_LIVE, $urls['path'])).'/';
            $otherarguments = trim(str_replace($mainurl, '', ACYM_LIVE), '/');
            if (strlen($otherarguments) > 0) {
                $otherarguments .= '/';
            }
        } else {
            $mainurl = ACYM_LIVE;
        }
    }

    if ($otherarguments && strpos($link, $otherarguments) === false) {
        $link = $otherarguments.$link;
    }

    return $mainurl;
}

function acym_bytes($val)
{
    $val = trim($val);
    if (empty($val)) {
        return 0;
    }
    $last = strtolower($val[strlen($val) - 1]);
    switch ($last) {
        case 'g':
            $val = intval($val) * 1073741824;
        case 'm':
            $val = intval($val) * 1048576;
        case 'k':
            $val = intval($val) * 1024;
    }

    return (int)$val;
}

function acym_getTables()
{
    return acym_loadResultArray('SHOW TABLES');
}

function acym_getColumns($table, $acyTable = true, $putPrefix = true)
{
    if ($putPrefix) {
        $prefix = $acyTable ? '#__acym_' : '#__';
        $table = $prefix.$table;
    }

    return acym_loadResultArray('SHOW COLUMNS FROM '.$table);
}

function acym_display($messages, $type = 'success', $close = true)
{
    if (empty($messages)) {
        return;
    }

    if (!is_array($messages)) {
        $messages = array($messages);
    }

    echo '<div id="acym_messages_'.$type.'" class="acym_message acym_'.$type.'">';

    if ($close) {
        echo '<i class="fa fa-close"></i>';
    }

    echo '<p>'.implode('</p><p>', $messages).'</p></div>';
}

function acym_secureDBColumn($fieldName)
{
    if (!is_string($fieldName) || preg_match('|[^a-z0-9#_.-]|i', $fieldName) !== 0) {
        die('field "'.htmlspecialchars($fieldName, ENT_COMPAT, 'UTF-8').'" not secured');
    }

    return $fieldName;
}

function acym_displayErrors()
{
    error_reporting(E_ALL);
    @ini_set("display_errors", 1);
}

function acym_increasePerf()
{
    @ini_set('max_execution_time', 600);
    @ini_set('pcre.backtrack_limit', 1000000);
}

function acym_config($reload = false)
{
    static $configClass = null;
    if ($configClass === null || $reload) {
        $configClass = acym_get('class.configuration');
        $configClass->load();
    }

    return $configClass;
}

function acym_getModuleFormName()
{
    static $i = 1;

    return 'formAcym'.rand(1000, 9999).$i++;
}

function acym_initModule($params = null)
{
    static $loaded = false;
    if ($loaded) {
        return;
    }
    $loaded = true;

    if (method_exists($params, 'get')) {
        $nameCaption = $params->get('nametext');
        $emailCaption = $params->get('emailtext');
    }

    if (empty($nameCaption)) {
        $nameCaption = acym_translation('ACYM_NAME');
    }
    if (empty($emailCaption)) {
        $emailCaption = acym_translation('ACYM_EMAIL');
    }

    $js = "	var acymModule = [];
			acymModule['emailRegex'] = /^".acym_getEmailRegex(true)."$/i;
			acymModule['NAMECAPTION'] = '".str_replace("'", "\'", $nameCaption)."';
			acymModule['NAME_MISSING'] = '".str_replace("'", "\'", acym_translation('ACYM_MISSING_NAME'))."';
			acymModule['EMAILCAPTION'] = '".str_replace("'", "\'", $emailCaption)."';
			acymModule['VALID_EMAIL'] = '".str_replace("'", "\'", acym_translation('ACYM_VALID_EMAIL'))."';
			acymModule['CAPTCHA_MISSING'] = '".str_replace("'", "\'", acym_translation('ACYM_WRONG_CAPTCHA'))."';
			acymModule['NO_LIST_SELECTED'] = '".str_replace("'", "\'", acym_translation('ACYM_SELECT_LIST'))."';
			acymModule['ACCEPT_TERMS'] = '".str_replace("'", "\'", acym_translation('ACYM_ACCEPT_TERMS'))."';
		";

    echo "<script type=\"text/javascript\">
                <!--
                $js
                //-->
            </script>";

    $config = acym_config();
    echo "\n".'<script type="text/javascript" src="'.ACYM_JS.'module.min.js?v='.str_replace('.', '', $config->get('version')).'" ></script>'."\n";
    echo "\n".'<link rel="stylesheet" type="text/css" href="'.ACYM_CSS.'module.min.css?v='.str_replace('.', '', $config->get('version')).'" >'."\n";
}

function acym_footer()
{
    $config = acym_config();
    $description = ACYM_CMS.' E-mail Marketing';
    $text = '<!-- '.ACYM_NAME.' Component powered by '.ACYM_ACYWEBSITE.' -->
		<!-- version '.$config->get('level').' : '.$config->get('version').' -->';
    if (acym_level(1) && !acym_level(4)) {
        return $text;
    }
    $level = $config->get('level');
    $text .= '<div class="acym_footer" align="center" style="text-align:center"><a href="'.ACYM_ACYWEBSITE.'?utm_source=acym-'.$level.'&utm_medium=front-end&utm_content=txt&utm_campaign=powered-by" target="_blank" title="'.ACYM_NAME.' : '.str_replace(
            'TM ',
            ' ',
            strip_tags($description)
        ).'">'.ACYM_NAME;
    $text .= ' - '.$description.'</a></div>';

    return $text;
}

function acym_perf($name)
{
    static $previoustime = 0;
    static $previousmemory = 0;
    static $file = '';

    if (empty($file)) {
        $file = ACYM_ROOT.'acydebug_'.rand().'.txt';
        $previoustime = microtime(true);
        $previousmemory = memory_get_usage();
        file_put_contents(
            $file,
            "\r\n\r\n-- new test : ".$name." -- ".date('d M H:i:s')." from ".@$_SERVER['REMOTE_ADDR'],
            FILE_APPEND
        );

        return;
    }

    $nowtime = microtime(true);
    $totaltime = $nowtime - $previoustime;
    $previoustime = $nowtime;

    $nowmemory = memory_get_usage();
    $totalmemory = $nowmemory - $previousmemory;
    $previousmemory = $nowmemory;

    file_put_contents(
        $file,
        "\r\n".$name.' : '.number_format($totaltime, 2).'s - '.$totalmemory.' / '.memory_get_usage(),
        FILE_APPEND
    );
}

function acym_get($path)
{
    list($group, $class) = explode('.', $path);

    $className = $class.ucfirst(str_replace('_front', '', $group));
    if ($group == 'helper' && strpos($className, 'acym') !== 0) {
        $className = 'acym'.$className;
    }
    if ($group == 'class') {
        $className = 'acym'.$className;
    }

    if (substr($group, 0, 4) == 'view') {
        $className = $className.ucfirst($class);
        $class .= DS.'view.html';
    }

    if (!class_exists($className)) {
        include(constant(strtoupper('ACYM_'.$group)).$class.'.php');
    }

    if (!class_exists($className)) {
        return null;
    }

    return new $className();
}

function acym_getCID($field = '')
{
    $oneResult = acym_getVar('array', 'cid', array(), '');
    $oneResult = intval(reset($oneResult));
    if (!empty($oneResult) || empty($field)) {
        return $oneResult;
    }

    $oneResult = acym_getVar('int', $field, 0, '');

    return intval($oneResult);
}

function acym_checkRobots()
{
    if (preg_match('#(libwww-perl|python|googlebot)#i', @$_SERVER['HTTP_USER_AGENT'])) {
        die('Not allowed for robots. Please contact us if you are not a robot');
    }
}

function acym_importFile($file, $uploadPath, $onlyPict, $maxwidth = '')
{
    acym_checkToken();

    $config = acym_config();
    $additionalMsg = '';

    if ($file["error"] > 0) {
        $file["error"] = intval($file["error"]);
        if ($file["error"] > 8) {
            $file["error"] = 0;
        }

        $phpFileUploadErrors = array(
            0 => 'Unknown error',
            1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
            2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
            3 => 'The uploaded file was only partially uploaded',
            4 => 'No file was uploaded',
            6 => 'Missing a temporary folder',
            7 => 'Failed to write file to disk',
            8 => 'A PHP extension stopped the file upload',
        );

        acym_enqueueNotification(acym_translation_sprintf('ACYM_ERROR_UPLOADING_FILE_X', $phpFileUploadErrors[$file["error"]]), 'error', 5000);

        return false;
    }

    acym_createDir($uploadPath, true);

    if (!is_writable($uploadPath)) {
        @chmod($uploadPath, '0755');
        if (!is_writable($uploadPath)) {
            acym_display(acym_translation_sprintf('ACYM_WRITABLE_FOLDER', $uploadPath), 'error');

            return false;
        }
    }

    if ($onlyPict) {
        $allowedExtensions = array('png', 'jpeg', 'jpg', 'gif', 'ico', 'bmp');
    } else {
        $allowedExtensions = explode(',', $config->get('allowed_files'));
    }

    if (!preg_match('#\.('.implode('|', $allowedExtensions).')$#Ui', $file["name"], $extension)) {
        $ext = substr($file["name"], strrpos($file["name"], '.') + 1);
        acym_display(
            acym_translation_sprintf(
                'ACYM_ACCEPTED_TYPE',
                htmlspecialchars($ext, ENT_COMPAT, 'UTF-8'),
                implode(', ', $allowedExtensions)
            ),
            'error'
        );

        return false;
    }

    if (preg_match('#\.(php.?|.?htm.?|pl|py|jsp|asp|sh|cgi)#Ui', $file["name"])) {
        acym_display(
            'This extension name is blocked by the system regardless your configuration for security reasons',
            'error'
        );

        return false;
    }

    $file["name"] = preg_replace(
                        '#[^a-z0-9]#i',
                        '_',
                        strtolower(substr($file["name"], 0, strrpos($file["name"], '.')))
                    ).'.'.$extension[1];

    if ($onlyPict) {
        $imageSize = getimagesize($file['tmp_name']);
        if (empty($imageSize)) {
            acym_display('Invalid image', 'error');

            return false;
        }
    }

    if (file_exists($uploadPath.DS.$file["name"])) {
        $i = 1;
        $nameFile = preg_replace("/\\.[^.\\s]{3,4}$/", "", $file["name"]);
        $ext = substr($file["name"], strrpos($file["name"], '.') + 1);
        while (file_exists($uploadPath.DS.$nameFile.'_'.$i.'.'.$ext)) {
            $i++;
        }

        $file["name"] = $nameFile.'_'.$i.'.'.$ext;
        $additionalMsg = '<br />'.acym_translation_sprintf('ACYM_FILE_RENAMED', $file["name"]);
        if ($onlyPict) {
            $additionalMsg .= '<br /><a style="color: blue; cursor: pointer;" onclick="confirmBox(\'rename\', \''.$file['name'].'\', \''.$nameFile.'.'.$ext.'\')">'.acym_translation(
                    'ACYM_RENAME_OR_REPLACE'
                ).'</a>';
        }
    }

    if (!acym_uploadFile($file["tmp_name"], rtrim($uploadPath, DS).DS.$file["name"])) {
        if (!move_uploaded_file($file["tmp_name"], rtrim($uploadPath, DS).DS.$file["name"])) {
            acym_display(
                acym_translation_sprintf(
                    'ACYM_FAIL_UPLOAD',
                    '<b><i>'.htmlspecialchars($file["tmp_name"], ENT_COMPAT, 'UTF-8').'</i></b>',
                    '<b><i>'.htmlspecialchars(rtrim($uploadPath, DS).DS.$file["name"], ENT_COMPAT, 'UTF-8').'</i></b>'
                ),
                'error'
            );

            return false;
        }
    }

    if (!empty($maxwidth) || ($onlyPict && $imageSize[0] > 1000)) {
        $imageHelper = acym_get('helper.image');
        if ($imageHelper->available()) {
            $imageHelper->maxHeight = 9999;
            if (empty($maxwidth)) {
                $imageHelper->maxWidth = 700;
            } else {
                $imageHelper->maxWidth = $maxwidth;
            }
            $message = 'ACYM_IMAGE_RESIZED';
            $imageHelper->destination = $uploadPath;
            $thumb = $imageHelper->generateThumbnail(rtrim($uploadPath, DS).DS.$file["name"], $file["name"]);
            $resize = acym_moveFile($thumb['file'], $uploadPath.DS.$file["name"]);
            if ($thumb) {
                $additionalMsg .= '<br />'.acym_translation($message);
            }
        }
    }
    acym_enqueueNotification(acym_translation('ACYM_SUCCESS_FILE_UPLOAD').$additionalMsg, 'success', 5000);

    return $file["name"];
}

function acym_inputFile($name, $value = '', $id = '', $class = '', $attributes = '')
{
    $return = '<div class="cell acym__input__file '.$class.' grid-x"><input '.$attributes.' style="display: none" id="'.$id.'" type="file" name="'.$name.'"><button type="button" class="smaller-button acym__button__file button button-secondary cell shrink">'.acym_translation('ACYM_CHOOSE_FILE').'</button><span class="cell shrink margin-left-2">';
    $return .= empty($value) ? acym_translation('ACYM_NO_FILE_CHOSEN') : $value;
    $return .= '</span></div>';

    return $return;
}

function acym_getFilesFolder($folder = 'upload', $multipleFolders = false)
{
    $listClass = acym_get('class.list');
    if (acym_isAdmin()) {
        $allLists = $listClass->getAll();
    } else {
        $allLists = $listClass->getAll();
    }
    $newFolders = array();

    $config = acym_config();
    if ($folder == 'upload') {
        $uploadFolder = $config->get('uploadfolder', ACYM_UPLOAD_FOLDER);
    } else {
        $uploadFolder = $config->get('mediafolder', ACYM_UPLOAD_FOLDER);
    }

    $folders = explode(',', $uploadFolder);

    foreach ($folders as $k => $folder) {
        $folders[$k] = trim($folder, '/');
        if (strpos($folder, '{userid}') !== false) {
            $folders[$k] = str_replace('{userid}', acym_currentUserId(), $folders[$k]);
        }

        if (strpos($folder, '{listalias}') !== false) {
            if (empty($allLists)) {
                $noList = new stdClass();
                $noList->alias = 'none';
                $allLists = array($noList);
            }

            foreach ($allLists as $oneList) {
                $newFolders[] = str_replace(
                    '{listalias}',
                    strtolower(str_replace(array(' ', '-'), '_', $oneList->alias)),
                    $folders[$k]
                );
            }

            $folders[$k] = '';
            continue;
        }

        if (strpos($folder, '{groupid}') !== false || strpos($folder, '{groupname}') !== false) {
            $groups = acym_getGroupsByUser(acym_currentUserId(), false);
            acym_arrayToInteger($groups);
            if (empty($groups)) {
                $groups[] = 0;
            }

            $completeGroups = acym_loadObjectList(
                'SELECT id, title FROM #__usergroups WHERE id IN ('.implode(',', $groups).')'
            );

            foreach ($completeGroups as $group) {
                $newFolders[] = str_replace(
                    array('{groupid}', '{groupname}'),
                    array($group->id, strtolower(str_replace(' ', '_', $group->title))),
                    $folders[$k]
                );
            }

            $folders[$k] = '';
        }
    }

    $folders = array_merge($folders, $newFolders);
    $folders = array_filter($folders);
    sort($folders);
    if ($multipleFolders) {
        return $folders;
    } else {
        return array_shift($folders);
    }
}

function acym_generateArborescence($folders)
{
    $folderList = array();
    foreach ($folders as $folder) {
        $folderPath = acym_cleanPath(ACYM_ROOT.trim(str_replace('/', DS, trim($folder)), DS));
        if (!file_exists($folderPath)) {
            acym_createDir($folderPath);
        }
        $subFolders = acym_listFolderTree($folderPath, '', 15);
        $folderList[$folder] = array();
        foreach ($subFolders as $oneFolder) {
            $subFolder = str_replace(ACYM_ROOT, '', $oneFolder['relname']);
            $subFolder = str_replace(DS, '/', $subFolder);
            $folderList[$folder][$subFolder] = ltrim($subFolder, '/');
        }
        $folderList[$folder] = array_unique($folderList[$folder]);
    }

    return $folderList;
}

function acym_arrayToInteger(&$array)
{
    if (is_array($array)) {
        $array = array_map('intval', $array);
    } else {
        $array = array();
    }
}

function acym_arrayToString($array, $inner_glue = '=', $outer_glue = ' ', $keepOuterKey = false)
{
    $output = array();

    foreach ($array as $key => $item) {
        if (is_array($item)) {
            if ($keepOuterKey) {
                $output[] = $key;
            }

            $output[] = acym_arrayToString($item, $inner_glue, $outer_glue, $keepOuterKey);
        } else {
            $output[] = $key.$inner_glue.'"'.$item.'"';
        }
    }

    return implode($outer_glue, $output);
}

function acym_makeSafeFile($file)
{
    $file = rtrim($file, '.');
    $regex = array('#(\.){2,}#', '#[^A-Za-z0-9\.\_\- ]#', '#^\.#');

    return trim(preg_replace($regex, '', $file));
}

function acym_sortablelist($table, $ordering)
{
    acym_addScript(false, ACYM_JS.'sortable.js?v='.@filemtime(ACYM_MEDIA.'js'.DS.'sortable.js'));

    $js = "
		document.addEventListener(\"DOMContentLoaded\", function(event) {
			Sortable.create(document.getElementById('acym_sortable_listing'), {
				handle: '.acyicon-draghandle',
				animation: 150,
				dataIdAttr: 'acyorderid',
				ghostClass: 'acysortable-ghost',
				store: {
					set: function (sortable) {
						var cid = sortable.toArray();
						var order = [".$ordering."];
						
						var xhr = new XMLHttpRequest();
						xhr.open('GET', '".acym_prepareAjaxURL(
            $table
        )."&task=saveorder&'+cid.join('&')+'&'+order.join('&')+'&".acym_getFormToken()."');
						xhr.send();
					}
				}
			});
		});";

    acym_addScript(true, $js);
}

function acym_tooltip($text, $tooltipText, $classContainer = '', $title = '', $link = '')
{
    if (!empty($link)) {
        $text = '<a href="'.$link.'" title="'.acym_escape($title).'"">'.$text.'</a>';
    }

    return '<span class="acym__tooltip '.$classContainer.'"><span class="acym__tooltip__text">'.$tooltipText.'</span>'.$text.'</span>';
}

function acym_deleteFolder($path)
{
    $path = acym_cleanPath($path);
    if (!is_dir($path)) {
        acym_enqueueNotification(acym_translation_sprintf('ACYM_IS_NOT_A_FOLDER', $path), 'error', 0);

        return false;
    }
    $files = acym_getFiles($path);
    if (!empty($files)) {
        foreach ($files as $oneFile) {
            if (!acym_deleteFile($path.DS.$oneFile)) {
                return false;
            }
        }
    }

    $folders = acym_getFolders($path, '.', false, false, array());
    if (!empty($folders)) {
        foreach ($folders as $oneFolder) {
            if (!acym_deleteFolder($path.DS.$oneFolder)) {
                return false;
            }
        }
    }

    if (@rmdir($path)) {
        $ret = true;
    } else {
        acym_enqueueNotification(acym_translation_sprintf('ACYM_COULD_NOT_DELETE_FOLDER', $path), 'error', 0);
        $ret = false;
    }

    return $ret;
}

function acym_createFolder($path = '', $mode = 0755)
{
    $path = acym_cleanPath($path);
    if (file_exists($path)) {
        return true;
    }

    $origmask = @umask(0);
    $ret = @mkdir($path, $mode, true);
    @umask($origmask);

    return $ret;
}

function acym_getFolders(
    $path,
    $filter = '.',
    $recurse = false,
    $full = false,
    $exclude = array('.svn', 'CVS', '.DS_Store', '__MACOSX'),
    $excludefilter = array('^\..*')
) {
    $path = acym_cleanPath($path);

    if (!is_dir($path)) {
        acym_enqueueNotification(acym_translation_sprintf('ACYM_IS_NOT_A_FOLDER', $path), 'error', 0);

        return false;
    }

    if (count($excludefilter)) {
        $excludefilter_string = '/('.implode('|', $excludefilter).')/';
    } else {
        $excludefilter_string = '';
    }

    $arr = acym_getItems($path, $filter, $recurse, $full, $exclude, $excludefilter_string, false);
    asort($arr);

    return array_values($arr);
}

function acym_getFiles(
    $path,
    $filter = '.',
    $recurse = false,
    $full = false,
    $exclude = array('.svn', 'CVS', '.DS_Store', '__MACOSX'),
    $excludefilter = array('^\..*', '.*~'),
    $naturalSort = false
) {
    $path = acym_cleanPath($path);

    if (!is_dir($path)) {
        acym_enqueueNotification(acym_translation_sprintf('ACYM_IS_NOT_A_FOLDER', $path), 'error', 0);

        return false;
    }

    if (count($excludefilter)) {
        $excludefilter_string = '/('.implode('|', $excludefilter).')/';
    } else {
        $excludefilter_string = '';
    }

    $arr = acym_getItems($path, $filter, $recurse, $full, $exclude, $excludefilter_string, true);

    if ($naturalSort) {
        natsort($arr);
    } else {
        asort($arr);
    }

    return array_values($arr);
}

function acym_getItems($path, $filter, $recurse, $full, $exclude, $excludefilter_string, $findfiles)
{
    $arr = array();

    if (!($handle = @opendir($path))) {
        return $arr;
    }

    while (($file = readdir($handle)) !== false) {
        if ($file == '.' || $file == '..' || in_array($file, $exclude) || (!empty($excludefilter_string) && preg_match(
                    $excludefilter_string,
                    $file
                ))) {
            continue;
        }
        $fullpath = $path.'/'.$file;

        $isDir = is_dir($fullpath);

        if (($isDir xor $findfiles) && preg_match("/$filter/", $file)) {
            if ($full) {
                $arr[] = $fullpath;
            } else {
                $arr[] = $file;
            }
        }

        if ($isDir && $recurse) {
            if (is_int($recurse)) {
                $arr = array_merge(
                    $arr,
                    acym_getItems(
                        $fullpath,
                        $filter,
                        $recurse - 1,
                        $full,
                        $exclude,
                        $excludefilter_string,
                        $findfiles
                    )
                );
            } else {
                $arr = array_merge(
                    $arr,
                    acym_getItems(
                        $fullpath,
                        $filter,
                        $recurse,
                        $full,
                        $exclude,
                        $excludefilter_string,
                        $findfiles
                    )
                );
            }
        }
    }

    closedir($handle);

    return $arr;
}

function acym_copyFolder($src, $dest, $path = '', $force = false, $use_streams = false)
{

    if ($path) {
        $src = acym_cleanPath($path.'/'.$src);
        $dest = acym_cleanPath($path.'/'.$dest);
    }

    $src = rtrim($src, DIRECTORY_SEPARATOR);
    $dest = rtrim($dest, DIRECTORY_SEPARATOR);

    if (!file_exists($src)) {
        acym_enqueueNotification(acym_translation_sprintf('ACYM_FOLDER_DOES_NOT_EXIST', $src), 'error', 0);

        return false;
    }

    if (file_exists($dest) && !$force) {
        acym_enqueueNotification(acym_translation_sprintf('ACYM_FOLDER_ALREADY_EXIST', $dest), 'error', 0);

        return true;
    }

    if (!acym_createFolder($dest)) {
        acym_enqueueNotification(acym_translation('ACYM_CANNOT_CREATE_DESTINATION_FOLDER'), 'error', 0);

        return false;
    }

    if (!($dh = @opendir($src))) {
        acym_enqueueNotification(acym_translation('ACYM_CANNOT_OPEN_SOURCE_FOLDER'), 'error', 0);

        return false;
    }

    while (($file = readdir($dh)) !== false) {
        $sfid = $src.'/'.$file;
        $dfid = $dest.'/'.$file;

        switch (filetype($sfid)) {
            case 'dir':
                if ($file != '.' && $file != '..') {
                    $ret = acym_copyFolder($sfid, $dfid, null, $force, $use_streams);

                    if ($ret !== true) {
                        return $ret;
                    }
                }
                break;

            case 'file':
                if (!@copy($sfid, $dfid)) {
                    acym_enqueueNotification(acym_translation_sprintf('ACYM_COPY_FILE_FAILED_PERMISSION', $sfid), 'error', 0);

                    return false;
                }
                break;
        }
    }

    return true;
}

function acym_moveFolder($src, $dest, $path = '', $use_streams = false)
{
    if ($path) {
        $src = acym_cleanPath($path.'/'.$src);
        $dest = acym_cleanPath($path.'/'.$dest);
    }

    if (!file_exists($src)) {
        acym_enqueueNotification(acym_translation_sprintf('ACYM_FOLDER_DOES_NOT_EXIST', $src), 'error', 0);

        return false;
    }

    if (!@rename($src, $dest)) {
        acym_enqueueNotification(acym_translation_sprintf('ACYM_COULD_NOT_MOVE_FOLDER_PERMISSION', $src, $dest), 'error', 0);

        return false;
    }

    return true;
}

function acym_listFolderTree($path, $filter, $maxLevel = 3, $level = 0, $parent = 0)
{
    $dirs = array();

    if ($level == 0) {
        $GLOBALS['acym_folder_tree_index'] = 0;
    }

    if ($level < $maxLevel) {
        $folders = acym_getFolders($path, $filter);

        foreach ($folders as $name) {
            $id = ++$GLOBALS['acym_folder_tree_index'];
            $fullName = acym_cleanPath($path.'/'.$name);
            $dirs[] = array(
                'id' => $id,
                'parent' => $parent,
                'name' => $name,
                'fullname' => $fullName,
                'relname' => str_replace(ACYM_ROOT, '', $fullName),
            );
            $dirs2 = acym_listFolderTree($fullName, $filter, $maxLevel, $level + 1, $id);
            $dirs = array_merge($dirs, $dirs2);
        }
    }

    return $dirs;
}

function acym_deleteFile($file)
{
    $file = acym_cleanPath($file);
    if (!is_file($file)) {
        acym_enqueueNotification(acym_translation_sprintf('ACYM_IS_NOT_A_FILE', $file), 'error', 0);

        return false;
    }

    @chmod($file, 0777);

    if (!@unlink($file)) {
        $filename = basename($file);
        acym_enqueueNotification(acym_translation_sprintf('ACYM_FAILED_DELETE', $filename), 'error', 0);

        return false;
    }

    return true;
}

function acym_writeFile($file, $buffer, $use_streams = false)
{
    if (!file_exists(dirname($file)) && acym_createFolder(dirname($file)) == false) {
        return false;
    }

    $file = acym_cleanPath($file);
    $ret = is_int(file_put_contents($file, $buffer));

    return $ret;
}

function acym_moveFile($src, $dest, $path = '', $use_streams = false)
{
    if ($path) {
        $src = acym_cleanPath($path.'/'.$src);
        $dest = acym_cleanPath($path.'/'.$dest);
    }

    if (!is_readable($src)) {
        acym_enqueueNotification(acym_translation_sprintf('ACYM_COULD_NOT_FIND_FILE_SOURCE_PERMISSION', $src), 'error', 0);

        return false;
    }

    if (!@rename($src, $dest)) {
        acym_enqueueNotification(acym_translation('ACYM_COULD_NOT_MOVE_FILE'), 'error', 0);

        return false;
    }

    return true;
}

function acym_uploadFile($src, $dest)
{
    $dest = acym_cleanPath($dest);

    $baseDir = dirname($dest);
    if (!file_exists($baseDir)) {
        acym_createFolder($baseDir);
    }

    if (is_writeable($baseDir) && move_uploaded_file($src, $dest)) {
        if (@chmod($dest, octdec('0644'))) {
            return true;
        } else {
            acym_enqueueNotification(acym_translation('ACYM_FILE_REJECTED_SAFETY_REASON'), 'error', 0);
        }
    } else {
        acym_enqueueNotification(acym_translation_sprintf('ACYM_COULD_NOT_UPLOAD_FILE_PERMISSION', $baseDir), 'error', 0);
    }

    return false;
}

function acym_copyFile($src, $dest, $path = null, $use_streams = false)
{
    if ($path) {
        $src = acym_cleanPath($path.'/'.$src);
        $dest = acym_cleanPath($path.'/'.$dest);
    }

    if (!is_readable($src)) {
        acym_enqueueNotification(acym_translation_sprintf('ACYM_COULD_NOT_FIND_FILE_SOURCE_PERMISSION', $src), 'error', 0);

        return false;
    }

    if (!@copy($src, $dest)) {
        acym_enqueueNotification(acym_translation_sprintf('ACYM_COULD_NOT_COPY_FILE_X_TO_X', $src, $dest), 'error', 0);

        return false;
    }

    return true;
}

function acym_fileGetExt($file)
{
    $dot = strrpos($file, '.');
    if ($dot === false) {
        return '';
    }

    return substr($file, $dot + 1);
}

function acym_cleanPath($path, $ds = DIRECTORY_SEPARATOR)
{
    $path = trim($path);

    if (empty($path)) {
        $path = ACYM_ROOT;
    } elseif (($ds == '\\') && substr($path, 0, 2) == '\\\\') {
        $path = "\\".preg_replace('#[/\\\\]+#', $ds, $path);
    } else {
        $path = preg_replace('#[/\\\\]+#', $ds, $path);
    }

    return $path;
}

function acym_createArchive($name, $files)
{
    $contents = array();
    $ctrldir = array();

    $timearray = getdate();
    $dostime = (($timearray['year'] - 1980) << 25) | ($timearray['mon'] << 21) | ($timearray['mday'] << 16) | ($timearray['hours'] << 11) | ($timearray['minutes'] << 5) | ($timearray['seconds'] >> 1);
    $dtime = dechex($dostime);
    $hexdtime = chr(hexdec($dtime[6].$dtime[7])).chr(hexdec($dtime[4].$dtime[5])).chr(hexdec($dtime[2].$dtime[3])).chr(
            hexdec($dtime[0].$dtime[1])
        );

    foreach ($files as $file) {
        $data = $file['data'];
        $filename = str_replace('\\', '/', $file['name']);

        $fr = "\x50\x4b\x03\x04\x14\x00\x00\x00\x08\x00".$hexdtime;

        $unc_len = strlen($data);
        $crc = crc32($data);
        $zdata = gzcompress($data);
        $zdata = substr(substr($zdata, 0, strlen($zdata) - 4), 2);
        $c_len = strlen($zdata);

        $fr .= pack('V', $crc).pack('V', $c_len).pack('V', $unc_len).pack('v', strlen($filename)).pack(
                'v',
                0
            ).$filename.$zdata;

        $old_offset = strlen(implode('', $contents));
        $contents[] = $fr;

        $cdrec = "\x50\x4b\x01\x02\x00\x00\x14\x00\x00\x00\x08\x00".$hexdtime;
        $cdrec .= pack('V', $crc).pack('V', $c_len).pack('V', $unc_len).pack('v', strlen($filename)).pack('v', 0).pack(
                'v',
                0
            ).pack('v', 0).pack('v', 0).pack('V', 32).pack('V', $old_offset).$filename;

        $ctrldir[] = $cdrec;
    }

    $data = implode('', $contents);
    $dir = implode('', $ctrldir);
    $buffer = $data.$dir."\x50\x4b\x05\x06\x00\x00\x00\x00".pack('v', count($ctrldir)).pack('v', count($ctrldir)).pack(
            'V',
            strlen($dir)
        ).pack('V', strlen($data))."\x00\x00";

    return acym_writeFile($name.'.zip', $buffer);
}

function acym_currentURL()
{
    $url = isset($_SERVER['HTTPS']) ? 'https' : 'http';
    $url .= '://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

    return $url;
}

function acym_accessList()
{
    $listid = acym_getVar('int', 'listid');
    if (empty($listid)) {
        return false;
    }

    $listClass = acym_get('class.list');
    $myList = $listClass->get($listid);
    if (empty($myList->listid)) {
        die('Invalid List');
    }

    $currentUserid = acym_currentUserId();
    if (!empty($currentUserid) && $currentUserid == (int)$myList->userid) {
        return true;
    }
    if (empty($currentUserid) || $myList->access_manage == 'none') {
        return false;
    }
    if ($myList->access_manage != 'all' && !acym_isAllowed($myList->access_manage)) {
        return false;
    }

    return true;
}

function acym_gridSort(
    $title,
    $order,
    $direction = 'asc',
    $selected = '',
    $task = null,
    $new_direction = 'asc',
    $tip = ''
) {
    $direction = strtolower($direction);
    if ($order != $selected) {
        $direction = $new_direction;
    } else {
        $direction = $direction == 'desc' ? 'asc' : 'desc';
    }

    $icon = array('acyicon-up', 'acyicon-down');
    $index = (int)($direction == 'desc');

    $result = '<a href="#" onclick="acym.tableOrdering(\''.$order.'\', \''.$direction.'\', \''.$task.'\');return false;">';
    $result .= acym_tooltip(acym_translation('ACYM_ORDER_COLUMN'), '', '', acym_translation($title));
    if ($order == $selected) {
        $result .= '<span class="'.$icon[$index].'"></span>';
    }
    $result .= '</a>';

    return $result;
}

function acym_session()
{
    $sessionID = session_id();
    if (empty($sessionID)) {
        @session_start();
    }
}

function acym_filterOrdering($orderingOptions, $selected)
{
    echo '<input type="hidden" id="acym_ordering" name="ordering" value="'.$selected.'"/>';

    foreach ($orderingOptions as $value => $text) {
        $class = 'acym_ordering_option large-shrink medium-auto small-6 cell button primary';
        if ($value == $selected) {
            $class .= ' acym__listing__sort-by--selected';
        }
        echo acym_tooltip('<button type="button" ordering="'.$value.'" class="'.$class.'">'.acym_translation($text).'</button>', acym_translation("ACYM_SORT_BY").' '.acym_translation($text));
    }
}

function acym_dropdown($id = null, $target = null, $content = '', $dataPosition = 'bottom', $dataAlignment = 'center')
{
    $dropdown = $target;
    $dropdown .= '<div class="dropdown-pane" id="'.$id.'" data-dropdown data-hover="true"';
    $dropdown .= 'data-hover-pane="true" data-position="'.$dataPosition.'" data-alignment="'.$dataAlignment.'">';
    $dropdown .= $content;
    $dropdown .= '</div>';

    return $dropdown;
}

function acym_listingActions($actions)
{
    $defaultAction = new stdClass();
    $defaultAction->value = 0;
    $defaultAction->text = acym_translation('ACYM_CHOOSE_ACTION');
    $defaultAction->disable = true;

    array_unshift($actions, $defaultAction);
    echo acym_select($actions, '', null, 'class="medium-shrink cell margin-right-1"', 'value', 'text', 'listing_actions');
}

function acym_switchFilter($switchOptions, $selected, $name, $addClass = '')
{
    $return = '<input type="hidden" id="acym__type-template" name="'.$name.'" value="'.$selected.'">';
    foreach ($switchOptions as $value => $text) {
        $class = 'button hollow acym__type__choosen cell small-6 xlarge-auto large-shrink';
        if ($value == $selected) {
            $class .= ' is-active';
        }
        $class .= ' '.$addClass;
        $return .= '<button class="'.$class.'" type="'.$value.'">'.acym_translation($text).'</button>';
    }

    return $return;
}

function acym_filterStatus($options, $selected, $name)
{
    echo '<input type="hidden" id="acym_filter_status" name="'.$name.'" value="'.$selected.'"/>';

    foreach ($options as $value => $text) {
        $class = 'acym__filter__status clear button secondary';
        if ($value == $selected) {
            $class .= ' font-bold acym__status__select';
        }
        $disabled = empty($text[1]) ? ' disabled' : '';
        echo '<button type="button" status="'.$value.'" class="'.$class.'"'.$disabled.'>'.acym_translation($text[0]).' ('.$text[1].')</button>';
    }
}

function acym_filterSearch($search, $name, $placeholder = 'ACYM_SEARCH', $showClearBtn = true)
{
    $searchField = '<div class="input-group acym__search-area">
        <div class="input-group-button">
            <button class="button acym__search__button hide-for-small-only"><i class="material-icons">search</i></button>
        </div>
        <input class="input-group-field acym__search-field" type="text" name="'.$name.'" placeholder="'.acym_translation($placeholder).'" value="'.$search.'">';
    if ($showClearBtn) {
        $searchField .= '<span class="acym__search-clear"><i class="fa fa-close"></i></span>';
    }
    $searchField .= '</div>';

    return $searchField;
}

function acym_switch($name, $value, $label = null, $attrInput = array(), $labelClass = 'medium-6 small-9', $switchContainerClass = "auto", $switchClass = "tiny", $toggle = null, $toggleOpen = true)
{
    static $occurrence = 100;
    $occurrence++;

    $id = 'switch_'.$occurrence;
    $checked = $value == 1 ? 'checked="checked"' : '';

    $switch = '
    <div class="switch '.$switchClass.'">
        <input type="hidden" name="'.$name.'" data-switch="'.$id.'" value="'.$value.'"';

    if (!empty($toggle)) {
        $switch .= ' data-toggle-switch="'.$toggle.'" data-toggle-switch-open="'.($toggleOpen ? 'show' : 'hide').'"';
    }

    foreach ($attrInput as $oneAttributeName => $oneAttributeValue) {
        $switch .= ' '.$oneAttributeName.'="'.acym_escape($oneAttributeValue).'"';
    }
    $switch .= '>';
    $switch .= '
        <input class="switch-input" type="checkbox" id="'.$id.'" value="1" '.$checked.'>
        <label class="switch-paddle switch-label" for="'.$id.'">
            <span class="switch-active" aria-hidden="true">1</span>
            <span class="switch-inactive" aria-hidden="true">0</span>
        </label>
    </div>';

    if (!empty($label)) {
        $switch = '<label for="'.$id.'" class="cell '.$labelClass.' switch-label">'.$label.'</label><div class="cell '.$switchContainerClass.'">'.$switch.'</div>';
    }

    return $switch;
}

function acym_selectTemplates($mailOptions, $selected, $type, $listId)
{
    echo '<input type="hidden" id="acym_template" name="mailSelected" value="'.$selected.'"/>';
    foreach ($mailOptions as $oneTpl) {
        echo '<div class="cell grid-x acym__template__block text-center">';
        $buttonSelectedClass = '';
        $iconSelectedClass = ' not_selected_template';
        if ($oneTpl->id == $selected) {
            $buttonSelectedClass = ' acym_template_option--selected';
            $iconSelectedClass = ' selected_template';
        }
        $button = '<i class="fa fa-check-circle '.$iconSelectedClass.'"></i>
                <button type="button" template="'.htmlspecialchars($oneTpl->id).'" class="cell acym__templates__oneTpl acym__listing__block acym_template_option'.$buttonSelectedClass.'">
                <div class="cell grid-x text-center">
                    <div class="cell acym__templates__pic text-center">
                        <img src="'.htmlspecialchars(((strpos($oneTpl->thumbnail, 'default_template_thumbnail') === false && strpos($oneTpl->thumbnail, 'default_template') === false) ? ACYM_TEMPLATE_THUMBNAILS.$oneTpl->thumbnail : $oneTpl->thumbnail)).'" alt="'.htmlspecialchars($oneTpl->name).'" />
                    </div>
                    <div class="cell grid-x text-center acym__templates__footer">
                        <div class="cell acym__template__footer__title">'.htmlspecialchars($oneTpl->name).'</div>
                    </div>
                </div>
            </button>
        </div>';
        echo $button;
    }
    echo '<div class="cell grid-x acym__template__block text-center align-center acym_vcenter"><a class="acym_vcenter text-center align-center acym__color__white acym__list__button__add__mail__welcome__unsub" href="'.acym_completeLink('mails&task=edit&step=editEmail&type='.$type.'&type_editor=acyEditor&return='.urlencode(acym_completeLink('lists&task=edit&step='.$type.'&id='.$listId.'&edition=1'))).'"><i class="material-icons">add</i></a></div>';
}

function acym_getCurrentIP()
{
    $ip = '';
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) && strlen($_SERVER['HTTP_X_FORWARDED_FOR']) > 6) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } elseif (!empty($_SERVER['HTTP_CLIENT_IP']) && strlen($_SERVER['HTTP_CLIENT_IP']) > 6) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['REMOTE_ADDR']) && strlen($_SERVER['REMOTE_ADDR']) > 6) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }

    return strip_tags($ip);
}

function acym_validEmail($email, $extended = false)
{
    if (empty($email) || !is_string($email)) {
        return false;
    }

    if (!preg_match('/^'.acym_getEmailRegex().'$/i', $email)) {
        return false;
    }

    if (!$extended) {
        return true;
    }


    $config = acym_config();

    if ($config->get('email_checkdomain', false) && function_exists('getmxrr')) {
        $domain = substr($email, strrpos($email, '@') + 1);
        $mxhosts = array();
        $checkDomain = getmxrr($domain, $mxhosts);
        if (!empty($mxhosts) && strpos($mxhosts[0], 'hostnamedoesnotexist')) {
            array_shift($mxhosts);
        }
        if (!$checkDomain || empty($mxhosts)) {
            $dns = @dns_get_record($domain, DNS_A);
            $domainChanged = true;
            foreach ($dns as $oneRes) {
                if (strtolower($oneRes['host']) == strtolower($domain)) {
                    $domainChanged = false;
                }
            }
            if (empty($dns) || $domainChanged) {
                return false;
            }
        }
    }

    if ($config->get('email_iptimecheck', 0)) {
        $lapseTime = time() - 7200;
        $ip = acym_getCurrentIP();
        $nbUsers = acym_loadResult('SELECT COUNT(*) FROM #__acym_subscriber WHERE created > '.intval($lapseTime).' AND ip = '.acym_escapeDB($ip));
        if ($nbUsers >= 3) {
            return false;
        }
    }

    return true;
}

function acym_backToListing($listingName)
{
    return '<p class="acym__back_to_listing"><a href="'.acym_completeLink($listingName).'"><i class="fa fa-chevron-left"></i> '.acym_translation('ACYM_BACK_TO_LISTING').'</a></p>';
}

function acym_sortBy($options = array(), $listing, $default = "")
{
    $default = empty($default) ? reset($options) : $default;

    $selected = acym_getVar('string', $listing.'_ordering', $default);
    $orderingSortOrder = acym_getVar('string', $listing.'_ordering_sort_order', 'desc');
    $classSortOrder = $orderingSortOrder == 'asc' ? 'fa-sort-amount-asc' : 'fa-sort-amount-desc';

    $display = '<span class="acym__color__dark-gray">'.acym_translation('ACYM_SORT_BY').'</span>
				<select name="'.$listing.'_ordering" id="acym__listing__ordering">';

    foreach ($options as $oneOptionValue => $oneOptionText) {
        $display .= '<option value="'.$oneOptionValue.'"';
        if ($selected == $oneOptionValue) {
            $display .= ' selected';
        }
        $display .= '>'.$oneOptionText.'</option>';
    }

    $display .= '</select>';

    $tooltipText = $orderingSortOrder == 'asc' ? acym_translation('ACYM_SORT_ASC') : acym_translation('ACYM_SORT_DESC');
    $display .= acym_tooltip('<i class="fa '.$classSortOrder.' acym__listing__ordering__sort-order" aria-hidden="true"></i>', $tooltipText);

    $display .= '<input type="hidden" id="acym__listing__ordering__sort-order--input" name="'.$listing.'_ordering_sort_order" value="'.$orderingSortOrder.'">';

    return $display;
}

function acym_enqueueNotification($message, $type = 'info', $time = 0)
{
    if (!acym_isAdmin()) {
        return acym_enqueueNotification_front($message, $type = 'info', $time = 0);
    }

    $notification = '';

    $logo = 'fa-bell';

    if ($type == 'success') {
        $notification .= '<div class="callout acym__callout__confirm"';
        $logo = 'fa-check-circle';
    } elseif ($type == 'warning') {
        $notification .= '<div class="callout acym__callout__warning"';
        $logo = 'fa-exclamation-triangle';
    } elseif ($type == 'error') {
        $notification .= '<div class="callout acym__callout__error"';
        $logo = 'fa-exclamation-circle';
    } else {
        $notification .= '<div class="callout acym__callout__information"';
    }
    $notification .= $time > 0 ? ' callout-timer="'.$time.'" style="display: none"><div class="progress" role="progressbar"><div class="progress-meter" style="width: 0"></div></div>' : ' style="display: none">';

    if (is_array($message)) {
        $message = implode('<br />', $message);
    }
    $notification .= '<i class="fa '.$logo.'"></i><div class="acym_notification_container">'.$message.'</div><i class="fa fa-close"></i></div>';

    if (empty($_SESSION)) {
        acym_session();
    }
    if (empty($_SESSION['acynotif'])) {
        $_SESSION['acynotif'] = array();
    }
    $_SESSION['acynotif'][] = $notification;
}

function acym_getJSMessages()
{
    $msg = "{";
    $msg .= '"email": "'.acym_translation('ACYM_VALID_EMAIL', true).'",';
    $msg .= '"number": "'.acym_translation('ACYM_VALID_NUMBER', true).'",';
    $msg .= '"requiredMsg": "'.acym_translation('ACYM_REQUIRED_FIELD', true).'",';
    $msg .= '"defaultMsg": "'.acym_translation('ACYM_DEFAULT_VALIDATION_ERROR', true).'"';

    $keysToLoad = array(
        'ACYM_ARE_YOU_SURE',
        'ACYM_INSERT_IMG_BAD_NAME',
        'ACYM_NON_VALID_URL',
        'ACYM_DYNAMIC_TEXT',
        'ACYM_ARE_YOU_SURE_DELETE',
        'ACYM_ARE_YOU_SURE_ACTIVE',
        'ACYM_ARE_YOU_SURE_INACTIVE',
        'ACYM_SEARCH_TAGS',
        'ACYM_SEARCH_CAMPAIGN',
        'ACYM_SEARCH_ENCODING',
        'ACYM_CANCEL',
        'ACYM_CONFIRM',
        'ACYM_TEMPLATE_CHANGED_CLICK_ON_SAVE',
        'ACYM_SURE_SEND_TRANSALTION',
        'ACYM_TESTS_SPAM_SENT',
        'ACYM_CONFIRMATION_CANCEL_CAMPAIGN_QUEUE',
        'ACYM_EXPORT_SELECT_LIST',
        'ACYM_YES',
        'ACYM_NO',
        'ACYM_NEXT',
        'ACYM_BACK',
        'ACYM_SKIP',
        'ACYM_INTRO_ADD_DTEXT',
        'ACYM_INTRO_CREATE_DD',
        'ACYM_INTRO_CREATE_HTML',
        'ACYM_INTRO_TEMPLATE',
        'ACYM_INTRO_DRAG_BLOCKS',
        'ACYM_INTRO_DRAG_CONTENT',
        'ACYM_INTRO_SETTINGS',
        'ACYM_INTRO_CUSTOMIZE_FONT',
        'ACYM_INTRO_IMPORT_CSS',
        'ACYM_INTRO_SAFE_CHECK',
        'ACYM_INTRO_MAIL_SETTINGS',
        'ACYM_INTRO_ADVANCED',
        'ACYM_INTRO_DKIM',
        'ACYM_INTRO_CRON',
        'ACYM_INTRO_SUBSCRIPTION',
        'ACYM_INTRO_CHECK_DATABASE',
        'ACYM_SEND_TEST_SUCCESS',
        'ACYM_SEND_TEST_ERROR',
        'ACYM_COPY_DEFAULT_TRANSLATIONS_CONFIRM',
        'ACYM_BECARFUL_BACKGROUND_IMG',
        'ACYM_CANT_DELETE_AND_SAVE',
        'ACYM_AND',
        'ACYM_OR',
        'ACYM_ERROR',
        'ACYM_EDIT_MAIL',
        'ACYM_CREATE_MAIL',
        'ACYM_NO_RAND_FOR_MULTQUEUE',
        'ACYM_DELETE_MY_DATA_CONFIRM',
        'ACYM_CHOOSE_COLUMN',
    );

    foreach ($keysToLoad as $oneKey) {
        $msg .= ',"'.$oneKey.'": "'.acym_translation($oneKey, true).'"';
    }

    $msg .= "}";

    return $msg;
}

global $acymPlugins;
function acym_loadPlugins()
{
    $dynamics = acym_getFolders(ACYM_BACK.'dynamics');

    global $acymPlugins;
    foreach ($dynamics as $oneDynamic) {
        $dynamicFile = ACYM_BACK.'dynamics'.DS.$oneDynamic.DS.'plugin.php';
        $className = 'plgAcym'.ucfirst($oneDynamic);

        if (isset($acymPlugins[$className]) || !file_exists($dynamicFile) || !include_once($dynamicFile)) {
            continue;
        }
        if (!class_exists($className)) {
            continue;
        }

        $plugin = new $className();
        if (!in_array($plugin->cms, array('all', 'Joomla')) || !$plugin->installed) {
            continue;
        }

        $acymPlugins[$className] = $plugin;
    }
}

function acym_getPlugin($family, $name = null)
{
    $plugin = new stdClass();
    $plugin->params = array();

    return $plugin;
}

function acym_trigger($method, $args = array(), $plugin = null)
{
    global $acymPlugins;
    if (empty($acymPlugins)) {
        acym_loadPlugins();
    }

    $result = array();
    foreach ($acymPlugins as $class => $onePlugin) {
        if (!method_exists($onePlugin, $method)) {
            continue;
        }
        if (!empty($plugin) && $class != $plugin) {
            continue;
        }
        $value = call_user_func_array(array($onePlugin, $method), $args);

        if (isset($value)) {
            $result[] = $value;
        }
    }

    return $result;
}

function acym_displayParam($type, $value, $name, $params = array())
{
    if (!include_once(ACYM_FRONT.'params'.DS.$type.'.php')) {
        return '';
    }

    $class = 'JFormField'.ucfirst($type);

    $field = new $class();
    $field->value = $value;
    $field->name = $name;

    if (!empty($params)) {
        foreach ($params as $param => $val) {
            $field->$param = $val;
        }
    }

    return $field->getInput();
}

function acym_upgradeTo($version)
{
    $link = $version == 'essential' ? 'https://www.acyba.com/acymailing/essential.html' : 'https://www.acyba.com/acymailing/enterprise.html';
    $text = $version == 'essential' ? 'ACYM_ESSENTIAL' : 'ACYM_ENTERPRISE';
    echo '<div class="acym__upgrade cell grid-x text-center align-center">
            <h1 class="acym__listing__empty__title cell">'.acym_translation_sprintf('ACYM_USE_THIS_FEATURE', '<span class="acym__color__blue">'.acym_translation($text).'</span>').'</h1>
            <a target="_blank" href="'.$link.'" class="button smaller-button cell shrink">'.acym_translation('ACYM_UPGRADE_NOW').'</a>
          </div>';
}

function acym_checkbox($values, $name, $selected = array(), $label = '', $parentClass = '', $labelClass = '')
{
    echo '<div class="'.$parentClass.'"><div class="cell acym__label '.$labelClass.'">'.$label.'</div><div class="cell auto grid-x">';
    foreach ($values as $key => $value) {
        echo '<label class="cell grid-x margin-top-1"><input type="checkbox" name="'.$name.'" value="'.$key.'" '.(in_array($key, $selected) ? 'checked' : '').' >'.$value.'</label>';
    }
    echo '</div></div>';
}

function acym_table($name, $component = true)
{
    $prefix = $component ? ACYM_DBPREFIX : '#__';

    return $prefix.$name;
}


function acym_existsAcyMailing59()
{
    $allTables = acym_getTables();

    if (in_array(acym_getPrefix()."acymailing_config", $allTables)) {
        $queryVersion = "SELECT `value` FROM #__acymailing_config WHERE `namekey` LIKE 'version'";

        $version = acym_loadResult($queryVersion);

        if (version_compare($version, "5.9.0") >= 0) {
            return true;
        }
    }

    return false;
}


include_once(ACYM_LIBRARY.'class.php');
include_once(ACYM_LIBRARY.'parameter.php');
include_once(ACYM_LIBRARY.'controller.php');
include_once(ACYM_LIBRARY.'view.php');
include_once(ACYM_LIBRARY.'plugin.php');

acym_loadLanguage();
