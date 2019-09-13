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

class acymParameter
{
    public function __construct($params = null)
    {
        if (is_string($params)) {
            $this->params = json_decode($params);
        } elseif (is_object($params)) {
            $this->paramObject = $params;
        } elseif (is_array($params)) {
            $this->params = (object)$params;
        }
    }

    function get($path, $default = null)
    {
        if (empty($this->paramObject)) {
            if (empty($this->params->$path) && !(isset($this->params->$path) && $this->params->$path === '0')) {
                return $default;
            }

            return $this->params->$path;
        } else {
            $value = $this->paramObject->get($path, 'noval');
            if ($value === 'noval') {
                $value = $this->paramObject->get('data.'.$path, $default);
            }

            return $value;
        }
    }
}

