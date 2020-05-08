(function($, Drupal, drupalSettings) {
  'use strict';

  Drupal.behaviors.simeAjustesAdmin = {
    /**
     * Drupal attach behavior.
     */
    attach: function(context, settings) {
      // Cambiar cpi en url en inasistencias.
      if ($("form.sime-inasistencias-form").length) {
        $("select[name='field_listado_cpis']" ).change(function() {
          var cpi = $("select[name='field_listado_cpis']" ).val();
          window.location.replace(window.location.href.split('?')[0].concat('?field_cpi='+cpi));
        });
      }

      // Mostrar relacion comuna-cpis en select multiples CPI.
      // Otros link utiles en select CPI.
      if (!($("form.form-cpis-sime-salidas-cuadro-anual-na").length || $("form.form-cpis-sime-salidas-cuadro-anual-no").length)) {
        var comunas  = drupalSettings.simeAjustes.comunas;
        var all_cpis = drupalSettings.simeAjustes.all_cpis;
        if (Object.keys(comunas).length > 0) {
          var list = '<div class="comunas-container">';
          var list = list.concat('<span>Comuna:</span>');
          var list = list.concat('<ul class="comunas"><li class="comuna-item">');
          var list = list.concat('<a href="#" class="link-comuna">');
          var list = list.concat(Object.keys(comunas).join('</a></li><li class="comuna-item"><a href="#" class="link-comuna">'));
          var list = list.concat('</a></li></ul>');
          var list = list.concat('<a href="#" class="link-todos">Ninguno</a> ');
          var list = list.concat('</div>');
          $("select.listado-cpis-multiple").after(list);
          $("a.link-comuna").click(function(event) {
            var values = $("select#edit-field-listado-cpis").val();
            $("select#edit-field-listado-cpis").val(values.concat(comunas[$(event.target).text()])).trigger("chosen:updated");
          });
          $("a.link-todos").click(function() {
            $("select#edit-field-listado-cpis").val('').trigger("chosen:updated");
          });
        }
      }

      //Ocultar campo field_nino.
      $('#edit-field-nino-0-target-id:not([value=""])').once('sime-ajustes-hide').each(function (i, element) {
        $(element).parents('.form-item-field-nino-0-target-id').hide();
      });

      if ($('.datos-antropometricos-nino-js').length) {
        // Calcular IMC en datos antropometricos del nino.
        $('.field--name-field-imc-dato-antropometrico input').prop('readonly', true);
        $('.field--name-field-peso input, .field--name-field-talla input').change(function () {
          var $parent = $(this).closest('.datos-antropometricos-nino-js');
          var peso = $parent.find('.field--name-field-peso input').val();
          var talla = $parent.find('.field--name-field-talla input').val();
          talla /= 100;
          talla = Math.pow(talla, 2);
          var imc = (peso / talla).toFixed(2);
          $parent.find('.field--name-field-imc-dato-antropometrico input').val(imc);
        });

        // Mostrar info de la edad del nino en datos antropometricos.
        var fechaNac = drupalSettings.simeAjustes.ninoInfoBlock.fechaNac;
        var annos = fechaNac.annos > 1 ? fechaNac.annos + ' años' : fechaNac.annos + ' año';
        var meses = fechaNac.meses > 1 ? fechaNac.meses + ' meses' : fechaNac.meses + ' mes';
        var dias = fechaNac.dias > 1 ? fechaNac.dias + ' días.' : fechaNac.dias + ' día.';
        if (fechaNac.annos == 0) {
          var annos = '';
        }
        if (fechaNac.meses == 0) {
          var meses = '';
        }
        if (fechaNac.dias == 0) {
          var dias = '';
        }
        $(".datos-antropometricos-nino-js .field--type-datetime .description").text('Edad: '+ annos +' '+ meses +' '+ dias);
      }

      // Imprimir acta.
      $("a.imprimir-acta").click(function() {
        window.print()
      });

    }
};
})(jQuery, Drupal, drupalSettings);
