<?php

namespace App;

use Illuminate\Support\Arr;
use Log;
use Modules\ProvBase\Entities\Contract;

/**
 * Helper to hold functionality used for import commands
 *
 * @author Nino Ryschawy
 */
trait ImportTrait
{
    public static $importantTodos = [];

    /**
     * Check if already a (n internet) contract exists for this customer
     *
     * @return object contract if exists, otherwise null or []
     */
    public static function contractExists($number, $firstname, $lastname, $street, $city, $houseNr)
    {
        $contract = $number ? Contract::with('cccUser')->where('number', $number)->first() : null;

        if ($contract) {
            // Check if name and address differs - could be a different customer
            // Attention: strtolower doesn't work for ÄÖÜ, but i dont know if a street begins with such a char
            $existingStreet = str_replace(['strasse', 'straße', 'str.'], 'str', strtolower($contract->street));
            $newStreet = str_replace(['strasse', 'straße', 'str.'], 'str', strtolower($street));

            $diff = [];
            if ($contract->firstname != $firstname) {
                $diff['Vorname'] = $contract->firstname.' | '.$firstname;
            }
            if ($contract->lastname != $lastname) {
                $diff['Nachname'] = $contract->lastname.' | '.$lastname;
            }
            if (strtolower($contract->city) != strtolower($city)) {
                $diff['Stadt'] = $contract->city.' | '.$city;
            }
            if ($existingStreet != $newStreet) {
                $diff['Straße'] = $existingStreet.' | '.$newStreet;
            }

            if ($diff) {
                self::addTodo("Vertragsnummer $number existiert bereits, aber ".implode(', ', array_keys($diff)).' '.(count($diff) > 1 ? 'weichen' : 'weicht').' ab - Bitte korrigieren Sie die Daten!');

                return $contract;
            }

            Log::notice("Vertrag $number existiert bereits übereinstimmend ($firstname $lastname) - füge nur TV Tarif hinzu");
        } else {
            // TODO: Check if customer/name & address already exists with another contract number
            $contract = Contract::where('firstname', $firstname)
                ->where('lastname', $lastname)
                ->where('house_number', $houseNr)
                // make Straße or Str. respective ..straße or ..str. indifferent on searching in DB
                ->whereIn('street', [$street, str_replace(['trasse', 'traße'], 'tr.', $street)])
                ->where('city', $city)->first();

            if ($contract) {
                // $msg = "Customer $number is probably already added with different contract number [$contract->number] (found same name [$firstname $lastname], city & street [$street]). Check this manually!";
                $msg = "Kunde $number existiert bereits unter der Vertragsnummer $contract->number (selber Name, Stadt, Straße: , $city, $street gefunden). Füge nur TV Tarif hinzu.";
                Log::notice($msg);
            }
        }

        return $contract;
    }

    public static function addTodo($todo): void
    {
        Log::warning($todo);
        self::$importantTodos[] = $todo;
    }

    public static function printImportantTodos(): void
    {
        if (! self::$importantTodos) {
            return;
        }

        echo "\n".implode("\n", self::$importantTodos)."\n";
    }

    /**
     * Check if validation would fail.
     *
     * @author Roy Schneider
     *
     * @param  object  $model
     * @param  array  $data
     * @param  array  $identifier
     * @return bool|void
     */
    public static function validationFailed($model, $data, $identifier)
    {
        $validator = \Validator::make($data, (new $model)->rules());

        $modelName = (new \ReflectionClass($model))->getShortName();

        if ($validator->fails()) {
            $identifier = implode(
                ', ',
                Arr::map($identifier, function ($value, $key) {
                    return is_string($key) ? "{$key}: {$value}" : $value;
                })
            );
            self::addTodo("Cannot add {$modelName} with {$identifier} because of invalid data: ".implode(', ', $validator->errors()->all()));

            return true;
        }
    }

    /**
     * From https://stackoverflow.com/questions/4400110/how-to-increase-of-mac-address-by-a-defined-value
     */
    public function mac2mtaMac($mac)
    {
        $mac = preg_replace('/[^0-9A-Fa-f]/', '', $mac);
        $macVendorID = substr($mac, 0, 6);
        $macDec = hexdec(substr($mac, 6));
        $macDec += 1;
        $macHex = dechex($macDec);
        $mtaMac = $macVendorID.(strlen($macHex) < 6 ? str_repeat('0', 6 - strlen($macHex)) : '').$macHex;

        return $mtaMac;
    }
}
