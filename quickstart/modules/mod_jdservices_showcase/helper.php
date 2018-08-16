<?php

/**
 * Helper class for Hello World! module
 * 
 * @package    Joomla.Tutorials
 * @subpackage Modules
 * @link http://docs.joomla.org/J3.x:Creating_a_simple_module/Developing_a_Basic_Module
 * @license        GNU/GPL, see LICENSE.php
 * mod_helloworld is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */
defined('_JEXEC') or die;

class modJDServicesShowcaseHelper {

   public static function formatGrid($services = []) {
      $services = (array) $services;
      $return = [];
      switch (count($services)) {
         case 1:
         case 2:
         case 3:
            $services = array_chunk($services, 1);
            foreach ($services as $serviceCol) {
               $return[] = $serviceCol;
            }
            break;
         case 4:
            $services = array_chunk($services, 2);
            $return[] = $services[0];
            $return[] = [$services[1][0]];
            $return[] = [$services[1][1]];
            break;
         case 5:
            $services = array_chunk($services, 3);
            $return[] = [$services[0][0], $services[0][1]];
            $return[] = [$services[0][2]];
            $return[] = $services[1];
            break;
         case 6:
            $services = array_chunk($services, 2);
            $return[] = $services[0];
            $return[] = $services[1];
            $return[] = $services[2];
            break;
      }
      return $return;
   }

}
