<?php

declare(strict_types=1);

/**
 * @package    Grav\Common\Flex
 *
 * @copyright  Copyright (c) 2023 Sebastian Laube
 * @license    MIT License; see LICENSE file for details.
 */

namespace Grav\Plugin\News\Flex\Types\News;

use Grav\Plugin\News\Utils;
use Grav\Common\Flex\Types\Generic\GenericObject;
use Grav\Common\File\CompiledYamlFile;
use Grav\Common\Grav;

/**
 * Class NewsObject
 * @package Grav\Common\Flex\Generic
 *
 * @extends FlexObject<string,GenericObject>
*/
class NewsObject extends GenericObject
{
    /**
     * {@inheritdoc}
     * @see FlexObjectInterface::save()
     */
    public function save()
    {
        $grav = Grav::instance();
        // is this a new entry?
        if ( ! $this->exists() )
        {
            // do we have a custom slug?
            if ( ! $this->getProperty( 'slug' ) )
            {
                $this->setSlug( $this->getProperty( 'title' ) );
            }
            $this->setProperty( 'created_at', date( 'Y-m-d G:i:s' ) );
        }

        // do we update a existing entry?
        $changes = $this->getChanges();
        if ( ! empty( $changes ) )
        {
            $this->setProperty( 'updated_at', date( 'Y-m-d G:i:s' ) );

            // we could change the slug if the title changes?
            /*
            if (isset($changes['title']))
            {
                $this->setSlug($this->getProperty('title'));
            }
            */

            // change slug if slug is changed
            if ( array_key_exists( 'slug', $changes ) )
            {
                // do not change to -nothing-!
                if ( $changes['slug'] == null )
                {
                    $this->setSlug( $this->getProperty('title') );
                }
                else {
                    $this->setSlug( $changes['slug'] );
                }
            }
        }

        // is the slug field empty (better check again)?
        if ( ! $this->getProperty( 'date' ) )
        {
            $this->setProperty( 'date', date( 'Y-m-d G:i' ) );
        }

        // do we have to handle tags?
        if ( $this->getProperty( 'tags' ) )
        {
            $this->setTagIndex( $this->getProperty('tags') ?? [] );
        }

        // is the date field empty?
        if ( ! $this->getProperty( 'date' ))
        {
            $this->setSlug( $this->getProperty( 'title' ) );
        }

        // save to date index
        $this->setDateIndex( $this->getProperty( 'date' ) ?? null );

        // create a shadow property (that is used in frontend) with correct media paths added
        $mediaPattern = '/\[(.*?)\]\((?!https?:\/\/|\/)(.*?)\)/';
        $mediaReplacement = '[$1](/' . $this->getMediaFolder() . '/$2)';
        $mediaContent = preg_replace( $mediaPattern, $mediaReplacement, $this->getProperty('rawContent') );
        $this->setProperty( 'content', $mediaContent );

        // save
        if ( $this->checkDuplicateKey( $this->getProperty( 'title' ) ) ) {
            parent::save();
        }
    }

    public function setSlug($title)
    {
        $slug = Utils::slug($title, true);
        $this->setStorageKey($slug);
        $this->setProperty('slug', $slug);
    }

    private function checkDuplicateKey($title)
    {
        $slug = Utils::slug($title, true);
        $original = $this->getOriginalData();
        $original = key_exists('slug', $original) ? $original['slug'] : null;
        $probe = $this->getFlexDirectory()->getObject($slug);

        if ($probe && $slug != $original) {
            // return false;
            $this->setSlug($this->getProperty('title') . ' ' . time() );
        }
        return true;
    }

    public function setDateIndex($date)
    {
        $path = Grav::instance()['locator']->findResource('user-data://') . '/news-dates.yaml';
        if (!file_exists($path))
        {
            touch($path);
        }
        $dateFile = CompiledYamlFile::instance($path);

        $existingTags = (array) $dateFile->content();
        $datetime = new \DateTime( $date );
        $date = $datetime->format("Y-m");
        $now = new \DateTime('now');
        if ( $now->format( 'Y-m' ) < $date )
        {
            return;
        }

        $dates = array_merge( $existingTags, [ $date ] );
        rsort( $dates );

        $dateFile->content(array_unique($dates));
        $dateFile->save();
    }

    public function setTagIndex(array $tags)
    {
        $path = Grav::instance()['locator']->findResource('user-data://') . '/news-tags.yaml';
        if (!file_exists($path))
        {
            touch($path);
        }
        $tagsFile = CompiledYamlFile::instance($path);

        $existingTags = (array) $tagsFile->content();
        $tags = array_merge( $existingTags, $tags );
        sort( $tags );

        $tagsFile->content(array_unique($tags));
        $tagsFile->save();
    }

}
