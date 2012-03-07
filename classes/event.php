<?php
/**
 * Part of the Fuel framework.
 *
 * @package    Fuel
 * @version    1.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2012 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Core;

/**
 * Event Class
 *
 * @package		Fuel
 * @category	Core
 * @author		Eric Barnes
 * @author		Harro "WanWizard" Verton
 */
class Event
{

	/**
	 * @var	array	An array of listeners
	 */
	protected static $_events = array();

	// --------------------------------------------------------------------

	/**
	 * Register
	 *
	 * Registers a Callback for a given event
	 *
	 * @access	public
	 * @param	string	The name of the event
	 * @param	mixed	Callback information
	 * @param	int		Priority (default = 10, lowest value has more priority)orit
	 * @return	bool
	 */
	public static function register($event, $callback, $priority = 10)
	{
		// if the arguments are valid, register the event
		if (is_string($event) and is_callable($callback) and is_int($priority))
		{
			// make sure we have an array for this event
			isset(static::$_events[$event]) or static::$_events[$event] = array();
			isset(static::$_events[$event][$priority]) or static::$_events[$event][$priority] = array();

			// store the callback on the call stack
			static::$_events[$event][$priority][] = $callback;

			// and report success
			return true;
		}
		else
		{
			// can't register the event
			return false;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Trigger
	 *
	 * Triggers an event and returns the results.  The results can be returned
	 * in the following formats:
	 *
	 * 'array'
	 * 'json'
	 * 'serialized'
	 * 'string'
	 *
	 * @access	public
	 * @param	string	The name of the event
	 * @param	mixed	Any data that is to be passed to the listener
	 * @param	string	The return type
	 * @return	mixed	The return of the listeners, in the return type
	 */
	public static function trigger($event, $data = '', $return_type = 'string')
	{
		$calls = array();

		// check if we have events registered
		if (static::has_events($event))
		{
			// order them by priority (lowest key is higest priority)
			ksort(static::$_events[$event]);

			// process them
			foreach (static::$_events[$event] as $events)
			{
				foreach ($events as $event)
				{
					// call the callback event
					if (is_callable($event))
					{
						$calls[] = call_user_func($event, $data);
					}
				}
			}
		}

		return static::_format_return($calls, $return_type);
	}

	// --------------------------------------------------------------------

	/**
	 * method called by register_shutdown_event
	 *
	 * @access	public
	 * @param	void
	 * @return	void
	 */
	public static function shutdown()
	{
		if (static::has_events('shutdown'))
		{
			// trigger the shutdown events
			static::trigger('shutdown', '', 'none');
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Has Listeners
	 *
	 * Checks if the event has listeners
	 *
	 * @access	public
	 * @param	string	The name of the event
	 * @return	bool	Whether the event has listeners
	 */
	public static function has_events($event)
	{
		if (isset(static::$_events[$event]) and count(static::$_events[$event]) > 0)
		{
			return true;
		}
		return false;
	}

	// --------------------------------------------------------------------

	/**
	 * Format Return
	 *
	 * Formats the return in the given type
	 *
	 * @access	protected
	 * @param	array	The array of returns
	 * @param	string	The return type
	 * @return	mixed	The formatted return
	 */
	protected static function _format_return(array $calls, $return_type)
	{
		switch ($return_type)
		{
			case 'array':
				return $calls;
				break;
			case 'json':
				return json_encode($calls);
				break;
			case 'none':
				return;
			case 'serialized':
				return serialize($calls);
				break;
			case 'string':
				$str = '';
				foreach ($calls as $call)
				{
					$str .= $call;
				}
				return $str;
				break;
			default:
				return $calls;
				break;
		}

		return false;
	}
}

