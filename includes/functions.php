<?php
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function uploadFile($file, $target_dir) {
    // Verifica erros de upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Erro no upload do arquivo.'];
    }
    
    // Verifica se é uma imagem
    $check = getimagesize($file["tmp_name"]);
    if ($check === false) {
        return ['success' => false, 'error' => 'O arquivo não é uma imagem válida.'];
    }
    
    // Verifica o tipo do arquivo
    $imageFileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    
    if (!in_array($imageFileType, $allowed_types)) {
        return ['success' => false, 'error' => 'Apenas arquivos JPG, JPEG, PNG e GIF são permitidos.'];
    }
    
    // Verifica tamanho do arquivo (2MB máximo)
    if ($file["size"] > 2000000) {
        return ['success' => false, 'error' => 'O arquivo é muito grande (máximo 2MB).'];
    }
    
    // Gera um nome único para o arquivo
    $new_filename = uniqid('profile_', true) . '.' . $imageFileType;
    $target_path = $target_dir . $new_filename;
    
    // Move o arquivo para o diretório de uploads
    if (move_uploaded_file($file["tmp_name"], $target_path)) {
        return ['success' => true, 'filename' => $new_filename];
    } else {
        return ['success' => false, 'error' => 'Erro ao mover o arquivo para o servidor.'];
    }
}

