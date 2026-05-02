<?php
/**
 * NoDB-DomainPark
 * Author: max-godman (max_godman@foxmail.com)
 * GitHub: https://github.com/max-godman
 * 
 * SHA256 encryption function
 * @param string $str Input string
 * @return string 32-character SHA256 hash
 */
function sha256_hash($str) {
    return hash('sha256', $str);
}
?>
