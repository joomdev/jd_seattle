<?php
defined('_JEXEC') or die('Restricted access');
?><?php

class acymsplashscreenHelper extends acymObject
{
    const SPLASHSCREEN_OPTION_NAME = 'display_splashscreen';

    public function getDisplaySplashscreenForViewName($view = null)
    {
        if (is_null($view)) {
            return 0;
        }

        $displaySplashscreen = $this->config->get(self::SPLASHSCREEN_OPTION_NAME);

        if (!empty($displaySplashscreen)) {
            $arrayDisplaySplashscreen = json_decode($displaySplashscreen, true);
            if (!is_null($arrayDisplaySplashscreen) && is_array($arrayDisplaySplashscreen)) {
                return isset($arrayDisplaySplashscreen[$view]) ? $arrayDisplaySplashscreen[$view] : 0;
            }
        }

        return 0;
    }

    public function setDisplaySplashscreenForViewName($view = null, $value = 0)
    {
        if (is_null($view)) {
            return false;
        }

        if ($value != 0 && $value != 1) {
            $value = 0;
        }

        $displaySplashscreen = $this->config->get(self::SPLASHSCREEN_OPTION_NAME);
        $newArrayDisplaySplashscreen = [];

        if (!empty($displaySplashscreen)) {
            $arrayDisplaySplashscreen = json_decode($displaySplashscreen, true);
            if (!is_null($arrayDisplaySplashscreen) && is_array($arrayDisplaySplashscreen)) {
                $newArrayDisplaySplashscreen = $arrayDisplaySplashscreen;
            }
        }

        $newArrayDisplaySplashscreen[$view] = $value;
        $newDisplaySplashscreen = json_encode($newArrayDisplaySplashscreen);

        return $this->config->save([self::SPLASHSCREEN_OPTION_NAME => $newDisplaySplashscreen]);
    }
}
