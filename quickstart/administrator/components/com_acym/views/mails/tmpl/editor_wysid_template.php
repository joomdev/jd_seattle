<?php
defined('_JEXEC') or die('Restricted access');
?><?php
if (strpos($this->content, 'acym__wysid__template') !== false) {
    echo $this->content;
} else {
    echo $this->defaultTemplate;
}

