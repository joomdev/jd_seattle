<?php
jimport('joomla.plugin.plugin');

class plgSystemNextendSmartslider3 extends JPlugin {

    public function onInitN2Library() {
        N2Base::registerApplication(JPATH_SITE . DIRECTORY_SEPARATOR . "libraries" . DIRECTORY_SEPARATOR . 'nextend2/smartslider/smartslider/N2SmartsliderApplicationInfo.php');
    }

    public function onNextendBeforeCompileHead() {
        $application = JFactory::getApplication();
        if ($application->isSite()) {
            $request = $application->input->request;
            if ($application->get('frontediting', 1) && !JFactory::getUser()->guest && $request->get('option') == 'com_content' && $request->get('view') == 'form' && $request->get('layout') == 'edit') {
                return;
            }

            $body = $application->getBody();

            // Simple performance check to determine whether bot should process further
            if (strpos($body, 'smartslider3[') !== false) {
                if (class_exists('EshopHelper', false) && EshopHelper::getConfigValue('rich_snippets') == '1') {
                    $body = preg_replace_callback('/(<.*?>)?smartslider3\[([0-9]+)\]/', 'plgSystemNextendSmartslider3::cleanEshop', $body);
                }

                $bodyParts = explode('</head>', $body);

                if (isset($bodyParts[0])) {
                    $bodyParts[1] = preg_replace_callback('/smartslider3\[([0-9]+)\]/', 'plgSystemNextendSmartslider3::prepare', $bodyParts[1]);
                } else {
                    $bodyParts[0] = preg_replace_callback('/smartslider3\[([0-9]+)\]/', 'plgSystemNextendSmartslider3::prepare', $bodyParts[0]);
                }

                $application->setBody(implode('</head>', $bodyParts));
            }
        }
    }

    public static function prepare($matches) {
        ob_start();
        nextend_smartslider3($matches[1]);

        return ob_get_clean();
    }

    public static function cleanEshop($matches) {
        if (strpos($matches[1], 'itemprop') !== false) {
            return $matches[1];
        }

        return $matches[0];
    }
}

function nextend_smartslider3($sliderId, $usage = 'Used in PHP') {
    jimport("nextend2.nextend.joomla.library");

    N2Base::getApplication("smartslider")
          ->getApplicationType('frontend')
          ->render(array(
              "controller" => 'home',
              "action"     => 'joomla',
              "useRequest" => false
          ), array(
              $sliderId,
              $usage
          ));
}