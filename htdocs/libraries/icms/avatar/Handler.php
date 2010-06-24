<?php
/**
 * Manage avatars for users
 *
 * @copyright	http://www.xoops.org/ The XOOPS Project
 * @copyright	XOOPS_copyrights.txt
 * @copyright	http://www.impresscms.org/ The ImpressCMS Project
 * @license		LICENSE.txt
 * @package		core
 * @subpackage	avatar
 * @since		XOOPS
 * @author		Kazumi Ono (aka onokazo)
 * @author		http://www.xoops.org The XOOPS Project
 * @version		$Id: avatar.php 19569 2010-06-24 00:53:57Z malanciault $
 */

if (!defined('ICMS_ROOT_PATH')) die("ImpressCMS root path not defined");

/**
 * XOOPS avatar handler class.
 * This class is responsible for providing data access mechanisms to the data source
 * of XOOPS avatar class objects.
 *
 *
 * @author  Kazumi Ono <onokazu@xoops.org>
 */

class icms_avatar_Handler extends icms_core_ObjectHandler
{

	/**
	 * Creates a new avatar object
	 * @see htdocs/kernel/icms_core_ObjectHandler#create()
	 */
	function &create($isNew = true)
	{
		$avatar = new icms_core_avatar_Object();
		if ($isNew) {
			$avatar->setNew();
		}
		return $avatar;
	}

	/**
	 * Gets an avatar object
	 * @see htdocs/kernel/icms_core_ObjectHandler#get($int_id)
	 * @return mixed
	 */
	function &get($id)
	{
		$avatar = false;
		$id = (int) ($id);
		if ($id > 0) {
			$sql = "SELECT * FROM ".$this->db->prefix('avatar')." WHERE avatar_id='".$id."'";
			if (!$result = $this->db->query($sql)) {
				return false;
			}
			$numrows = $this->db->getRowsNum($result);
			if ($numrows == 1) {
				$avatar = new icms_core_avatar_Object();
				$avatar->assignVars($this->db->fetchArray($result));
				return $avatar;
			}
		}
		return $avatar;
	}

	/**
	 * Inserts an avatar or updates an existing avatar
	 * @see htdocs/kernel/icms_core_ObjectHandler#insert($object)
	 * @return boolean
	 */
	function insert(&$avatar)
	{
		/**
		 * @TODO: Change to if (!(class_exists($this->className) && $obj instanceof $this->className)) when going fully PHP5
		 */
		if (!is_a($avatar, 'xoopsavatar')) {
			return false;
		}
		if (!$avatar->isDirty()) {
			return true;
		}
		if (!$avatar->cleanVars()) {
			return false;
		}
		foreach ($avatar->cleanVars as $k => $v) {
			${$k} = $v;
		}
		if ($avatar->isNew()) {
			$avatar_id = $this->db->genId('avatar_avatar_id_seq');
			$sql = sprintf("INSERT INTO %s (avatar_id, avatar_file, avatar_name, avatar_created, avatar_mimetype, avatar_display, avatar_weight, avatar_type) VALUES ('%u', %s, %s, '%u', %s, '%u', '%u', %s)", $this->db->prefix('avatar'), (int) ($avatar_id), $this->db->quoteString($avatar_file), $this->db->quoteString($avatar_name), time(), $this->db->quoteString($avatar_mimetype), (int) ($avatar_display), (int) ($avatar_weight), $this->db->quoteString($avatar_type));
		} else {
			$sql = sprintf("UPDATE %s SET avatar_file = %s, avatar_name = %s, avatar_created = '%u', avatar_mimetype= %s, avatar_display = '%u', avatar_weight = '%u', avatar_type = %s WHERE avatar_id = '%u'", $this->db->prefix('avatar'), $this->db->quoteString($avatar_file), $this->db->quoteString($avatar_name), (int) ($avatar_created), $this->db->quoteString($avatar_mimetype), (int) ($avatar_display), (int) ($avatar_weight), $this->db->quoteString($avatar_type), (int) ($avatar_id));
		}
		if (!$result = $this->db->query($sql)) {
			return false;
		}
		if (empty($avatar_id)) {
			$avatar_id = $this->db->getInsertId();
		}
		$avatar->assignVar('avatar_id', $avatar_id);
		return true;
	}

	/**
	 * Deletes an avatar
	 * @see htdocs/kernel/icms_core_ObjectHandler#delete($object)
	 * @return boolean
	 */
	function delete(&$avatar)
	{
		/**
		 * @TODO: Change to if (!(class_exists($this->className) && $obj instanceof $this->className)) when going fully PHP5
		 */
		if (!is_a($avatar, 'xoopsavatar')) {
			return false;
		}

		$id = (int) ($avatar->getVar('avatar_id'));
		$sql = sprintf("DELETE FROM %s WHERE avatar_id = '%u'", $this->db->prefix('avatar'), $id);
		if (!$result = $this->db->query($sql)) {
			return false;
		}
		$sql = sprintf("DELETE FROM %s WHERE avatar_id = '%u'", $this->db->prefix('avatar_user_link'), $id);
		$result = $this->db->query($sql);
		return true;
	}

	/**
	 *
	 * @param object $criteria
	 * @param boolean $id_as_key
	 * @return array
	 */
	function &getObjects($criteria = null, $id_as_key = false)
	{
		$ret = array();
		$limit = $start = 0;
		$sql = "SELECT a.*, COUNT(u.user_id) AS count FROM ".$this->db->prefix('avatar')." a LEFT JOIN ".$this->db->prefix('avatar_user_link')." u ON u.avatar_id=a.avatar_id";
		if (isset($criteria) && is_subclass_of($criteria, 'icms_core_CriteriaElement')) {
			$sql .= " ".$criteria->renderWhere();
			$sql .= " GROUP BY a.avatar_id ORDER BY avatar_weight, avatar_id";
			$limit = $criteria->getLimit();
			$start = $criteria->getStart();
		}
		$result = $this->db->query($sql, $limit, $start);
		if (!$result) {
			return $ret;
		}
		while ($myrow = $this->db->fetchArray($result)) {
			$avatar = new icms_core_avatar_Object();
			$avatar->assignVars($myrow);
			$avatar->setUserCount($myrow['count']);
			if (!$id_as_key) {
				$ret[] =& $avatar;
			} else {
				$ret[$myrow['avatar_id']] =& $avatar;
			}
			unset($avatar);
		}
		return $ret;
	}

	/**
	 * Get a count of avatars meeting criteria
	 * @param object $criteria
	 * @return integer
	 */
	function getCount($criteria = null)
	{
		$sql = 'SELECT COUNT(*) FROM '.$this->db->prefix('avatar');
		if (isset($criteria) && is_subclass_of($criteria, 'icms_core_CriteriaElement')) {
			$sql .= ' '.$criteria->renderWhere();
		}
		if (!$result =& $this->db->query($sql)) {
			return 0;
		}
		list($count) = $this->db->fetchRow($result);
		return $count;
	}

	/**
	 * Links a user with an avatar
	 * @param integer $avatar_id
	 * @param integer $user_id
	 * @return boolean
	 */
	function addUser($avatar_id, $user_id){
		$avatar_id = (int) ($avatar_id);
		$user_id = (int) ($user_id);
		if ($avatar_id < 1 || $user_id < 1) {
			return false;
		}
		$sql = sprintf("DELETE FROM %s WHERE user_id = '%u'", $this->db->prefix('avatar_user_link'), $user_id);
		$this->db->query($sql);
		$sql = sprintf("INSERT INTO %s (avatar_id, user_id) VALUES ('%u', '%u')", $this->db->prefix('avatar_user_link'), $avatar_id, $user_id);
		if (!$result =& $this->db->query($sql)) {
			return false;
		}
		return true;
	}

	/**
	 * Get an array of users linked to an avatar
	 * @param object $avatar
	 * @return array
	 */
	function getUser(&$avatar){
		$ret = array();

		/**
		 * @TODO: Change to if (!(class_exists($this->className) && $obj instanceof $this->className)) when going fully PHP5
		 */
		if (!is_a($avatar, 'xoopsavatar')) {
			return false;
		}

		$sql = "SELECT user_id FROM ".$this->db->prefix('avatar_user_link')." WHERE avatar_id='". (int) ($avatar->getVar('avatar_id'))."'";
		if (!$result = $this->db->query($sql)) {
			return $ret;
		}
		while ($myrow = $this->db->fetchArray($result)) {
			$ret[] =& $myrow['user_id'];
		}
		return $ret;
	}

	/**
	 * Get a list of avatars
	 * @param string $avatar_type
	 * @param integer $avatar_display
	 * @return array
	 */
	function getList($avatar_type = null, $avatar_display = null)
	{
		$criteria = new icms_core_CriteriaCompo();
		if (isset($avatar_type)) {
			$avatar_type = ($avatar_type == 'C') ? 'C' : 'S';
			$criteria->add(new icms_core_Criteria('avatar_type', $avatar_type));
		}
		if (isset($avatar_display)) {
			$criteria->add(new icms_core_Criteria('avatar_display', (int) ($avatar_display)));
		}
		$avatars =& $this->getObjects($criteria, true);
		$ret = array('blank.gif' => _NONE);
		foreach (array_keys($avatars) as $i) {
			$ret[$avatars[$i]->getVar('avatar_file')] = $avatars[$i]->getVar('avatar_name');
		}
		return $ret;
	}
}
?>