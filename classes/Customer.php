<?php

require_once __DIR__ . '/Model.php';

class Customer extends Model
{
    protected string $table = 'customers';

    public function create(string $name, ?string $address = null): int
    {
        return $this->insert([
            'name'    => $name,
            'address' => $address,
        ]);
    }

    public function updateCustomer(int $id, string $name, ?string $address = null): bool
    {
        return $this->updateById($id, [
            'name'    => $name,
            'address' => $address,
        ]);
    }
}