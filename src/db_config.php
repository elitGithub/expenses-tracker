<?php

$con = mysqli_connect("localhost", "root", "", "expense_tracker");

// Check connection
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
} else {
    //echo "Connection on";
}
