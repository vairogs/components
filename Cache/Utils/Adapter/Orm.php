<?php declare(strict_types = 1);

namespace Vairogs\Component\Cache\Utils\Adapter;

use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Exception;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\PdoAdapter;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Vairogs\Component\Utils\Vairogs;
use function class_exists;
use function interface_exists;
use function sprintf;

class Orm implements Cache
{
    /**
     * @param EntityManagerInterface $manager
     */
    public function __construct(private EntityManagerInterface $manager)
    {
        if (!interface_exists(Driver::class) || !class_exists(Query::class)) {
            throw new InvalidConfigurationException(sprintf('Packages %s and %s must be installed in order to use %s', 'doctrine/orm', 'doctrine/dbal', self::class));
        }
    }

    /**
     * @return CacheItemPoolInterface
     * @throws DBALException
     */
    public function getAdapter(): CacheItemPoolInterface
    {
        $table = sprintf('%s_items', Vairogs::VAIROGS);
        $schema = $this->manager->getConnection()
            ->getSchemaManager();
        $adapter = new PdoAdapter($this->manager->getConnection(), '', 0, ['db_table' => $table]);

        if ($schema && !$schema->tablesExist([$table])) {
            try {
                $adapter->createTable();
            } catch (Exception $exception) {
                throw new DBALException($exception->getMessage(), $exception->getCode(), $exception);
            }
        }

        if ($schema && $schema->tablesExist([$table])) {
            return $adapter;
        }

        throw DBALException::invalidTableName($table);
    }
}
