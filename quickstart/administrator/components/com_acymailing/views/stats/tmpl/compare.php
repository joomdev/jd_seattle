<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	5.10.3
 * @author	acyba.com
 * @copyright	(C) 2009-2018 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><div id="acy_content">
    <div id="iframedoc"></div>
    <form action="<?php echo acymailing_completeLink('stats'); ?>" method="post" name="adminForm" id="adminForm" style="text-align: center;">
        <div class="onelineblockoptions">
            <table class="acymailing_table">

            <?php
            $properties = array('JOOMEXT_SUBJECT','SEND_DATE','ACY_SENT','OPEN','CLICKED_LINK','ACY_CLICK_EFFICIENCY','UNSUBSCRIBE','FORWARDED','BOUNCES','FAILED');

            foreach($properties as $oneProp){
                echo '<tr><td>'.acymailing_translation($oneProp).'</td>';
                for($i = 0, $a = count($this->rows); $i < $a; $i++) {
                    $row =& $this->rows[$i];
                    $cleanSent = $row->senthtml + $row->senttext - $row->bounceunique;

                    if($oneProp != 'JOOMEXT_SUBJECT') echo '<td>';

                    if($oneProp == 'JOOMEXT_SUBJECT'){
                        echo '<td style="width: '.(100/(count($this->rows)+1)).'%;">';
                        $row->subject = acyEmoji::Decode($row->subject); ?>
                        <input type="hidden" name="cid[]" value="<?php echo $row->mailid; ?>">
                        <?php echo acymailing_popup(acymailing_completeLink('diagram&task=mailing&mailid='.$row->mailid, true), strlen($row->subject) > 30 ? acymailing_tooltip($row->subject, '', '', substr($row->subject, 0, 30).'...') : $row->subject, '', 800, 590); ?>
                    <?php }elseif($oneProp == 'SEND_DATE'){ ?>
                        <span style="font-size: 10px;"><?php echo acymailing_getDate($row->senddate); ?></span>
                    <?php }elseif($oneProp == 'ACY_SENT'){ ?>
                        <?php $text = '<b>'.acymailing_translation('HTML').' : </b>'.$row->senthtml;
                        $text .= '<br /><b>'.acymailing_translation('JOOMEXT_TEXT').' : </b>'.$row->senttext;
                        $title = acymailing_translation('ACY_SENT');
                        echo acymailing_tooltip($text, $title, '', $row->senthtml + $row->senttext, acymailing_completeLink('stats&task=detaillisting&filter_status=0&filter_mail='.$row->mailid)); ?>
                    <?php }elseif($oneProp == 'OPEN'){
                        if(!empty($row->senthtml)){
                            $text = '<b>'.acymailing_translation('OPEN_UNIQUE').' : </b>'.$row->openunique.' / '.$cleanSent;
                            $text .= '<br /><b>'.acymailing_translation('OPEN_TOTAL').' : </b>'.$row->opentotal;
                            $pourcent = ($cleanSent == 0 ? '0%' : (substr($row->openunique / $cleanSent * 100, 0, 5)).'%');
                            $title = acymailing_translation_sprintf('PERCENT_OPEN', $pourcent);
                            echo acymailing_tooltip($text, $title, '', $pourcent, acymailing_completeLink('stats&task=detaillisting&filter_status=open&filter_mail='.$row->mailid));
                        }
                    }elseif($oneProp == 'CLICKED_LINK'){
                        $text = '<b>'.acymailing_translation('UNIQUE_HITS').' : </b>'.$row->clickunique.' / '.$cleanSent;
                        $text .= '<br /><b>'.acymailing_translation('TOTAL_HITS').' : </b>'.$row->clicktotal;
                        $pourcent = ($cleanSent == 0 ? '0%' : (substr($row->clickunique / $cleanSent * 100, 0, 5)).'%');
                        $title = acymailing_translation_sprintf('PERCENT_CLICK', $pourcent);
                        echo acymailing_tooltip($text, $title, '', $pourcent, acymailing_completeLink('statsurl&filter_mail='.$row->mailid));
                    }elseif($oneProp == 'ACY_CLICK_EFFICIENCY'){
                        $text = '<b>'.acymailing_translation('UNIQUE_HITS').' : </b>'.$row->clickunique.' / '.$row->openunique;
                        $text .= '<br /><b>'.acymailing_translation('OPEN_UNIQUE').' : </b>'.$row->openunique;
                        $pourcentEfficiency = ($row->openunique == 0 ? '0%' : (substr($row->clickunique / $row->openunique * 100, 0, 5)).'%');
                        $title = acymailing_translation_sprintf('ACY_CLICK_EFFICIENCY_DESC', $pourcentEfficiency);
                        echo acymailing_tooltip($text, $title, '', $pourcentEfficiency, acymailing_completeLink('statsurl&filter_mail='.$row->mailid));
                    }elseif($oneProp == 'UNSUBSCRIBE'){
                        echo acymailing_popup(acymailing_completeLink('stats&task=unsubchart&mailid='.$row->mailid, true), '<i class="acyicon-statistic"></i>', '', 800, 590);
                        $pourcent = ($cleanSent == 0) ? '0%' : (substr($row->unsub / $cleanSent * 100, 0, 5)).'%';
                        $text = $row->unsub.' / '.$cleanSent;
                        $title = acymailing_translation('UNSUBSCRIBE');
                        echo acymailing_popup(acymailing_completeLink('stats&start=0&task=unsubscribed&filter_mail='.$row->mailid, true), acymailing_tooltip($text, $title, '', $pourcent), '', 800, 590);
                    }elseif($oneProp == 'FORWARDED'){
                        echo acymailing_popup(acymailing_completeLink('stats&start=0&task=forward&filter_mail='.$row->mailid, true), $row->forward, '', 800, 590);
                    }elseif($oneProp == 'BOUNCES'){
                        echo acymailing_popup(acymailing_completeLink('bounces&task=chart&mailid='.$row->mailid, true), '<i class="acyicon-statistic"></i>', '', 800, 590);
                        $text = $row->bounceunique.' / '.($row->senthtml + $row->senttext);
                        $title = acymailing_translation('BOUNCES');
                        $pourcent = (empty($row->senthtml) AND empty($row->senttext)) ? '0%' : (substr($row->bounceunique / ($row->senthtml + $row->senttext) * 100, 0, 5)).'%';
                        echo acymailing_tooltip($text, $title, '', $pourcent, acymailing_completeLink('stats&task=detaillisting&filter_status=bounce&filter_mail='.$row->mailid));
                    }else{ ?>
                        <a href="<?php echo acymailing_completeLink('stats&task=detaillisting&filter_status=failed&filter_mail='.$row->mailid); ?>">
                            <?php echo $row->fail; ?>
                        </a>
                    <?php }

                    echo '</td>';
                }
                echo '</tr>';
            }
            ?>
            </table>
        </div>
        <?php acymailing_formOptions(); ?>
    </form>
</div>
