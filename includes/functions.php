<?php
function sanitizeInput($data)
{
    return htmlspecialchars(stripslashes(trim($data)));
}

function checkAuth($data)
{
    echo "<pre>";
    var_dump($data);
    echo "</pre>";
    die();
}

function _dd($data)
{
    echo "<pre>";
    var_dump($data);
    echo "</pre>";
    die();
}