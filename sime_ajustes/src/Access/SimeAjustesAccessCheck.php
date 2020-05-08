<?php

namespace Drupal\sime_ajustes\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\HttpFoundation\Request;

class SimeAjustesAccessCheck implements AccessInterface {
  public function access(Route $route, RouteMatchInterface $route_match, AccountInterface $account) {
    $field_nino = \Drupal::request()->query->get('field_nino');
    if ($field_nino) {
      $node = \Drupal\node\Entity\Node::load($field_nino);
      if (!$node || !$node->access()) {
        return AccessResult::forbidden('No hay permisos para ver lo mandado por field_nino');
      }
    }
    $field_cpi = \Drupal::request()->query->get('field_cpi');
    if ($field_cpi) {
      $node = \Drupal\node\Entity\Node::load($field_cpi);
      if (!$node || !$node->access()) {
        return AccessResult::forbidden('No hay permisos para ver lo mandado por field_cpi');
      }
    }
    return AccessResult::allowed();
  }
}
