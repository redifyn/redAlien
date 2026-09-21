<?php

class Message extends Model
{
    public function createTransmission(array $data): int|false
    {
        $sql = "INSERT INTO messages
                (
                    channel_id,
                    sender_id,
                    parent_message_id,
                    message,
                    attachment_path,
                    attachment_name,
                    attachment_size,
                    attachment_mime,
                    message_type
                )
                VALUES
                (
                    :channel_id,
                    :sender_id,
                    :parent_message_id,
                    :message,
                    :attachment_path,
                    :attachment_name,
                    :attachment_size,
                    :attachment_mime,
                    :message_type
                )";

        try {
            $this->query($sql, [
                ':channel_id' => $data['channel_id'],
                ':sender_id' => $data['sender_id'],
                ':parent_message_id' => $data['parent_message_id'] ?? null,
                ':message' => $data['message'] ?? null,
                ':attachment_path' => $data['attachment_path'] ?? null,
                ':attachment_name' => $data['attachment_name'] ?? null,
                ':attachment_size' => $data['attachment_size'] ?? null,
                ':attachment_mime' => $data['attachment_mime'] ?? null,
                ':message_type' => $data['message_type'] ?? 'text'
            ]);

            return $this->lastInsertId();

        } catch (Throwable $exception) {
            error_log(
                'Create transmission error: ' .
                $exception->getMessage()
            );

            return false;
        }
    }


    public function getPodTransmissions(int $channelId): array
    {
        $sql = "SELECT
                    m.*,
                    u.full_name AS sender_name,
                    u.username AS sender_username,
                    u.avatar AS sender_avatar,
                    u.status AS sender_status,
                    parent.message AS parent_message,
                    parent_sender.full_name AS parent_sender_name
                FROM messages m
                INNER JOIN users u
                    ON u.id = m.sender_id
                LEFT JOIN messages parent
                    ON parent.id = m.parent_message_id
                LEFT JOIN users parent_sender
                    ON parent_sender.id = parent.sender_id
                WHERE m.channel_id = :channel_id
                  AND m.is_deleted = 0
                ORDER BY m.created_at ASC";

        return $this->fetchAll($sql, [
            ':channel_id' => $channelId
        ]);
    }


    public function findTransmissionForUser(
        int $messageId,
        int $userId
    ) {
        $sql = "SELECT
                    m.*,
                    c.team_id,
                    tm.role AS member_role
                FROM messages m
                INNER JOIN channels c
                    ON c.id = m.channel_id
                INNER JOIN team_members tm
                    ON tm.team_id = c.team_id
                   AND tm.user_id = :user_id
                WHERE m.id = :message_id
                  AND m.is_deleted = 0
                LIMIT 1";

        return $this->fetch($sql, [
            ':message_id' => $messageId,
            ':user_id' => $userId
        ]);
    }


    public function updateTransmission(
    int $messageId,
    int $senderId,
    string $message
    ): bool {
        $sql = "UPDATE messages
                SET
                    message = :message,
                    is_edited = 1,
                    edited_at = NOW(),
                    updated_at = NOW()
                WHERE id = :message_id
                AND sender_id = :sender_id
                AND is_deleted = 0";

        return $this->execute($sql, [
            ':message' => $message,
            ':message_id' => $messageId,
            ':sender_id' => $senderId
        ]);
    }


    public function deleteTransmission(
    int $messageId,
    int $senderId
    ): bool {
        $sql = "UPDATE messages
                SET
                    is_deleted = 1,
                    deleted_at = NOW(),
                    updated_at = NOW()
                WHERE id = :message_id
                AND sender_id = :sender_id
                AND is_deleted = 0";

        return $this->execute($sql, [
            ':message_id' => $messageId,
            ':sender_id' => $senderId
        ]);
    }


    public function countPodTransmissions(int $channelId): int
    {
        $sql = "SELECT COUNT(*) AS total
                FROM messages
                WHERE channel_id = :channel_id
                  AND is_deleted = 0";

        $result = $this->fetch($sql, [
            ':channel_id' => $channelId
        ]);

        return (int) ($result['total'] ?? 0);
    }

    public function findById(int $messageId): array|false
    {
        $sql = "SELECT
                    m.*,
                    u.full_name AS sender_name,
                    u.username AS sender_username,
                    u.avatar AS sender_avatar
                FROM messages m
                INNER JOIN users u
                    ON u.id = m.sender_id
                WHERE m.id = :message_id
                LIMIT 1";

        return $this->fetch($sql, [
            ':message_id' => $messageId
        ]);
    }


    public function getNewTransmissions(
    int $channelId,
    int $afterMessageId
): array {
    $sql = "SELECT
                m.*,
                u.full_name AS sender_name,
                u.username AS sender_username,
                u.avatar AS sender_avatar,
                parent.message AS parent_message,
                parent_sender.full_name AS parent_sender_name
            FROM messages m
            INNER JOIN users u
                ON u.id = m.sender_id
            LEFT JOIN messages parent
                ON parent.id = m.parent_message_id
            LEFT JOIN users parent_sender
                ON parent_sender.id = parent.sender_id
            WHERE m.channel_id = :channel_id
              AND m.id > :after_message_id
              AND m.is_deleted = 0
            ORDER BY m.id ASC";

    return $this->fetchAll($sql, [
        ':channel_id' => $channelId,
        ':after_message_id' => $afterMessageId
    ]);
}




public function getChangedTransmissions(
    int $channelId,
    string $afterUpdatedAt
    ): array {
        $sql = "SELECT
                    m.id,
                    m.channel_id,
                    m.sender_id,
                    m.message,
                    m.is_edited,
                    m.edited_at,
                    m.is_deleted,
                    m.deleted_at,
                    m.updated_at
                FROM messages m
                WHERE m.channel_id = :channel_id
                AND m.updated_at > :after_updated_at
                ORDER BY m.updated_at ASC";

        return $this->fetchAll($sql, [
            ':channel_id' => $channelId,
            ':after_updated_at' => $afterUpdatedAt
        ]);
    }


    /*
|--------------------------------------------------------------------------
| Toggle message reaction
|--------------------------------------------------------------------------
*/

public function toggleReaction(
    int $messageId,
    int $userId,
    string $emoji
): array {
    $sql = "SELECT id
            FROM message_reactions
            WHERE message_id = :message_id
              AND user_id = :user_id
              AND emoji = :emoji
            LIMIT 1";

    $existing = $this->fetch($sql, [
        ':message_id' => $messageId,
        ':user_id' => $userId,
        ':emoji' => $emoji
    ]);

    if ($existing) {
        $deleted = $this->execute(
            "DELETE FROM message_reactions
             WHERE id = :id",
            [
                ':id' => (int) $existing['id']
            ]
        );

        return [
            'success' => $deleted,
            'action' => 'removed'
        ];
    }

    $inserted = $this->execute(
        "INSERT INTO message_reactions
        (
            message_id,
            user_id,
            emoji
        )
        VALUES
        (
            :message_id,
            :user_id,
            :emoji
        )",
        [
            ':message_id' => $messageId,
            ':user_id' => $userId,
            ':emoji' => $emoji
        ]
    );

    return [
        'success' => $inserted,
        'action' => 'added'
    ];
}


/*
|--------------------------------------------------------------------------
| Get reactions for one message
|--------------------------------------------------------------------------
*/

public function getMessageReactions(
    int $messageId,
    int $currentUserId
): array {
    $sql = "SELECT
                mr.emoji,
                COUNT(*) AS total,
                MAX(
                    CASE
                        WHEN mr.user_id = :current_user_id
                        THEN 1
                        ELSE 0
                    END
                ) AS reacted_by_current_user
            FROM message_reactions mr
            WHERE mr.message_id = :message_id
            GROUP BY mr.emoji
            ORDER BY MIN(mr.created_at) ASC";

    return $this->fetchAll($sql, [
        ':message_id' => $messageId,
        ':current_user_id' => $currentUserId
    ]);
}


/*
|--------------------------------------------------------------------------
| Get reactions for all messages in a Pod
|--------------------------------------------------------------------------
*/

public function getChannelReactions(
    int $channelId,
    int $currentUserId
): array {
    $sql = "SELECT
                mr.message_id,
                mr.emoji,
                COUNT(*) AS total,
                MAX(
                    CASE
                        WHEN mr.user_id = :current_user_id
                        THEN 1
                        ELSE 0
                    END
                ) AS reacted_by_current_user
            FROM message_reactions mr
            INNER JOIN messages m
                ON m.id = mr.message_id
            WHERE m.channel_id = :channel_id
              AND m.is_deleted = 0
            GROUP BY
                mr.message_id,
                mr.emoji
            ORDER BY
                mr.message_id ASC,
                MIN(mr.created_at) ASC";

    return $this->fetchAll($sql, [
        ':channel_id' => $channelId,
        ':current_user_id' => $currentUserId
    ]);
}



    public function getPodAttachments(int $channelId): array
    {
        $sql = "SELECT
                    id,
                    attachment_path
                FROM messages
                WHERE channel_id = :channel_id
                AND attachment_path IS NOT NULL
                AND attachment_path <> ''";

        return $this->fetchAll($sql, [
            ':channel_id' => $channelId
        ]);
    }

    public function clearPod(int $channelId): bool
        {
            $sql = "DELETE FROM messages
                    WHERE channel_id = :channel_id";

            return $this->execute($sql, [
                ':channel_id' => $channelId
            ]);
        }

}