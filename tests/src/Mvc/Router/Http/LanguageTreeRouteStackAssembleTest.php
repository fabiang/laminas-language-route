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

namespace Fabiang\LaminasLanguageRoute\Mvc\Router\Http;

use Fabiang\LaminasLanguageRoute\Options\LanguageRouteOptions;
use Laminas\I18n\Translator\Translator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

#[CoversClass(LanguageTreeRouteStack::class)]
class LanguageTreeRouteStackAssembleTest extends TestCase
{
    use ProphecyTrait;

    private LanguageTreeRouteStack $route;
    private LanguageRouteOptions $options;

    protected function setUp(): void
    {
        $this->route   = new LanguageTreeRouteStack();
        $this->options = new LanguageRouteOptions();
        $this->route->setLanguageOptions($this->options);
    }

    public function testAssembleLocaleFromTranslatorFromOptions(): void
    {
        $translator = $this->prophesize(Translator::class);
        $translator->getLocale()->willReturn('de_DE');

        $this->route->setBaseUrl('/foobar');

        $this->route->addRoute('phpunit', ['type' => 'literal', 'options' => ['route' => '/phpunit']]);

        $this->assertSame(
            '/foobar/de/phpunit',
            $this->route->assemble([], ['translator' => $translator->reveal(), 'name' => 'phpunit'])
        );
        $this->assertSame('/foobar', $this->route->getBaseUrl());
    }

    public function testAssembleLocaleWithoutLanguages(): void
    {
        $this->route->setLanguageOptions(null);

        $translator = $this->prophesize(Translator::class);
        $translator->getLocale()->willReturn('de_DE');

        $this->route->setBaseUrl('/foobar');

        $this->route->addRoute('phpunit', ['type' => 'literal', 'options' => ['route' => '/phpunit']]);

        $this->assertSame(
            '/foobar/phpunit',
            $this->route->assemble([], ['translator' => $translator->reveal(), 'name' => 'phpunit'])
        );
        $this->assertSame('/foobar', $this->route->getBaseUrl());
    }

    public function testAssembleLocaleWithTranslatorFromObject(): void
    {
        $translator = $this->prophesize(Translator::class);
        $translator->getLocale()->willReturn('de_DE');

        $this->route->setTranslator($translator->reveal());

        $this->route->setBaseUrl('/foobar');

        $this->route->addRoute('phpunit', ['type' => 'literal', 'options' => ['route' => '/phpunit']]);

        $this->assertSame(
            '/foobar/de/phpunit',
            $this->route->assemble([], ['name' => 'phpunit'])
        );
        $this->assertSame('/foobar', $this->route->getBaseUrl());
    }

    public function testAssembleLocaleFromParams(): void
    {
        $translator = $this->prophesize(Translator::class);
        $translator->getLocale()->willReturn('de_DE');

        $this->route->setBaseUrl('/foobar');

        $this->route->addRoute('phpunit', ['type' => 'literal', 'options' => ['route' => '/phpunit']]);

        $this->assertSame(
            '/foobar/en/phpunit',
            $this->route->assemble(['locale' => 'en_US'], ['translator' => $translator->reveal(), 'name' => 'phpunit'])
        );
        $this->assertSame('/foobar', $this->route->getBaseUrl());
    }

    public function testAssembleLocaleWithoutTranslator(): void
    {
        $this->route->setBaseUrl('/foobar');

        $this->route->addRoute('phpunit', ['type' => 'literal', 'options' => ['route' => '/phpunit']]);

        $this->assertSame(
            '/foobar/phpunit',
            $this->route->assemble([], ['name' => 'phpunit'])
        );
        $this->assertSame('/foobar', $this->route->getBaseUrl());
    }
}
