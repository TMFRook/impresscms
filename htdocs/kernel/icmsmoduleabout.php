<?php
/**
 * @deprecated	Use icms_ipf_About, instead
 * @todo		Remove in version 1.4
 *
 */
class IcmsModuleAbout extends icms_ipf_About {
	private $_deprecated;
	public function __construct() {
		parent::getInstance();
		$this->_deprecated = icms_deprecated('icms_ipf_About', sprintf(_CORE_REMOVE_IN_VERSION, '1.4'));
	}
}

?>