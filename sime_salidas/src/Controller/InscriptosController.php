<?php

namespace Drupal\sime_salidas\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\sime_salidas\UtilityTrait;

class InscriptosController extends ControllerBase {

  use UtilityTrait;

  /**
   * Drupal\Core\Entity\EntityTypeManager definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Constructs a new SaludController object.
   */
  public function __construct(EntityTypeManager $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  public function view() {
    $datasets = [];
    for ($i = 1; $i <= 7 ; $i++) {
      $datasets[] = [
        'label' => 'CPI ' . $i,
        'data' => [rand(1, 25), rand(1, 25), rand(1, 25), rand(1, 25), rand(1, 25), rand(1, 25)],
        'backgroundColor' => $this->chartColors[$i-1],
      ];
    }

    $output['inscriptos'] = [
      '#theme' => 'sime_salidas_inscriptos',
      '#canvas_id' => 'inscriptos',
      '#attached' => [
        'drupalSettings' => [
          'simeSalidas' => [
            'charts' => [
              'inscriptos' => [
                'labels' => ['Lactantes', 'Deambuladores', '1 año', '2 años', '3 años', '4 años'],
                'datasets' => $datasets,
              ],
            ]
          ],
        ],
      ],
    ];
    $output['#attached'] = [
      'library' => array(
        'sime_salidas/sime_salidas.chartjs',
      ),
    ];
    return $output;
  }

}
