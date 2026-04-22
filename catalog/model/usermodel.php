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
                ':website' => $this->sanitizeOptionalUrl((string) ($data['website'] ?? '')),
                ':linkedin' => $this->sanitizeOptionalUrl((string) ($data['linkedin'] ?? '')),
                ':github' => $this->sanitizeOptionalUrl((string) ($data['github'] ?? '')),
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

        $normalizedEmail = mb_strtolower(trim($email));
        $user = $this->db->fetch(
            'SELECT user_id, email FROM users WHERE email = :email AND status = :status LIMIT 1',
            [
                ':email' => $normalizedEmail,
                ':status' => 'active',
            ]
        );

        if ($user === null) {
            return null;
        }

        $tokenPlain = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $tokenPlain);
        $userId = (int) ($user['user_id'] ?? 0);

        $this->db->transaction(function () use ($userId, $tokenHash): void {
            $this->db->execute(
                'UPDATE password_resets SET used_at = NOW() WHERE user_id = :user_id AND used_at IS NULL',
                [':user_id' => $userId]
            );

            $this->db->execute(
                'INSERT INTO password_resets (user_id, token, expires_at, created_at) VALUES (:user_id, :token, DATE_ADD(NOW(), INTERVAL 1 HOUR), NOW())',
                [
                    ':user_id' => $userId,
                    ':token' => $tokenHash,
                ]
            );
        });

        return $tokenPlain;
    }

    public function isPasswordResetTokenValid(string $token): bool
    {
        return $this->findActivePasswordResetByToken($token) !== null;
    }

    public function resetPasswordByToken(string $token, string $newPassword): bool
    {
        if ($this->db === null || !$this->isPasswordResetTokenFormat($token)) {
            return false;
        }

        return $this->db->transaction(function () use ($token, $newPassword): bool {
            $reset = $this->findActivePasswordResetByToken($token, true);
            if ($reset === null) {
                return false;
            }

            $userId = (int) ($reset['user_id'] ?? 0);
            $resetId = (int) ($reset['password_reset_id'] ?? 0);

            if ($userId <= 0 || $resetId <= 0) {
                return false;
            }

            $this->db->execute(
                'UPDATE users SET password_hash = :password_hash, updated_at = NOW() WHERE user_id = :user_id',
                [
                    ':password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
                    ':user_id' => $userId,
                ]
            );

            $this->db->execute(
                'UPDATE password_resets SET used_at = NOW() WHERE user_id = :user_id AND used_at IS NULL',
                [':user_id' => $userId]
            );

            return true;
        });
    }

    private function findActivePasswordResetByToken(string $token, bool $forUpdate = false): array|null
    {
        if ($this->db === null || !$this->isPasswordResetTokenFormat($token)) {
            return null;
        }

        $normalized = strtolower(trim($token));
        $tokenHash = hash('sha256', $normalized);

        $sql = 'SELECT password_reset_id, user_id
                FROM password_resets
                WHERE (token = :token_hash OR token = :token_legacy)
                  AND used_at IS NULL
                  AND expires_at >= NOW()
                ORDER BY password_reset_id DESC
                LIMIT 1';

        if ($forUpdate) {
            $sql .= ' FOR UPDATE';
        }

        return $this->db->fetch($sql, [
            ':token_hash' => $tokenHash,
            ':token_legacy' => $normalized,
        ]);
    }

    private function isPasswordResetTokenFormat(string $token): bool
    {
        return preg_match('/^[a-f0-9]{40,128}$/i', trim($token)) === 1;
    }

    private function sanitizeOptionalUrl(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        if (preg_match('/[\x00-\x1F\x7F]/', $value) === 1) {
            return '';
        }

        if (filter_var($value, FILTER_VALIDATE_URL) === false) {
            return '';
        }

        $scheme = strtolower((string) (parse_url($value, PHP_URL_SCHEME) ?? ''));
        if (!in_array($scheme, ['http', 'https'], true)) {
            return '';
        }

        return mb_substr($value, 0, 255);
    }
}
