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

namespace Fabiang\LaminasLanguageRoute\View\Helper\Service;

use Fabiang\LaminasLanguageRoute\Options\LanguageRouteOptions;
use Fabiang\LaminasLanguageRoute\View\Helper\LanguageSwitch;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

final class LanguageSwitchFactory implements FactoryInterface
{
    /**
     * @param string $requestedName
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): ?LanguageSwitch
    {
        if (! $container->has(LanguageRouteOptions::class)) {
            return null;
        }

        $languageOptions = $container->get(LanguageRouteOptions::class);
        $routeMatch      = $container->get('Application')->getMvcEvent()->getRouteMatch();
        return new LanguageSwitch($languageOptions, $routeMatch);
    }
}
