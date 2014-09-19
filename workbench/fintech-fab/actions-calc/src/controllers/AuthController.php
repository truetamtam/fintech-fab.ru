<?php

namespace FintechFab\ActionsCalc\Controllers;

use Config;
use Exception;
use FintechFab\ActionsCalc\Components\AuthHandler;
use FintechFab\ActionsCalc\Components\Validators;
use FintechFab\ActionsCalc\Models\Terminal;
use Hash;
use Input;
use Redirect;
use Session;
use Validator;
use Request;
use View;

/**
 * Class AuthController
 *
 * @author Ulashev Roman <truetamtam@gmail.com>
 */
class AuthController extends BaseController
{
	/**
	 * @var string
	 */
	protected $layout = 'main';

	/**
	 * Registering new client terminal.
	 *
	 * @return \Illuminate\Http\RedirectResponse|View
	 */
	public function registration()
	{
		// data
		$aRequestData = Input::all();

		// validation
		$oValidator = Validators::validate($aRequestData, Validators::getTerminalValidators());

		if ($oValidator->fails()) {
			return Redirect::to(route('auth.registration'))->withInput($aRequestData)->withErrors($oValidator);
		}

		// data valid here
		$aRequestData['password'] = AuthHandler::getHashedPassword($aRequestData['password']);
		$aRequestData['key'] = (strlen($aRequestData['key']) < 1) ?
			AuthHandler::getKey($aRequestData['name']) : $aRequestData['key'];

		$oNewTerminal = new Terminal;
		$oNewTerminal->fill($aRequestData);
		$oNewTerminal->password = $aRequestData['password'];

		try {
			$oNewTerminal->save();
		} catch (Exception $e) {
			return $this->error($e->getMessage());
		}

		return Redirect::route('calc.manage');
	}

	/**
	 * Updating terminal profile information.
	 *
	 * @return $this|array|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
	 */
	public function profile()
	{
		$aRequestData = Input::all();
		/** @var Terminal $oTerminal */
		$oTerminal = Terminal::find($this->iTerminalId);

		// on GET only opening, and fill in
		if (Request::isMethod('GET')) {
			return View::make('ff-actions-calc::auth.profile', ['terminal' => $oTerminal]);
		}

		$oValidator = Validators::validate($aRequestData, Validators::getProfileValidators());

		// validation fails
		if ($oValidator->fails()) {
			$oErrors = $oValidator->errors();

			return Redirect::to(route('auth.profile'))->withInput($aRequestData)->withErrors($oErrors);
		}

		// password change
		if (isset($aRequestData['change_password']) && $aRequestData['change_password'] == 1) {

			$oValidator = Validators::validate($aRequestData, Validators::getProfileChangePassValidators());

			if ($oValidator->fails()) {

				$oErrors = $oValidator->errors();

				return Redirect::to(route('auth.profile'))->withInput($aRequestData)->withErrors($oErrors);
			}

			// current password check
			if (Hash::check($aRequestData['current_password'], $oTerminal->password) === false) {
				$oErrors = $oValidator->errors();
				$oErrors->add('current_password', 'Текущий пароль и введённый, не совпадают.');

				return Redirect::to(route('auth.profile'))->withInput($aRequestData)->withErrors($oErrors);
			}

			// valid and saving
			$oTerminal->password = AuthHandler::getHashedPassword($aRequestData['password']);
		} else {
			unset($aRequestData['password']);
		}

		$oTerminal->fill($aRequestData);

		if ($oTerminal->save()) {
			Session::flash('auth.profile.success', 'Данные успешно обновлены.');
		}

		return Redirect::to(route('auth.profile'))->withInput($aRequestData);
	}

}