<?php
/**
 * File filters.php
 *
 * @author Ulashev Roman <truetamtam@gmail.com>
 */

use FintechFab\ActionsCalc\Components\AuthHandler;

Route::filter('ff-actions-calc.auth', function () {

	if (AuthHandler::isTerminalRegistered() === false) {
		return Redirect::route('auth.registration');
	}
});
