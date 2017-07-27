<?php
/**
 * Created by IntelliJ IDEA.
 * Author: Andru Cherny
 * E-mail: wiroatom[dogg]gmail[dot]com
 * Date: 24.10.15
 * Time: 21:32
 */

namespace Redefinitions\Components;

use Exception;
use ReflectionClass;
use ReflectionParameter;
use yii\base\InvalidConfigException;

/**
 * Class ClassBuilder
 * @package redefinitions\Components
 */
class ClassBuilder
{
	/**
	 * @param \ReflectionClass $class
	 * @param                  $config
	 *
	 * @return object
	 * @throws \yii\base\Exception
	 * @throws \yii\base\InvalidConfigException
	 */
	public static function build(ReflectionClass $class, array $config) {
		$dep = self::getDependencies($class);
		static::resolveDependencies($dep, $config);
		return $class->newInstanceArgs($config);

	}

	/**
	 * @param \ReflectionClass $class
	 *
	 * @return \ReflectionParameter[]
	 * @throws Exception
	 */
	private static function getDependencies(ReflectionClass $class) {
		$constructorObject = $class->getConstructor();
		if (null === $constructorObject) {
			throw new Exception();
		}
		return $constructorObject->getParameters();
	}

	/**
	 * @param array $dependencies
	 * @param array $dependencyArray
	 *
	 * @throws \Exception
	 * @throws \yii\base\InvalidConfigException
	 */
	private static function resolveDependencies(array $dependencies, array $dependencyArray) {
		/** @var ReflectionParameter $dependency */
		foreach ($dependencies as $index => $dependency) {
			if ($dependency instanceof ReflectionParameter) {
				if (!isset($dependencyArray[$dependency->getName()])) {
					throw new InvalidConfigException('Missing required parameter "' . $dependency->getName() . '" when instantiating "$class".');
				}
			} else {
				throw new Exception('$dependency is not instanceof ReflectionParameter');
			}
		}
	}
}