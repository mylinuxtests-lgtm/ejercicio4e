<?php

$nombre_relleno = "";
$sexo_relleno = "";
$edad_relleno = "";
$bday_relleno = "";
$photo_relleno = "";
$telefono_relleno = "";
$correo_relleno = "";
$domicilio_relleno = "";
$list_relleno = "";
$excel_relleno = "";
$error = "";
$rutaArchivo = "";
$xlsxFile = "";
$datosExcel = []; 

//verifica si el directorio existe en el sistema al momento de leer el codigo
$directorio = "uploads/";
if (!file_exists($directorio)) {
    if (!mkdir($directorio, 0755, true)); {

    }
}

if (file_exists($directorio) && !is_writable($directorio)) {

}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nombre_relleno = isset($_POST['nombre']) ? $_POST['nombre'] : "";
    $sexo_relleno = isset($_POST['sexo']) ? $_POST['sexo'] : "";
    $edad_relleno = isset($_POST['age']) ? $_POST['age'] : "";
    $bday_relleno = isset($_POST['bday']) ? $_POST['bday'] : "";
    $photo_relleno = isset($_POST['photo']) ? $_POST['photo'] : "";
    $telefono_relleno = isset($_POST['phone']) ? $_POST['phone'] : "";
    $correo_relleno = isset($_POST['correo']) ? $_POST['correo'] : "";
    $domicilio_relleno = isset($_POST['domicilio']) ? $_POST['domicilio'] : "";
    $list_relleno = isset($_POST['list']) ? $_POST['list'] : "";
    $excel_relleno = isset($_POST['excel']) ? $_POST['excel'] : "";

    //Si la opcion Otro es seleccionada, un nuevo campo de texto se abre
    if ($sexo_relleno == "Otro" && isset($_POST['especifique'])) {
        $sexo_relleno = $_POST['especifique'];
    }
    
    if (isset($_FILES["photo"])&& $_FILES["photo"]["error"] == UPLOAD_ERR_OK) {
        $nombreArchivo = basename($_FILES["photo"]["name"]);
        $rutaArchivo = $directorio . $nombreArchivo;

if (is_writable($directorio)) {
            if (!move_uploaded_file($_FILES["photo"]["tmp_name"], $rutaArchivo)) {
                echo "<p style='color:red;'>Error al subir la imagen </p>";
            }
        }
    } elseif (isset($_FILES["photo"])) {
        echo "<p style='color:red;'>Error al cargar la imagen " . $_FILES["photo"]["error"] . "</p>";
    }
} else {
    echo "No se recibió ningún formulario.";
}
//Verifica que el archivo existe
if (isset($_FILES["list"]) && $_FILES["list"]["error"] == UPLOAD_ERR_OK) {
    $archivo = $_FILES["list"]["tmp_name"];
    if (file_exists($archivo)) {
        $lineas = file_get_contents($archivo);
    } else {
        echo "<p style='color:red;'>El archivo <strong>$archivo</strong> no existe.</p>";
    }
} else {
    echo "<p style='color:red;'>Error al subir el archivo</p>";
}
//Comprueba la extension del archivo y extrae los datos
function procesarCSV($archivo) {
    $datos = [];
    if (($handle = fopen($archivo, "r")) !== FALSE) {
        while (($fila = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $datos[] = $fila;
        }
        fclose($handle);
    }
    return $datos;
}
//Comprueba la extension del archivo y extrae los datos
function procesarXLSX($archivo) {
    $datos = [];
    $zip = new ZipArchive;
    if ($zip->open($archivo) === TRUE) {
        $sharedStrings = [];
        if (($xmlStrings = $zip->getFromName("xl/sharedStrings.xml")) !== false) {
            $xmlStrings = simplexml_load_string($xmlStrings);
            foreach ($xmlStrings->si as $item) {
                $sharedStrings[] = (string) $item->t;
            }
        }
        //extrae los datos y los organiza en columnas y filas 
        $xmlSheet = simplexml_load_string($zip->getFromName("xl/worksheets/sheet1.xml"));
        
        foreach ($xmlSheet->sheetData->row as $row) {
            $fila = [];
            foreach ($row->c as $c) {
                $value = (string) $c->v;
                if (isset($c['t']) && $c['t'] == 's') {
                    $value = $sharedStrings[(int) $value] ?? '';
                }
                $fila[] = $value;
            }
            $datos[] = $fila;
        }
        $zip->close();
    return $datos;
    } else {
        return []; 
    }
}
//Comprubea la extension del archivo para saber si es compatible para subir
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['excel']) && $_FILES['excel']['error'] == UPLOAD_ERR_OK) {
    $xlsxFile = $_FILES['excel']['tmp_name'];
    $extension = strtolower(pathinfo($_FILES['excel']['name'], PATHINFO_EXTENSION));
    
    if ($extension === 'xlsx') {
        $datosExcel = procesarXLSX($xlsxFile);
    } elseif ($extension === 'csv') {
        $datosExcel = procesarCSV($xlsxFile);
    } else {
        echo "<p style='color:red;'>Formato de archivo no soportado. Use XLSX o CSV.</p>";
        $datosExcel = [];
    }

} else {
    echo "<p style='color:red;'>Error al subir el archivo.</p>";
}
//Son las validaciones del correo para verificar si es valido
function validarEmail($email)
{
    // sirve para eliminar espacios en blanco al inicio y final
    $email = trim($email);

    // verifica si el correo empieza con numeros
    if (preg_match('/^\d/', $email)) {
        return false;
    }

    // cerifica si contiene espacios dentro del correo
    if (strpos($email, ' ') !== false) {
        return false;
    }

    // verificar si contiene simbolos o caracterres invalidos
    if (preg_match('/[^a-zA-Z0-9@._-]/', $email)) {
        return false;
    }

    // verifica el formato basico del correo
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    return true;
}
//Comprueba si todos los campos estan rellenos antes de enviar la informacion
if (
    empty($nombre_relleno) || empty($sexo_relleno) || empty($edad_relleno) ||
    empty($bday_relleno) || empty($photo_relleno) || empty($telefono_relleno) ||
    empty($correo_relleno) || empty($domicilio_relleno) || empty($list_relleno) || empty($excel_relleno)
) {
    $error = "Por favor, complete todos los campos.";
} else {
    header("Location: Formulario.php");
    exit();
}


?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 80px;
            background-color: #f5f5f5;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 1);
            text-align: center;
        }

        .error {
            color: red;
            padding: 10px;
            background-color: #ffeeee;
            border: 1px solid red;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .data-field {
            margin-bottom: 30px;
            padding: 10px;
            background-color: rgba(253, 255, 160, 1);
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 1);
            text-align: center;
        }

        .data-field h2 {
            margin: 0 0 5px 0;
            color: #333;
            font-size: 18px;
        }

        .data-fieldp {
            margin: 0;
            padding: 5px;
            background-color: blue;
            border-radius: 3px;
        }

        table {
            font-family: arial, sans-serif;
            border-collapse: collapse;
            width: 100%;
        }

        h1 {
            text-align: center;
            color: #db1811ff;
        }

        td,
        th {
            border: 1px solid #3b47b8ff;
            text-align: center;
            padding: 8px;
        }

        tr:nth-child(even) {
            background-color: #a0f5f8ff;
        }

        .back-link {
            display: inline-block;
            padding: 10px 20px;
            background-color: #4e0848;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .back-link:hover {
            background-color: #a04598;
        }
    </style>
</head>

<body>
    <div class="container">

        <h1>Gracias por tus respuestas!!</h1>

        <div class="data-field">
            <h2>Nombre</h2>
            <p> <?php echo htmlspecialchars($nombre_relleno); ?> </p>
        </div>
        <div class="data-field">
            <h2>Sexo</h2>
            <p> <?php echo htmlspecialchars($sexo_relleno); ?> </p>
        </div>
        <div class="data-field">
            <h2>Edad</h2>
            <p> <?php echo htmlspecialchars($edad_relleno); ?> </p>
        </div>
        <div class="data-field">
            <h2>Cumpleaños</h2>
            <p> <?php echo htmlspecialchars($bday_relleno); ?> </p>
        </div>
        <div class="data-field">
            <h2>Selfie</h2>
            <img src=" <?php echo $rutaArchivo; ?>" alt="Tu imagen" width="400">

        </div>
        <div class="data-field">
            <h2>Telefono</h2>
            <p> <?php echo htmlspecialchars($telefono_relleno); ?> </p>
        </div>
        <div class="data-field">
            <h2>Correo</h2>
            <p> <?php echo htmlspecialchars($correo_relleno); ?> </p>
        </div>
        <div class="data-field">
            <h2>Domicilio</h2>
            <p> <?php echo nl2br(htmlspecialchars($domicilio_relleno)); ?> </p>
        </div>
        
        <div class="table">

            <table class="table table-success table-striped">
                <thead class="table-dark">
                    <tr>
                        <th scope="col">Nombre</th>
                        <th scope="col">Apellidos</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><?php //var_dump($lineas);
                    $lineas = explode(",", $lineas);
                    foreach ($lineas as $linea) {
                        $name = explode(" ", $linea);
                        ?>
                            <th scope="col"><?php echo $name[0]; ?></th>
                            <th scope="col"><?php echo $name[1]; ?></th>
                        </tr>
                    <?php } ?>
                </tbody>
            </table> <br> <br>
        </div>
        <table class="table">
            <tbody>
                <tr>
                    <?php
                    if (!empty($datosExcel)) {
                        echo "<table border='1' cellpadding='5'>";
                        
                        $isFirstRow = true;
                        foreach ($datosExcel as $fila) {
                            echo "<tr>";
                            $colIndex = 0;
                            foreach ($fila as $value) {
                                $style = "";

                                    // Para la primera fila 
                                    if ($isFirstRow) {
                                        $style = "background-color: #4e4e4eab; font-weight:bold;";
                                    } else {
                                        // Aplica los estilos según el tipo de dato y la columna
                                        if ($colIndex == 1) { //segunda columna
                                            if (strtoupper($value) === "H") {
                                                $style = "background-color: #23c7c7ff; font-weight:bold;";
                                            } elseif (strtoupper($value) === "M") {
                                                $style = "background-color: #e33abbff; font-weight:bold;";
                                            }
                                        } elseif ($colIndex == 2) { //tercera columna
                                            if (is_numeric($value)) {
                                                if ((int) $value < 18) {
                                                    $style = "background-color: #fa3d03ff; font-weight:bold;";
                                                } else {
                                                    $style = "background-color: #8279c5ff; font-weight:bold;";
                                                }
                                            }
                                        } elseif ($colIndex == 3) { //cuarta columna
                                            if (validarEmail($value)) {
                                                $style = "background-color: #5de51fff; font-weight:bold;";
                                            } else {
                                                $style = "background-color: #ff0342ff; font-weight:bold;";
                                            } 
                            }
                        }

                        echo "<td style='$style'>" . htmlspecialchars($value) . "</td>";
                        $colIndex++;
                    }
                    echo "</tr>";
                    $isFirstRow = false;
                }
                echo "</table>";
            } else {
                echo "<p style='color:red;'>No se pudieron procesar los datos del archivo.</p>";
            }
            ?> <br><br>
        <div>
            <a href="Formulario.html" class="back-link">Volver al formulario</a> <br><br>
        </div>
    </div>
</body>

</html>