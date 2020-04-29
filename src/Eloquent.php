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
    protected $db;

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

    
    public function __construct()
    {
        if(!property_exists(get_class($this), 'table')){
            throw EloquentException::forNoTableName(get_class($this));
        }

        $this->db = \Config\Database::connect();
        $this->builder = $this->db->table($this->table);
        $this->fields = $this->db->getFieldNames($this->table);
    }
     
    /**
     * @param array $filter
     * @return bool
     * 
     * check if data exist
     */
    public function isDataExist(array $filter){
        
        $data = static::findAll($filter);
        if(count($data) > 0){
            return true;
        }
        return false;
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
            return new static;
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
    public static function findOne(array $filter = null){
       
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
    public static function findOneOrNew(array $filter = null){
       
        $data = static::findAll($filter);
        if(empty($data))
            return new static;
        return $data[0];
    }

    /**
     * @param array $filter
     * @return App\Eloquent
     * 
     * get first data of result from table or throw error  
     * 
     */
    public static function findOneOrFail(array $filter = null){
       
        $data = static::findAll($filter);
        if(empty($data))
            return new DatabaseException("Cannot find any data");
        return $data[0];
    }

    /**
     * @param array $filter
     * @return array App\Eloquent
     * 
     * get all data result from table
     */
    public static function findAll(array $filter = null){
        $entity = new static;
        $result = $entity->fetch($filter);
        if(count($result) > 0 ){
            return $result;
        }
        return null;
    }

    /**
     * @param array $filter
     * @return array App\Eloquent
     * 
     * get all data result from table or throw error
     */
    public static function findAllOrFail(array $filter = null){
        $entity = new static;
        $result = $entity->fetch($filter);
        if(count($result > 0)){
            return $result;
        }
        throw new DatabaseException("Cannot find any data");
    }

    /**
     * @param array $filter
     * @return array App\Eloquent
     * 
     * get all data result from table
     */
    public function fetch(array $filter = null){

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

        if ($join)
            foreach($join as $key => $v){
                $type="";
                if(isset($v['type'])){
                    $type = $v['type'];
                }
                $this->builder->join($key, $v['key'],$type);
            }

        if ($where)
            $this->builder->where($where);

        if ($orwhere)
            $this->builder->orWhere($orwhere);

        if ($wherein){
            foreach($wherein as $key => $v){
                $this->builder->whereIn($key, $v);
            }
        }

        if ($orwherein){
            foreach($orwherein as $key => $v){
                $this->builder->orWhereIn($key, $v);
            }
        }
        
        if ($wherenotin){
            foreach($wherenotin as $key => $v){
                $this->builder->whereNotIn($key, $v);
            }
        }


        if ($like)
            $this->builder->like($like);

        if ($orlike)
            $this->builder->orLike($orlike);
        
        if ($orlike){
            foreach($orlike as $key => $v){
                $this->builder->orLike($key, $v);
            }
        }

        if ($notlike){
            foreach($notlike as $key => $v){
                $this->builder->notLike($key, $v);
            }
        }

        if ($ornotlike){
            foreach($ornotlike as $key => $v){
                $this->builder->orNotLike($key, $v);
            }
        }

        if ($order){
            foreach($order as $key => $v){
                $this->builder->orderBy($key, $v);
            }
        }

        if ($limit)
            $this->builder->limit($limit['size'], ($limit['page'] - 1) *  $limit['size']);

        $result = $this->builder->get()->getResult(get_class($this));

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
        foreach($this->fields as $field){
            $data[$field] = $this->$field;
        }
        if(empty($this->{static::$primaryKey}) || is_null($this->{static::$primaryKey})){
            if($this->builder->set($data, true)->insert()){
                $this->{static::$primaryKey} = $this->db->insertID();
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

    /**
     * @param string $relatedEloquent Relates Table \App\Eloquent\YourClass
     * @param string $foreignKey key name of this Eloquent
     * @return Eloquent Object or null
     * 
     * Get parent related table data
     */
    public function hasOne(string $relatedEloquent, string $foreignKey){
        if (!empty($this->$foreignKey)) {
            $result = $relatedEloquent::find($this->$foreignKey);
            return $result;
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
    public function hasOneOrNew(string $relatedEloquent, string $foreignKey){
        $result = $this->hasOne($relatedEloquent, $foreignKey);
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
    public function hasOneOrFail(string $relatedEloquent, string $foreignKey){
        $result = $this->hasOne($relatedEloquent, $foreignKey);
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
    public function hasMany(string $relatedEloquent, string $foreignKey, $params = array()){
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