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

namespace Fabiang\LaminasLanguageRoute\View\Helper;

use Fabiang\LaminasLanguageRoute\Options\LanguageRouteOptions;
use Laminas\I18n\Translator\Translator;
use Laminas\Router\RouteMatch;
use Laminas\View\Helper\Url as URLHelper;
use Laminas\View\Renderer\PhpRenderer;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @coversDefaultClass \Fabiang\LaminasLanguageRoute\View\Helper\LanguageSwitch
 */
class LanguageSwitchListItemTest extends TestCase
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
     * @covers ::renderListitem
     */
    public function invokeListItem(): void
    {
        $this->urlHelper->__invoke(null, ['test' => '1', 'locale' => 'de_DE'])->willReturn('/test');
        $this->urlHelper->__invoke(null, ['test' => '1', 'locale' => 'en_US'])->willReturn('/test');

        $translator = $this->prophesize(Translator::class);
        $result     = $this->helper->__invoke(
            LanguageSwitch::RENDER_TYPE_LIST_ITEM,
            'de_DE',
            [
                'translator'                => $translator->reveal(),
                'boxClass'                  => 'test1',
                'optionsClass'              => 'test2',
                'optionClass'               => 'test3',
                'optionActiveClass'         => 'test4',
                'optionLanguageClassPrefix' => 'test5',
                'optionLinkClass'           => 'test6',
                'captionLinkClass'          => 'test7',
            ]
        );
        $this->assertSame(
            '<li class="test1"><a class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" '
            . 'aria-expanded="false"><span class="test7" lang="de"></span><span class="sr-only">de_DE</span>'
            . '<span class="caret"></span></a><ul class="test2"><li class="test3 test4 test5de_DE"><a href="/test">'
            . '<span class="test6" lang="de"></span><span class="sr-only">de_DE</span></a></li><li class="test3 '
            . 'test5en_US"><a href="/test"><span class="test6" lang="en"></span><span class="sr-only">en_US</span>'
            . '</a></li></ul></li>',
            $result
        );
    }

    /**
     * @test
     * @covers ::__construct
     * @covers ::__invoke
     * @covers ::renderListitem
     */
    public function invokeListItemNoRouteMatchPassed(): void
    {
        $this->urlHelper->__invoke('home', ['locale' => 'de_DE'])->willReturn('/test');
        $this->urlHelper->__invoke('home', ['locale' => 'en_US'])->willReturn('/test');

        $helper = new LanguageSwitch(
            $this->languageOptions,
            null
        );
        $helper->setView($this->view->reveal());

        $translator = $this->prophesize(Translator::class);
        $result     = $helper->__invoke(
            LanguageSwitch::RENDER_TYPE_LIST_ITEM,
            'de_DE',
            [
                'translator' => $translator->reveal(),
            ]
        );
        $this->assertSame(
            '<li class="dropdown"><a class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" '
            . 'aria-expanded="false"><span class="lang-sm lang-lbl" lang="de"></span><span class="sr-only">de_DE</span>'
            . '<span class="caret"></span></a><ul class="dropdown-menu"><li class="language-option '
            . 'language-option-active language-option-language-de_DE"><a href="/test"><span class="lang-sm lang-lbl" '
            . 'lang="de"></span><span class="sr-only">de_DE</span></a></li><li class="language-option '
            . 'language-option-language-en_US"><a href="/test"><span class="lang-sm lang-lbl" lang="en"></span>'
            . '<span class="sr-only">en_US</span></a></li></ul></li>',
            $result
        );
    }

    /**
     * @test
     * @covers ::__construct
     * @covers ::__invoke
     * @covers ::renderListitem
     */
    public function invokeListItemNoCurrentLocalePassed(): void
    {
        $this->urlHelper->__invoke(null, ['test' => '1', 'locale' => 'de_DE'])->willReturn('/test');
        $this->urlHelper->__invoke(null, ['test' => '1', 'locale' => 'en_US'])->willReturn('/test');

        $translator = $this->prophesize(Translator::class);
        $translator->getLocale()->willReturn('de_DE');

        $result = $this->helper->__invoke(
            LanguageSwitch::RENDER_TYPE_LIST_ITEM,
            null,
            [
                'translator' => $translator->reveal(),
            ]
        );
        $this->assertSame(
            '<li class="dropdown"><a class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" '
            . 'aria-expanded="false"><span class="lang-sm lang-lbl" lang="de"></span><span class="sr-only">de_DE</span>'
            . '<span class="caret"></span></a><ul class="dropdown-menu"><li class="language-option '
            . 'language-option-active language-option-language-de_DE"><a href="/test"><span class="lang-sm lang-lbl" '
            . 'lang="de"></span><span class="sr-only">de_DE</span></a></li><li class="language-option '
            . 'language-option-language-en_US"><a href="/test"><span class="lang-sm lang-lbl" lang="en"></span>'
            . '<span class="sr-only">en_US</span></a></li></ul></li>',
            $result
        );
    }

    /**
     * @test
     * @covers ::__construct
     * @covers ::__invoke
     * @covers ::renderListitem
     */
    public function invokeListItemWithDefaultClasses(): void
    {
        $this->urlHelper->__invoke(null, ['test' => '1', 'locale' => 'de_DE'])->willReturn('/test');
        $this->urlHelper->__invoke(null, ['test' => '1', 'locale' => 'en_US'])->willReturn('/test');

        $translator = $this->prophesize(Translator::class);
        $result     = $this->helper->__invoke(
            LanguageSwitch::RENDER_TYPE_LIST_ITEM,
            'de_DE',
            [
                'translator' => $translator->reveal(),
            ]
        );
        $this->assertSame(
            '<li class="dropdown"><a class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" '
            . 'aria-expanded="false"><span class="lang-sm lang-lbl" lang="de"></span><span class="sr-only">de_DE</span>'
            . '<span class="caret"></span></a><ul class="dropdown-menu"><li class="language-option '
            . 'language-option-active language-option-language-de_DE"><a href="/test"><span class="lang-sm lang-lbl" '
            . 'lang="de"></span><span class="sr-only">de_DE</span></a></li><li class="language-option '
            . 'language-option-language-en_US"><a href="/test"><span class="lang-sm lang-lbl" lang="en"></span>'
            . '<span class="sr-only">en_US</span></a></li></ul></li>',
            $result
        );
    }

    /**
     * @test
     * @covers ::__construct
     * @covers ::__invoke
     * @covers ::renderListitem
     */
    public function invokeListItemWithInvalidLocale(): void
    {
        $this->urlHelper->__invoke(null, ['test' => '1', 'locale' => 'de'])->willReturn('/test');
        $this->urlHelper->__invoke(null, ['test' => '1', 'locale' => 'en'])->willReturn('/test');

        $this->languageOptions->setLanguages(['de' => 'de']);

        $translator = $this->prophesize(Translator::class);
        $result     = $this->helper->__invoke(
            LanguageSwitch::RENDER_TYPE_LIST_ITEM,
            'de_DE',
            [
                'translator' => $translator->reveal(),
            ]
        );

        $this->assertSame(
            '<li class="dropdown"><a class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" '
            . 'aria-expanded="false"><span class="lang-sm lang-lbl" lang="de"></span><span class="sr-only">de_DE</span>'
            . '<span class="caret"></span></a><ul class="dropdown-menu"><li class="language-option '
            . 'language-option-language-de"><a href="/test"><span class="lang-sm lang-lbl" lang="de"></span>'
            . '<span class="sr-only">de</span></a></li></ul></li>',
            $result
        );
    }

    /**
     * @test
     * @covers ::__construct
     * @covers ::__invoke
     * @covers ::renderListitem
     */
    public function invokeListItemWithInvalidLocaleAndNoLocalePassed(): void
    {
        $this->urlHelper->__invoke(null, ['test' => '1', 'locale' => 'de_DE'])->willReturn('/test');
        $this->urlHelper->__invoke(null, ['test' => '1', 'locale' => 'en_EN'])->willReturn('/test');

        $this->languageOptions->setLanguages(['de' => 'de_DE']);

        $translator = $this->prophesize(Translator::class);
        $result     = $this->helper->__invoke(
            LanguageSwitch::RENDER_TYPE_LIST_ITEM,
            null,
            [
                'translator' => $translator->reveal(),
            ]
        );

        $this->assertSame(
            '<li class="dropdown"><a class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" '
            . 'aria-expanded="false"><span class="lang-sm lang-lbl" lang="de"></span><span class="sr-only">de_DE</span>'
            . '<span class="caret"></span></a><ul class="dropdown-menu"><li class="language-option '
            . 'language-option-language-de_DE"><a href="/test"><span class="lang-sm lang-lbl" lang="de"></span>'
            . '<span class="sr-only">de_DE</span></a></li></ul></li>',
            $result
        );
    }
}
