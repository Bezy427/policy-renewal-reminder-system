<?php
declare(strict_types=1);

class User
{
    public int    $id;
    public string $fullName;
    public string $email;
    public string $password;
    public string $role;
    public bool   $isActive;
    public string $createdAt;
    public string $updatedAt;

    public function __construct(array $row)
    {
        $this->id        = (int)  $row['id'];
        $this->fullName  =        $row['full_name'];
        $this->email     =        $row['email'];
        $this->password  =        $row['password'];
        $this->role      =        $row['role'];
        $this->isActive  = (bool) $row['is_active'];
        $this->createdAt =        $row['created_at'];
        $this->updatedAt =        $row['updated_at'];
    }

    public function isAdmin(): bool          { return $this->role === 'admin'; }
    public function isPolicyOfficer(): bool  { return $this->role === 'policy_officer'; }
    public function isViewer(): bool         { return $this->role === 'viewer'; }

    public function canManagePolicies(): bool
    {
        return in_array($this->role, ['admin', 'policy_officer'], true);
    }

    public function canManageUsers(): bool
    {
        return $this->isAdmin();
    }

    public function getRoleLabel(): string
    {
        return match ($this->role) {
            'admin'          => 'Administrator',
            'policy_officer' => 'Policy Officer',
            'viewer'         => 'Viewer',
            default          => 'Unknown',
        };
    }
}
