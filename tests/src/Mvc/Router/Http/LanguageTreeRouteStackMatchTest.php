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

use Fabiang\LaminasLanguageRoute\Entity\LocaleUserInterface;
use Fabiang\LaminasLanguageRoute\Options\LanguageRouteOptions;
use Laminas\Authentication\AuthenticationServiceInterface;
use Laminas\Http\PhpEnvironment\Request;
use Laminas\I18n\Translator\Translator;
use Laminas\Router\Http\RouteMatch;
use Laminas\Stdlib\RequestInterface;
use Laminas\Uri\Http as URI;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * @coversDefaultClass \Fabiang\LaminasLanguageRoute\Mvc\Router\Http\LanguageTreeRouteStack
 */
class LanguageTreeRouteStackMatchTest extends TestCase
{
    use ProphecyTrait;

    private LanguageTreeRouteStack $route;

    protected function setUp(): void
    {
        $this->route = new LanguageTreeRouteStack();
    }

    /**
     * @test
     * @covers ::match
     */
    public function matchNoURI(): void
    {
        $request = $this->prophesize(RequestInterface::class);
        $this->assertNull($this->route->match($request->reveal()));
    }

    /**
     * @test
     * @covers ::match
     * @covers ::getLastMatchedLocale
     */
    public function matchLocaleFromURI(): void
    {
        $translator = $this->prophesize(Translator::class);
        $translator->getLocale()->willReturn('de_DE');

        $options = new LanguageRouteOptions();
        $this->route->setLanguageOptions($options);

        $this->route->setBaseUrl('/foobar');
        $this->route->addRoute('phpunit', ['type' => 'literal', 'options' => ['route' => '/phpunit']]);

        $uri     = new URI('http://phpunit/foobar/de/phpunit');
        $request = $this->prophesize(Request::class);
        $request->getUri()->willReturn($uri);

        $routeMatch = $this->route->match($request->reveal(), null, ['translator' => $translator->reveal()]);
        $this->assertInstanceOf(RouteMatch::class, $routeMatch);
        $this->assertSame('phpunit', $routeMatch->getMatchedRouteName());
        $this->assertSame(['locale' => 'de_DE'], $routeMatch->getParams());
        $this->assertSame('/foobar', $this->route->getBaseUrl());
        $this->assertSame('de_DE', $this->route->getLastMatchedLocale());
    }

    /**
     * @test
     * @covers ::match
     * @covers ::getLastMatchedLocale
     */
    public function matchLocaleFromURIWithBaseURLFormRequest(): void
    {
        $translator = $this->prophesize(Translator::class);
        $translator->getLocale()->willReturn('de_DE');

        $options = new LanguageRouteOptions();
        $this->route->setLanguageOptions($options);

        $this->route->addRoute('phpunit', ['type' => 'literal', 'options' => ['route' => '/phpunit']]);

        $uri     = new URI('http://phpunit/foobar/de/phpunit');
        $request = $this->prophesize(Request::class);
        $request->getUri()->willReturn($uri);
        $request->getBaseUrl()->willReturn('/foobar');

        $routeMatch = $this->route->match($request->reveal(), null, ['translator' => $translator->reveal()]);
        $this->assertInstanceOf(RouteMatch::class, $routeMatch);
        $this->assertSame('phpunit', $routeMatch->getMatchedRouteName());
        $this->assertSame(['locale' => 'de_DE'], $routeMatch->getParams());
        $this->assertSame('/foobar', $this->route->getBaseUrl());
        $this->assertSame('de_DE', $this->route->getLastMatchedLocale());
    }

    /**
     * @test
     * @covers ::match
     * @covers ::getLastMatchedLocale
     */
    public function matchLocaleFromURIWithTranslatorPassed(): void
    {
        $translator = $this->prophesize(Translator::class);
        $translator->getLocale()->willReturn('de_DE');
        $this->route->setTranslator($translator->reveal());

        $options = new LanguageRouteOptions();
        $this->route->setLanguageOptions($options);

        $this->route->setBaseUrl('/foobar');
        $this->route->addRoute('phpunit', ['type' => 'literal', 'options' => ['route' => '/phpunit']]);

        $uri     = new URI('http://phpunit/foobar/de/phpunit');
        $request = $this->prophesize(Request::class);
        $request->getUri()->willReturn($uri);

        $routeMatch = $this->route->match($request->reveal(), null, []);
        $this->assertInstanceOf(RouteMatch::class, $routeMatch);
        $this->assertSame('phpunit', $routeMatch->getMatchedRouteName());
        $this->assertSame(['locale' => 'de_DE'], $routeMatch->getParams());
        $this->assertSame('/foobar', $this->route->getBaseUrl());
        $this->assertSame('de_DE', $this->route->getLastMatchedLocale());
    }

    /**
     * @test
     * @covers ::match
     * @covers ::getLastMatchedLocale
     */
    public function matchLocaleFromIdentity(): void
    {
        $identity = $this->prophesize(LocaleUserInterface::class);
        $identity->getLocale()->willReturn('de_DE');

        $options = new LanguageRouteOptions();
        $this->route->setLanguageOptions($options);

        $authenticationService = $this->prophesize(AuthenticationServiceInterface::class);
        $authenticationService->hasIdentity()->willReturn(true);
        $authenticationService->getIdentity()->willReturn($identity->reveal());

        $this->route->setAuthenticationService($authenticationService->reveal());

        $translator = $this->prophesize(Translator::class);
        $translator->getLocale()->willReturn('de_DE');

        $this->route->setBaseUrl('/foobar');
        $this->route->addRoute('phpunit', ['type' => 'literal', 'options' => ['route' => '/phpunit']]);

        $uri     = new URI('http://phpunit/foobar/phpunit');
        $request = $this->prophesize(Request::class);
        $request->getUri()->willReturn($uri);

        $routeMatch = $this->route->match($request->reveal(), null, ['translator' => $translator->reveal()]);
        $this->assertInstanceOf(RouteMatch::class, $routeMatch);
        $this->assertSame('phpunit', $routeMatch->getMatchedRouteName());
        $this->assertSame(['locale' => 'de_DE'], $routeMatch->getParams());
        $this->assertSame('/foobar', $this->route->getBaseUrl());
        $this->assertSame('de_DE', $this->route->getLastMatchedLocale());
    }

    /**
     * @test
     * @covers ::match
     * @covers ::getLastMatchedLocale
     */
    public function matchLocaleFromTranslator(): void
    {
        $translator = $this->prophesize(Translator::class);
        $translator->getLocale()->willReturn('de_DE');

        $this->route->setBaseUrl('/foobar');
        $this->route->addRoute('phpunit', ['type' => 'literal', 'options' => ['route' => '/phpunit']]);

        $uri     = new URI('http://phpunit/foobar/phpunit');
        $request = $this->prophesize(Request::class);
        $request->getUri()->willReturn($uri);

        $routeMatch = $this->route->match($request->reveal(), null, ['translator' => $translator->reveal()]);
        $this->assertInstanceOf(RouteMatch::class, $routeMatch);
        $this->assertSame('phpunit', $routeMatch->getMatchedRouteName());
        $this->assertSame(['locale' => 'de_DE'], $routeMatch->getParams());
        $this->assertSame('/foobar', $this->route->getBaseUrl());
        $this->assertSame('de_DE', $this->route->getLastMatchedLocale());
    }

    /**
     * @test
     * @covers ::getLanguageOptions
     * @covers ::setLanguageOptions
     */
    public function languageOptions(): void
    {
        $languageOptions = new LanguageRouteOptions();
        $this->assertNull($this->route->getLanguageOptions());
        $this->route->setLanguageOptions($languageOptions);
        $this->assertSame($languageOptions, $this->route->getLanguageOptions());
    }

    /**
     * @test
     * @covers ::getAuthenticationService
     * @covers ::setAuthenticationService
     */
    public function authenticationService(): void
    {
        $this->assertNull($this->route->getAuthenticationService());
        $authenticationService = $this->prophesize(AuthenticationServiceInterface::class)->reveal();
        $this->route->setAuthenticationService($authenticationService);
        $this->assertSame($authenticationService, $this->route->getAuthenticationService());
    }
}
