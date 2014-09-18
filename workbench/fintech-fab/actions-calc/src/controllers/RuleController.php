<?php

namespace FintechFab\ActionsCalc\Controllers;

use FintechFab\ActionsCalc\Components\Validators;
use FintechFab\ActionsCalc\Models\Rule;
use Exception;
use Input;
use App;

/**
 * Class RuleController
 *
 * @package FintechFab\ActionsCalc\Controllers
 */
class RuleController extends BaseController
{

	/**
	 * Create rule.
	 * On GET sending view. On POST creating rule.
	 *
	 * @return string
	 */
	public function create()
	{
		// request data handling
		$oRequestData = Input::all();

		if (isset($oRequestData['flag_active'])) {
			$oRequestData['flag_active'] = ($oRequestData['flag_active'] == 'on') ? 1 : 0;
		}

		$oRequestData['terminal_id'] = $this->iTerminalId;

		// validation
		$oValidator = Validators::validate($oRequestData, Validators::getRuleValidators());

		if ($oValidator->fails()) {
			return $this->error($oValidator->failed());
		}

		try {
			Rule::create($oRequestData);
		} catch (Exception $e) {
			return $this->error($e->getMessage());
		}

		return $this->success('Новое правило создано.',
			['data' => ['count' => Rule::whereEventId($oRequestData['event_id'])->count()]]);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int $id
	 *
	 * @return \Illuminate\View\View
	 */
	public function update($id)
	{
		/** @var Rule $oRule */
		$oRule = Rule::find($id);

		// update process
		$oRequestData = Input::only('name', 'rule', 'event_id', 'signal_id');
		$oValidator = Validators::validate($oRequestData, Validators::getRuleValidators());

		if ($oValidator->fails()) {
			return $this->error($oValidator->failed());
		}

		$oRule->fill($oRequestData);

		if ($oRule->save()) {
			return $this->success('Правило обновлено.', ['update' => $oRequestData]);
		}

		return $this->error('Не удалось обновить событие.');
	}

	/**
	 * Rule delete
	 *
	 * @param $id
	 *
	 * @return string
	 */
	public function delete($id)
	{
		/** @var Rule $rule */
		$rule = Rule::find($id);

		if (is_null($rule)) {
			App::abort(401, 'Нет такого правила');
		}

		if ($rule->delete()) {

			$iRulesCount = $rule::whereEventId($rule->event_id)->count();

			return $this->success('Правило удалено.', ['data' => ['count' => $iRulesCount]]);
		}

		return $this->error('Не удалось удалить правило.');
	}

}
