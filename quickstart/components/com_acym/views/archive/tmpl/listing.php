<?php
/**
 * @package	AcyMailing for Joomla
 * @version	6.2.2
 * @author	acyba.com
 * @copyright	(C) 2009-2019 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><div class="acym_front_page">

    <?php

    $matchingNewsletters = $data['newsletters'];

    $paramsJoomla = $data['paramsJoomla'];

    if (!empty($paramsJoomla['show_page_heading'])) {
        echo '<h1 class="contentheading'.$paramsJoomla['suffix'].'">'.$paramsJoomla['page_heading'].'</h1>';
    }

    echo '<div class="acym__front__archive">';

    echo '<form method="post" action="'.acym_completeLink('archive').'" id="acym_form">';

    echo '<h1 class="acym__front__archive__title">'.acym_translation("ACYM_NEWSLETTERS").'</h1>';

    foreach ($matchingNewsletters as $oneNewsletter) {
        echo '<p class="acym__front__archive__newsletter_name" data-nlid="'.$oneNewsletter->id.'">'.$oneNewsletter->subject.'</p>';
        echo '<p class="acym__front__archive__newsletter_sending-date">'.acym_translation("ACYM_SENDING_DATE")." : ".acym_date($oneNewsletter->sending_date, "d M Y").'</p>';
        echo '<div id="acym__front__archive__modal__'.$oneNewsletter->id.'" class="acym__front__archive__modal" style="display: none">';
        echo '<div class="acym__front__archive__modal__content">';
        echo '<div class="acym__front__archive__modal__close"><span>&times;</span></div>';
        echo empty($data['userId']) ? '<p class="acym_front_message_warning">'.acym_translation("ACYM_FRONT_ARCHIVE_NOT_CONNECTED").'</p>' : '';

        echo '<iframe class="acym__front__archive__modal__iframe '.(empty($data['userId']) ? ' acym__front__not_connected_user' : '').'" src="'.acym_frontendLink('archive&task=view&id='.$oneNewsletter->id.'&'.acym_noTemplate()).'"></iframe>';

        echo '</div>';
        echo '</div>';
    }

    echo $data['pagination']->displayFront();

    echo '<input type="hidden" name="page" id="acym__front__archive__next-page">';
    echo '</form>';

    echo "</div>";
    ?>

</div>

