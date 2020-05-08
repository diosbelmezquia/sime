<?php

namespace Drupal\sime_salidas\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class DiagnosticoTiempoController extends SemestralController {

  public function view() {
    $route_name = \Drupal::routeMatch()->getRouteName();
    $cpis = \Drupal::request()->query->get('c');
    $this->cpis = array_keys(sime_ajustes_get_all_cpi());
    if (isset($cpis)) {
      $this->cpis = $cpis;
    }
    $block['form'] = $this->formBuilder()->getForm('\Drupal\sime_salidas\Form\CpiMultipleForm');
    if ($this->cpis && isset($cpis)) {
      $block['form']['field_listado_cpis']['#value'] = $this->cpis;
    }

    if (!$this->cpis) {
      drupal_set_message('No se encontraron CPIs para mostrar', 'warning');
      return $block;
    }

    $query = db_select('node__field_diagnostico', 'd');
    $query->addExpression("d.field_diagnostico_value", 'diagnostico');
    $query->addExpression("SUM(IF(d.field_diagnostico_value <> '_none', 1, 0))", 'total_evaluados');
    $query->addExpression("SUM(IF(TIMESTAMPDIFF(YEAR, fecha.field_inscripcion_fecha_value, CURDATE()) < 1, 1, 0))", 'menos_12_meses');
    $query->addExpression("SUM(IF(TIMESTAMPDIFF(YEAR, fecha.field_inscripcion_fecha_value, CURDATE()) >= 1 AND TIMESTAMPDIFF(YEAR, fecha.field_inscripcion_fecha_value, CURDATE()) <= 2, 1, 0))", 'doce_24_meses');
    $query->addExpression("SUM(IF(TIMESTAMPDIFF(YEAR, fecha.field_inscripcion_fecha_value, CURDATE()) > 2, 1, 0))", 'mas_24_meses');
    $query->leftJoin('node__field_cpi', 'cpi', 'd.bundle = cpi.bundle AND d.entity_id = cpi.entity_id AND d.revision_id = cpi.revision_id');
    $query->leftJoin('node__field_inscripto', 'ins', 'ins.entity_id = cpi.entity_id AND ins.revision_id = cpi.revision_id');
    $query->leftJoin('node__field_inscripcion_fecha', 'fecha', 'ins.bundle = fecha.bundle AND fecha.entity_id = ins.entity_id AND fecha.revision_id = ins.revision_id');
    $query->condition('ins.field_inscripto_value', 1);
    $query->condition('cpi.field_cpi_target_id', $this->cpis, 'IN');
    $query->groupBy('d.field_diagnostico_value');
    $diagnosticos = $query->execute()->fetchAllAssoc('diagnostico');

    if (!$diagnosticos) {
      drupal_set_message('No hay diagnósticos para mostrar', 'warning');
      return $block;
    }

    $d = [];
    foreach (array_keys($diagnosticos) as $diagnostico) {
      if ($diagnostico != '_none') {
        $d[$diagnostico] = [
          'total_evaluados'=> $diagnosticos[$diagnostico]->total_evaluados,
          'menos_12_meses' => $diagnosticos[$diagnostico]->menos_12_meses,
          'doce_24_meses'  => $diagnosticos[$diagnostico]->doce_24_meses,
          'mas_24_meses'   => $diagnosticos[$diagnostico]->mas_24_meses,
        ];
      }
    }

    $diagnosticos = $d;

    $cant_total_evaluados = $cant_menos_12_meses = $cant_doce_24_meses = $cant_mas_24_meses = 0;
    foreach ($diagnosticos as $diagnostico) {
      $cant_total_evaluados += $diagnostico['total_evaluados'];
      $cant_menos_12_meses += $diagnostico['menos_12_meses'];
      $cant_doce_24_meses  += $diagnostico['doce_24_meses'];
      $cant_mas_24_meses  += $diagnostico['mas_24_meses'];
    }

    $header = ['Diagnósticos' ,'Total Evaluados', '< 12 meses en CPI', '%', '12 a 24 meses en CPI', '%', '> 24 meses en CPI', '%'];

    $footer = [
      ['Total de niños', $cant_total_evaluados, $cant_menos_12_meses, '', $cant_doce_24_meses, '', $cant_mas_24_meses, ''],
    ];

    $block['nino'] = [
        '#type' => 'table',
        '#header' => $header,
        '#footer' => $footer,
        '#attributes' => [
          'class' => [
            'listado-semestral', 'diagnostico-tiempo'
            ],
        ],
      ];

    foreach (array_keys($diagnosticos) as $i => $diagnostico) {
      $block['nino'][$i]['#attributes'] = [
        'class' => ['foo','baz'],
      ];

      $block['nino'][$i]['cpi'] = [
        '#type' => 'item',
        '#markup' => $this->nombre_real[$diagnostico],
        '#wrapper_attributes' =>[
          'class' => parent::sime_ajustes_color_td($diagnostico),
          ],
      ];
      $total_evaluados = $diagnosticos[$diagnostico]['total_evaluados'];
      $block['nino'][$i]['total_evaluados'] = [
        '#type' => 'item',
        '#markup' => $total_evaluados,
      ];
      $block['nino'][$i]['menor_12_meses'] = [
        '#type' => 'item',
        '#markup' => $diagnosticos[$diagnostico]['menos_12_meses'],
      ];
      $block['nino'][$i]['porciento1'] = [
        '#type' => 'item',
        '#markup' => round($diagnosticos[$diagnostico]['menos_12_meses'] * 100 / $total_evaluados, 1) . ' %',
      ];
      $block['nino'][$i]['12_24_meses'] = [
        '#type' => 'item',
        '#markup' => $diagnosticos[$diagnostico]['doce_24_meses'],
      ];
      $block['nino'][$i]['porciento2'] = [
        '#type' => 'item',
        '#markup' => round($diagnosticos[$diagnostico]['doce_24_meses'] * 100 / $total_evaluados, 1) . ' %',
      ];
      $block['nino'][$i]['mayor_24_meses'] = [
        '#type' => 'item',
        '#markup' => $diagnosticos[$diagnostico]['mas_24_meses'],
      ];
      $block['nino'][$i]['porciento3'] = [
        '#type' => 'item',
        '#markup' => round($diagnosticos[$diagnostico]['mas_24_meses'] * 100 / $total_evaluados, 1) . ' %',
      ];
    }
    return $block;
  }

  // On submit form.
  public static function sime_salidas_diagnostico_tiempo_form_submit(&$form, FormStateInterface $form_state) {
    $cpis = $form_state->getValue('field_listado_cpis');
    $route_name = \Drupal::routeMatch()->getRouteName();
    $response = new RedirectResponse(\Drupal::url($route_name, [
      'c' => $cpis,
    ]));
    $response->send();
  }

}
