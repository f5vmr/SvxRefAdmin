<?php
   $privateKeyFile = '/var/www/.ssh/id_rsa';
    $publicKeyFile = '/var/www/.ssh/id_rsa.pub';

    if (file_exists($privateKeyFile)) {
     echo "Private key exists<br>";
     } else {
         echo "Private key does not exist<br>";
     }
     if (file_exists($publicKeyFile)) {
        echo "Public key exists<br>";
     } else {
        echo "Public key does not exist<br>";
     }

     $handle = @fopen($privateKeyFile, "r");
     if ($handle) {
        echo "Private key readable<br>";
        fclose($handle);
      } else {
          echo "Private key unreadable<br>";
      }
