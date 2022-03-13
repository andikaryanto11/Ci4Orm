<?php

namespace Ci4Orm\Entities;


class ORM
{

    /**
     * Get props
	 * @param string$entityName
	 * @return array
     */
    public static function getProps(string $entityName)
    {
		$parse = self::parse();
		foreach($parse as $key => $item){
			if($entityName == $key){
				return $item;
			}
		}
    }


    /**
     * Get columns only
	 * @param string $entityName
	 * @return array
     */
	public static function getColumns(string $entityName){
		$parse = self::parse();
		$columns = [];
		foreach($parse as $key => $item){
			if($entityName == $key){
				foreach($item['props'] as $propKey => $prop){
					if(!$prop['isEntity']){
						$columns[] = $propKey;
					} else {
						if($prop['relationType'] != 'many_to_one')
							$columns[] = $prop['foreignKey'];
					}
				}
				return $columns;
			}
		}
	}


    /**
     * Get columns appended with table name
	 * @param string $entityName
	 * @return array
     */
	public static function getSelectColumns(string $entityName){
		$parse = self::parse();
		$columns = [];
		foreach($parse as $key => $item){
			if($entityName == $key){
				foreach($item['props'] as $propKey => $prop){
					if(!$prop['isEntity']){
						$columns[] = $item['table'] . '.' . $propKey;
					} else {
						if($prop['relationType'] != 'many_to_one')
							$columns[] = $item['table'] . '.' .$prop['foreignKey'];
					}
				}
				return $columns;
			}
		}
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
    public static function parse(string $patToDir = null)
    {
        return MappingReader::read($patToDir);
    }
}