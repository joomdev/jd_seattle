<?php
/**
 * @package		Register Login Joomla Module
 * @author		JoomDev
 * @copyright	Copyright (C) 2018 Joomdev, Inc. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die;
JHtml::_('behavior.keepalive');
JHtml::_('bootstrap.tooltip');
$type = 'mootools-core.js';
JHTML::_('behavior.modal');
JHtml::_('behavior.framework', $type);
$document  = JFactory::getDocument();
$itemId = JFactory::getApplication()->getMenu()->getActive()->id;
$document->addScriptDeclaration('var itemId = "'.$itemId .'";    ');
$error =  '';
$view  = (isset($_REQUEST['openview']) && !empty($_REQUEST['openview'])) ? $_REQUEST['openview'] : $params->get('view');

if(@$_SESSION['jd_user_registered'] == 1){
    $view = 1;
}
$_SESSION['jd_user_registered'] = 0;

$layout =  $params->get('view1');

$document->addScript(JURI::root() .'modules/mod_registerlogin/tmpl/assets/jquery.validate.js');
if($params->get('ajax_registration')){
     $document->addScript(JURI::root() .'modules/mod_registerlogin/tmpl/assets/registerloginajax.js');   
}else{
     $document->addScript(JURI::root() .'modules/mod_registerlogin/tmpl/assets/registerlogin.js');
}
$usersConfig = JComponentHelper::getParams( 'com_users' );
$siteKey = $params->get('sitekey');
$secret = $params->get('secretkey');
$lang = 'en';
?>
    <link href="<?php echo JURI::root() .'modules/mod_registerlogin/tmpl/assets/registerlogin.css' ?>" type="text/css" rel="stylesheet" />
    <script type="text/javascript" src="https://www.google.com/recaptcha/api.js?hl=<?php echo $lang; ?>"></script>
    <script type="text/javascript" src="<?php echo JURI::root() .'modules/mod_registerlogin/tmpl/assets/jquery.registerloginplugin.js'?>"></script>
    <div id="error_message1">
        <?php if($errorMessage){ ?>
            <div class="alert alert-error"><a data-dismiss="alert" class="close">x</a>
                <div>
                    <p>
                        <?php echo $errorMessage; ?>
                    </p>
                </div>
            </div>
        <?php } ?>
    </div>
    <div id="jd-logrig-module-<?php echo $module->id; ?>" class="jd-register-login-wrapper jd-clearfix">
        <div class="jd-register-login-container jd-clearfix">
            <?php if(isset($layout ) && $layout  == 1){ ?>
               <ul class="jd-register-login-tab">
                  <li class="jd-inputbox-control jd-control-check-raido"><input class="jd-form-checkbox-radio <?php echo (isset($view) && $view  == 1) ? 'active' : ''; ?>" type="radio" value="1" name="view" id="login_view" data-tab-target="#jd-login-container-<?php echo $module->id; ?>" <?php echo (isset($view) && $view==1 ) ? 'checked="checked"' : ''; ?> >
                     <?php echo JText::_('MOD_REGISTERLOGIN_PARAM_DEFAULTVIEW_LOGIN'); ?>
                  </li>
                  <li class="jd-inputbox-control jd-control-check-raido"><input class="jd-form-checkbox-radio <?php echo (isset($view) && $view  == 2) ? 'active' : ''; ?>" type="radio" value="2" name="view" id="register_view" data-tab-target="#jd-register-container-<?php echo $module->id; ?>" <?php echo (isset($view) && $view==2 ) ? 'checked="checked"' : ''; ?> >
                     <?php echo JText::_('MOD_REGISTERLOGIN_PARAM_DEFAULTVIEW_REGISTER'); ?>
                  </li>
               </ul>
            <?php } ?>
            <!-- End radio Tab -->
            <?php if(isset($layout ) && $layout  == 2){ ?>
               <ul class="jd-register-login-tab">
                  <li><span class="<?php echo (isset($view) && $view  == 1) ? 'active' : 'notactive'; ?>" data-tab-target="#jd-login-container-<?php echo $module->id; ?>"><?php echo JText::_('MOD_REGISTERLOGIN_LOGINLEBEL'); ?></span></li>
                  <li><span class="<?php echo (isset($view) && $view  == 2) ? 'active' : 'notactive'; ?>" data-tab-target="#jd-register-container-<?php echo $module->id; ?>"><?php echo JText::_('MOD_REGISTERLOGIN_REGISTERLEBEL'); ?></span></li>
            </ul>
            <?php } ?>
            <!-- End Tab  -->
            <div class="jd-register-login-box">
                <div data-tab id="jd-login-container-<?php echo $module->id; ?>" class="jd-login-container">
                    <form action="<?php echo JRoute::_('index.php', true, $params->get('usesecure')); ?>" method="post" id="login-form" name="josForm" class="form-validate form-horizontal">
                        <div class="jd-inputbox-control">
                            <?php if ($params->get('usetext')) : ?><label for=""><?php echo JText::_('COM_USERS_LOGIN_USERNAME_LABEL'); ?> </label>
                            <?php endif; ?>
                            <input type="text" id="modlgn-username" name="username" class="jd-form-input required" value="" tabindex="0" size="18" placeholder="<?php  if(!$params->get('usetext')) { echo JText::_('COM_USERS_LOGIN_USERNAME_LABEL'); } ?>" required="true">
                        </div>
                        <div class="jd-inputbox-control">
                            <?php if ($params->get('usetext')) : ?><label for=""><?php echo JText::_('COM_USERS_PROFILE_PASSWORD1_LABEL'); ?> </label>
                            <?php endif; ?>
                            <input value="" id="modlgn_passwd" type="password" name="password" class="jd-form-input required password" tabindex="0" size="18" placeholder="<?php  if(!$params->get('usetext')) { echo JText::_('COM_USERS_PROFILE_PASSWORD1_LABEL'); } ?>" required="true">
                            <span class="jd-input-group-addon" onclick="previewpass()">
                                <span class="showpass" id="loginshowpass" aria-hidden="true"><?php echo JText::_('JSHOW') ?></span>
                            </span>
                        </div>
                        <div class="jd-inputbox-control jd-control-check-raido">
                            <?php if (JPluginHelper::isEnabled('system', 'remember')) : ?><label for=""><input type="checkbox" name="remember" class="jd-form-checkbox-radio" value="yes"><?php echo JText::_('COM_USERS_LOGIN_REMEMBER_ME') ?></label>
                            <?php endif; ?>
                        </div>
                        <div class="jd-button-control">
                            <input type="hidden" value="login" name="module<?php echo $module->id; ?>">
                            <button type="submit" tabindex="0" id="submit" name="Submit" class="jd-form-button"><?php echo JText::_('JLOGIN') ?></button>
                            <input type="hidden" name="option" value="com_users" />
                            <input type="hidden" name="task" value="user.login" />
                            <input type="hidden" name="return" value="<?php echo $return; ?>" />
                            <?php echo JHtml::_('form.token'); ?>
                        </div>
                    </form>
                    <div class="jd-list-wrapper">
                        <div class="jd-list-group">
                            <a href="<?php echo JRoute::_('index.php?option=com_users&view=remind'); ?>" class="jd-list-block ForgotUser">
                                <?php echo JText::_('MOD_REGISTERLOGIN_FORGOT_YOUR_USERNAME'); ?>
                            </a>
                            <a href="<?php echo JRoute::_('index.php?option=com_users&view=reset'); ?>" class="jd-list-block ForgotpPass">
                                <?php echo JText::_('MOD_REGISTERLOGIN_FORGOT_YOUR_PASSWORD'); ?>
                            </a>
                        </div>
                    </div>
                </div>
                <!-- End jd login container -->
                
                  <div data-tab id="jd-register-container-<?php echo $module->id; ?>" class="jd-register-container">
                      <?php if ($usersConfig->get('allowUserRegistration')) { ?>
                        <form action="" method="post" id="registration_form" name="josForm" class="form-validate form-horizontal">
                            <div class="jd-inputbox-control">
                                <?php if ($params->get('usetext')) : ?><label for=""><?php echo JText::_('COM_USERS_REGISTER_NAME_LABEL'); ?> </label>
                                <?php endif; ?>
                                <input tabindex="1" placeholder="<?php if(!$params->get('usetext')) { echo JText::_('COM_USERS_REGISTER_NAME_LABEL'); } ?>" type="text" name="jform[name]" id="jform_name" size="20" class="jd-form-input required" required/>
                            </div>
                            <div class="jd-inputbox-control">
                                <?php if ($params->get('usetext')) : ?><label for=""><?php echo JText::_('JGLOBAL_USERNAME'); ?> </label>
                                <?php endif; ?>
                                <input tabindex="2" type="text" placeholder="<?php if(!$params->get('usetext')) { echo JText::_('JGLOBAL_USERNAME'); } ?>" id="jform_username" name="jform[username]" size="20" class="jd-form-input required" required/>
                            </div>
                            <div class="jd-inputbox-control">
                                <?php if ($params->get('usetext')) : ?><label for=""><?php echo JText::_('JGLOBAL_PASSWORD'); ?> </label>
                                <?php endif; ?>
                                <input tabindex="3" placeholder="<?php  if(!$params->get('usetext')) { echo JText::_('JGLOBAL_PASSWORD'); } ?>" class="jd-form-input required" type="password" id="jform_password1" name="jform[password1]" size="20" value="" required/>
                            </div>
                            <div class="jd-inputbox-control">
                                <?php if ($params->get('usetext')) : ?><label for=""><?php echo JText::_('COM_USERS_REGISTER_PASSWORD2_DESC'); ?> </label>
                                <?php endif; ?>
                                <input tabindex="4" placeholder="<?php if(!$params->get('usetext')) { echo JText::_('COM_USERS_REGISTER_PASSWORD2_DESC'); } ?>" data-rule-equalTo="#jform_password1" class="jd-form-input required" type="password" id="jform_password2" name="jform[password2]" size="20" value="" required/>
                                <span class="jd-input-group-addon" onclick="previewpassonregister()">
                                    <span class="showpass" id="regshowpass" aria-hidden="true"><?php echo JText::_('JSHOW') ?></span>
                                </span>
                            </div>
                            <div class="jd-inputbox-control">
                                <?php if ($params->get('usetext')) : ?><label for=""><?php echo JText::_('COM_USERS_REGISTER_EMAIL1_DESC'); ?> </label>
                                <?php endif; ?>
                                <input tabindex="5" placeholder="<?php if(!$params->get('usetext')) { echo JText::_('COM_USERS_REGISTER_EMAIL1_DESC'); } ?>" type="email" id="jform_email1" name="jform[email1]" size="20" class="jd-form-input validate-email required email" required/>
                            </div>
                            <div class="jd-inputbox-control">
                                <?php if ($params->get('usetext')) : ?><label for=""><?php echo JText::_('COM_USERS_REGISTER_EMAIL2_DESC'); ?> </label>
                                <?php endif; ?>
                                <input tabindex="6" placeholder="<?php if(!$params->get('usetext')) { echo JText::_('COM_USERS_REGISTER_EMAIL2_DESC');} ?>" type="email" id="jform_email2" name="jform[email2]" size="20" class="jd-form-input required email" data-rule-equalTo="#jform_email1" required/>
                            </div>
                            <div class="jd-inputbox-control">
                                <?php if ($params->get('enablecap_on_register')) { ?>
                                <?php if ($params->get('usetext')) : ?>
                                <label for="">Captcha </label>
                                <?php endif; ?>
                                <?php
                            if($siteKey){ ?>
                                    <div class="g-recaptcha" data-sitekey="<?php echo $siteKey; ?>"></div>
                                    <?php }
                            else{
                                JError::raiseWarning( 100, 'Please enter the ReCaptcha public and secret key' ); ?>
                                    <span class="jd-error">Please enter the ReCaptcha public and secret key</span>
                                    <?php } } ?>
                            </div>
                            <?php  if ($params->get('tou')) { ?>
                            <div class="jd-inputbox-control jd-control-check-raido">
                                <label for="" class="" >
                            <input name="terms" class="required" type="checkbox" <?php if($params->get('checkbox')) { echo "checked='checked'"; } ?>  id="tou" required="true"/> &nbsp 
                            <?php if($params->get('newwindow') == 'modal'){  ?>
                            <a href="<?php echo JURI::root(); ?>index.php?option=com_content&view=article&id=<?php echo $params->get('articleid') ?>&tmpl=component" rel="{handler:'iframe', size:{x:1000,y:700}}" class="modal jd-modal-link"><?php echo $title = $params->get('title') ? $params->get('title') : "I Agree to the Terms of Use"; ?></a>
                            <?php } else {  ?>
                            <a id="terms_" href="<?php echo JURI::root(); ?>index.php?option=com_content&view=article&id=<?php echo $params->get('articleid') ?>" target="<?php echo $params->get('newwindow'); ?>"><?php echo $title = $params->get('title') ? $params->get('title') : "I Agree to the Terms of Use"; ?></a>
                            <?php } ?>
                            </label>
                            </div>
                            <?php } ?>
                            <div class="jd-button-control">
                                <button type="submit" id="register_submit" name="Submit" class="jd-form-button validate"><?php echo JText::_('JREGISTER') ?> <img src="<?php echo JURI::root(); ?>/modules/mod_registerlogin/tmpl/assets/loader.gif" class="regload" style="display:none;" /></button>
                                <input type="hidden" value="register" name="module<?php echo $module->id; ?>">
                                <input type="hidden" value="" name="openview" id="openview">
                                <?php echo JHTML::_('form.token'); ?>
                            </div>
                        </form>

                     <?php }else{ ?>
                        <div class="alert alert-warning m-2 p-2">
                            <strong><?php echo JText::_('WARNING'); ?></strong><?php echo JText::_('MOD_REGISTERLOGIN_NOT_ALLWED'); ?>
                        </div>
                     <?php } ?>
                  </div>
            </div>
            <!-- End jd register login box -->
        </div>
    </div>
    <script>
        (function($) {
            $('#jd-logrig-module-<?php echo $module->id; ?>').jdRegisterLogin();
            <?php if(!$params->get('ajax_registration')){ ?>
                $('#registration_form').validate({ // initialize the plugin
                    rules: {
                        'terms[]': {
                            required: true,
                            maxlength: 2
                        }
                    },
                    messages: {
                        'terms[]': {
                            required: "You must check at least 1 box",
                            maxlength: "Check no more than {0} boxes"
                        }
                    },
                });
            <?php } ?>
        }(jQuery))

        function previewpass() {
            var login = document.getElementById("modlgn_passwd");
           
            if (login.type === "password") {
                login.type = "text";
                document.getElementById("loginshowpass").innerHTML = "<?php echo JText::_('JSHOW') ?>";
            } else {
                login.type = "password";
                document.getElementById("loginshowpass").innerHTML = "<?php echo JText::_('JHIDE') ?>";
            }
        }

        function previewpassonregister() {
            var pass1 = document.getElementById("jform_password1");
            var pass2 = document.getElementById("jform_password2");
            if ((pass1.type === "password") && (pass2.type === "password")) {
                pass1.type = "text";
                pass2.type = "text";
                document.getElementById("reghidepass").innerHTML = "<?php echo JText::_('JHIDE') ?>";
            } else {
                pass1.type = "password";
                pass2.type = "password";
                document.getElementById("reghidepass").innerHTML = "<?php echo JText::_('JSHOW') ?>";
            }
        }
    </script>