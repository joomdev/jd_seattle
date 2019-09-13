<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2019 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace JDPageBuilder\Element;

class Layout extends BaseElement {

   protected $sections = [];

   public function __construct($object) {
      parent::__construct($object);
      $layout = \json_decode($object->layout, FALSE);
      if (isset($layout->sections)) {
         foreach ($layout->sections as $section) {
            $this->sections[] = new Section($section, $this);
         }
      }
      $this->id = 'jdb-layout-' . $this->id;
      $this->addClass($this->id);
      //$this->addAttribute('jdb-layout');
   }

   public function getContent() {
      $content = [];
      foreach ($this->sections as $section) {
         $content[] = $section->render();
      }
      return implode("", $content);
   }

}
