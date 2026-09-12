<?php

require_once __DIR__ . '/Model.php';

class Orders extends Model
{
    protected string $table = 'orders';

    /**
     * Create a new order from $_POST-style data (order.php).
     * Generates the next ORD-#### code automatically.
     */
    public function create(array $data): int
    {
        $weight = isset($data['weight']) && $data['weight'] !== '' ? (float)$data['weight'] : 0;
        $serviceType = $data['service-type'] ?? '';

        $payload = [
            'order_code'           => $this->generateOrderCode(),
            'customer_name'        => $data['customerName'],
            'mode'                 => $data['mode'],               
            'service_type'         => $serviceType,
            'weight_kg'            => $weight ?: null,
            'item_count'           => $data['items'] ?? null,
            'special_instructions' => $data['instructions'] ?? null,
            'schedule_date'        => $data['pickup-date'] ?? null,
            'schedule_time'        => $data['pickup-time'] ?? null,
            'address'              => $data['address'] ?? null,
            'amount'               => $this->calculateAmount($serviceType, $weight),
            'order_status'         => 'pending',
            'payment_status'       => 'unpaid',
        ];

        return $this->insert($payload);
    }

    public function updateOrder(int $id, array $data): bool
    {
        $weight = isset($data['weight']) && $data['weight'] !== '' ? (float)$data['weight'] : 0;
        $serviceType = $data['service-type'] ?? '';

        $payload = [
            'customer_name'        => $data['customerName'],
            'mode'                 => $data['mode'],
            'service_type'         => $serviceType,
            'weight_kg'            => $weight ?: null,
            'item_count'           => $data['items'] ?? null,
            'special_instructions' => $data['instructions'] ?? null,
            'schedule_date'        => $data['pickup-date'] ?? null,
            'schedule_time'        => $data['pickup-time'] ?? null,
            'address'              => $data['address'] ?? null,
            'amount'               => $this->calculateAmount($serviceType, $weight),
            'payment_status'       => $data['payment'] ?? 'unpaid',
        ];

        return $this->updateById($id, $payload);
    }

    public function updateStatus(int $id, string $status): bool
    {
        return $this->updateById($id, ['order_status' => $status]);
    }

    public function findByOrderCode(string $code): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE order_code = :code LIMIT 1");
        $stmt->execute(['code' => $code]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Calculate the amount based on service type and weight.
     * Pricing per 8kg chunk, rounded up.
     */
    private function calculateAmount(string $serviceType, float $weight): float
    {
        $prices = [
            'wash-fold'     => 60,
            'dry-cleaning'  => 60,
            'full-service'  => 180,
            'fold-only'     => 30,
        ];

        $base = $prices[$serviceType] ?? 0;

        // Ensure at least one chunk
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