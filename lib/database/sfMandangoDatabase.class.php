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

/**
 * sfMandangoDatabase
 *
 * @package sfMandangoPlugin
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
class sfMandangoDatabase extends sfDatabase
{
  protected $MandangoConnection;

  /**
   * @see sfDatabase
   */
  public function initialize($parameters = array())
  {
    parent::initialize($parameters);

    // server
    if (!$this->hasParameter('server'))
    {
      throw new RuntimeException(sprintf('Connection "%s" without server".', $this->getParameter('name')));
    }
    $server = $this->getParameter('server');

    // database
    if (!$this->hasParameter('database'))
    {
      throw new RuntimeException(sprintf('Connection "%s" without database.', $this->getParameter('name')));
    }
    $database = $this->getParameter('database');

    // options
    $options = array();
    if ($this->hasParameter('persist'))
    {
      $options['persist'] = $this->getParameter('persist');
    }

    $this->MandangoConnection = new Mandango\Connection($server, $database, $options);
  }

  /**
   * Returns the Mandango connection.
   *
   * @return MandangoConnection The Mandango connection.
   */
  public function getMandangoConnection()
  {
    return $this->MandangoConnection;
  }

  /**
   * @see sfDatabase
   */
  public function connect()
  {
  }

  /**
   * @see sfDatabase
   */
  public function shutdown()
  {
  }
}
