<?php

namespace Gregoriohc\Attributum;

use Carbon\Carbon;

class Manager
{
    protected static $modelAttributes = [];

    private static $defaultAttributeOptions = [
        'length' => null,
        'values' => null,
        'default' => null,
        'nullable' => false,
    ];

    /**
     * @param string|object $modelClass
     * @param bool $update
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function modelAttributes($modelClass, $update = false)
    {
        $modelClass = self::modelClass($modelClass);

        if (!isset(static::$modelAttributes[$modelClass]) || $update) {
            static::$modelAttributes[$modelClass] = ModelAttribute::where('attributable_type', '=', $modelClass)->get();
        }

        return static::$modelAttributes[$modelClass];
    }

    /**
     * @param string|object $modelClass
     * @param string $name
     * @param string $type
     * @param array $options
     * @return ModelAttribute
     */
    public static function addModelAttribute($modelClass, $name, $type, $options = [])
    {
        $modelClass = self::modelClass($modelClass);

        if (self::hasModelAttribute($modelClass, $name)) {
            return new \Exception(sprintf('The model "%s" already have a "%s" attribute', $modelClass, $name));
        }

        if (!is_string($type)) {
            $type = 'string';
        }

        $options = self::validateModelAttributeOptions($options);

        $attribute = ModelAttribute::create([
            'attributable_type' => $modelClass,
            'name' => $name,
            'type' => $type,
            'options' => $options,
        ]);

        self::modelAttributes($modelClass, true);

        return $attribute;
    }

    /**
     * @param string|object $modelClass
     * @param string $name
     * @param string $type
     * @param array $options
     * @return ModelAttribute
     */
    public static function updateModelAttribute($modelClass, $name, $type, $options = [])
    {
        $modelClass = self::modelClass($modelClass);

        if (!self::hasModelAttribute($modelClass, $name)) {
            return new \Exception(sprintf('The model "%s" does not have a "%s" attribute', $modelClass, $name));
        }

        if (!is_string($type)) {
            $type = 'string';
        }

        $options = self::validateModelAttributeOptions($options);

        $attribute = self::getModelAttribute($modelClass, $name);

        $attribute->save([
            'type' => $type,
            'options' => $options,
        ]);

        self::modelAttributes($modelClass, true);

        return $attribute;
    }

    /**
     * @param string|object $modelClass
     * @param string $name
     * @param string $newName
     * @return ModelAttribute
     */
    public static function renameModelAttribute($modelClass, $name, $newName)
    {
        $modelClass = self::modelClass($modelClass);

        if (!self::hasModelAttribute($modelClass, $name)) {
            return new \Exception(sprintf('The model "%s" does not have a "%s" attribute', $modelClass, $name));
        }

        $attribute = self::getModelAttribute($modelClass, $name);

        $attribute->save([
            'name' => $newName,
        ]);

        self::modelAttributes($modelClass, true);

        return $attribute;
    }

    /**
     * @param string|object $modelClass
     * @param string $name
     * @return ModelAttribute
     * @throws \Exception
     */
    public static function getModelAttribute($modelClass, $name)
    {
        $modelClass = self::modelClass($modelClass);

        if (!self::hasModelAttribute($modelClass, $name)) {
            throw new \Exception(sprintf('The model "%s" does not have a "%s" attribute', $modelClass, $name));
        }

        return self
            ::modelAttributes(self::modelClass($modelClass))
            ->where('name', $name)
            ->first();
    }

    /**
     * @param string|object $modelClass
     * @param string $name
     * @return bool
     */
    public static function hasModelAttribute($modelClass, $name)
    {
        return !is_null(
            self
                ::modelAttributes(self::modelClass($modelClass))
                ->where('name', $name)
                ->first()
        );
    }

    /**
     * @param string|object $modelClass
     * @return string
     */
    public static function modelClass($modelClass)
    {
        if (is_object($modelClass)) {
            $modelClass = get_class($modelClass);
        }

        return $modelClass;
    }

    /**
     * @param array $options
     * @return array
     */
    public static function validateModelAttributeOptions($options)
    {
        $options = array_filter($options, function ($key) {
            return in_array($key, array_keys(self::$defaultAttributeOptions));
        }, ARRAY_FILTER_USE_KEY);

        if (isset($options['length']) && !is_int($options['length'])) {
            unset($options['length']);
        }

        if (isset($options['values']) && !is_array($options['values'])) {
            unset($options['values']);
        }

        if (isset($options['nullable']) && !is_bool($options['nullable'])) {
            unset($options['nullable']);
        }

        return array_merge(self::$defaultAttributeOptions, $options);
    }

    /**
     * @param mixed $value
     * @param string $type
     * @param $options
     * @return mixed
     */
    public static function castValueGet($value, $type, $options)
    {
        switch ($type) {
            case 'string':
            case 'char':
            case 'text':
            case 'mediumText':
            case 'longText': {
                $value = (string)$value;
                if (is_numeric($options['length'])) {
                    $value = substr($value, 0, $options['length']);
                }
                break;
            }
            case 'enum': {
                $value = (string)$value;
                if (is_array($options['values']) && !in_array($value, $options['values'])) {
                    $value = null;
                }
                break;
            }
            case 'integer':
            case 'tinyInteger':
            case 'mediumInteger':
            case 'bigInteger': {
                $value = (int)$value;
                break;
            }
            case 'float': {
                $value = (float)$value;
                break;
            }
            case 'decimal':
            case 'double': {
                if (!is_null($options['length'])) {
                    list($precision, $scale) = explode(',', $options['length']);
                } else {
                    if ('double' == $type) {
                        $precision = 15;
                        $scale = 8;
                    } else {
                        $precision = 5;
                        $scale = 2;
                    }
                }
                $value = round($value, $scale);
                $max = (double)str_repeat('9', $precision - $scale) . '.' . str_repeat('9', $scale);
                if ($value > $max) {
                    $value = floor($max * pow(10, $scale)) / pow(10, $scale);
                }
                break;
            }
            case 'boolean': {
                $value = (bool)$value;
                break;
            }
            case 'date': {
                $value = new Carbon($value);
                break;
            }
            case 'time': {
                $value = new Carbon($value);
                break;
            }
            case 'datetime': {
                $value = new Carbon($value);
                break;
            }
            case 'timestamp': {
                $value = Carbon::createFromTimestamp($value);
                break;
            }
        }

        return $value;
    }

    /**
     * @param mixed $value
     * @param string $type
     * @param $options
     * @return mixed
     */
    public static function castValueSet($value, $type, $options)
    {
        switch ($type) {
            case 'string':
            case 'char':
            case 'text':
            case 'mediumText':
            case 'longText': {
                $value = (string)$value;
                if (is_numeric($options['length'])) {
                    $value = substr($value, 0, $options['length']);
                }
                break;
            }
            case 'enum': {
                $value = (string)$value;
                if (is_array($options['values']) && !in_array($value, $options['values'])) {
                    $value = null;
                }
                break;
            }
            case 'integer':
            case 'tinyInteger':
            case 'mediumInteger':
            case 'bigInteger': {
                $value = (string)$value;
                break;
            }
            case 'float': {
                $value = (string)$value;
                break;
            }
            case 'decimal':
            case 'double': {
                if (!is_null($options['length'])) {
                    list($precision, $scale) = explode(',', $options['length']);
                } else {
                    if ('double' == $type) {
                        $precision = 15;
                        $scale = 8;
                    } else {
                        $precision = 5;
                        $scale = 2;
                    }
                }
                $value = round($value, $scale);
                $max = (double)str_repeat('9', $precision - $scale) . '.' . str_repeat('9', $scale);
                if ($value > $max) {
                    $value = floor($max * pow(10, $scale)) / pow(10, $scale);
                }
                $value = (string)$value;
                break;
            }
            case 'boolean': {
                $value = (string)$value;
                break;
            }
            case 'date': {
                if (!($value instanceof Carbon)) {
                    $value = new Carbon($value);
                }
                $value = $value->toDateString();
                break;
            }
            case 'time': {
                if (!($value instanceof Carbon)) {
                    $value = new Carbon($value);
                }
                $value = $value->toTimeString();
                break;
            }
            case 'datetime': {
                if (!($value instanceof Carbon)) {
                    $value = new Carbon($value);
                }
                $value = $value->toDateTimeString();
                break;
            }
            case 'timestamp': {
                if (!($value instanceof Carbon)) {
                    if (is_numeric($value)) {
                        $value = Carbon::createFromTimestamp($value);
                    } else {
                        $value = new Carbon($value);
                    }
                }
                $value = (string)$value->timestamp;
                break;
            }
        }

        return $value;
    }
}