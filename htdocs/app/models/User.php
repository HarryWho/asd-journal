<?php
require_once __DIR__ . '/../core/Model.php';

class User extends Model
{
    public function findByEmail(string $email): array|false
    {
        return $this->fetchOne('SELECT * FROM users WHERE email = ?', [$email]);
    }

    public function findByUsername(string $username): array|false
    {
        return $this->fetchOne('SELECT * FROM users WHERE username = ?', [$username]);
    }

    public function findById(int $id): array|false
    {
        return $this->fetchOne('SELECT * FROM users WHERE id = ?', [$id]);
    }

    public function create(string $username, string $email, string $password): int
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $this->query(
            'INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)',
            [$username, $email, $hash, 'subscriber']
        );
        return (int) $this->lastInsertId();
    }

    public function verifyPassword(string $plainPassword, string $hash): bool
    {
        return password_verify($plainPassword, $hash);
    }
}
