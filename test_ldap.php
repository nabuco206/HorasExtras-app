<?php
$host = '172.18.1.7';
$port = 389;

echo "PHP version: " . PHP_VERSION . PHP_EOL;
echo "ldap extension loaded: " . (extension_loaded('ldap') ? 'yes' : 'no') . PHP_EOL;
echo "function ldap_connect exists: " . (function_exists('ldap_connect') ? 'yes' : 'no') . PHP_EOL;

$ds = @ldap_connect($host, $port);
if (!$ds) {
    echo "ldap_connect returned false\n";
    exit(1);
}
ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
if (!@ldap_bind($ds)) {
    echo "ldap_bind failed: " . ldap_error($ds) . " (errno: " . ldap_errno($ds) . ")\n";
} else {
    echo "ldap_bind OK (anonymous)\n";
}
?>
