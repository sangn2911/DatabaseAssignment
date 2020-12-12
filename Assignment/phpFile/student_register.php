<?php
include 'util/config.php';
include 'util/const.php';

//Session start
session_start();

// Get phone number
$username = $_SESSION['curr_username'];
$_SESSION['curr_user_role'] = studentType;
$user_role = studentType;

// Send phone number back to front end
if ($_SERVER['REQUEST_METHOD'] == 'GET') {

    if (is_null($username)) return;
    else {
        $json_obj = array(
            'username' => $username,
            'user_role' => $user_role,
        );
        $jsonData = json_encode($json_obj);
        echo $jsonData;
    }

    return;
}

//Check if user does NOT provide enough infomation
if (
    !isset($_POST['password']) || !isset($_POST['fullname'])
    || !isset($_POST['email'])
) {
    $json_obj = array(
        'status_code' => notEnoughInfo,
    );
    $jsonData = json_encode($json_obj);
    echo $jsonData;
    return;
} elseif (
    $_POST['password'] == '' || $_POST['fullname'] == ''
    || $_POST['email'] == ''
) {
    $json_obj = array(
        'status_code' => notEnoughInfo,
    );
    $jsonData = json_encode($json_obj);
    echo $jsonData;
    return;
}

//Get Connection with database
$mysqlConnect = require_once('../phpFile/connect_to_db.php');

add_patient(
    $mysqlConnect,
    $username,
    $_POST['password'],
    $_POST['fullname'],
    $_POST['email']
);

// Add new patient to database
function add_patient(
    $mysqlConnect,
    $username,
    $password,
    $fullname,
    $email
) {
    // Prepare and bind
    $sqlQuery = $mysqlConnect->prepare("INSERT INTO student(Username, Password, FullName, Email) VALUES (?, ?, ?, ?)");
    $sqlQuery->bind_param("ssss", $username, $password, $fullname, $email);
    $sqlQuery->execute();

    if ($mysqlConnect->errno == 1062) {
        $json_obj = array(
            'status_code' => failToAdd,
            'error' =>
            "Connect Error: " . $mysqlConnect->errno
                . "Bind Error: " . $sqlQuery->errno
                . "Execute Error: " . $sqlQuery->errno,
        );
        $jsonData = json_encode($json_obj);
        echo $jsonData;
        return;
    };

    // Send response back
    $json_obj = array(
        'status_code' => addUserSuccessfully,
    );
    $jsonData = json_encode($json_obj);
    echo $jsonData;

    // Close
    $sqlQuery->close();
    $mysqlConnect->close();
}
