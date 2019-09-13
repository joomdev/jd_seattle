<?php
/**
 * @package	AcyMailing for Joomla
 * @version	6.2.2
 * @author	acyba.com
 * @copyright	(C) 2009-2019 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><?php

class DashboardController extends acymController
{
    public function __construct()
    {
        parent::__construct();

        $this->loadScripts = [
            'all' => ['colorpicker', 'datepicker', 'thumbnail', 'foundation-email', 'parse-css'],
        ];
    }

    public function listing()
    {
        acym_setVar('layout', 'listing');
        $config = acym_config();

        if ($config->get('migration') == 0 && acym_existsAcyMailing59()) {

            acym_setVar("layout", "migrate");

            parent::display();

            return;
        }

        if (ACYM_CMS === 'wordpress') {
            $installDate = $config->get('install_date', time());
            $remindme = json_decode($config->get('remindme', '[]'));

            if ($installDate < time() - 1814400 && !in_array('reviews', $remindme)) {
                $this->feedback();

                return true;
            }
        }

        $newConfig = new stdClass();

        $newConfig->migration = '1';
        $config->save($newConfig);

        if ($config->get('walk_through') == 1) {
            $walkthroughParams = json_decode($config->get('walkthrough_params', '[]'), true);
            if (!empty($walkthroughParams['step'])) {
                $this->{$walkthroughParams['step']}();
            } else {
                $this->stepSubscribe();
            }

            return;
        }

        $data = [];
        $campaignClass = acym_get('class.campaign');
        $mailStatsClass = acym_get('class.mailstat');
        $urlClickClass = acym_get('class.urlclick');
        $mails = $mailStatsClass->getAllMailsForStats();
        $data['campaignsScheduled'] = $campaignClass->getCampaignForDashboard();
        $data['dashboard_stats'] = true;

        if (empty($mails)) {
            $data['emptyGlobal'] = 'campaigns';
            parent::display($data);

            return;
        }

        $data['mails'] = [];

        foreach ($mails as $mail) {
            if (empty($mail->name) || (empty($mail->id) && $mail->sent != 1)) continue;

            $newMail = new stdClass();
            $newMail->name = $mail->name;
            $newMail->value = $mail->id;
            $data['mails'][] = $newMail;
        }

        $data['selectedMailid'] = empty($selectedMail) ? '' : $selectedMail;

        $statsMailSelected = $mailStatsClass->getOneByMailId($data['selectedMailid']);

        if (empty($statsMailSelected)) {
            $data['emptyGlobal'] = empty($data['selectedMailid']) ? 'campaigns' : 'stats';
        }

        if (empty($statsMailSelected->sent)) {
            $data['emptyGlobal'] = 'stats';
        }

        $statsMailSelected->totalMail = $statsMailSelected->sent + $statsMailSelected->fail;
        $statsMailSelected->pourcentageSent = empty($statsMailSelected->totalMail) ? 0 : intval(($statsMailSelected->sent * 100) / $statsMailSelected->totalMail);
        $statsMailSelected->allSent = empty($statsMailSelected->totalMail) ? acym_translation_sprintf('ACYM_X_MAIL_SUCCESSFULLY_SENT_OF_X', 0, 0) : acym_translation_sprintf('ACYM_X_MAIL_SUCCESSFULLY_SENT_OF_X', $statsMailSelected->sent, $statsMailSelected->totalMail);

        $openRateCampaign = empty($data['selectedMailid']) ? $campaignClass->getOpenRateAllCampaign() : $campaignClass->getOpenRateOneCampaign($data['selectedMailid']);
        $statsMailSelected->pourcentageOpen = empty($openRateCampaign->sent) ? 0 : intval(($openRateCampaign->open_unique * 100) / $openRateCampaign->sent);
        $statsMailSelected->allOpen = empty($openRateCampaign->sent) ? acym_translation_sprintf('ACYM_X_MAIL_OPENED_OF_X', 0, 0) : acym_translation_sprintf('ACYM_X_MAIL_OPENED_OF_X', $openRateCampaign->open_unique, $openRateCampaign->sent);

        $clickRateCampaign = $urlClickClass->getClickRate($data['selectedMailid']);
        $statsMailSelected->pourcentageClick = empty($statsMailSelected->sent) ? 0 : intval(($clickRateCampaign->click * 100) / $statsMailSelected->sent);
        $statsMailSelected->allClick = empty($statsMailSelected->sent) ? acym_translation_sprintf('ACYM_X_MAIL_CLICKED_OF_X', 0, 0) : acym_translation_sprintf('ACYM_X_MAIL_CLICKED_OF_X', $clickRateCampaign->click, $statsMailSelected->sent);

        $bounceRateCampaign = empty($data['selectedMailid']) ? $campaignClass->getBounceRateAllCampaign() : $campaignClass->getBounceRateOneCampaign($data['selectedMailid']);
        $statsMailSelected->pourcentageBounce = empty($statsMailSelected->sent) ? 0 : intval(($bounceRateCampaign->bounce_unique * 100) / $statsMailSelected->sent);
        $statsMailSelected->allBounce = empty($statsMailSelected->sent) ? acym_translation_sprintf('ACYM_X_BOUNCE_OF_X', 0, 0) : acym_translation_sprintf('ACYM_X_BOUNCE_OF_X', $bounceRateCampaign->bounce_unique, $statsMailSelected->sent);


        $campaignOpenByMonth = $campaignClass->getOpenByMonth($data['selectedMailid']);
        $campaignOpenByDay = $campaignClass->getOpenByDay($data['selectedMailid']);
        $campaignOpenByHour = $campaignClass->getOpenByHour($data['selectedMailid']);

        $campaignClickByMonth = $urlClickClass->getAllClickByMailMonth($data['selectedMailid']);
        $campaignClickByDay = $urlClickClass->getAllClickByMailDay($data['selectedMailid']);
        $campaignClickByHour = $urlClickClass->getAllClickByMailHour($data['selectedMailid']);

        if (empty($campaignOpenByMonth) || empty($campaignOpenByDay) || empty($campaignOpenByHour)) {
            $statsMailSelected->empty = true;
            $data['stats_mail_1'] = $statsMailSelected;

            parent::display($data);

            return;
        }

        #To get all the month between the first open date and the last
        $begin = new DateTime(empty($campaignClickByMonth) ? $campaignOpenByMonth[0]->open_date : min([$campaignOpenByMonth[0]->open_date, $campaignClickByMonth[0]->date_click]));
        $end = new DateTime(empty($campaignClickByMonth) ? end($campaignOpenByMonth)->open_date : max([end($campaignOpenByMonth)->open_date, end($campaignClickByMonth)->date_click]));

        $end->modify('+1 day');

        $interval = new DateInterval('P1M');
        $daterange = new DatePeriod($begin, $interval, $end);

        $rangeMonth = [];

        foreach ($daterange as $date) {
            $rangeMonth[] = acym_getTime($date->format('Y-m-d H:i:s'));
        }

        #To get all the day between the first open date and the last
        $begin = new DateTime(empty($campaignClickByDay) ? $campaignOpenByDay[0]->open_date : min([$campaignOpenByDay[0]->open_date, $campaignClickByDay[0]->date_click]));
        $end = new DateTime(empty($campaignClickByDay) ? end($campaignOpenByDay)->open_date : max([end($campaignOpenByDay)->open_date, end($campaignClickByDay)->date_click]));

        $end->modify('+1 hour');

        $interval = new DateInterval('P1D');
        $daterange = new DatePeriod($begin, $interval, $end);

        $rangeDay = [];

        foreach ($daterange as $date) {
            $rangeDay[] = acym_getTime($date->format('Y-m-d H:i:s'));
        }


        #To get all the hour between the first open date and the last
        $begin = new DateTime(empty($campaignClickByHour) ? $campaignOpenByHour[0]->open_date : min([$campaignOpenByHour[0]->open_date, $campaignClickByHour[0]->date_click]));
        $end = new DateTime(empty($campaignClickByHour) ? end($campaignOpenByHour)->open_date : max([end($campaignOpenByHour)->open_date, end($campaignClickByHour)->date_click]));

        $end->modify('+1 min');

        $interval = new DateInterval('PT1H');
        $daterange = new DatePeriod($begin, $interval, $end);

        $rangeHour = [];

        foreach ($daterange as $date) {
            $rangeHour[] = acym_getTime($date->format('Y-m-d H:i:s'));
        }

        $openMonthArray = [];
        $openDayArray = [];
        $openHourArray = [];

        foreach ($campaignOpenByMonth as $one) {
            $openMonthArray[acym_date(acym_getTime($one->open_date), 'M Y')] = $one->open;
        }

        foreach ($campaignOpenByDay as $one) {
            $openDayArray[acym_date(acym_getTime($one->open_date), 'd M Y')] = $one->open;
        }

        foreach ($campaignOpenByHour as $one) {
            $openHourArray[acym_date(acym_getTime($one->open_date), 'd M Y H')] = $one->open;
        }

        $clickMonthArray = [];
        $clickDayArray = [];
        $clickHourArray = [];

        foreach ($campaignClickByMonth as $one) {
            $clickMonthArray[acym_date(acym_getTime($one->date_click), 'M Y')] = $one->click;
        }

        foreach ($campaignClickByDay as $one) {
            $clickDayArray[acym_date(acym_getTime($one->date_click), 'd M Y')] = $one->click;
        }

        foreach ($campaignClickByHour as $one) {
            $clickHourArray[acym_date(acym_getTime($one->date_click), 'd M Y H')] = $one->click;
        }

        $statsMailSelected->month = [];
        foreach ($rangeMonth as $one) {
            $one = acym_date($one, 'M Y');
            $currentMonth = [];
            $currentMonth['open'] = empty($openMonthArray[$one]) ? 0 : $openMonthArray[$one];
            $currentMonth['click'] = empty($clickMonthArray[$one]) ? 0 : $clickMonthArray[$one];
            $statsMailSelected->month[$one] = $currentMonth;
        }

        $statsMailSelected->day = [];
        foreach ($rangeDay as $one) {
            $one = acym_date($one, 'd M Y');
            $currentDay = [];
            $currentDay['open'] = empty($openDayArray[$one]) ? 0 : $openDayArray[$one];
            $currentDay['click'] = empty($clickDayArray[$one]) ? 0 : $clickDayArray[$one];
            $statsMailSelected->day[$one] = $currentDay;
        }

        $statsMailSelected->hour = [];
        foreach ($rangeHour as $one) {
            $one = acym_date($one, 'd M Y H');
            $currentHour = [];
            $currentHour['open'] = empty($openHourArray[$one]) ? 0 : $openHourArray[$one];
            $currentHour['click'] = empty($clickHourArray[$one]) ? 0 : $clickHourArray[$one];
            $statsMailSelected->hour[$one.':00'] = $currentHour;
        }
        $data['stats_mail_1'] = $statsMailSelected;

        parent::display($data);
    }

    public function stepSubscribe()
    {
        acym_setVar('layout', 'walk_through');

        $data = [
            'step' => 'subscribe',
            'email' => acym_currentUserEmail(),
        ];

        parent::display($data);
    }

    public function saveStepSubscribe()
    {
        $this->_saveWalkthrough(['step' => 'stepEmail']);
        $this->stepEmail();
    }

    public function stepEmail()
    {
        acym_setVar('layout', 'walk_through');

        $config = acym_config();

        $walkthroughParams = json_decode($config->get('walkthrough_params', '[]'), true);

        $mailClass = acym_get('class.mail');
        $updateHelper = acym_get('helper.update');

        $mail = empty($walkthroughParams['mail_id']) ? $mailClass->getOneByName(acym_translation($updateHelper::FIRST_EMAIL_NAME_KEY)) : $mailClass->getOneById($walkthroughParams['mail_id']);

        if (empty($mail)) {
            $updateHelper = acym_get('helper.update');
            if (!$updateHelper->installNotifications()) {
                $this->stepSubscribe();

                return;
            }
            $mail = $mailClass->getOneByName(acym_translation($updateHelper::FIRST_EMAIL_NAME_KEY));
        }

        $editor = acym_get('helper.editor');
        $editor->content = $mail->body;
        $editor->autoSave = '';
        $editor->settings = $mail->settings;
        $editor->stylesheet = $mail->stylesheet;
        $editor->editor = 'acyEditor';
        $editor->mailId = $mail->id;
        $editor->walkThrough = true;

        $data = [
            'step' => 'email',
            'editor' => $editor,
            'social_icons' => $config->get('social_icons', '{}'),
            'mail' => $mail,
        ];

        parent::display($data);
    }

    public function saveAjax()
    {
        $mailController = acym_get('controller.mails');

        $isWellSaved = $mailController->store(true);
        echo json_encode(['error' => $isWellSaved ? '' : acym_translation('ACYM_ERROR_SAVING'), 'data' => $isWellSaved]);
        exit;
    }

    public function saveStepEmail()
    {
        $mailController = acym_get('controller.mails');

        $mailId = $mailController->store();

        if (empty($mailId)) {
            acym_enqueueNotification(acym_translation('ACYM_ERROR_SAVING'), 'error', 10000);
            $this->passWalkThrough();
        } else {
            $this->_saveWalkthrough(['step' => 'stepList', 'mail_id' => $mailId]);
            $this->stepList();
        }
    }

    public function stepList()
    {
        acym_setVar('layout', 'walk_through');
        $listClass = acym_get('class.list');
        $config = acym_config();
        $walkthroughParams = json_decode($config->get('walkthrough_params', '[]'), true);

        $users = empty($walkthroughParams['list_id']) ? [] : $listClass->getSubscribersByListId($walkthroughParams['list_id']);
        $usersReturn = [];
        if (!empty($users)) {
            foreach ($users as $user) {
                $usersReturn[] = $user->email;
            }
        }

        if (empty($usersReturn)) $usersReturn[] = acym_currentUserEmail();

        $data = [
            'step' => 'list',
            'users' => $usersReturn,
        ];

        parent::display($data);
    }

    public function saveStepList()
    {
        $config = acym_config();
        $walkthroughParams = json_decode($config->get('walkthrough_params', '[]'), true);
        if (empty($walkthroughParams['list_id'])) {
            $testingList = new stdClass();
            $testingList->name = acym_translation('ACYM_TESTING_LIST');
            $testingList->visible = 0;
            $testingList->active = 1;
            $testingList->color = '#94d4a6';

            $listClass = acym_get('class.list');
            $listId = $listClass->save($testingList);
        } else {
            $listId = $walkthroughParams['list_id'];
        }

        if (empty($listId)) {
            acym_enqueueNotification(acym_translation('ACYM_ERROR_SAVE_LIST'), 'error', 5000);
            $this->passWalkThrough();

            return;
        }

        $userClass = acym_get('class.user');

        $addresses = acym_getVar('array', 'addresses', []);
        $addresses = array_unique($addresses);
        $wrongAddresses = [];
        foreach ($addresses as $oneAddress) {
            if (!acym_isValidEmail($oneAddress)) {
                $wrongAddresses[] = $oneAddress;
                continue;
            }

            $existing = $userClass->getOneByEmail($oneAddress);
            if (empty($existing)) {
                $newUser = new stdClass();
                $newUser->email = $oneAddress;
                $newUser->confirmed = 1;

                $userId = $userClass->save($newUser);
            } else {
                $userId = $existing->id;
            }

            $userClass->subscribe($userId, $listId);
        }

        if (!empty($wrongAddresses)) acym_enqueueNotification(acym_translation_sprintf('ACYM_WRONG_ADDRESSES', implode(', ', $wrongAddresses)), 'warning', 5000);

        $nextStep = acym_isLocalWebsite() ? 'stepGmail' : 'stepPhpmail';

        $this->_saveWalkthrough(['step' => $nextStep, 'list_id' => $listId]);
        $this->$nextStep();
    }

    public function stepPhpmail()
    {
        acym_setVar('layout', 'walk_through');

        $data = [
            'step' => 'phpmail',
            'userEmail' => acym_currentUserEmail(),
        ];

        parent::display($data);
    }

    public function saveStepPhpmail()
    {
        $config = acym_config();


        if (!$this->_saveFrom()) {
            $this->stepPhpmail();

            return;
        }

        $mailerMethod = ['mailer_method' => 'phpmail'];
        if (false === $config->save($mailerMethod)) {
            acym_enqueueMessage(acym_translation('ACYM_ERROR_SAVING', 'error'));
            $this->stepPhpmail();

            return;
        }

        if (false === $this->_sendFirstEmail()) {
            $this->stepPhpmail();

            return;
        }

        $this->_saveWalkthrough(['step' => 'stepResult']);
        $this->stepResult();
    }

    public function stepGmail()
    {
        acym_setVar('layout', 'walk_through');

        $data = [
            'step' => 'gmail',
            'userEmail' => acym_currentUserEmail(),
        ];

        parent::display($data);
    }

    public function saveStepGmail()
    {
        $config = acym_config();

        if (!$this->_saveFrom() || !$this->_saveGmailInformation()) {
            $this->stepGmail();

            return;
        }

        $this->_sendFirstEmail();

        $this->_saveWalkthrough(['step' => 'stepResult']);
        $this->stepResult();
    }

    public function stepResult()
    {
        acym_setVar('layout', 'walk_through');

        $data = [
            'step' => 'result',
        ];

        parent::display($data);
    }

    public function saveStepResult()
    {
        $config = acym_config();
        $result = acym_getVar('boolean', 'result');

        $walkthroughParams = json_decode($config->get('walkthrough_params', '[]'), true);

        $stepFail = acym_isLocalWebsite() || !empty($walkthroughParams['step_fail']) ? 'stepFaillocal' : 'stepFail';

        $nextStep = $result ? 'stepSuccess' : $stepFail;
        $this->_saveWalkthrough(['step' => $nextStep]);

        $this->$nextStep();
    }

    public function stepSuccess()
    {
        acym_setVar('layout', 'walk_through');

        $data = [
            'step' => 'success',
        ];

        parent::display($data);
    }

    public function saveStepSuccess()
    {
        $this->passWalkThrough();
    }

    public function stepFaillocal()
    {
        acym_setVar('layout', 'walk_through');
        $data = [
            'step' => 'faillocal',
            'email' => acym_currentUserEmail(),
        ];
        parent::display($data);
    }

    public function saveStepFaillocal()
    {
        $this->_handleContactMe('stepFaillocal');
    }

    public function stepFail()
    {
        acym_setVar('layout', 'walk_through');

        $data = [
            'step' => 'fail',
            'email' => acym_currentUserEmail(),
        ];

        parent::display($data);
    }

    public function saveStepFail()
    {
        $choice = acym_getVar('cmd', 'choice', 'gmail');
        if ('gmail' === $choice) {
            $this->_saveGmailInformation();
            $this->_sendFirstEmail();
            $this->_saveWalkthrough(['step' => 'stepResult', 'step_fail' => true]);
            $this->stepResult();
        } else {
            $this->_handleContactMe('stepFail');
        }
    }

    private function _handleContactMe($fromFunction)
    {
        $email = acym_getVar('string', 'email');
        if (empty($email) || !acym_isValidEmail($email)) {
            acym_enqueueNotification(acym_translation('ACYM_PLEASE_ADD_YOUR_EMAIL'), 'error', 10000);
            $this->$fromFunction();

            return;
        }

        $config = acym_config();

        $handle = curl_init();
        $url = ACYM_UPDATEMEURL.'contact&task=contactme&email='.urlencode($email).'&version='.$config->get('version', '6').'&cms='.ACYM_CMS;
        curl_setopt($handle, CURLOPT_URL, $url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($handle);
        curl_close($handle);
        $output = json_decode($output, true);
        if (!empty($output['error'])) {
            acym_enqueueMessage(acym_translation('ACYM_SOMETHING_WENT_WRONG_CONTACT_ON_ACYBA'), 'error');
            $this->passWalkThrough();
        } else {
            $this->_saveWalkthrough(['step' => 'stepSupport']);
            $this->stepSupport();
        }
    }

    public function stepSupport()
    {
        acym_setVar('layout', 'walk_through');

        $data = [
            'step' => 'support',
        ];

        parent::display($data);
    }

    public function saveStepSupport()
    {
        $this->passWalkThrough();
    }

    public function passWalkThrough()
    {
        $config = acym_config();
        $newConfig = new stdClass();
        $newConfig->walk_through = 0;

        if ($config->get('templates_installed') == 0) {
            $updateHelper = acym_get('helper.update');
            $updateHelper->installTemplate();
            $newConfig->templates_installed = 1;
        }
        $config->save($newConfig);

        acym_redirect(acym_completeLink('users&task=import', false, true));

        return;
    }

    public function preMigration()
    {
        $elementToMigrate = acym_getVar("string", "element");
        $helperMigration = acym_get('helper.migration');

        $result = $helperMigration->preMigration($elementToMigrate);

        if (!empty($result["isOk"])) {
            echo $result["count"];
        } else {
            echo "ERROR : ";
            if (!empty($result["errorInsert"])) {
                echo strtoupper(acym_translation("ACYM_INSERT_ERROR"));
            }
            if (!empty($result["errorClean"])) {
                echo strtoupper(acym_translation("ACYM_CLEAN_ERROR"));
            }

            if (!empty($result["errors"])) {
                echo "<br>";

                foreach ($result["errors"] as $key => $oneError) {
                    echo "<br>".$key." : ".$oneError;
                }
            }
        }
        exit;
    }

    public function migrate()
    {
        $elementToMigrate = acym_getVar("string", "element");
        $helperMigration = acym_get('helper.migration');
        $functionName = "do".ucfirst($elementToMigrate)."Migration";

        $result = $helperMigration->$functionName($elementToMigrate);

        if (!empty($result["isOk"])) {
            echo json_encode($result);
        } else {
            echo "ERROR : ";
            if (!empty($result["errorInsert"])) {
                echo strtoupper(acym_translation("ACYM_INSERT_ERROR"));
            }
            if (!empty($result["errorClean"])) {
                echo strtoupper(acym_translation("ACYM_CLEAN_ERROR"));
            }

            if (!empty($result["errors"])) {
                echo "<br>";

                foreach ($result["errors"] as $key => $oneError) {
                    echo "<br>".$key." : ".$oneError;
                }
            }
        }
        exit;
    }

    public function migrationDone()
    {
        $config = acym_config();

        $newConfig = new stdClass();
        $newConfig->migration = "1";
        $config->save($newConfig);

        $updateHelper = acym_get('helper.update');
        $updateHelper->installNotifications();

        $this->listing();
    }

    private function acym_existsAcyMailing59()
    {
        $allTables = acym_getTables();

        if (in_array(acym_getPrefix().'acymailing_config', $allTables)) {
            $queryVersion = 'SELECT `value` FROM #__acymailing_config WHERE `namekey` LIKE "version"';

            $version = acym_loadResult($queryVersion);

            if (version_compare($version, '5.9.0') >= 0) {
                return true;
            }
        }

        return false;
    }

    public function upgrade()
    {
        acym_setVar('layout', 'upgrade');

        $version = acym_getVar('string', 'version', 'enterprise');

        $data = ['version' => $version];

        parent::display($data);
    }

    public function feedback()
    {
        acym_setVar('layout', 'feedback');

        parent::display();

        return;
    }

    private function _saveFrom()
    {
        $fromName = acym_getVar('string', 'from_name', 'Test');
        $fromAddress = acym_getVar('string', 'from_address', 'test@test.com');

        $mailClass = acym_get('class.mail');
        $updateHelper = acym_get('helper.update');

        $firstMail = $mailClass->getOneByName(acym_translation($updateHelper::FIRST_EMAIL_NAME_KEY));

        if (empty($firstMail)) {
            acym_enqueueNotification(acym_translation('ACYM_PLEASE_REINSTALL_ACYMAILING'), 'error');

            return false;
        }

        $firstMail->from_name = $fromName;
        $firstMail->from_email = $fromAddress;

        $statusSaveMail = $mailClass->save($firstMail);

        if (empty($statusSaveMail)) {
            acym_enqueueNotification(acym_translation('ACYM_ERROR_SAVING'), 'error');

            return false;
        }

        return true;
    }

    private function _saveGmailInformation()
    {
        $gmailAddress = acym_getVar('string', 'gmail_address', '');
        $gmailPassword = acym_getVar('string', 'gmail_password', '');

        $config = acym_config();

        if (empty($gmailAddress) || empty($gmailPassword)) {
            acym_enqueueMessage(acym_translation('ACYM_EMPTY_ADDRESS_OR_PASSWORD'), 'error');

            return false;
        }

        $newSmtpConfiguration = [
            'smtp_auth' => '1',
            'smtp_host' => 'smtp.gmail.com',
            'smtp_keepalive' => '1',
            'smtp_port' => '465',
            'smtp_secured' => 'ssl',
            'smtp_username' => $gmailAddress,
            'smtp_password' => $gmailPassword,
            'mailer_method' => 'smtp',
        ];

        if (false === $config->save($newSmtpConfiguration)) {
            acym_enqueueMessage(acym_translation('ACYM_ERROR_SAVING', 'error'));

            return false;
        }

        return true;
    }

    private function _sendFirstEmail()
    {
        $config = acym_config();
        $walkthroughParams = json_decode($config->get('walkthrough_params', '[]'), true);
        $listClass = acym_get('class.list');
        $mailClass = acym_get('class.mail');
        $updateHelper = acym_get('helper.update');
        $mailerHelper = acym_get('helper.mailer');

        $testingList = empty($walkthroughParams['list_id']) ? $listClass->getOneByName(acym_translation('ACYM_TESTING_LIST')) : $listClass->getOneById($walkthroughParams['list_id']);
        $firstMail = empty($walkthroughParams['mail_id']) ? $mailClass->getOneByName(acym_translation($updateHelper::FIRST_EMAIL_NAME_KEY)) : $mailClass->getOneById($walkthroughParams['mail_id']);

        if (empty($testingList)) {
            acym_enqueueNotification(acym_translation('ACYM_CANT_RETRIEVE_TESTING_LIST'), 'error');

            return false;
        }

        if (empty($firstMail)) {
            acym_enqueueNotification(acym_translation('ACYM_CANT_RETRIEVE_TEST_EMAIL'), 'error');
        }

        $subscribersTestingListIds = $listClass->getSubscribersIdsById($testingList->id);

        $nbSent = 0;
        foreach ($subscribersTestingListIds as $subscriberId) {
            if ($mailerHelper->sendOne($firstMail->id, $subscriberId, true)) $nbSent++;
        }

        if ($nbSent === 0) {
            return false;
        }

        return true;
    }

    private function _saveWalkthrough($params)
    {
        $config = acym_config();

        $newParams = json_decode($config->get('walkthrough_params', '[]'), true);
        foreach ($params as $key => $value) {
            $newParams[$key] = $value;
        }
        $config->save(['walkthrough_params' => json_encode($newParams)]);
    }
}

