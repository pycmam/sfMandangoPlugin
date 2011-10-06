<?php

/*
 * Copyright 2010 Pablo Díez Pascual <pablodip@gmail.com>
 *
 * This file is part of sfMandangoPlugin.
 *
 * sfMandangoPlugin is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * sfMandangoPlugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with sfMandangoPlugin. If not, see <http://www.gnu.org/licenses/>.
 */

use Mandango\Query;

/**
 * sfMandangoPager.
 *
 * Based in sf|Propel/Doctrine|Pager.
 *
 * @package sfMandangoPlugin
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
class sfMandangoPager extends sfPager
{
  protected $query;

  /**
   * Sets the query.
   *
   * @param Mandango\Query $query The query.
   */
  public function setQuery(Query $query)
  {
    $this->query = $query;
  }

  /**
   * Returns the query.
   *
   * @return Mandango\Query The query.
   */
  public function getQuery()
  {
    if (!$this->query)
    {
        $class = $this->getClass();
        $this->query = $class::query();
    }

    return $this->query;
  }

  /**
   * @see sfPager
   */
  public function init()
  {
    $this->resetIterator();

    $query = $this->getQuery();

    $count = $query->count();
    $this->setNbResults($count);

    if (0 == $this->getPage() || 0 == $this->getMaxPerPage() || 0 == $this->getNbResults())
    {
      $this->setLastPage(0);
    }
    else
    {
      $offset = ($this->getPage() - 1) * $this->getMaxPerPage();

      $this->setLastPage(ceil($this->getNbResults() / $this->getMaxPerPage()));

      $query->limit($this->getMaxPerPage())->skip($offset);
    }
  }

  /**
   * @see sfPager
   */
  public function getResults()
  {
    return $this->getQuery()->all();
  }

  /**
   * @see sfPager
   */
  public function retrieveObject($offset)
  {
    return $this->getQuery()->skip($offset - 1)->one();
  }
}
