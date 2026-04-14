<?php
declare(strict_types=1);

namespace NosfirVertex\Catalog\Model;

use NosfirVertex\System\Engine\Model;

class UserModel extends Model
{
    public function createUser(string $fullName, string $email, string $password): int
    {
        if ($this->db === null) {
            throw new \RuntimeException('Banco de dados não configurado.');
        }

        $this->db->execute(
            'INSERT INTO users (full_name, email, password_hash, role, status, created_at) VALUES (:full_name, :email, :password_hash, :role, :status, NOW())',
            [
                ':full_name' => trim($fullName),
                ':email' => mb_strtolower(trim($email)),
                ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
                ':role' => 'user',
                ':status' => 'active',
            ]
        );

        $userId = $this->db->lastInsertId();

        $this->db->execute(
            'INSERT INTO profiles (user_id, created_at) VALUES (:user_id, NOW())',
            [':user_id' => $userId]
        );

        return $userId;
    }

    public function existsByEmail(string $email): bool
    {
        if ($this->db === null) {
            return false;
        }

        $row = $this->db->fetch('SELECT user_id FROM users WHERE email = :email LIMIT 1', [
            ':email' => mb_strtolower(trim($email)),
        ]);

        return $row !== null;
    }

    public function getById(int $userId): array|null
    {
        if ($this->db === null) {
            return null;
        }

        return $this->db->fetch(
            'SELECT u.user_id, u.full_name, u.email, p.phone, p.city, p.state, p.country, p.website, p.linkedin, p.github
             FROM users u
             LEFT JOIN profiles p ON p.user_id = u.user_id
             WHERE u.user_id = :user_id
             LIMIT 1',
            [':user_id' => $userId]
        );
    }

    public function updateAccount(int $userId, array $data): void
    {
        if ($this->db === null) {
            return;
        }

        $this->db->execute(
            'UPDATE users SET full_name = :full_name, updated_at = NOW() WHERE user_id = :user_id',
            [
                ':full_name' => trim((string) ($data['full_name'] ?? '')),
                ':user_id' => $userId,
            ]
        );

        $exists = $this->db->fetch('SELECT profile_id FROM profiles WHERE user_id = :user_id LIMIT 1', [':user_id' => $userId]);

        if ($exists === null) {
            $this->db->execute('INSERT INTO profiles (user_id, created_at) VALUES (:user_id, NOW())', [':user_id' => $userId]);
        }

        $this->db->execute(
            'UPDATE profiles SET
                phone = :phone,
                city = :city,
                state = :state,
                country = :country,
                website = :website,
                linkedin = :linkedin,
                github = :github,
                updated_at = NOW()
             WHERE user_id = :user_id',
            [
                ':phone' => trim((string) ($data['phone'] ?? '')),
                ':city' => trim((string) ($data['city'] ?? '')),
                ':state' => trim((string) ($data['state'] ?? '')),
                ':country' => trim((string) ($data['country'] ?? '')),
                ':website' => trim((string) ($data['website'] ?? '')),
                ':linkedin' => trim((string) ($data['linkedin'] ?? '')),
                ':github' => trim((string) ($data['github'] ?? '')),
                ':user_id' => $userId,
            ]
        );

        if (!empty($data['new_password'])) {
            $this->db->execute(
                'UPDATE users SET password_hash = :password_hash, updated_at = NOW() WHERE user_id = :user_id',
                [
                    ':password_hash' => password_hash((string) $data['new_password'], PASSWORD_DEFAULT),
                    ':user_id' => $userId,
                ]
            );
        }
    }

    public function createPasswordReset(string $email): string|null
    {
        if ($this->db === null) {
            return null;
        }

        $user = $this->db->fetch('SELECT user_id FROM users WHERE email = :email LIMIT 1', [
            ':email' => mb_strtolower(trim($email)),
        ]);

        if ($user === null) {
            return null;
        }

        $token = bin2hex(random_bytes(20));

        $this->db->execute(
            'INSERT INTO password_resets (user_id, token, expires_at, created_at) VALUES (:user_id, :token, DATE_ADD(NOW(), INTERVAL 1 HOUR), NOW())',
            [
                ':user_id' => (int) $user['user_id'],
                ':token' => $token,
            ]
        );

        return $token;
    }
}
