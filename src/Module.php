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

namespace Fabiang\LaminasLanguageRoute;

use Laminas\EventManager\EventInterface;
use Laminas\ModuleManager\Feature\BootstrapListenerInterface;
use Laminas\ModuleManager\Feature\ConfigProviderInterface;
use Laminas\ModuleManager\Feature\ServiceProviderInterface;
use Laminas\ModuleManager\Feature\ViewHelperProviderInterface;
use Laminas\Mvc\MvcEvent;
use Laminas\Router\Http\TreeRouteStack;

class Module implements
    ConfigProviderInterface,
    ServiceProviderInterface,
    BootstrapListenerInterface,
    ViewHelperProviderInterface
{
    public function onBootstrap(EventInterface $e): void
    {
        if ($e instanceof MvcEvent) {
            $app          = $e->getApplication();
            $eventManager = $app->getEventManager();
            $container    = $app->getServiceManager();

            /** @var Listener\RouteListener $routeListener */
            $routeListener = $container->get(Listener\RouteListener::class);
            $routeListener->attach($eventManager);
        }
    }

    public function getConfig(): array
    {
        return require __DIR__ . '/../config/module.config.php';
    }

    public function getServiceConfig(): array
    {
        return [
            'factories'  => [
                Listener\RouteListener::class       => Listener\Service\RouteListenerFactory::class,
                Options\LanguageRouteOptions::class => Options\Service\LanguageRouteOptionsFactory::class,
            ],
            'delegators' => [
                'HttpRouter'          => [Mvc\Router\Http\Service\LanguageTreeRouteStackDelegatorFactory::class],
                TreeRouteStack::class => [Mvc\Router\Http\Service\LanguageTreeRouteStackDelegatorFactory::class],
            ],
        ];
    }

    public function getViewHelperConfig(): array
    {
        return [
            'factories' => [
                View\Helper\LanguageSwitch::class => View\Helper\Service\LanguageSwitchFactory::class,
            ],
            'aliases'   => [
                'languageSwitch' => View\Helper\LanguageSwitch::class,
            ],
        ];
    }
}
