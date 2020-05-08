<?php

namespace Drupal\sime_salidas\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\sime_salidas\UtilityTrait;
use Drupal\node\Entity\Node;

class PorcientoAusentismoController extends ControllerBase {
  use UtilityTrait;

  protected $cpis = NULL;
  protected $motivos = ['ausente', 'viaje', 'salud'];

  public function view() {

    $route_name = \Drupal::routeMatch()->getRouteName();
    $cpis = \Drupal::request()->query->get('c');
    $all_cpis = sime_ajustes_get_all_cpi();
    if($all_cpis) {
      $this->cpis = array_keys(sime_ajustes_get_all_cpi());
    }
    if ($cpis) {
      $this->cpis = $cpis;
    }
    $output['form'] = $this->formBuilder()->getForm('\Drupal\sime_salidas\Form\CpiMultipleForm');
    if ($this->cpis && $cpis) {
      $output['form']['field_listado_cpis']['#value'] = $this->cpis;
    }
    if (!$this->cpis) {
      drupal_set_message('No se encontraron CPIs para mostrar', 'warning');
      return $output;
    }
    // Obtener total de ninos inscriptos por CPI.
    $totales_ninos = sime_ajustes_total_ninos_cpi();
    $total_ausentes = $clases = $ausentes_cpis = $listado_cpis = [];

    $total = 0;
    //Obtengo el total de nino de los CPIs seleccionados.
    foreach ($this->cpis as $cpi) {
      if (isset($totales_ninos[$cpi])) {
        $total += $totales_ninos[$cpi]->total;
      }
    }

    if (empty($totales_ninos) || $total === 0) {
      drupal_set_message('No se encontraron niños para mostrar', 'warning');
      return $output;
    }

    //Obtener total de ninos ausentes por más de 10 dias seguidos.
    $query = db_select('node__field_cpi', 'cpi');
    $query->addField('cpi', 'field_cpi_target_id', 'cpi');
    $query->addField('mo', 'field_motivo_value', 'motivo');
    $query->addExpression("COUNT(*)", 'cantidad');
    $query->condition('cpi.bundle', 'nino');
    $query->condition('au.field_ausente_value', '1');
    $query->condition('cpi.field_cpi_target_id', $this->cpis, 'IN');
    $query->condition('ins.field_inscripto_value', 1);
    $query->leftJoin('node__field_ausente', 'au', 'au.entity_id = cpi.entity_id AND au.revision_id = cpi.revision_id');
    $query->leftJoin('node__field_motivo', 'mo', 'mo.entity_id = cpi.entity_id AND mo.revision_id = cpi.revision_id');
    $query->leftJoin('node__field_inscripto', 'ins', 'ins.entity_id = cpi.entity_id AND ins.revision_id = cpi.revision_id');
    $query->groupBy('cpi.field_cpi_target_id');
    $query->groupBy('mo.field_motivo_value');
    $ausentes = $query->execute()->fetchAll();
    $motivos = array_unique($query->execute()->fetchCol(1));

    //Obtener el total de ausentes por motivo.
    if (!empty($motivos)) {
      foreach ($motivos as $motivo) {
        $total_ausentes[$motivo]['cantidad'] = 0;
        $total_ausentes[$motivo]['porciento'] = 0;
        foreach ($ausentes as $ausente) {
          if ($ausente->motivo == $motivo) {
            $total_ausentes[$motivo]['cantidad'] += $ausente->cantidad;
            $total_ausentes[$motivo]['porciento'] = round($total_ausentes[$motivo]['cantidad'] * 100 / $total,1);
          }
        }
        foreach ($ausentes as $ausente) {
          if ($ausente->motivo == $motivo) {
            $listado_cpis[] = $ausente->cpi;
            //Obtener total de ausentes por CPI-Motivos.(Las 3 ultimas tortas).
            $ausentes_cpis[$motivo][] = [
              'cpi' => Node::load($ausente->cpi)->label() . ' ' . round($ausente->cantidad * 100 / $total_ausentes[$motivo]['cantidad'], 1) . "%",
              'cant' => $ausente->cantidad,
              'cpi_id' => $ausente->cpi,
            ];
          }
        }
      }
      $clases[] = 'cant-ausentes';
    }
    else {
      drupal_set_message('No se encontraron ausentes para mostrar', 'warning');
    }

    //Calcular presentes.
    //Total de ninos menos la cantidad de ausentes.
    $total_ausentes['presentes']['cantidad'] = $total - array_sum(array_column($total_ausentes, 'cantidad'));
    $total_ausentes['presentes']['porciento'] = round($total_ausentes['presentes']['cantidad'] * 100 / $total,1);
    if ($total_ausentes['presentes']['cantidad'] <= 0) {
      unset($total_ausentes['presentes']);
    }

    //Verificar que hayan ausentes, para mostrar la torta por js. (Las 3 ultimas tortas).
    foreach ($this->motivos as $motivo) {
      if (isset($ausentes_cpis[$motivo])) {
        $clases[] = 'cant-ausentes-' .  $motivo;
      }
    }

    $output['porciento_cpis'] = [
      '#theme' => 'sime_salidas_porciento_cpis',
      '#cant_ausentes' => $clases,
      '#attached' => [
        'drupalSettings' => [
          'simeSalidas' => [
            'cant_ausentes' => $total_ausentes,
            'ausentes_cpis' => $ausentes_cpis,
            'colors' => $this->chartColors,
            'colors_indexed' => $this->sime_get_indexed_colors($listado_cpis),
          ],
        ],
      ],
    ];

    $output['#attached'] = [
      'library' => [
        'sime_salidas/sime_salidas.chartjs',
     ],
    ];
    return $output;
  }

    // On submit form.
  public static function sime_salidas_ausentismo_form_submit(&$form, FormStateInterface $form_state) {
    $cpis = $form_state->getValue('field_listado_cpis');
    $route_name = \Drupal::routeMatch()->getRouteName();
    $response = new RedirectResponse(\Drupal::url($route_name, [
      'c' => $cpis,
    ]));
    $response->send();
  }

}
