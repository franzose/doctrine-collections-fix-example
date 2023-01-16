<?php
declare(strict_types=1);

namespace Hierarchy\Tests;

use Doctrine\DBAL\Configuration as DBALConfiguration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Logging\Middleware;
use Doctrine\ORM\Configuration as ORMConfiguration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Hierarchy\Category;
use Hierarchy\CategoryRepositoryInterface;
use Hierarchy\DefaultCategoryRepository;
use Hierarchy\ImprovedCategoryRepository;
use JetBrains\PhpStorm\ArrayShape;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class CategoryRepositoryTest extends TestCase
{
    /**
     * @dataProvider behaviourDataProvider
     */
    public function testBehavior(string $repositoryClassName, array $expectedQueries): void
    {
        [$logger, $repository] = self::setUpTestData($repositoryClassName);
    
        // foo_bar, foo_bar_qux, foo_bar_qux_doo
        $descendants = $repository->findDescendantsByUid('foo');
        
        self::assertEquals('foo_bar_qux_doo', $descendants[0]->children()->first()->children()->first()->uid());
        self::assertEquals($expectedQueries, $logger->selectQueries());
    }
    
public function behaviourDataProvider(): array
{
    return [
        [
            DefaultCategoryRepository::class,
            [
                'SELECT c0_.parent_id AS parent_id_0, c0_.uid AS uid_1, c0_.created_at AS created_at_2, c0_.updated_at AS updated_at_3, c0_.id AS id_4, c0_.name AS name_5, c0_.parent_id AS parent_id_6 FROM category c0_ WHERE c0_.uid LIKE ? ORDER BY c0_.uid ASC',
                'SELECT t0.parent_id AS parent_id_1, t0.uid AS uid_2, t0.created_at AS created_at_3, t0.updated_at AS updated_at_4, t0.id AS id_5, t0.name AS name_6, t0.parent_id AS parent_id_7 FROM category t0 WHERE t0.parent_id = ?',
                'SELECT t0.parent_id AS parent_id_1, t0.uid AS uid_2, t0.created_at AS created_at_3, t0.updated_at AS updated_at_4, t0.id AS id_5, t0.name AS name_6, t0.parent_id AS parent_id_7 FROM category t0 WHERE t0.parent_id = ?'
            ]
        ],
        [
            ImprovedCategoryRepository::class,
            [
                'SELECT c0_.parent_id AS parent_id_0, c0_.uid AS uid_1, c0_.created_at AS created_at_2, c0_.updated_at AS updated_at_3, c0_.id AS id_4, c0_.name AS name_5, c0_.parent_id AS parent_id_6 FROM category c0_ WHERE c0_.uid LIKE ? ORDER BY c0_.uid ASC',
            ]
        ]
    ];
}

    private static function createCategories(
        EntityManagerInterface $em,
        CategoryRepositoryInterface $repository
    ): void {
        $parent = new Category($repository->nextCategoryId(), 'foo', 'Foo');
        $child = new Category($repository->nextCategoryId(), 'bar', 'Bar', $parent);
        $grandChild = new Category($repository->nextCategoryId(), 'qux', 'Qux', $child);
        $grandGrandChild = new Category($repository->nextCategoryId(), 'doo', 'Doo', $grandChild);
    
        foreach ([$parent, $child, $grandChild, $grandGrandChild] as $entity) {
            $em->persist($entity);
        }
    
        $em->flush();
        $em->clear();
    }

    private static function createEntityManager(Connection $connection): EntityManagerInterface
    {
        $config = new ORMConfiguration();
        $config->setMetadataDriverImpl(new AttributeDriver([$_SERVER['DOCTRINE_SRC_DIR']]));
        $config->setProxyDir($_SERVER['DOCTRINE_PROXY_DIR']);
        $config->setProxyNamespace($_SERVER['DOCTRINE_PROXY_NAMESPACE']);
    
        return new EntityManager($connection, $config);
    }
    
    private static function createConnection(LoggerInterface $logger): Connection
    {
        $params = [
            'host' => $_SERVER['DB_HOST'],
            'port' => $_SERVER['DB_PORT'],
            'user' => $_SERVER['DB_USER'],
            'password' => $_SERVER['DB_PASSWORD'],
            'dbname' => $_SERVER['DB_NAME'],
            'driver' => $_SERVER['DB_DRIVER']
        ];
    
        $config = new DBALConfiguration();
        $config->setMiddlewares([
            new Middleware($logger)
        ]);
    
        return DriverManager::getConnection($params, $config);
    }

    #[ArrayShape([TestDatabaseLogger::class, CategoryRepositoryInterface::class])]
    private static function setUpTestData(string $repositoryClassName): array
    {
        $logger = new TestDatabaseLogger();
    
        $connection = self::createConnection($logger);
        $connection->executeStatement('delete from category');
    
        $em = self::createEntityManager($connection);
        $repository = new $repositoryClassName($em);
    
        self::createCategories($em, $repository);
    
        return [$logger, $repository];
    }
}
