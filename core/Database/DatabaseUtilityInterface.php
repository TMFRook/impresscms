<?php
/**
 * Utility interface methods for database functions
 *
 * @copyright	The ImpressCMS Project - http://www.impresscms.org/
 * @license		GNU General Public License (GPL) - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

namespace ImpressCMS\Core\Database;

/**
 * Utility interface for all DB drivers
 *
 * @copyright	The ImpressCMS Project - http://www.impresscms.org/
 *
 * @package	ICMS\Database
 * @since	2.0
 */
interface DatabaseUtilityInterface {

	/**
	 * Creates a new utility object
	 */
	public function __construct();

	/**
	 * Removes comment and splits large sql files into individual queries
	 *
	 *
	 * @param   array    the split sql commands
	 * @param   string   the sql commands
	 * @return  boolean  always true
	 */
	public static function splitSqlFile(&$ret, $sql);

	/**
	 * add a prefix.'_' to all tablenames in a query
	 *
	 * @param   string  $query  valid SQL query string
	 * @param   string  $prefix prefix to add to all table names
	 * @return  mixed   FALSE on failure
	 */
	public static function prefixQuery($query, $prefix);

	/**
	 * Determine if the SQL string is safe
	 *
	 * @param string $sql
	 * @return bool	TRUE if the string is safe
	 */
	public static function checkSQL($sql);

}