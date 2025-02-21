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
        $contract = Contract::with('cccUser')->where('number', $number)->first();

        if ($contract) {
            // Check if name and address differs - could be a different customer
            // Attention: strtolower doesn't work for ÄÖÜ, but i dont know if a street begins with such a char
            if ($contract->firstname != $firstname || $contract->lastname != $lastname || strtolower($contract->street) != strtolower($street)) {
                self::addTodo("Vertragsnummer $number existiert bereits, aber Name, Straße oder Stadt weichen ab - Bitte korrigieren Sie die Daten!");

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
}
