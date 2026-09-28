<?php

require_once __DIR__ . '/Model.php';

class Machine extends Model
{
    protected string $table = 'machines';

    public function getVacant(): array
    {
        $stmt = $this->db->query(
            "SELECT * FROM {$this->table} WHERE status = 'vacant' ORDER BY type, name"
        );
        return $stmt->fetchAll();
    }

    public function getGroupedByType(): array
    {
        $all = $this->findAll('type ASC, name ASC');
        $grouped = ['washer' => [], 'dryer' => []];

        foreach ($all as $machine) {
            $grouped[$machine['type']][] = $machine;
        }

        return $grouped;
    }

    public function assignToOrder(int $orderId, int $machineId): bool
    {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare(
                "INSERT IGNORE INTO order_machines (order_id, machine_id, released_at)
                 VALUES (:order_id, :machine_id, NULL)"
            );
            $stmt->execute(['order_id' => $orderId, 'machine_id' => $machineId]);

            $stmt = $this->db->prepare(
                "UPDATE order_machines
                 SET released_at = NULL, assigned_at = NOW()
                 WHERE order_id = :order_id AND machine_id = :machine_id"
            );
            $stmt->execute(['order_id' => $orderId, 'machine_id' => $machineId]);


            $stmt = $this->db->prepare(
                "UPDATE {$this->table} SET status = 'in-use' WHERE id = :id"
            );
            $stmt->execute(['id' => $machineId]);

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function releaseFromOrder(int $orderId): bool
    {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare(
                "SELECT machine_id FROM order_machines
                 WHERE order_id = :order_id AND released_at IS NULL"
            );
            $stmt->execute(['order_id' => $orderId]);
            $machineIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($machineIds)) {

                $stmt = $this->db->prepare(
                    "UPDATE order_machines
                     SET released_at = NOW()
                     WHERE order_id = :order_id AND released_at IS NULL"
                );
                $stmt->execute(['order_id' => $orderId]);

                $in = implode(',', array_fill(0, count($machineIds), '?'));
                $stmt = $this->db->prepare(
                    "UPDATE {$this->table} SET status = 'vacant' WHERE id IN ($in)"
                );
                $stmt->execute($machineIds);
            }

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            return false;
        }
    }
    public function getForOrder(int $orderId): array
    {
        $stmt = $this->db->prepare(
            "SELECT m.* FROM {$this->table} m
             INNER JOIN order_machines om ON om.machine_id = m.id
             WHERE om.order_id = :order_id AND om.released_at IS NULL
             ORDER BY m.type, m.name"
        );
        $stmt->execute(['order_id' => $orderId]);
        return $stmt->fetchAll();
    }

    public function getActiveOrder(int $machineId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT o.* FROM orders o
         INNER JOIN order_machines om ON om.order_id = o.id
         WHERE om.machine_id = :machine_id AND om.released_at IS NULL
         ORDER BY om.assigned_at DESC
         LIMIT 1"
        );
        $stmt->execute(['machine_id' => $machineId]);
        return $stmt->fetch() ?: null;
    }

    public function setStatus(int $id, string $status): bool
    {
        $allowed = ['vacant', 'in-use', 'unavailable'];
        if (!in_array($status, $allowed)) {
            return false;
        }
        return $this->updateById($id, ['status' => $status]);
    }
}