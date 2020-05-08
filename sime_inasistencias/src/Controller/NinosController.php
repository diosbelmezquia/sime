<?php

namespace Drupal\sime_inasistencias\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;

class NinosController extends ControllerBase {

  // Obtener formulario.
  public function view() {
    $block['form_ninos'] = $this->formBuilder()->getForm('\Drupal\sime_inasistencias\Form\InasistenciaForm');
    $block['form_listado'] = $this->formBuilder()->getForm('\Drupal\sime_inasistencias\Form\InasistenciaListadoForm');
    return $block;
  }

  // On submit form.
  public static function sime_inasistencias_ninos_form_submit(&$form, FormStateInterface $form_state) {
    $cpi = $form_state->getValue('field_listado_cpis');
    $nombre = $form_state->getValue('field_nombre_inasistencias');
    $options = sime_inasistencias_get_salas($cpi, $nombre);
    $sala = $options['nombre_pertenece'];
    $fecha = $form_state->getValue('field_fecha_inasistencias');
    $ninos = sime_inasistencias_get_ninos($cpi, $sala, $nombre, $fecha);
    self::sime_inasistencias_send_data($cpi, $sala, $nombre, $fecha, $ninos);
  }

    // On submit form.
  public static function sime_inasistencias_listado_ninos_form_submit(&$form, FormStateInterface $form_state) {
    $ninos_inasistencias = $form_state->getValue('field_ninos_inasistencias');
        // Si hay ninos(rows).
    if ($ninos_inasistencias > 0) {
      foreach ($ninos_inasistencias as $nino_ausente) {
        $id_nino = $nino_ausente['node_nino'];
        $ausente = $nino_ausente['ausente'];
        $motivo = $nino_ausente['motivo'];
        $nino = Node::load($id_nino);
        $fecha = \Drupal::request()->query->get('fecha');
        if ($ausente) {
          sime_inasistencias_add_inasistencia($nino, $fecha, $motivo);
        }
        else {
          sime_inasistencias_remove_inasistencia($nino, $fecha);
        }
      }
      $cpi = \Drupal::request()->query->get('field_cpi');
      $sala  = \Drupal::request()->query->get('sala');
      $nombre  = \Drupal::request()->query->get('name');
      self::sime_inasistencias_send_data($cpi, $sala, $nombre, $fecha);
      drupal_set_message('Datos guardados correctamente.');
    }
    else {
      drupal_set_message('No se encontraron datos para guardar.', 'error');
    }
  }

  static function sime_inasistencias_send_data($cpi, $sala, $nombre, $fecha) {
    $route_name = \Drupal::routeMatch()->getRouteName();
    $response = new RedirectResponse(\Drupal::url($route_name, [
      'field_cpi' => $cpi,
      'sala' => $sala,
      'name' => $nombre,
      'fecha' => $fecha,
    ]));
    $response->send();
  }

}
