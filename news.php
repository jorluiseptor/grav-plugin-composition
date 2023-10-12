<?php
namespace Grav\Plugin;

use Composer\Autoload\ClassLoader;
use Grav\Common\Plugin;
use Grav\Events\FlexRegisterEvent;
use Grav\Common\Uri;
use Grav\Plugin\News\Utils;
use Twig\TwigFunction;

/**
 * Class NewsPlugin
 * @package Grav\Plugin
 */
class NewsPlugin extends Plugin
{
    public $features = [
        'blueprints' => 0,
    ];

    /**
     * @return array
     *
     * The getSubscribedEvents() gives the core a list of events
     *     that the plugin wants to listen to. The key of each
     *     array section is the event that the plugin listens to
     *     and the value (in the form of an array) contains the
     *     callable (or function) as well as the priority. The
     *     higher the number the higher the priority.
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onPluginsInitialized' => [
                // Uncomment following line when plugin requires Grav < 1.7
                // ['autoload', 100000],
                ['onPluginsInitialized', 0]
            ],
            FlexRegisterEvent::class => [
                ['onRegisterFlex', 0]
            ],
            'onTwigTemplatePaths'   => ['onTwigTemplatePaths', 1],
            'onTwigExtensions'      => ['onTwigExtensions', 0],
            'onFlexAfterSave'       => ['onFlexAfterSave', 0],
            'onPagesInitialized'    => ['onPagesInitialized', 0],
        ];
    }

    /**
     * Composer autoload
     *
     * @return ClassLoader
     */
    public function autoload(): ClassLoader
    {
        return require __DIR__ . '/vendor/autoload.php';
    }

    /**
     * Initialize the plugin
     */
    public function onPluginsInitialized(): void
    {
        // Don't proceed if we are in the admin plugin
        if ($this->isAdmin()) {
            return;
        }

        // Enable the main events we are interested in
        $this->enable([
            // Put your main events here
        ]);
    }

    public function onRegisterFlex($event): void
    {
        $flex = $event->flex;

        $flex->addDirectoryType(
            'news',
            'blueprints://flex-objects/news.yaml'
        );

    }

    /**
     * Add current directory to twig lookup paths.
     */
    public function onTwigTemplatePaths() : void
    {
        $this->grav['twig']->twig_paths[] = __DIR__ . '/templates';

        if ($this->isAdmin())
        {
            $this->grav['twig']->twig_paths[] = __DIR__ . '/admin/templates';
        }
    }


    public function onTwigExtensions()
    {
        $this->grav['twig']->twig()->addFunction(
            new TwigFunction('news_pagination', [new Utils, 'pagination'])
            // new \Twig_SimpleFunction('news_pager', [new Utils, 'pagination'])
        );
        $this->grav['twig']->twig()->addFunction(
            new TwigFunction('news_postnav', [new Utils, 'post_nav'])
        );
    }

    /**
     * this fixes a bug of when you edit the storage key (you get a 404) because the page moved
     */
    public function onFlexAfterSave($event)
    {
        $obj = $event['object'];
        // only handle our type
        if( $obj->getType() !== 'news') {
            return;
        }

        $uri = $this->grav['uri'];

        if ($obj->getKey() === $obj->getStorageKey()) {
            return;
        }

        $paths = $uri->paths();
        if (isset($paths[2])) {
            $paths[2] = $obj->getStorageKey();
            $this->grav->redirect(implode('/', $paths), 302);
        }
    }

    /**
     * hook into page routing to place our detail pages
     */
    public function onPagesInitialized($event)
    {
        $current = Uri::getCurrentRoute();
        $route = $current->getRoute();
        $this->simpleRouting($route);
    }

    /**
     * create a page on the fly by using a vessel page
     */
    protected function addPage(string $route, ?string $path, $template): void
    {
        if ( $path )
        {
            // check if this post is public
            $object = $this->grav['flex']->getObject( $path, 'news' );
            if ( !$object || $object['published'] !== true )
            {
                return;
            }

            $pages = $this->grav['pages'];

            // is the template a string (path)
            if ( is_string($template) )
            {
                $page = $pages->find($template);
            }
            // or a page object?
            elseif (
                is_object($template) &&
                get_class( $template ) == 'Grav\Common\Page\Page'
            )
            {
                $page = $template;
            }
            // else we do nothing
            else
            {
                return;
            }

            /** @var Pages $pages */
            // $pages = $this->grav['pages'];
            // $page = $pages->find($template);
            if ($page) {
                $page->id($page->modified() . md5($route));
                $page->slug(basename($route));
                $page->folder(basename($route));
                $page->route($route);
                $page->rawRoute($route);
                $page->menu( $object['title'] );
                $page->title( $object['title'] );
                $page->modifyHeader( 'title', $object['title'] );
                $page->modifyHeader( 'object', $path ); // this here, i dynamically set that header.object value
                $pages->addPage($page, $route);
            }
        }
    }

    private function simpleRouting(string $route)
    {
        $config = $this->config->get('plugins.news');

        $normalized = trim($route, '/');
        if (!$normalized) {
            return;
        }

        $parts = explode('/', $normalized, 2);
        $key = array_shift($parts);
        $path = array_shift($parts);

        if ( $path && '/' . $key == $config['news_page'] )
        {
            $pages = $this->grav['pages'];
            $blog = $pages->find( $config['news_page'] );
            // dd( $blog );
            // dd( $this->config->get('plugins.news') );
            // $this->addPage($route, $path, $config['news_page'] . '/' . $config['article_page']);
            $this->addPage($route, $path, $blog->children()->first());
        }

        /*
        switch ($key) {
            case News\Utils::EMISSIONS:
                $this->addPage($route, $path, '/emissions/emission');
                break;

            case News\Utils::NEWS:
                $this->addPage($route, $path, '/news/article');
                break;
        }
        */
    }
}
