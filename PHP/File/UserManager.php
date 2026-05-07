<?php
class UserManager {
    private $storage;

    public function __construct(StorageInterface $storage) {
        $this->storage = $storage;
    }

    public function addUser($name, $email) {
        $userData = [
            'name' => $name,
            'email' => $email
        ];
        $this->storage->save($userData);
    }

    public function getUsers() {
        return $this->storage->getAll();
    }

    public function login($username, $password) {

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $users = $this->storage->getAll();
        $found = false; 

        foreach ($users as $userLine) {
            $userData = explode(" | ", $userLine);
            
            
            $storedUsername = isset($userData[0]) ? trim($userData[0]) : '';
            $storedPassword = isset($userData[1]) ? trim($userData[1]) : '';

            if ($username === $storedUsername && $password === $storedPassword) {
                $found = true;
                break; 
            }
        }

        if ($found) {
            
            $_SESSION['user_name'] = $username;
            $_SESSION['is_logged_in'] = true;
            header('Location: file_set_location.php');
            exit; 
        } else {
            
            //  $this->addUser($username, $password);
            echo "Invalid user Name or Password.";
            
        }
    }


}

/*class UserManager {
    private $storage;

    public function __construct(StorageInterface $storage) {
        $this->storage = $storage;
    }

    // পাসওয়ার্ড হ্যাশ করে সেভ করা
    public function addUser($name, $email, $password) {
        $userData = [
            'name' => $name,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT) // সিকিউর পাসওয়ার্ড
        ];
        $this->storage->save($userData);
    }

    public function login($email, $password) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $users = $this->storage->getAll();

        foreach ($users as $userLine) {
            $userData = explode(" | ", $userLine);
            
            // ডাটা ফরম্যাট অনুযায়ী ইনডেক্স ঠিক রাখা
            $storedEmail = isset($userData[1]) ? trim($userData[1]) : '';
            $storedHash  = isset($userData[2]) ? trim($userData[2]) : '';

            // ইমেইল মিললে পাসওয়ার্ড ভেরিফাই করা
            if ($email === $storedEmail && password_verify($password, $storedHash)) {
                $_SESSION['user_email'] = $email;
                $_SESSION['is_logged_in'] = true;
                header('Location: main.php');
                exit;
            }
        }

        return "ভুল ইমেইল অথবা পাসওয়ার্ড!";
    }
}*/
?>
