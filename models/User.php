<?php
// models/User.php

namespace App;

use PDO;

class User {
    protected $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Holt alle Benutzer aus der Datenbank.
     *
     * @return array
     */
    public function getAllUsers() {
        $stmt = $this->pdo->query("SELECT * FROM users");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Findet einen Benutzer anhand des Benutzernamens.
     *
     * @param string $username
     * @return array|false
     */
    public function findByUsername($username) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
        $stmt->execute(['username' => $username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Erstellt einen neuen Benutzer.
     *
     * @param string $username
     * @param string $hashed_password
     * @param string $first_name
     * @param string $last_name
     * @param string $role
     * @return bool
     */
    public function create($username, $hashed_password, $first_name, $last_name, $role) {
        $stmt = $this->pdo->prepare("
            INSERT INTO users (username, password, first_name, last_name, role, created_at)
            VALUES (:username, :password, :first_name, :last_name, :role, NOW())
        ");
        return $stmt->execute([
            'username'   => $username,
            'password'   => $hashed_password,
            'first_name' => $first_name,
            'last_name'  => $last_name,
            'role'       => $role
        ]);
    }

    /**
     * Aktualisiert einen bestehenden Benutzer ohne Passwortänderung.
     *
     * @param int $id
     * @param string $username
     * @param string $first_name
     * @param string $last_name
     * @param string $role
     * @return bool
     */
    public function update($id, $username, $first_name, $last_name, $role) {
        $stmt = $this->pdo->prepare("
            UPDATE users
            SET username = :username, first_name = :first_name, last_name = :last_name, role = :role, updated_at = NOW()
            WHERE id = :id
        ");
        return $stmt->execute([
            'id'         => $id,
            'username'   => $username,
            'first_name' => $first_name,
            'last_name'  => $last_name,
            'role'       => $role
        ]);
    }

    /**
     * Aktualisiert einen bestehenden Benutzer inklusive Passwortänderung.
     *
     * @param int $id
     * @param string $username
     * @param string $hashed_password
     * @param string $first_name
     * @param string $last_name
     * @param string $role
     * @return bool
     */
    public function updateWithPassword($id, $username, $hashed_password, $first_name, $last_name, $role) {
        $stmt = $this->pdo->prepare("
            UPDATE users
            SET username = :username, password = :password, first_name = :first_name, last_name = :last_name, role = :role, updated_at = NOW()
            WHERE id = :id
        ");
        return $stmt->execute([
            'id'         => $id,
            'username'   => $username,
            'password'   => $hashed_password,
            'first_name' => $first_name,
            'last_name'  => $last_name,
            'role'       => $role
        ]);
    }

    /**
     * Löscht einen Benutzer anhand der ID.
     *
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
?>
