<?php

namespace Drupal\sime_ajustes\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class RouteSubscriber.
 *
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    foreach ($collection as $name => $route) {
      $route->setRequirement('_sime_ajustes_access', 'TRUE');

      if ($name == 'node.add') {
        $defaults = $route->getDefaults();
        $defaults['_title_callback'] = '\Drupal\sime_ajustes\Controller\NodeController::addPageTitle';
        $route->setDefaults($defaults);
      }
    }
  }
}
