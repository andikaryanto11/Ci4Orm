<?php

namespace Ci4Orm\Entities;

use Config\Entity;
use Symfony\Component\Yaml\Yaml;

class MappingReader {
    public static function read($patToDir = null){
        $result = array();
        $dir = is_null($patToDir) ? Entity::register() : $patToDir;
        $cdir = scandir($dir);
        foreach ($cdir as $key => $value) {
            if (!in_array($value, array(".", ".."))) {
                if (!is_dir($dir . DIRECTORY_SEPARATOR . $value)) {
                    $fileRead = Yaml::parseFile($dir . DIRECTORY_SEPARATOR . $value);
                    foreach($fileRead as $key => $allProps){
						$result[$key] = $allProps;
					}
                }
            }
        }
        return $result;
    }
}