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

namespace Fabiang\LaminasLanguageRoute\Options\Service;

use Fabiang\LaminasLanguageRoute\Options\LanguageRouteOptions;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

final class LanguageRouteOptionsFactory implements FactoryInterface
{
    public const CONFIG_KEY = 'language-route';

    /**
     * @param string $requestedName
     */
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): LanguageRouteOptions {
        $config = $container->get('config');
        return new LanguageRouteOptions($config[static::CONFIG_KEY] ?? []);
    }
}
