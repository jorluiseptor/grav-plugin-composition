<?php

declare(strict_types=1);

/**
 * @package    Grav\Common\Flex
 *
 * @copyright  Copyright (c) 2023 Sebastian Laube
 * @license    MIT License; see LICENSE file for details.
 */

namespace Grav\Plugin\News\Flex\Types\News;

use Grav\Common\Flex\Types\Generic\GenericObject;
use Grav\Plugin\News\Utils;

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
        if (!$this->exists())
        {
            $this->setSlug($this->getProperty('title'));
            $this->setProperty('created_at', time());
        }

        // do we update a existing entry?
        $changes = $this->getChanges();
        if (!empty($changes))
        {
            $this->setProperty('updated_at', time());

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
                    $this->setSlug($this->getProperty('title'));
                }
                else {
                    $this->setSlug($changes['slug']);
                }
            }
        }

        // is the slug field empty (better check again)?
        if (!$this->getProperty('date'))
        {
            $this->setProperty('date', date( 'Y-m-d G:i' ));
        }
        // dd($this);

        // is the date field empty?
        if (!$this->getProperty('date'))
        {
            $this->setSlug($this->getProperty('title'));
        }

        if ($this->checkDuplicateKey($this->getProperty('title'))) {
            // dump( 'save');
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

}
