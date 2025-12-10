<?php

namespace KayStrobach\Invoice\Domain\Model\Invoice\Embeddable;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * @ORM\Embeddable()
 *
 */
class AddressEmbeddable
{
    /**
     * @ORM\Column(type="text")
     * @var string
     */
    protected $name = '';

    /**
     * @ORM\Column(type="text")
     * @Flow\Validate(type="NotEmpty", validationGroups={"ContactAddress"})
     * @var string
     */
    protected string $combinedAddress = '';

    /**
     * @var string
     * @Flow\Validate(type="String")
     * @Flow\Validate(type="StringLength")
     */
    protected string $street = '';

    /**
     * @var string
     * @Flow\Validate(type="String")
     * @Flow\Validate(type="StringLength")
     */
    protected string $houseNumber = '';

    /**
     * @var string
     * @Flow\Validate(type="StringLength")
     */
    protected string $addressAddon = '';

    /**
     * @var string
     * @Flow\Validate(type="String")
     */
    protected string $roomNumber = '';

    /**
     * @var string
     * @Flow\Validate(type="Number")
     * @Flow\Validate(type="StringLength", options={ "minimum"=5, "maximum"=5},validationGroups={"ContactAddress"})
     */
    protected string $zipCode = '';

    /**
     * @Flow\Validate(type="String")
     * @Flow\Validate(type="StringLength", options={ "minimum"=1, "maximum"=255},validationGroups={"ContactAddress"})
     * @var string
     */
    protected string $city = '';

    /**
     * @Flow\Validate(type="String")
     * @Flow\Validate(type="StringLength", options={ "minimum"=1, "maximum"=255},validationGroups={"ContactAddress"})
     * @var string
     */
    protected $country = '';

    /**
     * https://de.wikipedia.org/wiki/ISO-3166-1-Kodierliste
     * Only Alpha 2 is allowed
     *
     * @Flow\Validate(type="String")
     * @Flow\Validate(type="StringLength", options={ "minimum"=1, "maximum"=2},validationGroups={"ContactAddress"})
     * @var string
     */
    protected $countryCode = '';

    /**
     * @Flow\Validate(type="String")
     * @var string
     */
    protected string $vatID = '';

    /**
     * @Flow\Validate(type="String")
     * @Flow\Validate(type="StringLength", options={"minimum"=1, "maximum"=255},validationGroups={"ContactAddress"})
     * @Flow\Validate(type="Email")
     * @var ?string
     */
    protected ?string $email = null;

    public function getCombinedAddress(): string
    {
        return $this->combinedAddress;
    }

    public function setCombinedAddress(string $combinedAddress): void
    {
        $this->combinedAddress = $combinedAddress;
    }

    /**
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * @param string $street
     */
    public function setStreet($street)
    {
        $this->street = $street;
    }

    /**
     * @return string
     */
    public function getHouseNumber()
    {
        return $this->houseNumber;
    }

    /**
     * @param string $houseNumber
     */
    public function setHouseNumber($houseNumber)
    {
        $this->houseNumber = $houseNumber;
    }

    /**
     * @return string
     */
    public function getAddressAddon()
    {
        return $this->addressAddon;
    }

    /**
     * @param string $addressAddon
     */
    public function setAddressAddon($addressAddon)
    {
        $this->addressAddon = $addressAddon;
    }

    /**
     * @return string
     */
    public function getRoomNumber()
    {
        return $this->roomNumber;
    }

    /**
     * @param string $roomNumber
     */
    public function setRoomNumber($roomNumber)
    {
        $this->roomNumber = $roomNumber;
    }

    /**
     * @return string
     */
    public function getZipCode(): string
    {
        return $this->zipCode;
    }

    /**
     * @param string $zipCode
     */
    public function setZipCode(string $zipCode)
    {
        $this->zipCode = $zipCode;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param string $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param string $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    /**
     * @ORM\PrePersist()
     * @return void
     */
    public function updateCombinedAdress(string $personname): void
    {
        if ($this->getStreet() === '') {
            return;
        }
        $data = [
            $personname,
            '',
            $this->street . ' ' . $this->houseNumber,
            $this->roomNumber,
            $this->addressAddon,
            $this->zipCode . ' ' . $this->city . ' - ' . $this->country . ' (' . $this->countryCode . ')'
        ];

        // Trimme alle Zeilen und filtere leere Zeilen heraus
        $filteredData = array_filter(
            array_map('trim', $data),
            function ($value) {
                return $value !== '';
            }
        );

        $this->combinedAddress = implode(
            PHP_EOL,
            $filteredData
        );
    }
}
