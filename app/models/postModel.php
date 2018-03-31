<?php

namespace Model;

use Nette;


class PostModel extends BaseModel
{
	/** @var Nette\Database\Context */
	protected $database;

	protected $tableName;

	public function __construct(Nette\Database\Context $database, $tableName = 'posts')
	{
		parent::__construct();
		$this->database = $database;
		$this->tableName = $tableName;
	}

}
