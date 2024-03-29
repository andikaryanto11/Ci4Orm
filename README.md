# Codeigniter 4 ORM
This package is created to map your database to entity / Class you make. 
Inspired by .NET Enitity Framwork.

# Rules
  Create class then Extends to this Eloquent

# Install
  
    composer require andikaryanto11/ci4orm

# Props
  - protected $table = "your_table_name" is mandatory property you have to set
  - static $primaryKey = "your_primary_key_field_name" is mandatory property you have to set

        
        use AndikAryanto11\Eloquent;
        class MyEloquent extends Eloquent{

          public $FieldInTable;
          public $AnotherFieldInTable;

          protected $table = "MyTable";
          static $primaryKey = "MyPKfield";

          public function __construct()
          {
              $db = \Config\Database::connect();
              parent::__construct($db);
          }
        }

  - protected $hideFieldValue = [ ] , when you put field  in this array the field will be unset when you fetch data from eloquent

        protected $hideFieldValue = [ 'Password' ]

  - protected $cast = [], when you put field  in this array the field will be casted to specific datatype when you fetch data from eloquent. available cast datatype will be : 
        - integer
        - boolean
        - decimal:2 -> 2 will be decimal digit
        - datetime:Y-m-d -> Y-m-d will be formatted date
        - string 

        protected $cast = [
          'Id'             => 'integer',
          'IsLoggedIn'     => 'boolean',
          'Paid'           => 'decimal:2',
          'Created'        => 'datetime:Y-m-d',
        ];



# Method
  - find($id)
  
    Will get data from your table with "id"
    
        Entity::find(1);
        // return your Entity Object with "Id" = 1 or null
        
  - findOrNew($id)
  
    Will get data from your table with "id" or new object if null
    
        Enitity::findOrNew(1);
        //return your Entity Object with "Id" = 1 or New object if no data found
        
  - findOrFail($id)
  
    Will get data from your table with "id" or new object if null
    
        Enitity::findOrFail(1);
        //return your Entity Object with "Id" = 1 or throw an error

  - findOne(array $params)
  
    Will get data from your table in first row from result
    
        $params = [
          "where" => [
            "colum_name" => "some_value"
          ]
        ];
        
        Entity::findOne($params);
        //$params is nullable
        //return first row of results or null;

  - findOneOrNew(array $params)

    Will get data from your table in first row from result or new object

        $params = [
          "where" => [
            "colum_name" => "some_value"
          ]
        ];
        
        Entity::findOneOrNew($params);
        //$params is nullable
        //return first row of results or new object;

        
  - findOneOrFail(array $params)

    Will get data from your table in first row from result or throw error

        $params = [
          "where" => [
            "colum_name" => "some_value"
          ]
        ];
        
        Entity::findOneOrFail($params);
        //$params is nullable
        //return first row of results or throw error;

  - findAll(array $params)

    Will get data from your table or null

        $params = [
          "where" => [
            "colum_name" => "some_value"
          ]
        ];
        
        Entity::findAll($params);
        //$params is nullable
        //return first row of results or null;

  - findAllOrFail(array $params)

    Will get data from your table or throw error

        $params = [
          "where" => [
            "colum_name" => "some_value"
          ]
        ];
        
        Entity::findAll($params);
        //$params is nullable
        //return first row of results or throw error;

  - beforeSave()

    Will be executed before save method. Override this method if you wanna do something before save.

  - save()

    Will store your data to table or update your data,
    If your "primary_key" entity is null then it will save data otherwise will update;

        $ent = new Entity();
        $ent->Name = "whatever";
        $ent->save();
        //insert

        $ent = Entity::find(1);
        $ent->Name = "whatever";
        $ent->save();
        //update

  - hasOne(string $relatedEloquent, string $foreignKey)

    Will get your related table data parent or null

        $ent = Entity::find(1);
        $parent = $ent->hasOne("Your\EntityNamespace\EntityName", "$ent foregin_key_name");
        // $parent is data parent of your related table;

  - hasOneOrNew(string $relatedEloquent, string $foreignKey)

    Will get your related table data parent or new object

        $ent = Entity::find(1);
        $parent = $ent->hasOneOrNew("Your\EntityNamespace\EntityName", "$ent foregin_key_name");
        // $parent is data parent of your related table or new object;

  - hasOneOrFail(string $relatedEloquent, string $foreignKey)

    Will get your related table data parent or throw error

        $ent = Entity::find(1);
        $parent = $ent->hasOneOrNew("Your\EntityNamespace\EntityName", "$ent foregin_key_name");
        // $parent is data parent of your related table or new object;

  - hasMany(string $relatedEloquent, string $foreignKey, array $params)

    Will get your related table data child or null

        $ent = Entity::find(1);

        $params = [
          "where" => [
            "colum_name" => "some_value"
          ]
        ];

        $child = $ent->hasMany("Your\EntityNamespace\EntityName", "$ent foregin_key_name", $params);
        //$params is nullable
        // $child is data child of your related table or new object;

    
  - hasManyOrFail(string $relatedEloquent, string $foreignKey, array $params)

    Will get your related table data child or throw error

        $ent = Entity::find(1);

        $params = [
          "where" => [
            "colum_name" => "some_value"
          ]
        ];

        $child = $ent->hasMany("Your\EntityNamespace\EntityName", "$ent foregin_key_name", $params);
        //$params is nullable
        // $child is data parent of your related table or new object;

  - hasFirst(string $relatedEloquent, string $foreignKey, $params = [])
    Will get first data

        $ent = Entity::find(1);

        $params = [
          "where" => [
            "colum_name" => "some_value"
          ]
        ];

        $child = $ent->hasFirst("Your\EntityNamespace\EntityName", "$ent foregin_key_name", $params);
        //$params is nullable
        // $child is data parent of your related table or new object;

  - collect(array $filter = [])
    Will return EloquentList, open in vendor folder to see available method.

        $obj = Entity::collect()
  
  - paging($filter = [], $page = 1, $size = 6, $showedPage = 5, $queryParams = [])
    Will return array of data and othe property

        $obj = Entity::paging([], 1, 6, 5, []);

  - datatables(array $filter = [], boolean $returnEntity = true, boolean $useIndex = true)
    Will return EloquentDatatables, open vendor folder to see available method.


        $obj = Entity::datatables([], true, true);

# Params ($params)

  General for $params that's used to filter data

        $params = [
          "join" => [
            "table_name" => [[
                "key" => "table_name.key = table_name.key",
                "type" => "LEFT" || "RIGHT" //optional
            ]]
          ],
          "you can add more key params below, 'where', 'whereIn', etc"
        ];

        $params = [
          "where" => [
            "colum_name" => "some_value"
          ]
        ];

        
        $params = [
          "orWhere" => [
            "colum_name" => "some_value"
          ]
        ];

        $params = [
          "whereIn" => [
            "colum_name" => ["some_value", "other_value"]
          ]
        ];

        $params = [
          "orWhereIn" => [
            "colum_name" => ["some_value", "other_value"]
          ]
        ];

        $params = [
          "whereNotIn" => [
            "colum_name" => ["some_value", "other_value"]
          ]
        ];

        $params = [
          "like" => [
            "colum_name" => "some_value"
          ]
        ];

        $params = [
          "orLike" => [
            "colum_name" => "some_value"
          ]
        ];

        $params = [
          "orLike" => [
            "colum_name" => "some_value"
          ]
        ];

        $params = [
          "order" => [
            "colum_name" => "ASC",
            "colum_name" => "DESC"
          ]
        ];

        $params = [
          "limit" => [
            "page" => "ASC",
            "size" => "DESC"
          ]
        ];





  