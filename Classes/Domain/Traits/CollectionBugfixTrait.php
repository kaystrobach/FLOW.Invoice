<?php

namespace KayStrobach\Invoice\Domain\Traits;

use Doctrine\Common\Collections\Collection;

/**
 * Trait CollectionBugfixTrait
 * @package KayStrobach\EventManager\Domain\Traits
 *
 * use like this:
 *
 * function setWhatever(Collection $whatever) {
 *   $this->whatever = $this->mergeCollections($this->whatever, $whatever);
 * }
 *
 */

trait CollectionBugfixTrait
{
    protected function mergeCollections(Collection $currentCollection, Collection $newChildren): Collection
    {
        foreach ($currentCollection as $child) {
            if (!$newChildren->contains($child)) {
                $currentCollection->removeElement($child);
            }
        }
        foreach ($newChildren as $child) {
            if (!$currentCollection->contains($child)) {
                $currentCollection->add($child);
            }
        }
        return $currentCollection;
    }
}
