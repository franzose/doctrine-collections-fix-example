<?php
declare(strict_types=1);

namespace Hierarchy;

use Doctrine\ORM\EntityManagerInterface;

final readonly class DefaultCategoryRepository implements CategoryRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function nextCategoryId(): int
    {
        return (int) $this->em
            ->getConnection()
            ->executeQuery("select nextval('category_id_seq')")
            ->fetchOne();
    }

    /**
     * @return Category[]
     */
    public function findDescendantsByUid(string $uid): array
    {
        return $this->em
            ->createQueryBuilder()
            ->select('c')
            ->from(Category::class, 'c')
            ->where('c.uid LIKE :uid')
            ->setParameter('uid', sprintf('%s_%%', $uid))
            ->orderBy('c.uid')
            ->getQuery()
            ->getResult();
    }
}
