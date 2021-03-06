<?php
namespace ImpressCMS\Core\View\ModelLinkedForm\Elements;

use ImpressCMS\Core\IPF\AbstractDatabaseModel;

/**
 * Form control creating a textbox to enter time for an object derived from \ImpressCMS\Core\IPF\AbstractModel
 *
 * @copyright	The ImpressCMS Project http://www.impresscms.org/
 * @license	http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @package	ICMS\IPF\Form\Elements
 * @since	1.1
 * @author	marcan <marcan@impresscms.org>
 */
class TimeElement extends \ImpressCMS\Core\View\Form\Elements\SelectElement {
	/**
	 * Constructor
	 * @param	AbstractDatabaseModel    $object   reference to target object
	 * @param	string    $key      the form name
	 */
	public function __construct($object, $key) {
		$var = $object->getVarInfo($key);
		$timearray = [];
		for ($i = 0; $i < 24; $i++) {
			for ($j = 0; $j < 60; $j += 10) {
				$key_t = ($i * 3600) + ($j * 60);
				$timearray[$key_t] = ($j !== 0)?$i . ':' . $j:$i . ':0' . $j;
			}
		}
		ksort($timearray);
		parent::__construct($var['form_caption'], $key, $object->getVar($key, 'e'));
		$this->addOptionArray($timearray);
	}
}