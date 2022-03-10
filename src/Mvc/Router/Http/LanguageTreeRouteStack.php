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
use Laminas\Mvc\I18n\Router\TranslatorAwareTreeRouteStack;
use Laminas\Router\Http\RouteMatch;
use Laminas\Stdlib\RequestInterface;

use function array_keys;
use function array_search;
use function count;
use function explode;
use function in_array;
use function is_callable;
use function ltrim;
use function method_exists;
use function strlen;
use function substr;

/**
 * Manages multilanguage routes by adding a language key to the baseUrl
 */
class LanguageTreeRouteStack extends TranslatorAwareTreeRouteStack
{
    protected ?LanguageRouteOptions $languageOptions                 = null;
    protected ?AuthenticationServiceInterface $authenticationService = null;
    protected ?string $lastMatchedLocale                             = null;

    /**
     * assemble(): defined by \Laminas\Mvc\Router\RouteInterface interface.
     *
     * @todo ununcomment return-type when dopping PHP 7.4 support
     * @todo Is there any way to ensure that this is called only for top level?
     * @return mixed
     */
    public function assemble(array $params = [], array $options = []) /*: mixed*/
    {
        $translator = null;
        if (isset($options['translator'])) {
            $translator = $options['translator'];
        } elseif ($this->hasTranslator() && $this->isTranslatorEnabled()) {
            $translator = $this->getTranslator();
        }

        $languages = $this->getRouteLanguages();

        $oldBase = $this->getBaseUrl(); // save old baseUrl
        // only add language key when more than one language is supported
        if (count($languages) > 1) {
            if (isset($params['locale'])) {
                // use parameter if provided
                $locale = $params['locale'];
                // get key for locale
                $key = array_search($locale, $languages);
            } elseif (is_callable([$translator, 'getLocale'])) {
                // use getLocale if possible
                $locale = $translator->getLocale();
                // get key for locale
                $key = array_search($locale, $languages);
            }

            if (! empty($key)) {
                // add key to baseUrl
                $this->setBaseUrl($oldBase . '/' . $key);
            }
        }

        $res = parent::assemble($params, $options);
        // restore baseUrl
        $this->setBaseUrl($oldBase);
        return $res;
    }

    /**
     * match(): defined by \Laminas\Mvc\Router\RouteInterface
     *
     * @param integer|null $pathOffset
     * @psalm-suppress UndefinedDocblockClass
     */
    public function match(RequestInterface $request, $pathOffset = null, array $options = []): ?RouteMatch
    {
        // Languages should only be added on top level. Since there seems to be
        // no way to ensure this stack is only at top level, the language has
        // to be checked every time this method is called.
        /*
        if ($pathOffset !== null) {
            return parent::match($request, $pathOffset, $options);
        }
        */

        if (! method_exists($request, 'getUri')) {
            return null;
        }

        if ($this->baseUrl === null && method_exists($request, 'getBaseUrl')) {
            $this->setBaseUrl($request->getBaseUrl());
        }

        /** @var TranslatorInterface $translator */
        $translator = null;
        if (isset($options['translator'])) {
            $translator = $options['translator'];
        } elseif ($this->hasTranslator() && $this->isTranslatorEnabled()) {
            $translator = $this->getTranslator();
        }

        $languages    = $this->getRouteLanguages();
        $languageKeys = array_keys($languages);

        // save old baseUrl
        $oldBase = $this->baseUrl;
        $locale  = null;

        // extract /-separated path parts
        $uri           = $request->getUri();
        $baseUrlLength = strlen($this->baseUrl);
        $path          = ltrim(substr($uri->getPath(), $baseUrlLength), '/');
        $pathParts     = explode('/', $path);

        // check if language was provided in first part
        if (count($languages) > 1 && in_array($pathParts[0], $languageKeys)) {
            // if language was provided, save the locale and adjust the baseUrl
            $locale = $languages[$pathParts[0]];
            $this->setBaseUrl($oldBase . '/' . $pathParts[0]);
        } elseif (! empty($this->getAuthenticationService()) && $this->getAuthenticationService()->hasIdentity()) {
            // try to get user language if no language was provided by url
            $user = $this->getAuthenticationService()->getIdentity();
            if ($user instanceof LocaleUserInterface) {
                $userLocale = $user->getLocale();
                if (in_array($userLocale, $languages)) {
                    $locale = $userLocale;
                }
            }
        }

        if (empty($locale) && ! empty($translator) && method_exists($translator, 'getLocale')) {
            // If still no language found, check the translator locale
            $locale = $translator->getLocale();
        }

        // set the last matched locale
        $this->lastMatchedLocale = $locale;

        /** @var RouteMatch|null $res */
        $res = parent::match($request, $pathOffset, $options);
        $this->setBaseUrl($oldBase);
        if ($res instanceof RouteMatch && ! empty($locale)) {
            $res->setParam('locale', $locale);
        }
        return $res;
    }

    public function getLanguageOptions(): ?LanguageRouteOptions
    {
        return $this->languageOptions;
    }

    public function setLanguageOptions(?LanguageRouteOptions $languageOptions): void
    {
        $this->languageOptions = $languageOptions;
    }

    public function getAuthenticationService(): ?AuthenticationServiceInterface
    {
        return $this->authenticationService;
    }

    public function setAuthenticationService(?AuthenticationServiceInterface $authenticationService)
    {
        $this->authenticationService = $authenticationService;
    }

    /**
     * Returns the locale that was found in the last matched URL. It is also
     * stored if no RouteMatch instance is provided (e.g. 404 error)
     */
    public function getLastMatchedLocale(): ?string
    {
        return $this->lastMatchedLocale;
    }

    protected function getRouteLanguages(): array
    {
        if (! empty($this->getLanguageOptions())) {
            return $this->getLanguageOptions()->getLanguages();
        }
        return [];
    }
}
