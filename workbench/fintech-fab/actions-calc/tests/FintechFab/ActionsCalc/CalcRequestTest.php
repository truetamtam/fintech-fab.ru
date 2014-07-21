<?php


use FintechFab\ActionsCalc\Components\SendResults;

class CalcRequestTest extends CalcTestCase
{
	/**
	 * @var Mockery\MockInterface|SendResults
	 */
	private $mock;
	private $sign;

	public function setUp()
	{
		parent::setUp();

		$this->mock = Mockery::mock('FintechFab\ActionsCalc\Components\SendResults');
		$this->sign = md5('terminal=1|event=im_hungry|key');
	}

	public function testGetRequest1()
	{

		App::bind('FintechFab\ActionsCalc\Queue\SendResults', function () {
			$this->mock
				->shouldReceive('sendHttp')
				->withArgs(['http://test', '1']);
			$this->mock
				->shouldReceive('requestToQueue')
				->withArgs(['queueTest', 'go_eat']);

			return $this->mock;

		});

		$requestData = array(
			'term'   => 1,
			'event' => 'im_hungry',
			'data'   => json_encode(array('time' => '13.05')),
			'sign'  => $this->sign,
		);

		$response = $this->call(
		'POST',
			'/actions-calc/getRequest',
			$requestData
		);

		$this->assertContains(json_encode(['countFitRules' => 1]), $response->original);
	}

	public function testGetRequest2()
	{

		App::bind('FintechFab\ActionsCalc\Queue\SendResults', function () {
			$this->mock
				->shouldReceive('sendHttp')
				->withArgs(['http://test', '1']);
			$this->mock
				->shouldReceive('requestToQueue')
				->withArgs(['queueTest', 'wait']);

			return $this->mock;

		});

		$requestData = array(
			'term'   => 1,
			'event' => 'im_hungry',
			'data'   => json_encode(array('time' => '12.05')),
			'sign'  => $this->sign,
		);

		$response = $this->call(
			'POST',
			'/actions-calc/getRequest',
			$requestData
		);

		$this->assertContains(json_encode(['countFitRules' => 1]), $response->original);
	}

	public function testGetRequest3()
	{

		App::bind('FintechFab\ActionsCalc\Queue\SendResults', function () {
			$this->mock
				->shouldReceive('sendHttp')
				->withArgs(['http://test', '1']);
			$this->mock
				->shouldReceive('requestToQueue')
				->withArgs(['queueTest', 'endure']);

			return $this->mock;

		});

		$requestData = array(
			'term'   => 1,
			'event' => 'im_hungry',
			'data'   => json_encode(array('time' => '14.30')),
			'sign'  => $this->sign,
		);

		$response = $this->call(
			'POST',
			'/actions-calc/getRequest',
			$requestData
		);

		$this->assertContains(json_encode(['countFitRules' => 1]), $response->original);
	}

	public function testGetRequest4()
	{

		$requestData = array(
			'term'   => 1,
			'event' => 'im_hungry',
			'data'   => json_encode(array('time' => '13.30', 'have_money' => false)),
			'sign'  => $this->sign,
		);

		$response = $this->call(
			'POST',
			'/actions-calc/getRequest',
			$requestData
		);

		$this->assertContains(json_encode(['countFitRules' => 0]), $response->original);
	}

	public function testGetRequest5()
	{

		App::bind('FintechFab\ActionsCalc\Queue\SendResults', function () {
			$this->mock
				->shouldReceive('sendHttp')
				->withArgs(['http://test', '1']);
			$this->mock
				->shouldReceive('requestToQueue')
				->withArgs(['queueTest', 'go_eat']);

			return $this->mock;

		});

		$requestData = array(
			'term'   => 1,
			'event' => 'im_hungry',
			'data'   => json_encode(array('time' => '13.30', 'have_money' => true)),
			'sign'  => $this->sign,
		);

		$response = $this->call(
			'POST',
			'/actions-calc/getRequest',
			$requestData
		);

		$this->assertContains(json_encode(['countFitRules' => 1]), $response->original);
	}

	public function setDown()
	{
		Mockery::close();
	}

}