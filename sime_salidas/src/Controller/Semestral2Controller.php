<?php

namespace Drupal\sime_salidas\Controller;

use Drupal\Core\Controller\ControllerBase;

class Semestral2Controller extends SemestralController {

  public function view() {
    $block['form'] = parent::view();

    $diagnosticos = $this->diagnosticos;
    if (!$diagnosticos) {
      drupal_set_message('No hay diagnósticos para mostrar', 'warning');
      return $block;
    }

    $header = ['CPI' ,'Niñas < 1 a','Niños < 1 a', 'Niñas de 1 a 2', 'Niños de 1 a 2', 'Niñas de 2 a 3', 'Niños de 2 a 3', 'Niñas de 3 a 4', 'Niños de 3 a 4', 'Niñas > 4', 'Niños > 4','Cantidad', 'Total Porcentaje'];

    $cant_ninas_1 = $cant_ninos_1 = $cant_ninas_1_2 = $cant_ninos_1_2 = 0;
    $cant_ninas_2_3 = $cant_ninos_2_3 = $cant_ninas_3_4 = 0;
    $cant_ninos_3_4 = $cant_ninas_4 = $cant_ninos_4 = $cantidad = 0;
    foreach ($diagnosticos as $diagnostico) {
      $cant_ninas_1   += $diagnostico['ninas_1'];
      $cant_ninos_1   += $diagnostico['ninos_1'];
      $cant_ninas_1_2 += $diagnostico['ninas_1_2'];
      $cant_ninos_1_2 += $diagnostico['ninos_1_2'];
      $cant_ninas_2_3 += $diagnostico['ninas_2_3'];
      $cant_ninos_2_3 += $diagnostico['ninos_2_3'];
      $cant_ninas_3_4 += $diagnostico['ninas_3_4'];
      $cant_ninos_3_4 += $diagnostico['ninos_3_4'];
      $cant_ninas_4   += $diagnostico['ninas_4'];
      $cant_ninos_4   += $diagnostico['ninos_4'];
      $cantidad       += $diagnostico['cantidad'];
    }

     // Calcular porcientos.
    $tn = $diagnosticos['total_ninos'] === 0 ? 0 : 100 / $diagnosticos['total_ninos'];
    $por_cant_ninas_1   = round($cant_ninas_1   * $tn, 1) . '%';
    $por_cant_ninos_1   = round($cant_ninos_1   * $tn, 1) . '%';
    $por_cant_ninas_1_2 = round($cant_ninas_1_2 * $tn, 1) . '%';
    $por_cant_ninos_1_2 = round($cant_ninos_1_2 * $tn, 1) . '%';
    $por_cant_ninas_2_3 = round($cant_ninas_2_3 * $tn, 1) . '%';
    $por_cant_ninos_2_3 = round($cant_ninos_2_3 * $tn, 1) . '%';
    $por_cant_ninas_3_4 = round($cant_ninas_3_4 * $tn, 1) . '%';
    $por_cant_ninos_3_4 = round($cant_ninos_3_4 * $tn, 1) . '%';
    $por_cant_ninas_4   = round($cant_ninas_4   * $tn, 1) . '%';
    $por_cant_ninos_4   = round($cant_ninos_4   * $tn, 1) . '%';
    $por_cantidad       = round($cantidad * $tn, 1) . '%';

    $footer = [
      ['Cantidad total',$cant_ninas_1, $cant_ninos_1, $cant_ninas_1_2, $cant_ninos_1_2, $cant_ninas_2_3, $cant_ninos_2_3, $cant_ninas_3_4, $cant_ninos_3_4, $cant_ninas_4, $cant_ninos_4, $cantidad, ''],
      ['Porcentaje', $por_cant_ninas_1, $por_cant_ninos_1, $por_cant_ninas_1_2, $por_cant_ninos_1_2, $por_cant_ninas_2_3, $por_cant_ninos_2_3, $por_cant_ninas_3_4, $por_cant_ninos_3_4, $por_cant_ninas_4, $por_cant_ninos_4, $por_cantidad, ''],
    ];

    $block['nino'] = [
        '#type' => 'table',
        '#header' => $header,
        '#footer' => $footer,
        '#attributes' => [
          'class' => [
            'listado-semestral',
            ],
        ],
      ];

    $remove_total_ninos = array_shift($diagnosticos);
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

      $block['nino'][$i]['ninas_1'] = [
        '#type' => 'item',
        '#markup' => $diagnosticos[$diagnostico]['ninas_1'],
      ];
      $block['nino'][$i]['ninos_1'] = [
        '#type' => 'item',
        '#markup' => $diagnosticos[$diagnostico]['ninos_1'],
      ];
      $block['nino'][$i]['ninas_1_2'] = [
        '#type' => 'item',
        '#markup' => $diagnosticos[$diagnostico]['ninas_1_2'],
      ];
      $block['nino'][$i]['ninos_1_2'] = [
        '#type' => 'item',
        '#markup' => $diagnosticos[$diagnostico]['ninos_1_2'],
      ];
      $block['nino'][$i]['ninas_2_3'] = [
        '#type' => 'item',
        '#markup' => $diagnosticos[$diagnostico]['ninas_2_3'],
      ];
      $block['nino'][$i]['ninos_2_3'] = [
        '#type' => 'item',
        '#markup' => $diagnosticos[$diagnostico]['ninos_2_3'],
      ];
      $block['nino'][$i]['ninas_3_4'] = [
        '#type' => 'item',
        '#markup' => $diagnosticos[$diagnostico]['ninas_3_4'],
      ];
      $block['nino'][$i]['ninos_3_4'] = [
        '#type' => 'item',
        '#markup' => $diagnosticos[$diagnostico]['ninos_3_4'],
      ];
      $block['nino'][$i]['ninas_4'] = [
        '#type' => 'item',
        '#markup' => $diagnosticos[$diagnostico]['ninas_4'],
      ];
      $block['nino'][$i]['ninos_4'] = [
        '#type' => 'item',
        '#markup' => $diagnosticos[$diagnostico]['ninos_4'],
      ];

      $block['nino'][$i]['cantidad'] = [
        '#type' => 'item',
        '#markup' => $diagnosticos[$diagnostico]['cantidad'],
      ];
      $porcentaje = round($diagnosticos[$diagnostico]['cantidad'] * $tn, 1) . '%';
      $block['nino'][$i]['porcentaje'] = [
        '#type' => 'item',
        '#markup' => $porcentaje,
      ];
    }
    return $block;
  }

}

