<?php
require __DIR__ . '/vendor/autoload.php';

var_dump(
    class_exists('\MercadoPago\SDK'),
    class_exists('\MercadoPago\Preference'),
    class_exists('\MercadoPago\Item')
);