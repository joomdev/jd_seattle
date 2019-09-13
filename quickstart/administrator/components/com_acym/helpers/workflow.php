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

class acymworkflowHelper
{
    var $disabledAfter = null;

    function display($steps, $currentStep, $edition, $workflowMode = true)
    {
        $workflow = [];
        $currentStepReached = false;
        $disableTabs = false;
        foreach ($steps as $task => $title) {

            $class = 'step';
            if ($disableTabs || ($currentStepReached && !$edition)) {
                $class .= ' disabled_step';
            }

            if ($currentStep === $task) {
                $currentStepReached = true;
                $class .= ' current_step';
            }

            $params = '';
            if ($edition && $workflowMode && !$disableTabs) {
                $params .= 'data-task="edit" data-step="'.$task.'"';
                $class .= ' acy_button_submit';
            }

            $title = acym_translation($title);

            if (!$workflowMode) {
                $title = '<a href="'.acym_completeLink(acym_getVar('cmd', 'ctrl').'&task='.$task).'">'.$title.'</a>';
            }

            $workflow[] = '<li '.$params.' class="'.$class.'">'.$title.'</li>';
            if (!$edition) {
                $workflow[] = '<li class="step_separator fa fa-angle-right"></li>';
            }

            if ($task == $this->disabledAfter) {
                $disableTabs = true;
            }
        }

        if (!$edition) {
            array_pop($workflow);
        }

        $result = '<ul id="workflow" class="'.($edition ? 'tabs' : '').'">';
        $result .= implode('', $workflow);
        $result .= '</ul>';

        if (!$edition) {
            $result .= '<hr/>';
        }

        return $result;
    }
}

