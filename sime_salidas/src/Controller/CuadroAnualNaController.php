<?php

namespace Drupal\sime_salidas\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\field\Entity\FieldConfig;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\sime_salidas\UtilityTrait;

class CuadroAnualNaController extends ControllerBase {
  use UtilityTrait;

  public function mostrar() {

    $cpi = \Drupal::request()->query->get('cpi');
    $block['form'] = $this->formBuilder()->getForm('\Drupal\sime_salidas\Form\CpiMultipleForm');
    if ($cpi) {
      $block['form']['field_listado_cpis']['#value'] = $cpi;
    }
    else {
      return $block;
    }

    //Obtener fields de checklist de supervision.
    $fields_supervision = UtilityTrait::sime_get_fields_supervision();
    $fields = [];
    foreach ($fields_supervision as $field) {
      if ($field->getType() == 'list_string') {
        $fields[$field->getLabel()] = $field->getName();
      }
    }

    //Obtener meses de anno.
    $meses_fields = [];
    $monthNames = \Drupal\Core\Datetime\DateHelper::monthNamesUntranslated();
    $months = array_flip($monthNames);

    //Hacer querys por cada field.
    foreach ($fields as $index => $field) {
      $value_field = 's.' . $field . '_value';
      $node_field = 'node__' . $field;

      $query = db_query("SELECT
          MONTHNAME(f.field_re_periodo_value) as mes,
          SUM(IF($value_field = 'na', 1, 0)) as na,
          count(*) as total
        FROM $node_field s
        LEFT JOIN node__field_cpi cpi on s.bundle = cpi.bundle AND s.entity_id = cpi.entity_id AND s.revision_id = cpi.revision_id
        LEFT JOIN node__field_re_periodo f ON s.bundle = f.bundle AND s.entity_id = f.entity_id AND s.revision_id = f.revision_id
        WHERE cpi.field_cpi_target_id = :cpi
        GROUP BY MONTHNAME(f.field_re_periodo_value)", [':cpi' => $cpi])->fetchAllAssoc('mes');

      //Agrego todos los meses al array para despues mostralo.
      foreach ($query as $i => $m) {
        $months[$i] = $m;
      }
      //Agrego cada field de supervision con sus meses y la cantidad de (N/O).
      $meses_fields[$index] = $months;
    }

    //Meses para el header de la tabla.
    $meses = ['', 'Ene', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
    $block['anual'] = [
        '#type' => 'table',
        '#header' => $meses,
        '#attributes' => [
          'class' => [
            'cuadro-anual',
            ],
        ],
      ];

    //Recorro los fields de supervision.
    $cont = 0;
    foreach ($meses_fields as $i => $field) {
      $block['anual'][$cont]['#attributes'] = [
        'class' => ['foo','baz'],
      ];
      $block['anual'][$cont]['element'] = [
        '#type' => 'item',
        '#markup' => $i,
        '#wrapper_attributes' =>[
          'class' => 'items-supervision',
          ],
      ];
      //Recorro el subarray de los meses del field actual.
      foreach ($field as $mes => $value) {
        $block['anual'][$cont][$mes]['#type'] = 'item';
        if (is_object($value) && $value->na > 0) {
          $block['anual'][$cont][$mes]['#markup'] = $value->na;
          if ($value->na >= 3) {
            $block['anual'][$cont][$mes]['#wrapper_attributes']['class'] = ['rojo'];
          }
        }
        else {
          $block['anual'][$cont][$mes]['#markup'] = '';
        }
      }
      $cont++;
    }
    return $block;
  }

    // On submit form.
  public static function sime_salidas_cuadro_anual_na_form_submit(&$form, FormStateInterface $form_state) {
    $cpi = $form_state->getValue('field_listado_cpis');
    $route_name = \Drupal::routeMatch()->getRouteName();
    $response = new RedirectResponse(\Drupal::url($route_name, [
      'cpi' => $cpi,
    ]));
    $response->send();
  }
}
