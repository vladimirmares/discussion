<?php

namespace Control;

use Nette\Application\UI;
use Nette;
use Service\DiscussionService;

/**
 * Class PostsControl
 * @package Control
 */
class PostsControl extends UI\Control
{

	/** @var DiscussionService */
	protected $discussionService;


	/**
	 * @var
	 */
	private $postId;


	/**
	 * @param $id
	 */
	public function setPostId($id)
	{
		$this->postId = $id;
	}

	/**
	 * @param DiscussionService $discussionService
	 */
	public function setDiscussionService(DiscussionService $discussionService)
	{
		$this->discussionService = $discussionService;
	}


	/**
	 *
	 */
	public function render()
	{
		$this->template->setFile(__DIR__ . '/PostsControl.latte');
		$this->template->post = $this->discussionService->getPost($this->postId);
		$this->template->render();
	}


	/**
	 * @param $id
	 * @throws Nette\Application\AbortException
	 */
	public function handleDelete($id)
	{
		if($this->presenter->getUser()->isInRole('admin')) {
			$this->discussionService->deletePost($id);
		}
		if ($this->presenter->isAjax()) {
			$this->presenter->redrawControl();
		} else {
			$this->redirect('this');
		}
	}

	/**
	 * @return UI\Multiplier
	 */
	protected function createComponentCommentsContainer()
	{
		$service = $this->discussionService;
		return new Nette\Application\UI\Multiplier(function ($id) use ($service) {
			$control = new CommentsControl();
			$control->setCommentId($id);
			$control->setPostId($this->postId);
			$control->setDiscussionService($service);
			return $control;
		});
	}

}