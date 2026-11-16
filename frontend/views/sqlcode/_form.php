<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use frontend\assets\CodeMirrorAsset;

/* @var $this yii\web\View */
/* @var $model frontend\models\SqlCode */
/* @var $form yii\widgets\ActiveForm */

$codeMirrorPath = Yii::getAlias('@vendor/almasaeed2010/adminlte/bower_components/ckeditor/samples/toolbarconfigurator/lib/codemirror');
$hasCodeMirror = is_file($codeMirrorPath . '/codemirror.js')
    && is_file($codeMirrorPath . '/codemirror.css');
$sqlCodeCssPath = Yii::getAlias('@webroot/css/sqlcode.css');
$this->registerCssFile('@web/css/sqlcode.css?v=' . filemtime($sqlCodeCssPath), [
    'depends' => ['frontend\assets\CfpsThemeAsset'],
]);
if ($hasCodeMirror) {
    CodeMirrorAsset::register($this);
}
?>
<div class="sql-code-form">
    <?php $form = ActiveForm::begin(); ?>
    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'his')->textInput(['maxlength' => 100, 'placeholder' => 'เช่น JHCIS, HospitalOSV4']) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'type')->dropDownList([
                \frontend\models\SqlCode::TYPE_OP => 'OP — ผู้ป่วยนอก',
                \frontend\models\SqlCode::TYPE_IP => 'IP — ผู้ป่วยใน',
            ]) ?>
        </div>
    </div>
    <?= $form->field($model, 'title')->textInput(['placeholder' => 'ชื่อ/คำอธิบายคำสั่ง SQL']) ?>

    <div class="form-group field-sqlcode-sql_command">
        <div class="sql-label-row">
            <label for="sqlcode-sql_command">คำสั่ง SQL</label>
            <button type="button" class="btn btn-xs btn-info" id="btn-format-sql"><i class="fa fa-indent"></i> จัดรูปแบบ SQL</button>
            <button type="button" class="btn btn-xs btn-default" id="btn-copy-sql"><i class="fa fa-copy"></i> คัดลอก</button>
        </div>
        <?= Html::textarea('SqlCode[sql_command]', $model->sql_command, [
            'id' => 'sqlcode-sql_command',
            'name' => 'SqlCode[sql_command]',
            'rows' => 18,
            'class' => 'form-control sql-command-fallback',
        ]) ?>
        <div id="sql-editor" hidden></div>
        <div class="help-block"></div>
    </div>

    <?= $form->field($model, 'note')->textInput(['placeholder' => 'หมายเหตุ (ถ้ามี)']) ?>
    <div class="form-group">
        <?= Html::submitButton('<i class="fa fa-save"></i> บันทึก', ['class' => 'btn btn-success']) ?>
        <?= Html::a('<i class="fa fa-arrow-left"></i> กลับ', ['index'], ['class' => 'btn btn-default']) ?>
    </div>
    <?php ActiveForm::end(); ?>

    <?php $this->registerJs(<<<JS
    var textarea = document.getElementById('sqlcode-sql_command');
    var editorElement = document.getElementById('sql-editor');
    var editor = null;
    if (typeof CodeMirror === 'function') {
        editorElement.hidden = false;
        editor = CodeMirror(editorElement, {
            value: textarea.value,
            lineNumbers: true,
            lineWrapping: true,
            indentWithTabs: false,
            tabSize: 4,
            autofocus: false,
            extraKeys: {"Tab": function(cm){ cm.replaceSelection("    "); }}
        });
        editor.setSize(null, 'auto');
        textarea.hidden = true;
        textarea.style.display = 'none';
        editor.refresh();
        window.requestAnimationFrame(function() {
            editor.refresh();
        });
    }

    function getSqlValue() {
        return editor ? editor.getValue() : textarea.value;
    }

    function setSqlValue(value) {
        if (editor) {
            editor.setValue(value);
        } else {
            textarea.value = value;
        }
    }

    // Sync editor value back to hidden textarea before submit
    document.querySelector('.sql-code-form form').addEventListener('submit', function() {
        textarea.value = getSqlValue();
    });

    // Format SQL button: uppercase keywords + basic indentation
    document.getElementById('btn-format-sql').addEventListener('click', function() {
        var sql = getSqlValue();
        var keywords = ['SELECT','FROM','WHERE','AND','OR','NOT','IN','JOIN','LEFT JOIN','RIGHT JOIN','INNER JOIN','ON','GROUP BY','ORDER BY','HAVING','LIMIT','OFFSET','INSERT INTO','VALUES','UPDATE','SET','DELETE FROM','CREATE TABLE','DROP TABLE','ALTER TABLE','UNION','UNION ALL','AS','DISTINCT','COUNT','SUM','AVG','MIN','MAX','CASE','WHEN','THEN','ELSE','END','IS NULL','IS NOT NULL','BETWEEN','LIKE','EXISTS'];
        keywords.sort(function(a,b){ return b.length - a.length; });
        keywords.forEach(function(kw) {
            var re = new RegExp('\\\\b' + kw.replace(/ /g,'\\\\s+') + '\\\\b', 'gi');
            sql = sql.replace(re, kw);
        });
        setSqlValue(sql);
    });

    // Copy button
    document.getElementById('btn-copy-sql').addEventListener('click', function() {
        var val = getSqlValue();
        if (navigator.clipboard) {
            navigator.clipboard.writeText(val);
        } else {
            textarea.value = val;
            textarea.select();
            document.execCommand('copy');
        }
        this.innerHTML = '<i class="fa fa-check"></i> คัดลอกแล้ว!';
        var btn = this;
        setTimeout(function(){ btn.innerHTML = '<i class="fa fa-copy"></i> คัดลอก'; }, 1500);
    });
    JS);
    ?>
</div>
