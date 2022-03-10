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
use Laminas\Authentication\AuthenticationServiceInterface;
use Laminas\Router\RouteStackInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * @coversDefaultClass \Fabiang\LaminasLanguageRoute\Mvc\Router\Http\Service\LanguageTreeRouteStackDelegatorFactory
 */
class LanguageTreeRouteStackDelegatorFactoryTest extends TestCase
{
    use ProphecyTrait;

    private LanguageTreeRouteStackDelegatorFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new LanguageTreeRouteStackDelegatorFactory();
    }

    /**
     * @test
     * @covers ::__invoke
     */
    public function invoke(): void
    {
        $router    = $this->prophesize(LanguageTreeRouteStack::class);
        $container = $this->prophesize(ContainerInterface::class);

        $options = new LanguageRouteOptions();
        $container->has(LanguageRouteOptions::class)->willReturn(true);
        $container->get(LanguageRouteOptions::class)->willReturn($options);

        $auth = $this->prophesize(AuthenticationServiceInterface::class)->reveal();
        $container->has('authentication')->willReturn(true);
        $container->get('authentication')->willReturn($auth);

        $router->setAuthenticationService($auth)->shouldBeCalled();
        $router->setLanguageOptions($options)->shouldBeCalled();

        $instance = $this->factory->__invoke(
            $container->reveal(),
            RouteListener::class,
            function () use ($router) {
                return $router->reveal();
            },
            []
        );

        $this->assertInstanceOf(LanguageTreeRouteStack::class, $instance);
    }

    /**
     * @test
     * @covers ::__invoke
     */
    public function invokeNotCorrectRouterInstance(): void
    {
        $router    = $this->prophesize(RouteStackInterface::class);
        $container = $this->prophesize(ContainerInterface::class);

        $container->has(LanguageRouteOptions::class)->shouldNotBeCalled()->willReturn(true);
        $container->has('authentication')->shouldNotBeCalled()->willReturn(true);

        $instance = $this->factory->__invoke(
            $container->reveal(),
            RouteListener::class,
            function () use ($router) {
                return $router->reveal();
            },
            []
        );

        $this->assertNotInstanceOf(LanguageTreeRouteStack::class, $instance);
    }
}
