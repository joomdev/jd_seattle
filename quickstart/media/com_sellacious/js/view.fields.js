/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
function listItemTask2(id, task, prefix, form) {
	var f = form || document.adminForm,
		i = 0, cbx,
		cb = f[prefix + id];

	if (!cb) return false;

	while (true) {
		cbx = f[prefix + i];
		if (!cbx) break;
		cbx.checked = false;
		i++;
	}

	cb.checked = true;
	Joomla.submitform(task);

	return false;
}

