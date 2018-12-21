<?php
/**
 * @package   Astroid Framework
 * @author    JoomDev https://www.joomdev.com
 * @copyright Copyright (C) 2009 - 2018 JoomDev.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
 */

defined('_JEXEC') or die;
?>
<div class="latest-news"> 
    <div class="footer-recent-post">
        <ul class="list-inline">
            <?php foreach ($list as $item): ?>
                <li>
                    <a href="<?php echo $item->link;?>">
                        <span class="newsTitle"><?php echo  $item->title; ?> </span>
                        <time class="badge badge-primary newsTime">
                            <i class="lni-alarm-clock"></i><?php echo  $created	=	date_format(date_create($item->created),"d M  Y"); ?>
                        </time>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>