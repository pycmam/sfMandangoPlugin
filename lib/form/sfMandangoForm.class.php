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

use Mandango\Document\Document;

/**
 * sfMandangoForm.
 *
 * @package sfMandangoPlugin
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
abstract class sfMandangoForm extends BaseForm
{
  protected $document;

  /**
   * Construct.
   *
   * @param Mandango\Document\Document $document   The document (optional).
   * @param array                      $options    An array of options (optional).
   * @param string                     $CSRFSecret The CSRF Secret (optional).
   *
   * @return void
   */
  public function __construct(Document $document = null, array $options = array(), $CSRFSecret = null)
  {
    $class = $this->getModelName();

    if ($document)
    {
  	  if (!$document instanceof $class)
  	  {
  	    throw new InvalidArgumentException(sprintf('The document is not of the class "%s".', $class));
  	  }

  	  $this->document = $document;
    }
    else
    {
      $this->document = new $class();
    }

  	$defaults = $this->document->toArray();

  	// sfWidgetFormDate does not support DateTime object as value
  	foreach ($defaults as &$default)
  	{
  	  if ($default instanceof DateTime)
  	  {
  	    $default = $default->getTimestamp();
  	  }
  	}

  	parent::__construct($defaults, $options, $CSRFSecret);
  }

  /**
   * Returns the model name.
   *
   * @return string The model name.
   */
  abstract public function getModelName();

  /**
   * Returns the document.
   *
   * @return Mandango\Document\Document The document.
   */
  public function getDocument()
  {
    return $this->document;
  }

  /**
   * Returns if the document is new.
   *
   * @return bool Returns if the document is new.
   */
  public function isNew()
  {
    return $this->document->isNew();
  }

  /**
   * Save the document with the form values.
   *
   * @return Mandango\Document\Document The document.
   *
   * @throws LogicException If the form is not valid.
   */
  public function save()
  {
    if (!$this->isValid())
    {
      throw new LogicException('Cannot save the sfMandangoForm if it is not valid.');
    }

    $datamap = $this->getDocument()->getDocumentData();

    $this->document->fromArray(array_intersect_key($this->getValues(), $datamap['fields']));

    $this->document->save();

    return $this->getDocument();
  }
}
