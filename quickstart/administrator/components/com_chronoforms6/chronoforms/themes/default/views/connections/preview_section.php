<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
$parser = new \G2\A\E\Chronofc\H\Parser($this);

echo $parser->section(array_keys($this->data['Connection']['sections'])[0]);