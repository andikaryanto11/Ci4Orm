<?php

namespace Ci4Orm\Repository;

use Ci4Orm\Entities\EntityList;
use Ci4Orm\Entities\ORM;
use Ci4Orm\Exception\EntityException;
use Ci4Orm\Interfaces\IEntity;
use Ci4Orm\Interfaces\IRepository;
use Ci4Orm\Libraries\Datatables;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\BaseConnection;
use DateTime;

class Repository implements IRepository
{

	/**
	 *
	 * @var BaseBuilder
	 */
	protected BaseBuilder $builder;

	/**
	 *
	 * @var string
	 */
	protected string $entityClass;

	/**
	 *
	 * @var array
	 */
	protected array $selectColumns;

	/**
	 *
	 * @var array
	 */
	protected array $props;

	/**
	 *
	 * @var ?IEntity
	 */
	protected BaseConnection $db;

	/**
	 *
	 * @param string $entityClass
	 */
	public function __construct(
		string $entityClass
	) {
		$this->entityClass = $entityClass;
		$this->props = ORM::getProps($this->entityClass);
		$this->db = \Config\Database::connect();
		$this->builder = $this->db->table($this->props['table']);
		$this->selectColumns = ORM::getSelectColumns($this->entityClass);
	}
	
	public function getProps()
	{
		return $this->props;
	}

	/**
	 * Create new instance of class
	 *
	 * @return IEntity;
	 */
	public function newEntity()
	{
		$newEntity = new $this->entityClass;
		$primaryKey = 'set' . $this->props['primaryKey'];
		$newEntity->$primaryKey(0);
		return $newEntity;
	}

	/**
	 *
	 * @inheritDoc
	 */
	public function find($id)
	{
		$param = [
			'where' => [
				$this->props['primaryKey'] => $id
			]
		];

		$result = $this->fetch($param);
		if (count($result) > 0) {
			return $result[0];
		}
		return null;
	}

	/**
	 *
	 * @inheritDoc
	 */
	public function findOrNew($id)
	{
		$result = $this->find($id);
		if (empty($result)) {
			return $this->newEntity();
		}
		return $result;
	}

	/**
	 *
	 * @inheritDoc
	 */
	public function findOrFail($id)
	{
		$result = $this->find($id);
		if (empty($result)) {
			throw new EntityException('Data with id ' . $id . ' not found');
		}
		return $result;
	}

	/**
	 *
	 * @inheritDoc
	 */
	public function findOne($filter = [])
	{
		$result = $this->fetch($filter);
		if (count($result) > 0) {
			return $result[0];
		}
		return null;
	}

	/**
	 *
	 * @param int|string $id
	 * @return mixed
	 */
	public function findOneOrFail($filter = [])
	{
		$result = $this->findOne($filter);
		if (empty($result)) {
			throw new EntityException('Data not found');
		}
		return $result[0];
	}

	/**
	 *
	 * @param int|string $id
	 * @return mixed
	 */
	public function findOneOrNew($filter = [])
	{
		$result = $this->findOne($filter);
		if (empty($result)) {
			return $this->newEntity();
		}
		return $result[0];
	}

	/**
	 * Will fetch array of entity.
	 * Deprecated, use collect instead to better performance.
	 * ::collect will lazy load related entity, and eager load of list entity
	 *
	 * @param array $filter
	 * @param array $columns
	 * @return array
	 *
	 * @deprecated <1.0.0
	 */
	public function findAll(array $filter = [], $columns = [])
	{
		return $this->fetch($filter, $columns);
	}

	/**
	 * set filter to query builder
	 *
	 * @param array $filter
	 */
	private function setFilters($filter = [])
	{

		if (!empty($filter)) {
			$join = (isset($filter['join']) ? $filter['join'] : FALSE);
			$where = (isset($filter['where']) ? $filter['where'] : FALSE);
			$wherein = (isset($filter['whereIn']) ? $filter['whereIn'] : FALSE);
			$orwherein = (isset($filter['orWhereIn']) ? $filter['orWhereIn'] : FALSE);
			$orwhere = (isset($filter['orWhere']) ? $filter['orWhere'] : FALSE);
			$wherenotin = (isset($filter['whereNotIn']) ? $filter['whereNotIn'] : FALSE);
			$like = (isset($filter['like']) ? $filter['like'] : FALSE);
			$orlike = (isset($filter['orLike']) ? $filter['orLike'] : FALSE);
			$notlike = (isset($filter['notLike']) ? $filter['notLike'] : FALSE);
			$ornotlike = (isset($filter['orNotLike']) ? $filter['orNotLike'] : FALSE);
			$order = (isset($filter['order']) ? $filter['order'] : FALSE);
			$limit = (isset($filter['limit']) ? $filter['limit'] : FALSE);
			$group = (isset($filter['group']) ? $filter['group'] : FALSE);

			if ($join) {
				foreach ($join as $key => $vv) {
					foreach ($vv as $v) {
						$type = "";
						if (isset($v['type'])) {
							$type = $v['type'];
						}
						$this->builder->join($key, $v['key'], $type);
					}
				}
			}
			if ($where)
				$this->builder->where($where);

			if ($orwhere)
				$this->builder->orWhere($orwhere);

			if ($wherein) {
				foreach ($wherein as $key => $v) {
					if (!empty($v))
						$this->builder->whereIn($key, $v);
				}
			}

			if ($orwherein) {
				foreach ($orwherein as $key => $v) {
					if (!empty($v))
						$this->builder->orWhereIn($key, $v);
				}
			}

			if ($wherenotin) {
				foreach ($wherenotin as $key => $v) {
					if (!empty($v))
						$this->builder->whereNotIn($key, $v);
				}
			}


			if ($like)
				$this->builder->like($like);

			if ($orlike)
				$this->builder->orLike($orlike);

			if ($orlike) {
				foreach ($orlike as $key => $v) {
					if (!empty($v))
						$this->builder->orLike($key, $v);
				}
			}

			if ($notlike) {
				foreach ($notlike as $key => $v) {
					if (!empty($v))
						$this->builder->notLike($key, $v);
				}
			}

			if ($ornotlike) {
				foreach ($ornotlike as $key => $v) {
					if (!empty($v))
						$this->builder->orNotLike($key, $v);
				}
			}

			if ($group) {
				$this->builder->groupStart();
				foreach ($group as $key => $v) {
					if ($key == 'orLike') {
						foreach ($v as $orLikeKey => $orLikeValue) {
							$this->builder->orLike($orLikeKey, $orLikeValue);
						}
					}
					if ($key == 'and') {
						foreach ($v as $andKey => $andValue) {
							$this->builder->where([$andKey => $andValue]);
						}
					}
				}
				$this->builder->groupEnd();
			}

			if ($order) {
				foreach ($order as $key => $v) {
					if (!empty($v))
						$this->builder->orderBy($key, $v);
				}
			}

			if ($limit)
				$this->builder->limit($limit['size'], ($limit['page'] - 1) *  $limit['size']);
		}
	}

	/**
	 * Undocumented function
	 *
	 * @param array $filter
	 * @param array $columns
	 * @return array
	 */
	private function fetch(array $filter = [], $columns = [], &$associated = [])
	{

		$this->setFilters($filter);

		$result = null;
		$fields = [];
		$imploded = null;
		$results = null;

		if (empty($columns)) {
			$imploded = implode(",", $this->selectColumns);
			$resultInterface =  $this->builder->select()->get();
			$results = $resultInterface->getResult();
		} else {
			$fields = $columns;
			$imploded = implode(",", $fields);
			$results = $this->builder->select($imploded)->get()->getResult();
		}

		$result = $this->setToEntity($results, $associated);

		return $result;
	}

	/**
	 * convert all result to intended entiry
	 *
	 * @param stdClass[] $results
	 * @return array;
	 */
	private function setToEntity($results, &$associated = [])
	{
		$objects = [];
		foreach ($results as $key => $result) {
			$obj = new $this->entityClass;

			foreach ($this->props['props'] as $key => $value) {
				// if (!is_null($result->$key)) {
				$method = 'set' . $key;
				if (!$value['isEntity']) {
					if (!is_null($result->$key)) {
						if ($value['type'] != 'DateTime') {
							if($value['type'] == 'boolean')
								$obj->$method((bool)$result->$key);

							$obj->$method($result->$key);
						} else {
							$newDate = new DateTime($result->$key);
							$obj->$method($newDate);
						}
					}
				}
				else {
					if (isset($value['foreignKey'])) {
						$foreignKey = $value['foreignKey'];
						if (!is_null($result->$foreignKey)) {
							$associated[$value['foreignKey']][] = $result->$foreignKey;
							$obj->constraints[$value['foreignKey']] = $result->$foreignKey;
						}
					}

				}
			}
			$objects[] = $obj;
		}

		$newAssociated = [];
		foreach($associated as $key => $value){
			$newAssociated[$key] = array_unique($associated[$key]);
		}

		$associated = $newAssociated;

		return $objects;
	}

	/**
	 * @inheritDoc
	 */
	public function collect($filter = [])
	{
		$associated = [];
		$result = $this->fetch($filter, [] , $associated);
		$entityList = new EntityList($result);
		$entityList->setListOf($this->entityClass);
		$entityList->setAssociatedKey($associated);
		return $entityList;
	}

	/**
	 * @inheritDoc
	 */
	public function count($filter)
	{
		$result = $this->findAll($filter);
		return empty($result) ? 0 : count($result);
	}

	/**
	 * @inheritDoc
	 */
	public static function datatables($filter = [], $returnEntity = true, $useIndex = true)
	{
		$datatables = new Datatables($filter, $returnEntity, $useIndex, static::class);
		return $datatables;
	}
}
