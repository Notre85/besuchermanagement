<?php
// controllers/BaseController.php

namespace App\Controllers;

use PDO;

// Einbinden der notwendigen Dateien
require_once __DIR__ . '/../config/csrf.php'; // CSRF-Schutz einbinden

class BaseController {
    protected $pdo;
    protected $logger;

    public function __construct(PDO $pdo, $logger) {
        $this->pdo    = $pdo;
        $this->logger = $logger;
    }

    /**
     * Rendert eine View-Datei und übergibt Daten.
     *
     * @param string $view
     * @param array $data
     */
    protected function render($view, $data = []) {
        extract($data);
        include __DIR__ . "/../views/$view.php";
    }

    /**
     * Leitet zu einer anderen URL weiter.
     *
     * @param string $url
     */
    protected function redirect($url) {
        if (!empty($_POST['kiosk']) && substr($url, 0, 10) === 'index.php?') {
            $query = [];
            parse_str(substr($url, 10), $query);
            if (isset($query['success'])) {
                $_SESSION['kiosk_flash'] = ['type' => 'success', 'code' => (string) $query['success']];
            } elseif (isset($query['error'])) {
                $_SESSION['kiosk_flash'] = ['type' => 'error', 'code' => (string) $query['error']];
            }
            $url = 'kiosk.php';
        }
        header("Location: $url");
        exit();
    }

    /**
     * Überprüft, ob der Benutzer eingeloggt ist.
     *
     * @return bool
     */
    protected function isLoggedIn() {
        return isset($_SESSION['user']);
    }

    /**
     * Erzwingt die Anmeldung des Benutzers.
     */
    protected function requireLogin() {
        if (!$this->isLoggedIn()) {
            $this->redirect('login.php');
        }
    }

    protected function requireRole(array $roles) {
        $this->requireLogin();
        $user = $this->getCurrentUser();
        if (!$user || !in_array($user['role'] ?? '', $roles, true)) {
            http_response_code(403);
            exit('Zugriff verweigert.');
        }
    }

    protected function requirePost() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            exit('Methode nicht erlaubt.');
        }
    }

    protected function requireCsrf() {
        if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
            $this->logger->warning('CSRF-Token-Validierung fehlgeschlagen.');
            http_response_code(403);
            exit('Ungültiges CSRF-Token.');
        }
    }

    protected function audit($action, $entityType = null, $entityId = null, $result = 'success', array $metadata = []) {
        try {
            $requestId = sprintf('%s-%s-%s-%s-%s', bin2hex(random_bytes(4)), bin2hex(random_bytes(2)), bin2hex(random_bytes(2)), bin2hex(random_bytes(2)), bin2hex(random_bytes(6)));
            $stmt = $this->pdo->prepare(
                'INSERT INTO audit_log (user_id, action, entity_type, entity_id, request_id, result, ip_address, device_id, metadata_json)
                 VALUES (:user_id, :action, :entity_type, :entity_id, :request_id, :result, :ip_address, :device_id, :metadata_json)'
            );
            $stmt->execute([
                'user_id' => $_SESSION['user']['id'] ?? null,
                'action' => $action,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'request_id' => $requestId,
                'result' => $result,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'device_id' => $_POST['kiosk'] ?? null,
                'metadata_json' => $metadata ? json_encode($metadata, JSON_THROW_ON_ERROR) : null,
            ]);
        } catch (\Throwable $e) {
            $this->logger->error('Audit-Eintrag konnte nicht geschrieben werden.');
        }
    }

    /**
     * Holt den aktuellen Benutzer aus der Session.
     *
     * @return array|null
     */
    protected function getCurrentUser() {
        if ($this->isLoggedIn()) {
            return $_SESSION['user'];
        }
        return null;
    }
}
?>
