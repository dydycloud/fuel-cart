<?php
/**
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package		Fuel
 * @version		1.0
 * @author		Fuel Development Team
 * @license		MIT License
 * @copyright	2010 - 2011 Fuel Development Team
 * @link		http://fuelphp.com
 */


namespace Cart;

/**
 * Exception for invalid cart instance retrieval.
 */
class InvalidCartException extends \Fuel_Exception {}
class InvalidCartItemException extends \Fuel_Exception {}


abstract class Cart {

	protected static $default = array();
	protected static $instances = array();
	protected static $instance = null;
	
	public static function factory($cart = 'default')
	{
		if(array_key_exists($cart, static::$instances))
		{
			return static::$instances[$cart];
		}
		
		$config = \Config::get('cart.carts.'.$cart);
		
		if( ! is_array($config))
		{
			throw new \InvalidCartException('Could not instantiate card: '.$cart);
		}
		
		$config = $config + static::$default;
		
		$config['cookie_name'] = \Config::get('cart.cookie_prefix', '').$cart.\Config::get('cart.cookie_suffix');
		
		$instance = new \Cart_Basket($config);
		
		static::$instances[$cart] =& $instance;
				
		\Event::register('shutdown', array($instance, 'save'));

		return static::$instances[$cart];
	}
	
	public static function instance($cart = 'default')
	{
		if(array_key_exists($cart, static::$instances))
		{
			return static::$instances[$cart];
		}
		return static::factory($cart);
	}
	
	public static function __callStatic($method, $args)
	{
		static::$instance or static::$instance = static::instance();
		
		if(method_exists(static::$instance, $method))
		{
			return call_user_func_array(array(static::$instance, $method), $args);
		}
		
		throw new \BadMethodCallException('Invalid method: '.get_called_class().'::'.$method);
	}

	public static function _init()
	{
		\Config::load('cart', true);
		
		static::$default = \Config::get('cart.default');
		
		class_exists('\\Cookie', true);
	}
	
}