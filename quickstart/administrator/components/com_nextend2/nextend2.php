<?php
if (JFactory::getUser()
            ->authorise('core.manage', 'com_nextend2')
) {
    jimport("nextend2.nextend.joomla.library");
    N2Base::getApplication("system")
          ->getApplicationType('backend')
          ->setCurrent()
          ->render(array(
              "controller" => "dashboard",
              "action"     => "index"
          ));
    n2_exit();
} else {
    return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}
