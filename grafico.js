/*
La función principal de grafico.js, que es un archivo JavaScript, es:

	1. Seleccionar el elemento del formulario, form, y los dos elementos de selección (año y region) utilizando sus identificadores (dato1 y dato2)
	2. Crear el gráfico utilizando Chart.js
	3. Manejar el evento submit del formulario
	4. Realizar la solicitud HTTP utilizando axios para obtener datos del servidor
	5. Actualizar el gráfico con nuevos datos
		
En definitiva, este archivo grafico.js contiene funciones 
*/

document.addEventListener('DOMContentLoaded', function() { // El evento DOMContentLoaded se desencadena cuando el documento HTML se ha analizado ('parsed') por completo y todos los script diferidos se han descargado y ejecutado
  const form = document.querySelector('form'); // Selecciona el evento del formulario
  const dato1 = document.getElementById('dato1'); // Elemento de selección dato1 (año)
  const dato2 = document.getElementById('dato2'); // Elemento de selección dato2 (region)
  
// Crear el gráfico utilizando Chart.js  
  const ctx = document.getElementById('mi-grafico-ramon-cris-proyecto').getContext('2d');
  const chart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: [],
      datasets: [{
        label: '',
        data: [],
        backgroundColor: 'rgba(54, 162, 235, 0.2)',
        borderColor: 'rgba(54, 162, 235, 1)',
        borderWidth: 1
      }]
    },
    options: {
      hover: {
        mode: null
      },
      scales: {
        yAxes: [{
          ticks: {
            beginAtZero: true
          }
        }]
      }
    }
  });

// Manejar el evento submit del formulario
  form.addEventListener('submit', function(event) { 
    event.preventDefault(); // Evita que se envíe el formulario de forma predeterminada

// Obtener los valores seleccionados por el usuario
    const año = dato1.value;
    const region = dato2.value;

// Realizar la solicitud HTTP utilizando axios
    axios.get('grafico.php', {
      params: {
        año: año,
        region: region
      }
    })
    .then(function (response) {
      const datos = response.data; // Obtener los datos devueltos por el servidor

// Actualización de la información del gráfico de barras, etiquetas del eje X, del conjunot de datos y valores del conjunto de datos con nuevos datos obtenidos de la base de datos y dibujar el gráfico nuevamente con chart.update(). Esta parte del código es importante para una solucionar este problema que nos encontramos en nuestras pruebas: Error: Canvas is already in use. Chart with ID '0' must be destroyed before the canvas with ID 'mi-grafico-ramon-cris-proyecto' can be reused.
      chart.data.labels = datos.labels; 
      chart.data.datasets[0].label = datos.label;
      chart.data.datasets[0].data = datos.data;
      chart.update(); // Actualizar el gráfico con nuevos datos. 
    })
    .catch(function (error) {
      console.log(error);
    });
  });
});