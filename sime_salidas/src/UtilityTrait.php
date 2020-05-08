<?php

namespace Drupal\sime_salidas;

use Drupal\field\Entity\FieldConfig;

trait UtilityTrait {

  public $chartColors = [
    'rgb(255, 99, 132)',
    'rgb(255, 159, 64)',
    'rgb(255, 205, 86)',
    'rgb(75, 192, 192)',
    'rgb(54, 162, 235)',
    'rgb(153, 102, 255)',
    'rgb(201, 203, 207)',
    'rgb(255, 99, 132)',
    'rgb(255, 159, 64)',
    'rgb(255, 205, 86)',
    'rgb(75, 192, 192)',
    'rgb(54, 162, 235)',
    'rgb(153, 102, 255)',
    'rgb(201, 203, 207)',
  ];

  // Obtener allowed_values de Diagnosticos.
  public static function sime_diagnosticos() {
    $entityManager = \Drupal::service('entity.manager');
    $fields = array_filter($entityManager->getFieldDefinitions('paragraph', 'datos_antropometricos'), function ($field_definition) { return $field_definition instanceof FieldConfig; });
    $diagnosticos = $fields['field_diagnostico']->getFieldStorageDefinition()->getSetting('allowed_values');
    return $diagnosticos;
  }

  // Obtener allowed_values de Tipo de informe.
  public static function sime_tipos_informe() {
    $entityManager = \Drupal::service('entity.manager');
    $fields = array_filter($entityManager->getFieldDefinitions('paragraph', 'casos'), function ($field_definition) { return $field_definition instanceof FieldConfig; });
    $tipos_informe = $fields['field_tipo_informe']->getFieldStorageDefinition()->getSetting('allowed_values');
    return $tipos_informe;
  }

  // Obtener allowed_values de Tipo de casos, ASI, Maltrato, etc...
  public static function sime_tipos_casos() {
    $entityManager = \Drupal::service('entity.manager');
    $fields = array_filter($entityManager->getFieldDefinitions('paragraph', 'casos'), function ($field_definition) { return $field_definition instanceof FieldConfig; });
    $tipos_casos = $fields['field_tipo_caso']->getFieldStorageDefinition()->getSetting('allowed_values');
    return $tipos_casos;
  }

  //Obtener fields de checklist de supervision.
  public static function sime_get_fields_supervision() {
    $entityManager = \Drupal::service('entity.manager');
    $fields_supervision = array_filter($entityManager->getFieldDefinitions('node', 'supervision'), function ($field_definition) { return $field_definition instanceof FieldConfig; });
    return $fields_supervision;
  }

  //Obtener los nombres reales de sala.
  public static function sime_get_nombres_salas() {
    $entityManager = \Drupal::service('entity.manager');
    $fields = array_filter($entityManager->getFieldDefinitions('paragraph', 'vacantes_salas'), function ($field_definition) { return $field_definition instanceof FieldConfig; });
    $nombres_salas= $fields['field_salas']->getFieldStorageDefinition()->getSetting('allowed_values');
    return $nombres_salas;
  }

  //Asignar colores a valores de un array.
  protected function sime_get_indexed_colors(array $values) {
    $values_indexed = [];
    if (!empty($values)) {
      foreach ($values as $i => $value) {
        if (isset($this->chartColors[$i])) {
          $values_indexed[$value] = $this->chartColors[$i];
        }
      }
      return empty($values_indexed) ? $this->chartColors : $values_indexed;
    }
    return $this->chartColors;
  }

  //Validar cpis en CpiMultipleForm, para algunos forms (salidas).
  protected function validar_cpis() {
    $form['form'] = $this->formBuilder()->getForm('\Drupal\sime_salidas\Form\CpiMultipleForm');
    $all_cpis = sime_ajustes_get_all_cpi();
    $cpis = \Drupal::request()->query->get('c');
    if($all_cpis) {
      $this->cpis = array_keys(sime_ajustes_get_all_cpi());
    }
    if ($cpis) {
      $this->cpis = $cpis;
    }
    if ($this->cpis && $cpis) {
      $form['form']['field_listado_cpis']['#value'] = $this->cpis;
    }
    if (!$this->cpis) {
      drupal_set_message('No se encontraron CPIs para mostrar', 'warning');
      return $form;
    }
    // Obtener total de ninos inscriptos por CPI.
    $totales_ninos = sime_ajustes_total_ninos_cpi();
    //Obtengo el total de ninos de los CPIs seleccionados.
    foreach ($this->cpis as $cpi) {
      if (isset($totales_ninos[$cpi])) {
        $this->total_ninos += $totales_ninos[$cpi]->total;
      }
    }
    if (empty($totales_ninos) || $this->total_ninos === 0) {
      drupal_set_message('No se encontraron niños para mostrar', 'warning');
      return $form;
    }
    return $form;
  }

}
