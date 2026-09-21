<?php

class User extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Find user by email
    |--------------------------------------------------------------------------
    */

    public function findByEmail($email)
    {
        $sql = "SELECT *
                FROM users
                WHERE email = :email
                LIMIT 1";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':email' => $email
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /*
    |--------------------------------------------------------------------------
    | Find user by username
    |--------------------------------------------------------------------------
    */

    public function findByUsername($username)
    {
        $sql = "SELECT *
                FROM users
                WHERE username = :username
                LIMIT 1";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':username' => $username
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /*
    |--------------------------------------------------------------------------
    | Create account
    |--------------------------------------------------------------------------
    */

    public function create($data)
    {
        $sql = "INSERT INTO users
                (
                    full_name,
                    username,
                    email,
                    password
                )
                VALUES
                (
                    :full_name,
                    :username,
                    :email,
                    :password
                )";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([

            ':full_name' => $data['full_name'],

            ':username' => $data['username'],

            ':email' => $data['email'],

            ':password' => password_hash(
                $data['password'],
                PASSWORD_DEFAULT
            )

        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Login
    |--------------------------------------------------------------------------
    */

    public function login($email, $password)
    {
        $user = $this->findByEmail($email);

        if (!$user) {
            return false;
        }

        if (!password_verify(
            $password,
            $user['password']
        )) {
            return false;
        }

        return $user;
    }

    /*
    |--------------------------------------------------------------------------
    | Find by ID
    |--------------------------------------------------------------------------
    */

    public function find($id)
    {
        $sql = "SELECT *
                FROM users
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':id' => $id
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /*
|--------------------------------------------------------------------------
| Update user presence heartbeat
|--------------------------------------------------------------------------
*/

public function updatePresence(int $userId): bool
{
    $sql = "UPDATE users
            SET
                status = 'online',
                last_seen = NOW()
            WHERE id = :user_id";

    $stmt = $this->db->prepare($sql);

    return $stmt->execute([
        ':user_id' => $userId
    ]);
}


/*
|--------------------------------------------------------------------------
| Mark user offline
|--------------------------------------------------------------------------
*/

public function markOffline(int $userId): bool
{
    $sql = "UPDATE users
            SET
                status = 'offline',
                last_seen = NOW()
            WHERE id = :user_id";

    $stmt = $this->db->prepare($sql);

    return $stmt->execute([
        ':user_id' => $userId
    ]);
}


/*
|--------------------------------------------------------------------------
| Get Alien crew presence
|--------------------------------------------------------------------------
*/

public function getAlienCrewPresence(
    int $teamId
): array {
    $sql = "SELECT
                u.id,
                u.full_name,
                u.username,
                u.avatar,
                u.status,
                u.last_seen,
                tm.role,

                CASE
                    WHEN u.last_seen IS NOT NULL
                     AND u.last_seen >= DATE_SUB(
                         NOW(),
                         INTERVAL 20 SECOND
                     )
                    THEN 1
                    ELSE 0
                END AS is_online

            FROM team_members tm

            INNER JOIN users u
                ON u.id = tm.user_id

            WHERE tm.team_id = :team_id

            ORDER BY
                is_online DESC,
                u.full_name ASC";

    $stmt = $this->db->prepare($sql);

    $stmt->execute([
        ':team_id' => $teamId
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/*
|--------------------------------------------------------------------------
| Update Password
|--------------------------------------------------------------------------
*/

        public function updatePassword(
            int $userId,
            string $password
        ): bool {

            $sql = "UPDATE users
                    SET password = :password
                    WHERE id = :id";

            $stmt =
                $this->db->prepare(
                    $sql
                );

            return $stmt->execute([
                ':password' =>
                    password_hash(
                        $password,
                        PASSWORD_DEFAULT
                    ),

                ':id' =>
                    $userId
            ]);
        }





}