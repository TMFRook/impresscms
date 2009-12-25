<?php/** * JSVI adapter for ImpressCMS * * @copyright	ImpressCMS http://www.impresscms.org/ * @license		http://www.fsf.org/copyleft/gpl.html GNU public license * @author		MekDrop <mekdrop@gmail.com> * @since		1.2 * @package		sourceeditor */if (!defined("ICMS_ROOT_PATH")) {    die("ICMS root path not defined");}require_once ICMS_ROOT_PATH."/class/xoopsform/formdhtmltextarea.php";/** * Pseudo class * * @author	    MekDrop * @copyright	copyright (c) 2009 ImpressCMS.org */class IcmsSourceEditorJSVI extends XoopsFormTextArea{	var $rootpath = "";	    var $_width = "100%";    var $_height = "400px";	/**	 * Constructor	 *     * @param	array   $configs  Editor Options     * @param	binary 	$checkCompatible  true - return false on failure	 */	function __construct($configs, $checkCompatible = false)	{		$current_path = __FILE__;		if ( DIRECTORY_SEPARATOR != "/" ) $current_path = str_replace( strpos( $current_path, "\\\\", 2 ) ? "\\\\" : DIRECTORY_SEPARATOR, "/", $current_path);		$this->rootpath = substr(dirname($current_path), strlen(ICMS_ROOT_PATH));		if(is_array($configs)) {			$vars = array_keys(get_object_vars($this));			foreach($configs as $key => $val){				if(in_array("_".$key, $vars)) {					$this->{"_".$key} = $val;				}else{					$this->config[$key] = $val;				}			}		}		if($checkCompatible && !$this->isCompatible()) {			return false;		}		$this->XoopsFormTextArea("", @$this->_name, @$this->_value);		parent::setExtra("style='width: ".$this->_width."; height: ".$this->_height.";'");	}		/**	 * Check if compatible	 *     * @return	 */	function isCompatible()	{		return is_readable(ICMS_ROOT_PATH . $this->rootpath. "/vi.php");	}	function render() {		$ret = parent::render();		$ret .= '<script language="javascript" type="text/javascript" src="'.ICMS_URL.$this->rootpath.'/editor.js"></script><script language="javascript" type="text/javascript">	var jsvie = new jsvi_editor(\''.ICMS_URL.$this->rootpath.'/\', \''.$this->_name.'\',\''.$this->_width.'\',\''.$this->_height.'\');	JSVI_init();</script>';		return $ret;	}}?>