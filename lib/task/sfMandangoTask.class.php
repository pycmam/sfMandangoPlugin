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

use Mandango\Container;
use Mandango\Mandango;

/**
 * Base task for Mandango tasks.
 *
 * @package sfMandangoPlugin
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
abstract class sfMandangoTask extends sfBaseTask
{
  protected $mandango;

  protected $repositories;

  /**
   * Returns the Mandango.
   *
   * @return Mandango The Mandango.
   */
  protected function getMandango()
  {
    return sfContext::getInstance()->getMandango();
  }

  /**
   * Returns the repositories of the project.
   *
   * @return array The repositories.
   */
  protected function getRepositories()
  {
    $mandango = $this->getMandango();

    if (null === $this->repositories)
    {
      $this->repositories = array();
      foreach (sfFinder::type('file')->name('*Repository.php')->prune('Base')->in(sfConfig::get('sf_lib_dir').'/model/mandango') as $file)
      {
        $this->repositories[] = $mandango->getRepository(str_replace('Repository.php', '', basename($file)));
      }
    }

    return $this->repositories;
  }
}
