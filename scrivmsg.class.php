<?php


class ScrivMsg {
	
	
	/**
	 * An array of messages in type => message format.
	 * An example array might be:
	 * <?php
	 *  $messages = array(
     *              'success'  	=> Array
     *              				[0]	=>'Welcome',
     *              'info'  	=> Array
     *              				[0] => 'Sorry, try again'
     *              'error'     => Array 
     *              				[0] => 'could not process the file'
     *              );
     * ?>
     *
	 * @var array
	 * @access private
	 */
	private static $messages = array();
	
	
	
	
	/**
	 * Retrieve the messages for a given $type or all messages.
	 * Called statically. 
	 *
	 * <code>
	 *  ScrivMsg::get_messages($type);
	 * </code>
	 *
     * @param string $type - The type of message - 'success', 'info', or 'error'.
	 * @access public
	 */
	public static function get_messages($type=null) {
		
		if(isset($type)) {
			
			if(array_key_exists($type, self::$messages)) {
				return self::$messages[$type];
			} else {
				return array();
			}
		} else {
			return self::$messages;
		}
	}

	
	/**
	 * Set a message. Called statically.
	 * 
	 * <code>
	 *  ScrivMsg::set_message($type, $message);
	 * </code>
	 * 
	 * @param string $type
	 * @param string $message
	 */
	public static function set_message($type, $message) {
		
		if(!isset(self::$messages[$type])) {
			self::$messages[$type] = array();
		}
		self::$messages[$type][] = $message;
	}
	
}

