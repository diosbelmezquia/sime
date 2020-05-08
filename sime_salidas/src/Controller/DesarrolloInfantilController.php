<?php

namespace Drupal\sime_salidas\Controller;

use Drupal\sime_formularios_desarrollo\DesarrolloMediciones;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class DesarrolloInfantilController extends DesarrolloMediciones {

  public function mediciones() {
    $mediciones[] = $this->validar_cpis();
    $mediciones[] = $this->get_desarrollo_mediciones();
    return $mediciones;
  }

  // On submit form.
  public static function sime_salidas_desarrollo_form_submit(&$form, FormStateInterface $form_state) {
    $cpis = $form_state->getValue('field_listado_cpis');
    $route_name = \Drupal::routeMatch()->getRouteName();
    $response = new RedirectResponse(\Drupal::url($route_name, [
      'c' => $cpis,
    ]));
    $response->send();
  }

}
