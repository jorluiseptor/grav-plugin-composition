<?php

declare(strict_types=1);

/**
 * @package    Grav\Common\Flex
 *
 * @copyright  Copyright (c) 2023 Sebastian Laube
 * @license    MIT License; see LICENSE file for details.
 */

namespace Grav\Plugin\News\Flex\Types\News;

use Grav\Common\Flex\Types\Generic\GenericCollection;
use Doctrine\Common\Collections\Criteria;

/**
 * Class NewsCollection
 * @package Grav\Common\Flex\Generic
 *
 * @extends FlexCollection<string,GenericObject>
 */
class NewsCollection extends GenericCollection
{
    // custom filter for multiple values of the same key
    public function filterByArray( $key, $array ): NewsCollection
    {
        $expr = Criteria::expr();
        $criteria = Criteria::create();
        // https://www.doctrine-project.org/projects/doctrine-collections/en/1.6/expressions.html#orwhere

        foreach ( $array as $item ) {
            $criteria->orWhere( $expr->eq( $key, $item ) );
        }

        return $this->matching( $criteria );
    }

    // custom filter to get news intended to be public
    public function public(): NewsCollection
    {
        $expr = Criteria::expr();
        $criteria = Criteria::create();
        // https://www.doctrine-project.org/projects/doctrine-collections/en/1.6/expressions.html
        // https://www.doctrine-project.org/projects/doctrine-collections/en/stable/expression-builder.html

        $date = new \DateTime( 'now' );

        $criteria
            ->where( $expr->eq( 'published', true ) )
            // pub date is set and not in the future
            ->andWhere(
                $expr->orX( // one match, but both neither true or false
                    $expr->isNull( 'publish_date' ),
                    $expr->lte( 'publish_date', $date->format("Y-m-d G:i") )
                )
            )
            // un_pub date is set and not in the past
            ->andWhere(
                $expr->orX( // one match, but both neither true or false
                    $expr->isNull( 'unpublish_date' ),
                    $expr->gte( 'unpublish_date', $date->format("Y-m-d G:i") )
                )
            )
            // we really should not show posts with future date
            ->andWhere( $expr->lte( 'date', $date->format("Y-m-d G:i") ) )
            ->orderBy( [ 'date' => Criteria::DESC ] );

        return $this->matching( $criteria );
    }
}
