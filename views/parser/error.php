<?php

use yii\bootstrap\Alert;

foreach ($errors as $messages) {
    foreach ($messages as $message);
    echo Alert::widget([
        'options' => [
            'class' => 'alert-danger',
        ],
        'body' => $message,
    ]);
}