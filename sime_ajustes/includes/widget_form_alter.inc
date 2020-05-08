<?php

/**
 * Generally try to alter internal fields
 */
function sime_ajustes_field_widget_form_alter(&$element, \Drupal\Core\Form\FormStateInterface $form_state, $context) {
  $campos_internos = [];
  $cammpos_internos['node']['casos'] = [
    'field_motivo_intervencion' => 1,
    'field_fecha_update' => 1,
    'field_fecha_envio' => 1,
    'field_intervencion' => 1,
    'field_tipo_caso' => 1,
  ];

  $cammpos_internos['node']['nino'] = [
    'field_inscripto' => 1,
    'field_nino_sala' => 1,
    'field_inscripcion_fecha' => 1,
    'field_diagnostico' => 1,
    'field_ausencias_seguidas_salud' => 1,
    'field_ausencias_seguidas_viaje' => 1,
    'field_ausencias_seguidas_ausente' => 1,
    'field_anno_mediciones' => 1,
    'field_cambio_rezago' => 1,
    'field_con_rezago' => 1,
    'field_ultima_medicion' => 1,
    'field_primera_medicion' => 1,
  ];

  $cammpos_internos['paragraph']['datos_antropometricos'] = [
    'field_diagnostico' => 1,
  ];

  $field_definition = $context['items']->getFieldDefinition();
  $entity_type = $element['#entity_type'];
  $bundle = $element['#bundle'];
  $field_name = $field_definition->getName();

  if (!empty($cammpos_internos[$entity_type][$bundle][$field_name])) {
    //$element['#disabled'] = true;
    $campo_interno_input =& $element['value'];
    if (empty($campo_interno_input['#description'])) {
      $campo_interno_input['#description'] = '';
    }

    $campo_interno_input['#description'] = '<b>Campo interno.</b> ' . $campo_interno_input['#description'];
    if (isset($element['#description'])) {
      $element['#description'] = '<b>Campo interno.</b> ' . $element['#description'];
    }
    if (!\Drupal::currentUser()->hasPermission('ver campos internos')) {
      $element['#access'] = FALSE;
    }
  }
}

/**
 * Implements hook_field_widget_WIDGET_TYPE_form_alter().
 */
function sime_ajustes_field_widget_options_select_form_alter(&$element, \Drupal\Core\Form\FormStateInterface $form_state, $context) {
  if (!empty($element['#options']['_none']) && count($element['#options']) == 2 && empty($element['#default_value']) && $element['#required']) {
    $keys = array_keys($element['#options']);
    $element["#default_value"] = $keys[1];
  }
}

/**
 * Implements hook_field_widget_WIDGET_TYPE_form_alter().
 *
 * - Globally hide remove button
 */
function sime_ajustes_field_widget_entity_reference_paragraphs_form_alter(&$element, \Drupal\Core\Form\FormStateInterface $form_state, $context) {
  $remove_button = &$element['top']['links']['remove_button'];
  if (\Drupal::currentUser()->id() != 1) {
     $remove_button['#access'] = FALSE;
  }
  if (isset($element['#paragraph_type'])) {
    $function = 'sime_ajustes_field_widget_entity_reference_paragraphs_' . $element['#paragraph_type'] . '_form_alter';
    if (function_exists($function)) {
      $function($element, $form_state, $context);
    }
  }
}

/**
 *  - Selectively show remove button
 *  - FAPI states
 */
function sime_ajustes_field_widget_entity_reference_paragraphs_inscripcion_form_alter(&$element, \Drupal\Core\Form\FormStateInterface $form_state, $context) {
  $remove_button = &$element['top']['links']['remove_button'];
  if (\Drupal::currentUser()->id() != 1) {
    $remove_button['#access'] = FALSE;
  }

  if (empty($element['top']['links']['confirm_remove_button'])) {
    $year_element =& $element['subform']['field_ano']['widget'][0]['value'];
    if (empty($year_element['#default_value'])) {
      $remove_button['#access'] = TRUE;
      $year_element['#type'] = 'select';
      $year_element['#options'] = [
        date('Y') => date('Y'),
        date('Y')+1 => date('Y')+1
      ];
    }
    else {
      $year_element['#disabled'] = TRUE;
    }
  }

  $element['subform']['field_sala_nombre']['#states'] = [
    'visible' => [
      ':input[name="field_inscripcion[' . $element['#delta'] . '][subform][field_inscripto][value]"]' => array('checked' => TRUE),
    ],
  ];
}


function sime_ajustes_field_widget_entity_reference_paragraphs_casos_form_alter(&$element, \Drupal\Core\Form\FormStateInterface $form_state, $context) {

  $para_corregir = $element['subform']['field_leido']['widget']['value']['#default_value'];
  if (!\Drupal::currentUser()->hasPermission('admin casos')) {
    $element['subform']['field_resolucion']['#access'] = FALSE;
    // Campos Notificacion en Casos
    unset($element['subform']['subform']['#fieldgroups']['group_notificacion']);
    $element['subform']['field_fecha_envio']['#access'] = FALSE;
    $element['subform']['field_sade']['#access'] = FALSE;
    $element['subform']['field_intervencion']['#access'] = FALSE;
    $element['subform']['field_defensorias']['#access'] = FALSE;
    $element['subform']['field_correcciones']['#access'] = FALSE;
    if ($para_corregir) {
      $element['subform']['field_correcciones']['#access'] = TRUE;
    }
  }
  else {
    $informe_ok = $element['subform']['field_fecha_envio']['widget'][0]['value']['#default_value'];
    //Si Admin casos definio una fecha de envio(notificacion), poner read-only.
    //Este campo se toma como el OK del informe.
    if ($informe_ok) {
      $element['subform']['field_fecha_envio']['widget'][0]['#disabled'] = TRUE;
    }
  }

  // El primero va a ser siempre primera intervención
  if ($element['#delta'] == 0) {
    unset($element['subform']['field_tipo_informe']['widget']['#options']['acualizacion']);
  }

  $tipo_informe = $element['subform']['field_tipo_informe']['widget']['#default_value'];

  if (!empty($tipo_informe)) {
    if ($tipo_informe[0] == 'intervencion') {
      unset($element['subform']['field_tipo_informe']['widget']['#options']['acualizacion']);
    }
    else {
      unset($element['subform']['field_tipo_informe']['widget']['#options']['intervencion']);
    }
  }

  // TODO: Si el paragraph esta para corregir, ponertodos los camposen read only(disabled)
  if (!empty($para_corregir)) {
    foreach (array_keys($element['subform']) as $field) {
      if (preg_match('/^field_/', $field)) {
        $element['subform'][$field]['#disabled'] = !Drupal::currentUser()->hasPermission('admin casos');
      }
    }
  }
  else {
    $element['subform']['field_leido']['#access'] = Drupal::currentUser()->hasPermission('admin casos');
  }

  $element['subform']['field_correcciones']['#states'] = [
    'visible' => [
      ':input[name="field_casos[' . $element['#delta'] . '][subform][field_leido][value]"]' => ['checked' => TRUE],
    ],
  ];

  $element['subform']['field_tipo_caso_otro']['#states'] = [
    'visible' => [
      ':input[name="field_casos[' . $element['#delta'] . '][subform][field_tipo_caso]"]' => ['value' => 'otro'],
    ],
  ];

  unset($element['subform']['field_tipo_caso_otro']['widget'][0]['value']['#title']);
  $element['subform']['field_tipo_caso_otro']['widget'][0]['value']['#size'] = 25;

  $element['subform']['field_defensorias']['#states'] = [
    'visible' => [
      ':input[name="field_casos[' . $element['#delta'] . '][subform][field_intervencion][value]"]' => ['checked' => TRUE],
    ],
  ];
  $element['subform']['field_tipo_caso']['#states'] =
  $element['subform']['field_fecha_situacion']['#states'] = [
    'visible' => [
      ':input[name="field_casos[' . $element['#delta'] . '][subform][field_tipo_informe]"]' => ['value' => 'intervencion'],
    ],
  ];
}

function sime_ajustes_field_widget_entity_reference_paragraphs_datos_personales_form_alter(&$element, \Drupal\Core\Form\FormStateInterface $form_state, $context) {
  if ($element['#bundle'] == 'nino') {
    $element['subform']['field_documento_id']['#states'] =
    $element['subform']['field_tipo_documento']['#states'] = [
      'visible' => [
        ':input[name="field_datos_personales[' . $element['#delta'] . '][subform][field_sin_dni][value]"]' => array('checked' => TRUE),
      ],
    ];
    $element['subform']['field_direccion']['widget'][0]['subform']['field_partido']['#states'] = [
      'visible' => [
        ':input[name="field_datos_personales[' . $element['#delta'] . '][subform][field_direccion][0][subform][field_jurisdiccion]"]' => array('value' => 'ba'),
      ],
    ];
    $element['subform']['field_direccion']['widget'][0]['subform']['field_barrio']['#states'] = [
      'visible' => [
        ':input[name="field_datos_personales[' . $element['#delta'] . '][subform][field_direccion][0][subform][field_jurisdiccion]"]' => array('value' => 'caba'),
      ],
    ];
  }
}
