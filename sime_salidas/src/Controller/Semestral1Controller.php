<?php

namespace Drupal\sime_salidas\Controller;

use Drupal\Core\Controller\ControllerBase;

class Semestral1Controller extends SemestralController {

  public function view() {
    $block['form'] = parent::view();

    $diagnosticos = $this->diagnosticos;
    if (!$diagnosticos) {
      drupal_set_message('No hay diagnósticos para mostrar', 'warning');
      return $block;
    }

    $header = ['CPI' ,'Niñas < 2 a','Niños < 2 a', 'Niñas 2-5 a', 'Niños 2-5 a', 'Cantidad', 'Total Porcentaje'];
    $cantidad = $cant_ninas_2 = $cant_ninos_2 = $cant_ninas_2_5 = 0;
    $cant_ninos_2_5 = $porcentaje = $por_ninos = 0;
    foreach ($diagnosticos as $diagnostico) {
      $cant_ninas_2   += $diagnostico['ninas_2'];
      $cant_ninos_2   += $diagnostico['ninos_2'];
      $cant_ninas_2_5 += $diagnostico['ninas_2_5'];
      $cant_ninos_2_5 += $diagnostico['ninos_2_5'];
      $cantidad       += $diagnostico['cantidad'];
    }
    // Calcular porcientos.
    $tn = $diagnosticos['total_ninos'] === 0 ? 0 : 100 / $diagnosticos['total_ninos'];
    $por_cant_ninas_2   = round($cant_ninas_2   * $tn, 1) . '%';
    $por_cant_ninos_2   = round($cant_ninos_2   * $tn, 1) . '%';
    $por_cant_ninas_2_5 = round($cant_ninas_2_5 * $tn, 1) . '%';
    $por_cant_ninos_2_5 = round($cant_ninos_2_5 * $tn, 1) . '%';
    $por_cantidad       = round($cantidad * $tn, 1) . '%';

    $footer = [
      ['Cantidad total', $cant_ninas_2, $cant_ninos_2, $cant_ninas_2_5, $cant_ninos_2_5, $cantidad, ''],
      ['Porcentaje', $por_cant_ninas_2, $por_cant_ninos_2, $por_cant_ninas_2_5, $por_cant_ninos_2_5, $por_cantidad, ''],
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

      $block['nino'][$i]['ninas_2'] = [
        '#type' => 'item',
        '#markup' => $diagnosticos[$diagnostico]['ninas_2'],
      ];
      $block['nino'][$i]['ninos_2'] = [
        '#type' => 'item',
        '#markup' => $diagnosticos[$diagnostico]['ninos_2'],
      ];
      $block['nino'][$i]['ninas_2_5'] = [
        '#type' => 'item',
        '#markup' => $diagnosticos[$diagnostico]['ninas_2_5'],
      ];
      $block['nino'][$i]['ninos_2_5'] = [
        '#type' => 'item',
        '#markup' => $diagnosticos[$diagnostico]['ninos_2_5'],
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

