<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Company;
use App\Models\CompanyFeature;
use App\Models\ExternalInvoice;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\Setting;
use App\Models\Supplier;
use App\Models\SystemNotification;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AuditObserver
{
    private const HIDDEN_FIELDS = [
        'password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes', 'token_hash', 'api_key',
    ];

    public function created(Model $model): void
    {
        $this->write($model, 'created', [], $model->getAttributes());
    }

    public function updated(Model $model): void
    {
        $changes = array_diff_key($model->getChanges(), array_flip(['updated_at']));

        if ($changes === []) {
            return;
        }

        $old = [];
        foreach (array_keys($changes) as $key) {
            $old[$key] = $model->getOriginal($key);
        }

        $this->write($model, 'updated', $old, $changes);
    }

    public function deleted(Model $model): void
    {
        $this->write($model, 'deleted', $model->getAttributes(), []);
    }

    private function write(Model $model, string $event, array $oldValues, array $newValues): void
    {
        $actor = auth()->user();
        $request = app()->bound('request') ? request() : null;
        $companyId = $model->getAttribute('company_id') ?? $actor?->company_id;

        $oldValues = $this->safeValues($oldValues);
        $newValues = $this->safeValues($newValues);

        ActivityLog::create([
            'company_id' => $companyId,
            'user_id' => $actor?->id,
            'user_name' => $actor?->name ?? 'System',
            'action' => $event,
            'event' => $event,
            'auditable_type' => $model::class,
            'auditable_id' => $model->getKey(),
            'details' => $this->details($model, $event, $oldValues, $newValues),
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
            'ip_address' => $request?->ip(),
            'user_agent' => $request ? mb_substr((string) $request->userAgent(), 0, 1000) : null,
        ]);

        $this->notifyUsers($model, $event, $companyId, $actor);
    }

    private function safeValues(array $values): array
    {
        return array_diff_key($values, array_flip(self::HIDDEN_FIELDS));
    }

    private function details(Model $model, string $event, array $oldValues, array $newValues): string
    {
        $fields = $event === 'updated' ? implode(', ', array_keys($newValues)) : '';
        $subject = class_basename($model).' #'.$model->getKey();

        return $event === 'updated' ? $subject.' ('.$fields.')' : $subject;
    }

    private function notifyUsers(Model $model, string $event, ?int $companyId, ?User $actor): void
    {
        if (!$model instanceof Company
            && !$model instanceof ExternalInvoice
            && !$model instanceof CompanyFeature
            && !$model instanceof Customer
            && !$model instanceof Supplier
            && !$model instanceof Receipt
            && !$model instanceof Payment
            && !$model instanceof Setting
            && !$model instanceof User) {
            return;
        }

        $recipients = User::query()
            ->where(function ($query) use ($companyId) {
                if ($companyId) {
                    $query->where('company_id', $companyId)->orWhere('role', 'super_admin');
                } else {
                    $query->where('role', 'super_admin');
                }
            })
            ->get(['id', 'company_id']);

        $eventLabel = __('messages.'.$event);
        $subject = class_basename($model).' #'.$model->getKey();
        $actorName = $actor?->name ?? 'System';

        foreach ($recipients as $recipient) {
            SystemNotification::create([
                'user_id' => $recipient->id,
                'company_id' => $companyId,
                'kind' => 'audit_'.$event,
                'title' => __('messages.activity_notification_title', ['event' => $eventLabel]),
                'message' => __('messages.activity_notification_message', [
                    'actor' => $actorName,
                    'event' => $eventLabel,
                    'subject' => $subject,
                ]),
                'link' => '/dashboard',
            ]);
        }
    }
}
