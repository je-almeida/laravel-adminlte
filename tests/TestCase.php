<?php

use Illuminate\Auth\Access\Gate;
use Illuminate\Auth\GenericUser;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Events\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Routing\RouteCollection;
use Illuminate\Routing\UrlGenerator;
use JeAlmeida\LaravelAdminLte\AdminLte;
use JeAlmeida\LaravelAdminLte\Menu\ActiveChecker;
use JeAlmeida\LaravelAdminLte\Menu\Builder;
use JeAlmeida\LaravelAdminLte\Menu\Filters\ActiveFilter;
use JeAlmeida\LaravelAdminLte\Menu\Filters\ClassesFilter;
use JeAlmeida\LaravelAdminLte\Menu\Filters\DataFilter;
use JeAlmeida\LaravelAdminLte\Menu\Filters\GateFilter;
use JeAlmeida\LaravelAdminLte\Menu\Filters\HrefFilter;
use JeAlmeida\LaravelAdminLte\Menu\Filters\LangFilter;
use JeAlmeida\LaravelAdminLte\Menu\Filters\SearchFilter;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class TestCase extends BaseTestCase
{
    private $dispatcher;

    private $routeCollection;

    private $translator;

    protected function makeMenuBuilder($uri = 'http://example.com', GateContract $gate = null, $locale = 'en')
    {
        return new Builder([
            new GateFilter($gate ?: $this->makeGate()),
            new HrefFilter($this->makeUrlGenerator($uri)),
            new ActiveFilter($this->makeActiveChecker($uri)),
            new ClassesFilter(),
            new DataFilter(),
            new LangFilter($this->makeTranslator($locale)),
            new SearchFilter(),
        ]);
    }

    protected function makeTranslator($locale = 'en')
    {
        $translationLoader = new Illuminate\Translation\FileLoader(new Illuminate\Filesystem\Filesystem, 'resources/lang/');

        $this->translator = new Illuminate\Translation\Translator($translationLoader, $locale);
        $this->translator->addNamespace('adminlte', 'resources/lang/');

        return $this->translator;
    }

    protected function makeActiveChecker($uri = 'http://example.com', $scheme = null)
    {
        return new ActiveChecker($this->makeUrlGenerator($uri, $scheme));
    }

    private function makeRequest($uri)
    {
        return Request::createFromBase(SymfonyRequest::create($uri));
    }

    protected function makeAdminLte()
    {
        return new AdminLte($this->getFilters(), $this->getDispatcher(), $this->makeContainer());
    }

    protected function makeUrlGenerator($uri = 'http://example.com', $scheme = null)
    {
        $UrlGenerator = new UrlGenerator(
            $this->getRouteCollection(),
            $this->makeRequest($uri)
        );

        if ($scheme) {
            $UrlGenerator->forceScheme($scheme);
        }

        return $UrlGenerator;
    }

    protected function makeGate()
    {
        $userResolver = function () {
            return new GenericUser([]);
        };

        return new Gate($this->makeContainer(), $userResolver);
    }

    protected function makeContainer()
    {
        return new Illuminate\Container\Container();
    }

    protected function getDispatcher()
    {
        if (! $this->dispatcher) {
            $this->dispatcher = new Dispatcher;
        }

        return $this->dispatcher;
    }

    private function getFilters()
    {
        return [];
    }

    protected function getRouteCollection()
    {
        if (! $this->routeCollection) {
            $this->routeCollection = new RouteCollection();
        }

        return $this->routeCollection;
    }

    protected function getTranslator()
    {
        return $this->translator;
    }
}
