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

namespace Database\Seeders;

/**
 * Provides realistic address data for Germany and USA
 * All addresses are based on public knowledge (major cities, common street names)
 */
class AddressDataProvider
{
    /**
     * Get realistic German addresses
     */
    public static function germanAddresses(): array
    {
        return [
            ['street' => 'Hauptstraße', 'zip' => '10115', 'city' => 'Berlin', 'country_code' => 'DE', 'lat' => 52.5200, 'lng' => 13.4050],
            ['street' => 'Bahnhofstraße', 'zip' => '80331', 'city' => 'München', 'country_code' => 'DE', 'lat' => 48.1351, 'lng' => 11.5820],
            ['street' => 'Kirchstraße', 'zip' => '20095', 'city' => 'Hamburg', 'country_code' => 'DE', 'lat' => 53.5511, 'lng' => 9.9937],
            ['street' => 'Gartenstraße', 'zip' => '50667', 'city' => 'Köln', 'country_code' => 'DE', 'lat' => 50.9375, 'lng' => 6.9603],
            ['street' => 'Bergstraße', 'zip' => '60311', 'city' => 'Frankfurt am Main', 'country_code' => 'DE', 'lat' => 50.1109, 'lng' => 8.6821],
            ['street' => 'Schulstraße', 'zip' => '70173', 'city' => 'Stuttgart', 'country_code' => 'DE', 'lat' => 48.7758, 'lng' => 9.1829],
            ['street' => 'Dorfstraße', 'zip' => '40213', 'city' => 'Düsseldorf', 'country_code' => 'DE', 'lat' => 51.2277, 'lng' => 6.7735],
            ['street' => 'Lindenstraße', 'zip' => '04109', 'city' => 'Leipzig', 'country_code' => 'DE', 'lat' => 51.3397, 'lng' => 12.3731],
            ['street' => 'Marktplatz', 'zip' => '01067', 'city' => 'Dresden', 'country_code' => 'DE', 'lat' => 51.0504, 'lng' => 13.7373],
            ['street' => 'Poststraße', 'zip' => '30159', 'city' => 'Hannover', 'country_code' => 'DE', 'lat' => 52.3759, 'lng' => 9.7320],
            ['street' => 'Ringstraße', 'zip' => '90402', 'city' => 'Nürnberg', 'country_code' => 'DE', 'lat' => 49.4521, 'lng' => 11.0767],
            ['street' => 'Waldstraße', 'zip' => '68161', 'city' => 'Mannheim', 'country_code' => 'DE', 'lat' => 49.4875, 'lng' => 8.4660],
            ['street' => 'Parkstraße', 'zip' => '28195', 'city' => 'Bremen', 'country_code' => 'DE', 'lat' => 53.0793, 'lng' => 8.8017],
            ['street' => 'Mühlenstraße', 'zip' => '45127', 'city' => 'Essen', 'country_code' => 'DE', 'lat' => 51.4556, 'lng' => 7.0116],
            ['street' => 'Schloßstraße', 'zip' => '44137', 'city' => 'Dortmund', 'country_code' => 'DE', 'lat' => 51.5136, 'lng' => 7.4653],
            ['street' => 'Friedrichstraße', 'zip' => '76133', 'city' => 'Karlsruhe', 'country_code' => 'DE', 'lat' => 49.0069, 'lng' => 8.4037],
            ['street' => 'Wilhelmstraße', 'zip' => '65183', 'city' => 'Wiesbaden', 'country_code' => 'DE', 'lat' => 50.0826, 'lng' => 8.2400],
            ['street' => 'Königstraße', 'zip' => '86150', 'city' => 'Augsburg', 'country_code' => 'DE', 'lat' => 48.3705, 'lng' => 10.8978],
            ['street' => 'Neuer Weg', 'zip' => '18055', 'city' => 'Rostock', 'country_code' => 'DE', 'lat' => 54.0887, 'lng' => 12.1403],
            ['street' => 'Am Markt', 'zip' => '99084', 'city' => 'Erfurt', 'country_code' => 'DE', 'lat' => 50.9848, 'lng' => 11.0299],
        ];
    }

    /**
     * Get realistic US addresses
     */
    public static function usAddresses(): array
    {
        return [
            ['street' => 'Main Street', 'zip' => '10001', 'city' => 'New York', 'state' => 'NY', 'country_code' => 'US', 'lat' => 40.7128, 'lng' => -74.0060],
            ['street' => 'Broadway', 'zip' => '10004', 'city' => 'New York', 'state' => 'NY', 'country_code' => 'US', 'lat' => 40.7580, 'lng' => -73.9855],
            ['street' => 'Market Street', 'zip' => '90001', 'city' => 'Los Angeles', 'state' => 'CA', 'country_code' => 'US', 'lat' => 34.0522, 'lng' => -118.2437],
            ['street' => 'Park Avenue', 'zip' => '60601', 'city' => 'Chicago', 'state' => 'IL', 'country_code' => 'US', 'lat' => 41.8781, 'lng' => -87.6298],
            ['street' => 'Oak Street', 'zip' => '77001', 'city' => 'Houston', 'state' => 'TX', 'country_code' => 'US', 'lat' => 29.7604, 'lng' => -95.3698],
            ['street' => 'Elm Street', 'zip' => '85001', 'city' => 'Phoenix', 'state' => 'AZ', 'country_code' => 'US', 'lat' => 33.4484, 'lng' => -112.0740],
            ['street' => 'Washington Street', 'zip' => '19101', 'city' => 'Philadelphia', 'state' => 'PA', 'country_code' => 'US', 'lat' => 39.9526, 'lng' => -75.1652],
            ['street' => 'Maple Avenue', 'zip' => '78201', 'city' => 'San Antonio', 'state' => 'TX', 'country_code' => 'US', 'lat' => 29.4241, 'lng' => -98.4936],
            ['street' => 'Franklin Street', 'zip' => '92101', 'city' => 'San Diego', 'state' => 'CA', 'country_code' => 'US', 'lat' => 32.7157, 'lng' => -117.1611],
            ['street' => 'First Avenue', 'zip' => '75201', 'city' => 'Dallas', 'state' => 'TX', 'country_code' => 'US', 'lat' => 32.7767, 'lng' => -96.7970],
            ['street' => 'Second Street', 'zip' => '95101', 'city' => 'San Jose', 'state' => 'CA', 'country_code' => 'US', 'lat' => 37.3382, 'lng' => -121.8863],
            ['street' => 'Third Street', 'zip' => '78701', 'city' => 'Austin', 'state' => 'TX', 'country_code' => 'US', 'lat' => 30.2672, 'lng' => -97.7431],
            ['street' => 'Fifth Avenue', 'zip' => '32801', 'city' => 'Jacksonville', 'state' => 'FL', 'country_code' => 'US', 'lat' => 30.3322, 'lng' => -81.6557],
            ['street' => 'Sixth Street', 'zip' => '43201', 'city' => 'Columbus', 'state' => 'OH', 'country_code' => 'US', 'lat' => 39.9612, 'lng' => -82.9988],
            ['street' => 'Seventh Street', 'zip' => '37201', 'city' => 'Nashville', 'state' => 'TN', 'country_code' => 'US', 'lat' => 36.1627, 'lng' => -86.7816],
            ['street' => 'Eighth Street', 'zip' => '28201', 'city' => 'Charlotte', 'state' => 'NC', 'country_code' => 'US', 'lat' => 35.2271, 'lng' => -80.8431],
            ['street' => 'Ninth Avenue', 'zip' => '94101', 'city' => 'San Francisco', 'state' => 'CA', 'country_code' => 'US', 'lat' => 37.7749, 'lng' => -122.4194],
            ['street' => 'Tenth Street', 'zip' => '80201', 'city' => 'Denver', 'state' => 'CO', 'country_code' => 'US', 'lat' => 39.7392, 'lng' => -104.9903],
            ['street' => 'Central Avenue', 'zip' => '98101', 'city' => 'Seattle', 'state' => 'WA', 'country_code' => 'US', 'lat' => 47.6062, 'lng' => -122.3321],
            ['street' => 'Highland Avenue', 'zip' => '02101', 'city' => 'Boston', 'state' => 'MA', 'country_code' => 'US', 'lat' => 42.3601, 'lng' => -71.0589],
        ];
    }

    /**
     * Get German street names for variation
     */
    public static function germanStreetNames(): array
    {
        return [
            'Hauptstraße', 'Bahnhofstraße', 'Kirchstraße', 'Gartenstraße', 'Bergstraße',
            'Schulstraße', 'Dorfstraße', 'Lindenstraße', 'Marktplatz', 'Poststraße',
            'Ringstraße', 'Waldstraße', 'Parkstraße', 'Mühlenstraße', 'Schloßstraße',
            'Friedrichstraße', 'Wilhelmstraße', 'Königstraße', 'Neuer Weg', 'Am Markt',
            'Rosenstraße', 'Ahornweg', 'Birkenallee', 'Eichenweg', 'Blumenstraße',
        ];
    }

    /**
     * Get US street names for variation
     */
    public static function usStreetNames(): array
    {
        return [
            'Main Street', 'Broadway', 'Market Street', 'Park Avenue', 'Oak Street',
            'Elm Street', 'Washington Street', 'Maple Avenue', 'Franklin Street', 'First Avenue',
            'Second Street', 'Third Street', 'Fifth Avenue', 'Sixth Street', 'Seventh Street',
            'Eighth Street', 'Ninth Avenue', 'Tenth Street', 'Central Avenue', 'Highland Avenue',
            'Cedar Lane', 'Pine Street', 'Cherry Avenue', 'Walnut Street', 'Grove Street',
        ];
    }

    /**
     * Get random address by region
     */
    public static function randomAddress(string $region = 'GER'): array
    {
        $addresses = $region === 'US' ? self::usAddresses() : self::germanAddresses();

        return $addresses[array_rand($addresses)];
    }

    /**
     * Get random street name by region
     */
    public static function randomStreetName(string $region = 'GER'): string
    {
        $streets = $region === 'US' ? self::usStreetNames() : self::germanStreetNames();

        return $streets[array_rand($streets)];
    }
}
