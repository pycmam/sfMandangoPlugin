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
 * sfMandangoPluginConfiguration.
 *
 * @package sfMandangoPlugin
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
class sfMandangoPluginConfiguration extends sfPluginConfiguration
{
  protected $logs = array();

  /**
   * @see sfPluginConfiguration
   */
  public function initialize()
  {
    require_once(dirname(__FILE__).'/../lib/vendor/mandango/vendor/symfony/src/Symfony/Component/ClassLoader/UniversalClassLoader.php');

    $loader = new Symfony\Component\ClassLoader\UniversalClassLoader();
    $loader->registerNamespaces(array(
      //'Mandango\Behavior' => sfConfig::get('sf_mandango_behaviors_lib_dir', dirname(__FILE__).'/../lib/vendor/mandango-behaviors/lib'),
      'Mandango' => sfConfig::get('sf_mandango_lib_dir', dirname(__FILE__) .'/../lib/vendor/mandango/src'),
      'Model' => sfConfig::get('sf_lib_dir') .'/model/mandango',
    ));
    $loader->register();

    $this->dispatcher->connect('context.load_factories', array($this, 'listenToContextLoadFactories'));
    $this->dispatcher->connect('component.method_not_found', array($this, 'listenToComponentMethodNotFound'));
  }

  /**
   * Listen to context.load_factories event.
   *
   * Initialize the Mandango.
   *
   * @param sfEvent $event The event object.
   *
   * @return void
   */
  public function listenToContextLoadFactories(sfEvent $event)
  {
    $context = $event->getSubject();

    // log
    $loggerCallable = sfConfig::get('sf_logging_enabled') ? array($this, 'log') : null;

    $metadata = new Model\Mapping\MandangoMetadata();
    $cache = new Mandango\Cache\ArrayCache();

    $mandango = new Mandango\Mandango($metadata, $cache, $loggerCallable);

    // databases
    $databaseManager = $context->getDatabaseManager();
    foreach ($databaseManager->getNames() as $name)
    {
      $database = $databaseManager->getDatabase($name);
      if ($database instanceof sfMandangoDatabase)
      {
        $mandango->setConnection($name, $database->getMandangoConnection());
        if ($database->hasParameter('default') && $database->getParameter('default'))
        {
            $mandango->setDefaultConnectionName($name);
        }
      }
    }

    if (sfConfig::get('sf_logging_enabled') && sfConfig::get('sf_web_debug'))
    {
      $this->dispatcher->connect('debug.web.load_panels', array($this, 'listenToDebugWebLoadPanels'));
    }

    // context
    $context->set('mandango', $mandango);

    // container
    //Mandango\Container::setDefaultName('default');
    //Mandango\Container::set('default', $mandango);
  }

  /**
   * Listen to component.method_not_fount event.
   *
   * Returns the Mandango in actions and components: $this->getMandango()
   *
   * @param sfEvent $event The event.
   *
   * @return bool If it returns the Mandango.
   */
  public function listenToComponentMethodNotFound(sfEvent $event)
  {
    if ('getMandango' == $event['method'])
    {
      $event->setReturnValue($event->getSubject()->getContext()->get('mandango'));

      return true;
    }

    return false;
  }

  /**
   * Returns the logs.
   *
   * @return array The logs.
   */
  public function getLogs()
  {
    return $this->logs;
  }

  /**
   * Save a Mandango log.
   *
   * @param array $log The log.
   *
   * @return void
   */
  public function log(array $log)
  {
    $this->dispatcher->notify(new sfEvent('sfMandango', 'application.log', array('sfMandango')));

    $this->logs[] = $log;
  }

  /**
   * Listen to debug.web_load_panels event.
   *
   * @param sfEvent $event The event.
   *
   * @return void
   */
  public function listenToDebugWebLoadPanels(sfEvent $event)
  {
    $event->getSubject()->setPanel('mandango', new sfMandangoWebDebugPanel($event->getSubject()));
  }
}
