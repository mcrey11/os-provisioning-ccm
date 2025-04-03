<?php

/**
 * Copyright (c) NMS PRIME GmbH ("NMS PRIME Community Version")
 * and others – powered by CableLabs. All rights reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at:
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace App;

use Storage;

trait AddressFunctionsTrait
{
    private $formoptions_path = 'config/provbase/formoptions/';

    /**
     * Helper to define possible salutation values.
     * E.g. envia TEL API has a well defined set of valid values – using this method we can handle this.
     *
     * @author Patrick Reichel
     */
    public function getSalutationOptions($type = 'all')
    {
        $person_file = $this->formoptions_path.'salutations_person.txt';
        $institution_file = $this->formoptions_path.'salutations_institution.txt';

        if (\Module::collections()->has('ProvVoipEnvia')) {
            // special handling needed for Envia TEL – only certain values are allowed

            // envia TEL expects Herrn instead of Herr ⇒ to be as compatible as possible to other use cases
            // we nevertheless store Herr in database and fix this in XML generation within
            // ProvVoipEnvia->_add_fields
            $persons = [
                'Herr',
                'Frau',
            ];
            $institutions = [
                'Firma',
                'Behörde',
            ];
        } else {
            $persons = [];
            // do not “explode” at “\n” here – there is a real danger of files edited in Windows environments
            $tmp = preg_split('/\r\n|\r|\n/', Storage::get($person_file));
            foreach ($tmp as $person) {
                $person = trim($person);
                if ($person) {
                    $persons[] = $person;
                }
            }
            $institutions = [];
            // do not “explode” at “\n” here – there is a real danger of files edited in Windows environments
            $tmp = preg_split('/\r\n|\r|\n/', Storage::get($institution_file));
            foreach ($tmp as $institution) {
                $institution = trim($institution);
                if ($institution) {
                    $institutions[] = $institution;
                }
            }
        }

        $result = [];

        if ('person' == $type) {
            foreach ($persons as $person) {
                $result[$person] = $person;
            }
        } elseif ('institution' == $type) {
            foreach ($institutions as $institution) {
                $result[$institution] = $institution;
            }
        } else {
            $result[''] = ''; // add empty option
            foreach ($persons as $person) {
                $result[$person] = $person;
            }
            foreach ($institutions as $institution) {
                $result[$institution] = $institution;
            }
        }

        return $result;
    }

    /**
     * Wrapper to get person salutation options.
     *
     * @author Patrick Reichel
     */
    public function getSalutationOptionsPerson()
    {
        return $this->getSalutationOptions('person');
    }

    /**
     * Wrapper to get institution salutation options.
     *
     * @author Patrick Reichel
     */
    public function getSalutationOptionsInstitution()
    {
        return $this->getSalutationOptions('institution');
    }

    /**
     * Helper to define possible academic degree values.
     * E.g. envia TEL API has a well defined set of valid values – using this method we can handle this.
     *
     * @author Patrick Reichel
     */
    public function getAcademicDegreeOptions()
    {
        $degree_file = $this->formoptions_path.'academic_degrees.txt';

        if (\Module::collections()->has('ProvVoipEnvia')) {
            // special handling needed for Envia TEL – only certain values are allowed
            $degrees = [
                'Dr.',
                'Prof. Dr.',
            ];
        } else {
            $degrees = [];
            // do not “explode” at “\n” here – there is a real danger of files edited in Windows environments
            $tmp = preg_split('/\r\n|\r|\n/', Storage::get($degree_file));
            foreach ($tmp as $degree) {
                $degree = trim($degree);
                if ($degree) {
                    $degrees[] = $degree;
                }
            }
        }

        $result = [];
        $result[''] = ''; // add empty option
        foreach ($degrees as $degree) {
            $result[$degree] = $degree;
        }

        return $result;
    }

    /**
     * Separate number from street in address string
     */
    public static function splitStreetHousenr($string)
    {
        preg_match('/(\d+)(?!.*\d)/', $string, $matches);
        $matches = $matches ? $matches[0] : '';

        if (! $matches) {
            return [$string, null];
        }

        $x = strpos($string, $matches);
        $housenr = substr($string, $x);

        if (strlen($housenr) > 6) {
            $street = str_replace($matches, '', $string);
            $housenr = $matches;
        } else {
            $street = trim(substr($string, 0, $x));
        }

        return [$street, $housenr];
    }

    /**
     * Try to get a country code.
     *
     * @author Patrick Reichel
     */
    public function determineCountryCode($countryCode = null)
    {
        // return if given
        if ($countryCode) {
            return $countryCode;
        }

        // try to get from instance (ignore if empty string)
        if (isset($this->country_code) && $this->country_code) {
            return $this->country_code;
        }

        // try to get from model (ignore if empty string)
        if (isset($this->model) && isset($this->model->country_code) && $this->model->country_code) {
            return $this->model->country_code;
        }

        // try to get from global config (ignore if empty string)
        $globalConfig = GlobalConfig::find(1);
        if ($globalConfig->default_country_code) {
            return $globalConfig->default_country_code;
        }

        return null;
    }

    /**
     * Translates the government building ID depending on local and global country code.
     *
     * @author Patrick Reichel
     */
    public function translateNationalBuildingId($countryCode = null)
    {
        $code = $this->determineCountryCode($countryCode);
        $translateStringShort = 'dt_header.national_building_id';
        $translateStringLong = $translateStringShort.'_'.$code;

        // return translation with country code (if exists)
        if (trans($translateStringLong) != $translateStringLong) {
            return trans($translateStringLong);
        }

        // return general translation
        return trans($translateStringShort);
    }
}
