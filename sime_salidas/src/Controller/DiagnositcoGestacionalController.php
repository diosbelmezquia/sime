<?php

namespace Drupal\sime_salidas\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class DiagnositcoGestacionalController extends SemestralController {

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

    $query = db_select('node__field_diagnostico', 'd');
    $query->addExpression("d.field_diagnostico_value", 'diagnostico');
    $query->addExpression("SUM(IF(d.field_diagnostico_value <> '_none' AND edad.field_edad_gestacional_value >= 1, 1, 0))", 'total_evaluados');
    $query->addExpression("SUM(IF(edad.field_edad_gestacional_value >= 1 AND edad.field_edad_gestacional_value < 38, 1, 0))", 'prematuros');
    $query->addExpression("SUM(IF(edad.field_edad_gestacional_value >= 38, 1, 0))", 'termino');
    $query->leftJoin('node__field_datos_personales', 'dp', 'd.bundle = dp.bundle AND d.entity_id = dp.entity_id and d.revision_id = dp.revision_id');
    $query->leftJoin('node__field_cpi', 'cpi', 'd.bundle = cpi.bundle AND d.entity_id = cpi.entity_id and d.revision_id = cpi.revision_id');
    $query->leftJoin('node__field_inscripto', 'ins', 'ins.entity_id = cpi.entity_id AND ins.revision_id = cpi.revision_id');
    $query->leftJoin('paragraph__field_edad_gestacional', 'edad', 'dp.field_datos_personales_target_id = edad.entity_id AND dp.field_datos_personales_target_revision_id = edad.revision_id');
    $query->condition('ins.field_inscripto_value', 1);
    $query->condition('cpi.field_cpi_target_id', $this->cpis, 'IN');
    $query->groupBy('d.field_diagnostico_value');
    $diagnosticos = $query->execute()->fetchAllAssoc('diagnostico');

    $d = [];
    foreach (array_keys($diagnosticos) as $diagnostico) {
      if ($diagnostico != '_none') {
        $d[$diagnostico] = [
          'diagnostico'     => $diagnosticos[$diagnostico]->diagnostico,
          'total_evaluados' => $diagnosticos[$diagnostico]->total_evaluados,
          'prematuros'      => $diagnosticos[$diagnostico]->prematuros,
          'termino'         => $diagnosticos[$diagnostico]->termino,
        ];
      }
    }

    $diagnosticos = $d;

    if (!$diagnosticos) {
      drupal_set_message('No hay diagnósticos para mostrar', 'warning');
      return $block;
    }

    $cant_total_evaluados = $cant_prematuros = $cant_termino = 0;
    foreach ($diagnosticos as $diagnostico) {
      $cant_total_evaluados += $diagnostico['total_evaluados'];
      $cant_prematuros += $diagnostico['prematuros'];
      $cant_termino  += $diagnostico['termino'];
    }

    $header = ['Diagnósticos' ,'Total Evaluados', 'Prematuros', '%', 'a Término', '%'];
    $footer = [['Total de niños', $cant_total_evaluados, $cant_prematuros, '', $cant_termino, '']];

    $block['nino'] = [
        '#type' => 'table',
        '#header' => $header,
        '#footer' => $footer,
        '#attributes' => [
          'class' => [
            'listado-semestral', 'diagnositco-gestacional',
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
        '#markup' => $diagnosticos[$diagnostico]['total_evaluados'],
      ];
      $block['nino'][$i]['prematuros'] = [
        '#type' => 'item',
        '#markup' => $diagnosticos[$diagnostico]['prematuros'],
      ];
      $prematuros = $diagnosticos[$diagnostico]['prematuros'];
      $block['nino'][$i]['porciento1'] = [
        '#type' => 'item',
        '#markup' => $prematuros == 0 ? 0 : round($prematuros * 100 / $total_evaluados, 1) . ' %',
      ];
      $block['nino'][$i]['termino'] = [
        '#type' => 'item',
        '#markup' => $diagnosticos[$diagnostico]['termino'],
      ];
      $a_termino = $diagnosticos[$diagnostico]['termino'];
      $block['nino'][$i]['porciento2'] = [
        '#type' => 'item',
        '#markup' => $a_termino == 0 ? 0 : round($diagnosticos[$diagnostico]['termino'] * 100 / $total_evaluados, 1) . ' %',
      ];
    }
    return $block;
  }

  // On submit form.
  public static function sime_salidas_diagnositco_gestacional_form_submit(&$form, FormStateInterface $form_state) {
    $cpis = $form_state->getValue('field_listado_cpis');
    $route_name = \Drupal::routeMatch()->getRouteName();

    $response = new RedirectResponse(\Drupal::url($route_name, [
      'c' => $cpis,
    ]));
    $response->send();
  }

}
