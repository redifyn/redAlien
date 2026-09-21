<?php

class PasswordReset extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Create Password Reset
    |--------------------------------------------------------------------------
    */

    public function createReset(
        int $userId,
        string $tokenHash,
        string $expiresAt
    ): int|false {

        $sql = "INSERT INTO password_resets
                (
                    user_id,
                    token_hash,
                    expires_at,
                    created_at
                )
                VALUES
                (
                    :user_id,
                    :token_hash,
                    :expires_at,
                    NOW()
                )";

        try {

            $this->query(
                $sql,
                [
                    ':user_id' =>
                        $userId,

                    ':token_hash' =>
                        $tokenHash,

                    ':expires_at' =>
                        $expiresAt
                ]
            );

            return $this->lastInsertId();

        } catch (Throwable $exception) {

            error_log(
                'Create password reset error: ' .
                $exception->getMessage()
            );

            return false;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Find Valid Password Reset
    |--------------------------------------------------------------------------
    */

    public function findValidByTokenHash(
        string $tokenHash
    ) {
        $sql = "SELECT
                    pr.*,
                    u.email,
                    u.full_name
                FROM password_resets pr
                INNER JOIN users u
                    ON u.id = pr.user_id
                WHERE pr.token_hash = :token_hash
                  AND pr.used_at IS NULL
                  AND pr.expires_at > NOW()
                ORDER BY pr.id DESC
                LIMIT 1";

        return $this->fetch(
            $sql,
            [
                ':token_hash' =>
                    $tokenHash
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Mark Reset As Used
    |--------------------------------------------------------------------------
    */

    public function markUsed(
        int $resetId
    ): bool {

        $sql = "UPDATE password_resets
                SET used_at = NOW()
                WHERE id = :id
                  AND used_at IS NULL";

        return $this->execute(
            $sql,
            [
                ':id' =>
                    $resetId
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Invalidate Previous Active Resets
    |--------------------------------------------------------------------------
    */

    public function invalidateForUser(
        int $userId
    ): bool {

        $sql = "UPDATE password_resets
                SET used_at = NOW()
                WHERE user_id = :user_id
                  AND used_at IS NULL";

        return $this->execute(
            $sql,
            [
                ':user_id' =>
                    $userId
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Delete Old Reset Records
    |--------------------------------------------------------------------------
    */

    public function deleteExpired(): bool
    {
        $sql = "DELETE FROM password_resets
                WHERE expires_at < DATE_SUB(
                    NOW(),
                    INTERVAL 7 DAY
                )";

        return $this->execute(
            $sql
        );
    }
}