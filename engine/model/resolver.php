<?php
namespace sb\model;
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

class resolver
{
    private $base = null;
    private $notFound = null;
    private $defaultCntrl = null;
    private $instances = array();
    function __construct($default)
    {
        $this->base         = new \ReflectionClass('sb\controller\base');
        $this->notFound     = $this->getController('_notFound');
        $this->defaultCntrl = $this->getController($default);
        
    }
    function getController($classname)
    {
        if(is_null($classname))
            return $this->defaultCntrl;
        if($classname[0] == "_" && !is_null($this->notFound))
            return $this->notFound;
        $name = load::fetch('controller',$classname);
        if($name)
        {
            if(class_exists($name))
            {
                return $this->loadController($name);
            }
        }
        else
            return $this->notFound;
    }
    function loadController($classname)
    {
        try
        {
            $controller = new \ReflectionClass($classname);
        }
        catch (\ReflectionException $e)
        {
            throw new Exception("ReflectionException: ".$e->getMessage(),$e->getCode(),$e);
        }
        if($controller->isSubClassOf($this->base))
        {
            try
            {
                $this->instances[$classname] = $controller->newInstance();
            }
            catch( \ReflectionException $e)
            {
                throw new Exception("ReflectionException: ".$e->getMessage(),$e->getCode(),$e);
            }
            return $this->instances[$classname];
        }
        return $this->notFound;
    }
}
