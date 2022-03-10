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

namespace Fabiang\LaminasLanguageRoute\Listener;

use Fabiang\LaminasLanguageRoute\Entity\LocaleUserInterface;
use Fabiang\LaminasLanguageRoute\Mvc\Router\Http\LanguageTreeRouteStack;
use Fabiang\LaminasLanguageRoute\Options\LanguageRouteOptions;
use Laminas\Authentication\AuthenticationServiceInterface;
use Laminas\EventManager\EventManagerInterface;
use Laminas\I18n\Translator\Translator;
use Laminas\Mvc\MvcEvent;
use Laminas\Router\RouteStackInterface;
use Laminas\Stdlib\RequestInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @coversDefaultClass \Fabiang\LaminasLanguageRoute\Listener\RouteListener
 */
class RouteListenerTest extends TestCase
{
    use ProphecyTrait;

    private RouteListener $object;
    private LanguageRouteOptions $options;
    private ObjectProphecy $router;
    private ObjectProphecy $request;
    private ObjectProphecy $translator;
    private ObjectProphecy $authService;

    protected function setUp(): void
    {
        $this->options     = new LanguageRouteOptions();
        $this->router      = $this->prophesize(LanguageTreeRouteStack::class);
        $this->request     = $this->prophesize(RequestInterface::class);
        $this->translator  = $this->prophesize(Translator::class);
        $this->authService = $this->prophesize(AuthenticationServiceInterface::class);

        $this->object = new RouteListener(
            $this->options,
            $this->router->reveal(),
            $this->request->reveal(),
            $this->translator->reveal(),
            $this->authService->reveal()
        );
    }

    /**
     * @test
     * @covers ::attach
     */
    public function attach(): void
    {
        $events = $this->prophesize(EventManagerInterface::class);
        $events->attach(MvcEvent::EVENT_ROUTE, [$this->object, 'onRoute'], 1000)
            ->shouldBeCalled();

        $this->object->attach($events->reveal(), 1000);
    }

    /**
     * @test
     * @covers ::__construct
     * @covers ::onRoute
     */
    public function onRoute(): void
    {
        $this->router->match(Argument::type(RequestInterface::class))
            ->shouldBeCalled();

        $this->router->getLastMatchedLocale()
            ->shouldBeCalled()
            ->willReturn('de_DE');

        $this->translator->setLocale('de_DE')->shouldBeCalled();

        $this->authService->hasIdentity()
            ->shouldBeCalled()
            ->willReturn(true);

        $identity = $this->prophesize(LocaleUserInterface::class);
        $identity->setLocale('de_DE')->shouldBeCalled();

        $this->authService->getIdentity()
            ->shouldBeCalled()
            ->willReturn($identity->reveal());

        $e = new MvcEvent();
        $this->object->onRoute($e);
    }

    /**
     * @test
     * @covers ::onRoute
     */
    public function onRouteNotCorrectRouter(): void
    {
        $router = $this->prophesize(RouteStackInterface::class);

        $object = new RouteListener(
            $this->options,
            $router->reveal(),
            $this->request->reveal(),
            $this->translator->reveal(),
            $this->authService->reveal()
        );

        $this->router->match(Argument::type(RequestInterface::class))
            ->shouldNotBeCalled();

        $this->router->getLastMatchedLocale()
            ->shouldNotBeCalled()
            ->willReturn('de_DE');

        $this->translator->setLocale('de_DE')->shouldNotBeCalled();

        $this->authService->hasIdentity()
            ->shouldNotBeCalled()
            ->willReturn(true);

        $identity = $this->prophesize(LocaleUserInterface::class);
        $identity->setLocale('de_DE')->shouldNotBeCalled();

        $this->authService->getIdentity()
            ->shouldNotBeCalled()
            ->willReturn($identity->reveal());

        $e = new MvcEvent();
        $object->onRoute($e);
    }

    /**
     * @test
     * @covers ::onRoute
     */
    public function onRouteNoMatchingLocale(): void
    {
        $this->router->match(Argument::type(RequestInterface::class))
            ->shouldBeCalled();

        $this->router->getLastMatchedLocale()
            ->shouldBeCalled()
            ->willReturn('');

        $this->translator->setLocale('')->shouldNotBeCalled();

        $this->authService->hasIdentity()
            ->shouldNotBeCalled()
            ->willReturn(true);

        $identity = $this->prophesize(LocaleUserInterface::class);
        $identity->setLocale('')->shouldNotBeCalled();

        $this->authService->getIdentity()
            ->shouldNotBeCalled()
            ->willReturn($identity->reveal());

        $e = new MvcEvent();
        $this->object->onRoute($e);
    }

    /**
     * @test
     * @covers ::__construct
     * @covers ::onRoute
     */
    public function onRouteNoAuthServicePassed(): void
    {
        $object = new RouteListener(
            $this->options,
            $this->router->reveal(),
            $this->request->reveal(),
            $this->translator->reveal(),
            null
        );

        $this->router->match(Argument::type(RequestInterface::class))
            ->shouldBeCalled();

        $this->router->getLastMatchedLocale()
            ->shouldBeCalled()
            ->willReturn('de_DE');

        $this->translator->setLocale('de_DE')->shouldBeCalled();

        $this->authService->hasIdentity()
            ->shouldNotBeCalled()
            ->willReturn(true);

        $identity = $this->prophesize(LocaleUserInterface::class);
        $identity->setLocale('de_DE')->shouldNotBeCalled();

        $this->authService->getIdentity()
            ->shouldNotBeCalled()
            ->willReturn($identity->reveal());

        $e = new MvcEvent();
        $object->onRoute($e);
    }
}
