<?php
/**
 * IcmsMetagen
 *
 * Containing the class to manage meta informations of icms_ipf_Object
 *
 * @copyright	The ImpressCMS Project http://www.impresscms.org/
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License (GPL)
 * @package		icms_ipf_Object
 * @since		1.1
 * @author		marcan <marcan@impresscms.org>
 * @version		$Id$
 */

if (!defined("ICMS_ROOT_PATH")) die("ImpressCMS root path not defined");
/**
 * @deprecated	Use icms_ipf_Metagen
 * @todo		Remove in version 1.4
 *
 */
class IcmsMetagen extends icms_ipf_Metagen {
	private $_deprecated;
	public function __construct() {
		parent::getInstance();
		$this->_deprecated = icms_core_Debug::setDeprecated('icms_ipf_Metagen', sprintf(_CORE_REMOVE_IN_VERSION, '1.4'));
	}
}

?>