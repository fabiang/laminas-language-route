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
use Laminas\Mvc\Application;
use Laminas\Mvc\MvcEvent;
use Laminas\Router\Http\RouteMatch;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * @coversDefaultClass \Fabiang\LaminasLanguageRoute\View\Helper\Service\LanguageSwitchFactory
 */
class LanguageSwitchFactoryTest extends TestCase
{
    use ProphecyTrait;

    private LanguageSwitchFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new LanguageSwitchFactory();
    }

    /**
     * @test
     * @covers ::__invoke
     */
    public function invoke(): void
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->has(LanguageRouteOptions::class)->willReturn(true);
        $container->get(LanguageRouteOptions::class)
            ->willReturn(new LanguageRouteOptions());

        $mvcEvent   = new MvcEvent();
        $routeMatch = new RouteMatch([]);
        $mvcEvent->setRouteMatch($routeMatch);

        $app = $this->prophesize(Application::class);
        $app->getMvcEvent()->willReturn($mvcEvent);
        $container->get('Application')->willReturn($app->reveal());

        $instance = $this->factory->__invoke($container->reveal(), LanguageSwitch::class, []);
        $this->assertInstanceOf(LanguageSwitch::class, $instance);
        $this->assertSame($routeMatch, $instance->getRouteMatch());
    }

    /**
     * @test
     * @covers ::__invoke
     */
    public function invokeNoLanguageOptionsSet(): void
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->has(LanguageRouteOptions::class)->willReturn(false);
        $container->get(LanguageRouteOptions::class)
            ->shouldNotBeCalled()
            ->willReturn(new LanguageRouteOptions());

        $this->assertNull($this->factory->__invoke($container->reveal(), LanguageSwitch::class, []));
    }
}
