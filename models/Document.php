<?php
declare(strict_types=1);


class Document
{
    public int    $id;
    public int    $policyId;
    public string $fileName;
    public string $storedName;
    public string $fileType;
    public int    $fileSize;
    public int    $uploadedBy;
    public string $uploadedAt;

    public ?string $uploaderName;
    public ?string $policyNumber;

    public function __construct(array $row)
    {
        $this->id           = (int) $row['id'];
        $this->policyId     = (int) $row['policy_id'];
        $this->fileName     =       $row['file_name'];
        $this->storedName   =       $row['stored_name'];
        $this->fileType     =       $row['file_type'];
        $this->fileSize     = (int) $row['file_size'];
        $this->uploadedBy   = (int) $row['uploaded_by'];
        $this->uploadedAt   =       $row['uploaded_at'];
        $this->uploaderName =       $row['uploader_name'] ?? null;
        $this->policyNumber =       $row['policy_number'] ?? null;
    }

    public function getFileSizeFormatted(): string
    {
        $kb = $this->fileSize / 1024;
        return $kb < 1024
            ? round($kb, 1) . ' KB'
            : round($kb / 1024, 2) . ' MB';
    }

    public function getFileIcon(): string
    {
        return match (true) {
            str_contains($this->fileType, 'pdf')   => '📄',
            str_contains($this->fileType, 'image') => '🖼️',
            default                                 => '📎',
        };
    }

    public function getStoredPath(): string
    {
        return UPLOAD_DIR . $this->storedName;
    }
}
