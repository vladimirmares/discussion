<?php

namespace Service;

use Nette;
use Model\PostModel;
use Model\LikesModel;
use Model\CommentModel;


/**
 * Class DiscussionService
 * @package Service
 */
class DiscussionService
{

	/** @var PostModel */
	private $postModel;

	/** @var CommentModel */
	private $commentModel;

	/** @var LikesModel */
	private $likestModel;
	/**
	 * @var Nette\Database\Context
	 */
	protected $database;


	/**
	 * DiscussionService constructor.
	 * @param Nette\Database\Context $database
	 * @param PostModel $postModel
	 * @param CommentModel $commentModel
	 * @param LikesModel $likesModel
	 */
	public function __construct(
		Nette\Database\Context $database,
		PostModel $postModel,
		CommentModel $commentModel,
		LikesModel $likesModel)
	{
		$this->postModel = $postModel;
		$this->commentModel = $commentModel;
		$this->database = $database;
		$this->likestModel = $likesModel;
	}

	/**
	 * @param $id
	 * @return false|Nette\Database\Table\ActiveRow
	 */
	public function getPost($id)
	{
		return $this->postModel->get($id);
	}

	/**
	 * @return Nette\Database\Table\Selection
	 */
	public function getAllPosts()
	{
		return $this->postModel->getAll();
	}

	/**
	 * @param $values
	 * @return int
	 */
	public function savePost($values)
	{
		return $this->postModel->save($values);
	}

	/**
	 * @param $id
	 */
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


	/**
	 * @param $id
	 * @return Nette\Database\Table\Selection
	 */
	public function getCommentChildComments($id)
	{
		return $this->commentModel->getChildComments($id);
	}

	/**
	 * @param $id
	 * @return Nette\Database\Table\Selection
	 */
	public function getCommentMasterComments($id)
	{
		return $this->commentModel->getMasterComments($id);
	}

	/**
	 * @param $values
	 * @return int
	 */
	public function saveComment($values)
	{
		$id = $this->commentModel->save($values);
		$this->updatePostsData($values['post_id']);
		return $id;
	}

	/**
	 * @param $id
	 */
	public function deleteComment($id)
	{
		try {
			$this->database->beginTransaction();
		    $comment = $this->commentModel->get($id);
			$this->commentModel->deleteRecursively($id);
			$this->updatePostsData($comment->post_id);
			$this->database->commit();
		} catch (\Exception $e) {
			$this->database->rollBack();
		}
	}

	/**
	 * @param null $postId
	 */
	public function updatePostsData($postId = null)
	{
		$commentsCount = $this->commentModel->getCommentsForPost($postId)->count();
		$lastComment = $this->commentModel->getCommentsForPost($postId, array(),'created','DESC',1)->fetch();
		if($lastComment) {
			$lastCommentCreated = $lastComment->created;
		} else {
			$lastCommentCreated = null;
		}
		$this->postModel->edit($postId, ['commentCount' => $commentsCount, 'lastCommentCreated' => $lastCommentCreated]);
	}

	/**
	 * @param $commentId
	 * @param $userId
	 * @param null $like
	 * @param null $dislike
	 * @return null
	 */
	public function processLike($commentId, $userId, $like = null, $dislike = null)
	{
		$existsLike = $this->likestModel->getLike($commentId, $userId)->fetch();
		if($existsLike) {
			if($like && $existsLike->like) {
				return null;
			} elseif ($dislike && $existsLike->dislike) {
			  	return null;
			} else {
			  $values['like'] = $like;
			  $values['dislike'] = $dislike;
			  $this->likestModel->edit($existsLike->id, $values);
			}
		} else {
			$values['comment_id'] = $commentId;
			$values['user_id'] = $userId;
			$values['like'] = $like;
			$values['dislike'] = $dislike;
			$this->likestModel->create($values);
		}
		$this->updateCommentData($commentId);
	}


	/**
	 * @param $commentId
	 */
	public function updateCommentData($commentId)
	{
		$values['likes'] = $this->likestModel->getLikesForComment($commentId)->count();
		$values['dislikes'] = $this->likestModel->getDislikesForComment($commentId)->count();
		$this->commentModel->edit($commentId, $values);
	}

}
