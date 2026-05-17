<?php
declare(strict_types=1);

class Policy
{
    public int    $id;
    public string $policyNumber;
    public string $clientName;
    public string $insuranceType;
    public float  $premiumAmount;
    public string $startDate;
    public string $renewalDate;
    public string $status;
    public int    $createdBy;
    public string $createdAt;
    public string $updatedAt;

    public ?string $creatorName;

    public function __construct(array $row)
    {
        $this->id            = (int)   $row['id'];
        $this->policyNumber  =         $row['policy_number'];
        $this->clientName    =         $row['client_name'];
        $this->insuranceType =         $row['insurance_type'];
        $this->premiumAmount = (float) $row['premium_amount'];
        $this->startDate     =         $row['start_date'];
        $this->renewalDate   =         $row['renewal_date'];
        $this->status        =         $row['status'];
        $this->createdBy     = (int)   $row['created_by'];
        $this->createdAt     =         $row['created_at'];
        $this->updatedAt     =         $row['updated_at'];
        $this->creatorName   =         $row['creator_name'] ?? null;
    }

    public function getDaysUntilRenewal(): int
    {
        $today   = new DateTimeImmutable('today');
        $renewal = new DateTimeImmutable($this->renewalDate);
        return (int) $today->diff($renewal)->days * ($renewal >= $today ? 1 : -1);
    }

    public function isNearingRenewal(int $noticeDays = RENEWAL_NOTICE_DAYS): bool
    {
        $days = $this->getDaysUntilRenewal();
        return $days >= 0 && $days <= $noticeDays;
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'active'          => 'Active',
            'expired'         => 'Expired',
            'pending_renewal' => 'Pending Renewal',
            default           => 'Unknown',
        };
    }

    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            'active'          => 'badge-active',
            'expired'         => 'badge-expired',
            'pending_renewal' => 'badge-pending',
            default           => '',
        };
    }
}
