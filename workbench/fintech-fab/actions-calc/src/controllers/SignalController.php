<?php

namespace FintechFab\ActionsCalc\Controllers;

use Exception;
use FintechFab\ActionsCalc\Components\Validators;
use FintechFab\ActionsCalc\Models\Rule;
use FintechFab\ActionsCalc\Models\Signal;
use Validator;
use Input;
use View;
use App;

/**
 * Class SignalController
 *
 * @package FintechFab\ActionsCalc\Controllers
 */
class SignalController extends BaseController
{
	/**
	 * Store a newly created resource in storage.
	 */
	public function store()
	{
		$aRequestData = Input::only('name', 'signal_sid');

		$aRequestData['terminal_id'] = $this->iTerminalId;

		$oValidator = Validators::validate($aRequestData, Validators::getSignalValidator());

		if ($oValidator->fails()) {
			return $this->error($oValidator->failed());
		}

		$oSignal = Signal::create($aRequestData);
		$aReturnData = [];

		if (!$oSignal->push()) {
			return $this->error('Не удалось создать сигнал');
		}

		$aReturnData['id'] = $oSignal->id;
		$aReturnData['name'] = $aRequestData['name'];
		$aReturnData['signal_sid'] = $aRequestData['signal_sid'];

		return $this->success('Сигнал успешно создан', ['data' => $aReturnData]);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int $id
	 *
	 * @return array
	 */
	public function update($id)
	{
		/** @var Signal $oSignal */
		$oSignal = Signal::find($id);
		$aRequestData = Input::only('name', 'signal_sid');

		// validation
		$oValidator = Validators::validate($aRequestData, Validators::getSignalValidator(), ['signal_sid' => $id]);

		if ($oValidator->fails()) {
			return $this->error($oValidator->failed());
		}

		// filling and updating
		$oSignal->fill($aRequestData);

		if (!$oSignal->save()) {
			return ['status' => 'error', 'message' => 'Не удалось обновить сигнал'];
		}

		try {
			$oSignal->save();
		} catch (Exception $e) {
			return $this->error($e->getMessage());
		}

		return $this->success("Сигнал \"$oSignal->name\" обновлён.", [
			'data' => [
				'name'       => $oSignal->name,
				'signal_sid' => $oSignal->signal_sid
			]
		]);
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int $id
	 *
	 * @return string
	 */
	public function destroy($id)
	{
		/** @var Signal $oSignal */
		$oSignal = Signal::find($id);

		$oRules = Rule::whereSignalId($id)->first();

		if (!is_null($oRules)) {
			return $this->error('Сигнал используется.');
		}

		if (is_null($oSignal)) {
			App::abort(401, 'Нет такого правила');
		}

		try {
			$oSignal->delete();
		} catch (Exception $e) {
			return $this->error($e->getMessage());
		}

		return $this->success('Событие удалено.');
	}

}
