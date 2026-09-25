<?php

namespace App;

class PrintTemplate extends BaseModel
{
    public function getAll(): array
    {
        return $this->pdo->query(
            'SELECT print_templates.*, COUNT(print_template_versions.id) AS version_count
             FROM print_templates LEFT JOIN print_template_versions ON print_template_versions.template_id = print_templates.id
             GROUP BY print_templates.id ORDER BY print_templates.purpose, print_templates.name'
        )->fetchAll();
    }

    public function getActiveVersion(int $templateId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT print_template_versions.*, print_templates.name, print_templates.purpose
             FROM print_template_versions JOIN print_templates ON print_templates.id = print_template_versions.template_id
             WHERE print_template_versions.template_id = :id AND print_template_versions.active = 1
             ORDER BY print_template_versions.version_no DESC LIMIT 1'
        );
        $stmt->execute(['id' => $templateId]);
        return $stmt->fetch() ?: null;
    }

    public function findVersion(int $versionId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM print_template_versions WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $versionId]);
        return $stmt->fetch() ?: null;
    }

    public function getVersions(int $templateId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM print_template_versions WHERE template_id = :id ORDER BY version_no DESC');
        $stmt->execute(['id' => $templateId]);
        return $stmt->fetchAll();
    }

    public function create(string $name, string $purpose, int $userId): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO print_templates (name, purpose, created_by) VALUES (:name, :purpose, :user_id)');
        $stmt->execute(['name' => $name, 'purpose' => $purpose, 'user_id' => $userId]);
        return (int) $this->pdo->lastInsertId();
    }

    public function createVersion(int $templateId, string $format, string $source, array $variables, ?float $width, ?float $height, int $userId): int
    {
        $stmt = $this->pdo->prepare('SELECT COALESCE(MAX(version_no), 0) + 1 FROM print_template_versions WHERE template_id = :id');
        $stmt->execute(['id' => $templateId]);
        $version = (int) $stmt->fetchColumn();
        $insert = $this->pdo->prepare(
            'INSERT INTO print_template_versions (template_id, version_no, format, source, page_width, page_height, variables_json, created_by)
             VALUES (:template_id, :version_no, :format, :source, :width, :height, :variables, :user_id)'
        );
        $insert->execute([
            'template_id' => $templateId, 'version_no' => $version, 'format' => $format,
            'source' => $source, 'width' => $width, 'height' => $height,
            'variables' => json_encode(array_values($variables), JSON_THROW_ON_ERROR), 'user_id' => $userId,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function activateVersion(int $templateId, int $versionId): bool
    {
        $this->pdo->beginTransaction();
        try {
            $off = $this->pdo->prepare('UPDATE print_template_versions SET active = 0 WHERE template_id = :template_id');
            $off->execute(['template_id' => $templateId]);
            $on = $this->pdo->prepare('UPDATE print_template_versions SET active = 1 WHERE id = :id AND template_id = :template_id');
            $on->execute(['id' => $versionId, 'template_id' => $templateId]);
            if ($on->rowCount() !== 1) {
                throw new \RuntimeException('Vorlagenversion nicht gefunden.');
            }
            $this->pdo->commit();
            return true;
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            throw $e;
        }
    }
}
