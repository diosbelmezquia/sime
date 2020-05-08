<?php

/**
 * @file
 * Contains \Drupal\sime_inasistencias\Form\InasistenciaForm.
 */

namespace Drupal\sime_inasistencias\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Datetime\DrupalDateTime;

class InasistenciaForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'sime_inasistencias_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $cpi   = \Drupal::request()->query->get('field_cpi');
    $sala  = \Drupal::request()->query->get('sala');
    $name  = \Drupal::request()->query->get('name');
    $fecha = \Drupal::request()->query->get('fecha');

    // Obtener todos los cpi.
    $listado_cpis = sime_ajustes_get_all_cpi();

    $form['field_listado_cpis'] = [
      '#type' => 'select',
      '#title' => 'CPI',
      '#options' =>  $listado_cpis,
      '#required' => TRUE,
      '#multiple' => FALSE,
      '#description' => 'Seleccione un CPI',
      '#attributes' => [
        'class' => []
      ],
    ];

    $cpi_url = isset($cpi) ? Node::load($cpi) : NULL;
    $cpi_select = $form['field_listado_cpis']['#options'];
    $options = [
      'salas' => [],
      'sala_nombre' => []
    ];
    if (isset($cpi_url)) {
      $form['field_listado_cpis']['#value'] = $cpi;
      $options = sime_inasistencias_get_salas($cpi);
    }
    elseif (count($cpi_select) == 1) {
      $cpis = array_keys($form['field_listado_cpis']['#options']);
      $cpi = reset($cpis);
      $form['field_listado_cpis']['#value'] = $cpi;
      $options = sime_inasistencias_get_salas($cpi);
    }

    $form['field_fecha_inasistencias'] = [
      '#type' => 'date',
      '#title' => 'Fecha',
      '#required' => TRUE,
      '#default_value' => date('Y-m-d'),
    ];

    $form['field_sala_inasistencias'] = [
      '#type' => 'select',
      '#title' => 'Sala',
      '#required' => FALSE,
      '#options' =>  $options['salas'] ? $options['salas'] : ['No hay salas cargadas'],
      '#empty_option' => 'Seleccione una sala',
      '#description' => 'Seleccione una sala',
      '#access' => FALSE,
    ];

    $form['field_nombre_inasistencias'] = [
      '#type' => 'select',
      '#required' => TRUE,
      '#title' => 'Nombre de la sala',
      '#options' => $options['sala_nombre'] ? $options['sala_nombre'] : ['No hay nombres cargados'],
    ];

    $form['buscar'] = [
      '#type' => 'submit',
      '#value' => 'Buscar',
      '#button_type' => 'primary',
      '#access' => TRUE,
    ];

    if ($fecha || $sala || $name) {
      $form['field_fecha_inasistencias']['#default_value']  = $fecha;
      $form['field_sala_inasistencias']['#default_value']   = $sala;
      $form['field_nombre_inasistencias']['#default_value'] = $name;
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

}
