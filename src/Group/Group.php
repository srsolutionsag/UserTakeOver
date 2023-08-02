<?php

declare(strict_types=1);

namespace srag\Plugins\UserTakeOver\Group;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class Group
{
    public const MAX_TITLE_LENGTH = 254;

    protected string $title;

    /**
     * @param int[] $restrict_to_roles
     * @param int[] $group_members
     */
    public function __construct(
        protected ?int $id,
        string $title,
        protected string $description = '',
        protected array $restrict_to_roles = [],
        protected bool $restrict_to_members = true,
        protected array $group_members = [],
    ) {
        $this->setTitle($title);
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
        return $this->restrict_to_roles;
    }

    /**
     * @param int[] $allowed_roles
     */
    public function setAllowedRoles(array $allowed_roles): self
    {
        $this->restrict_to_roles = $allowed_roles;
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
        return !empty($this->restrict_to_roles);
    }

    /**
     * @return int[]
     */
    public function getGroupMembers(): array
    {
        return $this->group_members;
    }

    /**
     * @param int[] $group_members
     */
    public function setGroupMembers(array $group_members): self
    {
        $this->group_members = $group_members;
        return $this;
    }

    protected function abortIfTitleTooLarge(string $title): void
    {
        if (self::MAX_TITLE_LENGTH < strlen($title)) {
            throw new \LogicException(
                self::class . "::\$title must only have " . self::MAX_TITLE_LENGTH . " characters."
            );
        }
    }
}
