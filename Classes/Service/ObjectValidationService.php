<?php

declare(strict_types=1);

namespace KayStrobach\Invoice\Service;

use Neos\Flow\Validation\ValidatorResolver;
use Neos\Flow\Annotations as Flow;
use Neos\Error\Messages\Result as ErrorResult;
class ObjectValidationService
{
    /**
     * @Flow\Inject
     */
    protected ValidatorResolver $validatorResolver;

    public function getValidationResults(Object $object, array $validationGroups = ['Default']): ErrorResult
    {
        $validator = $this->validatorResolver->getBaseValidatorConjunction(get_class($object), $validationGroups);
        return $validator->validate($object);
    }

    public function isValid(Object $object, array $validationGroups = ['Default']): bool
    {
        return !$this->getValidationResults($object, $validationGroups)->hasErrors();
    }
}
