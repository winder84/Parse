<?php
/**
 * @author Rustam Ibragimov
 * @mail Rustam.Ibragimov@softline.ru
 * @date 07.05.13
 */
namespace Sait\Model;

use Zend\Db\Sql\Sql;

class SaitTable
{
	protected $table;

	public function __construct($dbad)
	{
		$this->adapter = $dbad;
	}

	public function fetchAll($table)
	{
		$adapter = $this->adapter;
		$sql = new Sql($adapter);
		$select = $sql->select();
		$select->from($table);
		$selectString = $sql->getSqlStringForSqlObject($select);
		$results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
		return $results;
	}


	public function getAllByVal($table, $valArray)
	{
		$adapter = $this->adapter;
		$sql = new Sql($adapter, $table);
		$select = $sql->select();
		$select->where($valArray);
		$statement = $sql->prepareStatementForSqlObject($select);
		$results = $statement->execute();

		return $results;
	}

	public function saveOne($id, $table,  $siteUpd)
	{
		$adapter = $this->adapter;
		$sql = new Sql($adapter);
		$update = $sql->update($table);
		$update->where(array('id' => $id));
		$update->set($siteUpd);

		$statement = $sql->prepareStatementForSqlObject($update);
		$results = $statement->execute();
		return $results;
	}

	public function deleteOneById($table, $id)
	{
		$adapter = $this->adapter;
		$sql = new Sql($adapter);
		$delete = $sql->delete($table);
		$delete->where(array('id' => $id));
		$statement = $sql->prepareStatementForSqlObject($delete);
		$results = $statement->execute();

		return $results;
	}

	public function addOne($table, $addArray) {
		$adapter = $this->adapter;
		$sql = new Sql($adapter);
		$insert = $sql->insert($table);
		$insert->values($addArray);

		$statement = $sql->prepareStatementForSqlObject($insert);
		$results = $statement->execute();
		return $results;
	}
}