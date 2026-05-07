<?php
    require_once 'App/ProfileA.php';
    require_once 'App/ProfileU.php';

    use App\Admin\Profile as AdminProfile;
    use App\User\Profile as UserProfile;

    $admin = new AdminProfile();
    $user = new UserProfile();

    $admin->show();
    echo "<br>";
    $user->show();


?>