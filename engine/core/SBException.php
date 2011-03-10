<?php
namespace sb;
if ( ! defined('SB_ENGINE_PATH')) exit('No direct script access allowed');
/**
 * @package sambhuti
 * @author Piyush Mishra<me[at]piyushmishra[dot]com>
 */

class SBException extends \Exception
{
	private $_classname;
	private $_code;
	function __construct($message,$code=0,$classname="")
	{
		parent::__construct($message);
		$this->_classname=$classname;
		$this->_code=$code;
		
	}
	function getClassName()
	{
		return $this->_classname;
	}
}

/**
 * End of file SBException
 */
