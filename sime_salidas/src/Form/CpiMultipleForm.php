<?php

namespace Drupal\sime_salidas\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class CpiMultipleForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    $route_name = \Drupal::routeMatch()->getRouteName();
    return 'form_cpis_' . $route_name;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Obtener todos los cpi.
    $listado_cpis = sime_ajustes_get_all_cpi();

    $form['field_listado_cpis'] = [
      '#type' => 'select',
      '#title' => 'CPI',
      '#options' =>  $listado_cpis,
      '#required' => FALSE,
      '#multiple' => TRUE,
      '#description' => 'Seleccione uno o varios CPI. <br> <i>Sino selecciona ninguno, se mostrarán todos.</i>',
      '#attributes' => [
        'class' => ['listado-cpis-multiple']
      ],
    ];

    $form['actions']['#type'] = 'actions';

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => 'Mostrar',
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

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

}
