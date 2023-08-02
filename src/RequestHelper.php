<?php

declare(strict_types=1);

namespace srag\Plugins\UserTakeOver;

use ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper;
use ILIAS\Refinery\Transformation;
use ILIAS\Refinery\Factory as Refinery;
use srag\Plugins\UserTakeOver\Group\Group;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
trait RequestHelper
{
    /**
     * Returns the requested object reference id, by either using the 'ref_id' or 'target'
     * request parameters, to allow support for ILIAS goto links.
     */
    protected function getRequestedReferenceId(ArrayBasedRequestWrapper $request): ?int
    {
        $ref_id = $this->getRequestedInteger($request, IRequestParameters::REF_ID);
        if (null !== $ref_id) {
            return $ref_id;
        }

        $target = $this->getRequestedString($request, IRequestParameters::TARGET);
        if (null === $target) {
            return null;
        }

        $pieces = explode('_', $target);
        if (2 > count($pieces)) {
            return null;
        }

        // assuming the last piece is the ref id
        return (int) array_pop($pieces);
    }

    protected function getRequestedInteger(ArrayBasedRequestWrapper $request, string $parameter_name): ?int
    {
        return $this->getRequestedParameter($request, $parameter_name, $this->getRefinery()->kindlyTo()->int());
    }

    protected function getRequestedString(ArrayBasedRequestWrapper $request, string $parameter_name): ?string
    {
        return $this->getRequestedParameter($request, $parameter_name, $this->getRefinery()->kindlyTo()->string());
    }

    /**
     * @return mixed|null
     */
    protected function getRequestedMixed(ArrayBasedRequestWrapper $request, string $parameter_name): mixed
    {
        return $this->getRequestedParameter($request, $parameter_name, $this->getMixedTransformation());
    }

    abstract protected function getRefinery(): Refinery;

    /**
     * @return mixed|null
     */
    private function getRequestedParameter(
        ArrayBasedRequestWrapper $request,
        string $parameter_name,
        Transformation $transformation
    ) {
        if ($request->has($parameter_name)) {
            return $request->retrieve($parameter_name, $transformation);
        }

        return null;
    }

    private function getMixedTransformation(): Transformation
    {
        return $this->getRefinery()->custom()->transformation(
            static function ($value) {
                return $value;
            }
        );
    }
}
