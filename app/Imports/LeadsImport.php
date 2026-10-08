<?php

namespace App\Imports;

use App\Models\Lead;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;

class LeadsImport implements OnEachRow, WithHeadingRow, SkipsEmptyRows
{
    /** @var array<string, array<int, string>> */
    protected const FIELD_ALIASES = [
        'name' => ['name', 'full_name', 'fullname', 'contact_name', 'lead_name'],
        'email' => ['email', 'email_address', 'e_mail'],
        'phone' => ['phone', 'phone_number', 'mobile', 'mobile_number', 'contact_number'],
        'company' => ['company', 'company_name', 'business_name', 'website'],
        'source' => ['source', 'platform', 'form_name', 'campaign_name'],
        'status' => ['status', 'lead_status'],
    ];

    protected const ALLOWED_STATUSES = ['new', 'contacted', 'interested', 'qualified', 'converted', 'lost'];

    protected int $successCount = 0;

    protected int $duplicateCount = 0;

    /** @var array<int, true> */
    protected array $skippedRows = [];

    /** @var array<int, string> */
    protected array $errors = [];

    /** @var array<int, true> */
    protected array $seenEmails = [];

    public function onRow(Row $row): void
    {
        $raw = $row->toArray();
        $rowNumber = $row->getIndex();

        $mapped = $this->mapRow($raw);

        $validator = Validator::make($mapped, [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'source' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            $this->skip($rowNumber, $validator->errors()->all());

            return;
        }

        $emailKey = strtolower($mapped['email']);
        if (isset($this->seenEmails[$emailKey]) || Lead::withTrashed()->where('email', $mapped['email'])->exists()) {
            $this->duplicateCount++;
            $this->seenEmails[$emailKey] = true;

            return;
        }

        $this->seenEmails[$emailKey] = true;

        $extraFields = $this->extractExtraFields($raw, $mapped['consumed_keys']);

        Lead::create([
            'name' => $mapped['name'],
            'email' => $mapped['email'],
            'phone' => $mapped['phone'],
            'company' => $mapped['company'],
            'source' => $mapped['source'] ?: 'Meta Ads',
            'status' => $mapped['status'],
            'extra_fields' => $extraFields ?: null,
            'created_by' => Auth::id(),
        ]);

        $this->successCount++;
    }

    /**
     * @param  array<string, mixed>  $raw
     * @return array{name: ?string, email: ?string, phone: ?string, company: ?string, source: ?string, status: string, consumed_keys: array<int, string>}
     */
    protected function mapRow(array $raw): array
    {
        $consumed = [];

        $name = $this->pick($raw, self::FIELD_ALIASES['name'], $consumed);
        $email = $this->pick($raw, self::FIELD_ALIASES['email'], $consumed);
        $phone = $this->cleanPhone($this->pick($raw, self::FIELD_ALIASES['phone'], $consumed));
        $company = $this->pick($raw, self::FIELD_ALIASES['company'], $consumed);
        $source = $this->pick($raw, self::FIELD_ALIASES['source'], $consumed);
        $statusRaw = $this->pick($raw, self::FIELD_ALIASES['status'], $consumed);

        return [
            'name' => $name !== null ? trim((string) $name) : null,
            'email' => $email !== null ? trim((string) $email) : null,
            'phone' => $phone,
            'company' => $company !== null ? trim((string) $company) : null,
            'source' => $source !== null ? $this->normalizeSource((string) $source) : null,
            'status' => $this->mapStatus($statusRaw),
            'consumed_keys' => $consumed,
        ];
    }

    /**
     * @param  array<string, mixed>  $raw
     * @param  array<int, string>  $aliases
     * @param  array<int, string>  $consumed
     */
    protected function pick(array $raw, array $aliases, array &$consumed): mixed
    {
        foreach ($aliases as $alias) {
            foreach ($raw as $key => $value) {
                if ($key === null || $key === '') {
                    continue;
                }

                $normalized = $this->normalizeKey((string) $key);
                if ($normalized !== $alias) {
                    continue;
                }

                if ($value === null || $value === '') {
                    continue;
                }

                $consumed[] = (string) $key;

                return $value;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $raw
     * @param  array<int, string>  $consumedKeys
     * @return array<string, string>
     */
    protected function extractExtraFields(array $raw, array $consumedKeys): array
    {
        $extra = [];
        $consumedLookup = array_fill_keys($consumedKeys, true);

        foreach ($raw as $key => $value) {
            if ($key === null || $key === '' || ! is_string($key) && ! is_int($key)) {
                continue;
            }

            $key = (string) $key;
            if ($key === '' || isset($consumedLookup[$key])) {
                continue;
            }

            // Skip empty unnamed trailing columns from Meta exports
            if (preg_match('/^\d+$/', $key) && ($value === null || $value === '')) {
                continue;
            }

            if ($value === null || $value === '') {
                continue;
            }

            $label = $this->humanizeHeading($key);
            $rawValue = is_scalar($value) ? (string) $value : json_encode($value);
            $extra[$label] = Lead::humanizeExtraValue($rawValue);
        }

        return $extra;
    }

    protected function cleanPhone(mixed $phone): ?string
    {
        if ($phone === null || $phone === '') {
            return null;
        }

        $phone = trim((string) $phone);
        $phone = preg_replace('/^p:/i', '', $phone) ?? $phone;

        return trim($phone) !== '' ? trim($phone) : null;
    }

    protected function normalizeSource(string $source): string
    {
        $source = trim($source);
        $map = [
            'ig' => 'Instagram',
            'fb' => 'Facebook',
            'msg' => 'Messenger',
            'an' => 'Audience Network',
        ];

        $lower = strtolower($source);

        return $map[$lower] ?? $source;
    }

    protected function mapStatus(mixed $status): string
    {
        if ($status === null || $status === '') {
            return 'new';
        }

        $status = strtolower(trim((string) $status));

        $map = [
            'created' => 'new',
            'new' => 'new',
            'contacted' => 'contacted',
            'interested' => 'interested',
            'qualified' => 'qualified',
            'converted' => 'converted',
            'lost' => 'lost',
        ];

        $mapped = $map[$status] ?? 'new';

        return in_array($mapped, self::ALLOWED_STATUSES, true) ? $mapped : 'new';
    }

    protected function normalizeKey(string $key): string
    {
        $key = strtolower(trim($key));
        $key = preg_replace('/[^a-z0-9]+/', '_', $key) ?? $key;

        return trim($key, '_');
    }

    protected function humanizeHeading(string $key): string
    {
        $key = str_replace(['_', '-'], ' ', $key);
        $key = preg_replace('/\s+/', ' ', $key) ?? $key;

        return ucwords(trim($key));
    }

    /**
     * @param  array<int, string>  $messages
     */
    protected function skip(int $rowNumber, array $messages): void
    {
        $this->skippedRows[$rowNumber] = true;
        foreach ($messages as $message) {
            $this->errors[] = "Row {$rowNumber}: {$message}";
        }
    }

    public function getSuccessCount(): int
    {
        return $this->successCount;
    }

    public function getDuplicateCount(): int
    {
        return $this->duplicateCount;
    }

    public function getSkippedCount(): int
    {
        return count($this->skippedRows);
    }

    /**
     * @return array<int, string>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
