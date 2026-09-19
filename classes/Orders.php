<?php

require_once __DIR__ . '/Model.php';

class Orders extends Model
{
    protected string $table = 'orders';

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

    
    // CALCULATES THE TOTAL 
    // NOTE: BAGUHIN TO IF NAGBAGO RIN PRESYO NILA 
    private function calculateAmount(string $serviceType, float $weight): float
    {
        $prices = [
            'wash-fold'     => 60,
            'dry-cleaning'  => 60,
            'full-service'  => 180,
            'fold-only'     => 30,
        ];

        $base = $prices[$serviceType] ?? 0;

        $chunks = $weight > 0 ? ceil($weight / 8) : 1;

        return $base * $chunks;
    }

    //  PARA WALANG PAREHONGO ORDER ID
    //  BASICALLY: Kunin last then aadd ng +1 para ayun ung ibigay na number
    private function generateOrderCode(): string
    {
        $stmt = $this->db->query("SELECT order_code FROM {$this->table} ORDER BY id DESC LIMIT 1");
        $last = $stmt->fetchColumn();

        $lastNumber = $last ? (int) str_replace('ORD-', '', $last) : 2200;
        $nextNumber = $lastNumber + 1;

        return 'ORD-' . $nextNumber;
    }
}