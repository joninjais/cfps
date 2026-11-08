<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model frontend\models\SqlCode */
/* @var $form yii\widgets\ActiveForm */

// Load CodeMirror for SQL syntax highlighting
$this->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.css');
$this->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/theme/eclipse.min.css');
$this->registerJsFile('https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.js', ['position' => \yii\web\View::POS_HEAD]);
$this->registerJsFile('https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/sql/sql.min.js', ['position' => \yii\web\View::POS_HEAD, 'depends' => []]);
?>
<style>
.CodeMirror {
    font-family: 'Consolas', 'Courier New', monospace;
    font-size: 15px;
    line-height: 1.7;
    border: 1.5px solid #bdbdbd;
    border-radius: 4px;
    min-height: 200px;
    height: auto;
}
.CodeMirror-focused {
    border-color: #66afe9;
    box-shadow: inset 0 1px 1px rgba(0,0,0,.075), 0 0 8px rgba(102,175,233,.6);
}
.sql-label-row {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 4px;
}
.sql-label-row label {
    margin-bottom: 0;
    font-weight: 700;
    color: #31708f;
}
</style>
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
            'style' => 'display:none',
        ]) ?>
        <div id="sql-editor"></div>
        <div class="help-block"></div>
    </div>

    <?= $form->field($model, 'note')->textInput(['placeholder' => 'หมายเหตุ (ถ้ามี)']) ?>
    <div class="form-group">
        <?= Html::submitButton('<i class="fa fa-save"></i> บันทึก', ['class' => 'btn btn-success']) ?>
        <?= Html::a('<i class="fa fa-arrow-left"></i> กลับ', ['index'], ['class' => 'btn btn-default']) ?>
    </div>
    <?php ActiveForm::end(); ?>

    <?php $this->registerJs(<<<JS
    var sqlValue = document.getElementById('sqlcode-sql_command').value;
    var editor = CodeMirror(document.getElementById('sql-editor'), {
        value: sqlValue,
        mode: 'text/x-sql',
        theme: 'eclipse',
        lineNumbers: true,
        lineWrapping: true,
        indentWithTabs: false,
        tabSize: 4,
        autofocus: false,
        extraKeys: {"Tab": function(cm){ cm.replaceSelection("    "); }}
    });
    editor.setSize(null, 'auto');

    // Sync editor value back to hidden textarea before submit
    document.querySelector('form').addEventListener('submit', function() {
        document.getElementById('sqlcode-sql_command').value = editor.getValue();
    });

    // Format SQL button: uppercase keywords + basic indentation
    document.getElementById('btn-format-sql').addEventListener('click', function() {
        var sql = editor.getValue();
        var keywords = ['SELECT','FROM','WHERE','AND','OR','NOT','IN','JOIN','LEFT JOIN','RIGHT JOIN','INNER JOIN','ON','GROUP BY','ORDER BY','HAVING','LIMIT','OFFSET','INSERT INTO','VALUES','UPDATE','SET','DELETE FROM','CREATE TABLE','DROP TABLE','ALTER TABLE','UNION','UNION ALL','AS','DISTINCT','COUNT','SUM','AVG','MIN','MAX','CASE','WHEN','THEN','ELSE','END','IS NULL','IS NOT NULL','BETWEEN','LIKE','EXISTS'];
        keywords.sort(function(a,b){ return b.length - a.length; });
        keywords.forEach(function(kw) {
            var re = new RegExp('\\\\b' + kw.replace(/ /g,'\\\\s+') + '\\\\b', 'gi');
            sql = sql.replace(re, kw);
        });
        editor.setValue(sql);
    });

    // Copy button
    document.getElementById('btn-copy-sql').addEventListener('click', function() {
        var val = editor.getValue();
        if (navigator.clipboard) {
            navigator.clipboard.writeText(val);
        } else {
            document.getElementById('sqlcode-sql_command').value = val;
            document.getElementById('sqlcode-sql_command').select();
            document.execCommand('copy');
        }
        this.innerHTML = '<i class="fa fa-check"></i> คัดลอกแล้ว!';
        var btn = this;
        setTimeout(function(){ btn.innerHTML = '<i class="fa fa-copy"></i> คัดลอก'; }, 1500);
    });
    JS);
    ?>
</div>
