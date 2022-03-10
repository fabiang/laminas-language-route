<?php

declare(strict_types=1);

/*
 * Copyright (C) 2016 schurix, 2022 Fabian Grutschus
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace Fabiang\LaminasLanguageRoute\Mvc\Router\Http\Service;

use Fabiang\LaminasLanguageRoute\Mvc\Router\Http\LanguageTreeRouteStack;
use Fabiang\LaminasLanguageRoute\Options\LanguageRouteOptions;
use Interop\Container\ContainerInterface;
use Laminas\Router\RouteStackInterface;
use Laminas\ServiceManager\Factory\DelegatorFactoryInterface;

final class LanguageTreeRouteStackDelegatorFactory implements DelegatorFactoryInterface
{
    /**
     * @param string $name
     */
    public function __invoke(
        ContainerInterface $container,
        $name,
        callable $callback,
        ?array $options = null
    ): RouteStackInterface {
        $router = $callback();

        if (! $router instanceof LanguageTreeRouteStack) {
            return $router;
        }

        if ($container->has(LanguageRouteOptions::class)) {
            $router->setLanguageOptions($container->get(LanguageRouteOptions::class));
        }

        if ($container->has('authentication')) {
            $router->setAuthenticationService($container->get('authentication'));
        }

        return $router;
    }
}
