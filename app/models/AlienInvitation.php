<?php

class AlienInvitation extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Create Invitation
    |--------------------------------------------------------------------------
    */

    public function createInvitation(
        int $teamId,
        int $invitedBy,
        string $email,
        string $token,
        string $expiresAt
    ): int|false {
        $sql = "INSERT INTO alien_invitations
                (
                    team_id,
                    invited_by,
                    email,
                    token,
                    status,
                    expires_at,
                    created_at
                )
                VALUES
                (
                    :team_id,
                    :invited_by,
                    :email,
                    :token,
                    'pending',
                    :expires_at,
                    NOW()
                )";

        try {

            $this->query(
                $sql,
                [
                    ':team_id' =>
                        $teamId,

                    ':invited_by' =>
                        $invitedBy,

                    ':email' =>
                        $email,

                    ':token' =>
                        $token,

                    ':expires_at' =>
                        $expiresAt
                ]
            );

            return $this->lastInsertId();

        } catch (Throwable $exception) {

            error_log(
                'Create Alien invitation error: ' .
                $exception->getMessage()
            );

            return false;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Find Pending Invitation By Email
    |--------------------------------------------------------------------------
    */

    public function findPendingByEmail(
        int $teamId,
        string $email
    ) {
        $sql = "SELECT *
                FROM alien_invitations
                WHERE team_id = :team_id
                  AND email = :email
                  AND status = 'pending'
                  AND expires_at > NOW()
                ORDER BY id DESC
                LIMIT 1";

        return $this->fetch(
            $sql,
            [
                ':team_id' =>
                    $teamId,

                ':email' =>
                    $email
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Find Invitation By Token
    |--------------------------------------------------------------------------
    */

    public function findByToken(
        string $token
    ) {
        $sql = "SELECT
                    ai.*,
                    t.name AS alien_name,
                    t.invite_code,
                    u.full_name AS inviter_name
                FROM alien_invitations ai
                INNER JOIN teams t
                    ON t.id = ai.team_id
                INNER JOIN users u
                    ON u.id = ai.invited_by
                WHERE ai.token = :token
                LIMIT 1";

        return $this->fetch(
            $sql,
            [
                ':token' =>
                    $token
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Mark Invitation Accepted
    |--------------------------------------------------------------------------
    */

    public function markAccepted(
        int $invitationId
    ): bool {
        $sql = "UPDATE alien_invitations
                SET
                    status = 'accepted',
                    accepted_at = NOW()
                WHERE id = :id
                  AND status = 'pending'";

        return $this->execute(
            $sql,
            [
                ':id' =>
                    $invitationId
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Cancel Invitation
    |--------------------------------------------------------------------------
    */

    public function cancelInvitation(
        int $invitationId,
        int $teamId
    ): bool {
        $sql = "UPDATE alien_invitations
                SET status = 'cancelled'
                WHERE id = :id
                  AND team_id = :team_id
                  AND status = 'pending'";

        return $this->execute(
            $sql,
            [
                ':id' =>
                    $invitationId,

                ':team_id' =>
                    $teamId
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Expire Old Invitations
    |--------------------------------------------------------------------------
    */

    public function expireOldInvitations(): bool
    {
        $sql = "UPDATE alien_invitations
                SET status = 'expired'
                WHERE status = 'pending'
                  AND expires_at <= NOW()";

        return $this->execute(
            $sql
        );
    }
}