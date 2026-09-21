<?php

class Channel extends Model
{
    public function createPod(array $data): int|false
    {
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
                    :name,
                    :slug,
                    :description,
                    :type
                )";

        try {
            $this->query($sql, [
                ':team_id' => $data['team_id'],
                ':created_by' => $data['created_by'],
                ':name' => $data['name'],
                ':slug' => $data['slug'],
                ':description' => $data['description'] ?? null,
                ':type' => $data['type']
            ]);

            return $this->lastInsertId();

        } catch (Throwable $exception) {
            error_log(
                'Create Pod error: ' .
                $exception->getMessage()
            );

            return false;
        }
    }


    public function slugExistsForAlien(
        int $teamId,
        string $slug
    ): bool {
        $sql = "SELECT id
                FROM channels
                WHERE team_id = :team_id
                  AND slug = :slug
                LIMIT 1";

        return (bool) $this->fetch($sql, [
            ':team_id' => $teamId,
            ':slug' => $slug
        ]);
    }


  public function findPodForUser(
    int $podId,
    int $userId
        ) {
            $sql = "SELECT
                        c.*,
                        t.name AS alien_name,
                        t.logo AS alien_logo,
                        tm.role AS member_role
                    FROM channels c
                    INNER JOIN teams t
                        ON t.id = c.team_id
                    INNER JOIN team_members tm
                        ON tm.team_id = t.id
                    AND tm.user_id = :user_id
                    WHERE c.id = :pod_id
                    AND c.deleted_at IS NULL
                    LIMIT 1";

            return $this->fetch(
                $sql,
                [
                    ':pod_id' => $podId,
                    ':user_id' => $userId
                ]
            );
        }


    public function markMessagesCleared(
            int $channelId
            ): bool {
                $sql = "UPDATE channels
                        SET
                            messages_cleared_at = NOW(),
                            updated_at = NOW()
                        WHERE id = :channel_id";

                return $this->execute($sql, [
                    ':channel_id' => $channelId
                ]);
            }


            public function startMeeting(
        int $channelId,
        int $userId
    ): bool {
        $sql = "UPDATE channels
                SET
                    meeting_active = 1,
                    meeting_started_by = :user_id,
                    meeting_started_at = NOW()
                WHERE id = :channel_id";

        return $this->execute($sql, [
            ':channel_id' => $channelId,
            ':user_id' => $userId
        ]);
    }


    public function stopMeeting(
        int $channelId
    ): bool {
        $sql = "UPDATE channels
                SET
                    meeting_active = 0,
                    meeting_started_by = NULL,
                    meeting_started_at = NULL
                WHERE id = :channel_id";

        return $this->execute($sql, [
            ':channel_id' => $channelId
        ]);
    }

    public function getMeetingStatus(
            int $channelId
        ): array|false {
            $sql = "SELECT
                        meeting_active,
                        meeting_started_by,
                        meeting_started_at
                    FROM channels
                    WHERE id = :channel_id
                    LIMIT 1";

            return $this->fetch($sql, [
                ':channel_id' => $channelId
            ]);
        }


        public function deletePod(
            int $podId
        ): bool {
            try {

                $this->beginTransaction();

                /*
                |--------------------------------------------------------------------------
                | Delete WebRTC Signals
                |--------------------------------------------------------------------------
                */

                $this->query(
                    "DELETE FROM video_signals
                    WHERE channel_id = :channel_id",
                    [
                        ':channel_id' => $podId
                    ]
                );


                /*
                |--------------------------------------------------------------------------
                | Delete Channel Members
                |--------------------------------------------------------------------------
                */

                $this->query(
                    "DELETE FROM channel_members
                    WHERE channel_id = :channel_id",
                    [
                        ':channel_id' => $podId
                    ]
                );


                /*
                |--------------------------------------------------------------------------
                | Delete Messages
                |--------------------------------------------------------------------------
                */

                $this->query(
                    "DELETE FROM messages
                    WHERE channel_id = :channel_id",
                    [
                        ':channel_id' => $podId
                    ]
                );


                /*
                |--------------------------------------------------------------------------
                | Finally Delete Pod
                |--------------------------------------------------------------------------
                */

                $statement =
                    $this->query(
                        "DELETE FROM channels
                        WHERE id = :channel_id",
                        [
                            ':channel_id' => $podId
                        ]
                    );

                $this->commit();

                return $statement !== false;

            } catch (Throwable $exception) {

                if ($this->db->inTransaction()) {
                    $this->rollBack();
                }

                error_log(
                    'Delete Pod error: ' .
                    $exception->getMessage()
                );

                return false;
            }
        }

        public function softDeletePod(
                int $podId
            ): bool {
                $sql = "UPDATE channels
                        SET
                            deleted_at = NOW(),
                            meeting_active = 0,
                            meeting_started_by = NULL,
                            meeting_started_at = NULL
                        WHERE id = :pod_id
                        AND deleted_at IS NULL";

                return $this->execute(
                    $sql,
                    [
                        ':pod_id' => $podId
                    ]
                );
            }

            public function countActivePods(
                int $teamId
            ): int {
                $sql = "SELECT COUNT(*) AS total
                        FROM channels
                        WHERE team_id = :team_id
                        AND deleted_at IS NULL";

                $row =
                    $this->fetch(
                        $sql,
                        [
                            ':team_id' => $teamId
                        ]
                    );

                return (int) (
                    $row['total']
                    ?? 0
                );
            }

}