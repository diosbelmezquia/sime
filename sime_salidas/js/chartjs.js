/**
 * @file
 */

(function($, Drupal) {
  'use strict';

  var charts = {};


  Drupal.behaviors.simeSalidas = {
    /**
     * Drupal attach behavior.
     */
    attach: function(context, settings) {
      for (var indicador in settings.simeSalidas.charts) {
        var ctx = document.getElementById(indicador);
        if (ctx) {
          console.log(settings.simeSalidas.charts[indicador]);
          charts[indicador] = new Chart(ctx, {
            type: 'bar',
            data: settings.simeSalidas.charts[indicador],
            options: {
              tooltips: {
                  mode: 'index',
                  intersect: false
              },
              responsive: true,
              scales: {
                  xAxes: [{
                      stacked: true,
                  }],
                  yAxes: [{
                      stacked: true
                  }]
              },
              legend: {
                labels: {
                  padding: 50
                }
              }
            }
          });
          // charts[indicador].getDatasetMeta(0).hidden = true;
          // charts[indicador].update();
        }
      }
//...............................................................................
      // Mostrar torta de cantidad de ausentes por motivos.
      var porcientocpis = document.getElementById('cant-ausentes');
      if (porcientocpis) {
        var cant = [];
        var labels = [];
        $.each(settings.simeSalidas.cant_ausentes, function(motivo, value) {
          switch(motivo) {
            case 'ausente':
              cant.push(value['cantidad']);
              labels.push('Ausentes sin motivo (' + value['porciento'] + '%)');
              break;
            case 'salud':
              cant.push(value['cantidad']);
              labels.push('Ausentes por Salud (' + value['porciento'] + '%)');
              break;
            case 'viaje':
              cant.push(value['cantidad']);
              labels.push('Ausentes por Viaje (' + value['porciento'] + '%)');
              break;
            case 'presentes':
              cant.push(value['cantidad']);
              labels.push('Presentes (' + value['porciento'] + '%)');
              break;
          }
        });
        var chart_porciento_cpis = new Chart(porcientocpis, {
          type: 'pie',
          data: {
            datasets: [{
              data: cant,
              backgroundColor: settings.simeSalidas.colors,
            }],
            labels: labels
          },
           options: {
              title: {
                display: true,
                text: 'Datos generales'
              }
            },
        })
      }
      //Mostar ausentismos por CPI.
      var motivos = settings.simeSalidas.ausentes_cpis;
        $.each( motivos, function( motivo, array ) {
        var ausentes_motivo = document.getElementById('cant-ausentes-' + motivo);
        if (ausentes_motivo) {
          switch(motivo) {
            case 'ausente':
              var label = 'Ausentes sin motivo';
              break;
            case 'salud':
              var label = 'Ausentes por Salud';
              break;
            case 'viaje':
              var label = 'Ausentes por Viaje';
              break;
          }
          var cpi = [];
          var porciento = [];
          var colors = [];
          $.each( settings.simeSalidas.ausentes_cpis[motivo], function( key, value ) {
            cpi.push(value.cpi);
            colors.push(settings.simeSalidas.colors_indexed[value.cpi_id]);
            porciento.push(value.cant);
          });
          new Chart(ausentes_motivo, {
            type: 'pie',
            data: {
              datasets: [{
                data: porciento,
                backgroundColor: colors,
              }],
              labels: cpi
            },
            options: {
              title: {
                display: true,
                text: label
              }
            },
          })
        }
      });
//...............................................................................

      // Mostrar tortas con ninos/ninas por sala.
      var ninos_sala = document.getElementById('ninos-salas');
      var ninos_sala_hombre = document.getElementById('ninos-salas-hombre');
      var ninos_sala_mujer = document.getElementById('ninos-salas-mujer');
      var ninos_sala_cpi = settings.simeSalidas.ninos_sala;
      var ninos_sala_cpi_hombre = settings.simeSalidas.ninos_sala_hombre;
      var ninos_sala_cpi_mujer = settings.simeSalidas.ninos_sala_mujer;
      var salas = [];
      var cantidad = [];
      var colors = [];
      if (ninos_sala) {
        $.each( ninos_sala_cpi.salas, function( key, value ) {
          salas.push(value.sala);
          colors.push(settings.simeSalidas.ninos_sala.colors_indexed[key]);
          cantidad.push(value.cantidad);
        });
        //Torta 1: Total de ninos por sala.
        var chart_ninos_sala = new Chart(ninos_sala, {
          type: 'pie',
          data: {
            datasets: [{
              data: cantidad,
              backgroundColor: colors,
            }],
            labels: salas,
          },
          options: {
            title: {
              display: true,
              text: 'Total general de niños y niñas por Sala'
            }
          },
        })
      }
      var salas = [];
      var cantidad = [];
      var colors = [];
      if (ninos_sala_hombre) {
        $.each( ninos_sala_cpi_hombre.salas, function( key, value ) {
          salas.push(value.sala);
          colors.push(settings.simeSalidas.ninos_sala.colors_indexed[key]);
          cantidad.push(value.cantidad);
        });
        //Torta 2: Total de ninos por sala.(Hombre).
        var chart_ninos_sala_hombre = new Chart(ninos_sala_hombre, {
          type: 'pie',
          data: {
            datasets: [{
              data: cantidad,
              backgroundColor: colors,
            }],
            labels: salas,
          },
          options: {
            title: {
              display: true,
              text: 'Total de niños por Sala'
            }
          },
        })
      }
      var salas = [];
      var cantidad = [];
      var colors = [];
      if (ninos_sala_mujer) {
        $.each( ninos_sala_cpi_mujer.salas, function( key, value ) {
          salas.push(value.sala);
          colors.push(settings.simeSalidas.ninos_sala.colors_indexed[key]);
          cantidad.push(value.cantidad);
        });
        //Torta 3: Total de ninos por sala.(Mujer).
        var chart_ninos_sala_mujer = new Chart(ninos_sala_mujer, {
          type: 'pie',
          data: {
            datasets: [{
              data: cantidad,
              backgroundColor: colors,
            }],
            labels: salas,
          },
          options: {
            title: {
              display: true,
              text: 'Total de niñas por Sala'
            }
          },
        })
      }

      // Mostrar torta porciento de casos por tipo.
      var porciento_casos_tipo = document.getElementById('porciento-casos-tipo');
      if (porciento_casos_tipo) {
        var porciento_casos = settings.simeSalidas.porciento_casos_tipo;
        var porcientos = [];
        var casos = [];
        for (var indicador in porciento_casos) {
          porcientos.push(porciento_casos[indicador]);
          casos.push(indicador);
        }
        var chart_casos = new Chart(porciento_casos_tipo, {
          type: 'pie',
          data: {
            datasets: [{
              data: porcientos,
              backgroundColor: settings.simeSalidas.colors,
            }],
            labels: casos,
          },
        })
      }

    },
  };

})(jQuery, Drupal);
