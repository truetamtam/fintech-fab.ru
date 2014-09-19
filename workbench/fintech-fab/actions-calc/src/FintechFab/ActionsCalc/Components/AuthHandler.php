<?php

namespace FintechFab\ActionsCalc\Components;

use FintechFab\ActionsCalc\Models\Terminal;
use Config;
use Hash;

/**
 * Class AuthHandler
 *
 * @author Ulashev Roman <truetamtam@gmail.com>
 */
class AuthHandler
{

	/**
	 * Compare signatures
	 *
	 * @param $aRequestData
	 *
	 * @return bool
	 */
	public static function checkSign($aRequestData)
	{
		/** *@var Terminal $terminal */
		$terminal = Terminal::find($aRequestData['terminal_id'], ['id', 'key']);

		if (is_null($terminal)) {
			return false;
		}

		$signature = sha1($terminal->id . '|' . $aRequestData['event_sid'] . '|' . $terminal->key);

		return $signature == $aRequestData['auth_sign'];
	}

	/**
	 * Authenticate client by hist config terminal_id
	 *
	 * @return bool
	 */
	public static function isTerminalRegistered()
	{
		$iTerminalId = Config::get('ff-actions-calc::terminal_id');

		return !is_null(Terminal::find($iTerminalId, ['id']));
	}

	/**
	 * Get terminal id.
	 *
	 * @return int
	 */
	public static function getTerminalId()
	{
		return (int)Config::get('ff-actions-calc::terminal_id');
	}

	/**
	 * Get password.
	 *
	 * @param $sPassword
	 *
	 * @return string
	 */
	public static function getHashedPassword($sPassword)
	{
		return Hash::make(trim($sPassword));
	}

	/**
	 * Generate terminal key.
	 *
	 * @param $sName
	 *
	 * @return string
	 */
	public static function getKey($sName)
	{
		return sha1($sName . microtime(true) . rand(10000, 90000));
	}
}