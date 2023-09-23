<?php

namespace Adebipe\Model\Type;

interface ModelTypeInterface
{
    /**
     * Is the value of this type can be null?
     *
     * @return bool|null
     */
    public function canBeNull(): bool;

    /**
     * Is the value of this type is auto increment?
     *
     * @return bool|null
     */
    public function isAutoIncrement(): bool;

    /**
     * Get the PDO type of this type
     *
     * @return int|null
     */
    public function getPDOParamType(): ?int;

    /**
     * Check if the value is of the type of this type
     *
     * @param  mixed $value
     * @return bool|null
     */
    public function checkType(mixed $value): ?bool;

    /**
     * Get the SQL creation type of this type
     * (with NOT NULL and AUTO_INCREMENT)
     *
     * @return string|null
     */
    public function getSqlCreationType(): ?string;

    /**
     * Get the SQL type of this type
     *
     * @return string|null
     */
    public function getSqlType(): string;

    /**
     * Get more SQL for the construction of the database
     *
     * @return array|null
     */
    public function getMoreSql(): array;
}
