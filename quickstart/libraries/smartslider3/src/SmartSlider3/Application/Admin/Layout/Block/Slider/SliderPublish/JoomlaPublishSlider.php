<?php
/**
 * @required N2JOOMLA
 */

namespace Nextend\SmartSlider3\Application\Admin\Layout\Block\Slider\SliderPublish;

use JComponentHelper;
use JFactory;
use Nextend\Framework\Database\Database;

class JoomlaPublishSlider {

    protected $db;
    protected $sliderID;
    protected $moduleType;

    public function __construct($sliderID) {
        $this->sliderID   = $sliderID;
        $this->db         = Database::getInstance();
        $this->moduleType = JComponentHelper::getComponent('com_advancedmodules', true)->enabled ? 'com_advancedmodules' : 'com_modules';
    }

    public function getCreateModuleLink() {
        $ss3Module = $this->db->queryRow("SELECT extension_id FROM `#__extensions` WHERE `element` LIKE  'mod_smartslider3'");
        if (count($ss3Module)) {
            return 'index.php?option=' . $this->moduleType . '&task=module.add&eid=' . $ss3Module['extension_id'] . '&params[slider]=' . $this->sliderID;
        } else {
            return 'index.php?option=' . $this->moduleType . '&view=select';
        }
    }

    public function getModuleList() {
        $modulesData = array();
        $modules     = $this->db->queryAll("SELECT * FROM `#__modules` WHERE `module` LIKE 'mod_smartslider3' AND `params` LIKE '%\"slider\":\"" . $this->sliderID . "\"%'");
        if (count($modules)) {
            $list = '<ul>';
            $IDs  = array();
            foreach ($modules AS $module) {
                $IDs[] = intval($module['id']);

                $modulesData[] = array(
                    'url'   => 'index.php?option=' . $this->moduleType . '&view=module&layout=edit&id=' . $module['id'],
                    'label' => $module['title']
                );
                $list          .= '
                        <li>
                            <a href="index.php?option=' . $this->moduleType . '&view=module&layout=edit&id=' . $module['id'] . '" target="_blank">' . $module['title'] . '</a>
                        </li>';
            }

            $context = 'com_modules.edit.module';
            $app     = JFactory::getApplication();
            $app->setUserState($context . '.id', $IDs);
        }

        return $modulesData;
    }
}