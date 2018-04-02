<?php
namespace Model;

use Nette;


/**
 * Class BaseModel
 * @package Model
 */
class BaseModel
{
	/** @var Nette\Database\Context */
	protected $database;

	/**
	 * @var string
	 */
	protected $tableName;

	/**
	 * BaseModel constructor.
	 */
	public function __construct()
	{
	}

	/**
	 * @param $id
	 * @return false|Nette\Database\Table\ActiveRow
	 */
	public function get($id)
	{
		return $this->database->table($this->tableName)->get($id);
	}

	/**
	 * @return Nette\Database\Table\Selection
	 */
	public function getAll()
	{
    	return $this->database->table($this->tableName);
	}

	/**
	 * @param $values
	 * @return int
	 */
	public function create($values)
	{
		$row = $this->database
			->table($this->tableName)
			->insert($values);
		return $row->id;
	}

	/**
	 * @param $values
	 * @param $id
	 */
	public function edit($id, $values)
	{
		$this->database
			->table($this->tableName)
			->where('id', $id)
			->update($values);
	}

	/**
	 * @param $values
	 * @return int
	 */
	public function save($values)
	{
		if (isset($values['id'])) {
			$id = $values['id'];
			unset($values['id']);
			$this->edit($id, $values);
		} else {
			$id = $this->create($values);
		}

		return $id;
	}

	/**
	 * @param $id
	 * @return int
	 */
	public function delete($id)
	{
		return $this->database->table($this->tableName)->where('id', [$id])->delete();
	}

}