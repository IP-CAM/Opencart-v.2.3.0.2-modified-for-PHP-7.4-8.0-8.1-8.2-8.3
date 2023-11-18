<?php
/**
 * @package		OpenCart
 * @author		Daniel Kerr
 * @copyright	Copyright (c) 2005 - 2023, OpenCart, Ltd. (https://www.opencart.com/)
 * @license		https://opensource.org/licenses/GPL-3.0
 * @link		https://www.opencart.com
*/

/**
* Log class
*/
class Log {
	private $file;

	/**
	 * Constructor
	 *
	 * @param	string	$filename
 	*/
	public function __construct($filename) {
		$this->file = DIR_LOGS . $filename;
	}

	/**
     * 
     *
     * @param	string	$message
     */
	public function write($message) {
		file_put_contents($this->file, date('Y-m-d H:i:s') . ' - ' . print_r($message, true) . "\n", FILE_APPEND);
	}
}