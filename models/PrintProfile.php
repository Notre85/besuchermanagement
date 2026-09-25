<?php

namespace App;

class PrintProfile extends BaseModel
{
    public function getActiveTemplateVersions(): array
    {
        return $this->pdo->query(
            'SELECT print_template_versions.id, print_template_versions.version_no,
                    print_templates.name, print_templates.purpose, print_template_versions.format
             FROM print_template_versions JOIN print_templates ON print_templates.id = print_template_versions.template_id
             WHERE print_template_versions.active = 1 AND print_templates.active = 1
             ORDER BY print_templates.name'
        )->fetchAll();
    }

    public function create(string $name, string $outputType, int $printerId, int $versionId, ?int $locationId): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO print_profiles (name, output_type, printer_id, template_version_id, location_id)
             VALUES (:name, :output_type, :printer_id, :version_id, :location_id)'
        );
        $stmt->execute(['name' => $name, 'output_type' => $outputType, 'printer_id' => $printerId, 'version_id' => $versionId, 'location_id' => $locationId]);
        return (int) $this->pdo->lastInsertId();
    }

    public function versionMatchesOutput(int $versionId, string $outputType): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM print_template_versions v
             JOIN print_templates t ON t.id = v.template_id
             WHERE v.id = :version_id AND v.active = 1 AND t.active = 1 AND t.purpose = :purpose'
        );
        $stmt->execute(['version_id' => $versionId, 'purpose' => $outputType]);
        return (int) $stmt->fetchColumn() === 1;
    }

    public function getAll(): array
    {
        return $this->pdo->query(
            'SELECT print_profiles.*, printers.name AS printer_name, printers.printer_uri,
                    print_template_versions.version_no, print_templates.name AS template_name
             FROM print_profiles
             JOIN printers ON printers.id = print_profiles.printer_id
             JOIN print_template_versions ON print_template_versions.id = print_profiles.template_version_id
             JOIN print_templates ON print_templates.id = print_template_versions.template_id
             WHERE print_profiles.active = 1 ORDER BY print_profiles.name'
        )->fetchAll();
    }

    public function findForOutput(string $outputType, ?int $locationId = null): ?array
    {
        $sql = 'SELECT print_profiles.*, printers.name AS printer_name, printers.printer_uri,
                       print_template_versions.format, print_template_versions.source,
                       print_template_versions.variables_json, print_template_versions.page_width,
                       print_template_versions.page_height
                FROM print_profiles
                JOIN printers ON printers.id = print_profiles.printer_id
                JOIN print_template_versions ON print_template_versions.id = print_profiles.template_version_id
                WHERE print_profiles.active = 1 AND printers.active = 1
                  AND print_profiles.output_type = :output_type
                  AND print_template_versions.active = 1';
        if ($locationId !== null) {
            $sql .= ' ORDER BY (print_profiles.location_id = :location_id) DESC, print_profiles.id LIMIT 1';
        } else {
            $sql .= ' ORDER BY print_profiles.id LIMIT 1';
        }
        $stmt = $this->pdo->prepare($sql);
        $params = ['output_type' => $outputType];
        if ($locationId !== null) $params['location_id'] = $locationId;
        $stmt->execute($params);
        return $stmt->fetch() ?: null;
    }
}
