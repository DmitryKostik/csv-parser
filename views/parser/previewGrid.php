<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap\ActiveForm $form */
/** @var app\models\PreviewForm $model */

use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
$preview = $model->preview;
$columnsCount = $model->columnsCount;
?>

<div class="form">
    <?php $form = ActiveForm::begin([
        'id' => 'preview-form',
        'layout' => 'horizontal',
        'action' => Url::toRoute(['parser/import'])
    ]); ?>
    <?= $form->field($model, 'filePath')->hiddenInput(['value' => $model->filePath])->label(false); ?>
    <?= $form->field($model, 'delimiter')->hiddenInput(['value' => $model->delimiter])->label(false); ?>
    <?= $form->field($model, 'encoding')->hiddenInput(['value' => $model->encoding])->label(false); ?>
    <table class="table">
        <thead>
            <tr>
            <?php for ($i = 0; $i < $columnsCount; $i++): ?>
                <td>
                <?= $form->field($model, "columnMap[$i]")->dropDownList($attributes, ['prompt' => "Выберите столбец"])->label(false); ?>
                </td>
            <?php endfor; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach($preview as $row) : ?>
                <tr>
                    <?php foreach($row as $column): ?>
                        <td><?= $column ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <button type="submit">Импортировать</button>
    
    <?php ActiveForm::end(); ?>
</div>
