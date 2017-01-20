<?php
    // @var string user's email
    $email = htmlspecialchars(trim($_POST["newsletter_email"]));

    // @var string message in case of processing error
    $errorMsg = "";

    // @var array MySQL config
    $mysql_config = array(
        "host"  => "localhost",
        "user"  => "root",
        "pass"  => "",
        "db"    => "newsletter",
        "table" => "emails"
    );

    // @var boolean development mode
    $isDevEnv = TRUE;

    // Step 1
    // Validation
    if (empty($email)) {
        $errorMsg = "E-mail is empty. Please, re-enter an e-mail address.";
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMsg = "Invalid email."; 
    }

    // Stop here if Step 1 fails
    if ($errorMsg) {
        die($errorMsg);
    }

    // Step 2
    // Connection with the DB (Note the use of "@": not to show errors coming from functions)
    // http://ca1.php.net/manual/en/mysqli.quickstart.php
    $mysql = @new mysqli(
        $mysql_config["host"],
        $mysql_config["user"],
        $mysql_config["pass"],
        $mysql_config["db"]
    );

    // Check connection
    if ($mysql->connect_error) {
        $errorMsg = "Connection failed.";
        if ($isDevEnv) {
            $errorMsg .= " (Error: {$mysql->connect_error})";
        }
        die($errorMsg);
    }

    // Step 4
    // Verify if the email already exists
    $check = "SELECT email
                FROM emails 
                WHERE email = '{$email}'";
    $result = $mysql->query($check);

    // Error message, if the varible $result does not exist
    // The variable $result exists and the value of the variable $result is false
    if (!$result) {
        $errorMsg = "The server couldn\'t understand your request.";
        if ($isDevEnv) {
            // Once we are using the objet mysqli in the variable $mysql,
            // it will be resposible for keeping the error messages as well
            $errorMsg .= " (Query: $check). (Error: {$mysql->error}).";
        }
        die($errorMsg);
    }

    // Step 5
    // Includes the option remove email
    $checkbox = isset($_POST["delete_email"]);

    if ($checkbox == "Yes") {
        $delete = "DELETE FROM emails
                    WHERE email = '{$email}'";

        // Checar se o valor foi deletado na tabela com sucesso
        if ($mysql->query($delete) !== TRUE) {
            $errorMsg = "Sorry, your record couldn't be deleted.";
            if ($isDevEnv) {
                $errorMsg .= " (Query: {$delete}) (Error: {$mysql->error})";
            }
        }
    }

    // Check if the email already exists in the DB
    // Here is a possible error "expects paramater 1 to be a resource"
    // if (mysqli_num_rows($result) > 0) {
    
    // $result was created in the Step 4
    else if ($result->num_rows > 0) {
        $errorMsg = "This email address is already in the database. Please, check your email.";
        $result->close();    // Open resources from server without closing mysqli connection
    } 
    else {
        // Step 3
        // Inserting values from user
        $insert = "INSERT INTO
            emails (email, data_hora)
            VALUES ('{$email}', NOW())";

        // Check if the value was saved successfully in the DB
        if ($mysql->query($insert) !== TRUE) {
            $errorMsg = "Sorry, your record couldn't be created.";
            if ($isDevEnv) {
                $errorMsg .= " (Query: {$insert}) (Error: {$mysql->error})";
            }
        }
    }
    

    // Close connection with the DB
    $mysql->close();

    // Send the last message to user
    echo (($errorMsg) ? $errorMsg : "Success");
?>