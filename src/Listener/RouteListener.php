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
use Laminas\EventManager\AbstractListenerAggregate;
use Laminas\EventManager\EventManagerInterface;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\Mvc\MvcEvent;
use Laminas\Router\RouteStackInterface;
use Laminas\Stdlib\RequestInterface;

use function method_exists;

/**
 * Injects language into translator and updates user locale
 */
class RouteListener extends AbstractListenerAggregate
{
    private LanguageRouteOptions $options;
    private RouteStackInterface $router;
    private RequestInterface $request;
    private TranslatorInterface $translator;
    private ?AuthenticationServiceInterface $authService = null;

    public function __construct(
        LanguageRouteOptions $options,
        RouteStackInterface $router,
        RequestInterface $request,
        TranslatorInterface $translator,
        ?AuthenticationServiceInterface $authService = null
    ) {
        $this->options     = $options;
        $this->router      = $router;
        $this->request     = $request;
        $this->authService = $authService;
        $this->translator  = $translator;
    }

    /**
     * @param integer $priority
     */
    public function attach(EventManagerInterface $events, $priority = 10): void
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_ROUTE, [$this, 'onRoute'], $priority);
    }

    public function onRoute(MvcEvent $e): void
    {
        $router = $this->router;
        if (! $router instanceof LanguageTreeRouteStack) {
            return;
        }

        $this->router->match($this->request);

        $locale = $this->router->getLastMatchedLocale();
        if (empty($locale)) {
            return;
        }

        if (method_exists($this->translator, 'setLocale')) {
            $this->translator->setLocale($locale);
        }

        if ($this->authService && $this->authService->hasIdentity()) {
            $user = $this->authService->getIdentity();
            if ($user instanceof LocaleUserInterface) {
                $user->setLocale($locale);
            }
        }
    }
}
