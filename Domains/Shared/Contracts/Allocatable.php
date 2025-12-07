<?php

namespace Domains\Shared\Contracts;

/**
 * Allocatable Contract
 *
 * Allows expenses to be allocated to different types of resources.
 * For example, an expense can be allocated to a Batch, or to the General Farm.
 *
 * Used with polymorphic relationships in the Expense model.
 */
interface Allocatable
{
    /**
     * Get the displayable name for the allocatable resource
     */
    public function getAllocatableName(): string;

    /**
     * Get the type identifier for this allocatable
     */
    public function getAllocatableType(): string;
}
