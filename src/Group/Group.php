<?php

declare(strict_types=1);

namespace srag\Plugins\UserTakeOver\Group;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class Group
{
    public const F_ID = 'id';
    public const F_TITLE = 'title';
    public const F_DESCRIPTION = 'description';
    public const F_RESTRICT_TO_MEMBERS = 'restrict_to_members';
    public const F_ALLOWED_ROLES = 'allowed_roles';

    protected const MAX_TITLE_LENGTH = 254;

    /**
     * @var int|null
     */
    protected $id;
    /**
     * @var string
     */
    protected $title;
    /**
     * @var string
     */
    protected $description;
    /**
     * @var int[]
     */
    protected $allowed_roles;
    /**
     * @var bool
     */
    protected $restrict_to_members;

    /**
     * @param int[] $restrict_to_roles
     */
    public function __construct(
        ?int $id,
        string $title,
        string $description = '',
        array $restrict_to_roles = [],
        bool $restrict_to_members = true
    ) {
        $this->id = $id;
        $this->setTitle($title);
        $this->description = $description;
        $this->allowed_roles = $restrict_to_roles;
        $this->restrict_to_members = $restrict_to_members;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->abortIfTitleTooLarge($title);
        $this->title = $title;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return int[]
     */
    public function getAllowedRoles(): array
    {
        return $this->allowed_roles;
    }

    /**
     * @param int[] $allowed_roles
     */
    public function setAllowedRoles(array $allowed_roles): self
    {
        $this->allowed_roles = $allowed_roles;
        return $this;
    }

    public function isRestrictedToMembers(): bool
    {
        return $this->restrict_to_members;
    }

    public function setRestrictToMembers(bool $restrict_to_members): self
    {
        $this->restrict_to_members = $restrict_to_members;
        return $this;
    }

    public function isRestrictedToRoles(): bool
    {
        return !empty($this->allowed_roles);
    }

    protected function abortIfTitleTooLarge(string $title): void
    {
        if (self::MAX_TITLE_LENGTH < strlen($title)) {
            throw new \LogicException(self::class . "::\$title must only have " . self::MAX_TITLE_LENGTH . " characters.");
        }
    }
}
