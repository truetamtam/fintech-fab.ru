<?php

namespace FintechFab\ActionsCalc\Controllers;

use FintechFab\ActionsCalc\Components\Validators;
use FintechFab\ActionsCalc\Models\Event;
use Paginator;
use Input;
use View;

/**
 * Class EventController
 *
 * @package FintechFab\ActionsCalc\Controllers
 */
class EventController extends BaseController
{

	/**
	 * Create event
	 *
	 * @return array|string
	 */
	public function create()
	{

		$oRequestData = Input::all();
		$oRequestData['terminal_id'] = $this->iTerminalId;

		$oValidator = Validators::validate($oRequestData, Validators::getEventRules());

		if ($oValidator->fails()) {
			return $this->error($oValidator->failed());
		}

		Event::create($oRequestData);

		return $this->success('Новое событие создано.');
	}

	/**
	 * Update event
	 *
	 * @param $id
	 *
	 * @return \Illuminate\View\View|string
	 */
	public function update($id)
	{
		/** @var Event $event */
		$event = Event::find($id);

		// update process
		$oRequestData = Input::only('id', 'event_sid', 'name');

		$oValidator = Validators::validate($oRequestData, Validators::getEventRules(), ['event_sid' => $id]);

		if ($oValidator->fails()) {
			return $this->error($oValidator->failed());
		}

		$event->name = $oRequestData['name'];
		$event->event_sid = $oRequestData['event_sid'];

		if ($event->save()) {
			return $this->success('Событие обновлено.', ['update' => $oRequestData]);
		}

		return $this->error('Не удалось обновить событие.');
	}

	/**
	 * Delete event
	 *
	 * @return string
	 */
	public function delete()
	{
		$aRequest = Input::only('id');

		/** @var Event $event */
		$event = Event::find((int)$aRequest['id']);

		if ($event->rules->count() > 0) {
			return $this->error('Сначала удалите правила.');
		}

		if ($event->delete()) {
			return $this->success('Событие удалено.');
		}

		return $this->error('Не удалось удалить событие.');
	}

	/**
	 * Update events table
	 *
	 * @return \Illuminate\View\View|string
	 */
	public function updateEventsTable()
	{
		$input = Input::all();
		$iPage = (int)$input['page'];

		// setting page that stored in span#pagination-events-current-page in _events.php
		Paginator::setCurrentPage($iPage);

		$aoEvents = Event::whereTerminalId($this->iTerminalId)->orderBy('created_at', 'desc')->paginate(10);
		$aoEvents->setBaseUrl('/actions-calc/events/table');

		return View::make('ff-actions-calc::calculator._events_table', [
			'events' => $aoEvents
		]);
	}

	/**
	 * Event search
	 *
	 * @return \Illuminate\View\View
	 */
	public function search()
	{
		$q = e(Input::get('q'));
		$aoEvents = Event::where('event_sid', 'LIKE', "%$q%")
			->orWhere('name', 'LIKE', "%$q%")
			->having('terminal_id', '=', $this->iTerminalId)
			->get();

		return View::make('ff-actions-calc::calculator._events_table', [
			'events' => $aoEvents,
		]);
	}

}
