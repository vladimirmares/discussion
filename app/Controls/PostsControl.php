<?php
/**
 * Created by PhpStorm.
 * User: vladik
 * Date: 23.3.18
 * Time: 18:12
 */

namespace Control;

use Model\CommentModel;
use Model\PostModel;
use Nette\Application\UI;
use Nette;
use Service\DiscussionService;
use Tracy\Debugger;

class PostsControl extends UI\Control
{

	/** @var DiscussionService */
	protected $discussionService;


	private $postId;


	public function setPostId($id)
	{
		$this->postId = $id;
	}

	public function setDiscussionService(DiscussionService $discussionService)
	{
		$this->discussionService = $discussionService;
	}


	public function render()
	{
		$this->template->setFile(__DIR__ . '/PostsControl.latte');
		$this->template->post = $this->discussionService->getPost($this->postId);
		$this->template->render();
	}

	protected function createComponentAddPostForm()
	{
		$form = new UI\Form();
		$form->addTextArea('title', 'Komentář')
			->setRequired();
		$form->addTextArea('content', 'Komentář')
			->setRequired();
		$form->addSubmit('send', 'Přidat komentář')
			->setHtmlAttribute('class', 'ajax');
		$form->onSuccess[] = [$this, 'processAddPostForm'];

		return $form;
	}

	public function processAddPostForm(UI\Form $form)
	{
		if($this->presenter->getUser()->loggedIn) {
			$values = $form->values;
			$values['userId'] = $this->presenter->getUser()->id;
			$this->discussionService->savePost($values);
			$form->setValues([], TRUE);
			$this->flashMessage('Komentář byl úspěšně přidán, děkujeme.');
		}
		if ($this->presenter->isAjax()) {
			$this->presenter->redrawControl();
		} else {
			$this->redirect('this');
		}
	}


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