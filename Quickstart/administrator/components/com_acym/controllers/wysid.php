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

class WysidController extends acymController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getPosts()
    {
        acym_getCMSPosts(acym_getVar('string', 'category'), acym_getVar('string', 'keyword'), acym_getVar('int', 'offset'));
        die();
    }

    public function getCategories()
    {
        acym_getCMSCategories();
        die();
    }
}
