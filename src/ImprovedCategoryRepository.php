<?php
declare(strict_types=1);

namespace Hierarchy;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\PersistentCollection;

final readonly class ImprovedCategoryRepository implements CategoryRepositoryInterface
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
        $result = $this->em
            ->createQueryBuilder()
            ->select('c')
            ->from(Category::class, 'c')
            ->indexBy('c', 'c.id')
            ->where('c.uid LIKE :uid')
            ->setParameter('uid', sprintf('%s_%%', $uid))
            ->orderBy('c.uid')
            ->getQuery()
            ->getResult();

        return $this->setUpCategoryRelations($result);
    }

    /**
     * @param Category[] $categories
     * @return Category[]
     */
    private function setUpCategoryRelations(array $categories): array
    {
        $metadata = $this->em->getClassMetadata(Category::class);
        $idField = $metadata->reflFields['id'];
        $parentField = $metadata->reflFields['parent'];
        $parentIdField = $metadata->reflFields['parentId'];
        $childrenField = $metadata->reflFields['children'];

        foreach ($categories as $category) {
            /** @var PersistentCollection $children */
            $children = $childrenField->getValue($category);
            $children->setInitialized(true);

            $parent = $categories[$parentIdField->getValue($category)] ?? null;
            
            if ($parent === null) {
                continue;
            }
            
            /** @var PersistentCollection $children */
            $children = $childrenField->getValue($parent);

            if (!$children->contains($category)) {
                $parentField->setValue($category, $parent);
                $parentIdField->setValue($category, $idField->getValue($parent));
                $children->add($category);
            }
        }

        return array_values($categories);
    }
}
