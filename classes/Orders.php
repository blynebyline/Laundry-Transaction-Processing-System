<?php

require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/Machine.php';

class Orders extends Model
{
    protected string $table = 'orders';

    public function create(array $data): int
    {
        $weight = isset($data['weight']) && $data['weight'] !== '' ? (float)$data['weight'] : 0;
        $serviceType = $data['service-type'] ?? '';

        $payload = [
            'order_code'           => $this->generateOrderCode(),
            'customer_id'          => $data['customer_id'] ?? null,
            'customer_name'        => $data['customerName'],
            'mode'                 => $data['mode'],
            'service_type'         => $serviceType,
            'weight_kg'            => $weight ?: null,
            'item_count'           => $data['items'] ?? null,
            'special_instructions' => $data['instructions'] ?? null,
            'extras'               => $data['extras'] ?? null,
            'delivery_note'        => $data['delivery_note'] ?? null,
            'schedule_date'        => $data['pickup-date'] ?? null,
            'schedule_time'        => $data['pickup-time'] ?? null,
            'address'              => $data['address'] ?? null,
            'amount'               => $this->calculateAmount($serviceType, $weight),
            'order_status'         => 'pending',
            'payment_status'       => 'unpaid',
        ];

        $orderId = $this->insert($payload);

        if (!empty($data['machines']) && is_array($data['machines'])) {
            $machine = new Machine($this->db);
            foreach ($data['machines'] as $machineId) {
                $machine->assignToOrder($orderId, (int)$machineId);
            }
        }

        return $orderId;
    }

    public function updateOrder(int $id, array $data): bool
    {
        $weight = isset($data['weight']) && $data['weight'] !== '' ? (float)$data['weight'] : 0;
        $serviceType = $data['service-type'] ?? '';

        $payload = [
            'customer_id'          => $data['customer_id'] ?? null,
            'customer_name'        => $data['customerName'],
            'mode'                 => $data['mode'],
            'service_type'         => $serviceType,
            'weight_kg'            => $weight ?: null,
            'item_count'           => $data['items'] ?? null,
            'special_instructions' => $data['instructions'] ?? null,
            'extras'               => $data['extras'] ?? null,
            'delivery_note'        => $data['delivery_note'] ?? null,
            'schedule_date'        => $data['pickup-date'] ?? null,
            'schedule_time'        => $data['pickup-time'] ?? null,
            'address'              => $data['address'] ?? null,
            'amount'               => $this->calculateAmount($serviceType, $weight),
            'payment_status'       => $data['payment'] ?? 'unpaid',
        ];

        $updated = $this->updateById($id, $payload);

        $machine = new Machine($this->db);
        $machine->releaseFromOrder($id);

        if (!empty($data['machines']) && is_array($data['machines'])) {
            foreach ($data['machines'] as $machineId) {
                $machine->assignToOrder($id, (int)$machineId);
            }
        }

        return $updated;
    }

    public function updateStatus(int $id, string $status): bool
    {
        $updated = $this->updateById($id, ['order_status' => $status]);

        if ($updated && $status === 'finished') {
            $machine = new Machine($this->db);
            $machine->releaseFromOrder($id);
        }

        return $updated;
    }

    private function calculateAmount(string $serviceType, float $weight): float
    {
        $prices = [
            'wash-fold'    => 60,
            'dry-cleaning' => 60,
            'full-service' => 180,
            'fold-only'    => 30,
        ];

        $base = $prices[$serviceType] ?? 0;
        $chunks = $weight > 0 ? ceil($weight / 8) : 1;

        return $base * $chunks;
    }

    private function generateOrderCode(): string
    {
        $stmt = $this->db->query("SELECT order_code FROM {$this->table} ORDER BY id DESC LIMIT 1");
        $last = $stmt->fetchColumn();

        $lastNumber = $last ? (int) str_replace('ORD-', '', $last) : 2200;
        $nextNumber = $lastNumber + 1;

        return 'ORD-' . $nextNumber;
    }
}