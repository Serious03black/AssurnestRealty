<?php

// ────────────────────────────────────────────────
//       CHANGE ONLY THIS LINE ↓↓↓
// ────────────────────────────────────────────────
$your_plain_password = '000000';     // ← Put your desired password here

// ────────────────────────────────────────────────
//       Do NOT change anything below
// ────────────────────────────────────────────────

$hash = password_hash($your_plain_password, PASSWORD_DEFAULT);

echo "<pre>";
echo "Your plain password was: " . htmlspecialchars($your_plain_password) . "\n\n";
echo "Generated hash (copy this completely):\n";
echo $hash . "\n\n";
echo "Use it like this in SQL:\n";
echo "INSERT INTO users (admin_id, admin_name,user, password)\n";
echo "VALUES ('datalogger', 'datalogger', 'admin','$hash');\n";
echo "</pre>";

?>