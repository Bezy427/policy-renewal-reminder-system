<?php
declare(strict_types=1);

class DocumentService
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getByPolicy(int $policyId): array
    {
        $stmt = $this->db->prepare(
            "SELECT d.*, u.full_name AS uploader_name
             FROM documents d
             JOIN users u ON u.id = d.uploaded_by
             WHERE d.policy_id = :pid
             ORDER BY d.uploaded_at DESC"
        );
        $stmt->execute([':pid' => $policyId]);
        return array_map(fn($r) => new Document($r), $stmt->fetchAll());
    }

    public function getById(int $id): ?Document
    {
        $stmt = $this->db->prepare(
            "SELECT d.*, u.full_name AS uploader_name, p.policy_number
             FROM documents d
             JOIN users u ON u.id = d.uploaded_by
             JOIN policies p ON p.id = d.policy_id
             WHERE d.id = :id LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ? new Document($row) : null;
    }

    public function upload(array $file, int $policyId, int $uploadedBy): array
    {
        // Validate upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return [false, 'File upload error. Please try again.'];
        }

        $maxBytes = UPLOAD_MAX_MB * 1024 * 1024;
        if ($file['size'] > $maxBytes) {
            return [false, 'File exceeds maximum size of ' . UPLOAD_MAX_MB . ' MB.'];
        }

        $finfo    = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if (!in_array($mimeType, ALLOWED_TYPES, true)) {
            return [false, 'Invalid file type. Only JPG, PNG, and PDF are allowed.'];
        }

        $originalName = basename($file['name']);
        $ext          = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if (!in_array($ext, ALLOWED_EXTS, true)) {
            return [false, 'Invalid file extension.'];
        }

        $storedName = sprintf('%s_%s.%s', uniqid('doc_', true), bin2hex(random_bytes(4)), $ext);
        $destination = UPLOAD_DIR . $storedName;

        if (!is_dir(UPLOAD_DIR)) {
            mkdir(UPLOAD_DIR, 0755, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return [false, 'Failed to save the file. Please try again.'];
        }

        $stmt = $this->db->prepare(
            "INSERT INTO documents (policy_id, file_name, stored_name, file_type, file_size, uploaded_by)
             VALUES (:pid, :fn, :sn, :ft, :fs, :ub)"
        );
        $stmt->execute([
            ':pid' => $policyId,
            ':fn'  => $originalName,
            ':sn'  => $storedName,
            ':ft'  => $mimeType,
            ':fs'  => $file['size'],
            ':ub'  => $uploadedBy,
        ]);

        return [true, 'Document uploaded successfully.'];
    }

    public function delete(int $id): bool
    {
        $doc = $this->getById($id);
        if (!$doc) return false;

        if (file_exists($doc->getStoredPath())) {
            unlink($doc->getStoredPath());
        }

        $stmt = $this->db->prepare('DELETE FROM documents WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }
}
