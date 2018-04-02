<?php

namespace Model;

use Nette;


/**
 * Class LikesModel
 * @package Model
 */
class LikesModel extends BaseModel
{
	/** @var Nette\Database\Context */
	protected $database;

	/**
	 * @var string
	 */
	protected $tableName;

	/**
	 * LikesModel constructor.
	 * @param Nette\Database\Context $database
	 * @param string $tableName
	 */
	public function __construct(Nette\Database\Context $database, $tableName = 'like')
	{
		parent::__construct();
		$this->database = $database;
		$this->tableName = $tableName;
	}

	/**
	 * @param $commentId
	 * @param $userId
	 * @return Nette\Database\Table\Selection
	 */
	public function getLike($commentId, $userId)
	{
		$table = $this->database->table($this->tableName);
		$table->where('comment_id', [$commentId]);
		$table->where('user_id', [$userId]);
		return $table;
	}

	/**
	 * @param $commentId
	 * @return Nette\Database\Table\Selection
	 */
	public function getLikesForComment($commentId)
	{
		$table = $this->database->table($this->tableName);
		$table->where('comment_id', [$commentId]);
		$table->where('like', [1]);
		return $table;
	}

	/**
	 * @param $commentId
	 * @return Nette\Database\Table\Selection
	 */
	public function getDislikesForComment($commentId)
	{
		$table = $this->database->table($this->tableName);
		$table->where('comment_id', [$commentId]);
		$table->where('dislike', [1]);
		return $table;
	}

}
