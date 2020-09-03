<?php
defined('_JEXEC') or die('Restricted access');
?><?php

class acymworkflowHelper extends acymObject
{
    var $disabledAfter = null;

    public function display($steps, $currentStep, $edition = true, $needTabs = false)
    {
        $ctrl = acym_getVar('cmd', 'ctrl');
        $id = acym_getVar('int', 'id', 0);

        $workflow = [];
        $disableTabs = false;
        foreach ($steps as $task => $title) {
            $title = acym_translation($title);

            $class = 'step';
            if ($disableTabs) $class .= ' disabled_step';
            if ($currentStep === $task) $class .= ' current_step';

            if (!$disableTabs) {
                if ($edition) {
                    $link = $ctrl.'&task=edit&step='.$task.'&id='.$id;
                } else {
                    $link = $ctrl.'&task='.$task;
                }
                $title = '<a href="'.acym_completeLink($link).'">'.$title.'</a>';
            }

            $workflow[] = '<li class="'.$class.'">'.$title.'</li>';
            $workflow[] = '<li class="step_separator '.($needTabs ? '' : 'acymicon-keyboard_arrow_right').'"></li>';

            if ($task == $this->disabledAfter) {
                $disableTabs = true;
            }
        }

        array_pop($workflow);

        $result = '<ul id="workflow">';
        $result .= implode('', $workflow);
        $result .= '</ul>';

        return $result;
    }

    public function displayTabs($steps, $currentStep)
    {
        $ctrl = acym_getVar('cmd', 'ctrl');

        $workflow = [];
        foreach ($steps as $task => $title) {
            $title = acym_translation($title);

            $linkAttribute = $currentStep == $task ? 'aria-selected="true"' : '';

            $link = $ctrl.'&task='.$task;

            $title = '<a class="acym_tab acym__color__medium-gray" '.$linkAttribute.' href="'.acym_completeLink($link).'">'.$title.'</a>';


            $workflow[] = '<li class="tabs-title">'.$title.'</li>';
        }

        $result = '<ul class="tabs" id="workflow">';
        $result .= implode('', $workflow);
        $result .= '</ul>';

        return $result;
    }
}

