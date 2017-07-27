<?php
/**
 * Created by IntelliJ IDEA.
 * Author: Andru Cherny
 * E-mail: wiroatom[dogg]gmail[dot]com
 * Date: 24.10.15
 * Time: 21:35
 */

namespace Redefinitions\Components;

use Exception;
use Redefinitions\Helpers\ArrayHelper;
use ReflectionClass;
use yii\base\InvalidConfigException;

/**
 * Class Api
 * @package Redefinitions\Components
 */
class Api extends ClassBuilder
{

    /**
     * @var string
     */
    protected static $configFile = null;

    /**
     * @var array
     */
    private static $objectStorage = [];

    /**
     * @param string $key
     *
     * @return static
     * @throws \Exception
     */
    public static function instance($key = 'default') {
        if (!isset(self::$objectStorage[get_called_class() . '_' . $key])) {
            $ref = new ReflectionClass(get_called_class());
            if (static::$configFile == null) {
                throw new \Exception('Call not found static protected var $configFolder in class ' . $ref->getName());
            }
            if (!file_exists(\Yii::getAlias('@config') . '/' . static::$configFile)) {
                throw new Exception('Config file to Api class "' . $ref->getShortName() . '" is not found in folder ' . static::$configFile);
            }

            /** @noinspection PhpIncludeInspection */
            $config = require(\Yii::getAlias('@config') . '/' . static::$configFile);
            if (!is_array($config)) {
                throw new Exception('Config file ' . static::$configFile . ' not a array');
            }

            if (isset($config['version'])) {
                $config = self::parseConfigVersions($config);
            }
            self::$objectStorage[$key] = self::build($ref, $config);
        }

        return self::$objectStorage[$key];
    }

    private static function parseConfigVersions($config) {
        switch ($config['version']) {
            case 2:
                $default = ArrayHelper::getValue($config, 'default', YII_ENV);
                $result = ArrayHelper::getValue($config, YII_ENV, false);
                if ($result === false) {
                    $result = ArrayHelper::getValue($config, $default, false);
                    if ($result == false) {
                        throw new InvalidConfigException('Configuration ' . $result . ' not found');
                    }
                }
                return $result;
            default:
                throw new InvalidConfigException('Config version' . $config['version'] . ' not found');
        }
    }
}