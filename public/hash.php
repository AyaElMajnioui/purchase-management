<?php
// Pour le ROLE_USER (nouveau mot de passe : user123)
echo "Hash pour user123 : " . password_hash('user123', PASSWORD_BCRYPT) . "<br>";

// Pour le ROLE_ADMIN (mot de passe : admin123)
echo "Hash pour admin123 : " . password_hash('admin123', PASSWORD_BCRYPT);

