<?php
declare(strict_types=1);

namespace NosfirVertex\System\Library;

class Auth
{
    public function __construct(
        private readonly Session $session,
        private readonly Database|null $db,
        private readonly string $role = 'user'
    ) {
    }

    public function attempt(string $email, string $password): bool
    {
        if ($this->db === null) {
            return false;
        }

        $user = $this->db->fetch(
            'SELECT user_id, full_name, email, password_hash, role, status FROM users WHERE email = :email LIMIT 1',
            [':email' => mb_strtolower(trim($email))]
        );

        if ($user === null || $user['status'] !== 'active' || $user['role'] !== $this->role) {
            return false;
        }

        if (!password_verify($password, $user['password_hash'])) {
            return false;
        }

        $this->session->regenerateId();

        $this->session->set($this->sessionKey(), [
            'user_id' => (int) $user['user_id'],
            'full_name' => $user['full_name'],
            'email' => $user['email'],
            'role' => $user['role'],
        ]);

        return true;
    }

    public function check(): bool
    {
        return is_array($this->session->get($this->sessionKey()));
    }

    public function user(): array|null
    {
        $user = $this->session->get($this->sessionKey());

        return is_array($user) ? $user : null;
    }

    public function id(): int|null
    {
        $user = $this->user();

        if ($user === null) {
            return null;
        }

        return (int) ($user['user_id'] ?? 0);
    }

    public function logout(): void
    {
        $this->session->remove($this->sessionKey());
        $this->session->regenerateId();
    }

    private function sessionKey(): string
    {
        return 'auth_' . $this->role;
    }
}
