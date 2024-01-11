<?php

namespace Tools\PHPStan;

use Registry;
use PHPStan\Broker\Broker;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertiesClassReflectionExtension;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\TypeCombinator;

class RegistryPropertyReflectionExtension implements PropertiesClassReflectionExtension {
	public function hasProperty(ClassReflection $classReflection, string $propertyName): bool {
		if (!$classReflection->is(Registry::class)) {
			return false;
		}

		return preg_match('/^model_.+$/', $propertyName, $matches) === 1;
	}

	public function getProperty(ClassReflection $classReflection, string $propertyName): PropertyReflection {
		preg_match('/^(model_.+)$/', $propertyName, $matches);
		$className = $this->convertSnakeToStudly($matches[1]);

		$broker = Broker::getInstance();

		$type = null;
		if ($broker->hasClass($className)) {
			$found = new ObjectType($className);
			if ($classType === 'Model') {
				$found = new GenericObjectType('\Proxy', [$found]);
			}
			$type = $type ? TypeCombinator::union($type, $found) : $found;
		}
		if ($type) {
			$type = TypeCombinator::addNull($type);
		} else {
			$type = new NullType();
		}

		return new LoadedProperty($classReflection, $type);
	}

	private function convertSnakeToStudly(string $value): string {
		return str_replace(' ', '', ucwords(str_replace('_', ' ', $value)));
	}
}
