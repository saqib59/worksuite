<?php

declare(strict_types=1);

namespace Square\Models;

use stdClass;

/**
 * Request object for the [CreateLocation]($e/Locations/CreateLocation) endpoint.
 */
class CreateLocationRequest implements \JsonSerializable
{
    /**
     * @var Location|null
     */
    private $location;

    /**
     * Returns Location.
     *
     * Represents one of a business's locations.
     */
    public function getLocation(): ?Location
    {
        return $this->location;
    }

    /**
     * Sets Location.
     *
     * Represents one of a business's locations.
     *
     * @maps location
     */
    public function setLocation(?Location $location): void
    {
        $this->location = $location;
    }

    /**
     * Encode this object to JSON
     *
     * @param bool $asArrayWhenEmpty Whether to serialize this model as an array whenever no fields
     *        are set. (default: false)
     *
     * @return mixed
     */
    public function jsonSerialize(bool $asArrayWhenEmpty = false)
    {
        $json = [];
        if (isset($this->location)) {
            $json['location'] = $this->location;
        }
        $json = array_filter($json, function ($val) {
            return $val !== null;
        });

        return (!$asArrayWhenEmpty && empty($json)) ? new stdClass() : $json;
    }
}
