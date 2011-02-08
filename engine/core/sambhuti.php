<?php
namespace sb;
if ( ! defined('SB_ENGINE_PATH')) exit('No direct script access allowed');
/**
 * @package sambhuti
 * @author Piyush Mishra<me[at]piyushmishra[dot]com>
 */
final class sambhuti
{
	private static $_lazy_paths=array();
	private static $_thirdparty=array();
	private static $_pimple;
	private function __construct(){}
	public static function run($sb_apps,$paths=null,$thirdparty=null)
	{
		self::setlazypaths($paths,$thirdparty);
		spl_autoload_register(array(__CLASS__, 'autoload' ));
		self::pimpleinit();
		self::setapppath($sb_apps);
		self::sbinit();		
	}
	public static function pimple()
	{
		return self::$_pimple;
	}

	public static function setapppath($sb_apps)
	{
		self::$_pimple->_app_paths=$sb_apps;
		self::$_pimple->uri;
		if(! defined('SB_APP_PATH') || SB_APP_PATH=='/')
		exit('Please check your $sb_apps array.');
	}
	private static function pimpleinit()
	{
		self::$_pimple= new \Pimple();
		self::$_pimple->config=self::$_pimple->asShared(function($pimple)
			{
				$confinst= new config($pimple->_conf);
				unset($pimple->_conf);
				return $confinst;
				});
		self::$_pimple->uri=self::$_pimple->asShared(function($pimple)
			{
				$uriinst= new uri($pimple->_app_paths);
				unset($pimple->_app_paths);
				return $uriinst;
			});
		self::$_pimple->load=function($pimple)
			{
				return new load($pimple->config);
			};
		self::$_pimple->controller=self::$_pimple->asShared(function($pimple)
			{
				return new $pimple->_cname();
			});
	}
	static function ping($type)
	{
		switch($type)
		{
			case 'SBbase':
			return array('uri'=>self::$_pimple->uri,'load'=>self::$_pimple->load);
			break;
			default:
			throw new SBException(__CLASS__,"Unknown ping from ".$type);
		}
	}
	private static function sbinit($asd="aaa")
	{
		require_once SB_APP_PATH.'config.php';
		if(isset($app_config) && is_array($app_config))
			self::$_pimple->_conf=$app_config;
		unset($app_config);
		self::addlazypath(self::$_pimple->config->get('lazy_path'));
		$segments=self::$_pimple->uri->total_segments();
		//print_r(self::$_pimple->uri->segment_array());
		self::$_pimple->_cname=($segments) ? self::$_pimple->uri->segment(1) : self::$_pimple->config->get('default_controller');		
		try{
			$controller=self::$_pimple->controller;
			$args=array();
			if($segments>1)
			{
				$method=self::$_pimple->uri->segment(2);
				if(method_exists($controller,$method))
				{
					if($segments>2)
					{
						$args=self::$_pimple->uri->segment_array();
						array_shift($args);
						array_shift($args);
					}	
				}else
					throw new SBException($method,404,"Not Found");
			}else
				$method='index';
			if(is_callable(array($controller,$method)))
				call_user_func_array(array($controller,$method),$args);
			
			
		}
		catch(SBException $e)
		{
			
			echo "Exception:".$e->getclassname()." Not found.";

		}
	}
	
	private static function setlazypaths($paths=null,$thirdparty=null)
	{
		self::$_lazy_paths=array
		(
			'sb'=>array
			(
				SB_ENGINE_PATH.'core/',
				SB_ENGINE_PATH.'lib/'
			)
		);
		self::$_thirdparty=array
		(
			'Pimple'=>SB_ENGINE_PATH.'thirdparty/Pimple/lib/Pimple.php',
		);
		if(isset($paths) && is_array($paths))
			self::$_lazy_paths=array_merge_recursive(self::$_lazy_paths,$paths);
		if(isset($thirdparty) && is_array($thirdparty))
			self::$_thirdparty=array_merge_recursive(self::$_thirdparty,$thirdparty);
	}
	public static function getthirdparty()
	{
		return self::$_thirdparty;
	}
	public static function addthirdparty($thirdparty)
	{
		if(is_array($thirdparty))
			self::$_thirdparty=array_merge_recursive(self::$_thirdparty,$thirdparty);
	}
	public static function addlazypath($path,$ulta=false)
	{
		if(is_array($path))
			if(is_bool($ulta) && $ulta)
				self::$_lazy_paths=array_merge_recursive($path,self::$_lazy_paths);
			else
				self::$_lazy_paths=array_merge_recursive(self::$_lazy_paths,$path);
	}
	public static function autoload($class,$type="any")
	{
		$break_array = explode('\\',$class);
		$classname=array_pop($break_array);
		$name=implode($break_array,'\\');
		$namespace = ($name=='') ? 'global' : $name;		
		if(class_exists($classname, false))
			return;
		
		if(array_key_exists($namespace,self::$_lazy_paths))
		{
			foreach(self::$_lazy_paths[$namespace] as $key=>$path) 
			{
				if($type=='any' || $key==$type)
					$file_name = $path.$classname.'.php';
					if(file_exists($file_name))
					{
						require_once $file_name;
						return true;
					}
			}
			
		}
		elseif(array_key_exists($class,self::$_thirdparty))
		{
			require_once self::$_thirdparty[$class];
			return true;
		}
			throw new SBException($classname,404,"Not Found");
	}
	public static function stop()
	{
		self::$_lazy_paths=array();
		self::$_thirdparty=array();
		spl_autoload_unregister(array(__CLASS__, 'autoload' ));
	}
	
	
}


/**
 * End of file loader
 */