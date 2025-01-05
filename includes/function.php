<?php
function authenticate($username, $password, $conn) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }
    return false;
}

function register_user($username, $email, $password, $role, $conn) {
    try {

        if(empty($role)){
            $role = 'user';
        }
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (:username, :email, :password, :role)");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':role', $role);
        return $stmt->execute();
    } catch (PDOException $e) {
        return false;
    }
}

function get_all_users($conn) {
    // Ambil semua pengguna kecuali superuser
    $stmt = $conn->prepare("SELECT id, username, email, role FROM users WHERE role != 'superuser'");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


function update_user_role($user_id, $new_role, $conn) {
    try {
        $stmt = $conn->prepare("UPDATE users SET role = :role WHERE id = :id");
        $stmt->bindParam(':role', $new_role);
        $stmt->bindParam(':id', $user_id);
        return $stmt->execute();
    } catch (PDOException $e) {
        return false;
    }
}

?>


