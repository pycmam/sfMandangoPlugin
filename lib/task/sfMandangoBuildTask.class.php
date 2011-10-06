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
 * Global task for run Mandango builders.
 *
 * @package sfMandangoPlugin
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
class sfMandangoBuildTask extends sfMandangoTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application', true),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('and-load', null, sfCommandOption::PARAMETER_OPTIONAL | sfCommandOption::IS_ARRAY, 'Load fixture data'),
    ));

    $this->namespace = 'mandango';
    $this->name = 'build';
    $this->briefDescription = 'Build';

    $this->detailedDescription = <<<EOF
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $this->logSection('mandango', 'generating classes');

    $mondator = new Mandango\Mondator\Mondator();
    $mondator->setConfigClasses($this->prepareConfigClasses());
    $mondator->setExtensions(array(
      new Mandango\Extension\Core(array(
        'metadata_factory_class' => sfConfig::get('sf_mandango_metadata_factory_class', 'Model\Mapping\MandangoMetadata'),
        'metadata_factory_output' => sfConfig::get('sf_mandango_metadata_factory_output', sfConfig::get('sf_lib_dir').'/model/mandango/mapping'),
        'default_output' => sfConfig::get('sf_lib_dir').'/model/mandango',
      )),
      new sfMandangoExtensionPluginClasses(),
      new Mandango\Extension\DocumentArrayAccess(),
      new Mandango\Extension\DocumentPropertyOverloading(),
      new sfMandangoExtensionForms(array(
        'output' => sfConfig::get('sf_lib_dir').'/form/mandango',
      )),
    ));
    $mondator->process();

    // BaseFormMandango
    if (!file_exists($file = sfConfig::get('sf_lib_dir').'/form/mandango/BaseFormMandango.class.php'))
    {
      if (!file_exists(dirname($file))) {
        mkdir(dirname($file));
      }

      file_put_contents($file, <<<EOF
<?php

/**
 * Mandango Base Class.
 */
abstract class BaseFormMandango extends sfMandangoForm
{
  public function setup()
  {
  }
}
EOF
      );
    }

    // data-load
    if ($options['and-load'])
    {
      $this->runTask('mandango:data-load');
    }
  }

  protected function prepareConfigClasses()
  {
    $configClasses = array();

    $finder = sfFinder::type('file')->name('*.yml')->sort_by_name()->follow_link();

    // plugins
    foreach ($this->configuration->getPlugins() as $pluginName)
    {
      $plugin = $this->configuration->getPluginConfiguration($pluginName);

      foreach ($finder->in($plugin->getRootDir().'/config/mandango') as $file)
      {
        foreach (sfYaml::load($file) as $className => $configClass)
        {
          if (array_key_exists($className, $configClasses))
          {
            $configClasses[$className] = sfToolkit::arrayDeepMerge($configClasses[$className], $configClass);
          }
          else
          {
            $configClasses[$className] = $configClass;
          }

          if (!array_key_exists('plugin_name', $configClasses[$className]))
          {
            $configClasses[$className]['plugin_name'] = $pluginName;
          }
          if (!array_key_exists('plugin_dir', $configClasses[$className]))
          {
            $configClasses[$className]['plugin_dir'] = $plugin->getRootDir();
          }
        }
      }
    }

    // project
    foreach ($finder->in(sfConfig::get('sf_config_dir').'/mandango') as $file)
    {
      $configClasses = sfToolkit::arrayDeepMerge($configClasses, sfYaml::load($file));
    }

    return $configClasses;
  }
}
