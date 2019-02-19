<?php

/** @var array $displayData
 */
extract($displayData);


echo '<div class="accordion-group' . $class . '">'
	. '<div class="accordion-heading">'
	. '<strong><a href="#' . $id . '" data-toggle="collapse" ' . $parent . ' class="accordion-toggle' . $collapsed . '">'
	. $text
	. '</a></strong>'
	. '</div>'
	. '<div class="accordion-body collapse ' . $in . '" id="' . $id . '">'
	. '<div class="accordion-inner">';
