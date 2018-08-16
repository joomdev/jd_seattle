<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	5.10.3
 * @author	acyba.com
 * @copyright	(C) 2009-2018 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><div id="acy_content" class="installacysms">
    <div id="iframedoc"></div>
    <span style="font-weight: bold;"><i class="acyicon-statistic" style="margin-right: 10px;vertical-align:middle;"></i><?php echo acymailing_translation('ACY_SMS_PRESENTATION'); ?></span>
    <div id="startbutton" class="myacymailingarea"><button onclick="document.getElementById('meter').style.display = '';document.getElementById('startbutton').style.display = 'none';installAcySMS();"><?php echo acymailing_translation('ACY_TRY_IT'); ?></button></div>
    <div id="meter" style="display:none;">
        <div>
            <span id="progressbar"></span>
            <div id="information"></div>
        </div>
    </div>
    <div id="postinstall" style="display:none;font-weight: bold;margin-top: 15px;">
        <?php echo acymailing_translation_sprintf('ACY_INSTALLED', '<a href="https://www.acyba.com/member-area/your-subscription.html#acysms-uexpress" target="_blank">', '</a>'); ?>
        <div class="myacymailingarea"><a href="index.php?option=com_acysms" ><button><?php echo acymailing_translation('ACY_TRY_IT'); ?></button></a></div>
    </div>

    <div id="acy_main_features" style="max-width: 980px;margin:auto;margin-top:50px;">
        <div class="contentsize shadowleft" style="padding-top: 0px;">
            <div class="row-fluid">
                <div class="span8">
                    <h4>Send personalized messages</h4>
                    <ul>
                        <li><strong>Filter your users</strong> for targeted communication. Revive the customers who bought a product or the attenders of an event...</li>
                        <li>Create <strong>marketing campaigns</strong> with follow-up messages. <strong>Automatically send a SMS</strong> to your contact X days after his subscription.</li>
                        <li><strong>Personalize your communication</strong> using information from the user profile (ex: Happy birthday "John"!)</li>
                    </ul>
                </div>
                <div class="span4"><img style="margin-top: 40px;" src="https://www.acyba.com/images/main_features_acysms/acysms1.png" alt=""></div>
            </div>
        </div>
        <div class="greybg">
            <div class="contentsize shadowright">
                <div class="row-fluid">
                    <div class="span4"><img src="https://www.acyba.com/images/main_features_acysms/acysms2.png" alt=""></div>
                    <div class="span8">
                        <h4>Increase your sales thanks to sms</h4>
                        <ul>
                            <li>Send <strong>coupons and special offers</strong> via SMS to your customers.</li>
                            <li>Generate automatic messages for their orders ("Your order is shipped today"). <strong>Send reminders</strong> when the order is confirmed or shipped.</li>
                            <li>Combine AcySMS with <strong>your online store</strong>. AcySMS is integrated with the main e-commerce solutions for Joomla (Virtuemart, HikaShop, RedShop, MijoShop).</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="contentsize shadowleft">
            <div class="row-fluid">
                <div class="span8">
                    <h4>GET STATISTICS ON EACH CAMPAIGN</h4>
                    <ul>
                        <li>Analyze the success of your campaigns, thanks to <strong>powerful statistics</strong>.&nbsp;</li>
                        <li>Check <strong>how many messages were sent</strong> and how many have failed. Get a detailed error if your message has not been sent.</li>
                        <li>AcySMS handles <strong>delivery reports</strong>, so that you can check who has received your message.</li>
                    </ul>
                </div>
                <div class="span4"><img src="https://www.acyba.com/images/main_features_acysms/acysms3.png" alt=""></div>
            </div>
        </div>
        <div class="greybg">
            <div class="contentsize shadowright">
                <div class="row-fluid">
                    <div class="span4"><img src="https://www.acyba.com/images/main_features_acysms/acysms4.png" alt=""></div>
                    <div class="span8">
                        <h4>PERFORM ACTIONS DEPENDING ON THE ANSWERS</h4>
                        <ul>
                            <li><strong>Unsubscribe users</strong> automatically from your lists ("STOP" word).</li>
                            <li>Send a specific message <strong>depending on the answer</strong> you received on your first SMS/Text Message.</li>
                            <li>As an option, you can even <strong>forward the SMS answer</strong> to the administrator.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="contentsize shadowleft">
            <div class="row-fluid">
                <div class="span8">
                    <h4>MANAGE AND ORGANIZE YOUR CONTACTS</h4>
                    <ul>
                        <li>Create new <strong>contacts</strong> or complete the current ones by adding <strong>custom fields</strong> to their profile.</li>
                        <li><strong>Add users</strong> directly inside AcySMS or <strong>use a user list</strong> that you already have in another component.</li>
                        <li>AcySMS is <strong>integrated with the main user management and e-commerce </strong><strong>extensions </strong> (AcyMailing, CB, JoomSocial, VM, HikaShop, RedShop, MijoShop)</li>
                    </ul>
                </div>
                <div class="span4"><img src="https://www.acyba.com/images/main_features_acysms/acysms5.png" alt=""></div>
            </div>
        </div>
        <div class="greybg">
            <div class="contentsize shadowright">
                <div class="row-fluid">
                    <div class="span4"><img src="https://www.acyba.com/images/main_features_acysms/acysms6.png" alt=""></div>
                    <div class="span8">
                        <h4>CHOOSE AMONG MANY GATEWAYS</h4>
                        <ul>
                            <li>More than 40 SMS providers available, so that you can find the <strong>best price</strong>.</li>
                            <li>Choose your favorite gateway and send <strong>SMS/</strong><strong>Text Messaging campaigns worldwide</strong>.</li>
                            <li><strong>No commitment</strong>. You only pay for what you use.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
