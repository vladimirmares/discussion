<?php

namespace App\Presenters;

use Control\CommentsControl;
use Control\PostsControl;
use Model\CommentModel;
use Model\PostModel;
use Nette;
use Service\DiscussionService;
use Tracy\Debugger;


class HomepagePresenter extends Nette\Application\UI\Presenter
{
	/** @var     Nette\Database\IRow */
	private $post;

	/** @var DiscussionService */
	private $discussionService;


	public function __construct(DiscussionService $discussionService)
	{
		parent::__construct();
		$this->discussionService = $discussionService;
	}


	public function actionDetail($id)
	{
		$this->post = $this->discussionService->getPost($id);
		if (!$this->post) $this->error('Post not found');
		$this->template->post = $this->post;
	}

	public function actionOverview()
	{
		$this->template->posts = $this->discussionService->getAllPosts();
	}

	public function actionPosts()
	{
		$this->template->posts = $this->discussionService->getAllPosts();
	}



	protected function createComponentCommentsContainer()
	{
		$service = $this->discussionService;
		return new Nette\Application\UI\Multiplier(function ($id) use ($service) {
			$control = new CommentsControl();
			$control->setPostId($id);
			$control->setDiscussionService($service);
			return $control;
		});
	}

	protected function createComponentPostsContainer()
	{
		$service = $this->discussionService;
		return new Nette\Application\UI\Multiplier(function ($id) use ($service) {
			$control = new PostsControl();
			$control->setPostId($id);
			$control->setDiscussionService($service);
			return $control;
		});
	}
}
