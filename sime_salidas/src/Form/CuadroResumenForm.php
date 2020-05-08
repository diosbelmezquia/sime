<?php

namespace Drupal\sime_salidas\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;

class CuadroResumenForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'sime_salidas_resumen_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['field_edad'] = [
      '#type' => 'select',
      '#title' => 'Edad',
      '#options' =>  ['Menos 1 año', '1 año', '2 años', '3 años', '4 años', '5 años'],
      '#required' => FALSE,
      '#multiple' => TRUE,
      '#description' => 'Seleccione la edad',
    ];

    $form['field_genero'] = [
      '#type' => 'select',
      '#title' => 'Género',
      '#options' =>  [
        'hombre' => 'Masculino',
        'mujer' => 'Femenino'
        ],
      '#required' => FALSE,
      '#multiple' => TRUE,
      '#description' => 'Seleccione el género',
    ];

    $form['actions']['#type'] = 'actions';

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => 'Mostrar',
      '#button_type' => 'primary',
      '#access' => TRUE,
    ];

    $form['texto'] = [
      '#type' => 'html_tag',
      '#tag' => 'p',
      '#value' => '<i>Sino selecciona ninguno, se mostrarán todos.</i>',
      '#attributes' => [
        'class' => [
          'texto-cuadro-resumen',
        ],
      ],
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
