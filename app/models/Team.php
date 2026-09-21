<?php

class Team extends Model
{
    public function createAlien(array $data): int|false
    {
        try {
            $this->beginTransaction();

            $sql = "INSERT INTO teams
                    (
                        owner_id,
                        name,
                        slug,
                        description,
                        logo,
                        visibility,
                        invite_code
                    )
                    VALUES
                    (
                        :owner_id,
                        :name,
                        :slug,
                        :description,
                        :logo,
                        :visibility,
                        :invite_code
                    )";

            $this->query($sql, [
                ':owner_id' => $data['owner_id'],
                ':name' => $data['name'],
                ':slug' => $data['slug'],
                ':description' => $data['description'] ?? null,
                ':logo' => $data['logo'] ?? null,
                ':visibility' => $data['visibility'],
                ':invite_code' => $data['invite_code']
            ]);

            $teamId = $this->lastInsertId();

            if ($teamId <= 0) {
                throw new RuntimeException('Alien could not be created.');
            }

            $this->addOwner(
                $teamId,
                (int) $data['owner_id']
            );

            $this->createDefaultPod(
                $teamId,
                (int) $data['owner_id']
            );

            $this->commit();

            return $teamId;

        } catch (Throwable $exception) {

            if ($this->db->inTransaction()) {
                $this->rollBack();
            }

            error_log(
                'Create Alien error: ' .
                $exception->getMessage()
            );

            return false;
        }
    }


    private function addOwner(
        int $teamId,
        int $userId
    ): void {
        $sql = "INSERT INTO team_members
                (
                    team_id,
                    user_id,
                    role,
                    joined_at
                )
                VALUES
                (
                    :team_id,
                    :user_id,
                    'owner',
                    NOW()
                )";

        $this->query($sql, [
            ':team_id' => $teamId,
            ':user_id' => $userId
        ]);
    }


    private function createDefaultPod(
        int $teamId,
        int $createdBy
    ): void {
        $sql = "INSERT INTO channels
                (
                    team_id,
                    created_by,
                    name,
                    slug,
                    description,
                    type
                )
                VALUES
                (
                    :team_id,
                    :created_by,
                    'General',
                    'general',
                    'General discussion for this Alien.',
                    'public'
                )";

        $this->query($sql, [
            ':team_id' => $teamId,
            ':created_by' => $createdBy
        ]);
    }


    public function getUserAliens(int $userId): array
    {
        $sql = "SELECT
                    t.*,
                    tm.role AS member_role,
                    owner.full_name AS owner_name,
                    (
                        SELECT COUNT(*)
                        FROM team_members members
                        WHERE members.team_id = t.id
                    ) AS member_count,
                    (
                        SELECT COUNT(*)
                        FROM channels c
                        WHERE c.team_id = t.id
                    ) AS channel_count
                FROM teams t
                INNER JOIN team_members tm
                    ON tm.team_id = t.id
                LEFT JOIN users owner
                    ON owner.id = t.owner_id
                WHERE tm.user_id = :user_id
                ORDER BY t.created_at DESC";

        return $this->fetchAll($sql, [
            ':user_id' => $userId
        ]);
    }


    public function findByIdForUser(
        int $teamId,
        int $userId
    ) {
        $sql = "SELECT
                    t.*,
                    tm.role AS member_role,
                    owner.full_name AS owner_name
                FROM teams t
                INNER JOIN team_members tm
                    ON tm.team_id = t.id
                   AND tm.user_id = :user_id
                LEFT JOIN users owner
                    ON owner.id = t.owner_id
                WHERE t.id = :team_id
                LIMIT 1";

        return $this->fetch($sql, [
            ':team_id' => $teamId,
            ':user_id' => $userId
        ]);
    }


    public function findByInviteCode(string $inviteCode)
    {
        $sql = "SELECT *
                FROM teams
                WHERE invite_code = :invite_code
                LIMIT 1";

        return $this->fetch($sql, [
            ':invite_code' => $inviteCode
        ]);
    }


  
    public function isMember(
    int $teamId,
    int $userId
): bool {
    $sql = "SELECT id
            FROM team_members
            WHERE team_id = :team_id
              AND user_id = :user_id
            LIMIT 1";

    return (bool) $this->fetch($sql, [
        ':team_id' => $teamId,
        ':user_id' => $userId
    ]);
}


 public function joinAlien(
    int $teamId,
    int $userId
): bool {

    /*
    |--------------------------------------------------------------------------
    | Blocked Users Cannot Rejoin
    |--------------------------------------------------------------------------
    */

    if (
        $this->isBanned(
            $teamId,
            $userId
        )
    ) {
        return false;
    }


    /*
    |--------------------------------------------------------------------------
    | Already A Member
    |--------------------------------------------------------------------------
    */

    if (
        $this->isMember(
            $teamId,
            $userId
        )
    ) {
        return true;
    }


    /*
    |--------------------------------------------------------------------------
    | Join Alien
    |--------------------------------------------------------------------------
    */

    $sql = "INSERT INTO team_members
            (
                team_id,
                user_id,
                role,
                joined_at
            )
            VALUES
            (
                :team_id,
                :user_id,
                'member',
                NOW()
            )";


    $statement =
        $this->query(
            $sql,
            [
                ':team_id' => $teamId,
                ':user_id' => $userId
            ]
        );


    return $statement !== false;
}



/*
|--------------------------------------------------------------------------
| Check If User Is Blocked From Alien
|--------------------------------------------------------------------------
*/

public function isBanned(
    int $teamId,
    int $userId
): bool {
    $sql = "SELECT id
            FROM team_bans
            WHERE team_id = :team_id
              AND user_id = :user_id
            LIMIT 1";

    return (bool) $this->fetch(
        $sql,
        [
            ':team_id' => $teamId,
            ':user_id' => $userId
        ]
    );
}


/*
|--------------------------------------------------------------------------
| Block Crew Member
|--------------------------------------------------------------------------
*/

public function blockCrewMember(
    int $teamId,
    int $userId,
    int $bannedBy,
    ?string $reason = null
): bool {
    try {

        $this->beginTransaction();


        /*
        |--------------------------------------------------------------------------
        | Store Ban
        |--------------------------------------------------------------------------
        */

        $sql = "INSERT INTO team_bans
                (
                    team_id,
                    user_id,
                    banned_by,
                    reason,
                    created_at
                )
                VALUES
                (
                    :team_id,
                    :user_id,
                    :banned_by,
                    :reason,
                    NOW()
                )
                ON DUPLICATE KEY UPDATE
                    banned_by = VALUES(banned_by),
                    reason = VALUES(reason)";

        $this->query(
            $sql,
            [
                ':team_id' => $teamId,
                ':user_id' => $userId,
                ':banned_by' => $bannedBy,
                ':reason' => $reason
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Remove From Alien
        |--------------------------------------------------------------------------
        */

        $this->query(
            "DELETE FROM team_members
             WHERE team_id = :team_id
               AND user_id = :user_id
               AND role != 'owner'",
            [
                ':team_id' => $teamId,
                ':user_id' => $userId
            ]
        );


        $this->commit();

        return true;

    } catch (Throwable $exception) {

        if ($this->db->inTransaction()) {
            $this->rollBack();
        }

        error_log(
            'Block crew member error: ' .
            $exception->getMessage()
        );

        return false;
    }
}


/*
|--------------------------------------------------------------------------
| Unblock User
|--------------------------------------------------------------------------
*/

public function unblockCrewMember(
    int $teamId,
    int $userId
): bool {
    $sql = "DELETE FROM team_bans
            WHERE team_id = :team_id
              AND user_id = :user_id";

    $statement =
        $this->query(
            $sql,
            [
                ':team_id' => $teamId,
                ':user_id' => $userId
            ]
        );

    return $statement !== false;
}

    

    public function slugExists(string $slug): bool
    {
        $sql = "SELECT id
                FROM teams
                WHERE slug = :slug
                LIMIT 1";

        return (bool) $this->fetch($sql, [
            ':slug' => $slug
        ]);
    }


    public function inviteCodeExists(string $inviteCode): bool
    {
        $sql = "SELECT id
                FROM teams
                WHERE invite_code = :invite_code
                LIMIT 1";

        return (bool) $this->fetch($sql, [
            ':invite_code' => $inviteCode
        ]);
    }

    public function getAlienPods(int $teamId): array
{
    $sql = "SELECT
                c.*,
                u.full_name AS creator_name,
                (
                    SELECT COUNT(*)
                    FROM channel_members cm
                    WHERE cm.channel_id = c.id
                ) AS member_count
            FROM channels c
            LEFT JOIN users u
                ON u.id = c.created_by
            WHERE c.team_id = :team_id
            AND c.deleted_at IS NULL
            ORDER BY
                CASE WHEN c.slug = 'general' THEN 0 ELSE 1 END,
                c.created_at ASC";

    return $this->fetchAll($sql, [
        ':team_id' => $teamId
    ]);
}


public function getAlienCrew(int $teamId): array
{
    $sql = "SELECT
                tm.*,
                u.full_name,
                u.username,
                u.email,
                u.avatar,
                u.status,
                u.last_seen
            FROM team_members tm
            INNER JOIN users u
                ON u.id = tm.user_id
            WHERE tm.team_id = :team_id
            ORDER BY
                CASE tm.role
                    WHEN 'owner' THEN 1
                    WHEN 'admin' THEN 2
                    WHEN 'moderator' THEN 3
                    ELSE 4
                END,
                tm.joined_at ASC";

    return $this->fetchAll($sql, [
        ':team_id' => $teamId
    ]);
}



/*
|--------------------------------------------------------------------------
| Find Alien Crew Member
|--------------------------------------------------------------------------
*/

public function getCrewMember(
    int $teamId,
    int $userId
) {
    $sql = "SELECT
                tm.*,
                u.full_name,
                u.username,
                u.email
            FROM team_members tm
            INNER JOIN users u
                ON u.id = tm.user_id
            WHERE tm.team_id = :team_id
              AND tm.user_id = :user_id
            LIMIT 1";

    return $this->fetch(
        $sql,
        [
            ':team_id' => $teamId,
            ':user_id' => $userId
        ]
    );
}


/*
|--------------------------------------------------------------------------
| Remove Crew Member From Alien
|--------------------------------------------------------------------------
*/

public function removeCrewMember(
    int $teamId,
    int $userId
): bool {
    $sql = "DELETE FROM team_members
            WHERE team_id = :team_id
              AND user_id = :user_id
              AND role != 'owner'";

    $statement =
        $this->query(
            $sql,
            [
                ':team_id' => $teamId,
                ':user_id' => $userId
            ]
        );

    return $statement !== false;
}



/*
|--------------------------------------------------------------------------
| Can User Remove Crew Member?
|--------------------------------------------------------------------------
*/

public function canRemoveCrewMember(
    int $teamId,
    int $actingUserId,
    int $targetUserId
): bool {

    $actingMember =
        $this->getCrewMember(
            $teamId,
            $actingUserId
        );

    $targetMember =
        $this->getCrewMember(
            $teamId,
            $targetUserId
        );


    if (
        !$actingMember ||
        !$targetMember
    ) {
        return false;
    }


    /*
    |--------------------------------------------------------------------------
    | Owner Can Never Be Removed
    |--------------------------------------------------------------------------
    */

    if (
        $targetMember['role'] === 'owner'
    ) {
        return false;
    }


    /*
    |--------------------------------------------------------------------------
    | Cannot Remove Yourself
    |--------------------------------------------------------------------------
    */

    if (
        $actingUserId ===
        $targetUserId
    ) {
        return false;
    }


    /*
    |--------------------------------------------------------------------------
    | Owner Can Remove Anyone Except Owner
    |--------------------------------------------------------------------------
    */

    if (
        $actingMember['role'] === 'owner'
    ) {
        return true;
    }


    /*
    |--------------------------------------------------------------------------
    | Admin Can Remove Moderator / Member
    |--------------------------------------------------------------------------
    */

    if (
        $actingMember['role'] === 'admin' &&
        in_array(
            $targetMember['role'],
            [
                'moderator',
                'member'
            ],
            true
        )
    ) {
        return true;
    }


    return false;
}




public function countAlienMessages(int $teamId): int
    {
        $sql = "SELECT COUNT(*) AS total
                FROM messages m
                INNER JOIN channels c
                    ON c.id = m.channel_id
                WHERE c.team_id = :team_id
                AND m.is_deleted = 0";

        $row = $this->fetch($sql, [
            ':team_id' => $teamId
        ]);

        return (int) ($row['total'] ?? 0);
    }

    public function canManageAlien(int $teamId, int $userId): bool
        {
            $sql = "SELECT role
                    FROM team_members
                    WHERE team_id = :team_id
                    AND user_id = :user_id
                    LIMIT 1";

            $membership = $this->fetch($sql, [
                ':team_id' => $teamId,
                ':user_id' => $userId
            ]);

            if (!$membership) {
                return false;
            }

            return in_array(
                $membership['role'],
                ['owner', 'admin', 'moderator'],
                true
            );
        }


        public function canStartMeeting(
    int $teamId,
    int $userId
): bool {
    $sql = "SELECT role
            FROM team_members
            WHERE team_id = :team_id
              AND user_id = :user_id
            LIMIT 1";

    $membership =
        $this->fetch(
            $sql,
            [
                ':team_id' => $teamId,
                ':user_id' => $userId
            ]
        );

    if (!$membership) {
        return false;
    }

    return in_array(
        $membership['role'],
        [
            'owner',
            'admin',
            'moderator'
        ],
        true
    );
}
}