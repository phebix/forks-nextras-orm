<?php declare(strict_types=1);

/**
 * This file is part of the Nextras\Orm library.
 * @license    MIT
 * @link       https://github.com/nextras/orm
 */

namespace Nextras\Orm\Mapper\Dbal;

use Nette\Caching\Cache;
use Nextras\Dbal\IConnection;
use Nextras\Dbal\Platforms\IPlatform;
use Nextras\Dbal\Platforms\PostgreSqlPlatform;
use Nextras\Dbal\QueryBuilder\QueryBuilder;
use Nextras\Dbal\Result\Result;
use Nextras\Dbal\Result\Row;
use Nextras\Orm\Collection\ArrayCollection;
use Nextras\Orm\Collection\ICollection;
use Nextras\Orm\Entity\IEntity;
use Nextras\Orm\Entity\IProperty;
use Nextras\Orm\Entity\Reflection\PropertyMetadata;
use Nextras\Orm\Entity\Reflection\PropertyRelationshipMetadata as Relationship;
use Nextras\Orm\InvalidArgumentException;
use Nextras\Orm\Mapper\BaseMapper;
use Nextras\Orm\Mapper\IMapper;
use Nextras\Orm\Mapper\IRelationshipMapper;
use Nextras\Orm\NotSupportedException;
use Nextras\Orm\StorageReflection\IStorageReflection;

class DbalCryptoMapper extends DbalMapper
{
	/** @var ICrypto */
	private $crypto;

	/** @var string  */
	protected static $sqlPostfix = '_encrypted';

	public function __construct(
		IConnection $connection,
		DbalMapperCoordinator $mapperCoordinator,
		Cache $cache,
		ICrypto $crypto
	) {
		parent::__construct($connection, $mapperCoordinator, $cache);

		$this->crypto = $crypto;
	}

	protected function createStorageReflection()
	{

		$storageReflection = parent::createStorageReflection();

		foreach($this->getRepository()->getEntityMetadata()->getProperties() as $propertyName => $propertyMetadata){
			if(key_exists('isEncrypted', $propertyMetadata) && $propertyMetadata->isEncrypted){
				// is crypted
				$crypto = $this->crypto;

				$storageReflection->setMapping(
					$propertyName,
					$storageReflection->convertEntityToStorageKey($propertyName) . self::$sqlPostfix,
					function ($garble) use ($crypto) {
						return $garble === NULL ? NULL : $crypto->decrypt($garble);
					},
					function ($plain) use ($crypto) {
						return $plain === NULL ? $crypto->encrypt('') : $crypto->encrypt($plain);
					}
				);
			}
		}

		return $storageReflection;
	}
}
