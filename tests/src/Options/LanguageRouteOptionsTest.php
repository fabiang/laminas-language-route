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

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * @coversDefaultClass \Fabiang\LaminasLanguageRoute\Options\LanguageRouteOptions
 */
class LanguageRouteOptionsTest extends TestCase
{
    use ProphecyTrait;

    private LanguageRouteOptions $options;

    protected function setUp(): void
    {
        $this->options = new LanguageRouteOptions([
            'languages' => ['ru' => 'ru_RU', 'uk' => 'uk_UA'],
            'homeRoute' => 'test',
        ]);
    }

    /**
     * @test
     * @covers ::__construct
     * @covers ::setLanguages
     * @covers ::getLanguages
     */
    public function language(): void
    {
        $this->assertSame(['ru' => 'ru_RU', 'uk' => 'uk_UA'], $this->options->getLanguages());
        $this->options->setLanguages(['de' => 'de_DE']);
        $this->assertSame(['de' => 'de_DE'], $this->options->getLanguages());
    }

    /**
     * @test
     * @covers ::__construct
     * @covers ::setLanguages
     * @covers ::getLanguages
     */
    public function homeRoute(): void
    {
        $this->assertSame('test', $this->options->getHomeRoute());
        $this->options->setHomeRoute('foobar');
        $this->assertSame('foobar', $this->options->getHomeRoute());
    }

    /**
     * @test
     * @covers ::__construct
     * @covers ::setHomeRoute
     * @covers ::getHomeRoute
     */
    public function defaults(): void
    {
        $options = new LanguageRouteOptions();
        $this->assertSame(['de' => 'de_DE', 'en' => 'en_US'], $options->getLanguages());
        $this->assertSame('home', $options->getHomeRoute());
    }
}
