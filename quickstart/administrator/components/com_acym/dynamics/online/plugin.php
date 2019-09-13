<?php
/**
 * @package	AcyMailing for Joomla
 * @version	6.2.2
 * @author	acyba.com
 * @copyright	(C) 2009-2019 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><?php

class plgAcymOnline extends acymPlugin
{
    function dynamicText()
    {
        $onePlugin = new stdClass();
        $onePlugin->name = acym_translation('ACYM_WEBSITE_LINKS');
        $onePlugin->plugin = __CLASS__;
        $onePlugin->help = 'plugin-online';

        return $onePlugin;
    }

    function textPopup()
    {
        $others = [];
        $others['readonline'] = ['default' => acym_translation('ACYM_VIEW_ONLINE', true), 'desc' => acym_translation('ACYM_VIEW_ONLINE_DESC')];

        ?>
		<script language="javascript" type="text/javascript">
            <!--
            var selectedTag = '';

            function changeOnlineTag(tagName) {
                selectedTag = tagName;
                defaultText = [];
                <?php
                foreach ($others as $tagname => $tag) {
                    echo 'defaultText["'.$tagname.'"] = "'.$tag['default'].'";';
                }
                ?>
                jQuery('#tr_' + tagName).addClass('selected_row');
                document.getElementById('acym__popup__online__tagtext').value = defaultText[tagName];

                setOnlineTag();
            }

            function setOnlineTag() {
                var tag = '{' + selectedTag + '}' + document.getElementById('acym__popup__online__tagtext').value + '{/' + selectedTag + '}';
                setTag(tag, jQuery('#tr_' + selectedTag));
            }

            //-->
		</script>

		<div class="acym__popup__listing text-center grid-x">
			<div class="grid-x medium-12 cell acym__listing__row text-left">
				<div class="grid-x cell medium-5 small-12 acym__listing__title acym__listing__title__dynamics">
					<label class="small-3" style="line-height: 40px;" for="acym__popup__online__tagtext"><?php echo acym_translation('ACYM_TEXT'); ?>: </label>
					<input class="small-9" type="text" name="tagtext" id="acym__popup__online__tagtext" onchange="setOnlineTag();">
				</div>
				<div class="medium-auto"></div>
			</div>

            <?php
            foreach ($others as $tagname => $tag) {
                $onclick = 'changeOnlineTag(\''.$tagname.'\');';
                echo '<div class="grid-x small-12 cell acym__listing__row acym__listing__row__popup text-left"  onclick="'.$onclick.'" id="tr_'.$tagname.'" ><div class="cell small-12 acym__listing__title acym__listing__title__dynamics">'.$tag['desc'].'</div></div>';
            }
            ?>
		</div>

        <?php
    }

    function replaceContent(&$email, $send = true)
    {
        $match = '#(?:{|%7B)(readonline)([^}]*)(?:}|%7D)(.*)(?:{|%7B)/(readonline)(?:}|%7D)#Uis';
        $variables = ['body'];
        $found = false;
        $results = [];
        foreach ($variables as $var) {
            if (empty($email->$var)) continue;

            $found = preg_match_all($match, $email->$var, $results[$var]) || $found;
            if (empty($results[$var][0])) unset($results[$var]);
        }

        if (!$found) return;

        $tags = [];

        foreach ($results as $var => $allresults) {
            foreach ($allresults[0] as $i => $oneTag) {
                if (isset($tags[$oneTag])) {
                    continue;
                }
                $arguments = explode('|', strip_tags(str_replace('%7C', '|', $allresults[2][$i])));
                $tag = new stdClass();
                $tag->type = $allresults[1][$i];
                for ($j = 0, $a = count($arguments) ; $j < $a ; $j++) {
                    $args = explode(':', $arguments[$j]);
                    $arg0 = trim($args[0]);
                    if (empty($arg0)) {
                        continue;
                    }
                    if (isset($args[1])) {
                        $tag->$arg0 = $args[1];
                    } else {
                        $tag->$arg0 = true;
                    }
                }

                $addkey = empty($email->key) ? '' : '&key='.$email->key;
                $lang = empty($email->lang) ? '' : '&lang='.$email->lang;

                $link = '';
                if ($tag->type == 'readonline') {
                    $link = acym_frontendLink('archive&task=view&id='.$email->id.'&userid={subtag:id}-{subtag:key}'.$addkey.$lang.'&'.acym_noTemplate());
                }

                if (empty($allresults[3][$i])) {
                    $tags[$oneTag] = $link;
                } else {
                    $tags[$oneTag] = '<a style="text-decoration:none;" href="'.$link.'" target="_blank"><span class="acym_online">'.$allresults[3][$i].'</span></a>';
                }
            }
        }

        $this->acympluginHelper->replaceTags($email, $tags);
    }
}

