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

use Fabiang\LaminasLanguageRoute\Listener\RouteListener;
use Fabiang\LaminasLanguageRoute\Mvc\Router\Http\LanguageTreeRouteStack;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\Mvc\ApplicationInterface;
use Laminas\Mvc\MvcEvent;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;

/**
 * @coversDefaultClass \Fabiang\LaminasLanguageRoute\Module
 */
class ModuleTest extends TestCase
{
    use ProphecyTrait;

    private Module $module;

    protected function setUp(): void
    {
        $this->module = new Module();
    }

    /**
     * @test
     * @covers ::onBootstrap
     */
    public function onBootstrap(): void
    {
        $events = $this->prophesize(EventManagerInterface::class);

        $listener = $this->prophesize(ListenerAggregateInterface::class);
        $listener->attach(Argument::type(EventManagerInterface::class))->shouldBeCalled();

        $container = $this->prophesize(ContainerInterface::class);
        $container->get(RouteListener::class)
            ->willReturn($listener->reveal());

        $app = $this->prophesize(ApplicationInterface::class);
        $app->getEventManager()->willReturn($events->reveal());
        $app->getServiceManager()->willReturn($container->reveal());

        $e = $this->prophesize(MvcEvent::class);
        $e->getApplication()->willReturn($app->reveal());
        $this->module->onBootstrap($e->reveal());
    }

    /**
     * @test
     * @covers ::getConfig
     */
    public function getConfig(): void
    {
        $config = $this->module->getConfig();
        $this->assertIsArray($config);
        $this->assertArrayHasKey('router', $config);
        $this->assertArrayHasKey('router_class', $config['router']);
        $this->assertSame(LanguageTreeRouteStack::class, $config['router']['router_class']);
    }

    /**
     * @test
     * @covers ::getServiceConfig
     */
    public function getServiceConfig(): void
    {
        $config = $this->module->getServiceConfig();
        $this->assertIsArray($config);
        $this->assertArrayHasKey('factories', $config);
        $this->assertArrayHasKey('delegators', $config);
    }

    /**
     * @test
     * @covers ::getViewHelperConfig
     */
    public function getViewHelperConfig(): void
    {
        $config = $this->module->getViewHelperConfig();
        $this->assertIsArray($config);
        $this->assertArrayHasKey('factories', $config);
        $this->assertArrayHasKey('aliases', $config);
    }
}
