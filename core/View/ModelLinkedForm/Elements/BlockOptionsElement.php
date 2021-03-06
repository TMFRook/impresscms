<?php
namespace ImpressCMS\Core\View\ModelLinkedForm\Elements;

use ImpressCMS\Core\IPF\AbstractDatabaseModel;
use ImpressCMS\Core\View\Form\Elements\LabelElement;
use ImpressCMS\Core\View\Form\Elements\TrayElement;

/**
 * Form control creating the options of a block
 *
 * @copyright	The ImpressCMS Project http://www.impresscms.org/
 * @license	http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @since	1.2
 * @author	marcan <marcan@impresscms.org>
 * @author	phoenyx
 * @package	ICMS\IPF\Form\Elements
 */
class BlockOptionsElement extends TrayElement {
	/**
	 * Constructor
	 * @param	AbstractDatabaseModel    $object   reference to targetobject
	 * @param	string    $key      the form name
	 */
	public function __construct($object, $key) {
		$var = $object->getVarInfo($key);
		parent::__construct($var['form_caption'], ' ', 'options_tray');
		$func = $object->edit_func;

		require_once ICMS_ROOT_PATH . "/modules/" . $object->handler->getModuleDirname($object->getVar('mid', 'e')) . "/blocks/" . $object->func_file;
		icms_loadLanguageFile($object->handler->getModuleDirname($object->getVar('mid', 'e')), 'blocks');

		if (!function_exists($func)) {
			return;
		}
		$visible_label = new LabelElement('', $func(explode('|', $object->options)));
		$this->addElement($visible_label);
	}
}