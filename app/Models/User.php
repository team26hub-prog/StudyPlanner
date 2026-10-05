<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class User
{
    public function allWithSummaries(): array
    {
        $statement = Database::connection()->query(
            'SELECT u.id, u.name, u.email, u.role, u.created_at,
                (SELECT COUNT(*) FROM subjects s WHERE s.user_id = u.id) AS subject_count,
                (SELECT COUNT(*) FROM tasks t WHERE t.user_id = u.id) AS task_count,
                (SELECT COUNT(*) FROM tasks t WHERE t.user_id = u.id AND t.status = "completed") AS completed_count
             FROM users u ORDER BY u.created_at DESC'
        );

        return $statement->fetchAll();
    }

    public function findWithSummary(int $id): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT u.id, u.name, u.email, u.role, u.created_at,
                (SELECT COUNT(*) FROM subjects s WHERE s.user_id = u.id) AS subject_count,
                (SELECT COUNT(*) FROM tasks t WHERE t.user_id = u.id) AS task_count,
                (SELECT COUNT(*) FROM tasks t WHERE t.user_id = u.id AND t.status = "completed") AS completed_count
             FROM users u WHERE u.id = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $user = $statement->fetch();

        return $user ?: null;
    }

    public function adminStats(): array
    {
        $statement = Database::connection()->query(
            'SELECT COUNT(*) AS user_count,
                (SELECT COUNT(*) FROM subjects) AS subject_count,
                (SELECT COUNT(*) FROM tasks) AS task_count,
                (SELECT COUNT(*) FROM tasks WHERE status = "completed") AS completed_count
             FROM users'
        );

        return array_map('intval', $statement->fetch() ?: []);
    }

    public function findByEmail(string $email): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT id, name, email, password_hash, role FROM users WHERE email = :email LIMIT 1'
        );
        $statement->execute(['email' => strtolower($email)]);
        $user = $statement->fetch();

        return $user ?: null;
    }

    public function create(string $name, string $email, string $password): int
    {
        $statement = Database::connection()->prepare(
            'INSERT INTO users (name, email, password_hash, role) VALUES (:name, :email, :password_hash, "user")'
        );
        $statement->execute([
            'name' => $name,
            'email' => strtolower($email),
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        ]);

        return (int) Database::connection()->lastInsertId();
    }

    public function deleteUser(int $id): void
    {
        $statement = Database::connection()->prepare(
            'DELETE FROM users WHERE id = :id AND role = "user"'
        );
        $statement->execute(['id' => $id]);
    }
}