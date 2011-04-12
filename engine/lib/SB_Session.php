<?php
namespace sb;
if ( ! defined('SB_ENGINE_PATH')) exit('No direct script access allowed');
/**
 * Sambhuti
 * Copyright (C) 2010-2011  Piyush Mishra
 *
 * License:
 * This file is part of Sambhuti (http://sambhuti.org)
 * 
 * Sambhuti is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * Sambhuti is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with Sambhuti.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package Sambhuti
 * @author Piyush Mishra <me[at]piyushmishra[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2010-2011 Piyush Mishra
 */

final class session
{
	private static $_session=array();
	public $ip;
	public function __construct()
	{
		session_start();
		$this->ip=filter_input(INPUT_SERVER,'REMOTE_ADDR');
		if(isset($_SESSION[$this->ip]))
		{
			self::$_session=$_SESSION[$this->ip];
		}
	}
	public function set($key,$val)
	{
	 	self::$_session[$key]=$val;
	}
	
	public function get($key)
	{
		if(isset(self::$_session[$key]))
			return 	self::$_session[$key];		
	}
	public function destroy()
	{
		self::$_session=null;
		session_destroy();
	}
	private function log()
	{
	
	}
	function __destruct()
	{
		if(isset(self::$_session))
		{
			$this->log();
			$_SESSION[$this->ip]=self::$_session;
		}
	}
}

/**
 *End of file Session
 */
