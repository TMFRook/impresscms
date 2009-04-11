<?php
/**
* xos_opal_Theme component class file
*
* @copyright	The Xoops project http://www.xoops.org/
* @license      http://www.fsf.org/copyleft/gpl.html GNU public license
* @author       Skalpa Keo <skalpa@xoops.org>
* @since        2.3.0
* @version		$Id$
* @package 		xos_opal
* @subpackage 	xos_opal_Theme
*/

/**
* xos_opal_ThemeFactory
*
* @author 		Skalpa Keo
* @package		xos_opal
* @subpackage	xos_opal_Theme
* @since        2.3.0
*/
class xos_opal_ThemeFactory {

	var $xoBundleIdentifier = 'xos_opal_ThemeFactory';
	/**
	 * Currently enabled themes (if empty, all the themes in themes/ are allowed)
	 * @var array
	 */
	var $allowedThemes = array();
	/**
	 * Default theme to instanciate if none specified
	 * @var string
	 */
	var $defaultTheme = 'iTheme';
	/**
	 * If users are allowed to choose a custom theme
	 * @var bool
	 */
	var $allowUserSelection = true;

	/**
	 * Instanciate the specified theme
	 */
	function &createInstance( $options = array(), $initArgs = array() ) {
		// Grab the theme folder from request vars if present
		if (@empty ( $options ['folderName'] )) {
			// xoops_theme_select still exists to keep compatibilitie ...
			if (($req = @$_REQUEST ['xoops_theme_select']) && $this->isThemeAllowed ( $req )) {
				$options ['folderName'] = $req;
				if (isset ( $_SESSION ) && $this->allowUserSelection) {
					$_SESSION [$this->xoBundleIdentifier] ['defaultTheme'] = $req;
				}
			} elseif (($req = @$_REQUEST ['theme_select']) && $this->isThemeAllowed ( $req )) {
				$options ['folderName'] = $req;
				if (isset ( $_SESSION ) && $this->allowUserSelection) {
					$_SESSION [$this->xoBundleIdentifier] ['defaultTheme'] = $req;
				}
			} elseif (isset ( $_SESSION [$this->xoBundleIdentifier] ['defaultTheme'] )) {
				$options ['folderName'] = $_SESSION [$this->xoBundleIdentifier] ['defaultTheme'];
			} elseif (@empty ( $options ['folderName'] ) || ! $this->isThemeAllowed ( $options ['folderName'] )) {
				$options ['folderName'] = $this->defaultTheme;
			}
			$GLOBALS['xoopsConfig']['theme_set'] = $options['folderName'];
		}
		$options['path'] = XOOPS_THEME_PATH . '/' . $options['folderName'];
		$inst =& new xos_opal_Theme();
		foreach ( $options as $k => $v ) $inst->$k = $v;
		$inst->xoInit();
		return $inst;
	}

	/**
	 * Checks if the specified theme is enabled or not
	 * @param string $name
	 * @return bool
	 */
	function isThemeAllowed( $name ) {
		return ( empty( $this->allowedThemes ) || in_array( $name, $this->allowedThemes ) );
	}

}

class xos_opal_Theme {
   /**
    * The name of this theme
    * @var string
    */
	var $folderName = '';
   /**
    * Physical path of this theme folder
    * @var string
    */
	var $path = '';
	var $url = '';

   /**
    * Whether or not the theme engine should include the output generated by php
    * @var string
    */
	var $bufferOutput = true;
	/**
	* Canvas-level template to use
	* @var string
	*/
	var $canvasTemplate = 'theme.html';
	/**
	* Content-level template to use
	* @var string
	*/
	var $contentTemplate = '';

	var $contentCacheLifetime = 0;
	var $contentCacheId = null;

	/**
	* Text content to display right after the contentTemplate output
	* @var string
	*/
	var $content = '';
	/**
	* Page construction plug-ins to use
	* @var array
	* @access public
	*/
	var $plugins = array( 'xos_logos_PageBuilder' );

	var $renderCount = 0;
	/**
	 * Pointer to the theme template engine
	 * @var XoopsTpl
	 */
	var $template = false;

	/**
	 * Array containing the document meta-information
	 * @var array
	 */
	var $metas = array(
		'http' => array(
			'Content-Script-Type' => 'text/javascript',
			'Content-Style-Type' => 'text/css',
		),
		'meta' => array(),
		'link' => array(),
		'script' => array(),
	);

	/**
	 * Array of strings to be inserted in the head tag of HTML documents
	 * @var array
	 */
	var $htmlHeadStrings = array();
	/**
	 * Custom variables that will always be assigned to the template
	 * @var array
	 */
	var $templateVars = array();

    /**
     * User extra information for cache id, like language, user groups
     *
     * @var boolean
     */
	var $use_extra_cache_id = true;

   /**#@-*/

	/**#@+ @tasktype 10 Initialization*/
	/**
	 * Initializes this theme
	 *
	 * Upon initialization, the theme creates its template engine and instanciates the
	 * plug-ins from the specified {@link $plugins} list. If the theme is a 2.0 theme, that does not
	 * display redirection messages, the HTTP redirections system is disabled to ensure users will
	 * see the redirection screen.
	 *
	 * @param array $options
	 * @return bool
	 */
   function xoInit( $options = array() ) {
		global $xoops;

		$this->path = XOOPS_THEME_PATH . '/' . $this->folderName;
		$this->url = XOOPS_THEME_URL . '/' . $this->folderName;

		$this->template =& new XoopsTpl();
		$this->template->currentTheme =& $this;
		$this->template->assign_by_ref( 'xoTheme', $this );

		global $xoopsConfig, $xoopsModule, $xoopsUser;
		$this->template->assign( array(
			'icms_style' => ICMS_URL.'/icms'.(( defined('_ADM_USE_RTL') && _ADM_USE_RTL )?'_rtl':'').'.css',
	    	'icms_theme' => $xoopsConfig['theme_set'],
	    	'icms_imageurl' => XOOPS_THEME_URL.'/'.$xoopsConfig['theme_set'].'/',
	    	'icms_themecss'=> xoops_getcss($xoopsConfig['theme_set']),
	    	'icms_requesturi' => htmlspecialchars( $_SERVER['REQUEST_URI'], ENT_QUOTES),
	    	'icms_sitename' => htmlspecialchars($xoopsConfig['sitename'], ENT_QUOTES),
	    	'icms_slogan' => htmlspecialchars($xoopsConfig['slogan'], ENT_QUOTES),
	    	'icms_dirname' => @$xoopsModule ? $xoopsModule->getVar( 'dirname' ) : 'system',
	    	'icms_banner' => $xoopsConfig['banners'] ? xoops_getbanner() : '&nbsp;',
	    	'icms_pagetitle' => isset($xoopsModule) && is_object($xoopsModule) ? $xoopsModule->getVar('name') : htmlspecialchars( $xoopsConfig['slogan'], ENT_QUOTES ),
	    	'xoops_theme' => $xoopsConfig['theme_set'],
	    	'xoops_imageurl' => XOOPS_THEME_URL.'/'.$xoopsConfig['theme_set'].'/',
	    	'xoops_themecss'=> xoops_getcss($xoopsConfig['theme_set']),
	    	'xoops_requesturi' => htmlspecialchars( $_SERVER['REQUEST_URI'], ENT_QUOTES),
	    	'xoops_sitename' => htmlspecialchars($xoopsConfig['sitename'], ENT_QUOTES),
	    	'xoops_slogan' => htmlspecialchars($xoopsConfig['slogan'], ENT_QUOTES),
	    	'xoops_dirname' => @$xoopsModule ? $xoopsModule->getVar( 'dirname' ) : 'system',
	    	'xoops_banner' => $xoopsConfig['banners'] ? xoops_getbanner() : '&nbsp;',
	    	'xoops_pagetitle' => isset($xoopsModule) && is_object($xoopsModule) ? $xoopsModule->getVar('name') : htmlspecialchars( $xoopsConfig['slogan'], ENT_QUOTES ),
	    ) );
	    if ( isset($xoopsUser) && is_object($xoopsUser) ) {
	        $this->template->assign( array(
	        	'icms_isuser' => true,
	        	'icms_userid' => $xoopsUser->getVar('uid'),
	        	'icms_uname' => $xoopsUser->getVar('uname'),
	        	'icms_isadmin' => $GLOBALS['xoopsUserIsAdmin'],
	        	'xoops_isuser' => true,
	        	'xoops_userid' => $xoopsUser->getVar('uid'),
	        	'xoops_uname' => $xoopsUser->getVar('uname'),
	        	'xoops_isadmin' => $GLOBALS['xoopsUserIsAdmin'],
	        ) );
	    } else {
	        $this->template->assign( array( 'icms_isuser' => false, 'icms_isadmin' => false, 'xoops_isuser' => false, 'xoops_isadmin' => false  ) );
	    }
		// Meta tags
	    $config_handler =& xoops_gethandler('config');
	    $criteria = new CriteriaCompo(new Criteria('conf_modid', 0));
	    $criteria->add(new Criteria('conf_catid', XOOPS_CONF_METAFOOTER));
	    $config = $config_handler->getConfigs($criteria, true);
	    foreach ( array_keys($config) as $i ) {
	    	$name = $config[$i]->getVar( 'conf_name', 'n' );
	    	$value = $config[$i]->getVar( 'conf_value', 'n' );
	    	if ( substr( $name, 0, 5 ) == 'meta_' ) {
	    		$this->addMeta( 'meta', substr( $name, 5 ), $value );
	    	} else {
	        	// prefix each tag with 'xoops_'
	        	$this->template->assign( "xoops_$name", $value );
	        	$this->template->assign( "icms_$name", $value );
	    	}
	    }

		if ( $this->bufferOutput ) {
			ob_start();
		}
		$GLOBALS['xoTheme'] =& $this;
		$GLOBALS['xoopsTpl'] =& $this->template;
		// Instanciate and initialize all the theme plugins
		foreach ( $this->plugins as $k => $bundleId ) {
			if ( !is_object( $bundleId ) ) {
				$this->plugins[$bundleId] =& new $bundleId();
				$this->plugins[$bundleId]->theme =& $this;
				$this->plugins[$bundleId]->xoInit();
				unset( $this->plugins[$k] );
			}
		}
		return true;
	}
	/**#@-*/

	/**
	 * Generate cache id based on extra information of language and user groups
	 *
	 * User groups other than anonymous should be detected to avoid disclosing group sensitive contents
	 *
	 * @param string	$cache_id		raw cache id
	 * @param string	$extraString	extra string
	 *
	 * @return string	complete cache id
	 */
	function generateCacheId($cache_id, $extraString = '') {
		static $extra_string;

		if (!$this->use_extra_cache_id) {
			return $cache_id;
		}

		if (empty($extraString)) {
			if (empty($extra_string)) {
				global $xoopsUser, $xoopsConfig;

				// Generate language section
				$extra_string = $xoopsConfig['language'];

				// Generate group section
				if ( !@is_object( $xoopsUser ) ) {
					$extra_string .= '|' . XOOPS_GROUP_ANONYMOUS;
				} else {
					$groups = $xoopsUser->getGroups();
					sort($groups);
					// Generate group string for non-anonymous groups,
					// XOOPS_DB_PASS and XOOPS_DB_NAME (before we find better variables) are used to protect group sensitive contents
					$extra_string .= '|' . implode(",", $groups).substr( md5(XOOPS_DB_PASS.XOOPS_DB_NAME), 0, strlen(XOOPS_DB_USER) * 2 );
				}
			}
			$extraString = $extra_string;
		}
		$cache_id .= '|' . $extraString;

		return $cache_id;
	}


  /**
   * Checks cache for a changed version of the template and renders template
   * @return  bool
   */
	function checkCache() {
		global $xoopsModule, $xoopsLogger;

		if ( $_SERVER['REQUEST_METHOD'] != 'POST' && $this->contentCacheLifetime ) {
			$template = $this->contentTemplate ? $this->contentTemplate : 'db:system_dummy.html';
			$dirname = $xoopsModule->getVar( 'dirname', 'n' );

			$this->template->caching = 2;
			$this->template->cache_lifetime = $this->contentCacheLifetime;
			$uri = str_replace( XOOPS_URL, '', $_SERVER['REQUEST_URI'] );
			// Clean uri by removing session id
			if (defined('SID') && SID && strpos($uri, SID)) {
				$uri = preg_replace("/([\?&])(".SID."$|".SID."&)/", "\\1", $uri);
			}
			$this->contentCacheId = $this->generateCacheId($dirname . '|' . $uri);

			if ( $this->template->is_cached( $template, $this->contentCacheId ) ) {
        $xoopsLogger->addExtra( $template, sprintf ( _REGENERATES, $this->contentCacheLifetime ) );
				$this->render( null, null, $template );
				return true;
      }
		}
		return false;
	}

	/**
	 * Render the page
	 *
	 * The theme engine builds pages from 2 templates: canvas and content.
	 *
	 * A module can call this method directly and specify what templates the theme engine must use.
	 * If render() hasn't been called before, the theme defaults will be used for the canvas and
	 * page template (and xoopsOption['template_main'] for the content).
	 *
	 * @param string $canvasTpl		The canvas template, if different from the theme default
	 * @param string $pageTpl		The page template, if different from the theme default (unsupported, 2.3+ only)
	 * @param string $contentTpl	The content template
	 * @param array	 $vars			Template variables to send to the template engine
	 */
	function render( $canvasTpl = null, $pageTpl = null, $contentTpl = null, $vars = array() ) {
		global $xoops, $xoopsLogger, $xoopsOption;

		if ( $this->renderCount ) {
			return false;
		}
		$xoopsLogger->startTime( 'Page rendering' );

		// @internal: Lame fix to ensure the metas specified in the xoops config page don't appear twice
		$old = array( 'robots', 'keywords', 'description', 'rating', 'author', 'copyright' );
		foreach ( $this->metas['meta'] as $name => $value ) {
			if ( in_array( $name, $old ) ) {
				$this->template->assign( "xoops_meta_$name", htmlspecialchars( $value, ENT_QUOTES ) );
				$this->template->assign( "icms_meta_$name", htmlspecialchars( $value, ENT_QUOTES ) );
				unset( $this->metas['meta'][$name] );
			}
		}

		if ( $canvasTpl )		$this->canvasTemplate	= $canvasTpl;
		if ( $contentTpl )		$this->contentTemplate	= $contentTpl;

		if ( !empty( $vars ) ) {
			$this->template->assign( $vars );
		}
		if ( $this->contentTemplate ) {
			$this->content = $this->template->fetch( $this->contentTemplate, $this->contentCacheId );
		}
		if ( $this->bufferOutput ) {
			$this->content .= ob_get_contents();
			ob_end_clean();
		}
		$this->template->assign_by_ref( 'xoops_contents', $this->content );
		$this->template->assign_by_ref( 'icms_contents', $this->content );

		$header = empty($xoopsOption['xoops_module_header']) ? $this->template->get_template_vars( 'xoops_module_header' ) : $xoopsOption['xoops_module_header'];
		$this->template->assign( 'xoops_module_header', $header . "\n" . $this->renderMetas( null, true ) );
		$this->template->assign( 'icms_module_header', $header . "\n" . $this->renderMetas( null, true ) );

		if ( !empty($xoopsOption['xoops_pagetitle']) ) {
			$this->template->assign( 'xoops_pagetitle', $xoopsOption['xoops_pagetitle'] );
			$this->template->assign( 'icms_pagetitle', $xoopsOption['xoops_pagetitle'] );
		}

		// Do not cache the main (theme.html) template output
		$this->template->caching = 0;
		$this->template->display( $this->path . '/' . $this->canvasTemplate );

		$this->renderCount++;
		$xoopsLogger->stopTime( 'Page rendering' );
	}

	/**#@+ @tasktype 20 Manipulating page meta-information*/
  /**
  * Adds script code to the document head
  *
  * This methods allows the insertion of an external script file (if $src is provided), or
  * of a script snippet. The file URI is parsed to take benefit of the theme resource
  * overloading system.
  *
  * The $attributes parameter allows you to specify the attributes that will be added to the
  * inserted <script> tag. If unspecified, the <var>type</var> attribute value will default to
  * 'text/javascript'.
  *
  * <code>
  * // Add an external script using a physical path
  * $theme->addScript( 'www/script.js', null, '' );
  * $theme->addScript( 'modules/newbb/script.js', null, '' );
  * // Specify attributes for the <script> tag
  * $theme->addScript( 'mod_xoops_SiteManager#common.js', array( 'type' => 'application/x-javascript' ), '' );
  * // Insert a code snippet
  * $theme->addScript( null, array( 'type' => 'application/x-javascript' ), 'window.open("Hello world");' );
  * </code>
  *
  * @param string $src path to an external script file
  * @param array $attributes hash of attributes to add to the <script> tag
  * @param string $content Code snippet to output within the <script> tag
  *
  * @return void
  **/
  function addScript( $src = '', $attributes = array(), $content = '' ) {
  	global $xoops;
  	if ( empty( $attributes ) )     		$attributes = array();
    if ( !empty( $src ) ) 					$attributes['src'] = $xoops->url( $this->resourcePath( $src ) );
    if ( !empty( $content ) )				$attributes['_'] = $content;
    if ( !isset( $attributes['type'] ) ) 	$attributes['type'] = 'text/javascript';
    $this->addMeta( 'script', $src, $attributes );
  }

  /**
   * Add StyleSheet or CSS code to the document head
   * @param string $src path to .css file
   * @param array $attributes name => value paired array of attributes such as title
   * @param string $content CSS code to output between the <style> tags (in case $src is empty)
   *
   * @return void
   **/
  function addStylesheet( $src = '', $attributes = array(), $content = '' ) {
  	global $xoops;
  	if ( empty( $attributes ) )     		$attributes = array();
    if ( !empty( $src ) ) 					$attributes['href'] = $xoops->url( $this->resourcePath( $src ) );
    if ( !isset($attributes['type']) ) 		$attributes['type'] = 'text/css';
    if ( !empty( $content ) ) 				$attributes['_'] = $content;
  	$this->addMeta( 'stylesheet', $src, $attributes );
  }



	/**
   * Add a <link> to the header
   * @param string	$rel		Relationship from the current doc to the anchored one
   * @param string	$href		URI of the anchored document
   * @param array		$attributes	Additional attributes to add to the <link> element
   */
	function addLink( $rel, $href = '', $attributes = array() ) {
		global $xoops;
  	if ( empty( $attributes ) )     		$attributes = array();
		if ( !empty( $href ) ) 					$attributes['href'] = $href;
		$this->addMeta( 'link', $rel, $attributes );
	}


	/**
	 * Set a meta http-equiv value
	 */
	function addHttpMeta( $name, $value = null ) {
		if ( isset($value) ) {
			return $this->addMeta( 'http', $name, $value );
		}
		unset( $this->metas['http'][$name] );
	}

	/**
	 * Change output page meta-information
	 */
  function addMeta( $type = 'meta', $name = '', $value = '' ) {
    if ( !isset( $this->metas[$type] ) ) {
    	$this->metas[$type] = array();
    }

  	if ( isset($name) ) {
  	$this->metas[$type][$name] = $value;
    } else {
    	$this->metas[$type][] = 	$value;
    }
    return $value;
  }


  /**
   * Puts $content into the htmlheadstrings array
   *
   * @param   string  $params
   * @param   string  $content    content to put in the htmlheadstrings array
   * @param   string  &$smarty
   * @param   string  &$repeat
   */
	function headContent( $params, $content, &$smarty, &$repeat ) {
		if ( !$repeat ) {
			$this->htmlHeadStrings[] = $content;
		}
	}


  /**
   * Render the meta content in the metas array (carefull Recursive!)
   *
   * @param   string  $type     what type of metacontent is it
   * @param   string  $return   will we return to the calling function (just default setting)
   * @return  bool
   */
	function renderMetas( $type = null, $return = false ) {
		$str = '';
		if ( !isset($type) ) {
			foreach ( array_keys($this->metas) as $type ) {
				$str .= $this->renderMetas( $type, true );
			}
			$str .= implode( "\n", $this->htmlHeadStrings );
		} else {
			switch ( $type ) {
			case 'script':
				foreach ( $this->metas[$type] as $attrs ) {
					$str .= '<script' . $this->renderAttributes( $attrs ) . ">\n";
					if ( @$attrs['_'] ) {
						$str .= "\n//<![CDATA[\n" . $attrs['_'] . "\n//]]>";
					}
					$str .= "</script>\n";
				}
				break;
			case 'link':
				foreach ( $this->metas[$type] as $rel => $attrs ) {
					$str .= '<link rel="' . $rel . '"' . $this->renderAttributes( $attrs ) . " />\n";
				}
				break;
			case 'stylesheet':
				foreach ( $this->metas[$type] as $attrs ) {
					if ( @$attrs['_'] ) {
						$str .= '<style' . $this->renderAttributes($attrs) . ">\n" . $attrs['_'] . "\n</style>";
					} else {
						$str .= '<link rel="stylesheet"' . $this->renderAttributes($attrs) . " />\n";
					}
				}
				break;
			case 'http':
				foreach ( $this->metas[$type] as $name => $content ) {
					$str .= '<meta http-equiv="' . htmlspecialchars( $name, ENT_QUOTES ) . '" content="' . htmlspecialchars( $content, ENT_QUOTES) . "\" />\n";
				}
				break;
			default:
				foreach ( $this->metas[$type] as $name => $content ) {
					$str .= '<meta name="' . htmlspecialchars( $name, ENT_QUOTES ) . '" content="' . htmlspecialchars( $content, ENT_QUOTES) . "\" />\n";
				}
				break;
			}
		}
		if ( $return ) {
			return $str;
		}
		echo $str;
		return true;
	}

	/**
	 * Generates a unique element ID
	 * @param string $tagName
	 * @return string
	 */
	function genElementId( $tagName = 'xos' ) {
		static $cache = array();
		if ( !isset( $cache[ $tagName ] ) ) {
			$cache[$tagName] = 1;
		}
		return $tagName . '-' . $cache[$tagName]++;
	}

	/**
	 * Transform an attributes collection to an XML string
	 * @param array $coll
	 * @return string
	 */
	function renderAttributes( $coll ) {
		$str = '';
		foreach ( $coll as $name => $val ) {
			if ( $name != '_' ) {
				$str .= ' ' . $name . '="' . htmlspecialchars( $val, ENT_QUOTES ) . '"';
			}
		}
		return $str;
	}

	/**
	 * Return a themable file resource path
	 *
	 * @param string $path
	 * @return string
	 */
	function resourcePath( $path ) {
		global $xoops;
		if ( substr( $path, 0, 1 ) == '/' ) {
			$path = substr( $path, 1 );
		}
		if ( file_exists( "$this->path/$path" ) ) {
			return "themes/$this->folderName/$path";
		}
		return $path;
	}




}


?>