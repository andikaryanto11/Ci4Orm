<?php
namespace Ci4Orm\Repository;

use Ci4Orm\Entity\Entity;
use Ci4Orm\Entity\ORM;
use CodeIgniter\Database\BaseBuilder;
use DateTime;

class Repository {

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
	 * @param string $entityClass
	 */
	public function __construct(string $entityClass) {
		$this->entityClass = $entityClass;
		$this->props = ORM::getProps($this->entityClass);
        $this->builder = \Config\Database::connect()->table($this->props['table']);
		$this->selectColumns = ORM::getSelectColumns($this->entityClass);
	}


	/**
	 *
	 * @param int|string $id
	 * @return mixed
	 */
	public function find($id){
		$param = [
			'where' => [
				$this->props['primaryKey'] => $id
			]
		];

		$result = $this->fetch($param);
		if(count($result) > 0){
			return $result[0];
		}
		return null;
	}

	/**
	 * Undocumented function
	 *
	 * @param array $filter
	 * @param array $columns
	 * @return array
	 */
	public function findAll(array $filter = [], $columns = []){
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
    public function fetch(array $filter = [], $columns = [])
    {

        $this->setFilters($filter);

        $result = null;
        $fields = [];
        $imploded = null;
        $results = null;

		if (empty($columns)) {
			$imploded = implode(",", $this->selectColumns);
			$results = $this->builder->select()->get()->getResult();
		} else {
			$fields = $columns;
			$imploded = implode(",", $fields);
			$results = $this->builder->select($imploded)->get()->getResult();
		}

		$result = $this->setToEntity($results);

        return $result;
    }

	/**
	 * convert all result to intended entiry
	 *
	 * @param stdClass[] $results
	 * @return array;
	 */
	public function setToEntity($results) {
        $objects = [];
        foreach($results as $result) {
            $obj = new $this->entityClass;
            foreach($this->props['props'] as $key => $value) {
				$method = 'set'.$key;
                if (!$value['isEntity']) {
					if($value['type'] != 'datetime') {
                    	$obj->$method($result->$key);
					} else {
						$newDate = new DateTime($result->$key);
                    	$obj->$method($newDate);
					}
                } else {
                    $instanceRelatedClass = new self($value['type']);
					$foreignKey = $value['foreignKey'];
                    $instance = $instanceRelatedClass->find($result->$foreignKey);
                    $obj->$method($instance);
                }
            }
            $objects[] = $obj;
        }
        return $objects;
    }

}
