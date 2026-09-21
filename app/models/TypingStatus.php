<?php

class TypingStatus extends Model
{
    public function setTyping(
        int $channelId,
        int $userId,
        bool $isTyping
    ): bool {
        $sql = "INSERT INTO typing_status
                (
                    channel_id,
                    user_id,
                    is_typing,
                    updated_at
                )
                VALUES
                (
                    :channel_id,
                    :user_id,
                    :is_typing,
                    NOW()
                )
                ON DUPLICATE KEY UPDATE
                    is_typing = VALUES(is_typing),
                    updated_at = NOW()";

        return $this->execute($sql, [
            ':channel_id' => $channelId,
            ':user_id' => $userId,
            ':is_typing' => $isTyping ? 1 : 0
        ]);
    }

    public function getActiveTypers(
        int $channelId,
        int $excludeUserId
    ): array {
        $sql = "SELECT
                    ts.user_id,
                    u.full_name,
                    u.username
                FROM typing_status ts
                INNER JOIN users u
                    ON u.id = ts.user_id
                WHERE ts.channel_id = :channel_id
                  AND ts.user_id != :exclude_user_id
                  AND ts.is_typing = 1
                  AND ts.updated_at >= DATE_SUB(
                      NOW(),
                      INTERVAL 5 SECOND
                  )
                ORDER BY ts.updated_at DESC";

        return $this->fetchAll($sql, [
            ':channel_id' => $channelId,
            ':exclude_user_id' => $excludeUserId
        ]);
    }

    public function stopTyping(
        int $channelId,
        int $userId
    ): bool {
        return $this->setTyping(
            $channelId,
            $userId,
            false
        );
    }

    public function clearChannel(
        int $channelId
    ): bool {
        $sql = "DELETE FROM typing_status
                WHERE channel_id = :channel_id";

        return $this->execute($sql, [
            ':channel_id' => $channelId
        ]);
    }
}