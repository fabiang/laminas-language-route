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
use Laminas\I18n\Exception;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\I18n\View\Helper\AbstractTranslatorHelper;
use Laminas\Router\RouteMatch;
use Laminas\View\Renderer\PhpRenderer;

use function array_keys;
use function array_search;
use function method_exists;
use function sprintf;
use function strpos;
use function substr;

class LanguageSwitch extends AbstractTranslatorHelper
{
    public const RENDER_TYPE_SELECT    = 'select';
    public const RENDER_TYPE_DIV       = 'div';
    public const RENDER_TYPE_NAVBAR    = 'navbar';
    public const RENDER_TYPE_LIST_ITEM = 'listitem';
    public const RENDER_TYPE_PARTIAL   = 'partial';

    protected LanguageRouteOptions $languageOptions;
    protected ?RouteMatch $routeMatch;

    /**
     * defaults
     */
    protected static string $selectFormat                    = '<select class="%s">%s</select>';
    protected static string $optionFormat                    = '<option %s value="%s">%s</option>';
    protected static string $selectClass                     = 'language-switch';
    protected static string $divBoxFormat                    = '<div class="%s">%s</div>';
    protected static string $divOptionFormat                 = '<div class="%s">%s</div>';
    protected static string $divOptionLinkFormat             = '<a class="%s" href="%s" lang="%s"></a>';
    protected static string $divBoxClass                     = 'language-switch';
    protected static string $divOptionClass                  = 'language-option';
    protected static string $divOptionActiveClass            = 'language-option-active';
    protected static string $divOptionLanguageClassPrefix    = 'language-option-language-';
    protected static string $divOptionLinkClass              = 'lang-sm lang-lbl';
    protected static string $navbarOuterBoxFormat            = '<ul class="nav navbar-nav">%s</ul>';
    protected static string $navbarBoxFormat                 = '<li class="%s">%s</li>';
    protected static string $navbarOptionsFormat             = '<ul class="%s">%s</ul>';
    protected static string $navbarOptionFormat              = '<li class="%s">%s</li>';
    protected static string $navbarCaptionLinkFormat         = '<a class="dropdown-toggle" data-toggle="dropdown"'
        . ' role="button" aria-haspopup="true" aria-expanded="false"><span class="%s" lang="%s"></span>'
        . '<span class="sr-only">%s</span><span class="caret"></span></a>';
    protected static string $navbarOptionLinkFormat          = '<a href="%s"><span class="%s" lang="%s"></span>'
        . '<span class="sr-only">%s</span></a>';
    protected static string $navbarBoxClass                  = 'dropdown';
    protected static string $navbarOptionsClass              = 'dropdown-menu';
    protected static string $navbarOptionClass               = 'language-option';
    protected static string $navbarOptionActiveClass         = 'language-option-active';
    protected static string $navbarOptionLanguageClassPrefix = 'language-option-language-';
    protected static string $navbarCaptionLinkClass          = 'lang-sm lang-lbl';
    protected static string $navbarOptionLinkClass           = 'lang-sm lang-lbl';

    public function __construct(LanguageRouteOptions $languageOptions, ?RouteMatch $routeMatch = null)
    {
        $this->routeMatch      = $routeMatch;
        $this->languageOptions = $languageOptions;
    }

    public function __invoke(
        string $renderType = self::RENDER_TYPE_LIST_ITEM,
        ?string $currentLocale = null,
        array $config = []
    ): string {
        /** @var PhpRenderer $renderer */
        $renderer = $this->getView();
        if (! method_exists($renderer, 'plugin')) {
            // Bail early if renderer is not pluggable
            return '';
        }

        if (isset($config['translator'])) {
            $translator = $config['translator'];
        } else {
            $translator = $this->getTranslator();
        }

        if (null === $translator) {
            throw new Exception\RuntimeException('Translator has not been set');
        }

        $locales = $this->getLanguageOptions()->getLanguages();

        switch ($renderType) {
            case self::RENDER_TYPE_NAVBAR:
                return $this->renderNavbar($translator, $locales, $currentLocale, $config);
            case self::RENDER_TYPE_LIST_ITEM:
                return $this->renderListitem($translator, $locales, $currentLocale, $config);
            case self::RENDER_TYPE_SELECT:
                return $this->renderSelect($translator, $locales, $currentLocale, $config);
            case self::RENDER_TYPE_DIV:
                return $this->renderDiv($translator, $locales, $currentLocale, $config);
            case self::RENDER_TYPE_PARTIAL:
                return $this->renderPartial($translator, $locales, $currentLocale, $config);
        }

        throw new Exception\InvalidArgumentException(sprintf('Invalid render type %s', $renderType));
    }

    protected function renderSelect(
        TranslatorInterface $translator,
        array $locales,
        ?string $currentLocale = null,
        array $config = []
    ): string {
        // check config and set variables
        $selectClass = static::$selectClass;
        if (isset($config['selectClass'])) {
            $selectClass = $config['selectClass'];
        }

        if (null === $currentLocale) {
            // detect current locale if not given
            if (method_exists($translator, 'getLocale')) {
                $currentLocale = $translator->getLocale();
            }
        }

        // build options
        $options = '';
        foreach ($locales as $localeKey => $locale) {
            $selected = '';
            if ($locale === $currentLocale) {
                $selected = 'selected="selected"';
            }
            $options .= sprintf(static::$optionFormat, $selected, $locale, $localeKey);
        }
        // return select with options
        return sprintf(static::$selectFormat, $selectClass, $options);
    }

    protected function renderDiv(
        TranslatorInterface $translator,
        array $locales,
        ?string $currentLocale = null,
        array $config = []
    ): string {
        // check config and set variables
        $boxClass = static::$divBoxClass;
        if (isset($config['boxClass'])) {
            $boxClass = $config['boxClass'];
        }
        $optionClass = static::$divOptionClass;
        if (isset($config['optionClass'])) {
            $optionClass = $config['optionClass'];
        }
        $optionActiveClass = static::$divOptionActiveClass;
        if (isset($config['optionActiveClass'])) {
            $optionActiveClass = $config['optionActiveClass'];
        }
        $optionLanguageClassPrefix = static::$divOptionLanguageClassPrefix;
        if (isset($config['optionLanguageClassPrefix'])) {
            $optionLanguageClassPrefix = $config['optionLanguageClassPrefix'];
        }
        $optionLinkClass = static::$divOptionLinkClass;
        if (isset($config['optionLinkClass'])) {
            $optionLinkClass = $config['optionLinkClass'];
        }

        if (null === $currentLocale) {
            // detect current locale if not given
            if (method_exists($translator, 'getLocale')) {
                $currentLocale = $translator->getLocale();
            }
        }
        $urlPlugin = $this->getView()->plugin('url');

        // build options
        $options = '';
        foreach ($locales as $localeKey => $locale) {
            $optClass = $optionClass;
            if ($locale === $currentLocale) {
                $optClass .= ' ' . $optionActiveClass;
            }
            $optClass  .= ' ' . $optionLanguageClassPrefix . $locale;
            $parameters = [];
            if ($this->getRouteMatch()) {
                $parameters = $this->getRouteMatch()->getParams();
            }
            $parameters['locale'] = $locale;

            $routeName = null;
            if (null === $this->routeMatch) {
                $routeName = $this->getLanguageOptions()->getHomeRoute();
            }
            $url      = $urlPlugin($routeName, $parameters);
            $link     = sprintf(static::$divOptionLinkFormat, $optionLinkClass, $url, $localeKey);
            $options .= sprintf(static::$divOptionFormat, $optClass, $link);
        }

        // return container with options
        return sprintf(static::$divBoxFormat, $boxClass, $options);
    }

    protected function renderNavbar(
        TranslatorInterface $translator,
        array $locales,
        ?string $currentLocale = null,
        array $config = []
    ): string {
        $listitem = $this->renderListitem($translator, $locales, $currentLocale, $config);
        return sprintf(static::$navbarOuterBoxFormat, $listitem);
    }

    protected function renderListitem(
        TranslatorInterface $translator,
        array $locales,
        ?string $currentLocale = null,
        array $config = []
    ): string {
        // check config and set variables
        $boxClass = static::$navbarBoxClass;
        if (isset($config['boxClass'])) {
            $boxClass = $config['boxClass'];
        }

        $optionsClass = static::$navbarOptionsClass;
        if (isset($config['optionsClass'])) {
            $optionsClass = $config['optionsClass'];
        }

        $optionClass = static::$navbarOptionClass;
        if (isset($config['optionClass'])) {
            $optionClass = $config['optionClass'];
        }

        $optionActiveClass = static::$navbarOptionActiveClass;
        if (isset($config['optionActiveClass'])) {
            $optionActiveClass = $config['optionActiveClass'];
        }

        $optionLanguageClassPrefix = static::$navbarOptionLanguageClassPrefix;
        if (isset($config['optionLanguageClassPrefix'])) {
            $optionLanguageClassPrefix = $config['optionLanguageClassPrefix'];
        }

        $optionLinkClass = static::$navbarOptionLinkClass;
        if (isset($config['optionLinkClass'])) {
            $optionLinkClass = $config['optionLinkClass'];
        }

        $captionLinkClass = static::$navbarCaptionLinkClass;
        if (isset($config['captionLinkClass'])) {
            $captionLinkClass = $config['captionLinkClass'];
        }

        if (null === $currentLocale) {
            // detect current locale if not given
            if (method_exists($translator, 'getLocale')) {
                $currentLocale = $translator->getLocale();
            }
        }

        $urlPlugin = $this->getView()->plugin('url');

        // build options
        $options = '';
        foreach ($locales as $localeKey => $locale) {
            $optClass = $optionClass;
            if ($locale === $currentLocale) {
                $optClass .= ' ' . $optionActiveClass;
            }
            $optClass  .= ' ' . $optionLanguageClassPrefix . $locale;
            $parameters = [];
            if ($this->routeMatch) {
                $parameters = $this->routeMatch->getParams();
            }
            $parameters['locale'] = $locale;
            $routeName            = null;
            if (! $this->routeMatch) {
                $routeName = $this->languageOptions->getHomeRoute();
            }
            $url      = $urlPlugin($routeName, $parameters);
            $link     = sprintf(static::$navbarOptionLinkFormat, $url, $optionLinkClass, $localeKey, $locale);
            $options .= sprintf(static::$navbarOptionFormat, $optClass, $link);
        }

        $captionKey    = array_search($currentLocale, $locales);
        $captionLocale = $currentLocale;
        if ($captionKey === false && ! empty($currentLocale)) {
            $captionKey = substr($currentLocale, 0, strpos($currentLocale, '_'));
        } elseif ($captionKey === false) {
            $captionKey    = array_keys($locales)[0];
            $captionLocale = $locales[$captionKey];
        }

        // return container with options
        return sprintf(
            static::$navbarBoxFormat,
            $boxClass,
            sprintf(static::$navbarCaptionLinkFormat, $captionLinkClass, $captionKey, $captionLocale)
            . sprintf(static::$navbarOptionsFormat, $optionsClass, $options)
        );
    }

    protected function renderPartial(
        TranslatorInterface $translator,
        array $locales,
        ?string $currentLocale = null,
        array $config = []
    ): string {
        if (! isset($config['partial'])) {
            throw new Exception\InvalidArgumentException('Partial is not set');
        }

        $options = [
            'translator'    => $translator,
            'locales'       => $locales,
            'currentLocale' => $currentLocale,
            'config'        => $config,
        ];

        return $this->getView()->render($config['partial'], $options);
    }

    public function getRouteMatch(): ?RouteMatch
    {
        return $this->routeMatch;
    }

    public function getLanguageOptions(): LanguageRouteOptions
    {
        return $this->languageOptions;
    }
}
