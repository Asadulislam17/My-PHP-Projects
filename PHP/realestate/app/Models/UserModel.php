<?php

namespace App\Models;

/**
 * UserModel – Handles all user data operations.
 */
class UserModel extends BaseModel
{
    protected string $table      = 'users';
    protected bool   $softDelete = true;
    protected bool   $timestamps = true;

    protected array $fillable = [
        'role_id', 'name', 'email', 'phone', 'password',
        'avatar', 'bio', 'address', 'otp_code', 'otp_expires_at',
        'email_verified_at', 'is_active', 'last_login_at',
        'last_login_ip', 'remember_token', 'lang',
    ];

    // ------------------------------------------------------------------ //
    //  Lookup helpers                                                      //
    // ------------------------------------------------------------------ //

    public function findByEmail(string $email): ?array
    {
        return $this->db->selectOne(
            "SELECT u.*, r.name AS role
               FROM users u
               JOIN roles r ON r.id = u.role_id
              WHERE u.email = ? AND u.deleted_at IS NULL",
            [$email]
        );
    }

    public function findWithRole(int $id): ?array
    {
        return $this->db->selectOne(
            "SELECT u.*, r.name AS role
               FROM users u
               JOIN roles r ON r.id = u.role_id
              WHERE u.id = ? AND u.deleted_at IS NULL",
            [$id]
        );
    }

    // ------------------------------------------------------------------ //
    //  Registration                                                        //
    // ------------------------------------------------------------------ //

    public function register(array $data): int|string
    {
        $roleId = $this->db->selectOne(
            "SELECT id FROM roles WHERE name = ?",
            [$data['role'] ?? 'buyer']
        )['id'] ?? 3;

        return $this->create([
            'role_id'  => $roleId,
            'name'     => $data['name'],
            'email'    => $data['email'],
            'phone'    => $data['phone'] ?? null,
            'password' => password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]),
        ]);
    }

    // ------------------------------------------------------------------ //
    //  OTP                                                                 //
    // ------------------------------------------------------------------ //

    public function setOtp(int $userId): string
    {
        $otp = (string) random_int(100000, 999999);
        $this->db->update(
            'users',
            [
                'otp_code'       => $otp,
                'otp_expires_at' => date('Y-m-d H:i:s', strtotime('+15 minutes')),
            ],
            'id = ?',
            [$userId]
        );
        return $otp;
    }

    public function verifyOtp(int $userId, string $otp): bool
    {
        $user = $this->find($userId);
        if (!$user) return false;
        if ($user['otp_code'] !== $otp) return false;
        if (strtotime($user['otp_expires_at']) < time()) return false;

        // Mark email verified and clear OTP
        $this->db->update(
            'users',
            [
                'email_verified_at' => date('Y-m-d H:i:s'),
                'otp_code'          => null,
                'otp_expires_at'    => null,
            ],
            'id = ?',
            [$userId]
        );
        return true;
    }

    public function resendOtp(string $email): ?array
    {
        $user = $this->findByEmail($email);
        if (!$user) return null;
        if ($user['email_verified_at']) return null; // already verified

        $otp = $this->setOtp((int) $user['id']);
        return ['user' => $user, 'otp' => $otp];
    }

    // ------------------------------------------------------------------ //
    //  Password reset                                                      //
    // ------------------------------------------------------------------ //

    public function setPasswordResetOtp(string $email): ?array
    {
        $user = $this->findByEmail($email);
        if (!$user) return null;

        $otp = $this->setOtp((int) $user['id']);
        return ['user' => $user, 'otp' => $otp];
    }

    public function resetPassword(int $userId, string $newPassword): bool
    {
        $affected = $this->db->update(
            'users',
            ['password' => password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12])],
            'id = ?',
            [$userId]
        );
        return $affected > 0;
    }

    // ------------------------------------------------------------------ //
    //  Login tracking                                                      //
    // ------------------------------------------------------------------ //

    public function recordLogin(int $userId, string $ip): void
    {
        $this->db->update(
            'users',
            [
                'last_login_at' => date('Y-m-d H:i:s'),
                'last_login_ip' => $ip,
            ],
            'id = ?',
            [$userId]
        );
    }

    // ------------------------------------------------------------------ //
    //  Remember me                                                         //
    // ------------------------------------------------------------------ //

    public function setRememberToken(int $userId): string
    {
        $token = bin2hex(random_bytes(32));
        $this->db->update('users', ['remember_token' => $token], 'id = ?', [$userId]);
        return $token;
    }

    public function findByRememberToken(string $token): ?array
    {
        return $this->db->selectOne(
            "SELECT u.*, r.name AS role
               FROM users u JOIN roles r ON r.id = u.role_id
              WHERE u.remember_token = ? AND u.deleted_at IS NULL AND u.is_active = 1",
            [$token]
        );
    }

    public function clearRememberToken(int $userId): void
    {
        $this->db->update('users', ['remember_token' => null], 'id = ?', [$userId]);
    }
}
