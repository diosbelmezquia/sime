<?php

/**
 * @file
 * Contains \Drupal\sime_inasistencias\Form\InasistenciaListadoForm.
 */

namespace Drupal\sime_inasistencias\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Datetime\DrupalDateTime;

class InasistenciaListadoForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'sime_inasistencias_listado_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['field_ninos_inasistencias'] = [
      '#type' => 'table',
      '#caption' => 'Niños',
      '#header' => ['Nombre(s) y Apellidos(s)', 'Ausente', 'Motivo', ''],
      '#empty' => 'No hay niños inscriptos en esta sala.',
      '#attributes' => ['id' => 'ninos-sala-table'],
    ];

    $cpi   = \Drupal::request()->query->get('field_cpi');
    $sala  = \Drupal::request()->query->get('sala');
    $name  = \Drupal::request()->query->get('name');
    $fecha = \Drupal::request()->query->get('fecha');
    $ninos = [];

    if ($fecha || $sala || $name) {
      $ninos = sime_inasistencias_get_ninos($cpi, $sala, $name, $fecha);
    }

    if (!$ninos) {
      return $form;
    }

    foreach ($ninos as $i => $nino) {
      $option = [];
      $checked = 0;
      // Cargar el motivo para un nino ya ausente.
      if (is_array($ninos[$i]['ausente'])) {
        // Obtengo el ultimo motivo de ausencia.
        $option = array_pop($ninos[$i]['ausente']);
        // Si tiene ausencia marcar el check.
        if ($option != '_none') {
          $checked = 1;
        }
      }

      $form['field_ninos_inasistencias'][$i]['#attributes'] = [
        'class' => ['foo','baz',],
      ];

      $form['field_ninos_inasistencias'][$i]['nombre_apellido'] = [
        '#type' => 'item',
        '#markup' => $ninos[$i]['nombre'],
      ];

      $form['field_ninos_inasistencias'][$i]['ausente'] = [
        '#type' => 'checkbox',
        '#default_value' => $checked,
      ];

      $form['field_ninos_inasistencias'][$i]['motivo'] = [
        '#type' => 'select',
        '#options' => [
          '_none' => '- Ninguno -',
          'ausente' => 'Ausente sin motivo',
          'viaje' => 'Ausente por viaje',
          'salud' => 'Ausente por un problema de salud'
        ],
        '#default_value' => $option,
      ];

      $form['field_ninos_inasistencias'][$i]['node_nino'] = [
        '#type' => 'hidden',
        '#default_value' => $ninos[$i]['nino_id'],
      ];
    }

    $form['actions']['#type'] = 'actions';

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => 'Guardar',
      '#button_type' => 'primary',
      '#access' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
      $ninos_inasistencias = $form_state->getValue('field_ninos_inasistencias');
      if ($ninos_inasistencias > 0) {
        foreach ($ninos_inasistencias as $index => $nino_ausente) {
          $id_nino = $nino_ausente['node_nino'];
          $ausente = $nino_ausente['ausente'];
          $motivo = $nino_ausente['motivo'];
          if ($ausente && $motivo == '_none') {
            $form_state->setErrorByName('field_ninos_inasistencias]['.$index.'][motivo', 'Seleccione un motivo de inasistencia.');
          }
          if (!$ausente && $motivo != '_none') {
            $form_state->setErrorByName('field_ninos_inasistencias]['.$index.'][motivo', 'No puede seleccionar motivo sin estar ausente.');
          }
        }
      }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {}

}
