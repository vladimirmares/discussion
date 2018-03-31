<?php
/**
 * Created by PhpStorm.
 * User: vladik
 * Date: 23.3.18
 * Time: 18:12
 */

namespace Control;

use Model\CommentModel;
use Nette\Application\UI;
use Nette;
use Service\DiscussionService;
use Tracy\Debugger;

class CommentsControl extends UI\Control
{

	/** @var DiscussionService */
	protected $discussionService;

	/**
	 * @var int
	 */
	protected $postId;

	protected $commentId;

	/**
	 * Vstříkne službu, kterou tato komponenta bude používat pro práci s komentáři.
	 *
	 * @param    DiscussionService $service
	 * @return   void
	 */
	public function setDiscussionService(DiscussionService $discussionService)
	{
		$this->discussionService = $discussionService;
	}

	public function setPostId($id)
	{
		$this->postId = $id;
	}

	public function setCommentId($id)
	{
		$this->commentId = $id;
	}


	public function render()
	{
		$this->template->setFile(__DIR__ . '/CommentsControl.latte');
		$this->template->postId = $this->postId;
		$this->template->commentId = $this->commentId;
		if (isset($this->commentId)) {
			$snippetAreaName = 'comment-' . $this->commentId;
			$this->template->comments = $this->discussionService->getCommentChildComments($this->commentId);
		} else {
			$snippetAreaName = 'post-' . $this->postId;
			$this->template->comments = $this->discussionService->getCommentMasterComments($this->postId);
		}
		$this->template->snippetAreaName = $snippetAreaName;
		$this->template->render();
	}

	protected function createComponentAddCommentForm()
	{
		$form = new UI\Form();
		$form->addTextArea('comment', 'Komentář')
			->setRequired();
		$form->addSubmit('send', 'Přidat komentář')
			->setHtmlAttribute('class', 'ajax');
		$form->onSuccess[] = [$this, 'processAddCommentForm'];

		return $form;
	}

	public function processAddCommentForm(UI\Form $form)
	{
		if($this->presenter->getUser()->loggedIn) {
			$values = $form->values;
			$values['parentCommentId'] = $this->commentId;
			$values['postId'] = $this->postId;
			$values['userId'] = $this->presenter->getUser()->id;
			$this->discussionService->saveComment($values);
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
			$this->discussionService->deleteComment($id);
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