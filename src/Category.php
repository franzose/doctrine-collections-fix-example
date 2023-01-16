<?php
declare(strict_types=1);

namespace Hierarchy;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;

#[Entity, Table('category')]
class Category
{
    #[Column(name: 'parent_id', type: 'bigint', nullable: true)]
    private ?int $parentId;
    
    #[Column(name: 'uid', type: 'string', nullable: false)]
    private string $uid;

    #[OneToMany(mappedBy: 'parent', targetEntity: self::class)]
    private Collection $children;

    #[Column(name: 'created_at', type: 'datetimetz_immutable', nullable: false)]
    private DateTimeImmutable $createdAt;

    #[Column(name: 'updated_at', type: 'datetimetz_immutable', nullable: false)]
    private DateTimeImmutable $updatedAt;

    public function __construct(
        #[Id]
        #[Column(name: 'id', type: 'bigint', nullable: false)]
        #[GeneratedValue(strategy: 'NONE')]
        private int $id,
        string $uid,
        #[Column(name: 'name', type: 'string', nullable: false)]
        private string $name,
        #[ManyToOne(targetEntity: self::class, inversedBy: 'children')]
        #[JoinColumn(name: 'parent_id', referencedColumnName: 'id')]
        private ?self $parent = null
    ) {
        $this->parentId = $this->parent?->id;
        $this->uid = $this->parent === null ? $uid : sprintf('%s_%s', $this->parent->uid, $uid);
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
        $this->children = new ArrayCollection();
    }

    public function id(): int
    {
        return $this->id;
    }

    public function uid(): string
    {
        return $this->uid;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function parent(): ?self
    {
        return $this->parent;
    }

    public function parentId(): ?int
    {
        return $this->parentId;
    }

    public function children(): Collection
    {
        return new ArrayCollection($this->children->toArray());
    }
}
