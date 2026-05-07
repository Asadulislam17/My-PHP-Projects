<?php
require_once 'StorageInterface.php';
require_once 'FileStorage.php';
require_once 'UserManager.php';

$fileDb = new FileStorage('users.txt');
$userManager = new Student($fileDb);

// সেভ করার লজিক (Warning দূর করার জন্য isset ব্যবহার করা হয়েছে)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save'])) {
    $userManager->addUser($_POST['id'], $_POST['name'], $_POST['batch']);
}
?>

<!DOCTYPE html>
<html>
<body>
    <div>
        <h1>Student Form</h1>
        <form method="post">
            <label>ID:</label>
            <input type="text" name="id" placeholder="Id" required>
            <label>Name:</label>
            <input type="text" name="name" placeholder="Name" required>
            <label>Batch:</label>
            <input type="text" name="batch" placeholder="Batch" required>
            <button type="submit" name="save">Save</button>
        </form>

        <h3>Search Student (By ID):</h3>
        <form method="post">
            <label>Student ID:</label>
            <input type="text" name="search_id" placeholder="Enter ID" required>
            <button type="submit" name="search_btn">Search</button>
        </form>
    </div>

    <?php
    if (isset($_POST["search_btn"])) {
        $result = $userManager->re($_POST['search_id']);

        if ($result) {
            echo "<h4>Result Found:</h4>";
            echo "<ul><li>" . htmlspecialchars($result) . "</li></ul>";
        } else {
            echo "<p style='color:red;'>Not Found!</p>";
        }
    }
    ?>
</body>
</html>
