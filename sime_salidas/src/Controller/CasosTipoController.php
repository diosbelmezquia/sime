<?php

namespace Drupal\sime_salidas\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\sime_salidas\UtilityTrait;

class CasosTipoController extends ControllerBase {
  use UtilityTrait;

  protected $cpis = NULL;

  protected static $tipo_casos = [];

  // Obtener formulario.
  public function view() {
    $cpis = \Drupal::request()->query->get('c');
    $porciento = \Drupal::request()->query->get('p');
    $this->cpis = array_keys(sime_ajustes_get_all_cpi());
    $block['form'] = $this->formBuilder()->getForm('\Drupal\sime_salidas\Form\CpiMultipleForm');
    if (!$porciento) {
      $porciento =  $this->cpis ? self::sime_salidas_calcular_porciento($this->cpis) : NULL;
    }
    if ($cpis) {
      $this->cpis = $cpis;
    }
    if ($this->cpis && $cpis) {
      $block['form']['field_listado_cpis']['#value'] = $this->cpis;
    }

    if (!$this->cpis) {
      drupal_set_message('No se encontraron CPIs para mostrar', 'warning');
      return $output;
    }

    if ($porciento) {
      $block['porciento_casos_tipo'] = [
        '#theme' => 'sime_salidas_porciento_casos_tipo',
        '#porciento_casos_tipo' => 'porciento-casos-tipo',
        '#attached' => [
          'drupalSettings' => [
            'simeSalidas' => [
              'porciento_casos_tipo' => $porciento,
              'colors' => $this->chartColors,
            ],
          ],
        ],
      ];
     $block['#attached'] = [
        'library' => [
          'sime_salidas/sime_salidas.chartjs',
        ],
      ];
    }
    else {
      drupal_set_message('No hay datos para mostrar', 'warning');
    }
    return $block;
  }

  // On submit form.
  public static function sime_salidas_porciento_casos_tipo_form_submit(&$form, FormStateInterface $form_state) {
    $cpis = $form_state->getValue('field_listado_cpis');
    $route_name = \Drupal::routeMatch()->getRouteName();
    $porciento = !empty($cpis) ? self::sime_salidas_calcular_porciento($cpis) : NULL ;
    $response = new RedirectResponse(\Drupal::url($route_name, [
      'c' => $cpis,
      'p' => $porciento,
    ]));
    $response->send();
  }

  // Calcular porciento.
  private static function sime_salidas_calcular_porciento($cpis) {
    // Obtener cantidad de ninos por tipo de caso.
    $query = db_select('node__field_motivo_intervencion', 'mo');
    $query->addField('mo', 'field_motivo_intervencion_value', 'motivo');
    $query->addExpression("COUNT(*)", 'cantidad');
    $query->condition('cpi.bundle', 'nino');
    $query->condition('cpi.field_cpi_target_id', $cpis, 'IN');
    $query->condition('ins.field_inscripto_value', 1);
    $query->leftJoin('node__field_nino', 'ni', 'ni.entity_id = mo.entity_id AND ni.revision_id = mo.revision_id AND ni.bundle = mo.bundle');
    $query->leftJoin('node__field_cpi', 'cpi', 'ni.field_nino_target_id = cpi.entity_id');
    $query->leftJoin('node__field_inscripto', 'ins', 'ins.entity_id = cpi.entity_id AND ins.revision_id = cpi.revision_id');
    $query->groupBy('motivo');
    self::$tipo_casos = $query->execute()->fetchAllAssoc('motivo');

    //Quedarme con los tipo casos (Otros).
    $fields = array_filter(self::$tipo_casos, function ($field) {
      if (preg_match('/^otro:/', $field->motivo)) {
        unset(self::$tipo_casos[$field->motivo]);
        return TRUE;
      }
      else return FALSE;
    });
    //Contar la cantidad de (Otros).
    $otro_cantidad = array_sum(array_column($fields, 'cantidad'));
    if ($otro_cantidad != 0) {
      $otro = (object) [
        'motivo' => 'Otro',
        'cantidad' => $otro_cantidad,
      ];
      self::$tipo_casos['Otro'] = $otro;
    }

    // Obtener total de ninos inscriptos por CPI.
    $totales_ninos = sime_ajustes_total_ninos_cpi();
    if (empty($totales_ninos)) {
      return [];
    }

    // Obtener cantidad total de niños de todos los CPIs seleccionados.
    $total_ninos = 0;
    foreach ($cpis as $cpi) {
      if (isset($totales_ninos[$cpi])) {
        $total_ninos += $totales_ninos[$cpi]->total;
      }
    }

    // Calcular porciento de niños por tipo de caso, de los CPIs seleccionados.
    $porciento_casos = [];
    $tipos_casos_names = UtilityTrait::sime_tipos_casos();
    foreach (self::$tipo_casos as $caso) {
      $cantidad = $caso->cantidad;
      $porciento = round($caso->cantidad * 100 / $total_ninos, 1);
      $porciento_casos[$caso->motivo . " ($porciento%)"] = $cantidad;
    }
    return $porciento_casos;
  }
}
