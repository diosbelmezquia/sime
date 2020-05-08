<?php

namespace Drupal\sime_salidas\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\sime_salidas\UtilityTrait;

class NinosSalasController extends ControllerBase {

  use UtilityTrait;

  protected $cpis = NULL;

  // Obtener formulario.
  public function view() {
    $cpis  = \Drupal::request()->query->get('c');
    $block['form'] = $this->formBuilder()->getForm('\Drupal\sime_salidas\Form\CpiMultipleForm');
    $this->cpis = array_keys(sime_ajustes_get_all_cpi());
    if ($cpis) {
      $this->cpis = $cpis;
    }
    if ($this->cpis && $cpis) {
      $block['form']['field_listado_cpis']['#value'] = $this->cpis;
    }
    if (!$this->cpis) {
      drupal_set_message('No se encontraron CPIs para mostrar', 'warning');
      return $block;
    }
    // Obtener total de ninos inscriptos por CPI.
    $totales_ninos = sime_ajustes_total_ninos_cpi();

    $total = 0;
    //Obtengo el total de nino de los CPIs seleccionados.
    foreach ($this->cpis as $cpi) {
      if (isset($totales_ninos[$cpi])) {
        $total += $totales_ninos[$cpi]->total;
      }
    }

    if (empty($totales_ninos) || $total === 0) {
      drupal_set_message('No se encontraron niños para mostrar', 'warning');
      return $block;
    }

    //Obtener cantidad de ninos/ninas por sala.
    $query = db_select('node__field_nino_sala', 'sala');
    $query->addField('sala', 'field_nino_sala_value', 'sala');
    $query->addExpression("COUNT(*)", 'cantidad');
    $query->leftJoin('node__field_cpi', 'cpi', 'sala.entity_id = cpi.entity_id AND sala.revision_id = cpi.revision_id');
    $query->leftJoin('node__field_inscripto', 'ins', 'ins.entity_id = cpi.entity_id AND ins.revision_id = cpi.revision_id');
    $query->condition('ins.field_inscripto_value', 1);
    $query->condition('cpi.field_cpi_target_id', $this->cpis, 'IN');
    $query->groupBy('sala.field_nino_sala_value');
    $ninos_por_salas = $query->execute()->fetchAllAssoc('sala');

    //Obtener cantidad de ninos/ninas por sala(Con genero).
    $query = db_select('node__field_nino_sala', 'sala');
    $query->addField('sala', 'field_nino_sala_value', 'sala');
    $query->addField('sexo', 'field_sexo_value', 'genero');
    $query->addExpression("COUNT(*)", 'cantidad');
    $query->leftJoin('node__field_cpi', 'cpi', 'sala.entity_id = cpi.entity_id AND sala.revision_id = cpi.revision_id');
    $query->leftJoin('node__field_inscripto', 'ins', 'ins.entity_id = cpi.entity_id AND ins.revision_id = cpi.revision_id');
    $query->leftJoin('node__field_datos_personales', 'dp', 'ins.entity_id = dp.entity_id');
    $query->leftJoin('paragraph__field_sexo', 'sexo', 'dp.field_datos_personales_target_id = sexo.entity_id AND sexo.revision_id = dp.field_datos_personales_target_revision_id');
    $query->condition('ins.field_inscripto_value', 1);
    $query->condition('cpi.field_cpi_target_id', $this->cpis, 'IN');
    $query->groupBy('sala');
    $query->groupBy('genero');
    $ninos_genero_salas = $query->execute()->fetchAll();

    if (empty($ninos_genero_salas)) {
      drupal_set_message('No se encontraron niños/as para mostrar.', 'warning');
    }

    $ninos_salas_hombre = array_filter($ninos_genero_salas, function($field) {
      return $field->genero == 'hombre';
    });
    foreach ($ninos_salas_hombre as $i => $sala) {
      $ninos_salas_hombre[$sala->sala] = $sala;
      unset($ninos_salas_hombre[$i]);
    }

    $ninos_salas_mujer = array_filter($ninos_genero_salas, function($field) {
      return $field->genero == 'mujer';
    });
    foreach ($ninos_salas_mujer as $i => $sala) {
      $ninos_salas_mujer[$sala->sala] = $sala;
      unset($ninos_salas_mujer[$i]);
    }

    //Obtener los nombres reales de sala.
    $nombres_salas = UtilityTrait::sime_get_nombres_salas();

    $cant_total_ninos = array_sum(array_column($ninos_salas_hombre, 'cantidad'));
    $cant_total_ninas = array_sum(array_column($ninos_salas_mujer, 'cantidad'));

    //Cambio machine_name por nombres de salas reales.
    foreach ($ninos_por_salas as $i => $sala) {
      if (isset($nombres_salas[$i])) {
        $porciento_general = round($sala->cantidad * 100 / $total, 1);
        if (isset($ninos_salas_mujer[$sala->sala])) {
          $porciento_ninas = round($ninos_salas_mujer[$sala->sala]->cantidad * 100 / $cant_total_ninas, 1);
          $ninos_salas_mujer[$sala->sala]->sala = $nombres_salas[$sala->sala] . " ($porciento_ninas%)";
        }
        if (isset($ninos_salas_hombre[$sala->sala])) {
          $porciento_ninos = round($ninos_salas_hombre[$sala->sala]->cantidad * 100 / $cant_total_ninos, 1);
          $ninos_salas_hombre[$sala->sala]->sala = $nombres_salas[$sala->sala] . " ($porciento_ninos%)";
        }
        $sala->sala = $nombres_salas[$sala->sala] . " ($porciento_general%)";
      }
    }

    $block['ninos_sala'] = [
      '#theme' => 'sime_salidas_porciento_cpis',
      '#ninos_por_salas' => empty($ninos_por_salas) ? NULL : 'ninos-salas',
      '#ninos_por_salas_hombre' => empty($ninos_salas_hombre) ? NULL : 'ninos-salas-hombre',
      '#ninos_por_salas_mujer' => empty($ninos_salas_mujer) ? NULL : 'ninos-salas-mujer',
      '#attached' => [
        'drupalSettings' => [
          'simeSalidas' => [
            'ninos_sala' => [
              'salas' => $ninos_por_salas,
              'colors' => $this->chartColors,
              'colors_indexed' => $this->sime_get_indexed_colors(array_keys($ninos_por_salas)),
            ],
            'ninos_sala_hombre' => [
              'salas' => $ninos_salas_hombre,
            ],
            'ninos_sala_mujer' => [
              'salas' => $ninos_salas_mujer,
            ],
          ],
        ],
      ],
    ];
   $block['#attached'] = [
      'library' => [
        'sime_salidas/sime_salidas.chartjs',
      ],
    ];
    return $block;
  }

  // On submit form.
  public static function sime_salidas_ninos_salas_form_submit(&$form, FormStateInterface $form_state) {
    $cpis = $form_state->getValue('field_listado_cpis');
    $route_name = \Drupal::routeMatch()->getRouteName();
    $response = new RedirectResponse(\Drupal::url($route_name, [
      'c' => $cpis,
    ]));
    $response->send();
  }
}
