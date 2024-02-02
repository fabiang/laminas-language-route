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

namespace Fabiang\LaminasLanguageRoute\Listener\Service;

use Fabiang\LaminasLanguageRoute\Listener\RouteListener;
use Fabiang\LaminasLanguageRoute\Options\LanguageRouteOptions;
use Laminas\Authentication\AuthenticationServiceInterface;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\Router\RouteStackInterface;
use Laminas\Stdlib\RequestInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;

/**
 * @coversDefaultClass \Fabiang\LaminasLanguageRoute\Listener\Service\RouteListenerFactory
 */
class RouteListenerFactoryTest extends TestCase
{
    use ProphecyTrait;

    private RouteListenerFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new RouteListenerFactory();
    }

    /**
     * @test
     * @covers ::__invoke
     */
    public function invoke(): void
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->get(LanguageRouteOptions::class)
            ->willReturn(new LanguageRouteOptions());
        $container->get('router')
            ->willReturn($this->prophesize(RouteStackInterface::class)->reveal());
        $container->get('request')
            ->willReturn($this->prophesize(RequestInterface::class)->reveal());
        $container->get('MvcTranslator')
            ->willReturn($this->prophesize(TranslatorInterface::class)->reveal());
        $container->has('authentication')->willReturn(true);
        $container->get('authentication')
            ->willReturn($this->prophesize(AuthenticationServiceInterface::class)->reveal());

        $this->assertInstanceOf(
            RouteListener::class,
            $this->factory->__invoke($container->reveal(), RouteListener::class, [])
        );
    }
}
