<?php
if (!defined("ICMS_ROOT_PATH")) die("ImpressCMS root path not defined");
/**
 * @deprecated	Use icms_ipf_Object, instead
 * @todo		Remove in version 1.4
 *
 */
class IcmsPersistableObject extends icms_ipf_Object {
	private $_deprecated;
	public function __construct() {
		parent::getInstance();
		$this->_deprecated = icms_deprecated('icms_ipf_Object', sprintf(_CORE_REMOVE_IN_VERSION, '1.4'));
	}
}

?>