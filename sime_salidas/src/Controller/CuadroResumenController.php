<?php

namespace Drupal\sime_salidas\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class CuadroResumenController extends ControllerBase {

  public function mostrar() {
    $cpis = sime_ajustes_get_all_cpi();
    $cpis_ids = array_keys($cpis);
    $block['form'] = $this->formBuilder()->getForm('\Drupal\sime_salidas\Form\CuadroResumenForm');
    $edades  = \Drupal::request()->query->get('e');
    $generos = \Drupal::request()->query->get('g');

    if ($edades) {
      $block['form']['field_edad']['#value'] = $edades;
    }
    else {
      $edades = array_keys($block['form']['field_edad']['#options']);
    }

    if ($generos) {
      $block['form']['field_genero']['#value'] = $generos;
    }
    else {
      $generos = array_keys($block['form']['field_genero']['#options']);
    }

    $query = db_query("SELECT
      comuna.field_comuna_value AS comuna,
      cpi_data.nid AS id_cpi,
      cpi_data.title AS nombre_cpi,
      SUM(IF(tc.field_tipo_caso_value = 'asi', 1, 0)) as asi,
      SUM(IF(tc.field_tipo_caso_value = 'maltrato', 1, 0)) as maltrato,
      SUM(IF(tc.field_tipo_caso_value = 'negligencia', 1, 0)) as negligencia,
      SUM(IF(tc.field_tipo_caso_value NOT IN ('asi', 'maltrato', 'negligencia'), 1, 0)) as otro,
      count(*) as total
    FROM
    -- table principal
      paragraph__field_tipo_caso tc
    -- paragraph casso more data
    LEFT JOIN paragraph__field_leido l ON tc.bundle = l.bundle and tc.entity_id = l.entity_id AND tc.revision_id = l.revision_id
    LEFT JOIN paragraph__field_tipo_informe i ON tc.bundle = i.bundle and tc.entity_id = i.entity_id AND tc.revision_id = i.revision_id
    -- node tables left joins
    LEFT JOIN node__field_casos c ON c.bundle = 'casos' AND tc.entity_id = c.field_casos_target_id AND tc.revision_id = c.field_casos_target_revision_id
    LEFT JOIN node__field_nino nino ON c.bundle = nino.bundle AND c.entity_id = nino.entity_id AND c.revision_id = nino.revision_id
    LEFT JOIN node__field_cpi cpi ON cpi.bundle = 'nino' AND nino.field_nino_target_id = cpi.entity_id
    LEFT JOIN node__field_inscripto ins ON ins.entity_id = cpi.entity_id AND ins.revision_id = cpi.revision_id
    LEFT JOIN node__field_comuna comuna ON comuna.bundle = 'cpi' AND comuna.entity_id = cpi.field_cpi_target_id
    LEFT JOIN node_field_data cpi_data ON cpi_data.type = 'cpi' AND cpi_data.nid = cpi.field_cpi_target_id
    -- left join del niño
    LEFT JOIN node__field_datos_personales dp on dp.bundle = 'nino' AND nino.field_nino_target_id = dp.entity_id
    LEFT JOIN paragraph__field_fecha_de_nacimiento fechnac ON dp.field_datos_personales_target_id = fechnac.entity_id AND dp.field_datos_personales_target_revision_id = fechnac.revision_id
    LEFT JOIN paragraph__field_sexo genero ON dp.field_datos_personales_target_id = genero.entity_id AND dp.field_datos_personales_target_revision_id = genero.revision_id
    WHERE l.field_leido_value = 0 AND i.field_tipo_informe_value = 'intervencion'
     AND cpi.field_cpi_target_id IN (:cpi[])
     AND genero.field_sexo_value IN (:sexo[])
     AND TIMESTAMPDIFF(YEAR, fechnac.field_fecha_de_nacimiento_value, CURDATE()) IN (:edad[])
     AND ins.field_inscripto_value = 1
    GROUP BY cpi_data.nid, comuna.field_comuna_value, cpi_data.title, cpi_data.nid
    ORDER BY comuna.field_comuna_value", [':cpi[]' => $cpis_ids, ':sexo[]' => $generos, ':edad[]' => $edades])->fetchAll();

    $header = ['Comuna', 'CPI', 'ASI', 'Maltrato', 'Neg', 'Otro', 'Total'];
    $block['resumen'] = [
        '#type' => 'table',
        '#header' => $header,
        '#empty' => 'No hay datos para mostrar.',
        '#attributes' => [
          'class' => [
            'cuadro-resumen',
            ],
        ],
      ];

    if (!empty($query)) {
      // Totales
      $total_asi = $total_mal = $total_neg = $total_otro = $total_total = 0;
      $sub_total_asi = $sub_total_mal = $sub_total_neg = $sub_total_otro = $sub_total_total = 0;
      $comuna_id = $query[0]->comuna;
      $row = 0;
      // Mostrar tabla desde la query.
      foreach ($query as $index => $cpi) {
        $nueva_fila = FALSE;
        $total_asi += $cpi->asi;
        $total_mal += $cpi->maltrato;
        $total_neg += $cpi->negligencia;
        $total_otro += $cpi->otro;
        $total_total += $cpi->total;

        if ($cpi->comuna == $comuna_id) {
          $sub_total_asi += $cpi->asi;
          $sub_total_mal += $cpi->maltrato;
          $sub_total_neg += $cpi->negligencia;
          $sub_total_otro += $cpi->otro;
          $sub_total_total += $cpi->total;
        }

        $node_cpi = Node::load($cpi->id_cpi);
        //Cargar nombre de la comuna del cpi actual.
        $comuna = $node_cpi->field_comuna;
        $comuna = $comuna->getFieldDefinition()->getFieldStorageDefinition()
          ->getOptionsProvider('value', $comuna->getEntity())->getPossibleOptions()[$comuna->value];

        $block['resumen'][$row]['#attributes'] = [
          'class' => ['foo','baz'],
        ];

        //Poner subtotal despues de que se acabe una comuna.
        if ($row != 0 && $cpi->comuna != $comuna_id) {
          $nueva_fila = TRUE;
          $row++;
          $block['resumen'][$row]['#attributes']['class'] = ['subtotal'];
          $block['resumen'][$row]['comuna']['#type'] = 'item';
          $block['resumen'][$row]['subtotal'] = [
            '#type' => 'item',
            '#markup' => 'Subtotal',
          ];
          $block['resumen'][$row]['subtotal_asi'] = [
            '#type' => 'item',
            '#markup' => $sub_total_asi,
          ];
          $block['resumen'][$row]['subtotal_maltrato'] = [
            '#type' => 'item',
            '#markup' => $sub_total_mal,
          ];
          $block['resumen'][$row]['subtotal_neg'] = [
            '#type' => 'item',
            '#markup' => $sub_total_neg,
          ];
          $block['resumen'][$row]['subtotal_otro'] = [
            '#type' => 'item',
            '#markup' => $sub_total_otro,
          ];
          $block['resumen'][$row]['subtotal_total'] = [
            '#type' => 'item',
            '#markup' => $sub_total_total,
          ];
          $row++;
        }
        //Seguir agregando CPIs despues del subtotal de arriba (en nueva row).
        if (!$nueva_fila) {
          $row++;
        }
        $block['resumen'][$row]['comuna'] = [
          '#type' => 'item',
          '#markup' => $comuna,
          '#wrapper_attributes' =>[
            'class' => 'items-comunas',
            ],
        ];
        $block['resumen'][$row]['cpi'] = [
          '#type' => 'item',
          '#markup' => $cpi->nombre_cpi,
        ];
        $block['resumen'][$row]['asi'] = [
          '#type' => 'item',
          '#markup' => $cpi->asi,
        ];
        $block['resumen'][$row]['maltrato'] = [
          '#type' => 'item',
          '#markup' => $cpi->maltrato,
        ];
        $block['resumen'][$row]['neg'] = [
          '#type' => 'item',
          '#markup' => $cpi->negligencia,
        ];
        $block['resumen'][$row]['otro'] = [
          '#type' => 'item',
          '#markup' => $cpi->otro,
        ];
        $block['resumen'][$row]['total'] = [
          '#type' => 'item',
          '#markup' => $cpi->total,
        ];
        if ($cpi->comuna != $comuna_id) {
          $sub_total_asi = $sub_total_mal = $sub_total_neg = 0;
          $sub_total_otro = $sub_total_total = 0;
          $sub_total_asi += $cpi->asi;
          $sub_total_mal += $cpi->maltrato;
          $sub_total_neg += $cpi->negligencia;
          $sub_total_otro += $cpi->otro;
          $sub_total_total += $cpi->total;
        }

        //Insertar un subtotal en la ultima comuna de la tabla.
        if (count($query) - 1 == $index) {
          $last_subtotal = $row;
          $last_subtotal++;
          $block['resumen'][$last_subtotal]['#attributes']['class'] = ['subtotal'];
          $block['resumen'][$last_subtotal]['comuna']['#type'] = 'item';
          $block['resumen'][$last_subtotal]['subtotal'] = [
            '#type' => 'item',
            '#markup' => 'Subtotal',
          ];
          $block['resumen'][$last_subtotal]['subtotal_asi'] = [
            '#type' => 'item',
            '#markup' => $sub_total_asi,
          ];
          $block['resumen'][$last_subtotal]['subtotal_maltrato'] = [
            '#type' => 'item',
            '#markup' => $sub_total_mal,
          ];
          $block['resumen'][$last_subtotal]['subtotal_neg'] = [
            '#type' => 'item',
            '#markup' => $sub_total_neg,
          ];
          $block['resumen'][$last_subtotal]['subtotal_otro'] = [
            '#type' => 'item',
            '#markup' => $sub_total_otro,
          ];
          $block['resumen'][$last_subtotal]['subtotal_total'] = [
            '#type' => 'item',
            '#markup' => $sub_total_total,
          ];
        }
        $comuna_id = $cpi->comuna;
      }
      $totales = [['Total', '', $total_asi, $total_mal, $total_neg, $total_otro, $total_total]];
      $block['resumen']['#footer'] = $totales;
    }

    return $block;
  }

    // On submit form.
  public static function sime_salidas_resumen_form_submit(&$form, FormStateInterface $form_state) {
    $edades = $form_state->getValue('field_edad');
    $generos = $form_state->getValue('field_genero');
    $route_name = \Drupal::routeMatch()->getRouteName();
    $response = new RedirectResponse(\Drupal::url($route_name, [
      'e' => $edades,
      'g' => $generos,
    ]));
    $response->send();
  }

}
