<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php if(\GApp::extension($ext)->valid() === false): ?>
<div class="ui message red">
	Your <?php echo $name; ?> installation on <strong><?php echo \G2\L\Url::domain(false); ?></strong> is NOT validated, there is a <strong style="text-decoration:underline;"><?php echo $msg; ?></strong>.
	<a class="ui button green small compact right labeled icon" href="<?php echo r2('index.php?ext='.$ext.'&act=validateinstall'); ?>"><?php el('Validate Now'); ?><i class="icon right chevron"></i></a>
</div>
<?php endif; ?>
<?php /*if(is_numeric(\GApp::extension($ext)->valid())): ?>
<div class="ui message yellow">
	Your <?php echo $name; ?> installation on <strong><?php echo \G2\L\Url::domain(false); ?></strong> is in trial mode and will expire in <?php echo \GApp::extension($ext)->valid(); ?> days.
</div>
<?php endif;*/ ?>