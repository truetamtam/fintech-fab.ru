<?php

namespace FintechFab\ActionsCalc\Controllers;

use App;
use Controller;
use FintechFab\ActionsCalc\Components\AuthHandler;
use Response;
use View;
use Request;

/**
 * Class BaseController
 *
 * @package FintechFab\ActionsCalc\Controllers
 */
class BaseController extends Controller
{
	/**
	 * @var int
	 */
	protected $iTerminalId;
	/**
	 * @var string
	 */
	private $sLayoutFolderName = 'default';

	/**
	 *
	 */
	public function __construct()
	{
		$this->iTerminalId = AuthHandler::getTerminalId();
	}

	/**
	 * Setup layout
	 */
	protected function setupLayout()
	{
		if (!is_null($this->layout)) {
			$this->layout = View::make('ff-actions-calc::layouts.' . $this->layout);
		}
	}

	/**
	 * @param       $sTemplate
	 * @param array $aParams
	 *
	 * @return $this|\Illuminate\View\View
	 */
	protected function make($sTemplate, $aParams = array())
	{
		if (Request::ajax()) {
			return $this->makePartial($sTemplate, $aParams);
		} else {
			return $this->layout->nest('content', 'ff-actions-calc::' . $sTemplate, $aParams);
		}
	}

	/**
	 * @param       $sTemplate
	 * @param array $aParams
	 *
	 * @return \Illuminate\View\View
	 */
	protected function makePartial($sTemplate, $aParams = array())
	{
		return View::make($this->sLayoutFolderName . '.' . $sTemplate, $aParams);
	}

	/**
	 * @param string|array $mMessages
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	protected function error($mMessages)
	{
		$jsonData = is_array($mMessages) ? ['status' => 'error', 'errors' => $mMessages]
			: ['status' => 'error', 'message' => $mMessages];

		return Response::json($jsonData, 400);
	}

	/**
	 * @param $sMessage
	 * @param $aData
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	protected function success($sMessage, $aData = null)
	{
		$aJsonData = ['status' => 'success', 'message' => $sMessage];

		if (is_array($aData)) {
			$aJsonData = array_merge($aJsonData, $aData);
		}

		return Response::json($aJsonData);
	}
}
