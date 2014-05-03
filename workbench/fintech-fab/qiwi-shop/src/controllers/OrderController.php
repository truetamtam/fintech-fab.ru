<?php
namespace FintechFab\QiwiShop\Controllers;

use Config;
use FintechFab\QiwiShop\Components\Orders;
use FintechFab\QiwiShop\Components\PaysReturn;
use FintechFab\QiwiShop\Components\QiwiGateConnect;
use FintechFab\QiwiShop\Components\Validators;
use FintechFab\QiwiShop\Models\Order;
use FintechFab\QiwiShop\Models\PayReturn;
use Input;
use Response;
use Validator;
use View;

class OrderController extends BaseController
{

	public $layout = 'qiwiShop';
	private $statusMap;
	private $statusRussian;

	public function __construct()
	{
		$this->statusMap = array(
			'waiting'  => 'payable',
			'paid'     => 'paid',
			'rejected' => 'canceled',
			'expired'  => 'expired',
			'processing' => 'onReturn',
			'success'    => 'returned',
		);
		$this->statusRussian = array(
			'payable'   => 'К оплате',
			'canceled'  => 'Отменён',
			'expired'   => 'Просрочен',
			'paid'      => 'Оплачен',
			'returning' => 'Возврат оплаты',
			'onReturn'  => 'на возврате',
			'returned'  => 'возвращен',
		);
	}

	public function getAction($action)
	{

		$order_id = Input::get('order_id');
		$order = Order::find($order_id);

		if ($order->user_id != Config::get('ff-qiwi-shop::user_id')) {
			return $this->resultMessage('Нет такого заказа');
		}

		return $this->$action($order); // вызываем метод по названию переменной (id кнопки во вьюхе)
	}

	/**
	 * Страница таблицы заказов
	 *
	 * @return void
	 */
	public function ordersTable()
	{
		$user_id = Config::get('ff-qiwi-shop::user_id');
		$orders = Order::whereUserId($user_id)->orderBy('id', 'desc')->paginate(10);
		$this->layout->content = View::make('ff-qiwi-shop::qiwiShop.ordersTable', array('orders' => $orders));
	}

	/**
	 * Страница создания заказа
	 *
	 * @return void
	 */
	public function createOrder()
	{
		$this->layout->content = View::make('ff-qiwi-shop::qiwiShop.createOrder');
	}

	/**
	 * Создание счёта.
	 *
	 * @return array $result
	 */
	public function postCreateOrder()
	{
		$data = Input::all();
		$validator = Validator::make($data, Validators::rulesForNewOrder(), Validators::messagesForErrors());
		$userMessages = $validator->messages();

		if ($validator->fails()) {
			$result['errors'] = array(
				'item'    => $userMessages->first('item'),
				'sum'     => $userMessages->first('sum'),
				'tel'     => $userMessages->first('tel'),
				'comment' => $userMessages->first('comment'),
			);
			return $result;
		}
		$data['user_id'] = Config::get('ff-qiwi-shop::user_id');
		$day = Config::get('ff-qiwi-shop::lifetime');
		$data['lifetime'] = date('Y-m-d H:i:s', time() + 3600 * 24 * $day);
		$order = Orders::newOrder($data);

		if ($order) {
			$result = array(
				'result'   => 'ok',
				'order_id' => $order->id,
				'message'  => 'Заказ №' . $order->id . ' успешно создан.',
			);

			return $result;
		}
		$result['errors'] = array(
			'common' => 'Неизвестная ошибка. Повторите ещё раз.',
		);
		return $result;
	}


	/**
	 * Статус счёта.
	 *
	 * @param Order $order
	 *
	 * @return mixed
	 */
	public function showStatus($order)
	{
		$gate = QiwiGateConnect::Instance();
		$oResponse = $gate->checkStatus($order->id);

		if (array_key_exists('error', $oResponse)) {
			return $this->resultMessage($oResponse['error']);
		}
		$currentOrderStatus = $order->status;
		$BillStatus = $oResponse->response->bill->status;

		//Не меняем статуса с "на возврате" на статус "оплачено"
		if ($currentOrderStatus == 'returning' && $BillStatus == 'paid') {
			$message = 'Текущий статус счета - ' . $this->statusRussian[$currentOrderStatus];

			return $this->resultMessage($message, 'Сообщение');
		}
		$newOrderStatus = $this->statusMap[$BillStatus];

		if ($currentOrderStatus != $newOrderStatus) {
			Order::whereId($order->id)->whereStatus($currentOrderStatus)->update(array('status' => $newOrderStatus));
		}
		$message = 'Текущий статус счета - ' . $this->statusRussian[$newOrderStatus];

		return $this->resultMessage($message, 'Сообщение');

	}

	/**
	 * Выставление счёта
	 *
	 * @param Order $order
	 *
	 * @return mixed
	 */
	public function createBill($order)
	{
		$gate = QiwiGateConnect::Instance();
		$oResponse = $gate->getBill($order);

		if (array_key_exists('error', $oResponse)) {
			return $this->resultMessage($oResponse['error']);
		}
		$update = Order::whereId($order->id)->whereStatus('new')->update(array('status' => 'payable'));
		if ($update) {
			$message = 'Счёт № ' . $order->id . ' выставлен';

			return $this->resultMessage($message, 'Сообщение');
		}

		return $this->resultMessage('Счёт не выставлен');

	}

	/**
	 * Отменить счёт.
	 *
	 * @param Order $order
	 *
	 * @return Response
	 */
	public function cancelBill($order)
	{
		$gate = QiwiGateConnect::Instance();
		$oResponse = $gate->cancelBill($order);
		if (array_key_exists('error', $oResponse)) {
			return $this->resultMessage($oResponse['error']);
		}
		$update = Order::whereId($order->id)->whereStatus('payable')->update(array('status' => 'canceled'));
		if ($update) {
			$message = 'Счёт № ' . $order->id . ' отменён.';

			return $this->resultMessage($message, 'Сообщение');
		}

		return $this->resultMessage('Счёт не отменён');

	}

	/**
	 * Возврат оплаты
	 *
	 * @param Order $order
	 *
	 * @return mixed|void
	 */
	public function payReturn($order)
	{
		$data = Input::all();

		//Проверяем данные на валидность
		$validator = Validator::make($data, Validators::rulesForPayReturn(), Validators::messagesForErrors());
		$userMessages = $validator->messages();

		if ($validator->fails()) {
			$result['error'] = array(
				'sum'     => $userMessages->first('sum'),
				'comment' => $userMessages->first('comment'),
			);

			return $result;
		}

		//Возможен ли возврат указанной суммы учитывая прошлые возвраты по этому счёту
		$returnsBefore = PayReturn::whereOrderId($order->id)->get();
		$sumReturn = 0;
		foreach ($returnsBefore as $one) {
			$sumReturn += $one->sum;
		}
		$possibleReturn = $order->sum - $sumReturn;
		if ($data['sum'] > $possibleReturn) {
			$result['error'] = array(
				'sum' => 'Слишком большая сумма',
			);

			return $result;
		}

		//Если не закончен придыдущий возврат, то не даём сделать новый
		if ($order->idLastReturn != null) {
			$currentStatusReturn = $order->PayReturn()->find($order->idLastReturn)->status;
			if ($currentStatusReturn == 'onReturn') {
				return $this->resultMessage('Дождитесь окончания предыдущего возврата.');
			}
		}

		//Создаём возврат в таблице и начинаем возврат
		$payReturn = PaysReturn::newPayReturn($data);
		if (!$payReturn) {
			return $this->resultMessage('Возврат не создан, повторите попытку.');
		}
		$gate = QiwiGateConnect::Instance();
		$oResponse = $gate->payReturn($payReturn);

		//Если ошибка, то удаляем наш возврат из таблицы
		if (array_key_exists('error', $oResponse)) {
			PayReturn::find($payReturn->id)->delete();

			return $this->resultMessage($oResponse['error']);
		}

		//Меняем статус заказа при успешном возврате

		Order::whereId($order->id)->update(array('status' => 'returning', 'idLastReturn' => $payReturn->id));


		$message = 'Сумма ' . $oResponse->response->refund->amount . ' руб. по счёту № ' . $order->id . ' отправлена на возврат';

		return $this->resultMessage($message, 'Сообщение');

	}

	/**
	 * Проверка статуса возврат оплаты
	 *
	 * @param Order $order
	 *
	 * @return mixed|void
	 */
	public function statusReturn($order)
	{
		$payReturn = PayReturn::find($order->idLastReturn);

		$gate = QiwiGateConnect::Instance();
		$oResponse = $gate->checkRefundStatus($payReturn);
		if (array_key_exists('error', $oResponse)) {
			return $this->resultMessage($oResponse['error']);
		}
		$currentReturnStatus = $payReturn->status;

		$newReturnStatus = $this->statusMap[$oResponse->response->refund->status];

		if ($currentReturnStatus != $newReturnStatus) {
			PayReturn::whereId($payReturn->id)->whereStatus($currentReturnStatus)
				->update(array('status' => $newReturnStatus));
		}
		$message = 'Текущий статус возврата - ' . $this->statusRussian[$newReturnStatus];

		return $this->resultMessage($message, 'Сообщение');

	}


	private function resultMessage($messages, $title = 'Ошибка')
	{
		$result['message'] = $messages;
		$result['title'] = $title;

		return $result;
	}


}