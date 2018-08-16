<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	5.10.3
 * @author	acyba.com
 * @copyright	(C) 2009-2018 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><?php

class filetreeType extends acymailingClass{
	public function display($folders, $currentFolder, $nameInput, $onclickCallBack){
		$tree = array();
		foreach($folders as $root => $children){
			$tree = array_merge($tree, $this->_searchChildren($children, $root));
		}
		echo '<div id="displaytree"><input style="margin:0; cursor:pointer; float: left; height: 20px;" disabled type="text" name="currentPath" id="currentPath" value="'.$currentFolder.'">';
		echo '<button style="margin:0; min-height: 24px;" class="btn"><i class="acyicon-tree"></i></button></div>';
		echo '<div style="display:none" class="tree" id="treefile">'.$this->_displayTree($tree, $currentFolder).'</div>';
		echo '<input type="hidden" name="'.$nameInput.'" id="'.$nameInput.'" value="'.$currentFolder.'">';
		$this->_treeBehavior($nameInput, $onclickCallBack);
	}

	private function _treeBehavior($idHiddenSelected, $onclickCallBack){
		$script = "
		var buttonDisplay = document.getElementById('displaytree');
		buttonDisplay.addEventListener('click', function(event){
			event.preventDefault();
			event.stopPropagation();
			var tree = document.getElementById('treefile');
			tree.style.display = (tree.style.display == 'block') ? 'none' : 'block';
		});

		var items = document.getElementsByClassName('tree-icon');
		for(var i = 0; i < items.length; i++) {
			var item = items[i];
			item.addEventListener('click', function(event) {
				event.preventDefault();
				event.stopPropagation();

				var input = document.getElementById('".$idHiddenSelected."');
				input.value = this.parentNode.dataset.path;

				if(this.parentNode.className.indexOf('tree-closed') != -1) {
					var foldericon = this.getElementsByClassName('acyicon-folder')[0];
					foldericon.className = foldericon.className.replace('acyicon-folder', 'acyicon-folderopen');
					this.parentNode.className = this.parentNode.className.replace('tree-closed', '');
				} else {
					var foldericon = this.getElementsByClassName('acyicon-folderopen')[0];
					foldericon.className = foldericon.className.replace('acyicon-folderopen', 'acyicon-folder');
					this.parentNode.className += ' tree-closed';
				}
			});
		}

		var links = document.getElementsByClassName('tree-child-title');
		for(var i = 0; i < links.length; i++) {
			var link = links[i];
			link.addEventListener('click', function(event) {
				event.preventDefault();
				event.stopPropagation();

				var path = this.parentNode.dataset.path;

				var input = document.getElementById('".$idHiddenSelected."');
				input.value = path;

				input = document.getElementById('currentPath');
				input.value = path;
				".$onclickCallBack."
			});
		}
		";

		echo '<script type="text/javascript">window.addEventListener("load", function() {'.$script.'})</script>';
	}

	private function _searchChildren($folders, $root){
		$tree = array();
		$tree[$root] = array();

		foreach($folders as $folder){
			$folder = trim(str_replace($root, '', $folder), '/\\');
			if(empty($folder)) continue;

			$pathParts = explode('/', $folder);
			$variable = &$tree[$root];
			foreach($pathParts as $pathPart){
				if(empty($variable[$pathPart])) $variable[$pathPart] = array();
				$variable = &$variable[$pathPart];
			}
		}
		return $tree;
	}

	private function _displayTree($tree, $pathValue, $path = ''){
		$results = '';
		$results .= '<ul>';
		foreach($tree as $key => $treeItem){
			$currentPath = (empty($path)) ? $key : $path.'/'.$key;
			if(strpos($pathValue, $currentPath) !== false){
				$extraClass = ($pathValue == $currentPath) ? 'tree-current' : '';
				$icon = 'acyicon-folderopen';
			}else{
				$extraClass = 'tree-closed';
				$icon = 'acyicon-folder';
			}

			if(empty($treeItem)){
				$extraClass .= ' tree-empty';
			}

			$subTree = $this->_displayTree($treeItem, $pathValue, $currentPath);
			$results .= '<li class="tree-child-item '.$extraClass.'" data-path="'.$currentPath.'"><span class="tree-icon"><i class="'.$icon.'"></i></span><span class="tree-child-title">'.$key.'</span>'.$subTree.'</li>';
		}
		$results .= '</ul>';

		return $results;
	}
}
