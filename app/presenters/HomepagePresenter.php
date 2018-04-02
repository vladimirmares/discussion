<?php

namespace App\Presenters;

use Nette;
use Control\PostsControl;
use Control\CommentsControl;
use Service\DiscussionService;


/**
 * Class HomepagePresenter
 * @package App\Presenters
 */
class HomepagePresenter extends Nette\Application\UI\Presenter
{
	/** @var     Nette\Database\IRow */
	private $post;

	/** @var DiscussionService */
	private $discussionService;


	/**
	 * HomepagePresenter constructor.
	 * @param DiscussionService $discussionService
	 */
	public function __construct(DiscussionService $discussionService)
	{
		parent::__construct();
		$this->discussionService = $discussionService;
	}


	/**
	 * @param $id
	 * @throws Nette\Application\BadRequestException
	 */
	public function actionDetail($id)
	{
		$this->post = $this->discussionService->getPost($id);
		if (!$this->post) $this->error('Post not found');
		$this->template->post = $this->post;
	}

	/**
	 *
	 */
	public function actionOverview()
	{
		$this->template->posts = $this->discussionService->getAllPosts();
	}

	/**
	 *
	 */
	public function actionDefault()
	{
		$this->template->posts = $this->discussionService->getAllPosts()->order('lastCommentCreated DESC');
	}


	/**
	 * @return Nette\Application\UI\Multiplier
	 */
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

	/**
	 * @return Nette\Application\UI\Multiplier
	 */
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

	/**
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentAddPostForm()
	{
		$form = new Nette\Application\UI\Form();
		$form->addText('title', 'Titulek')
			->setRequired();
		$form->addTextArea('content', 'Text')
			->setRequired();
		$form->addSubmit('send', 'Přidat komentář')
			->setHtmlAttribute('class', 'ajax');
		$form->onSuccess[] = [$this, 'processAddPostForm'];

		return $form;
	}

	/**
	 * @param Nette\Application\UI\Form $form
	 * @throws Nette\Application\AbortException
	 */
	public function processAddPostForm(Nette\Application\UI\Form $form)
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
}
