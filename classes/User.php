<?php

require_once __DIR__ . '/Model.php';

class User extends Model
{
    protected string $table = 'users';

    public function create(string $email, string $plainPassword): int
    {
        $payload = [
            'email'         => $email,
            'password_hash' => password_hash($plainPassword, PASSWORD_DEFAULT),
        ];  

        return $this->insert($payload);
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch() ?: null;
    }

    public function attemptLogin(string $email, string $plainPassword): ?array
    {
        $user = $this->findByEmail($email);

        if (!$user) {
            return null; // this user/email does not exist
        }

        if (!password_verify($plainPassword, $user['password_hash'])) {
            return null; // wrong password
        }

        unset($user['password_hash']); // security reasonz
        return $user;
    }

    public function emailExists(string $email): bool
    {
        return $this->findByEmail($email) !== null;
    }
}