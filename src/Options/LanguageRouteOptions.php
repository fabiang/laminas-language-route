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

namespace Fabiang\LaminasLanguageRoute\Options;

use Laminas\Stdlib\AbstractOptions;

class LanguageRouteOptions extends AbstractOptions
{
    /**
     * Array of languages allowed for language route. The key is the prefix
     * which is attached to the url (e.g. en), the value is the associated
     * locale  (e.g. 'en_US')
     */
    protected array $languages = ['de' => 'de_DE', 'en' => 'en_US'];

    /**
     * This route name will be used if no RouteMatch instance is provided to
     * the languageSwitch ViewHelper. This happens for example if a 404 error
     * occurs.
     */
    protected string $homeRoute = 'home';

    public function getLanguages(): array
    {
        return $this->languages;
    }

    public function setLanguages(array $languages): void
    {
        $this->languages = $languages;
    }

    public function getHomeRoute(): string
    {
        return $this->homeRoute;
    }

    public function setHomeRoute(string $homeRoute): void
    {
        $this->homeRoute = $homeRoute;
    }
}
