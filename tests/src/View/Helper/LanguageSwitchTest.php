<?php

declare(strict_types=1);

namespace Fabiang\LaminasLanguageRoute\View\Helper;

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

use Fabiang\LaminasLanguageRoute\Options\LanguageRouteOptions;
use Laminas\I18n\Exception as I18nException;
use Laminas\I18n\Translator\Translator;
use Laminas\Router\RouteMatch;
use Laminas\View\Helper\Url as URLHelper;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Renderer\RendererInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @coversDefaultClass \Fabiang\LaminasLanguageRoute\View\Helper\LanguageSwitch
 */
class LanguageSwitchTest extends TestCase
{
    use ProphecyTrait;

    private LanguageSwitch $helper;
    private LanguageRouteOptions $languageOptions;
    private RouteMatch $routeMatch;
    private ObjectProphecy $view;
    private ObjectProphecy $urlHelper;

    protected function setUp(): void
    {
        $this->view = $this->prophesize(PhpRenderer::class);

        $this->languageOptions = new LanguageRouteOptions();
        $this->routeMatch      = new RouteMatch(['test' => '1']);
        $this->routeMatch->setMatchedRouteName('testroute');

        $this->urlHelper = $this->prophesize(URLHelper::class);

        $this->view->plugin('url')
            ->willReturn($this->urlHelper->reveal());

        $this->helper = new LanguageSwitch(
            $this->languageOptions,
            $this->routeMatch
        );
        $this->helper->setView($this->view->reveal());
    }

    /**
     * @test
     * @covers ::__construct
     * @covers ::__invoke
     * @covers ::renderPartial
     */
    public function invokePartial(): void
    {
        $translator = $this->prophesize(Translator::class);

        $this->view->render('mypartial', Argument::that(function (array $options) {
                    $this->assertArrayHasKey('translator', $options);
                    $this->assertArrayHasKey('locales', $options);
                    $this->assertArrayHasKey('currentLocale', $options);
                    $this->assertArrayHasKey('config', $options);

                    $this->assertInstanceOf(Translator::class, $options['translator']);
                    $this->assertSame(['de' => 'de_DE', 'en' => 'en_US'], $options['locales']);
                    $this->assertSame('de_DE', $options['currentLocale']);
                    $this->assertIsArray($options['config']);
                    return true;
        }))
            ->shouldBeCalled()
            ->willReturn('partialcontent');

        $result = $this->helper->__invoke(
            LanguageSwitch::RENDER_TYPE_PARTIAL,
            'de_DE',
            [
                'translator' => $translator->reveal(),
                'partial'    => 'mypartial',
            ]
        );

        $this->assertSame('partialcontent', $result);
    }

    /**
     * @test
     * @covers ::__construct
     * @covers ::__invoke
     * @covers ::renderPartial
     */
    public function invokeListItemNoPartialGiven(): void
    {
        $this->expectException(I18nException\InvalidArgumentException::class);

        $translator = $this->prophesize(Translator::class);

        $this->helper->__invoke(
            LanguageSwitch::RENDER_TYPE_PARTIAL,
            'de_DE',
            [
                'translator' => $translator->reveal(),
            ]
        );
    }

    /**
     * @test
     * @covers ::__construct
     * @covers ::__invoke
     * @covers ::renderNavbar
     */
    public function invokeNavbar(): void
    {
        $translator = $this->prophesize(Translator::class);

        $result = $this->helper->__invoke(
            LanguageSwitch::RENDER_TYPE_NAVBAR,
            'de_DE',
            [
                'translator' => $translator->reveal(),
            ]
        );

        $this->assertSame(
            '<ul class="nav navbar-nav"><li class="dropdown"><a class="dropdown-toggle" data-toggle="dropdown" '
            . 'role="button" aria-haspopup="true" aria-expanded="false"><span class="lang-sm lang-lbl" lang="de">'
            . '</span><span class="sr-only">de_DE</span><span class="caret"></span></a><ul class="dropdown-menu">'
            . '<li class="language-option language-option-active language-option-language-de_DE"><a href="">'
            . '<span class="lang-sm lang-lbl" lang="de"></span><span class="sr-only">de_DE</span></a></li><li '
            . 'class="language-option language-option-language-en_US"><a href=""><span class="lang-sm lang-lbl"'
            . ' lang="en"></span><span class="sr-only">en_US</span></a></li></ul></li></ul>',
            $result
        );
    }

    /**
     * @test
     * @covers ::__construct
     * @covers ::__invoke
     * @covers ::renderSelect
     */
    public function invokeSelect(): void
    {
        $translator = $this->prophesize(Translator::class);
        $translator->getLocale()->willReturn('de_DE');

        $result = $this->helper->__invoke(
            LanguageSwitch::RENDER_TYPE_SELECT,
            null,
            [
                'translator'  => $translator->reveal(),
                'selectClass' => 'test1',
            ]
        );

        $this->assertSame(
            '<select class="test1"><option selected="selected" value="de_DE">de</option>'
            . '<option  value="en_US">en</option></select>',
            $result
        );
    }

    /**
     * @test
     * @covers ::__construct
     * @covers ::__invoke
     * @covers ::renderDiv
     */
    public function invokeDiv(): void
    {
        $this->urlHelper->__invoke(null, ['test' => '1', 'locale' => 'de_DE'])->willReturn('/test');
        $this->urlHelper->__invoke(null, ['test' => '1', 'locale' => 'en_US'])->willReturn('/test');

        $translator = $this->prophesize(Translator::class);
        $translator->getLocale()->willReturn('de_DE');

        $result = $this->helper->__invoke(
            LanguageSwitch::RENDER_TYPE_DIV,
            null,
            [
                'translator'                => $translator->reveal(),
                'boxClass'                  => 'test1',
                'optionClass'               => 'test2',
                'optionActiveClass'         => 'test3',
                'optionLanguageClassPrefix' => 'test4',
                'optionLinkClass'           => 'test5',
            ]
        );

        $this->assertSame(
            '<div class="test1"><div class="test2 test3 test4de_DE"><a class="test5" href="/test" lang="de"></a></div>'
            . '<div class="test2 test4en_US"><a class="test5" href="/test" lang="en"></a></div></div>',
            $result
        );
    }

    /**
     * @test
     * @covers ::__construct
     * @covers ::__invoke
     * @covers ::renderDiv
     */
    public function invokeDivNotRouteMatchPassed(): void
    {
        $this->urlHelper->__invoke('home', ['locale' => 'de_DE'])->willReturn('/test');
        $this->urlHelper->__invoke('home', ['locale' => 'en_US'])->willReturn('/test');

        $helper = new LanguageSwitch($this->languageOptions, null);
        $helper->setView($this->view->reveal());

        $translator = $this->prophesize(Translator::class);
        $translator->getLocale()->willReturn('de_DE');

        $result = $helper->__invoke(
            LanguageSwitch::RENDER_TYPE_DIV,
            null,
            [
                'translator'                => $translator->reveal(),
                'boxClass'                  => 'test1',
                'optionClass'               => 'test2',
                'optionActiveClass'         => 'test3',
                'optionLanguageClassPrefix' => 'test4',
                'optionLinkClass'           => 'test5',
            ]
        );

        $this->assertSame(
            '<div class="test1"><div class="test2 test3 test4de_DE"><a class="test5" href="/test" lang="de"></a></div>'
            . '<div class="test2 test4en_US"><a class="test5" href="/test" lang="en"></a></div></div>',
            $result
        );
    }

    /**
     * @test
     * @covers ::__invoke
     */
    public function invokeNotPluggableView(): void
    {
        $view = $this->prophesize(RendererInterface::class);
        $this->helper->setView($view->reveal());
        $this->assertSame('', $this->helper->__invoke(LanguageSwitch::RENDER_TYPE_PARTIAL));
    }

    /**
     * @test
     * @covers ::__invoke
     */
    public function invokeNoTranslator(): void
    {
        $this->expectException(I18nException\RuntimeException::class);
        $this->helper->__invoke(LanguageSwitch::RENDER_TYPE_PARTIAL);
    }

    /**
     * @test
     * @covers ::__invoke
     */
    public function invokeInvalidRendererType(): void
    {
        $translator = $this->prophesize(Translator::class);
        $this->expectException(I18nException\InvalidArgumentException::class);
        $this->helper->__invoke('unknown', null, ['translator' => $translator->reveal()]);
    }

    /**
     * @test
     * @covers ::__construct
     * @covers ::getLanguageOptions
     * @covers ::getRouteMatch
     */
    public function getters(): void
    {
        $this->assertSame($this->languageOptions, $this->helper->getLanguageOptions());
        $this->assertSame($this->routeMatch, $this->helper->getRouteMatch());
    }
}
