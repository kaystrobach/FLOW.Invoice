<?php

declare(strict_types=1);

namespace KayStrobach\Invoice\Service;

use Doctrine\ORM\Mapping as ORM;
use KayStrobach\Invoice\Domain\Model\Invoice\Embeddable\AddressEmbeddable;
use Neos\Flow\Annotations as Flow;

class AddressEmbeddableService
{
    public function updateFields(AddressEmbeddable $addressEmbeddable)
    {
        $this->setDefaults($addressEmbeddable);
        if (trim($addressEmbeddable->getStreet()) !== '') {
            $this->buildCombinedAdress($addressEmbeddable);
            return $addressEmbeddable;
        }
        if (trim($addressEmbeddable->getCombinedAddress()) !== '') {
            $this->parseCombinedAddress($addressEmbeddable);
            return $addressEmbeddable;
        }
        return $addressEmbeddable;
    }

    public function setDefaults(AddressEmbeddable $addressEmbeddable)
    {
        if ($addressEmbeddable->getCountryCode() === '') {
            $addressEmbeddable->setCountryCode('DE');
        }
        if ($addressEmbeddable->getCountry() === '') {
            $addressEmbeddable->setCountry('Deutschland');
        }
    }

    /**
     * Parse the current combinedAddress back into the single address fields.
     *
     * Expected format created by updateCombinedAdress():
     * [name]
     * [personName]
     * [street] [houseNumber]
     * [roomNumber]
     * [addressAddon]
     * [zip] [city] - [country] ([countryCode])
     *
     * Optional lines in brackets may be missing. Empty lines are ignored.
     */
    public function parseCombinedAddress(AddressEmbeddable $addressEmbeddable): void
    {
        $combined = trim((string)$addressEmbeddable->getCombinedAddress());
        if ($combined === '') {
            return;
        }

        // Split into non-empty, trimmed lines
        $lines = array_values(array_filter(array_map('trim', preg_split('/\r\n|\n|\r/', $combined) ?: []), function ($value) {
            return $value !== '';
        }));

        if (count($lines) < 2) {
            // Not enough information to parse meaningfully
            return;
        }

        $lastIndex = count($lines) - 1;
        $locationLine = $lines[$lastIndex];

        // Parse last line: "ZIP CITY - COUNTRY (CC)"
        $zip = '';
        $city = '';
        $country = '';
        $countryCode = '';

        $locationMatched = false;
        if (preg_match('/^\s*(\d{5})\s+(.+?)\s*-\s*(.+?)\s*\(([^)]+)\)\s*$/u', $locationLine, $m)) {
            $zip = $m[1];
            $city = $m[2];
            $country = $m[3];
            $countryCode = $m[4];
            $locationMatched = true;
        } else {
            // Try a more lenient parse without country code parentheses
            if (preg_match('/^\s*(\d{5})\s+(.+?)\s*-\s*(.+?)\s*$/u', $locationLine, $m)) {
                $zip = $m[1];
                $city = $m[2];
                $country = $m[3];
                $locationMatched = true;
            }
        }

        if ($locationMatched) {
            $addressEmbeddable->setZipCode($zip);
            $addressEmbeddable->setCity($city);
            $addressEmbeddable->setCountry($country);
            if ($countryCode !== '') {
                $addressEmbeddable->setCountryCode($countryCode);
            }
        }

        // Find the street + house number line (the first line with a trailing token containing a digit)
        $streetIndex = -1;
        $street = '';
        $houseNumber = '';
        for ($i = 0; $i < $lastIndex; $i++) {
            $line = $lines[$i];
            if (preg_match('/^(.*?)\s+([^\s]+)$/u', $line, $m)) {
                $lastToken = $m[2];
                if (preg_match('/\d/u', $lastToken)) {
                    $streetIndex = $i;
                    $street = trim($m[1]);
                    $houseNumber = $lastToken;
                    break;
                }
            }
        }

        if ($streetIndex >= 0) {
            $addressEmbeddable->setStreet($street);
            $addressEmbeddable->setHouseNumber($houseNumber);

            // Lines before streetIndex: name and optional personName
            if ($streetIndex > 0) {
                $addressEmbeddable->setName($lines[0] ?? '');
                if ($streetIndex > 1) {
                    $addressEmbeddable->setPersonName($lines[1] ?? '');
                } else {
                    $addressEmbeddable->setPersonName('');
                }
            } else {
                $addressEmbeddable->setName('');
                $addressEmbeddable->setPersonName('');
            }

            // Lines between street and location: roomNumber and addressAddon (in that order)
            $between = array_slice($lines, $streetIndex + 1, $lastIndex - $streetIndex - 1);
            if (count($between) === 0) {
                $addressEmbeddable->setRoomNumber('');
                $addressEmbeddable->setAddressAddon('');
            } elseif (count($between) === 1) {
                $addressEmbeddable->setRoomNumber($between[0]);
                $addressEmbeddable->setAddressAddon('');
            } else {
                $addressEmbeddable->setRoomNumber($between[0]);
                $addressEmbeddable->setAddressAddon($between[1]);
            }
        }
    }

    public function buildCombinedAdress(AddressEmbeddable $addressEmbeddable)
    {
        if ($addressEmbeddable->getStreet() === '') {
            return;
        }

        $country =  $addressEmbeddable->getCountry() ?  ' - ' . $addressEmbeddable->getCountry() : '';
        $countryCode = $addressEmbeddable->getCountryCode() ? ' (' . $addressEmbeddable->getCountryCode() . ')' : '';

        $data = [
            $addressEmbeddable->getName(),
            $addressEmbeddable->getPersonName(),
            '',
            $addressEmbeddable->getStreet() . ' ' . $addressEmbeddable->getHouseNumber(),
            $addressEmbeddable->getRoomNumber(),
            $addressEmbeddable->getAddressAddon(),
            $addressEmbeddable->getZipCode() . ' ' . $addressEmbeddable->getCity() . $country . $countryCode
        ];

        // Trimme alle Zeilen und filtere leere Zeilen heraus
        $filteredData = array_filter(
            array_map('trim', $data),
            function ($value) {
                return $value !== '';
            }
        );
        $addressEmbeddable->setCombinedAddress(
            implode(
                PHP_EOL,
                $filteredData
            )
        );
    }
}
