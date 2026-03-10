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

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;

/**
 * Tests if all routes use auth middleware
 *
 * @author Patrick Reichel
 */
class RoutesAuthTest extends TestCase
{
    // there can be routes not using auth middleware – define them here to exclude from testing
    protected $routes_not_using_auth_middleware = [
        'CustomerPsw',
        'Home',
        'HomeCcc',
        'ProvVoipEnvia.cron',
        'admin',
        'adminLogin',
        'customerLogin',
    ];

    // some routes make problems (e.g. returning status 500 in testing
    // Solve this problems and remove routes from array
    protected $routes_which_are_not_checked = [
        'debugbar.openhandler',
        'debugbar.clockwork',
        'debugbar.assets.css',
        'debugbar.assets.js',
    ];

    protected $route_name_prefixes_which_are_not_checked = [
        'boost.',
        'debugbar.',
        'default.livewire.',
        'generated::',
        'ignition.',
        'livewire.',
    ];

    protected $public_route_uris = [
        'admin',
        'customer/login',
    ];

    /**
     * Creates a Laravel application used for testing
     *
     * @author Patrick Reichel
     */
    public function createApplication(): Application
    {
        $app = parent::createApplication();

        return $app;
    }

    /**
     * Method to test all routes.
     *
     * @author Patrick Reichel
     */
    public function test_routes_auth_middleware(): void
    {
        $routeCollection = RouteFacade::getRoutes();
        foreach ($routeCollection as $route) {
            $name = (string) $route->getName();

            // no name -> no test
            if ($name === '') {
                continue;
            }
            if ($this->shouldSkipRoute($route, $name)) {
                continue;
            }

            $middleware = $this->normalizeMiddleware($route->gatherMiddleware());

            $this->assertTrue(
                $this->routeUsesAuthMiddleware($middleware),
                sprintf(
                    'Route "%s" (%s) does not use auth middleware. Found: [%s]',
                    $name,
                    $route->uri(),
                    implode(', ', $middleware),
                ),
            );
        }
    }

    /**
     * @param  array<int, mixed>  $middleware
     * @return array<int, string>
     */
    protected function normalizeMiddleware(array $middleware): array
    {
        $normalized = [];
        foreach ($middleware as $entry) {
            if (is_string($entry)) {
                $normalized[] = $entry;
            }
        }

        return $normalized;
    }

    /**
     * @param  array<int, string>  $middleware
     */
    protected function routeUsesAuthMiddleware(array $middleware): bool
    {
        foreach ($middleware as $entry) {
            if (
                $entry === 'auth' ||
                str_starts_with($entry, 'auth:') ||
                str_starts_with($entry, 'can:')
            ) {
                return true;
            }
        }

        return false;
    }

    protected function shouldSkipRoute(Route $route, string $name): bool
    {
        if (
            in_array($name, $this->routes_not_using_auth_middleware, true) ||
            in_array($name, $this->routes_which_are_not_checked, true)
        ) {
            return true;
        }

        foreach ($this->route_name_prefixes_which_are_not_checked as $prefix) {
            if (str_starts_with($name, $prefix)) {
                return true;
            }
        }

        $uri = $route->uri();
        if (
            str_starts_with($uri, '_debugbar/') ||
            str_starts_with($uri, '_ignition/') ||
            str_starts_with($uri, '_boost/') ||
            str_starts_with($uri, 'livewire/') ||
            str_starts_with($uri, 'customer/')
        ) {
            return true;
        }

        if (in_array($uri, $this->public_route_uris, true)) {
            return true;
        }

        if (
            str_contains($uri, 'login') ||
            str_contains($uri, 'logout') ||
            str_contains($uri, 'password')
        ) {
            return true;
        }

        return false;
    }
}
