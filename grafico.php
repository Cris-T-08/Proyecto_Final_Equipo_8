<?php

/* 
La función de este archivo grafico.php (que es un script de PHP) es:

	1. Conectarse a la base de datos
	2. Realizar consultas en función de los parámetros recibidos (el año y la region que el usuario selecciona en el formulario de la página web, determinado en index.html)
	3. Devolver los datos en formato JSON para ser consumidos por el archivo index.html
*/

// Recuperación de los parámetros GET; es decir, los valores de 'año' y 'region' enviados desde el formulario en index.html
$año = $_GET['año'];
$region = $_GET['region'];

// Conexión a la base de datos
$cnx = mysqli_connect("localhost", "root", "", "terrorismo"); //Conexión a la base de datos "terrorismo" utilizando la función mysqli_connect. Nótese cómo se le pasan los argumentos a la función: localhost es la dirección donde se encuentra la base de datos; en este caso, en la misma máquina donde se ejecuta el script (entorno de desarrollo local). root es el usuario, "" significa que no hay contraseña, y "terrorismo" es el nombre de la base de datos
if (!$cnx) { // Si la conexión a la base de datos falla...
  die("Error al conectar: " . mysqli_connect_error()); // ... se muestra un mensaje de error y se termina la ejecución del script
}

// Realizacón de la consulta
if ($region == 'Todas') { // Se verifica si el usuario ha seleccionado 'Todas' las regiones en el formulario del HTML o una región específica y, en función de ello, se crea la consulta SQL, que agrupa los datos por año y suma el número de víctimas mortales
  $query = "SELECT iano, SUM(nfallecidos) AS total_fallecidos FROM datosterrorismo WHERE iano = $año GROUP BY iano"; // Definición de la consulta si el usuario ha seleccionado 'Todas' las regiones
} else {
  $query = "SELECT iano, SUM(nfallecidos) AS total_fallecidos FROM datosterrorismo WHERE region_txt = '$region' AND iano = $año GROUP BY iano"; // Definición de la consulta si el usuario ha selecciona una región en particular
}
$result = mysqli_query($cnx, $query); // Ejecución de la consulta con mysqli_query

// Obtención de los resultados
	// Inicialmente, creación de dos arrays vacíos, $labels y $data
$labels = array();
$data = array();
while ($row = mysqli_fetch_assoc($result)) { // Los resultados de la consulta se procesan usando el bucle while y la función mysqli_fetch_assoc
  $labels[] = strval($row['iano']); // Los años (o, mejor dicho, en este caso el año) se almacena en el array $labels
  $data[] = intval($row['total_fallecidos']); // El número total de víctimas mortales, total_fallecidos se almacena en el array $data
}

// Cerrar la conexión
mysqli_close($cnx); // Se cierra la conexión a la base de datos con la función mysqli_close

// Devolución de los datos en formato JSON
	// El objetivo de este JSON es devolver los datos de la consulta a la base de datos en un formato fácil de manejar por el código JavaScript del archivo HTML. Así, JSON se utiliza para transmitir datos entre un servidor (en este caso, grafico.php) y una aplicación web (en este caso, index.html). Los arrays $labels y $data se devuelven en formato JSON usando la función json_encode, que convierte el array asociativo en una cadena JSON. Esta cadena JSON devuelta por grafico.php a index.html se procesa y se almacena en la variable data -var data = response.data;-, con la que luego se configura y actualizar el gráfico de barras -type: 'bar'- en la página web utilizando Chart.js
echo json_encode(['labels' => $labels, 'data' => $data, 'label' => 'Número de víctimas mortales']); // Se devuelve un objeto JSON con los datos procesados. Este objeto contiene tres propiedades: labels (lso años), data (total_fallecidos) y label (etiqueta descriptiva que dice 'Número de víctimas mortales'
?>