<?php

namespace Drupal\sime_ajustes\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\node\Entity\Node;
use Drupal\sime_salidas\UtilityTrait;

/**
 * Provides a block with a simple text.
 *
 * @Block(
 *   id = "sime_ajustes_info_block",
 *   admin_label = @Translation("Info block"),
 * )
 */
class InfoBlock extends BlockBase {
  use UtilityTrait;
  /**
   * {@inheritdoc}
   */
  public function build() {
    $nino_id = \Drupal::request()->query->get('field_nino');
    if (empty($nino_id)) {
      // Para node edit datos antropometricos.
      $node_edit = \Drupal::routeMatch()->getRouteName() == 'entity.node.edit_form';
      $node_antropometrico = \Drupal::routeMatch()->getParameters()->get('node')->getType() == 'datos_antropometricos';
      if ($node_edit && $node_antropometrico) {
        $nino_id = \Drupal::routeMatch()->getParameters()->get('node')->field_nino->getValue()[0]['target_id'];
      }
      else {
        return;
      }
    }
    $nino = Node::load($nino_id);
    if(!isset($nino)) {
      return;
    }

    $mediciones = sime_salidas_get_mediciones($nino);
    $ultima_medicion = sime_diagnostico_antropometrico_ultima_medicion($mediciones);
    $datos_personales = $nino->field_datos_personales->entity;
    // Datos del nino.
    $nombre = ucwords($datos_personales->field_nombres->value);
    $apellido = ucwords($datos_personales->field_apellidos->value);
    $fecha_nacimiento = $datos_personales->field_fecha_de_nacimiento->value;
    $fecha_nacimiento = format_date(strtotime($fecha_nacimiento), '', 'j/m/Y');
    $genero = $datos_personales->field_sexo->value;
    $genero = $genero == 'hombre' ? 'Masculino' : 'Femenino';
    $edad_gestacional = $datos_personales->field_edad_gestacional->value;
    $edad_meses = sime_formularios_desarrollo_calcular_edad($fecha_nacimiento);

    $markup_nombre = "<h3 class=\"nombre\">".$nombre." ".$apellido."</h3>";
    $markup_fecha = "<p class=\"nombre\">".$fecha_nacimiento."</p>";
    $markup_genero = "<p class=\"nombre\">".$genero."</p>";
    $markup_edad = "<p class=\"nombre\">".$edad_gestacional."</p>";



    $block['nino'] = [
      '#type' => 'table',
      '#header' => ['Nombre y Apellido','Fecha de nacimiento', 'Genero', 'Edad gestacional', 'Último diagnóstico', ''],
      '#attributes' => ['id' => 'ninos-table-datos-antropometricos'],
      '#cache' => [
        'max-age' => 0,
      ],
    ];

    $block['nino']['#attached']['drupalSettings']['simeAjustes']['ninoInfoBlock']['fechaNac'] = $edad_meses;

    $block['nino'][0]['#attributes'] = [
      'class' => ['foo','baz'],
    ];

    $block['nino'][0]['name'] = [
      '#type' => 'item',
      '#markup' => $markup_nombre,
    ];

    $block['nino'][0]['fecha'] = [
      '#type' => 'item',
      '#markup' => $markup_fecha,
    ];

    $block['nino'][0]['genero'] = [
      '#type' => 'item',
      '#markup' => $markup_genero,
    ];

    $block['nino'][0]['edad'] = [
      '#type' => 'item',
      '#markup' => $markup_edad,
    ];

    $block['nino'][0]['last_diagnostico'] = [
      '#type' => 'item',
      '#markup' => $ultima_medicion,
    ];

    //Intercambio los valores de array para poder compararlos.
    $diagnosticos = array_flip($this->sime_diagnosticos());
    if ($ultima_medicion == 'No hay definido') {
      $ultima_medicion = NULL;
    }
    else {
      $ultima_medicion = $diagnosticos[$ultima_medicion];
    }
    $access_acta = \Drupal::currentUser()->hasPermission('admin nino acta') && isset($ultima_medicion) && $ultima_medicion != 'normal' && $ultima_medicion != 'sobrepeso';

    $block['nino'][0]['button'] = [
      '#type' => 'html_tag',
      '#access' => $access_acta,
      '#tag' => 'a',
      '#value' => 'Ver Acta',
      '#attributes' => [
        'class' => [
          'ver-acta', 'button button--primary'
        ],
        'href' => \Drupal::url('sime_salidas_acta', ['node' => $nino_id]),
        'target' => '_blank',
      ],
      '#cache' => [
        'max-age' => 0,
      ],
    ];

    return $block;
  }
}
