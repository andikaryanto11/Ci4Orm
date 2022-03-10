<?php

namespace Ci4Orm\Eloquents;

use Ci4Orm\Eloquent;
use Ci4Orm\Exception\FabricatorException;
use Exception;
use Faker\Factory;
use JsonSerializable;

class EloquentFabricator
{
    /**
     * Set eloquent instance with fake data
     * @param string $eloquentClass
     * @param array $fakeFieldsFabracator
     * @param array $except - Filed that wont be faked
     * @return Eloquent
     */
    public static function assign(string $eloquentClass, array $fakeFieldsFabracator, array $except = [])
    {
        $faker = Factory::create();
        $classFields = $eloquentClass::getProperties();

        if (count($classFields) - count($except) != count($fakeFieldsFabracator))
            throw new FabricatorException("Eloquent Fields count is not the same with fakeField");

        $fieldIndex = 0;
        $eloquentInstance = new $eloquentClass;
        foreach ($classFields as $field) {
            if (!in_array($field, $except)) {
                $fakeField = $fakeFieldsFabracator[$fieldIndex];
                if (is_array($fakeField)) {
                    $fn = $fakeField[0];
                    $params = $fakeField[1];
                    $eloquentInstance->$field = $faker->$fn(...$params);
                } else {
                    $eloquentInstance->$field = $faker->$fakeField;
                }
                $fieldIndex++;
            }
        }

        return $eloquentInstance;
    }
}
