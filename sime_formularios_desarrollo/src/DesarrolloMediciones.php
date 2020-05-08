<?php

namespace Drupal\sime_formularios_desarrollo;

use Drupal\Core\Controller\ControllerBase;
use Drupal\sime_salidas\UtilityTrait;

class DesarrolloMediciones extends ControllerBase {

  use UtilityTrait;

  protected $cpis = NULL;

  protected $total_ninos = 0;

  protected $mediciones = [];

  protected $mediciones_names = ['primera_medicion', 'ultima_medicion'];

  protected $edades = [
    '0 a 11 m 29 días',
    '1 año a 1 año 11 meses 29 d',
    '2 años a 2 años 11 m 29 d',
    '3 años a 3 años 11 m 29d',
    '4 años a  4 años 11 m 29 d',
  ];

  public function get_desarrollo_mediciones() {
    foreach ($this->mediciones_names as $medicion) {
      $node_field  = 'node__field_' . $medicion;
      $field_value = 'rez.field_'   . $medicion . '_value';
      $query = db_select($node_field, 'rez');
      //Ninos menores de 1 anno.
      $query->addExpression("SUM(IF(TIMESTAMPDIFF(YEAR, fechnac.field_fecha_de_nacimiento_value, CURDATE()) < 1 AND genero.field_sexo_value = 'hombre', 1, 0))", 'ninos_1');
      $query->addExpression("SUM(IF(TIMESTAMPDIFF(YEAR, fechnac.field_fecha_de_nacimiento_value, CURDATE()) < 1 AND genero.field_sexo_value = 'mujer', 1, 0))", 'ninas_1');
      $query->addExpression("SUM(IF(TIMESTAMPDIFF(YEAR, fechnac.field_fecha_de_nacimiento_value, CURDATE()) < 1 AND $field_value = 'con_rezago', 1, 0))", 'total_ninos_rezago_1');
      $query->addExpression("SUM(IF(TIMESTAMPDIFF(YEAR, fechnac.field_fecha_de_nacimiento_value, CURDATE()) < 1 AND genero.field_sexo_value = 'hombre' AND $field_value = 'con_rezago', 1, 0))", 'hombre_rezago_1');
      $query->addExpression("SUM(IF(TIMESTAMPDIFF(YEAR, fechnac.field_fecha_de_nacimiento_value, CURDATE()) < 1 AND genero.field_sexo_value = 'mujer' AND $field_value = 'con_rezago', 1, 0))", 'mujer_rezago_1');

      //Ninos de 1 a 2 annos.
      $query->addExpression("SUM(IF(TIMESTAMPDIFF(YEAR, fechnac.field_fecha_de_nacimiento_value, CURDATE()) >= 1 AND TIMESTAMPDIFF(YEAR, fechnac.field_fecha_de_nacimiento_value, CURDATE()) <= 2 AND genero.field_sexo_value = 'mujer', 1, 0))", 'ninas_1_2');
      $query->addExpression("SUM(IF(TIMESTAMPDIFF(YEAR, fechnac.field_fecha_de_nacimiento_value, CURDATE()) >= 1 AND TIMESTAMPDIFF(YEAR, fechnac.field_fecha_de_nacimiento_value, CURDATE()) <= 2 AND genero.field_sexo_value = 'hombre', 1, 0))", 'ninos_1_2');
      $query->addExpression("SUM(IF(TIMESTAMPDIFF(YEAR, fechnac.field_fecha_de_nacimiento_value, CURDATE()) >= 1 AND TIMESTAMPDIFF(YEAR, fechnac.field_fecha_de_nacimiento_value, CURDATE()) <= 2 AND $field_value = 'con_rezago', 1, 0))", 'total_ninos_rezago_1_2');
      $query->addExpression("SUM(IF(TIMESTAMPDIFF(YEAR, fechnac.field_fecha_de_nacimiento_value, CURDATE()) >= 1 AND TIMESTAMPDIFF(YEAR, fechnac.field_fecha_de_nacimiento_value, CURDATE()) <= 2 AND genero.field_sexo_value = 'mujer' AND $field_value = 'con_rezago', 1, 0))", 'mujer_rezago_1_2');
      $query->addExpression("SUM(IF(TIMESTAMPDIFF(YEAR, fechnac.field_fecha_de_nacimiento_value, CURDATE()) >= 1 AND TIMESTAMPDIFF(YEAR, fechnac.field_fecha_de_nacimiento_value, CURDATE()) <= 2 AND genero.field_sexo_value = 'hombre' AND $field_value = 'con_rezago', 1, 0))", 'hombre_rezago_1_2');

      //Ninos de 2 a 3 annos.
      $query->addExpression("SUM(IF(TIMESTAMPDIFF(YEAR, fechnac.field_fecha_de_nacimiento_value, CURDATE()) > 2 AND TIMESTAMPDIFF(YEAR, fechnac.field_fecha_de_nacimiento_value, CURDATE()) <= 3 AND genero.field_sexo_value = 'mujer', 1, 0))", 'ninas_2_3');
      $query->addExpression("SUM(IF(TIMESTAMPDIFF(YEAR, fechnac.field_fecha_de_nacimiento_value, CURDATE()) > 2 AND TIMESTAMPDIFF(YEAR, fechnac.field_fecha_de_nacimiento_value, CURDATE()) <= 3 AND genero.field_sexo_value = 'hombre', 1, 0))", 'ninos_2_3');
      $query->addExpression("SUM(IF(TIMESTAMPDIFF(YEAR, fechnac.field_fecha_de_nacimiento_value, CURDATE()) > 2 AND TIMESTAMPDIFF(YEAR, fechnac.field_fecha_de_nacimiento_value, CURDATE()) <= 3 AND $field_value = 'con_rezago', 1, 0))", 'total_ninos_rezago_2_3');
      $query->addExpression("SUM(IF(TIMESTAMPDIFF(YEAR, fechnac.field_fecha_de_nacimiento_value, CURDATE()) > 2 AND TIMESTAMPDIFF(YEAR, fechnac.field_fecha_de_nacimiento_value, CURDATE()) <= 3 AND genero.field_sexo_value = 'mujer' AND $field_value = 'con_rezago', 1, 0))", 'mujer_rezago_2_3');
      $query->addExpression("SUM(IF(TIMESTAMPDIFF(YEAR, fechnac.field_fecha_de_nacimiento_value, CURDATE()) > 2 AND TIMESTAMPDIFF(YEAR, fechnac.field_fecha_de_nacimiento_value, CURDATE()) <= 3 AND genero.field_sexo_value = 'hombre' AND $field_value = 'con_rezago', 1, 0))", 'hombre_rezago_2_3');

      //Ninos de 3 a 4 annos.
      $query->addExpression("SUM(IF(TIMESTAMPDIFF(YEAR, fechnac.field_fecha_de_nacimiento_value, CURDATE()) > 3 AND TIMESTAMPDIFF(YEAR, fechnac.field_fecha_de_nacimiento_value, CURDATE()) <= 4 AND genero.field_sexo_value = 'mujer', 1, 0))", 'ninas_3_4');
      $query->addExpression("SUM(IF(TIMESTAMPDIFF(YEAR, fechnac.field_fecha_de_nacimiento_value, CURDATE()) > 3 AND TIMESTAMPDIFF(YEAR, fechnac.field_fecha_de_nacimiento_value, CURDATE()) <= 4 AND genero.field_sexo_value = 'hombre', 1, 0))", 'ninos_3_4');
      $query->addExpression("SUM(IF(TIMESTAMPDIFF(YEAR, fechnac.field_fecha_de_nacimiento_value, CURDATE()) > 3 AND TIMESTAMPDIFF(YEAR, fechnac.field_fecha_de_nacimiento_value, CURDATE()) <= 4 AND $field_value = 'con_rezago', 1, 0))", 'total_ninos_rezago_3_4');
      $query->addExpression("SUM(IF(TIMESTAMPDIFF(YEAR, fechnac.field_fecha_de_nacimiento_value, CURDATE()) > 3 AND TIMESTAMPDIFF(YEAR, fechnac.field_fecha_de_nacimiento_value, CURDATE()) <= 4 AND genero.field_sexo_value = 'mujer' AND $field_value = 'con_rezago', 1, 0))", 'mujer_rezago_3_4');
      $query->addExpression("SUM(IF(TIMESTAMPDIFF(YEAR, fechnac.field_fecha_de_nacimiento_value, CURDATE()) > 3 AND TIMESTAMPDIFF(YEAR, fechnac.field_fecha_de_nacimiento_value, CURDATE()) <= 4 AND genero.field_sexo_value = 'hombre' AND $field_value = 'con_rezago', 1, 0))", 'hombre_rezago_3_4');

      //Ninos mayores a 4 annos.
      $query->addExpression("SUM(IF(TIMESTAMPDIFF(YEAR, fechnac.field_fecha_de_nacimiento_value, CURDATE()) > 4 AND genero.field_sexo_value = 'mujer', 1, 0))", 'ninas_4');
      $query->addExpression("SUM(IF(TIMESTAMPDIFF(YEAR, fechnac.field_fecha_de_nacimiento_value, CURDATE()) > 4 AND genero.field_sexo_value = 'hombre', 1, 0))", 'ninos_4');
      $query->addExpression("SUM(IF(TIMESTAMPDIFF(YEAR, fechnac.field_fecha_de_nacimiento_value, CURDATE()) > 4 AND $field_value = 'con_rezago', 1, 0))", 'total_ninos_rezago_4');
      $query->addExpression("SUM(IF(TIMESTAMPDIFF(YEAR, fechnac.field_fecha_de_nacimiento_value, CURDATE()) > 4 AND genero.field_sexo_value = 'mujer' AND $field_value = 'con_rezago', 1, 0))", 'mujer_rezago_4');
      $query->addExpression("SUM(IF(TIMESTAMPDIFF(YEAR, fechnac.field_fecha_de_nacimiento_value, CURDATE()) > 4 AND genero.field_sexo_value = 'hombre' AND $field_value = 'con_rezago', 1, 0))", 'hombre_rezago_4');

      //$query->addExpression("count(*)", 'cantidad');
      $query->leftJoin('node__field_datos_personales', 'dp', 'rez.bundle = dp.bundle AND rez.entity_id = dp.entity_id and rez.revision_id = dp.revision_id');
      $query->leftJoin('node__field_cpi', 'cpi', 'rez.bundle = cpi.bundle AND rez.entity_id = cpi.entity_id and rez.revision_id = cpi.revision_id');
      $query->leftJoin('node__field_inscripto', 'ins', 'ins.entity_id = cpi.entity_id AND ins.revision_id = cpi.revision_id');
      $query->leftJoin('paragraph__field_fecha_de_nacimiento', 'fechnac', 'dp.field_datos_personales_target_id = fechnac.entity_id AND dp.field_datos_personales_target_revision_id = fechnac.revision_id');
      $query->leftJoin('paragraph__field_sexo', 'genero', 'dp.field_datos_personales_target_id = genero.entity_id AND dp.field_datos_personales_target_revision_id = genero.revision_id');
      $query->isNotNull('fechnac.field_fecha_de_nacimiento_value');
      $query->condition('cpi.field_cpi_target_id', $this->cpis, 'IN');
      $query->condition('ins.field_inscripto_value', 1);
      $ninos = $query->execute()->fetchAll();

      if (isset($ninos[0])) {
        $ninos = (array ) reset($ninos);
      }

      $info_ninos = array_chunk($ninos, 5, TRUE);
      if (!empty($info_ninos)) {
        $this->formar_filas($this->mediciones, $info_ninos[0], '1',   0, $medicion);
        $this->formar_filas($this->mediciones, $info_ninos[1], '1_2', 1, $medicion);
        $this->formar_filas($this->mediciones, $info_ninos[2], '2_3', 2, $medicion);
        $this->formar_filas($this->mediciones, $info_ninos[3], '3_4', 3, $medicion);
        $this->formar_filas($this->mediciones, $info_ninos[4], '4',   4, $medicion);
      }
    }

    if (!$this->mediciones) {
      drupal_set_message('No se encontraron datos para mostrar', 'warning');
      return [];
    }

    $tabla   = $this->generar_tabla($this->mediciones_names[0]);
    $tabla[] = $this->generar_tabla($this->mediciones_names[1]);
    $tabla[] = $this->generar_tabla_diff();
    return $tabla;
  }

  protected function formar_filas(array &$mediciones, $info_ninos, $suffix, $index, $medicion) {
    if (empty($info_ninos)) {
      return $mediciones;
    }
    $ninos = "ninos_$suffix";
    $ninas = "ninas_$suffix";
    $total_ninos_rezago = "total_ninos_rezago_$suffix";
    $hombre_rezago = "hombre_rezago_$suffix";
    $mujer_rezago = "mujer_rezago_$suffix";
    foreach ($info_ninos as $nino) {
      if ($info_ninos[$ninos] + $info_ninos[$ninas] > 0) {
        $mediciones[$this->edades[$index]][$medicion] = [
          'total_grupo'   => $info_ninos[$ninos] + $info_ninos[$ninas],
          'cant_hombre'   => $info_ninos[$ninos],
          'cant_hombre_porciento'   => ($info_ninos[$ninos] + $info_ninos[$ninas]) > 0 ? round($info_ninos[$ninos] * 100 / ($info_ninos[$ninos] + $info_ninos[$ninas]), 1) : 0,
          'cant_mujer'    => $info_ninos[$ninas],
          'cant_mujer_porciento'   => ($info_ninos[$ninos] + $info_ninos[$ninas]) > 0 ? round($info_ninos[$ninas] * 100 / ($info_ninos[$ninos] + $info_ninos[$ninas]), 1) : 0,
          'total_rezago'  => $info_ninos[$total_ninos_rezago],
          'total_rezago_porciento'  => ($info_ninos[$ninos] + $info_ninos[$ninas]) > 0 ? round($info_ninos[$total_ninos_rezago] * 100 / ($info_ninos[$ninos] + $info_ninos[$ninas]), 1) : 0,
          'hombre_rezago' => $info_ninos[$hombre_rezago],
          'hombre_rezago_porciento' => $info_ninos[$total_ninos_rezago] > 0 ? round($info_ninos[$hombre_rezago] * 100 / $info_ninos[$total_ninos_rezago], 1) : 0,
          'mujer_rezago'  => $info_ninos[$mujer_rezago],
          'mujer_rezago_porciento'  => $info_ninos[$total_ninos_rezago] > 0 ? round($info_ninos[$mujer_rezago] * 100 / $info_ninos[$total_ninos_rezago], 1) : 0,
        ];
      }
    }
    unset($mediciones[$index]);
    return $mediciones;
  }

  protected function generar_tabla($medicion) {
    if (empty($this->mediciones)) {
      return $this->mediciones;
    }

    $header = [
      'row1' => [
        [
          'data' => 'Edades',
          'rowspan' => 2,
        ],
        [
          'data' => 'Total por grupo de edad',
          'rowspan' => 2,
        ],
        [
          'data' => 'Sexo',
          'colspan' => 4,
        ],
        [
          'data' => 'Rezago según edad',
          'colspan' => 2,
        ],
        [
          'data' => 'Rezago según sexo',
          'colspan' => 4,
        ],
      ],
      'row2' => [
        'M(N°)', 'M(%)', 'F(N°)', 'F(%)', 'N°', '%', 'M(N°)', 'M(%)', 'F(N°)', 'F(%)'
      ]
    ];

    $medicion_title = $medicion == 'primera_medicion' ? 'Primera medición' : 'Ultima medición';
    $tabla['mediciones'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('@medicion', ['@medicion' => $medicion_title]),
      '#attributes' => [
        'class' => [
          'fieldset-desarrollo-mediciones',
          ],
      ],
    ];

    $tabla['mediciones']['nino'] = [
      '#type' => 'table',
      '#header' => $header,
      '#header_multilevel' => TRUE,
      '#empty' => 'No se encontraron resultados.',
      '#attributes' => [
        'class' => [
          'cuadro-rezago',
          ],
      ],
    ];

    $index = 0;
    foreach ($this->mediciones as $edad => $mediciones) {
      $tabla['mediciones']['nino'][$index]['#attributes'] = [
        'class' => ['foo','baz'],
      ];
      $tabla['mediciones']['nino'][$index]['edades'] = [
        '#type' => 'item',
        '#markup' => $edad,
        '#wrapper_attributes' => [
          'class' => '',
          ],
      ];
/*      if ($medicion == 'ultima_medicion' && $this->mediciones[$edad]['ultima_medicion']['total_rezago'] <= 0) {
        $tabla['mediciones']['nino'][$index++]['null'] = [
          '#type' => 'item',
          '#markup' => 'No hay segundas mediciones para mostrar datos.',
        ];
        continue;
      }*/
      $tabla['mediciones']['nino'][$index]['grupo_edad'] = [
        '#type' => 'item',
        '#markup' => $this->mediciones[$edad][$medicion]['total_grupo'],
      ];
      $tabla['mediciones']['nino'][$index]['hombre'] = [
        '#type' => 'item',
        '#markup' => $this->mediciones[$edad][$medicion]['cant_hombre'],
      ];
      $tabla['mediciones']['nino'][$index]['hombre_porciento'] = [
        '#type' => 'item',
        '#markup' => $this->mediciones[$edad][$medicion]['cant_hombre_porciento'] . ' %',
      ];
      $tabla['mediciones']['nino'][$index]['mujer'] = [
        '#type' => 'item',
        '#markup' => $this->mediciones[$edad][$medicion]['cant_mujer'],
      ];
      $tabla['mediciones']['nino'][$index]['mujer_porciento'] = [
        '#type' => 'item',
        '#markup' => $this->mediciones[$edad][$medicion]['cant_mujer_porciento'] . ' %',
      ];
      $tabla['mediciones']['nino'][$index]['rezago'] = [
        '#type' => 'item',
        '#markup' => $this->mediciones[$edad][$medicion]['total_rezago'],
      ];
      $tabla['mediciones']['nino'][$index]['rezago_porciento'] = [
        '#type' => 'item',
        '#markup' => $this->mediciones[$edad][$medicion]['total_rezago_porciento'] . ' %',
      ];
      $tabla['mediciones']['nino'][$index]['rezago_hombre'] = [
        '#type' => 'item',
        '#markup' => $this->mediciones[$edad][$medicion]['hombre_rezago'],
      ];
      $tabla['mediciones']['nino'][$index]['rezago_hombre_porciento'] = [
        '#type' => 'item',
        '#markup' => $this->mediciones[$edad][$medicion]['hombre_rezago_porciento'] . ' %',
      ];
      $tabla['mediciones']['nino'][$index]['rezago_mujer'] = [
        '#type' => 'item',
        '#markup' => $this->mediciones[$edad][$medicion]['mujer_rezago'],
      ];
      $tabla['mediciones']['nino'][$index]['rezago_mujer_porciento'] = [
        '#type' => 'item',
        '#markup' => $this->mediciones[$edad][$medicion]['mujer_rezago_porciento'] . ' %',
      ];
      $index++;
    }
    return $tabla;
  }

  protected function generar_tabla_diff() {
    if (empty($this->mediciones)) {
      return $this->mediciones;
    }

    $header = [
      'row1' => [
        [
          'data' => 'Edades',
          'rowspan' => 2,
        ],
        [
          'data' => 'Proporción de Rezago según edad',
          'colspan' => 2,
        ],
        [
          'data' => 'Diferencia en la prevalencia de rezago según edad',
          'rowspan' => 2,
        ],
        [
          'data' => 'Proporción de Rezago según edad y sexo',
          'colspan' => 2,
        ],
        [
          'data' => 'Proporción de Rezago según edad y sexo',
          'colspan' => 2,
        ],
        [
          'data' => 'Diferencia en la prevalencia de rezago según edad y sexo',
          'colspan' => 2,
        ],
      ],
      'row2' => [
        'Primera medición', 'Segunda medición', 'Primera medición (M)', 'Segunda medición (M)', 'Primera medición (F)', 'Segunda medición (F)', 'Diferencia (M)', 'Diferencia (F)'
      ]
    ];

    $tabla['mediciones'] = [
      '#type' => 'fieldset',
      '#title' => 'Delta o diferencia entre mediciones 1 y 2',
      '#attributes' => [
        'class' => [
          'fieldset-desarrollo-mediciones',
          ],
      ],
    ];

    $tabla['mediciones']['nino'] = [
      '#type' => 'table',
      '#header' => $header,
      '#header_multilevel' => TRUE,
      '#empty' => 'No se encontraron resultados.',
      '#attributes' => [
        'class' => [
          'cuadro-rezago',
          ],
      ],
    ];

    $index = 0;
    foreach ($this->mediciones as $edad => $mediciones) {
      $tabla['mediciones']['nino'][$index]['#attributes'] = [
        'class' => ['foo','baz'],
      ];
      $tabla['mediciones']['nino'][$index]['edades'] = [
        '#type' => 'item',
        '#markup' => $edad,
        '#wrapper_attributes' => [
          'class' => '',
          ],
      ];
/*      if ($this->mediciones[$edad]['ultima_medicion']['total_rezago'] <= 0) {
        $tabla['mediciones']['nino'][$index++]['null'] = [
          '#type' => 'item',
          '#markup' => 'No hay segundas mediciones para mostrar datos.',
        ];
        continue;
      }*/
      $diff_edad = $this->mediciones[$edad]['ultima_medicion']['total_rezago_porciento'] - $this->mediciones[$edad]['primera_medicion']['total_rezago_porciento'];

      $diff_m = $this->mediciones[$edad]['ultima_medicion']['hombre_rezago_porciento'] - $this->mediciones[$edad]['primera_medicion']['hombre_rezago_porciento'];

      $diff_f = $this->mediciones[$edad]['ultima_medicion']['mujer_rezago_porciento'] - $this->mediciones[$edad]['primera_medicion']['mujer_rezago_porciento'];

      $tabla['mediciones']['nino'][$index]['primera_medicion_edad'] = [
        '#type' => 'item',
        '#markup' => $this->mediciones[$edad]['primera_medicion']['total_rezago_porciento'] . ' %',
      ];
      $tabla['mediciones']['nino'][$index]['segunda_medicion_edad'] = [
        '#type' => 'item',
        '#markup' => $this->mediciones[$edad]['ultima_medicion']['total_rezago_porciento'] . ' %',
      ];
      $tabla['mediciones']['nino'][$index]['diff_edad'] = [
        '#type' => 'item',
        '#markup' => $diff_edad . ' %',
      ];
      $tabla['mediciones']['nino'][$index]['primera_medicion_m'] = [
        '#type' => 'item',
        '#markup' => $this->mediciones[$edad]['primera_medicion']['hombre_rezago_porciento'] . ' %',
      ];
      $tabla['mediciones']['nino'][$index]['segunda_medicion_m'] = [
        '#type' => 'item',
        '#markup' => $this->mediciones[$edad]['ultima_medicion']['hombre_rezago_porciento'] . ' %',
      ];
      $tabla['mediciones']['nino'][$index]['primera_medicion_f'] = [
        '#type' => 'item',
        '#markup' => $this->mediciones[$edad]['primera_medicion']['mujer_rezago_porciento'] . ' %',
      ];
      $tabla['mediciones']['nino'][$index]['segunda_medicion_f'] = [
        '#type' => 'item',
        '#markup' => $this->mediciones[$edad]['ultima_medicion']['mujer_rezago_porciento'] . ' %',
      ];
      $tabla['mediciones']['nino'][$index]['diff_m'] = [
        '#type' => 'item',
        '#markup' => $diff_m . ' %',
      ];
      $tabla['mediciones']['nino'][$index]['diff_f'] = [
        '#type' => 'item',
        '#markup' => $diff_f . ' %',
      ];
      $index++;
    }

    return $tabla;
  }

}
