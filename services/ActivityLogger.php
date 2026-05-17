<?php
declare(strict_types=1);

class ActivityLogger
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function log(
        ?int    $userId,
        string  $action,
        ?string $entity   = null,
        ?int    $entityId = null,
        ?string $detail   = null
    ): void {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO activity_log (user_id, action, entity, entity_id, detail)
                 VALUES (:uid, :ac, :en, :eid, :dt)"
            );
            $stmt->execute([
                ':uid' => $userId,
                ':ac'  => $action,
                ':en'  => $entity,
                ':eid' => $entityId,
                ':dt'  => $detail,
            ]);
        } catch (PDOException $e) {
            error_log('ActivityLogger error: ' . $e->getMessage());
        }
    }
}
