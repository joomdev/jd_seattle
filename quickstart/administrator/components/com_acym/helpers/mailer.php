<?php
defined('_JEXEC') or die('Restricted access');
?><?php

require_once ACYM_INC.'phpmailer'.DS.'exception.php';
require_once ACYM_INC.'phpmailer'.DS.'smtp.php';
require_once ACYM_INC.'phpmailer'.DS.'phpmailer.php';
require_once ACYM_INC.'emogrifier.php';

use acyPHPMailer\acyException;
use acyPHPMailer\acySMTP;
use acyPHPMailer\acyPHPMailer;
use acymEmogrifier\acymEmogrifier;

class acymmailerHelper extends acyPHPMailer
{
    var $encodingHelper;
    var $editorHelper;
    var $userClass;
    var $config;

    var $report = true;
    var $alreadyCheckedAddresses = false;
    var $errorNewTry = [1, 6];
    var $autoAddUser = false;
    var $reportMessage = '';

    var $trackEmail = false;

    public $From = '';
    public $FromName = '';
    public $SMTPAutoTLS = false;

    public $to = [];
    public $cc = [];
    public $bcc = [];
    public $ReplyTo = [];
    public $attachment = [];
    public $CustomHeader = [];

    public $stylesheet = '';
    public $settings;

    public $parameters = [];

    public function __construct()
    {
        parent::__construct();

        $this->encodingHelper = acym_get('helper.encoding');
        $this->editorHelper = acym_get('helper.editor');
        $this->userClass = acym_get('class.user');
        $this->config = acym_config();
        $this->setFrom($this->config->get('from_email'), $this->config->get('from_name'));
        $this->Sender = $this->cleanText($this->config->get('bounce_email'));
        if (empty($this->Sender)) {
            $this->Sender = '';
        }

        switch ($this->config->get('mailer_method', 'phpmail')) {
            case 'smtp' :
                $this->isSMTP();
                $this->Host = trim($this->config->get('smtp_host'));
                $port = $this->config->get('smtp_port');
                if (empty($port) && $this->config->get('smtp_secured') == 'ssl') {
                    $port = 465;
                }
                if (!empty($port)) {
                    $this->Host .= ':'.$port;
                }
                $this->SMTPAuth = (bool)$this->config->get('smtp_auth', true);
                $this->Username = trim($this->config->get('smtp_username'));
                $this->Password = trim($this->config->get('smtp_password'));
                $this->SMTPSecure = trim((string)$this->config->get('smtp_secured'));

                if (empty($this->Sender)) {
                    $this->Sender = strpos($this->Username, '@') ? $this->Username : $this->config->get('from_email');
                }
                break;
            case 'sendmail' :
                $this->isSendmail();
                $this->Sendmail = trim($this->config->get('sendmail_path'));
                if (empty($this->Sendmail)) {
                    $this->Sendmail = '/usr/sbin/sendmail';
                }
                break;
            case 'qmail' :
                $this->isQmail();
                break;
            case 'elasticemail' :
                $port = $this->config->get('elasticemail_port', 'rest');
                if (is_numeric($port)) {
                    $this->isSMTP();
                    if ($port == '25') {
                        $this->Host = 'smtp25.elasticemail.com:25';
                    } else {
                        $this->Host = 'smtp.elasticemail.com:2525';
                    }
                    $this->Username = trim($this->config->get('elasticemail_username'));
                    $this->Password = trim($this->config->get('elasticemail_password'));
                    $this->SMTPAuth = true;
                } else {
                    include_once(ACYM_INC.'phpmailer'.DS.'elasticemail.php');
                    $this->Mailer = 'elasticemail';
                    $this->{$this->Mailer} = new acyElasticemail();
                    $this->{$this->Mailer}->Username = trim($this->config->get('elasticemail_username'));
                    $this->{$this->Mailer}->Password = trim($this->config->get('elasticemail_password'));
                }

                break;
            default :
                $this->isMail();
                break;
        }

        if ($this->config->get('dkim', 0) && $this->Mailer != 'elasticemail') {
            $this->DKIM_domain = $this->config->get('dkim_domain');
            $this->DKIM_selector = $this->config->get('dkim_selector', 'acy');
            if (empty($this->DKIM_selector)) $this->DKIM_selector = 'acy';
            $this->DKIM_passphrase = $this->config->get('dkim_passphrase');
            $this->DKIM_identity = $this->config->get('dkim_identity');
            $this->DKIM_private = trim($this->config->get('dkim_private'));
            $this->DKIM_private_string = trim($this->config->get('dkim_private'));
        }

        $this->CharSet = strtolower($this->config->get('charset'));
        if (empty($this->CharSet)) {
            $this->CharSet = 'utf-8';
        }

        $this->clearAll();

        $this->Encoding = $this->config->get('encoding_format');
        if (empty($this->Encoding)) {
            $this->Encoding = '8bit';
        }

        @ini_set('pcre.backtrack_limit', 1000000);

        $this->SMTPOptions = ["ssl" => ["verify_peer" => false, "verify_peer_name" => false, "allow_self_signed" => true]];

        $this->addParamInfo();
    }

    protected function elasticemailSend($MIMEHeader, $MIMEBody)
    {
        $result = $this->elasticemail->sendMail($this);
        if (!$result) {
            $this->setError($this->elasticemail->error);
        }

        return $result;
    }

    public function send()
    {
        if (!file_exists(ACYM_INC.'phpmailer'.DS.'phpmailer.php')) {
            $this->reportMessage = acym_translation_sprintf('ACYM_X_FILE_MISSING', 'phpmailer', ACYM_INC.'phpmailer'.DS);
            if ($this->report) {
                acym_enqueueMessage($this->reportMessage, 'error');
            }

            return false;
        }

        if (empty($this->Subject) || empty($this->Body)) {
            $this->reportMessage = acym_translation('ACYM_SEND_EMPTY');
            $this->errorNumber = 8;
            if ($this->report) {
                acym_enqueueMessage($this->reportMessage, 'error');
            }

            return false;
        }

        if (empty($this->ReplyTo) && empty($this->ReplyToQueue)) {
            if (!empty($this->replyemail)) {
                $replyToEmail = $this->replyemail;
            } elseif ($this->config->get('from_as_replyto', 1) == 1) {
                $replyToEmail = $this->config->get('from_email');
            } else {
                $replyToEmail = $this->config->get('replyto_email');
            }

            if (!empty($this->replyname)) {
                $replyToName = $this->replyname;
            } elseif ($this->config->get('from_as_replyto', 1) == 1) {
                $replyToName = $this->config->get('from_name');
            } else {
                $replyToName = $this->config->get('replyto_name');
            }

            $this->_addReplyTo($replyToEmail, $replyToName);
        }

        if ((bool)$this->config->get('embed_images', 0) && $this->Mailer != 'elasticemail') {
            $this->embedImages();
        }

        if (!$this->alreadyCheckedAddresses) {
            $this->alreadyCheckedAddresses = true;

            $replyToTmp = '';
            if (!empty($this->ReplyTo)) {
                $replyToTmp = reset($this->ReplyTo);
                $replyToTmp = $replyToTmp[0];
            } elseif (!empty($this->ReplyToQueue)) {
                $replyToTmp = reset($this->ReplyToQueue);
                $replyToTmp = $replyToTmp[1];
            }

            if (empty($replyToTmp) || !acym_isValidEmail($replyToTmp)) {
                $this->reportMessage = acym_translation('ACYM_VALID_EMAIL').' ( '.acym_translation('ACYM_REPLYTO_EMAIL').' : '.(empty($this->ReplyTo) ? '' : $replyToTmp).' ) ';
                $this->errorNumber = 9;
                if ($this->report) {
                    acym_enqueueMessage($this->reportMessage, 'error');
                }

                return false;
            }

            if (empty($this->From) || !acym_isValidEmail($this->From)) {
                $this->reportMessage = acym_translation('ACYM_VALID_EMAIL').' ( '.acym_translation('ACYM_FROM_EMAIL').' : '.$this->From.' ) ';
                $this->errorNumber = 9;
                if ($this->report) {
                    acym_enqueueMessage($this->reportMessage, 'error');
                }

                return false;
            }

            if (!empty($this->Sender) && !acym_isValidEmail($this->Sender)) {
                $this->reportMessage = acym_translation('ACYM_VALID_EMAIL').' ( '.acym_translation('ACYM_BOUNCE_EMAIL').' : '.$this->Sender.' ) ';
                $this->errorNumber = 9;
                if ($this->report) {
                    acym_enqueueMessage($this->reportMessage, 'error');
                }

                return false;
            }
        }

        if (function_exists('mb_convert_encoding')) {
            $this->Body = mb_convert_encoding($this->Body, 'HTML-ENTITIES', 'UTF-8');
            $this->Body = str_replace(['&amp;', '&sigmaf;'], ['&', 'ς'], $this->Body);
        }

        if ($this->CharSet != 'utf-8') {
            $this->Body = $this->encodingHelper->change($this->Body, 'UTF-8', $this->CharSet);
            $this->Subject = $this->encodingHelper->change($this->Subject, 'UTF-8', $this->CharSet);
        }

        if (strpos($this->Host, 'elasticemail')) {
            $this->addCustomHeader('referral:2f0447bb-173a-459d-ab1a-ab8cbebb9aab');
        }

        $this->Subject = str_replace(
            ['’', '“', '”', '–'],
            ["'", '"', '"', '-'],
            $this->Subject
        );

        $this->Body = str_replace(" ", ' ', $this->Body);

        if ($this->ContentType != 'text/plain') {
            static $foundationCSS = null;
            $style = [];
            if (empty($foundationCSS)) {
                $foundationCSS = acym_fileGetContent(ACYM_MEDIA.'css'.DS.'libraries'.DS.'foundation_email.min.css');
                $foundationCSS = str_replace('#acym__wysid__template ', '', $foundationCSS);
            }

            if (strpos($this->Body, 'acym__wysid__template') !== false) $style['foundation'] = $foundationCSS;

            static $emailFixes = null;
            if (empty($emailFixes)) $emailFixes = acym_getEmailCssFixes();
            $style[] = $emailFixes;

            if (!empty($this->stylesheet)) $style[] = $this->stylesheet;

            $settingsStyles = $this->editorHelper->getSettingsStyle($this->settings);
            if (!empty($settingsStyles)) $style[] = $settingsStyles;

            preg_match('@<[^>"t]*body[^>]*>@', $this->Body, $matches);
            if (empty($matches[0])) $this->Body = '<body>'.$this->Body.'</body>';

            $styleFoundInBody = preg_match_all('/<\s*style[^>]*>(.*?)<\s*\/\s*style>/s', $this->Body, $matches);
            if ($styleFoundInBody) {
                foreach ($matches[1] as $match) {
                    $style[] = $match;
                }
            }

            $emogrifier = new \acymEmogrifier\acymEmogrifier($this->Body, implode('', $style));
            $this->Body = $emogrifier->emogrifyBodyContent();

            $style[] = $emogrifier->mediaCSS;

            preg_match('@<[^>"t]*/body[^>]*>@', $this->Body, $matches);
            if (empty($matches[0])) $this->Body = $this->Body.'</body>';

            unset($style['foundation']);

            $finalContent = '<html><head>';
            $finalContent .= '<meta http-equiv="Content-Type" content="text/html; charset='.strtolower($this->config->get('charset')).'" />'."\n";
            $finalContent .= '<meta name="viewport" content="width=device-width, initial-scale=1.0" />'."\n";
            $finalContent .= '<title>'.$this->Subject.'</title>'."\n";
            $finalContent .= '<style type="text/css">'.implode('</style><style type="text/css">', $style).'</style>';
            if (!empty($this->mailHeader)) $finalContent .= $this->mailHeader;
            $finalContent .= '</head>'.$this->Body.'</html>';

            $this->Body = $finalContent;
        }

        ob_start();
        $result = parent::send();
        $warnings = ob_get_clean();

        if (!empty($warnings) && strpos($warnings, 'bloque')) {
            $result = false;
        }

        $this->mailHeader = '';

        $receivers = [];
        foreach ($this->to as $oneReceiver) {
            $receivers[] = $oneReceiver[0];
        }
        if (!$result) {
            $this->reportMessage = acym_translation_sprintf('ACYM_SEND_ERROR', '<b>'.$this->Subject.'</b>', '<b>'.implode(' , ', $receivers).'</b>');
            if (!empty($this->ErrorInfo)) {
                $this->reportMessage .= " \n\n ".$this->ErrorInfo;
            }
            if (!empty($warnings)) {
                $this->reportMessage .= " \n\n ".$warnings;
            }
            $this->errorNumber = 1;
            if ($this->report) {
                $this->reportMessage = str_replace(
                    'Could not instantiate mail function',
                    '<a target="_blank" href="'.ACYM_REDIRECT.'could-not-instantiate-mail-function">'.acym_translation('ACYM_COUND_NOT_INSTANCIATE_MAIL_FUCNTION').'</a>',
                    $this->reportMessage
                );
                acym_enqueueMessage(nl2br($this->reportMessage), 'error');
            }
        } else {
            $this->reportMessage = acym_translation_sprintf('ACYM_SEND_SUCCESS', '<b>'.$this->Subject.'</b>', '<b>'.implode(' , ', $receivers).'</b>');
            if (!empty($warnings)) {
                $this->reportMessage .= " \n\n ".$warnings;
            }
            if ($this->report) {
                if (acym_isAdmin()) {
                    acym_enqueueMessage(preg_replace('#(<br( ?/)?>){2}#', '<br />', nl2br($this->reportMessage)), 'info');
                }
            }
        }

        return $result;
    }

    public function clearAll()
    {
        $this->Subject = '';
        $this->Body = '';
        $this->AltBody = '';
        $this->ClearAllRecipients();
        $this->ClearAttachments();
        $this->ClearCustomHeaders();
        $this->ClearReplyTos();
        $this->errorNumber = 0;
        $this->MessageID = '';
        $this->ErrorInfo = '';

        $this->setFrom($this->config->get('from_email'), $this->config->get('from_name'));
    }

    public function load($mailId)
    {
        $mailClass = acym_get('class.mail');
        $this->defaultMail[$mailId] = $mailClass->getOneById($mailId);

        if (empty($this->defaultMail[$mailId])) {
            $this->defaultMail[$mailId] = $mailClass->getOneByName($mailId);
        }

        if (empty($this->defaultMail[$mailId]->id)) {
            unset($this->defaultMail[$mailId]);

            return false;
        }

        $this->defaultMail[$mailId]->altbody = $this->textVersion($this->defaultMail[$mailId]->body);

        if (!empty($this->defaultMail[$mailId]->attachments)) {
            $this->defaultMail[$mailId]->attach = [];

            $attachments = json_decode($this->defaultMail[$mailId]->attachments);
            foreach ($attachments as $oneAttach) {
                $attach = new stdClass();
                $attach->name = basename($oneAttach->filename);
                $attach->filename = str_replace(['/', '\\'], DS, ACYM_ROOT).$oneAttach->filename;
                $attach->url = ACYM_LIVE.$oneAttach->filename;
                $this->defaultMail[$mailId]->attach[] = $attach;
            }
        }

        acym_trigger('replaceContent', [&$this->defaultMail[$mailId], true]);

        $this->defaultMail[$mailId]->body = acym_absoluteURL($this->defaultMail[$mailId]->body);

        return $this->defaultMail[$mailId];
    }

    private function canTrack($mailId, $user)
    {
        if (empty($mailId) || empty($user) || $user->tracking != 1) return false;

        $mailClass = acym_get('class.mail');

        $mail = $mailClass->getOneById($mailId);
        if (!empty($mail) && $mail->tracking != 1) return false;

        $lists = $mailClass->getAllListsByMailIdAndUserId($mailId, $user->id);

        foreach ($lists as $list) {
            if ($list->tracking != 1) return false;
        }

        return true;
    }

    public function sendOne($mailId, $user, $isTest = false, $testNote = '')
    {
        $this->clearAll();

        if (!isset($this->defaultMail[$mailId]) && !$this->load($mailId)) {
            $this->reportMessage = 'Can not load the e-mail : '.acym_escape($mailId);
            if ($this->report) {
                acym_enqueueMessage($this->reportMessage, 'error');
            }
            $this->errorNumber = 2;

            return false;
        }

        if (is_string($user) && strpos($user, '@')) {
            $receiver = $this->userClass->getOneByEmail($user);

            if (empty($receiver) && $this->autoAddUser && acym_isValidEmail($user)) {
                $newUser = new stdClass();
                $newUser->email = $user;
                $this->userClass->checkVisitor = false;
                $this->userClass->sendConf = false;
                acym_setVar('acy_source', 'When sending a test');
                $userId = $this->userClass->save($newUser);
                $receiver = $this->userClass->getOneById($userId);
            }
        } elseif (is_object($user)) {
            $receiver = $user;
        } else {
            $receiver = $this->userClass->getOneById($user);
        }

        if (empty($receiver->email)) {
            $this->reportMessage = acym_translation_sprintf('ACYM_SEND_ERROR_USER', '<b><i>'.acym_escape($user).'</i></b>');
            if ($this->report) {
                acym_enqueueMessage($this->reportMessage, 'error');
            }
            $this->errorNumber = 4;

            return false;
        }

        $this->MessageID = "<".preg_replace("|[^a-z0-9+_]|i", '', base64_encode(rand(0, 9999999))."AC".$receiver->id."Y".$this->defaultMail[$mailId]->id."BA".base64_encode(time().rand(0, 99999)))."@".$this->serverHostname().">";

        $addedName = '';
        if ($this->config->get('add_names', true)) {
            $addedName = $this->cleanText($receiver->name);
            if ($addedName == $this->cleanText($receiver->email)) {
                $addedName = '';
            }
        }
        $this->addAddress($this->cleanText($receiver->email), $addedName);

        $this->isHTML(true);

        $this->Subject = $this->defaultMail[$mailId]->subject;
        $this->Body = $this->defaultMail[$mailId]->body;
        if ($isTest && $testNote != '') {
            $this->Body = '<div style="text-align: center; padding: 25px; font-family: Poppins; font-size: 20px">'.$testNote.'</div>'.$this->Body;
        }
        $this->Preheader = $this->defaultMail[$mailId]->preheader;

        if ($this->config->get('multiple_part', false)) {
            $this->AltBody = $this->defaultMail[$mailId]->altbody;
        }

        if (!empty($this->defaultMail[$mailId]->stylesheet)) {
            $this->stylesheet = $this->defaultMail[$mailId]->stylesheet;
        }
        $this->settings = json_decode($this->defaultMail[$mailId]->settings, true);

        if (!empty($this->defaultMail[$mailId]->headers)) {
            $this->mailHeader = $this->defaultMail[$mailId]->headers;
        }

        $this->setFrom($this->defaultMail[$mailId]->from_email, $this->defaultMail[$mailId]->from_name);
        $this->_addReplyTo($this->defaultMail[$mailId]->reply_to_email, $this->defaultMail[$mailId]->reply_to_name);

        if (!empty($this->defaultMail[$mailId]->bcc)) {
            $bcc = trim(str_replace([',', ' '], ';', $this->defaultMail[$mailId]->bcc));
            $allBcc = explode(';', $bcc);
            foreach ($allBcc as $oneBcc) {
                if (empty($oneBcc)) continue;
                $this->AddBCC($oneBcc);
            }
        }

        if (!empty($this->defaultMail[$mailId]->attach)) {
            if ($this->config->get('embed_files')) {
                foreach ($this->defaultMail[$mailId]->attach as $attachment) {
                    $this->addAttachment($attachment->filename);
                }
            } else {
                $attachStringHTML = '<br /><fieldset><legend>'.acym_translation('ATTACHMENTS').'</legend><table>';
                $attachStringText = "\n"."\n".'------- '.acym_translation('ATTACHMENTS').' -------';
                foreach ($this->defaultMail[$mailId]->attach as $attachment) {
                    $attachStringHTML .= '<tr><td><a href="'.$attachment->url.'" target="_blank">'.$attachment->name.'</a></td></tr>';
                    $attachStringText .= "\n".'-- '.$attachment->name.' ( '.$attachment->url.' )';
                }
                $attachStringHTML .= '</table></fieldset>';

                $this->Body .= $attachStringHTML;
                if (!empty($this->AltBody)) {
                    $this->AltBody .= "\n".$attachStringText;
                }
            }
        }

        if (!empty($this->introtext)) {
            $this->Body = $this->introtext.$this->Body;
            $this->AltBody = $this->textVersion($this->introtext).$this->AltBody;
        }

        $preheader = '';
        if (!empty($this->Preheader)) {
            $spacing = '';

            for ($x = 0 ; $x < 100 ; $x++) {
                $spacing .= '&nbsp;&zwnj;';
            }
            $preheader = '<!--[if !mso 9]><!--><div style="visibility:hidden;mso-hide:all;font-size:0;color:transparent;height:0;line-height:0;max-height:0;max-width:0;opacity:0;overflow:hidden;">'.$this->Preheader.$spacing.'</div><!--<![endif]-->';
        }

        if (!empty($preheader)) {
            $this->Body = $preheader.$this->Body;
            $this->AltBody = $this->textVersion($preheader).$this->AltBody;
        }


        $this->replaceParams();

        $this->body = &$this->Body;
        $this->altbody = &$this->AltBody;
        $this->subject = &$this->Subject;
        $this->from = &$this->From;
        $this->fromName = &$this->FromName;
        $this->replyto = &$this->ReplyTo;
        $this->replyname = $this->defaultMail[$mailId]->reply_to_name;
        $this->replyemail = $this->defaultMail[$mailId]->reply_to_email;
        $this->id = $this->defaultMail[$mailId]->id;
        $this->creator_id = $this->defaultMail[$mailId]->creator_id;
        $this->type = $this->defaultMail[$mailId]->type;
        $this->stylesheet = &$this->stylesheet;
        $this->links_language = $this->defaultMail[$mailId]->links_language;

        if (!$isTest && $this->canTrack($mailId, $receiver)) {
            $this->statPicture($this->id, $receiver->id);
            $this->body = acym_absoluteURL($this->body);
            $this->statClick($this->id, $receiver->id);
        }

        $this->replaceParams();

        if (strpos($receiver->email, '@mailtester.acyba.com') !== false) {
            $currentUser = $this->userClass->getOneByEmail(acym_currentUserEmail());
            if (empty($currentUser)) {
                $currentUser = $receiver;
            }
            acym_trigger('replaceUserInformation', [&$this, &$currentUser, true]);
        } else {
            acym_trigger('replaceUserInformation', [&$this, &$receiver, true]);
        }

        $status = $this->send();
        if ($this->trackEmail) {
            $helperQueue = acym_get('helper.queue');
            $statsAdd = [];
            $statsAdd[$this->id][$status][] = $receiver->id;
            $helperQueue->statsAdd($statsAdd);
            $this->trackEmail = false;
        }

        return $status;
    }

    public function statPicture($mailId, $userId)
    {
        $pictureLink = acym_frontendLink('frontstats&task=openStats&id='.$mailId.'&userid='.$userId);

        $widthsize = 50;
        $heightsize = 1;
        $width = empty($widthsize) ? '' : ' width="'.$widthsize.'" ';
        $height = empty($heightsize) ? '' : ' height="'.$heightsize.'" ';

        $statPicture = '<img class="spict" alt="Statistics image" src="'.$pictureLink.'"  border="0" '.$height.$width.'/>';

        if (strpos($this->body, '</body>')) {
            $this->body = str_replace('</body>', $statPicture.'</body>', $this->body);
        } else {
            $this->body .= $statPicture;
        }
    }

    public function statClick($mailId, $userid, $fromStat = false)
    {
        if (!$fromStat && !in_array($this->type, ['standard', 'automation'])) return;

        $urlClass = acym_get('class.url');
        if ($urlClass === null) return;

        $urls = [];

        $trackingSystemExternalWebsite = $this->config->get('trackingsystemexternalwebsite', 1);
        $trackingSystem = $this->config->get('trackingsystem', 'acymailing');
        if (false === strpos($trackingSystem, 'acymailing') && false === strpos($trackingSystem, 'google')) return;

        if (strpos($trackingSystem, 'google') !== false) {
            $mailClass = acym_get('class.mail');
            $mail = $mailClass->getOneById($mailId);

            $utmCampaign = acym_getAlias($mail->subject);
        }

        preg_match_all('#href[ ]*=[ ]*"(?!mailto:|\#|ymsgr:|callto:|file:|ftp:|webcal:|skype:|tel:)([^"]+)"#Ui', $this->body, $results);
        if (empty($results)) return;

        $countLinks = array_count_values($results[1]);
        if (array_product($countLinks) != 1) {
            foreach ($results[1] as $key => $url) {
                if ($countLinks[$url] == 1) {
                    continue;
                }
                $countLinks[$url]--;

                $toAddUrl = (strpos($url, '?') === false ? '?' : '&').'idU='.$countLinks[$url];

                $posHash = strpos($url, '#');
                if ($posHash !== false) {
                    $newURL = substr($url, 0, $posHash).$toAddUrl.substr($url, $posHash);
                } else {
                    $newURL = $url.$toAddUrl;
                }

                $this->body = preg_replace('#href="('.preg_quote($url, '#').')"#Uis', 'href="'.$newURL.'"', $this->body, 1);
                if (!$fromStat) $this->altbody = preg_replace('#\( ('.preg_quote($url, '#').') \)#Uis', '( '.$newURL.' )', $this->altbody, 1);

                $results[0][$key] = 'href="'.$newURL.'"';
                $results[1][$key] = $newURL;
            }
        }

        foreach ($results[1] as $i => $url) {
            if (isset($urls[$results[0][$i]]) || strpos($url, 'task=unsub')) {
                continue;
            }

            $simplifiedUrl = str_replace(['https://', 'http://', 'www.'], '', $url);
            $simplifiedWebsite = str_replace(['https://', 'http://', 'www.'], '', ACYM_LIVE);
            $internalUrl = strpos($simplifiedUrl, rtrim($simplifiedWebsite, '/')) === 0;

            $subfolder = false;
            if ($internalUrl) {
                $urlWithoutBase = str_replace($simplifiedWebsite, '', $simplifiedUrl);
                if (strpos($urlWithoutBase, '/') || strpos($urlWithoutBase, '?')) {
                    $folderName = substr($urlWithoutBase, 0, strpos($urlWithoutBase, '/') == false ? strpos($urlWithoutBase, '?') : strpos($urlWithoutBase, '/'));
                    if (strpos($folderName, '.') === false) {
                        $subfolder = @is_dir(ACYM_ROOT.$folderName);
                    }
                }
            }

            if ((!$internalUrl || $subfolder) && $trackingSystemExternalWebsite != 1) {
                continue;
            }

            if (strpos($url, 'utm_source') === false && strpos($trackingSystem, 'google') !== false) {
                $args = [];
                $args[] = 'utm_source=newsletter_'.$mailId;
                $args[] = 'utm_medium=email';
                $args[] = 'utm_campaign='.$utmCampaign;
                $anchor = '';
                if (strpos($url, '#') !== false) {
                    $anchor = substr($url, strpos($url, '#'));
                    $url = substr($url, 0, strpos($url, '#'));
                }

                if (strpos($url, '?')) {
                    $mytracker = $url.'&'.implode('&', $args);
                } else {
                    $mytracker = $url.'?'.implode('&', $args);
                }
                $mytracker .= $anchor;
                $urls[$results[0][$i]] = str_replace($results[1][$i], $mytracker, $results[0][$i]);

                $url = $mytracker;
            }

            if (strpos($trackingSystem, 'acymailing') !== false) {
                if (preg_match('#subid|passw|modify|\{|%7B#i', $url)) continue;

                if (!$fromStat) $mytracker = $urlClass->getUrl($url, $mailId, $userid);
                if (empty($mytracker)) continue;

                $urls[$results[0][$i]] = str_replace($results[1][$i], $mytracker, $results[0][$i]);
            }
        }

        $this->body = str_replace(array_keys($urls), $urls, $this->body);
    }

    public function textVersion($html, $fullConvert = true)
    {
        $html = acym_absoluteURL($html);

        if ($fullConvert) {
            $html = preg_replace('# +#', ' ', $html);
            $html = str_replace(["\n", "\r", "\t"], '', $html);
        }

        $removepictureslinks = "#< *a[^>]*> *< *img[^>]*> *< *\/ *a *>#isU";
        $removeScript = "#< *script(?:(?!< */ *script *>).)*< */ *script *>#isU";
        $removeStyle = "#< *style(?:(?!< */ *style *>).)*< */ *style *>#isU";
        $removeStrikeTags = '#< *strike(?:(?!< */ *strike *>).)*< */ *strike *>#iU';
        $replaceByTwoReturnChar = '#< *(h1|h2)[^>]*>#Ui';
        $replaceByStars = '#< *li[^>]*>#Ui';
        $replaceByReturnChar1 = '#< */ *(li|td|dt|tr|div|p)[^>]*> *< *(li|td|dt|tr|div|p)[^>]*>#Ui';
        $replaceByReturnChar = '#< */? *(br|p|h1|h2|legend|h3|li|ul|dd|dt|h4|h5|h6|tr|td|div)[^>]*>#Ui';
        $replaceLinks = '/< *a[^>]*href *= *"([^#][^"]*)"[^>]*>(.+)< *\/ *a *>/Uis';

        $text = preg_replace([$removepictureslinks, $removeScript, $removeStyle, $removeStrikeTags, $replaceByTwoReturnChar, $replaceByStars, $replaceByReturnChar1, $replaceByReturnChar, $replaceLinks], ['', '', '', '', "\n\n", "\n* ", "\n", "\n", '${2} ( ${1} )'], $html);

        $text = preg_replace('#(&lt;|&\#60;)([^ \n\r\t])#i', '&lt; ${2}', $text);

        $text = str_replace([" ", "&nbsp;"], ' ', strip_tags($text));

        $text = trim(@html_entity_decode($text, ENT_QUOTES, 'UTF-8'));

        if ($fullConvert) {
            $text = preg_replace('# +#', ' ', $text);
            $text = preg_replace('#\n *\n\s+#', "\n\n", $text);
        }

        return $text;
    }

    protected function embedImages()
    {
        preg_match_all('/(src|background)=[\'|"]([^"\']*)[\'|"]/Ui', $this->Body, $images);
        $result = true;

        if (empty($images[2])) {
            return $result;
        }

        $mimetypes = ['bmp' => 'image/bmp', 'gif' => 'image/gif', 'jpeg' => 'image/jpeg', 'jpg' => 'image/jpeg', 'jpe' => 'image/jpeg', 'png' => 'image/png', 'tiff' => 'image/tiff', 'tif' => 'image/tiff'];

        $allimages = [];

        foreach ($images[2] as $i => $url) {
            if (isset($allimages[$url])) {
                continue;
            }
            $allimages[$url] = 1;

            $path = $url;
            $base = str_replace(['http://www.', 'https://www.', 'http://', 'https://'], '', ACYM_LIVE);
            $replacements = ['https://www.'.$base, 'http://www.'.$base, 'https://'.$base, 'http://'.$base];
            foreach ($replacements as $oneReplacement) {
                if (strpos($url, $oneReplacement) === false) {
                    continue;
                }
                $path = str_replace([$oneReplacement, '/'], [ACYM_ROOT, DS], urldecode($url));
                break;
            }

            $filename = str_replace(['%', ' '], '_', basename($url));
            $md5 = md5($filename);
            $cid = 'cid:'.$md5;
            $fileParts = explode(".", $filename);
            if (empty($fileParts[1])) {
                continue;
            }
            $ext = strtolower($fileParts[1]);
            if (!isset($mimetypes[$ext])) {
                continue;
            }
            $mimeType = $mimetypes[$ext];
            if ($this->addEmbeddedImage($path, $md5, $filename, 'base64', $mimeType)) {
                $this->Body = preg_replace("/".preg_quote($images[0][$i], '/')."/Ui", $images[1][$i]."=\"".$cid."\"", $this->Body);
            } else {
                $result = false;
            }
        }

        return $result;
    }

    public function cleanText($text)
    {
        return trim(preg_replace('/(%0A|%0D|\n+|\r+)/i', '', (string)$text));
    }

    protected function _addReplyTo($email, $name)
    {
        if (empty($email)) {
            return;
        }
        $replyToName = $this->config->get('add_names', true) ? $this->cleanText(trim($name)) : '';
        $replyToEmail = trim($email);
        if (substr_count($replyToEmail, '@') > 1) {
            $replyToEmailArray = explode(';', str_replace([';', ','], ';', $replyToEmail));
            $replyToNameArray = explode(';', str_replace([';', ','], ';', $replyToName));
            foreach ($replyToEmailArray as $i => $oneReplyTo) {
                $this->addReplyTo($this->cleanText($oneReplyTo), @$replyToNameArray[$i]);
            }
        } else {
            $this->addReplyTo($this->cleanText($replyToEmail), $replyToName);
        }
    }

    private function replaceParams()
    {
        if (empty($this->parameters)) return;

        $helperPlugin = acym_get('helper.plugin');

        $this->generateAllParams();

        $vars = [
            'Subject',
            'Body',
            'From',
            'FromName',
            'replyname',
            'replyemail',
        ];

        foreach ($vars as $oneVar) {
            if (!empty($this->$oneVar)) {
                $this->$oneVar = $helperPlugin->replaceDText($this->$oneVar, $this->parameters);
            }
        }

        if (!empty($this->ReplyTo)) {
            foreach ($this->ReplyTo as $i => $replyto) {
                foreach ($replyto as $a => $oneval) {
                    $this->ReplyTo[$i][$a] = $helperPlugin->replaceDText($this->ReplyTo[$i][$a], $this->parameters);
                }
            }
        }
    }

    private function generateAllParams()
    {
        $result = '<table style="border:1px solid;border-collapse:collapse;" border="1" cellpadding="10"><tr><td>Tag</td><td>Value</td></tr>';
        foreach ($this->parameters as $name => $value) {
            if (!is_string($value)) continue;

            $result .= '<tr><td>'.trim($name, '{}').'</td><td>'.$value.'</td></tr>';
        }
        $result .= '</table>';
        $this->addParam('allshortcodes', $result);
    }

    public function addParamInfo()
    {
        if (!empty($_SERVER)) {
            $serverinfo = [];
            foreach ($_SERVER as $oneKey => $oneInfo) {
                $serverinfo[] = $oneKey.' => '.strip_tags(print_r($oneInfo, true));
            }
            $this->addParam('serverinfo', implode('<br />', $serverinfo));
        }

        if (!empty($_REQUEST)) {
            $postinfo = [];
            foreach ($_REQUEST as $oneKey => $oneInfo) {
                $postinfo[] = $oneKey.' => '.strip_tags(print_r($oneInfo, true));
            }
            $this->addParam('postinfo', implode('<br />', $postinfo));
        }
    }

    public function addParam($name, $value)
    {
        $tagName = '{'.$name.'}';
        $this->parameters[$tagName] = $value;
    }



    public function setFrom($email, $name = '', $auto = false)
    {

        if (!empty($email)) {
            $this->From = $this->cleanText($email);
        }
        if (!empty($name) && $this->config->get('add_names', true)) {
            $this->FromName = $this->cleanText($name);
        }
    }

    public function getSMTPInstance()
    {
        if (!is_object($this->smtp)) {
            $this->smtp = new acySMTP();
        }

        return $this->smtp;
    }

    protected function edebug($str)
    {
        if (strpos($this->ErrorInfo, $str) === false) {
            $this->ErrorInfo .= ' '.$str;
        }
    }

    public function getMailMIME()
    {
        $result = parent::getMailMIME();

        $result = rtrim($result, static::$LE);

        if ($this->Mailer != 'mail') {
            $result .= static::$LE.static::$LE;
        }

        return $result;
    }

    public static function validateAddress($address, $patternselect = null)
    {
        return true;
    }
}

