<?php

namespace App;

class AuditLog extends BaseModel
{
    public function record(string $action, ?string $entityType, ?int $entityId, string $result = 'success', ?string $deviceId = null, array $metadata = []): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO audit_log (user_id, action, entity_type, entity_id, request_id, result, ip_address, device_id, metadata_json)
             VALUES (:user_id, :action, :entity_type, :entity_id, :request_id, :result, :ip_address, :device_id, :metadata_json)'
        );
        $stmt->execute([
            'user_id' => $_SESSION['user']['id'] ?? null,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'request_id' => bin2hex(random_bytes(16)),
            'result' => $result,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'device_id' => $deviceId,
            'metadata_json' => $metadata ? json_encode($metadata, JSON_THROW_ON_ERROR) : null,
        ]);
    }

    public function search(array $filters, int $limit = 100): array
    {
        $where = ['1 = 1']; $params = [];
        if ($filters['from'] ?? false) { $where[] = 'audit_log.created_at >= :from_date'; $params['from_date'] = $filters['from'] . ' 00:00:00'; }
        if ($filters['to'] ?? false) { $where[] = 'audit_log.created_at <= :to_date'; $params['to_date'] = $filters['to'] . ' 23:59:59'; }
        if ($filters['action'] ?? false) { $where[] = 'audit_log.action LIKE :action'; $params['action'] = '%' . $filters['action'] . '%'; }
        if ($filters['result'] ?? false) { $where[] = 'audit_log.result = :result'; $params['result'] = $filters['result']; }
        if ($filters['user_id'] ?? false) { $where[] = 'audit_log.user_id = :user_id'; $params['user_id'] = (int) $filters['user_id']; }
        $limit = max(1, min(500, $limit));
        $stmt = $this->pdo->prepare(
            'SELECT audit_log.*, users.username
             FROM audit_log LEFT JOIN users ON users.id = audit_log.user_id
             WHERE ' . implode(' AND ', $where) . '
             ORDER BY audit_log.created_at DESC LIMIT ' . $limit
        );
        foreach ($params as $name => $value) $stmt->bindValue(':' . $name, $value, is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
