<?php

namespace Adebipe\Model;

/**
 * Model object of the ORM
 *
 * @author BOUGET Alexandre <abouget68@gmail.com>
 */
interface ModelInterface
{
    /**
     * Get the schema of the model
     *
     * @return array
     */
    public function getSchema(): array;

    /**
     * Get the key of the model (the columns)
     *
     * @return array
     */
    public function getKey(): array;

    /**
     * Get the values of the model
     *
     * @return array
     */
    public function getValues(): array;

    /**
     * Add a value to a complex type (like a relation)
     *
     * @param string $name  The name of the column
     * @param object $value The value to add
     *
     * @return bool
     */
    public function addTo(string $name, object $value): bool;

    /**
     * Delete a value to a complex type (like a relation)
     *
     * @param string $name  The name of the column
     * @param object $value The value to delete
     *
     * @return bool
     */
    public function deleteTo(string $name, object $value): bool;
}
