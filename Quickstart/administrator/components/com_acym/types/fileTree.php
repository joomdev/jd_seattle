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

class fileTreeType extends acymClass
{
    function display($folders, $currentFolder, $nameInput)
    {
        $tree = array();
        foreach ($folders as $root => $children) {
            $tree = array_merge($tree, $this->searchChildren($children, $root));
        }

        $treeView = '<div id="displaytree" class="cell"><input disabled type="text" name="currentPath" id="currentPath" value="'.$currentFolder.'"></div>';
        $treeView .= '<div class="cell" id="treefile" style="display: none;">'.$this->displayTree($tree, $currentFolder).'</div>';
        $treeView .= '<input type="hidden" name="'.$nameInput.'" id="'.$nameInput.'" value="'.$currentFolder.'">';

        return $treeView;
    }

    private function searchChildren($folders, $root)
    {
        $tree = array();
        $tree[$root] = array();

        foreach ($folders as $folder) {
            $folder = trim(str_replace($root, '', $folder), '/\\');
            if (empty($folder)) {
                continue;
            }

            $pathParts = explode('/', $folder);
            $variable = &$tree[$root];
            foreach ($pathParts as $pathPart) {
                if (empty($variable[$pathPart])) {
                    $variable[$pathPart] = array();
                }
                $variable = &$variable[$pathPart];
            }
        }

        return $tree;
    }

    private function displayTree($tree, $pathValue, $path = '')
    {
        $results = '';
        if (!empty($tree)) {
            $results .= '<ul>';
            foreach ($tree as $key => $treeItem) {
                $currentPath = (empty($path)) ? $key : $path.'/'.$key;
                if (strpos($pathValue, $currentPath) !== false) {
                    $extraClass = ($pathValue == $currentPath) ? 'tree-current' : '';
                    $icon = 'fa fa-folder-open';
                } else {
                    $extraClass = 'tree-closed';
                    $icon = 'fa fa-folder';
                }

                if (empty($treeItem)) {
                    $extraClass .= ' tree-empty';
                }

                $subTree = $this->displayTree($treeItem, $pathValue, $currentPath);
                $results .= '<li class="tree-child-item '.$extraClass.'" data-path="'.$currentPath.'"><span class="tree-child-title"><i class="'.$icon.'"></i>'.$key.'</span>'.$subTree.'</li>';
            }
            $results .= '</ul>';
        }

        return $results;
    }
}
