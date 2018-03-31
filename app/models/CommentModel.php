<?php

namespace Model;

use Nette;

class CommentModel extends BaseModel
{
	/**
	 * @var Nette\Database\Context
	 */
	protected $database;

	/**
	 * @var string
	 */
	protected $tableName;


	/**
	 * CommentModel constructor.
	 * @param Nette\Database\Context $database
	 */
	public function __construct(Nette\Database\Context $database, $tableName = 'comments')
	{
		parent::__construct();
		$this->database = $database;
		$this->tableName = $tableName;
	}

	public function getCommentsForPost($postId, $where = array(), $order = null, $orderDirection = 'DESC', $limit = null){
		$where['postId'] = $postId;
		$table = $this->database->table($this->tableName);
		foreach ($where as $key => $val) {
			if ($key == 'condition') {
				call_user_func_array(array($table, 'where'), $val);
			} else {
				$table->where($key, $val);
			}
		}
		if($order) {
			$table->order($order.' '.$orderDirection);
		}
		if($limit) {
			$table->limit($limit);
		}
		return $table;
	}


	/**
	 * @param $postId
	 * @return Nette\Database\Table\Selection
	 */
	public function getMasterComments($postId)
	{
		return $this->database
			->table($this->tableName)
			->where('postId', [$postId])
			->where('parentCommentId', null);
	}

	/**
	 * @param $postId
	 * @return Nette\Database\Table\Selection
	 */
	public function getChildComments($commentId)
	{
		return $this->database
			->table($this->tableName)
			->where('parentCommentId', [$commentId]);
	}

	/**
	 * @param $id
	 * @return int
	 */
	public function deleteRecursively($id)
	{
		$comments = $this->getChildComments($id);
		foreach ($comments as $comment) {
			$this->deleteRecursively($comment->id);
		}

		return $this->delete($id);
	}
}
