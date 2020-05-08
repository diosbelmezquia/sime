<?php

namespace Drupal\sime_salidas\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class SemestralController extends ControllerBase {
  protected $diagnosticos = [];

  protected $cpis = NULL;

  protected $nombre_real = [
    'normal'       => 'Normal',
    'normal_bt'    => 'Normal c/ baja talla',
    'riesgo_bp'    => 'Riesgo de bajo peso',
    'riesgo_bp_bt' => 'Riesgo de bajo peso c/ baja talla',
    'bajo_peso'    => 'Bajo peso',
    'bajo_peso_bt' => 'Bajo peso c/ baja talla',
    'sobrepeso'    => 'Sobrepeso',
    'sobrepeso_bt' => 'Sobrepeso c/ baja talla',
    'obesidad'     => 'Obesidad',
    'obesidad_bt'  => 'Obesidad c/ baja talla',
    '_none'        => 'Sin diagnósticos',
  ];

  // Obtener formulario.
  protected function view() {
    $route_name = \Drupal::routeMatch()->getRouteName();
    $cpis = \Drupal::request()->query->get('c');
    $this->cpis = array_keys(sime_ajustes_get_all_cpi());
    if ($cpis) {
      $this->cpis = $cpis;
    }
    $block['form'] = $this->formBuilder()->getForm('\Drupal\sime_salidas\Form\CpiMultipleForm');
    if ($this->cpis && $cpis) {
      $block['form']['field_listado_cpis']['#value'] = $this->cpis;
    }

    $query = db_select('node__field_diagnostico', 'd');
    $query->addExpression("d.field_diagnostico_value", 'diagnostico');

    if ($route_name == 'sime_semestral_uno') {
      $query->addExpression("SUM(IF(TIMESTAMPDIFF(YEAR, fechnac.field_fecha_de_nacimiento_value, CURDATE()) < 2 AND genero.field_sexo_value = 'mujer', 1, 0))", 'ninas_2');
      $query->addExpression("SUM(IF(TIMESTAMPDIFF(YEAR, fechnac.field_fecha_de_nacimiento_value, CURDATE()) < 2 AND genero.field_sexo_value = 'hombre', 1, 0))", 'ninos_2');
      $query->addExpression("SUM(IF(TIMESTAMPDIFF(YEAR, fechnac.field_fecha_de_nacimiento_value, CURDATE()) >= 2 AND genero.field_sexo_value = 'mujer', 1, 0))", 'ninas_2_5');
      $query->addExpression("SUM(IF(TIMESTAMPDIFF(YEAR, fechnac.field_fecha_de_nacimiento_value, CURDATE()) >= 2 AND genero.field_sexo_value = 'hombre', 1, 0))", 'ninos_2_5');
    }
    elseif ($route_name == 'sime_semestral_dos') {
      $query->addExpression("SUM(IF(TIMESTAMPDIFF(YEAR, fechnac.field_fecha_de_nacimiento_value, CURDATE()) < 1 AND genero.field_sexo_value = 'mujer', 1, 0))", 'ninas_1');
      $query->addExpression("SUM(IF(TIMESTAMPDIFF(YEAR, fechnac.field_fecha_de_nacimiento_value, CURDATE()) < 1 AND genero.field_sexo_value = 'hombre', 1, 0))", 'ninos_1');
      $query->addExpression("SUM(IF(TIMESTAMPDIFF(YEAR, fechnac.field_fecha_de_nacimiento_value, CURDATE()) >= 1 AND TIMESTAMPDIFF(YEAR, fechnac.field_fecha_de_nacimiento_value, CURDATE()) <= 2 AND genero.field_sexo_value = 'mujer', 1, 0))", 'ninas_1_2');
      $query->addExpression("SUM(IF(TIMESTAMPDIFF(YEAR, fechnac.field_fecha_de_nacimiento_value, CURDATE()) >= 1 AND TIMESTAMPDIFF(YEAR, fechnac.field_fecha_de_nacimiento_value, CURDATE()) <= 2 AND genero.field_sexo_value = 'hombre', 1, 0))", 'ninos_1_2');
      $query->addExpression("SUM(IF(TIMESTAMPDIFF(YEAR, fechnac.field_fecha_de_nacimiento_value, CURDATE()) > 2 AND TIMESTAMPDIFF(YEAR, fechnac.field_fecha_de_nacimiento_value, CURDATE()) <= 3 AND genero.field_sexo_value = 'mujer', 1, 0))", 'ninas_2_3');
      $query->addExpression("SUM(IF(TIMESTAMPDIFF(YEAR, fechnac.field_fecha_de_nacimiento_value, CURDATE()) > 2 AND TIMESTAMPDIFF(YEAR, fechnac.field_fecha_de_nacimiento_value, CURDATE()) <= 3 AND genero.field_sexo_value = 'hombre', 1, 0))", 'ninos_2_3');
      $query->addExpression("SUM(IF(TIMESTAMPDIFF(YEAR, fechnac.field_fecha_de_nacimiento_value, CURDATE()) > 3 AND TIMESTAMPDIFF(YEAR, fechnac.field_fecha_de_nacimiento_value, CURDATE()) <= 4 AND genero.field_sexo_value = 'mujer', 1, 0))", 'ninas_3_4');
      $query->addExpression("SUM(IF(TIMESTAMPDIFF(YEAR, fechnac.field_fecha_de_nacimiento_value, CURDATE()) > 3 AND TIMESTAMPDIFF(YEAR, fechnac.field_fecha_de_nacimiento_value, CURDATE()) <= 4 AND genero.field_sexo_value = 'hombre', 1, 0))", 'ninos_3_4');
      $query->addExpression("SUM(IF(TIMESTAMPDIFF(YEAR, fechnac.field_fecha_de_nacimiento_value, CURDATE()) > 4 AND genero.field_sexo_value = 'mujer', 1, 0))", 'ninas_4');
      $query->addExpression("SUM(IF(TIMESTAMPDIFF(YEAR, fechnac.field_fecha_de_nacimiento_value, CURDATE()) > 4 AND genero.field_sexo_value = 'hombre', 1, 0))", 'ninos_4');
    }

    $query->addExpression("count(*)", 'cantidad');
    $query->leftJoin('node__field_datos_personales', 'dp', 'd.bundle = dp.bundle AND d.entity_id = dp.entity_id and d.revision_id = dp.revision_id');
    $query->leftJoin('node__field_cpi', 'cpi', 'd.bundle = cpi.bundle AND d.entity_id = cpi.entity_id and d.revision_id = cpi.revision_id');
    $query->leftJoin('node__field_inscripto', 'ins', 'ins.entity_id = cpi.entity_id AND ins.revision_id = cpi.revision_id');
    $query->leftJoin('paragraph__field_fecha_de_nacimiento', 'fechnac', 'dp.field_datos_personales_target_id = fechnac.entity_id AND dp.field_datos_personales_target_revision_id = fechnac.revision_id');
    $query->leftJoin('paragraph__field_sexo', 'genero', 'dp.field_datos_personales_target_id = genero.entity_id AND dp.field_datos_personales_target_revision_id = genero.revision_id');
    $query->isNotNull('fechnac.field_fecha_de_nacimiento_value');
    $query->condition('cpi.field_cpi_target_id', $this->cpis, 'IN');
    $query->condition('ins.field_inscripto_value', 1);
    $query->groupBy('d.field_diagnostico_value');
    $ninos = $query->execute()->fetchAllAssoc('diagnostico');

    if (!$ninos) {
      drupal_set_message('No se encontraron niños para mostrar', 'warning');
    }

    $diagnosticos = [];

    // Obtener el total de ninos inscriptos de los cpis seleccionados.
    $total_ninos = sime_ajustes_total_ninos_cpi();
    $diagnosticos['total_ninos'] = 0;
    if (!empty($total_ninos) && isset($this->cpis) && !empty($this->cpis)) {
      $total_ninos = array_filter($total_ninos,
        function ($index) {
        return in_array($index, $this->cpis);
      }, ARRAY_FILTER_USE_KEY);
      $total_ninos = array_sum(array_column($total_ninos, 'total'));
      $diagnosticos['total_ninos'] = $total_ninos;
    }

    if ($route_name == 'sime_semestral_uno') {
      foreach ($ninos as $nino) {
        $diagnosticos[$nino->diagnostico] = [
          'ninas_2'    => $nino->ninas_2,
          'ninos_2'    => $nino->ninos_2,
          'ninas_2_5'  => $nino->ninas_2_5,
          'ninos_2_5'  => $nino->ninos_2_5,
          'cantidad'   => $nino->cantidad,
        ];
      }
    }
    elseif ($route_name == 'sime_semestral_dos') {
      foreach ($ninos as $nino) {
        $diagnosticos[$nino->diagnostico] = [
          'ninas_1'    => $nino->ninas_1,
          'ninos_1'    => $nino->ninos_1,
          'ninas_1_2'  => $nino->ninas_1_2,
          'ninos_1_2'  => $nino->ninos_1_2,
          'ninas_2_3'  => $nino->ninas_2_3,
          'ninos_2_3'  => $nino->ninos_2_3,
          'ninas_3_4'  => $nino->ninas_3_4,
          'ninos_3_4'  => $nino->ninos_3_4,
          'ninas_4'    => $nino->ninas_4,
          'ninos_4'    => $nino->ninos_4,
          'cantidad'   => $nino->cantidad,
        ];
      }
    }

    $this->diagnosticos = $diagnosticos;
    return $block;
  }

  //Obtener color de la celda del diagnostico.
  public function sime_ajustes_color_td($diagnostico) {
    $d = array_keys($this->nombre_real);
    if ($diagnostico == $d[2] || $diagnostico == $d[3]) {
      return 'amarillo';
    }
    elseif($diagnostico == $d[4] || $diagnostico == $d[5]) {
      return 'rojo';
    }
    elseif ($diagnostico == $d[6] || $diagnostico == $d[7]) {
      return 'verde';
    }
    elseif ($diagnostico == $d[8] || $diagnostico == $d[9]) {
      return 'pasta';
    }
    else {
      return '';
    }
  }

  // On submit form.
  public static function sime_salidas_semestral_form_submit(&$form, FormStateInterface $form_state) {
    $cpis = $form_state->getValue('field_listado_cpis');
    $route_name = \Drupal::routeMatch()->getRouteName();
    $response = new RedirectResponse(\Drupal::url($route_name, [
      'c' => $cpis,
    ]));
    $response->send();
  }
}
