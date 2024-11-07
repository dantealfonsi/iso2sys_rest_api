<?php
if (isset($_GET['file'])) {
    $file = $_GET['file'];
    $filePath = "guides/" . basename($file);

    if (file_exists($filePath)) {
        // Obtener información del archivo
        $fileInfo = pathinfo($filePath);
        $fileName = $fileInfo['basename'];

        // Establecer cabeceras para la descarga
        header("Content-Description: File Transfer");
        header("Content-Type: application/octet-stream");
        header("Content-Disposition: attachment; filename=\"$fileName\"");
        header("Expires: 0");
        header("Cache-Control: must-revalidate");
        header("Pragma: public");
        header("Content-Length: " . filesize($filePath));

        // Leer el archivo y enviarlo al navegador
        readfile($filePath);
        exit;
    } else {
        echo "El archivo no existe.";
    }
} else {
    echo "No se ha especificado ningún archivo.";
}
?>