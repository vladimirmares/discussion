<?php
use Tester\Assert;
$container = require __DIR__ . '/bootstrap.php';

class SignPresenterTest extends Tester\TestCase
{
	private $container;

	public function __construct(Nette\DI\Container $container)
	{
		$this->container = $container;
	}

	public function setUp()
	{
		Tester\Environment::setup();
	}

	public function testSomething()
	{
		$presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');

		$presenter = $presenterFactory->createPresenter('Sign');
		$presenter->autoCanonicalize = false;
		$request = new Nette\Application\Request('Sign', 'GET', array('action' => 'in'));
		$response = $presenter->run($request);
		Assert::type('Nette\Application\Responses\TextResponse', $response);
		Assert::type('Nette\Bridges\ApplicationLatte\Template', $response->getSource());
		$html = (string) $response->getSource();
		$dom = Tester\DomQuery::fromHtml($html);

		Assert::true( $dom->has('input[name="username"]') );
		Assert::true( $dom->has('input[name="password"]') );

		$presenter = $this->container->createInstance(\Model\PostModel::class);
        $presenter->save(['title'=>'test', "content" =>'content']);


	}
}

$test = new SignPresenterTest($container);
$test->run();