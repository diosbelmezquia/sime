<?php

namespace Drupal\sime_salidas\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\NodeInterface;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ActaController extends ControllerBase {

  public function view(NodeInterface $node) {
    $type = $node->getType();
    $nombre_cpi = $nombre_nino = $apellido_nino = $texto_diagnostico1 = $texto_diagnostico2 = '';
    $current_date = date('j') . ' de ' . t(date('F')) . ' de ' . date('Y');
    //$response = new RedirectResponse(\Drupal::url('view.ninos.page_1'));
    $output  = '';
    $output .= '<div class="acta-nino">';
    $output .= '<div class="text-header">';
    $escudo_url = file_create_url(drupal_get_path('module', 'sime_salidas') . '/images/escudo-acta.png');
    $output .= '<a class="imprimir-acta button button--primary">Imprimir</a>';
    $output .= t('<img class="acta-escudo" src=":escudo">', [':escudo' => $escudo_url]);
    $output .= '<p><strong>' . 'GOBIERNO DE LA CIUDAD DE BUENOS AIRES' . '</strong></p>';
    $output .= '<p>' . 'MINISTERIO DE HABITAT Y DESARROLLO HUMANO' . '</br>';
    $output .= 'SUBSECRETARÍA DE PROMOCIÓN SOCIAL' . '</br>';
    $output .= 'DIRECCIÓN GENERAL FORTALECIMIENTO DE LA SOCIEDAD CIVIL' . '</br>';
    $output .= 'GERENCIA OPERATIVA DE APOYO A LA PRIMERA INFANCIA' . '</p>';
    $output .= '<p class="current-date">' . 'Buenos Aires, ' . $current_date . '</p>';
    $output .= '</div>';

    $salida = $output;
    $salida .= '<p>' . 'No hay datos antropométricos definidos para este nino' . '</p>';
    $exit['acta_texto'] = [
      '#type' => 'item',
      '#markup' => $salida,
    ];

    if ($type == 'nino') {
        $cpi  = Node::load($node->field_cpi->entity->id());
        $nombre_cpi = $cpi->title->value;
        $nombre_nino = $node->field_datos_personales->referencedEntities()[0]->field_nombres->value;
        $apellido_nino = $node->field_datos_personales->referencedEntities()[0]->field_apellidos->value;
        $mediciones = sime_salidas_get_mediciones($node);

        if (empty($mediciones)) {
          return $exit;
        }
        else {
          $no_value = '(No definido)';
          //Obtener ultima medicion.
          $ultima_medicion = end($mediciones);
          //Ultimas mediciones.
          $ultima_talla = $ultima_medicion->field_talla->value ? $ultima_medicion->field_talla->value : $no_value;
          $ultimo_peso = $ultima_medicion->field_peso->value ? $ultima_medicion->field_peso->value : $no_value;
          $ultimo_imc = $ultima_medicion->field_imc_dato_antropometrico->value ? $ultima_medicion->field_imc_dato_antropometrico->value : $no_value;
          $ultima_fecha = format_date(strtotime($ultima_medicion->field_fecha->value), '', 'j/m/Y');

        }
    }
    else {
      return $exit;
    }

    if (!empty($mediciones)) {
      if ($ultima_medicion->field_diagnostico->value == '_none') {
        return $exit;
      }
      $motivo1 = '<p>' . 'Motivo por el cual, y en atención a resguardar el bienestar del niño/a, se les solicita a los mismos la realización de una interconsulta con su pediatra de cabecera para que confirme
      dicho diagnóstico y evalúe la realización del tratamiento correspondiente.' . '</p>';
      $motivo2 = '<p>' . 'Motivo por el cual, y en atención a resguardar el bienestar del niño/a, se les solicita a los mismos que continúen con los lineamientos sugeridos por el pediatra de cabecera.' . '</p>';
      $texto_bajo_peso1 = "es positivo ya que continua evolucionando favorablemente";
      $texto_bajo_peso2 = '<p>' . "<strong>Talla:</strong> $ultima_talla cm. (por debajo del percentilo 3)." . '</p>';
      $texto_bajo_peso2 .= '<p>' . 'Rango de normalidad percentilo mayor a 3.</p>';
      $texto_bajo_peso2 .= '<p>' . "<strong>Peso:</strong> $ultimo_peso kg." . '</p>';
      $texto_bajo_peso2 .= '<p>' . "<strong>IMC:</strong> $ultimo_imc (IMC/ edad  mayor a percentil 10)." . '</p>';
      $texto_bajo_peso2 .= '<p>' . 'Rango de Normalidad: percentilos entre 10 y 85.' . '</p>';
      $texto_bajo_peso2 .= $motivo2;

      $texto_bajo_peso3 = '<p>' . "<strong>Talla:</strong> $ultima_talla cm.  <strong>Peso:</strong> $ultimo_peso kg." . '</p>';
      $texto_bajo_peso3 .= '<p>' . "<strong>IMC:</strong> $ultimo_imc (IMC/ edad  mayor a percentil 10)." . '</p>';
      $texto_bajo_peso3 .= '<p>' . 'Rango de Normalidad: percentilos entre 10 y 85.' . '</p>';
      $texto_bajo_peso3 .= $motivo2;
      switch ($ultima_medicion->field_diagnostico->value) {
        case 'normal_bt': //Normal c/ baja talla
          $texto_diagnostico1 .= "continua reflejando una talla por debajo de la normalidad con respecto a su edad";
          $texto_diagnostico2 .= '<p>' . "<strong>Talla:</strong> $ultima_talla cm (por debajo de percentilo 3)." . '</p>';
          $texto_diagnostico2 .= '<p>' . 'Rango de normalidad Percentilo mayor a 3.' . '</p>';
          $texto_diagnostico2 .= $motivo1;
            break;
        case 'obesidad': //Obesidad
          $texto_diagnostico1 .= "reflejó un peso por encima de la normalidad con respecto a su talla y edad";
          $texto_diagnostico2 .= '<p>' . "<strong>Talla:</strong> $ultima_talla cm." . '</p>';
          $texto_diagnostico2 .= '<p>' . "<strong>Peso:</strong> $ultimo_peso kg.  <strong>IMC:</strong> $ultimo_imc (por encima del percentilo 97)." . '</p>';
          $texto_diagnostico2 .= '<p>' . 'Rango de normalidad IMC entre Percentilos 10 y 85.' . '</p>';
          $texto_diagnostico2 .= $motivo1;
            break;
        case 'obesidad_bt': //Obesidad c/ baja talla
          $texto_diagnostico1 .= "reflejó un peso por encima de la normalidad y una talla baja con respecto a su edad";
          $texto_diagnostico2 .= '<p>' . "<strong>Peso:</strong> $ultimo_peso kg.  <strong>IMC:</strong> $ultimo_imc (por encima del percentilo 97)." . '</p>';
          $texto_diagnostico2 .= '<p>' . 'Rango de normalidad IMC entre Percentilos 10 y 85.' . '</p>';
          $texto_diagnostico2 .= '<p>' . "<strong>Talla:</strong> $ultima_talla cm (por debajo del percentilo 3)." . '</p>';
          $texto_diagnostico2 .= '<p>' . 'Rango de normalidad percentilo mayor a 3.</p>';
          $texto_diagnostico2 .= $motivo1;
            break;
        case 'bajo_peso': //Bajo peso
          $texto_diagnostico1 .= $texto_bajo_peso1;
          $texto_diagnostico2 .= $texto_bajo_peso3;
            break;
        case 'riesgo_bp': //Riesgo de bajo peso
          $texto_diagnostico1 .= $texto_bajo_peso1;
          $texto_diagnostico2 .= $texto_bajo_peso3;
            break;
        case 'riesgo_bp_bt': //Riesgo de bajo peso c/ baja talla
          $texto_diagnostico1 .= $texto_bajo_peso1;
          $texto_diagnostico2 .= $texto_bajo_peso2;
            break;
        case 'bajo_peso_bt': //Bajo peso c/ baja talla
          $texto_diagnostico1 .= $texto_bajo_peso1;
          $texto_diagnostico2 .= $texto_bajo_peso2;
            break;
      }
    }

    $output .= '<div class="text-body">';
    $output .= '<p>' . t('Por medio de la presente se da conocimiento a los padres y/o adultos responsables del
                niño/a :nino quien asiste al Centro de Primera Infancia ":cpi", que el diagnóstico de
                la valoración antropométrica realizada el día :fecha por el Equipo de
                Nutricionistas del Programa Centros de Primera Infancia dependientes del Ministerio de
                Hábitat y Desarrollo Humano del Gobierno de la Ciudad Autónoma de Buenos Aires :texto_diagnostico1.', [
                ':nino' => ucwords($nombre_nino) . ' ' . ucwords($apellido_nino),
                ':cpi' => ucwords($nombre_cpi),
                ':fecha' => $ultima_fecha,
                ':texto_diagnostico1' => $texto_diagnostico1,
                ]) . '</p>';

    $output .= $texto_diagnostico2;

    $output .= '<p>' . 'Asimismo, y en pos de poder realizar el seguimiento correspondiente, se establece el plazo de 45 días para presentar certificado firmado por el médico tratante de la consulta realizada y el tratamiento propuesto acorde a dicho diagnóstico.' . '</p>';
    $output .= '</div>';

    $output .= '<div class="text-footer">';
    $output .= '<p>' . 'Firma:'      . '</p>';
    $output .= '<p>' . 'Aclaración:' . '</p>';
    $output .= '<p>' . 'DNI'         . '</p>';
    $output .= '</div>';
    $output .= '</div>';

    $block['acta_texto'] = [
      '#type' => 'item',
      '#markup' => $output,
    ];
    return $block;
  }


}
