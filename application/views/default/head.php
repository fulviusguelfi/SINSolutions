<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<head>
    <?php
    $meta = array(
        array(
            'name' => 'description',
            'content' => 'SINSolutions teste'
        ),
        array(
            'name' => 'keywords',
            'content' => 'SINSolutions, teste'
        ),
        array(
            'name' => 'robots',
            'content' => 'no-cache'
        ),
        array(
            'name' => 'Content-type',
            'content' => 'text/html; charset=utf-8', 
            'type' => 'equiv'
        )
    );
    echo meta($meta);
    ?>
    <title><?php echo $title ?></title>
    <?php echo link_tag('css/style.css') ?>
</head>