<?php

namespace Service;

use Model\CommentModel;
use Model\PostModel;
use Nette;
use Tracy\Debugger;

class DiscussionService
{

	/** @var PostModel */
	private $postModel;

	/** @var CommentModel */
	private $commentModel;

	/**
	 * @var Nette\Database\Context
	 */
	protected $database;


	public function __construct(Nette\Database\Context $database, PostModel $postModel, CommentModel $commentModel)
	{
		$this->postModel = $postModel;
		$this->commentModel = $commentModel;
		$this->database = $database;
	}

	public function getPost($id)
	{
		return $this->postModel->get($id);
	}

	public function getAllPosts()
	{
		return $this->postModel->getAll();
	}

	public function savePost($values)
	{
		return $this->postModel->save($values);
	}

	public function deletePost($id)
	{
		try {
			$this->database->beginTransaction();
			$masterComments = $this->commentModel->getMasterComments($id);
			foreach ($masterComments as $masterComment) {
				$this->commentModel->delete($masterComment->id);
			}
			$this->postModel->delete($id);
			$this->database->commit();
		} catch (\Exception $e) {
			$this->database->rollBack();
		}
	}


	public function getCommentChildComments($id)
	{
		return $this->commentModel->getChildComments($id);
	}

	public function getCommentMasterComments($id)
	{
		return $this->commentModel->getMasterComments($id);
	}

	public function saveComment($values)
	{
		$id = $this->commentModel->save($values);
		$this->updatePostsData($values['postId']);
		return $id;
	}

	public function deleteComment($id)
	{
		try {
			$this->database->beginTransaction();
		    $comment = $this->commentModel->get($id);
		    Debugger::barDump($comment->postId);
			$this->commentModel->deleteRecursively($id);
			$this->updatePostsData($comment->postId);
			$this->database->commit();
		} catch (\Exception $e) {
		    Debugger::barDump($e);
			$this->database->rollBack();
		}
	}

	public function updatePostsData($postId = null)
	{
		$commentsCount = $this->commentModel->getCommentsForPost($postId)->count();
		$lastCommentCreated = $this->commentModel->getCommentsForPost($postId, array(),'created','DESC',1)->fetch()->created;
		$this->postModel->edit($postId, ['commentCount' => $commentsCount, 'lastCommentCreated' => $lastCommentCreated]);
	}

}
