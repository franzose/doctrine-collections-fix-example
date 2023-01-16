<?php
declare(strict_types=1);

namespace Hierarchy;

interface CategoryRepositoryInterface
{
    /**
     * Возвращает новый идентификатор категории.
     *
     * Стратегия формирования идентификатора зависит от конкретной реализации.
     */
    public function nextCategoryId(): int;

    /**
     * Возвращает потомки всех уровней для категории с указанным UID.
     *
     * @return Category[]
     */
    public function findDescendantsByUid(string $uid): array;
}
