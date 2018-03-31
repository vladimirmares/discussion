<?php
use Tester\Assert;
$container = require __DIR__ . '/bootstrap.php';

class PostModelTest extends Tester\TestCase
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
		$postModel= $this->container->createInstance(\Model\PostModel::class);
        $id = $postModel->save(['title'=>'title', "content" =>'content']);
		Assert::type('integer', $id);
		$post = $postModel->get($id);
		Assert::type('Nette\Database\Table\ActiveRow', $post);
		Assert::same('content', $post->content);
		Assert::same('title', $post->title);
		$postModel->save(['id'=>$id, 'title'=>'newTitle']);
		$post = $postModel->get($id);
		Assert::same('newTitle', $post->title);
		$postModel->delete($id);
		$post = $postModel->get($id);
		Assert::false($post);



	}
}

$test = new PostModelTest($container);
$test->run();