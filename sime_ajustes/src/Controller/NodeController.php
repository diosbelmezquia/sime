<?php

namespace Drupal\sime_ajustes\Controller;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\node\Controller\NodeController as NodeControllerBase;
use Drupal\node\NodeTypeInterface;
/**
 * Class NodeController.
 */
class NodeController extends NodeControllerBase {
  /**
   * The _title_callback for the node.add route.
   *
   * @param \Drupal\node\NodeTypeInterface $node_type
   *   The current node.
   *
   * @return string
   *   The page title.
   */
  public function addPageTitle(NodeTypeInterface $node_type) {
    return new FormattableMarkup('Ingresar @name', ['@name' => $node_type->label()]);
  }
}
