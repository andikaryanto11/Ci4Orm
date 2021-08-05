<?php

namespace AndikAryanto11;

use AndikAryanto11\Exception\DatabaseException;
use AndikAryanto11\Exception\EloquentException;
use AndikAryanto11\Libraries\Cast;
use AndikAryanto11\Libraries\EloquentDatatables;
use AndikAryanto11\Libraries\EloquentList;
use AndikAryanto11\Libraries\EloquentPaging;
use CodeIgniter\Database\BaseConnection;
use Exception;
use ReflectionClass;
use stdClass;

/**
 * Class Eloquent
 * 
 * Eloquent make getting data from database more usefull.
 * It will get data and the related table data with easy function
 * 
 * 
 * @package CodeIgniter
 * 
 */
class Eloquent
{

    /**
     * Database Connection
     *
     * @var BaseConnection
     */
    protected static $db;

    /**
     * Database Builder
     * 
     * @var BaseBuilder
     */
    protected $builder;

    /**
     * Field Exist On Intended Table
     */
    protected $fields;

    /**
     * Primary Key Field;
     */
    static $primaryKey;

    /**
     * filter params;
     */
    protected $filter;

    /**
     * @param $db is \Config\Database::connect();
     * 
     */

    /**
     * Default data to output is escaped, set this field to non escape field
     */
    protected $nonEscapedField = [];

    /**
     * Data will be escaped if set to true
     */
    protected $escapeToOutput = true;

    /**
     * Hide field value for some field(s), data will be set to null 
     * 
     */
    protected $hideFieldValue = [];

    /**
     * Cast field to intended value,
     * ex : [
     *      "Field" => Cast::BOOLEAN
     * ]
     */
    protected $cast = [];

    /**
     * Eager Load related classes
     */
    private $relatedClass = [];

    /**
     * Orignal Data
     */

    private $originalData;

    public function __construct(&$db)
    {
        if (!property_exists(get_class($this), 'table')) {
            throw EloquentException::forNoTableName(get_class($this));
        }
        helper('inflector');
        self::$db = $db;
        $this->builder = self::$db->table($this->getTableName());
        $this->fields = static::getProperties();
    }

    public function getTableName()
    {
        return $this->table;
    }

    /**
     * get columns of table
     */
    public static function getProperties()
    {
        $class = new ReflectionClass(static::class);
        $props = $class->getDefaultProperties();
        unset($props['primaryKey']);
        unset($props['db']);
        unset($props['table']);
        unset($props['hideFieldValue']);
        unset($props['dbs']);
        unset($props['validation']);
        unset($props['request']);
        unset($props['builder']);
        unset($props['fields']);
        unset($props['filter']);
        unset($props['nonEscapedField']);
        unset($props['escapeToOutput']);
        unset($props['cast']);
        unset($props['originalData']);
        $newProps = [];
        foreach ($props as $key => $value) {
            $newProps[] = $key;
        }
        return $newProps;
    }

    /**
     * Check if intance has changed value from orginal daata
     * 
     * @return boolean
     */
    public function isDirty()
    {
        if (empty($this->{static::$primaryKey}))
            return true;

        $clonedData = $this->getOriginalData();
        if (empty($clonedData))
            return true;

        foreach ($this as $key => $value) {
            if (in_array($key, $this->fields)) {
                if ($value != $clonedData->$key) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param array $filter
     * @return bool
     * 
     * check if data exist
     */
    public function isDataExist(array $filter)
    {
        $params = [
            "where" => $filter
        ];

        $data = static::findAll($params);
        if (count($data) > 0) {
            return true;
        }
        return false;
    }

    /**
     * @param array $filter
     * @return int
     * 
     * Count All data
     */
    public static function count(array $filter, $returnEntity = true)
    {

        // $data = static::findAll($filter, $returnEntity);

        $entity = new static(self::$db);
        return $entity->countData($filter);
        // return count($data);
    }

    /**
     * @param int $id
     * @return App\Eloquents|null
     * 
     * get data from table by Id
     */
    public static function find($id)
    {
        $where = [
            'where' => [
                static::$primaryKey => $id
            ]
        ];
        $data = static::findAll($where);
        if (count($data) > 0) {
            return $data[0];
        }
        return null;
    }

    /**
     * @param int $id
     * @return App\Eloquents
     * 
     * get data from table by Id or return new object
     */

    public static function findOrNew($id)
    {

        $where = [
            'where' => [
                static::$primaryKey => $id
            ]
        ];
        $data = static::findAll($where);
        if (empty($data))
            return new static(self::$db);
        return $data[0];
    }

    /**
     * @param int $id
     * @return App\Eloquents
     * @throws DbException
     * 
     * get data from table by Id or return throw error
     */
    public static function findOrFail($id)
    {
        $where = [
            'where' => [
                static::$primaryKey => $id
            ]
        ];
        $data = static::findAll($where);
        if (count($data) == 0)
            throw new DatabaseException("Cannot find data with id:$id");
        return $data[0];
    }



    /**
     * @param array $filter
     * @return App\Eloquent
     * 
     * get first data of result from table   
     */
    public static function findOne(array $filter = [])
    {

        $data = static::findAll($filter);
        if (empty($data))
            return null;
        return $data[0];
    }

    /**
     * @param array $filter
     * @return App\Eloquent
     * 
     * get first data of result from table   
     */
    public static function findOneOrNew(array $filter = [])
    {

        $data = static::findAll($filter);
        if (empty($data))
            return new static(self::$db);
        return $data[0];
    }

    /**
     * @param array $filter
     * @return App\Eloquent
     * 
     * get first data of result from table or throw error  
     * 
     */
    public static function findOneOrFail(array $filter = [])
    {

        $data = static::findAll($filter);
        if (empty($data))
            throw new DatabaseException("Cannot find any data");
        return $data[0];
    }

    /**
     * @param array $filter
     * @return mixed array App\Eloquent | this
     * 
     * get all data result from table
     */
    public static function findAll(array $filter = [], $returnEntity = true, $columns = [], $chunked = false)
    {
        $entity = new static(self::$db);
        $entity->filter = $filter;
        if ($chunked)
            return $entity;

        $result = $entity->fetch($filter, $returnEntity,  $columns);
        if (count($result) > 0) {
            return $result;
        }
        return [];
    }

    /**
     * @param array $filter
     * @return array App\Eloquent
     * 
     * get all data result from table or throw error
     */
    public static function findAllOrFail(array $filter = [], $returnEntity = true, $columns = [])
    {
        $entity = new static(self::$db);
        $result = $entity->fetch($filter, $returnEntity,  $columns);
        if (count($result) > 0) {
            return $result;
        }
        throw new DatabaseException("Cannot find any data");
    }

    /**
     * @param array $columnName
     * @return array Of specific column data
     * 
     * get all column data 
     */
    public function chunk($columnName)
    {

        $result = $this->fetch($this->filter);
        if (count($result) > 0) {
            $chunkedData = [];
            foreach ($result as $res) {
                $chunkedData[] = $res->$columnName;
            }
            return $chunkedData;
        }
        return [];
    }

    /**
     * result data converted to Intended Model
     * @return array
     */

    private function setToEntity($results, $type = "entity", $withRelatedData)
    {

        $listobject = [];
        foreach ($results as $result) {
            $newobject = null;
            if ($type = "entity") {
                $class = get_class($this);
                $newobject = new $class;
            } else {
                $newobject = new stdClass();
            }

            $related = new stdClass;
            $related->ClassName = null;
            $related->Data = null;
            $isFound = false;

            foreach ($result as $column => $value) {

                if (!in_array($column, $this->hideFieldValue)) {
                    if ($this->escapeToOutput) {
                        if (!in_array($column, $this->nonEscapedField)) {
                            $newobject->$column = esc($value);
                        } else {
                            $newobject->$column = $value;
                        }
                    } else {
                        $newobject->$column = $value;
                    }

                    if (array_key_exists($column, $this->cast)) {
                        Cast::casting($this->cast[$column], $newobject->$column);
                    }
                } else {
                    unset($this->$column);
                }
            }

            if (!is_null($withRelatedData)) {;
                foreach ($withRelatedData as $relatedData) {
                    $findRelated = function ($item) use ($newobject, $relatedData) {
                        return $newobject->{$relatedData->ForeignKey} == $item->{get_class($item)::$primaryKey};
                    };
                    $relatedDataFound = $relatedData->Data->where($findRelated);
                    $newobject->{$relatedData->ClassName} = empty($relatedDataFound) ? null : $relatedDataFound[0];
                }
            }

            $newobject->originalData = clone $newobject;

            $listobject[] = $newobject;
        }

        return $listobject;
    }

    /**
     * set filter to query builder
     * 
     * @param array $filter
     */

    public function setFilters($filter = [])
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
     * @param array $filter
     * @return array
     * 
     * get all data result from table
     */
    public function fetch(array $filter = [], $returnEntity = true, $columns = [])
    {

        $this->setFilters($filter);

        $result = null;
        $fields = [];
        $imploded = null;
        $results = null;
        $withRelated = null;
        if ($returnEntity) {

            if (empty($columns)) {
                $fields = static::getProperties();
                $imploded = implode("," . $this->table . ".", $fields);
                $results = $this->builder->select($this->table . "." . $imploded)->get()->getResult();
            } else {
                $fields = $columns;
                $imploded = implode(",", $fields);
                $results = $this->builder->select($imploded)->get()->getResult();
            }

            if (!empty($this->relatedClass))
                $withRelated = $this->fetchRelatedData($results);
            // echo json_encode($withRelated);
            $result = $this->setToEntity($results, "entity", $withRelated);
        } else {
            $imploded = implode(",", $columns);
            $results = $this->builder->select($imploded)->get()->getResult();
            if (!empty($this->relatedClass))
                $withRelated = $this->fetchRelatedData($results);
            $result = $this->setToEntity($results, "stdClass", $withRelated);
        }

        // $result[] = self::$db->getLastQuery()->getQuery();

        // echo json_encode($result);
        return $result;
    }

    /**
     * Eeager Load Query 
     */
    public static function with($relatedClasses)
    {
        $instance = new static(static::$db);
        foreach ($relatedClasses as $relatedClass) {
            $instance->relatedClass[] = $relatedClass;
        }
        return $instance;
    }

    /**
     * Get Related Data as array, used with "with" function for Eager Load
     * @param array $results of object
     * @return array
     */

    private function fetchRelatedData($results)
    {
        $resultRelatedData = [];
        $collectionResult = new EloquentList($results);
        $fieldValues = null;
        foreach ($this->relatedClass as $related) {
            $nameSpace = $related["Class"];
            $nameSpaceArr = explode("\\", $nameSpace);
            $className = $nameSpaceArr[count($nameSpaceArr) - 1];
            $fieldValues = $collectionResult->chunkUnique($related["ForeignKey"]);

            $params = [
                "whereIn" => [
                    $nameSpace::$primaryKey => $fieldValues
                ]
            ];

            $fetchedData = $nameSpace::collect($params);
            $result = [
                "ForeignKey" => $related["ForeignKey"],
                "ClassName" => $className,
                "Data" => $fetchedData
            ];
            $resultRelatedData[] = (object)$result;
        }
        return $resultRelatedData;
    }


    /**
     * count table data
     * @param array $filter
     * @return int
     */
    public function countData(array $filter = [])
    {
        $this->setFilters($filter);
        $result = $this->builder->selectCount($this->table . "." . static::$primaryKey)->get()->getResult();
        return (int)$result[0]->{static::$primaryKey};
    }


    /**
     * will be executed before save function
     */
    public function beforeSave()
    {
    }

    /**
     * @return bool
     * insert new data to table
     */
    private function insert($data)
    {
        if ($this->builder->set($data, true)->insert()) {
            $this->{static::$primaryKey} = static::$db->insertID();
            return true;
        }

        return false;
    }

    /**
     * @return bool
     * update new data to table
     */
    private function update($data)
    {
        $this->builder->where(static::$primaryKey, $this->{static::$primaryKey});
        if ($this->builder->update($data)) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     * insert new data to table if $Id is empty or null other wise update the data
     * @param bool $isAutoIncrement your primary key of table
     */

    public function save($isAutoIncrement = true)
    {
        $data = [];
        if (!$this->isDirty()) {
            return true;
        }

        $this->beforeSave();
        foreach ($this->fields as $field) {
            if (is_null($this->$field)) {
                $data[$field] = null;
                continue;
            }

            $data[$field] = $this->$field;
        }
        if (empty($this->{static::$primaryKey}) || is_null($this->{static::$primaryKey})) {
            return $this->insert($data);
        } else {
            if ($isAutoIncrement) {
                return $this->update($data);
            } else {
                $existedData = static::find($this->{static::$primaryKey});
                if (!$existedData) {
                    return $this->insert($data);
                } else {
                    return $this->update($data);
                }
            }
        }
        return false;
    }

    /**
     * Delete data where primary key in object is not null, throw while primary key null
     * @throws DatabaseException
     */
    public function delete()
    {
        if (empty($this->{static::$primaryKey}))
            throw new DatabaseException("Couldn't Find Any Data To Delete");

        $this->builder->where(static::$primaryKey, $this->{static::$primaryKey});
        if (!$this->builder->delete())
            return false;

        $this->{static::$primaryKey} = null;
        return true;
    }

    /**
     * @param string $relatedEloquent Relates Table \App\Eloquent\YourClass
     * @param string $foreignKey key name of this Eloquent
     * @return Eloquent Object or null
     * 
     * Get parent related table data
     */
    public function hasOne(string $relatedEloquent, string $foreignKey, $params = [])
    {
        if (!empty($this->$foreignKey)) {
            if (empty($params)) {
                $result = $relatedEloquent::find($this->$foreignKey);
                return $result;
            } else {
                if (isset($params['where'])) {
                    $params['where'][$foreignKey] = $this->$foreignKey;
                } else {
                    $params['where'] = [
                        $foreignKey => $this->$foreignKey
                    ];
                }
                $result = $relatedEloquent::findOne($params);
                return $result;
            }
        }

        return null;
    }

    /**
     * @param string $relatedEloquent Relates Table \App\Eloquent\YourClass
     * @param string $foreignKey key name of this Eloquent
     * @return Eloquent Object or New Object
     * 
     * Get parent related table data
     */
    public function hasOneOrNew(string $relatedEloquent, string $foreignKey, $params = [])
    {
        $result = $this->hasOne($relatedEloquent, $foreignKey, $params);
        if (!is_null($result)) {
            return $result;
        }
        return new $relatedEloquent;
    }

    /**
     * @param string $relatedEloquent Relates Table \App\Eloquent\YourClass
     * @param string $foreignKey key name of this Eloquent
     * @param array $params 
     * @return Eloquent Object or Error
     * 
     * Get parent related table data
     */
    public function hasOneOrFail(string $relatedEloquent, string $foreignKey, $params = [])
    {
        $result = $this->hasOne($relatedEloquent, $foreignKey, $params);
        if (!is_null($result)) {
            return $result;
        }
        throw new DatabaseException("Cannot find any data");
    }

    /**
     * Reverse of has one
     * @param string $relatedEloquent Relates Table \App\Eloquent\YourClass
     * @param string $foreignKey key name of this Eloquent
     * @param array $params 
     * @return null
     */
    public function belongsTo(string $relatedEloquent, string $foreignKey, $params = [])
    {

        if (!empty($this->$foreignKey)) {
            if (empty($params)) {
                $result = $relatedEloquent::find($this->$foreignKey);
                return $result;
            } else {
                if (isset($params['where'])) {
                    $params['where'][$relatedEloquent::$primaryKey] = $this->$foreignKey;
                } else {
                    $params['where'] = [
                        $relatedEloquent::$primaryKey => $this->$foreignKey
                    ];
                }
                $result = $relatedEloquent::findOne($params);
                return $result;
            }
        }

        return null;
    }

    /**
     * Reverse of has one
     * @param string $relatedEloquent Relates Table \App\Eloquent\YourClass
     * @param string $foreignKey key name of this Eloquent
     * @param array $params 
     * @return Eloquen
     * @throws DatabaseException
     */
    public function belongsToOrFail(string $relatedEloquent, string $foreignKey, $params = [])
    {

        $result = $this->belongsTo($relatedEloquent, $foreignKey, $params);
        if (!is_null($result)) {
            return $result;
        }
        throw new DatabaseException("Cannot find any data");
    }

    /**
     * @param string $relatedEloquent Relates Table \App\Eloquent\YourClass
     * @param string $foreignKey key name of related Eloquent
     * @param string $params param to filter data
     * @return Eloquent array Object or null
     * 
     * Get child related table data
     */
    public function hasMany(string $relatedEloquent, string $foreignKey, $params = [])
    {
        if (!property_exists(get_class($this), 'primaryKey')) {
            throw EloquentException::forNoPrimaryKey(get_class($this));
        }

        if (!empty($this->{static::$primaryKey})) {


            if (isset($params['where'])) {
                $params['where'][$foreignKey] = $this->{static::$primaryKey};
            } else {
                $params['where'] = [
                    $foreignKey => $this->{static::$primaryKey}
                ];
            }
            $result = $relatedEloquent::findAll($params);
            if (count($result) > 0) {
                return $result;
            }
        }
        return null;
    }

    /**
     * @param string $relatedEloquent Relates Table \App\Eloquent\YourClass
     * @param string $foreignKey key name of related Eloquent
     * @param string $params param to filter data
     * @return Eloquent array Object or null
     * 
     * Get child related table data
     */
    public function hasFirst(string $relatedEloquent, string $foreignKey, $params = [])
    {
        if (!property_exists(get_class($this), 'primaryKey')) {
            throw EloquentException::forNoPrimaryKey(get_class($this));
        }

        if (!empty($this->{static::$primaryKey})) {


            if (isset($params['where'])) {
                $params['where'][$foreignKey] = $this->{static::$primaryKey};
            } else {
                $params['where'] = [
                    $foreignKey => $this->{static::$primaryKey}
                ];
            }
            $result = $relatedEloquent::findAll($params);
            if (count($result) > 0) {
                return $result[0];
            }
        }


        return null;
    }


    /**
     * @param string $relatedEloquent Relates Table \App\Eloquent\YourClass
     * @param string $foreignKey key name of related Eloquent
     * @param string $params param to filter data
     * @return Eloquent array Object or null
     * 
     * Get child related table data
     */
    public function hasFirstOrNew(string $relatedEloquent, string $foreignKey, $params = [])
    {
        if (!property_exists(get_class($this), 'primaryKey')) {
            throw EloquentException::forNoPrimaryKey(get_class($this));
        }

        if (!empty($this->{static::$primaryKey})) {


            if (isset($params['where'])) {
                $params['where'][$foreignKey] = $this->{static::$primaryKey};
            } else {
                $params['where'] = [
                    $foreignKey => $this->{static::$primaryKey}
                ];
            }
            $result = $relatedEloquent::findAll($params);
            if (count($result) > 0) {
                return $result[0];
            }
        }
        return new $relatedEloquent;
    }

    /**
     * @param string $relatedEloquent Related Table \App\Eloquent\YourClass
     * @param string $foreignKey key name of related Eloquent
     * @param string $params param to filter data
     * @return Eloquent array Object or Error
     * 
     * Get child related table data
     */
    public function hasManyOrFail(string $relatedEloquent, string $foreignKey, $params = array())
    {

        $result = $this->hasMany($relatedEloquent, $foreignKey, $params);
        if (!is_null($result)) {
            return $result;
        }
        throw new DatabaseException("Cannot find any data");;
    }

    /**
     * get all data result from table
     * @param array $filter
     * @return EloquentList
     * 
     */
    public static function collect(array $filter = [])
    {
        $result = static::findAll($filter);
        return new EloquentList($result);
    }


    /**
     * get all data result from table
     * @param array $filter
     * @param int $page
     * @param int $size
     * @param int $shoedPage
     * @param array $queryParams wil be generated as query params
     * @return EloquentList
     * 
     */
    public static function paging($filter = [], $page = 1, $size = 6, $showedPage = 5, $queryParams = [])
    {
        $paging = new EloquentPaging(static::class, $filter, $page, $size, $showedPage, $queryParams);
        return $paging->fetch();
    }


    /**
     * Get datatables server side  results array
     * @param array $filter
     * @param boolean $returnEntity set to true array data will contain entity of class which call this function 
     * @param boolean $useIndex set to false if datatables in front end use column name
     * @return EloquentDatatables
     * 
     */
    public static function datatables($filter = [], $returnEntity = true, $useIndex = true)
    {
        $datatables = new EloquentDatatables($filter, $returnEntity, $useIndex, static::class);
        return $datatables;
    }

    /**
     * Return data before it's modified
     * @return static 
     */
    public function getOriginalData()
    {
        return $this->originalData;
    }

    /**
     * Batch delete data from entity table with ids
     * @return boolean
     */
    public static function batchDelete(array $ids)
    {
        $params = [
            "whereIn" => [
                static::$primaryKey => $ids
            ]
        ];

        return static::remove($params);
    }

    /**
     * Batch delete data from entity table with ids
     * @return boolean
     * @throws DatabaseException
     */
    public static function batchDeleteOrError(array $ids)
    {
        $params = [
            "whereIn" => [
                static::$primaryKey => $ids
            ]
        ];

        if(static::remove($params))
            return true;

        throw new DatabaseException("Something went wrong while deleting the data");
    }

    /**
     * Remove Data with condition
     * @return boolean
     */
    public static function remove(array $params){
        $instance = static::newInstance();
        $instance->setFilters($params);
        if($instance->builder->delete())
            return true;
        return false;

    }

    /**
     * Remove Data with condition
     * @return boolean
     * @throws DatabaseException
     */
    public static function removeOrError(array $params){
        if(static::remove($params))
            return true;
        throw new DatabaseException("Something went wrong while deleting the data");

    }

    /**
     * new static instance
     */
    private static function newInstance(){
        return new static(static::$db);
    }
}
