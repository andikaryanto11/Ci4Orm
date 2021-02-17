<?php
namespace AndikAryanto11;
use Andikaryanto11\Exception\DatabaseException;
use Andikaryanto11\Exception\EloquentException;


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
class Eloquent {

    /**
	 * Database Connection
	 *
	 * @var ConnectionInterface
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
    public function __construct(&$db)
    {
        if(!property_exists(get_class($this), 'table')){
            throw EloquentException::forNoTableName(get_class($this));
        }

        self::$db = $db;
        $this->builder = self::$db->table($this->getTableName());
        $this->fields = self::$db->getFieldNames($this->getTableName());
    }

    public function getTableName(){
        return $this->table;
    }

    /**
     * Check if intance has changed value from orginal daata
     * 
     * @return boolean
     */
    public function isDirty(){
        $clonedData = static::find($this->{static::$primaryKey});
        foreach($this as $key => $value){
            if($value != $clonedData->$key)
            {
                return true;
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
    public function isDataExist(array $filter){
        $params = [
            "where" => $filter
        ];
        
        $data = static::findAll($params);
        if(count($data) > 0){
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
    public static function count(array $filter, $returnEntity = true){
        
        $data = static::findAll($filter, $returnEntity);
        return count($data);
    }

    /**
     * @param int $id
     * @return App\Eloquents|null
     * 
     * get data from table by Id
     */
    public static function find($id){
        $where = [
            'where' => [
                static::$primaryKey => $id
            ]
        ];
        $data = static::findAll($where);
        if(count($data) > 0){
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

    public static function findOrNew($id){
       
        $where = [
            'where' => [
                static::$primaryKey => $id
            ]
        ];
        $data = static::findAll($where);
        if(empty($data))
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
    public static function findOrFail($id){
        $where = [
            'where' => [
                static::$primaryKey => $id
            ]
        ];
        $data = static::findAll($where);
        if(count($data) == 0)
            throw new DatabaseException("Cannot find data with id:$id");
        return $data[0];
    }

    /**
     * @param array $filter
     * @return App\Eloquent
     * 
     * get first data of result from table   
     */
    public static function findOne(array $filter = []){
       
        $data = static::findAll($filter);
        if(empty($data))
            return null;
        return $data[0];
    }

    /**
     * @param array $filter
     * @return App\Eloquent
     * 
     * get first data of result from table   
     */
    public static function findOneOrNew(array $filter = []){
       
        $data = static::findAll($filter);
        if(empty($data))
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
    public static function findOneOrFail(array $filter = []){
       
        $data = static::findAll($filter);
        if(empty($data))
            return new DatabaseException("Cannot find any data");
        return $data[0];
    }

    /**
     * @param array $filter
     * @return mixed array App\Eloquent | this
     * 
     * get all data result from table
     */
    public static function findAll(array $filter = [], $returnEntity = true, $columns = [], $chunked = false){
        $entity = new static(self::$db);
        $entity->filter = $filter;
        if($chunked)
            return $entity;

        $result = $entity->fetch($filter, $returnEntity,  $columns);
        if(count($result) > 0 ){
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
    public static function findAllOrFail(array $filter = [], $returnEntity = true, $columns = []){
        $entity = new static(self::$db);
        $result = $entity->fetch($filter, $returnEntity,  $columns);
        if(count($result) > 0){
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
    public function chunk($columnName){
        
        $result = $this->fetch($this->filter);
        if(count($result) > 0 ){
            $chunkedData = [];
            foreach($result as $res){
                $chunkedData[] = $res->$columnName;
            }
            return $chunkedData;
        }
        return [];
    }

    /**
     * @param array $filter
     * @return array App\Eloquent
     * 
     * get all data result from table
     */
    public function fetch(array $filter = [], $returnEntity = true, $columns = []){

        if(!empty($filter)){
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

            if ($join){
                foreach($join as $key => $vv){
                    foreach($vv as $v){
                        $type="";
                        if(isset($v['type'])){
                            $type = $v['type'];
                        }
                        $this->builder->join($key, $v['key'],$type);
                    }
                }
            }
            if ($where)
                $this->builder->where($where);

            if ($orwhere)
                $this->builder->orWhere($orwhere);

            if ($wherein){
                foreach($wherein as $key => $v){
                    if(!empty($v))
                        $this->builder->whereIn($key, $v);
                }
            }

            if ($orwherein){
                foreach($orwherein as $key => $v){
                    if(!empty($v))
                        $this->builder->orWhereIn($key, $v);
                }
            }
            
            if ($wherenotin){
                foreach($wherenotin as $key => $v){
                    if(!empty($v))
                        $this->builder->whereNotIn($key, $v);
                }
            }


            if ($like)
                $this->builder->like($like);

            if ($orlike)
                $this->builder->orLike($orlike);
            
            if ($orlike){
                foreach($orlike as $key => $v){
                    if(!empty($v))
                        $this->builder->orLike($key, $v);
                }
            }

            if ($notlike){
                foreach($notlike as $key => $v){
                    if(!empty($v))
                        $this->builder->notLike($key, $v);
                }
            }

            if ($ornotlike){
                foreach($ornotlike as $key => $v){
                    if(!empty($v))
                        $this->builder->orNotLike($key, $v);
                }
            }

            if($group){
                $this->builder->groupStart();
                foreach($group as $key => $v){
                    if($key == 'orLike'){
                        foreach($v as $orLikeKey => $orLikeValue){
                            $this->builder->orLike($orLikeKey, $orLikeValue);
                        }
                    }
                }
                $this->builder->groupEnd();
            }

            if ($order){
                foreach($order as $key => $v){
                    if(!empty($v))
                        $this->builder->orderBy($key, $v);
                }
            }

            if ($limit)
                $this->builder->limit($limit['size'], ($limit['page'] - 1) *  $limit['size']);
        }
        $result = null;
        if($returnEntity){
            $fields = self::$db->getFieldNames($this->table);

            $imploded = implode(",".$this->table.".",$fields);
            $result = $this->builder->select($this->table.".".$imploded)->get()->getResult(get_class($this));
        } else {
            $imploded = implode(",", $columns);
            $result = $this->builder->select($imploded)->get()->getResult();
        }

        // $result[] = self::$db->getLastQuery()->getQuery();
            
        // echo json_encode($result);
        return $result;
        
    }

    /**
     * will be executed before save function
     */
    public function beforeSave(){
    }

    /**
     * @return bool
     * insert new data to table if $Id is empty or null other wise update the data
     */

    public function save(){
        $data = [];
        $this->beforeSave();
        foreach($this->fields as $field){
            $data[$field] = $this->$field;
        }
        if(empty($this->{static::$primaryKey}) || is_null($this->{static::$primaryKey})){
            if($this->builder->set($data, true)->insert()){
                $this->{static::$primaryKey} =static::$db->insertID();
                return true;
            }
        } else{
            $this->builder->where(static::$primaryKey, $this->{static::$primaryKey});
            if($this->builder->update($data)){
                return true;
            }
        }
        return false;
    }

    public function delete(){
        $this->builder->where(static::$primaryKey, $this->{static::$primaryKey});
        if(!$this->builder->delete())
            return false;
        return true;
    }

    /**
     * @param string $relatedEloquent Relates Table \App\Eloquent\YourClass
     * @param string $foreignKey key name of this Eloquent
     * @return Eloquent Object or null
     * 
     * Get parent related table data
     */
    public function hasOne(string $relatedEloquent, string $foreignKey, $params = []){
        if (!empty($this->$foreignKey)) {
            if(empty($params)){
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
    public function hasOneOrNew(string $relatedEloquent, string $foreignKey, $params = []){
        $result = $this->hasOne($relatedEloquent, $foreignKey, $params);
        if(!is_null($result)){
            return $result;
        }
        return new $relatedEloquent;
    }

    /**
     * @param string $relatedEloquent Relates Table \App\Eloquent\YourClass
     * @param string $foreignKey key name of this Eloquent
     * @return Eloquent Object or Error
     * 
     * Get parent related table data
     */
    public function hasOneOrFail(string $relatedEloquent, string $foreignKey, $params = []){
        $result = $this->hasOne($relatedEloquent, $foreignKey, $params);
        if(!is_null($result)){
            return $result;
        }
        return new DatabaseException("Cannot find any data");
    }
    
    /**
     * @param string $relatedEloquent Relates Table \App\Eloquent\YourClass
     * @param string $foreignKey key name of related Eloquent
     * @param string $params param to filter data
     * @return Eloquent array Object or null
     * 
     * Get child related table data
     */
    public function hasMany(string $relatedEloquent, string $foreignKey, $params = []){
        if(!property_exists(get_class($this), 'primaryKey')){
            throw EloquentException::forNoPrimaryKey(get_class($this));
        }

        if(!empty($this->{static::$primaryKey})){
          

            if (isset($params['where'])) {
                $params['where'][$foreignKey] = $this->{static::$primaryKey};
            } else {
                $params['where'] = [
                    $foreignKey => $this->{static::$primaryKey}
                ];
            }
            $result = $relatedEloquent::findAll($params);
            if(count($result) > 0){
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
    public function hasManyOrFail(string $relatedEloquent, string $foreignKey, $params = array()){
          
        $result = $this->hasMany($relatedEloquent, $foreignKey, $params);
        if(!is_null($result)){
            return $result;
        }
        throw new DatabaseException("Cannot find any data");;
    }

}