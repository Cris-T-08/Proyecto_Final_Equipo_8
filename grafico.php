<?php
// CÓDIGO DE PROYECTO versión 2 (V2). La V2 es igual que la V1, solo que grafico.php está segurizado frente a los ataques más comunes.
/* 
La función de este archivo grafico.php (que es un script de PHP) es:

	1. Conectarse a la base de datos
	2. Realizar consultas en función de los parámetros recibidos (el año y la region que el usuario selecciona en el formulario de la página web, determinado en index.html)
	3. Devolver los datos en formato JSON para ser consumidos por el archivo index.html
	
	Este código PHP está protegido contra varios tipos comunes de ataques web, como la inyección SQL y los ataques XSS (ambos visto en la asigntura M11 de ASIR).
	Entre las medidas de seguridad implementadas se incluyen:
	
	1. Conversacíon del parámetro año a un entero (int)
	2. Aplicación de htmlspecialchars al parámetro region
	3. Escapado de caracteres especiales en la consulta SQL
	4. Preparación de consultas SQL
	5. Vinculación de parámetros a la consulta SQL preparada
	
	Con todo ello se pretende:
	1. Garantizar que los datos se escapen
	2. y formateen correctamente
	Ayudando a prevenir ataques, garantizando la integridad de la aplicación web
*/

// Recuperación del parámetro GET 'año'; es decir, el valor de 'año' enviado desde el formulario en index.html
// Se comprueba si el parámetro 'año' está presente y se convierte en un entero. Esta conversión a entero garantiza que se usa un valor numérico, evitando la inyección de código malicioso
if (isset($_GET['año'])) { // isset determina si una variable está definida y no es null. Previene una posible "Undefined index" notice o advertencia si el parámetro no está presente en la URL. Prevenir errores es una buena práctica para evitar la posible exposición de vulnerabilidades.
    $año = (int)$_GET['año'];
} else {
    die("El parámetro 'año' es obligatorio.");
}

// Recuperación del parámetro GET 'region'; es decir, el valor de 'region' enviado desde el formulario en index.html
// Se comprueba si el parámetro 'region' está presente.
// Se aplica al parámetro 'region' htmlspecialchars para evitar ataques XSS. htmlspecialchars convierte caracteres especiales en entidades HTML y evita que se ejecuten scripts maliciosos en el navegador.
if (isset($_GET['region'])) { // isset determina si una variable está definida y no es null. Previene una posible "Undefined index" notice o advertencia si el parámetro no está presente en la URL. Prevenir errores es una buena práctica para evitar la posible exposición de vulnerabilidades.
    $region = htmlspecialchars($_GET['region'], ENT_QUOTES, 'UTF-8'); //END_QUOTES es una flag de htmlspecialchars que convierte tanto las comillas dobles como las simples. La principal función de htmlspecialchars es evitar ataques XSS en el navegador.
} else {
    die("El parámetro 'region' es obligatorio.");
}

// Se establece la conexión a la base de datos usando la extensión mysqli_connect de PHP.
// Los parámetros son: servidor (localhost; es decir, desarrollo local), usuario (root), contraseña (ninguna) y nombre de la base de datos (terrorismo).
$cnx = mysqli_connect("localhost", "root", "", "terrorismo");

// Se comprueba si la conexión a la base de datos fue exitosa. Si no, se muestra un mensaje de error y termina la ejecución.
if (!$cnx) {
    die("Error al conectar: " . mysqli_connect_error());
}

// Se define la consulta SQL a ejecutar en función del valor de la variable 'region'.
if ($region == 'Todas') {
    $query = "SELECT iano, SUM(nfallecidos) AS total_fallecidos FROM datosterrorismo WHERE iano = ? GROUP BY iano";
} else {
    // Escapa la variable 'region' para evitar inyección SQL. mysqli_real_escape_string escapa caracteres especiales en la cadena para su uso en la consulta SQL.
    $region_escaped = mysqli_real_escape_string($cnx, $region);
    $query = "SELECT iano, SUM(nfallecidos) AS total_fallecidos FROM datosterrorismo WHERE region_txt = ? AND iano = ? GROUP BY iano"; // region_escaped es la "versión segura" de la variable region, que se utiliza en la consulta SQL $query a través del marcador de posición ? La principal función de mysqli_real_escape_string es evitar ataques de inyección SQL.
}

// Se prepara la consulta SQL para su ejecución. Preparar una sentencia SQL antes de su ejecución previene ataques de inyección SQL.
$stmt = mysqli_prepare($cnx, $query);

// Se vinculan los parámetros a la consulta SQL preparada (recuérdese los marcadores de posición ?), lo que garantiza que los datos se escapan y formatean correctamente, evitando la inyección SQL.
if ($region == 'Todas') {
    mysqli_stmt_bind_param($stmt, "i", $año); // i indica que el parámetro es de tipo entero (int).
} else {
    mysqli_stmt_bind_param($stmt, "si", $region_escaped, $año); // s indica que el parámetro es de tipo cadena (string). i indica que el parámetro es de tipo entero (int).
}

// Se ejecuta la consulta SQL preparada previamente con mysqli_prepare.
mysqli_stmt_execute($stmt);

// Se obtiene el resultado de la consulta ejecutada.
$result = mysqli_stmt_get_result($stmt);

// Creación de dos arrays vacíos, $labels y $data
$labels = array();
$data = array();
// Los resultados de la consulta se procesan usando el bucle while y la función mysqli_fetch_assoc
while ($row = mysqli_fetch_assoc($result)) {
    $labels[] = strval($row['iano']); // Los años (o, mejor dicho, en este caso el año) se almacena en el array $labels
    $data[] = intval($row['total_fallecidos']); // El número total de víctimas mortales, total_fallecidos, se almacena en el array $data
}

// Cerrar la conexión
mysqli_close($cnx); // Se cierra la conexión a la base de datos con la función mysqli_close

// Devolución de los datos en formato JSON
	// El objetivo de este JSON es devolver los datos de la consulta a la base de datos en un formato fácil de manejar por el código JavaScript del archivo HTML. Así, JSON se utiliza para transmitir datos entre un servidor (en este caso, grafico.php) y una aplicación web (en este caso, index.html). Los arrays $labels y $data se devuelven en formato JSON usando la función json_encode, que convierte el array asociativo en una cadena JSON. Esta cadena JSON devuelta por grafico.php a index.html se procesa y se almacena en la variable data -var data = response.data;-, con la que luego se configura y actualizar el gráfico de barras -type: 'bar'- en la página web utilizando Chart.js
echo json_encode(['labels' => $labels, 'data' => $data, 'label' => 'Número de víctimas mortales']); // Se devuelve un objeto JSON con los datos procesados. Este objeto contiene tres propiedades: labels (años), data (total_fallecidos) y label (etiqueta descriptiva que dice 'Número de víctimas mortales'
?>
