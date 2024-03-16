<?php
namespace Grav\Plugin\News;

use Grav\Common\Grav;
use Grav\Common\Page\Collection;
use Grav\Common\Uri;

class Utils
{
    /**
     * This method creates a slug from the id and the name of a property.
     *
     * @param string $str
     * @param false $lower
     * @return string
     */
    public static function slug(string $str): string
    {
        if (function_exists('transliterator_transliterate')) {
            $str = transliterator_transliterate('Any-Latin; Latin-ASCII; NFD; [:Nonspacing Mark:] Remove; NFC;', $str);
        } else {
            $str = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $str);
        }

        $str = mb_strtolower($str);

        $str = preg_replace('/[-\s]+/', '-', $str);
        $str = preg_replace('/[^a-z0-9-]/i', '', $str);
        return trim($str, '-');
    }

    public function pagination( $collection = null )
    {
        if (!$collection)
        {
            return false;
        }
        $grav = Grav::instance();
        $config = $grav['config'];
        $uri = $grav['uri'];

        $news_config = $config->get( 'plugins.news' );
        $current_page = $uri->currentPage();
        $param_sep = $config->get('system.param_sep');
        $delta = $news_config['pagination_delta'];
        $limit = $news_config['news_per_page'];

        // preserve url params, e.g. search
        $url_params = $this->url_params();
        $page_base = $grav['page']->url() . $url_params . '/page' . $param_sep;

        // how many entries?
        $collection_count = count( $collection );
        // how may paginated pages? round up for fractions
        $pagination_count = ceil( $collection_count / $limit );

        // prepare output
        $output = [
            'isFirst' => ( $current_page == 1 ),
            'isLast' => ( $current_page == $pagination_count ),
            'current' => $current_page,
            'stack' => [],
        ];

        if ( !$output['isFirst'] ) {
            $output['newerUrl'] = $page_base . ( $current_page - 1 );
        }

        if ( !$output['isLast'] ) {
            $output['olderUrl'] = $page_base . ( $current_page + 1 );
        }

        // spit out the list of pages with some info
        for ($i=1; $i < $pagination_count + 1; $i++)
        {
            $output['stack'][$i] = [
                'url' => $page_base . $i,
                'number' => $i,
                'isCurrent' => ($i == $current_page ),
                'isInDelta' => ( !$delta || abs( $current_page - $i) < $delta + 1 ),
                'isDeltaBorder' => ( $delta && abs( $current_page - $i) == $delta + 1 ),
            ];
        }

        // $grav['debugger']->addMessage( $output );

        return $output;
    }

    static function url_params()
    {
        $grav = Grav::instance();
        $config = $grav['config'];
        $uri = $grav['uri'];

        $url_params = explode( '/', ltrim((string) $uri->params() ?: '', '/') );
        foreach ($url_params as $key => $value) {
            if (strpos($value, 'page' . $config->get('system.param_sep')) !== false) {
                unset($url_params[$key]);
            }
        }

        $url_params = '/'.implode('/', $url_params);

        // check for empty params
        if ($url_params === '/') {
            $url_params = '';
        }

        return $url_params;
    }

    public function post_nav( $collection = null, $key = null )
    {
        if (!$collection || !$key)
        {
            return false;
        }

        $grav = Grav::instance();
        $uri = $grav['uri'];

        $base_path = '/' . $uri->paths()[0];

        $output = [
            'newerPost' => null,
            'olderPost' => null,
        ];
        $current_post = null;

        foreach ( $collection as $post )
        {
            $older_post = $current_post;
            $current_post = $post;

            // is this post the page we see?
            if ( $older_post && $current_post->getKey() == $key )
            {
                // remember the post before
                $output['newerPost'] = [
                    'url' => $base_path . '/' . $older_post->getKey(),
                    'title' => $older_post->getProperty( 'title' ),
                ];
            }
            // was the last post the post we see?
            if ( $older_post && $older_post->getKey() == $key )
            {
                // remember this as the older post and stop the loop
                $output['olderPost'] = [
                    'url' => $base_path . '/' . $current_post->getKey(),
                    'title' => $current_post->getProperty( 'title' ),
                ];
                break;
            }
        }

        // $grav['debugger']->addMessage( $output );
        return $output;
    }

    // provide list of tags for tags input (selectize)
    public static function getTags()
    {
        $flex = Grav::instance()['flex'] ?? null;
        $collection = $flex ? $flex->getCollection('news') : null;
        if ( $collection )
        {
            $raw_tags = $collection->getTagsIndex();
            $tags = [];
            foreach ( $raw_tags as $tag )
            {
                // selectize option format
                $tags[] = [ 'text' => $tag, 'value' => $tag ];
            }
            return $tags;
        }

        return null;
    }
}