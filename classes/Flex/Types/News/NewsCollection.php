<?php

declare(strict_types=1);

/**
 * @package    Grav\Common\Flex
 *
 * @copyright  Copyright (c) 2023 Sebastian Laube
 * @license    MIT License; see LICENSE file for details.
 */

namespace Grav\Plugin\News\Flex\Types\News;

use DateTime;
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
    // custom filter to test for multiple values on the same key (if field is type string)
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

    // custom filter for multiple values of the same key (if field is type array)
    public function inArray($key, array $values): NewsCollection
    {
        $mappedCollection = $this->map(function($obj) use ($values, $key) {
            foreach ($values as $value) {
                if (in_array($value, $obj->getProperty($key, []))) {
                    return $obj;
                }
            }
            return false; // if element doesnt match what we want, make it false
        });


        $array_with_elements_remove = array_filter($mappedCollection->getElements(), function($e){
            return $e; //remove all the unwanted elements that are false
        });

        return $this->createFrom(array_values($array_with_elements_remove));
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

    public function archive( $date ): NewsCollection
    {
        $expr = Criteria::expr();
        $criteria = Criteria::create();

        if ( preg_match( '@\d{4}-(1[0-2]|0[1-9])$@', $date, $matches ) )
        {
            $bom = new \DateTimeImmutable( $date );
            $eom = $bom->modify('next month');

            $criteria
                ->where( $expr->gte( 'date', $bom->format("Y-m-d G:i") ) )
                ->andWhere( $expr->lte( 'date', $eom->format("Y-m-d G:i") ) )
                ->orderBy( [ 'date' => Criteria::DESC ] );

            return $this->matching( $criteria );
        }

        // force empty as last resort
        $criteria
            ->where( $expr->eq( 'published', false ) );
        return $this->matching( $criteria );
    }
}
