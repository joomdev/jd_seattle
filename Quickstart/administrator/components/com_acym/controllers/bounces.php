<?php
/**
 * @package	AcyMailing for Joomla
 * @version	6.1.2
 * @author	acyba.com
 * @copyright	(C) 2009-2019 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><?php

class BouncesController extends acymController
{

    public function __construct()
    {
        parent::__construct();
        $this->breadcrumb[acym_translation('ACYM_BOUNCE_HANDLING')] = acym_completeLink('bounces');
    }

    function listing()
    {
        if (acym_level(2)) {
            acym_setVar("layout", "listing");
            $bounceClass = acym_get('class.rule');
            $listsClass = acym_get('class.list');

            $bounceRules = $bounceClass->getAll();

            $data = array(
                "allRules" => $bounceRules,
                "lists" => $listsClass->getAllWithIdName(),
            );

            parent::display($data);
        }

        if (!acym_level(2)) {
            acym_redirect(acym_completeLink('dashboard&task=upgrade&version=enterprise', false, true));
        }
    }


    function edit()
    {
        $ruleClass = acym_get('class.rule');
        acym_setVar("layout", "edit");
        $ruleId = acym_getVar("int", "id", 0);
        $listsClass = acym_get('class.list');

        $rule = "";

        if (!empty($ruleId)) {
            $rule = $ruleClass->getOneById($ruleId);
            $this->breadcrumb[acym_translation($rule->name)] = acym_completeLink('bounces&task=edit&id='.$ruleId);
        }else{
            $this->breadcrumb[acym_translation('ACYM_NEW')] = acym_completeLink('bounces&task=edit');
        }

        $data = array(
            "id" => $ruleId,
            "lists" => $listsClass->getAllWithIdName(),
            "rule" => $rule,
        );

        parent::display($data);
    }

    public function apply()
    {
        $this->saveRule();
        acym_setVar("id", acym_getVar('array', 'bounce')['id']);
        $this->edit();


        return;
    }

    public function save()
    {
        $this->saveRule();
        $this->listing();

        return;
    }

    public function saveRule()
    {
        $rule = acym_getVar('array', 'bounce');

        $ruleClass = acym_get('class.rule');
        $bounceClass = acym_get('class.bounce');

        $rule['executed_on'] = !empty($rule['executed_on']) ? json_encode($rule['executed_on']) : '[]';

        if (!empty($rule['action_user'])) {
            if (in_array('subscribe_user', $rule['action_user'])) {
                $rule['action_user']['subscribe_user_list'] = $rule['subscribe_user_list'];
            }
        }
        unset($rule['subscribe_user_list']);

        if (!empty($rule['action_message']) && !in_array('forward_message', $rule['action_message'])) {
            unset($rule['action_message']['forward_to']);
        }

        if (empty($rule['id'])) {
            $rule['ordering'] = $bounceClass->getOrderingNumber() + 1;
        }

        $ruleObject = new stdClass();
        $ruleObject->executed_on = '[]';
        $ruleObject->action_message = '[]';
        $ruleObject->action_user = '[]';

        foreach ($rule as $column => $value) {
            acym_secureDBColumn($column);
            if (is_array($value) || is_object($value)) {
                $ruleObject->$column = json_encode($value);
            } else {
                $ruleObject->$column = strip_tags($value);
            }
        }

        $res = $ruleClass->save($ruleObject);

        if (!$res) {
            acym_enqueueNotification(acym_translation('ACYM_ERROR_SAVING'), 'error', 5000);
        } else {
            acym_enqueueNotification(acym_translation('ACYM_SUCCESSFULLY_SAVED'), 'success', 5000);
        }
    }

    public function setOrdering()
    {
        $order = json_decode(acym_getVar('string', 'order'));
        $i = 1;
        $error = false;
        foreach ($order as $rule) {
            $query = 'UPDATE #__acym_rule SET `ordering`='.intval($i).' WHERE `id`='.intval($rule);
            $error = acym_query($query) >= 0 ? false : true;
            $i++;
        }
        if ($error) {
            echo 'error';
        } else {
            echo 'updated';
        }
        exit;
    }

    function process()
    {
        acym_increasePerf();

        $config = acym_config();
        $bounceClass = acym_get('helper.bounce');
        $bounceClass->report = true;
        if (!$bounceClass->init()) {
            return;
        }
        if (!$bounceClass->connect()) {
            acym_display($bounceClass->getErrors(), 'error');

            return;
        }
        $disp = "<html>\n<head>\n<meta http-equiv=\"Content-Type\" content=\"text/html;charset=utf-8\" />\n";
        $disp .= '<title>'.addslashes(acym_translation('ACYM_BOUNCE_PROCESS')).'</title>'."\n";
        $disp .= "<style>body{font-size:12px;font-family: Arial,Helvetica,sans-serif;padding-top:30px;}</style>\n</head>\n<body>";
        echo $disp;

        acym_display(acym_translation_sprintf('ACYM_BOUNCE_CONNECT_SUCC', $config->get('bounce_username')), 'success');
        $nbMessages = $bounceClass->getNBMessages();
        acym_display(acym_translation_sprintf('ACYM_NB_MAIL_MAILBOX', $nbMessages), 'info');

        if (empty($nbMessages)) {
            exit;
        }

        $bounceClass->handleMessages();
        $bounceClass->close();

        $cronHelper = acym_get('helper.cron');
        $cronHelper->messages[] = acym_translation_sprintf('ACYM_NB_MAIL_MAILBOX', $nbMessages);
        $cronHelper->detailMessages = $bounceClass->messages;
        $cronHelper->saveReport();

        if ($config->get('bounce_max', 0) != 0 && $nbMessages > $config->get('bounce_max', 0)) {
            $url = acym_completeLink('bounces&task=process&continuebounce=1', true, true);
            if (acym_getVar('int', 'continuebounce')) {
                echo '<script type="text/javascript" language="javascript">document.location.href=\''.$url.'\';</script>';
            } else {
                echo '<div style="padding:20px;"><a href="'.$url.'">'.acym_translation('ACYM_CLICK_HANDLE_ALL_BOUNCES').'</a></div>';
            }
        }

        echo "</body></html>";
        while ($bounceClass->obend-- > 0) {
            ob_start();
        }
        exit;
    }

    function saveconfig()
    {
        $this->_saveconfig();

        return $this->listing();
    }

    function _saveconfig()
    {
        acym_checkToken();

        $config = acym_config();
        $newConfig = acym_getVar('array', 'config', array(), 'POST');
        if (!empty($newConfig['bounce_username'])) {
            $newConfig['bounce_username'] = acym_punycode($newConfig['bounce_username']);
        }

        $newConfig['auto_bounce_next'] = min($config->get('auto_bounce_last', time()), time()) + $newConfig['auto_bounce_frequency'];

        $status = $config->save($newConfig);

        if ($status) {
            acym_enqueueNotification(acym_translation('ACYM_SUCCESSFULLY_SAVED'), 'message');
        } else {
            acym_enqueueNotification(acym_translation('ACYM_ERROR_SAVING'), 'error');
        }

        $config->load();
    }

    function chart()
    {
        acym_setVar('layout', 'chart');

        return parent::display();
    }

    function test()
    {

        $bounceClass = acym_get('class.bounce');

        if ($bounceClass->getOrderingNumber() < 1) {
            acym_enqueueNotification(acym_translation('ACYM_NO_RULES'), 'error', 5000);

            $this->listing();

            return;
        }

        acym_increasePerf();
        $config = acym_config();
        $bounceClass = acym_get('helper.bounce');
        $bounceClass->report = true;

        if ($bounceClass->init()) {
            if ($bounceClass->connect()) {
                $nbMessages = $bounceClass->getNBMessages();
                acym_enqueueNotification(acym_translation_sprintf('ACYM_BOUNCE_CONNECT_SUCC', $config->get('bounce_username')), "success", 5000);
                $bounceClass->close();
                if (!empty($nbMessages)) {
                    acym_enqueueNotification(
                        array(
                            acym_translation_sprintf('ACYM_NB_MAIL_MAILBOX', $nbMessages),
                            acym_modal(
                                acym_translation('ACYM_CLICK_BOUNCE'),
                                '',
                                null,
                                'data-reveal-larger',
                                'data-ajax="true" data-iframe="&ctrl=bounces&task=process" class="acym__color__light-blue cursor-pointer" style="margin: 0"'
                            ),
                        )
                    );
                }
            } else {
                $errors = $bounceClass->getErrors();
                if (!empty($errors)) {
                    acym_enqueueNotification($errors, 'error');
                    $errorString = implode(' ', $errors);
                    $port = $config->get('bounce_port', '');
                    if (preg_match('#certificate#i', $errorString) && !$config->get('bounce_certif', false)) {
                        acym_enqueueNotification('You may need to turn ON the option <i>'.acym_translation('ACYM_SELF_SIGNED_CERTIFICATE').'</i>', 'warning');
                    } elseif (!empty($port) AND !in_array($port, array('993', '143', '110'))) {
                        acym_enqueueNotification('Are you sure you selected the right port? You can leave it empty if you do not know what to specify', 'warning');
                    }
                }
            }
        }

        return $this->listing();
    }

    function reinstall()
    {
        $bounceClass = acym_get('class.bounce');
        $bounceClass->cleanTable();

        $updateHelper = acym_get('helper.update');
        $updateHelper->installBounceRules();

        return $this->listing();
    }

    public function config()
    {
        acym_redirect(acym_completeLink('configuration', false, true));
    }

    public function delete()
    {
        $rulesSelected = acym_getVar('array', 'elements_checked');

        $ruleClass = acym_get('class.rule');
        $ruleClass->delete($rulesSelected);

        $this->listing();
    }
}
