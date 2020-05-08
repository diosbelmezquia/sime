<?php

namespace Drupal\sime_salidas\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;
use Drupal\Core\Link;

class ObservacionesNegativasController extends ControllerBase {

  protected $supervisiones = [
    'supervision' => 'Supervisión Frecuente',
    'supervision_anual' => 'Supervisión Anual',
  ];

  protected $paragraphs_values = [];

  public function view() {
    $header = ['CPI' ,'Tipo de supervisión', 'Observaciones negativas', 'Fecha'];
    $supervisiones_cpis = [];
    //Obtener las ultimas supervisiones de cada CPI.
    foreach (array_keys($this->supervisiones) as $supervision) {
      $query = db_select('node__field_re_periodo', 'pe');
      $query->fields('pe', ['bundle', 'entity_id', 'field_re_periodo_value']);
      $query->addField('cpi', 'field_cpi_target_id');
      $query->leftJoin('node__field_cpi', 'cpi', 'pe.entity_id = cpi.entity_id AND pe.revision_id = cpi.revision_id AND cpi.bundle = pe.bundle');
      $query->condition('pe.bundle', $supervision);
      $query->orderBy('pe.field_re_periodo_value');
      //$pager = $query->extend('Drupal\Core\Database\Query\PagerSelectExtender')->limit();
      $supervisiones_cpis[$supervision] = $query->execute()->fetchAllAssoc('field_cpi_target_id');
    }

    $block['observacion'] = [
      '#type' => 'table',
      '#header' => $header,
      '#attributes' => [
        'class' => [
          'observaciones-negativas',
          ],
      ],
    ];

    //Generar la tabla.
    $index = 0;
    foreach ($supervisiones_cpis as $supervisiones) {
      foreach ($supervisiones as $supervision) {
        $supervision->observaciones_negativas = self::sime_salidas_get_observaciones_negativas($supervision->entity_id);
        if (!empty($supervision->observaciones_negativas)) {
          $ob = [];
          $concat_parapraph = '';
           if ($supervision->bundle == 'supervision_anual') {
            $seguridad_higiene = $supervision->observaciones_negativas['seguridad_higiene'];
            foreach ($seguridad_higiene as $field_ob => $fecha) {
              if ($fecha) {
                $fecha = format_date(strtotime($fecha), '', 'j/m/Y');
                $concat_parapraph .= $field_ob . " ($fecha). ";
              }
              else {
                $concat_parapraph .= $field_ob . ". ";
              }
            }
            unset($supervision->observaciones_negativas['seguridad_higiene']);
          }
          foreach ($supervision->observaciones_negativas as $observacion) {
            $ob[] = $observacion->getFieldDefinition()->getLabel();
          }
          $ob = implode(', ', $ob);
          if ($supervision->bundle == 'supervision_anual' && !empty($concat_parapraph)) {
            if (empty($ob)) {
              $ob = $ob . '<strong>Seguridad e Higiene:</strong> ' . $concat_parapraph;
            }
            else {
              $ob = $ob . '<br><strong>Seguridad e Higiene:</strong> ' . $concat_parapraph;
            }
          }
        }
        if (isset($ob) || (isset($concat_parapraph) && !empty($concat_parapraph))) {
          $block['observacion'][$index]['#attributes'] = [
            'class' => ['foo','baz'],
          ];
          $block['observacion'][$index]['cpi'] = [
            '#type' => 'item',
            '#markup' => Node::load($supervision->field_cpi_target_id)->label(),
          ];
          $url = Url::fromRoute('entity.node.canonical', ['node' => $supervision->entity_id]);
          $link_bundle = Link::fromTextAndUrl($this->supervisiones[$supervision->bundle], $url)->toString();
          $block['observacion'][$index]['tipo_supervision'] = [
            '#type' => 'item',
            '#markup' => $link_bundle,
          ];
          $block['observacion'][$index]['observacion'] = [
            '#type' => 'item',
            '#markup' => $ob,
            '#wrapper_attributes' => [
              'class' => ['observaciones-negativas-celda'],
            ],
          ];
          $fecha_supervision = format_date(strtotime($supervision->field_re_periodo_value), '', 'j/m/Y');
          $block['observacion'][$index]['fecha'] = [
            '#type' => 'item',
            '#markup' => $fecha_supervision,
          ];
        }
        $index++;
      }
    }

/*    $block['pager'] = [
      '#type' => 'pager',
    ];*/

    return $block;
  }

  public function sime_salidas_get_observaciones_negativas($node_id) {
    $checklist = Node::load($node_id);
    $fields_negativos = [];
    $fields_paragraph = ['field_ch_matafuegos', 'field_ch_desinsectacion', 'field_ch_potabilidad_agua', 'field_ch_seguro', 'field_ch_planos_evacuacion', 'field_ch_simulacro'];
    if (isset($checklist)) {
        $fields_negativos = array_filter($checklist->getFields(), function($field) {
        $instance = $field instanceof \Drupal\Core\Field\FieldItemList;
        if ($instance && !empty($field->getValue()) && isset($field->getValue()[0]['value'])) {
          return $field->getValue()[0]['value'] == 'no';
        }});
        if ($checklist->getType() == 'supervision_anual') {
          foreach ($fields_paragraph as $field) {
            $entity_paragraph = $checklist->$field->referencedEntities()[0];
            if ($field == 'field_ch_matafuegos' || $field == 'field_ch_seguro') {
              if(isset($entity_paragraph->field_vencido_novencido) && !$entity_paragraph->field_vencido_novencido->value) {
                $this->paragraphs_values[$checklist->$field->getFieldDefinition()->getLabel()] = $entity_paragraph->field_fecha_vencimiento->value;
              }
            }
            else {
              if(isset($entity_paragraph->field_realizado_norealizado) && $entity_paragraph->field_realizado_norealizado->value) {
                $this->paragraphs_values[$checklist->$field->getFieldDefinition()->getLabel()] = $entity_paragraph->field_ultima_realizacion->value;
              }
            }
          }
          $fields_negativos['seguridad_higiene'] = $this->paragraphs_values;
        }
    }
    return $fields_negativos;
  }

}
