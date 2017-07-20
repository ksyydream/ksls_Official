<?php
if (! defined ( 'BASEPATH' ))
	exit ( "no direct script access allowd" );
	// 以下是加载smarty的类文件
require_once (APPPATH . 'libraries/smarty/Smarty.class.php');
// 定义cismarty类，继承smarty类
class Cismarty extends Smarty {
	// 定义一个受保护的变量,
	protected $ci;
	protected $complie_dir;
	public $ext = 'html';
	public $dir = '';
	public $layout = 'layout/header';
	//public $index = 'layout/index';
	
	function __construct() {
		parent::__construct ();
		// 引用实例化CI,这里主要是将smarty的配置文件写到ci中，以方便程序管理
		$this->ci = & get_instance ();
		// var_dump($this->ci);die;
		// 加载ci的新建的smarty配置文件
		$this->ci->load->config ( 'smarty' );
		$this->cache_lifetime = $this->ci->config->item ( 'cache_lifetime' );
		$this->caching = $this->ci->config->item ( 'caching' );
		$this->template_dir = $this->ci->config->item ( 'template_dir' );
		$this->compile_dir = $this->ci->config->item ( 'compile_dir' );
		$this->cache_dir = $this->ci->config->item ( 'cache_dir' );
		$this->use_sub_dirs = $this->ci->config->item ( 'use_sub_dirs' );
		$this->left_delimiter = $this->ci->config->item ( 'left_delimiter' );
		$this->right_delimiter = $this->ci->config->item ( 'right_delimiter' );
		$this->left_delimiter = '{{';
		$this->right_delimiter = '}}';
	}
	
	/**
	 * 显示输出页面
	 * 
	 * @access public
	 * @return string
	 */
	public function show($tpl) {
		$this->assign ( 'jsFiles', $this->getJsHtml () );
		$this->assign ( 'jsFiles1', $this->getJsHtml ( 1 ) );
		$this->assign ( 'LAYOUT', $this->dir ? $this->dir . '/' . $tpl . '.' . $this->ext : $tpl . '.' . $this->ext );
		$this->display ( $this->layout . '.' . $this->ext );
		//$this->display ( $this->index . '.' . $this->ext );
	}
	/**
	 * 添加一个CSS文件包含
	 * 
	 * @param string $file
	 *        	文件名
	 * @access public
	 * @return void
	 */
	public function addCss($file) {
		if (strpos ( $file, '/' ) == false) {
			$file = config_item ( 'css' ) . $file;
		}
		$GLOBALS ['cssFiles'] [$file] = $file;
	}
	/**
	 * 添加一个JS文件包含
	 * 
	 * @param string $file
	 *        	文件名
	 * @access public
	 * @return void
	 */
	public function addJs($file, $btm = NULL) {
		if (strpos ( $file, '/' ) == false) {
			$file = config_item ( 'js' ) . $file;
		}
		if ($btm == NULL) {
			$GLOBALS ['jsfiles'] [$file] = $file;
		} else {
			$GLOBALS ['jsbtmfiles'] [$file] = $file;
		}
	}
	/**
	 * 取生成的包含JS HTML
	 * 
	 * @access public
	 * @return string
	 */
	public function getJsHtml($btm = NULL) {
		$html = '';
		$jsFile = $btm ? 'jsbtmfiles' : 'jsfiles';
		if (@$GLOBALS [$jsFile]) {
			foreach ( $GLOBALS [$jsFile] as $value ) {
				$html .= $this->jsInclude ( $value, true ) . "/n";
			}
			return $html;
		} else {
			return;
		}
	}
	
	/**
	 * 添加html标签
	 * @param string $tag 标签名
	 * @param mixed $attribute 属性
	 * @param string $content 内容
	 * @return string
	 */
	public function addTag($tag, $attribute = NULL, $content = NULL) {
		$this->js ();
		$html = '';
		$tag = strtolower ( $tag );
		$html .= '<' . $tag;
		if ($attribute != NULL) {
			if (is_array ( $attribute )) {
				foreach ( $attribute as $key => $value ) {
					$html .= ' ' . strtolower ( $key ) . '="' . $value . '"';
				}
			} else {
				$html .= ' ' . $attribute;
			}
		}
		if ($content) {
			$html .= '>' . $content . '</' . $tag . '>';
		} else {
			$html .= ' />';
		}
		$this->output .= $html;
		return $html;
	}
	
	/**
	 * 添加html文本
	 * @param string $content内容
	 * @return string
	 */
	public function addText($content) {
		$this->js ();
		$content = htmlentities ( $content );
		$this->output .= $content;
		return $content;
	}
	
	/**
	 * 添加js代码
	 * @param string $jscode js代码
	 * @param bool $end 是否关闭js 代码块
	 * @return void
	 */
	public function js($jscode = NULL, $end = false) {
		if (! $this->inJsArea && $jscode) {
			$this->output .= "/n<mce:script language='JavaScript' type='text/javascript'><!--
/n//<!--[CDATA[/n";
			$this->inJsArea = true;
		}
		if ($jscode == NULL && $this->inJsArea == true) {
			$this->output .= "/n//]]-->/n
// --></mce:script>/n";
			$this->inJsArea = false;
		} else {
			$this->output .= "/t$jscode/n";
			if ($end) {
				$this->js ();
			}
		}
		return;
	}
	/**
	 * 添加js提示代码
	 * 
	 * @param string $message
	 *        	提示内容
	 * @param bool $end
	 *        	是否关闭js 代码块
	 * @return void
	 */
	public function jsAlert($message, $end = false) {
		$this->js ( 'alert("' . strtr ( $message, '"', '//"' ) . '");', $end );
	}
	/**
	 * 添加js文件包含
	 * 
	 * @param string $fileName
	 *        	文件名
	 * @param bool $defer
	 *        	是否添加defer标记
	 * @return string
	 */
	public function jsInclude($fileName, $return = false, $defer = false) {
		if (! $return) {
			$this->js ();
		}
		$html = '<mce:script language="JavaScript" type="text/javascript" src="' . $fileName . '" mce_src="' . $fileName . '"' . (($defer) ? ' defer' : '') . '></mce:script>';
		if (! $return) {
			$this->output .= $html;
		} else {
			return $html;
		}
	}
	/**
	 * 添加css文件包含
	 * @param string $fileName 文件名
	 * @return string
	 */
	public function cssInclude($fileName, $return = false) {
		if (! $return) {
			$this->js ();
		}
		$html = '<LINK href="' . $fileName . '" mce_href="' . $fileName . '" rel=stylesheet>' . chr ( 13 );
		if (! $return) {
			$this->output .= $html;
		} else {
			return $html;
		}
	}
	/**
	 * 输出html内容
	 * @param bool $print 是否直接输出，可选，默认返回
	 * @return void
	 */
	public function output($print = false) {
		$this->js ();
		if ($print) {
			echo $this->output;
			$this->output = '';
			return;
		} else {
			$output = $this->output;
			$this->output = '';
			return $output;
		}
	}
}
?>