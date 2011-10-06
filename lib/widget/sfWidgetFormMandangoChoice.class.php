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
 * sfWidgetFormMandangoChoice.
 *
 * Based on sfWidgetFormDoctrineChoice.
 *
 * @package sfMandangoPlugin
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
class sfWidgetFormMandangoChoice extends sfWidgetFormChoice
{
  /**
   * @see sfWidget
   */
  public function __construct($options = array(), $attributes = array())
  {
    $options['choices'] = array();

    parent::__construct($options, $attributes);
  }

  /**
   * Options:
   *
   *   * model:        the model (required)
   *   * add_empty:    if add an empty option (false by default)
   *   * method:       the method to show the document (__toString by default)
   *   * find_options: the options for the find (empty array by default)
   *
   * @see sfWidgetFormSelect
   */
  protected function configure($options = array(), $attributes = array())
  {
    $this->addRequiredOption('model');
    $this->addOption('add_empty', false);
    $this->addOption('method', '__toString');
    $this->addOption('find_options', array());

    parent::configure($options, $attributes);
  }

  /**
   * Returns the choices associated to the model.
   *
   * @return array An array of choices
   */
  public function getChoices()
  {
    $choices = array();
    if (false !== $this->getOption('add_empty'))
    {
      $choices[''] = true === $this->getOption('add_empty') ? '' : $this->getOption('add_empty');
    }

    $method  = $this->getOption('method');
    foreach ((array) sfContext::getInstance()->get('Mandango')
      ->getRepository($this->getOption('model'))
      ->find($this->getOption('find_options'))
    as $document)
    {
      $choices[(string) $document->getId()] = $document->$method();
    }

    return $choices;
  }
}
