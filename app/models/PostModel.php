<?php

namespace Model;

use Nette;


class PostModel extends BaseModel
{
	/** @var Nette\Database\Context */
	protected $database;

	/**
	 * @var string
	 */
	protected $tableName;

	/**
	 * PostModel constructor.
	 * @param Nette\Database\Context $database
	 * @param string $tableName
	 */
	public function __construct(Nette\Database\Context $database, $tableName = 'post')
	{
		parent::__construct();
		$this->database = $database;
		$this->tableName = $tableName;
	}

}
