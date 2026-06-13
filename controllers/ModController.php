<?php

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Upload.php';
require_once __DIR__ . '/../models/Mod.php';
require_once __DIR__ . '/../models/ModVersion.php';
require_once __DIR__ . '/../models/Game.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../services/NotificationService.php';

class ModController
{
    private Mod $modModel;
    private ModVersion $versionModel;
    private Game $gameModel;
    private Category $categoryModel;

    public function __construct()
    {
        $this->modModel = new Mod();
        $this->versionModel = new ModVersion();
        $this->gameModel = new Game();
        $this->categoryModel = new Category();
    }

    public function index(): void
    {
        $user = Auth::user();
        $mods = $this->modModel->allVisible($user['id'], $user['role']);
        require __DIR__ . '/../views/mods/index.php';
    }

    public function show(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $mod = $this->modModel->findById($id);

        if (!$mod) {
            http_response_code(404);
            echo '404 Mod não encontrado.';
            return;
        }

        $user = Auth::user();

        if (!$this->modModel->isVisible($mod, $user['id'], $user['role'])) {
            http_response_code(403);
            echo 'Acesso negado.';
            return;
        }

        $categories = $this->modModel->getCategories($id);
        $images = $this->modModel->getImages($id);
        $versions = $this->versionModel->findByModId($id);
        require __DIR__ . '/../views/mods/show.php';
    }

    public function createForm(): void
    {
        Auth::require('user');
        $games = $this->gameModel->all();
        $categories = $this->categoryModel->all();
        require __DIR__ . '/../views/mods/create.php';
    }

    public function store(): void
    {
        Auth::require('user');

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        // Safely determine visibility, default to 'public' if not provided
        $visibility = (!empty($_POST['visibility']) && $_POST['visibility'] === 'private') ? 'private' : 'public';
        $gameId = (int)($_POST['game_id'] ?? 0);
        $categoryIds = array_map('intval', (array)($_POST['category_ids'] ?? []));

        if (!$title || !$description || !$gameId) {
            $error = Lang::t('fill_required_fields');
            $games = $this->gameModel->all();
            $categories = $this->categoryModel->all();
            require __DIR__ . '/../views/mods/create.php';
            return;
        }

        if (strlen($title) < 3 || strlen($title) > 150) {
            $error = Lang::t('js_title_length');
            $games = $this->gameModel->all();
            $categories = $this->categoryModel->all();
            require __DIR__ . '/../views/mods/create.php';
            return;
        }

        if (strlen($description) < 10) {
            $error = Lang::t('js_desc_length');
            $games = $this->gameModel->all();
            $categories = $this->categoryModel->all();
            require __DIR__ . '/../views/mods/create.php';
            return;
        }

        if (count($categoryIds) !== 2) {
            $error = Lang::t('js_select_2_categories');
            $games = $this->gameModel->all();
            $categories = $this->categoryModel->all();
            require __DIR__ . '/../views/mods/create.php';
            return;
        }

        if (empty($_FILES['cover_image']['name']) || $_FILES['cover_image']['error'] === UPLOAD_ERR_NO_FILE) {
            $error = Lang::t('js_cover_required');
            $games = $this->gameModel->all();
            $categories = $this->categoryModel->all();
            require __DIR__ . '/../views/mods/create.php';
            return;
        }

        if (empty($_FILES['mod_file']['name']) || $_FILES['mod_file']['error'] === UPLOAD_ERR_NO_FILE) {
            $error = Lang::t('js_mod_file_required');
            $games = $this->gameModel->all();
            $categories = $this->categoryModel->all();
            require __DIR__ . '/../views/mods/create.php';
            return;
        }

        $game = $this->gameModel->findById($gameId);
        if (!$game) {
            $error = Lang::t('err_game_not_exists');
            $games = $this->gameModel->all();
            $categories = $this->categoryModel->all();
            require __DIR__ . '/../views/mods/create.php';
            return;
        }

        $videoPath = null;
        try {
            $coverPath = Upload::image($_FILES['cover_image'], 'covers');
            $filePath = Upload::mod($_FILES['mod_file'], $game['allowed_extensions'] ?? 'zip');
            if (!empty($_FILES['demo_video']['name'])) {
                $videoPath = Upload::video($_FILES['demo_video']);
            }
        } catch (RuntimeException $e) {
            $error = $e->getMessage();
            $games = $this->gameModel->all();
            $categories = $this->categoryModel->all();
            require __DIR__ . '/../views/mods/create.php';
            return;
        }


        $modId = $this->modModel->create([
            'title' => $title,
            'description' => $description,
            'cover_image_path' => $coverPath,
            'file_path' => $filePath,
            'video_path' => $videoPath,
            'visibility' => $visibility,
            'game_id' => $gameId,
            'uploaded_by' => Auth::id(),
        ]);

        if ($categoryIds) {
            $this->modModel->attachCategories($modId, $categoryIds);
        }

        $extraImages = $_FILES['extra_images'] ?? [];
        if (!empty($extraImages['name'][0])) {
            foreach ($extraImages['name'] as $index => $name) {
                if ($extraImages['error'][$index] !== UPLOAD_ERR_OK) {
                    continue;
                }
                $singleFile = [
                    'name' => $name,
                    'type' => $extraImages['type'][$index],
                    'tmp_name' => $extraImages['tmp_name'][$index],
                    'error' => $extraImages['error'][$index],
                    'size' => $extraImages['size'][$index],
                ];
                try {
                    $imagePath = Upload::image($singleFile, 'mods');
                    $this->modModel->addImage($modId, $imagePath, $index);
                } catch (RuntimeException) {
                    continue;
                }
            }
        }

        // Notify subscribers of the new mod
        if ($visibility === 'public') {
            NotificationService::notifySubscribers($modId);
        }

        header('Location: ' . BASE_URL . '/mods?created=1');
        exit;
    }

    public function download(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $mod = $this->modModel->findById($id);

        if (!$mod) {
            http_response_code(404);
            echo 'Mod não encontrado.';
            return;
        }

        $user = Auth::user();

        if (!$this->modModel->isVisible($mod, $user['id'], $user['role'])) {
            http_response_code(403);
            echo 'Acesso negado.';
            return;
        }

        $this->modModel->incrementDownload($id);

        // usa o ficheiro da versão mais recente, se houver
        $latestVersion = $this->versionModel->latestByModId($id);
        $relativePath = $latestVersion ? $latestVersion['file_path'] : $mod['file_path'];
        if (defined('BASE_URL') && strpos($relativePath, BASE_URL) === 0) {
            $relativePath = substr($relativePath, strlen(BASE_URL));
        }
        $fullPath = __DIR__ . '/../public' . $relativePath;

        if (!file_exists($fullPath)) {
            http_response_code(404);
            echo 'Ficheiro não encontrado no servidor.';
            return;
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($fullPath) . '"');
        header('Content-Length: ' . filesize($fullPath));
        readfile($fullPath);
        exit;
    }

    public function delete(): void
    {
        Auth::require('user');

        $id = (int)($_GET['id'] ?? 0);
        $mod = $this->modModel->findById($id);

        if (!$mod) {
            header('Location: ' . BASE_URL . '/mods');
            exit;
        }

        $user = Auth::user();

        if (!$this->modModel->canDelete($id, $user['id'], $user['role'])) {
            http_response_code(403);
            echo 'Acesso negado.';
            return;
        }

        if (!empty($mod['cover_image_path'])) {
            Upload::delete($mod['cover_image_path']);
        }
        if (!empty($mod['file_path'])) {
            Upload::delete($mod['file_path']);
        }
        if (!empty($mod['video_path'])) {
            Upload::delete($mod['video_path']);
        }

        foreach ($this->modModel->getImages($id) as $image) {
            if (!empty($image['image_path'])) {
                Upload::delete($image['image_path']);
            }
        }

        $this->modModel->delete($id);
        header('Location: ' . BASE_URL . '/mods');
        exit;
    }

    public function toggleVisibility(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = (int) ($_GET['id'] ?? 0);
        $mod = $this->modModel->findById($id);

        if (!$mod) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Mod não encontrado.']);
            exit;
        }

        if (!Auth::isOwnerOrAdmin((int)$mod['uploaded_by'])) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Acesso negado.']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $visibility = trim($input['visibility'] ?? '');

        if ($visibility !== 'public' && $visibility !== 'private') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Visibilidade inválida.']);
            exit;
        }

        $success = $this->modModel->updateVisibility($id, $visibility);
        if ($success) {
            echo json_encode([
                'success' => true,
                'visibility' => $visibility,
                'label' => $visibility === 'private' ? Lang::t('private') : Lang::t('public')
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Falha ao atualizar a visibilidade.']);
        }
        exit;
    }

    public function addVersion(): void
    {
        Auth::require('user');

        $id = (int)($_GET['id'] ?? 0);
        $mod = $this->modModel->findById($id);

        if (!$mod) {
            http_response_code(404);
            echo 'Mod não encontrado.';
            return;
        }

        if (!Auth::isOwnerOrAdmin((int)$mod['uploaded_by'])) {
            http_response_code(403);
            echo 'Acesso negado.';
            return;
        }

        $version   = trim($_POST['version'] ?? '');
        $changelog = trim($_POST['changelog'] ?? '');

        if (!$version || !$changelog) {
            $_SESSION['message']   = Lang::t('msg_version_required');
            $_SESSION['toastClass'] = 'alert-danger';
            header('Location: ' . BASE_URL . '/mods/' . $id);
            exit;
        }

        if (empty($_FILES['version_file']['name']) || $_FILES['version_file']['error'] === UPLOAD_ERR_NO_FILE) {
            $_SESSION['message']   = Lang::t('msg_version_file_required');
            $_SESSION['toastClass'] = 'alert-danger';
            header('Location: ' . BASE_URL . '/mods/' . $id);
            exit;
        }

        try {
            $game = $this->gameModel->findById((int)$mod['game_id']);
            $filePath = Upload::mod($_FILES['version_file'], $game['allowed_extensions'] ?? 'zip');
        } catch (RuntimeException $e) {
            $_SESSION['message']   = $e->getMessage();
            $_SESSION['toastClass'] = 'alert-danger';
            header('Location: ' . BASE_URL . '/mods/' . $id);
            exit;
        }

        $this->versionModel->create($id, $version, $filePath, $changelog);

        $_SESSION['message']   = Lang::t('msg_version_added');
        $_SESSION['toastClass'] = 'alert-success';
        header('Location: ' . BASE_URL . '/mods/' . $id);
        exit;
    }

    public function importBatchForm(): void
    {
        Auth::require('sympathizer');
        $error = '';
        require __DIR__ . '/../views/mods/import_batch.php';
    }

    public function importBatch(): void
    {
        Auth::require('sympathizer');

        // Verificar erros de upload no PHP (como exceder o upload_max_filesize de 40MB)
        if (isset($_FILES['batch_file']) && $_FILES['batch_file']['error'] !== UPLOAD_ERR_OK) {
            $errCode = $_FILES['batch_file']['error'];
            switch ($errCode) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $error = 'O ficheiro ZIP excede o tamanho máximo de upload permitido pelo servidor (40MB).';
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $error = 'O upload do ficheiro foi feito apenas parcialmente.';
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $error = 'Nenhum ficheiro foi submetido.';
                    break;
                default:
                    $error = 'Erro no upload do ficheiro (código ' . $errCode . ').';
            }
            require __DIR__ . '/../views/mods/import_batch.php';
            return;
        }

        if (empty($_FILES['batch_file']['name'])) {
            $error = 'Por favor, selecione o ficheiro ZIP de lote.';
            require __DIR__ . '/../views/mods/import_batch.php';
            return;
        }

        $zipFile = $_FILES['batch_file']['tmp_name'];

        if (!class_exists('ZipArchive')) {
            $error = 'A extensão ZipArchive não está instalada ou ativada no PHP.';
            require __DIR__ . '/../views/mods/import_batch.php';
            return;
        }

        $zip = new ZipArchive();
        if ($zip->open($zipFile) !== true) {
            $error = 'Não foi possível abrir o ficheiro ZIP.';
            require __DIR__ . '/../views/mods/import_batch.php';
            return;
        }

        // Criar diretório temporário único
        $tempExtractDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'modyssey_batch_' . bin2hex(random_bytes(8));
        if (!mkdir($tempExtractDir, 0755, true)) {
            $error = 'Falha ao criar diretório temporário para extração.';
            require __DIR__ . '/../views/mods/import_batch.php';
            return;
        }

        $zip->extractTo($tempExtractDir);
        $zip->close();

        // Procurar por ficheiro XML
        $xmlPath = null;
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($tempExtractDir));
        foreach ($files as $file) {
            if ($file->isFile() && strtolower($file->getExtension()) === 'xml') {
                $xmlPath = $file->getRealPath();
                break;
            }
        }

        if (!$xmlPath) {
            // Limpar pasta
            self::rrmdir($tempExtractDir);
            $error = 'Ficheiro de meta-informação XML não encontrado dentro do ZIP.';
            require __DIR__ . '/../views/mods/import_batch.php';
            return;
        }

        try {
            $xml = simplexml_load_file($xmlPath);
            if ($xml === false) {
                throw new Exception("Ficheiro XML inválido ou malformado.");
            }

            $importedCount = 0;
            $warnings = [];
            $modIndex = 0;

            foreach ($xml->mod as $modNode) {
                $modIndex++;
                $title       = trim((string)$modNode->title);
                $description = trim((string)$modNode->description);
                $visibility  = trim((string)$modNode->visibility) === 'private' ? 'private' : 'public';
                $gameId      = (int)$modNode->game_id;
                $coverName   = trim((string)$modNode->cover_image);
                $modFileName = trim((string)$modNode->mod_file);
                $videoName   = isset($modNode->video_file) ? trim((string)$modNode->video_file) : '';

                if (!$title || !$description || !$gameId || !$coverName || !$modFileName) {
                    $warnings[] = "Mod #$modIndex ignorado: Faltam campos obrigatórios (Título, Descrição, ID do Jogo, Imagem de Capa ou Ficheiro do Mod).";
                    continue;
                }

                // Validar se o ID do Jogo existe, ou tentar procurar por nome caso o XML tenha o nome em vez de ID
                $game = null;
                if (is_numeric($gameId)) {
                    $game = $this->gameModel->findById((int)$gameId);
                }
                
                if (!$game) {
                    // Tentar procurar jogo por correspondência de nome
                    $xmlGameName = trim((string)$modNode->game_id);
                    if ($xmlGameName) {
                        $allGames = $this->gameModel->all();
                        foreach ($allGames as $g) {
                            if (strcasecmp($g['name'], $xmlGameName) === 0) {
                                $game = $g;
                                $gameId = (int)$g['id'];
                                break;
                            }
                        }
                    }
                }

                if (!$game) {
                    $warnings[] = "Mod \"$title\" ignorado: O ID ou Nome de Jogo \"$gameId\" não existe no sistema.";
                    continue;
                }

                // Caminhos dos ficheiros dentro da pasta temporária extraída (De forma case-insensitive)
                $xmlDir = dirname($xmlPath);
                $coverSource = self::findFileCaseInsensitive($xmlDir, $coverName) ?? self::findFileCaseInsensitive($tempExtractDir, $coverName);
                $modSource   = self::findFileCaseInsensitive($xmlDir, $modFileName) ?? self::findFileCaseInsensitive($tempExtractDir, $modFileName);
                $videoSource = $videoName ? (self::findFileCaseInsensitive($xmlDir, $videoName) ?? self::findFileCaseInsensitive($tempExtractDir, $videoName)) : null;

                if (!$coverSource) {
                    $warnings[] = "Mod \"$title\" ignorado: A imagem de capa \"$coverName\" não foi encontrada no ficheiro ZIP.";
                    continue;
                }

                if (!$modSource) {
                    $warnings[] = "Mod \"$title\" ignorado: O ficheiro ZIP do mod \"$modFileName\" não foi encontrado no ficheiro ZIP.";
                    continue;
                }

                // Copiar ficheiro de capa
                $destCoverDir = __DIR__ . '/../public/uploads/covers/';
                if (!is_dir($destCoverDir)) {
                    mkdir($destCoverDir, 0755, true);
                }
                $coverExt = pathinfo($coverSource, PATHINFO_EXTENSION);
                $coverFilename = bin2hex(random_bytes(16)) . '.' . ($coverExt ?: 'jpg');
                copy($coverSource, $destCoverDir . $coverFilename);
                $coverPath = BASE_URL . '/uploads/covers/' . $coverFilename;

                // Copiar ficheiro do mod
                $destModDir = __DIR__ . '/../public/uploads/mods/';
                if (!is_dir($destModDir)) {
                    mkdir($destModDir, 0755, true);
                }
                // Manter a extensão original do ficheiro do mod (validada contra as extensões do jogo)
                $modExt = strtolower(pathinfo($modSource, PATHINFO_EXTENSION)) ?: 'zip';
                $gameExtensions = Upload::parseExtensions($game['allowed_extensions'] ?? 'zip');
                if (!in_array('*', $gameExtensions, true) && !in_array($modExt, $gameExtensions, true)) {
                    $warnings[] = "Mod \"$title\" ignorado: A extensão \".$modExt\" não é permitida para este jogo (aceites: ." . implode(', .', $gameExtensions) . ").";
                    continue;
                }
                $modFilename = bin2hex(random_bytes(16)) . '.' . $modExt;
                copy($modSource, $destModDir . $modFilename);
                $filePath = BASE_URL . '/uploads/mods/' . $modFilename;

                // Copiar vídeo se existir
                $videoPath = null;
                if ($videoSource && file_exists($videoSource)) {
                    $destVideoDir = __DIR__ . '/../public/uploads/videos/';
                    if (!is_dir($destVideoDir)) {
                        mkdir($destVideoDir, 0755, true);
                    }
                    $videoExt = pathinfo($videoSource, PATHINFO_EXTENSION);
                    $videoFilename = bin2hex(random_bytes(16)) . '.' . ($videoExt ?: 'mp4');
                    copy($videoSource, $destVideoDir . $videoFilename);
                    $videoPath = BASE_URL . '/uploads/videos/' . $videoFilename;
                }

                // Criar o registo na base de dados
                $modId = $this->modModel->create([
                    'title' => $title,
                    'description' => $description,
                    'cover_image_path' => $coverPath,
                    'file_path' => $filePath,
                    'video_path' => $videoPath,
                    'visibility' => $visibility,
                    'game_id' => $gameId,
                    'uploaded_by' => Auth::id(),
                ]);

                // Associar categorias
                $categoryIds = [];
                if (isset($modNode->categories)) {
                    foreach ($modNode->categories->category_id as $catId) {
                        $categoryIds[] = (int)$catId;
                    }
                }
                if (!empty($categoryIds)) {
                    $this->modModel->attachCategories($modId, $categoryIds);
                }

                // Notificar subscritores
                if ($visibility === 'public') {
                    NotificationService::notifySubscribers($modId);
                }

                $importedCount++;
            }

            if (!empty($warnings)) {
                $warnStr = implode('<br>', array_map('htmlspecialchars', $warnings));
                $_SESSION['message'] = "Importação concluída. $importedCount mods importados com sucesso.<br><div style='text-align:left; margin-top:8px; font-size:0.85rem; max-height:150px; overflow-y:auto;'><strong>Avisos:</strong><br>$warnStr</div>";
                $_SESSION['toastClass'] = "alert-warning";
            } else {
                $_SESSION['message'] = "Importação em lote concluída com sucesso! $importedCount mods adicionados.";
                $_SESSION['toastClass'] = "alert-success";
            }

            header('Location: ' . BASE_URL . '/mods');
            exit;
        } catch (Exception $e) {
            $error = 'Erro ao processar ficheiros do lote: ' . $e->getMessage();
        } finally {
            self::rrmdir($tempExtractDir);
        }

        require __DIR__ . '/../views/mods/import_batch.php';
    }

    private static function rrmdir($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir . DIRECTORY_SEPARATOR . $object) && !is_link($dir . DIRECTORY_SEPARATOR . $object)) {
                        self::rrmdir($dir . DIRECTORY_SEPARATOR . $object);
                    } else {
                        unlink($dir . DIRECTORY_SEPARATOR . $object);
                    }
                }
            }
            rmdir($dir);
        }
    }

    private static function findFileCaseInsensitive(string $dir, string $filename): ?string
    {
        // Normalize filename paths (e.g. if XML references "enhanced_graphics_cover.jpg" or "testeLote/enhanced_graphics_cover.jpg")
        $filename = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $filename);
        
        $target = $dir . DIRECTORY_SEPARATOR . $filename;
        if (file_exists($target)) {
            return $target;
        }

        // Try direct recursive search within the extracted directory
        if (is_dir($dir)) {
            try {
                $iterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
                );
                
                $lowerFilename = strtolower(basename($filename));
                
                // If the XML specifies a subpath, get its lower parts
                $filenameParts = array_map('strtolower', explode(DIRECTORY_SEPARATOR, $filename));
                $partsCount = count($filenameParts);
                
                foreach ($iterator as $fileInfo) {
                    if ($fileInfo->isFile()) {
                        $filePath = $fileInfo->getRealPath();
                        
                        // Try matching just the basename first
                        if (strtolower($fileInfo->getBasename()) === $lowerFilename) {
                            // If XML had directories in the filename, verify they match too
                            if ($partsCount > 1) {
                                $realPathParts = array_reverse(array_map('strtolower', explode(DIRECTORY_SEPARATOR, $filePath)));
                                $match = true;
                                for ($i = 0; $i < $partsCount; $i++) {
                                    if (!isset($realPathParts[$i]) || $realPathParts[$i] !== $filenameParts[$partsCount - 1 - $i]) {
                                        $match = false;
                                        break;
                                    }
                                }
                                if ($match) {
                                    return $filePath;
                                }
                            } else {
                                return $filePath;
                            }
                        }
                    }
                }
            } catch (Exception $e) {
                // Fallback to basic scanning if recursive iterator fails
            }

            // Fallback: search scandir case insensitively (flat)
            $files = scandir($dir);
            $lowerFilename = strtolower($filename);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..' && strtolower($file) === $lowerFilename) {
                    return $dir . DIRECTORY_SEPARATOR . $file;
                }
            }
        }
        return null;
    }
}
