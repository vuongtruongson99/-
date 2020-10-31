<?php
function makeUser($user) {
    if (!is_string($user)) {
        echo "Error: wrong type of user!";
        exit(1);
    }
    $home = '/home/' . $user;
    $privateKey = $home . "/.shh/id_ed25519";
    $publicKey = $privateKey . ".pub";

    return ["home" => $home, "privateKey" => $privateKey, "publicKey" => $publicKey];
}
?>