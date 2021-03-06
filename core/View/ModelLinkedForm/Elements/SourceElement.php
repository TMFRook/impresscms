<?php
namespace ImpressCMS\Core\View\ModelLinkedForm\Elements;

use ImpressCMS\Core\Extensions\Editors\EditorsRegistry;
use ImpressCMS\Core\IPF\AbstractDatabaseModel;
use ImpressCMS\Core\View\Form\Elements\TextAreaElement;

/**
 * Form control creating a textbox for an object derived from \ImpressCMS\Core\IPF\AbstractModel
 *
 * @copyright	The ImpressCMS Project http://www.impresscms.org/
 * @license	http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @package	ICMS\IPF\Form\Elements
 * @since	1.2
 * @author	MekDrop <mekdrop@gmail.com>
 */
class SourceElement extends TextAreaElement {
	/*
	 * Editor's class instance
	 */
	private $_editor = null;

	/**
	 * Constructor
	 * @param	AbstractDatabaseModel    $object   reference to targetobject
	 * @param	string    $key      the form name
	 */
	public function __construct($object, $key) {
		global $icmsConfig;

		parent::__construct($object->getVarInfo($key, 'form_caption'), $key, $object->getVar($key, 'e'));

		$control = $object->getControl($key);

		$editor_handler = EditorsRegistry::getInstance('source');
		$this->_editor = &$editor_handler->get($icmsConfig['sourceeditor_default'],
			array('name' => $key,
				'value' => $object->getVar($key, 'e'),
				'language' => $control['language'] ?? _LANGCODE,
				'width' => $control['width'] ?? '100%',
				'height' => $control['height'] ?? '400px',
				'syntax' => $control['syntax'] ?? 'php'));
	}

	/**
	 * Renders the editor
	 * @return	string  the constructed html string for the editor
	 */
	public function render() {
		return $this->_editor ? $this->_editor->render() : parent::render();
	}
}