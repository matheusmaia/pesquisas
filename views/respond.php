<?php
$formErrors = $formErrors ?? [];
?>
<section class="card survey-card">
    <header class="survey-header">
        <h1><?= e((string) ($survey['titulo'] ?? 'Pesquisa')) ?></h1>
        <?php if (!empty($survey['descricao'])): ?>
            <p class="survey-intro"><?= e((string) $survey['descricao']) ?></p>
        <?php endif; ?>
        <p class="survey-privacy">Suas respostas são <?= ((int) ($survey['anonima'] ?? 1) === 1) ? 'anônimas' : 'confidenciais' ?>.</p>
    </header>

    <?php if (!empty($formErrors['_form'])): ?>
        <div class="alert alert-error"><?= e((string) $formErrors['_form']) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= e(tenant_url('/responder' . (!empty($conviteToken) ? '?token=' . urlencode((string) $conviteToken) : ''))) ?>" class="top-gap">
        <input type="hidden" name="_csrf" value="<?= e($csrf ?? '') ?>">
        <?php if (!empty($conviteToken)): ?>
            <input type="hidden" name="convite_token" value="<?= e((string) $conviteToken) ?>">
        <?php endif; ?>

        <?php foreach ($questions as $question): ?>
            <?php
            $qid = (int) $question['id'];
            $field = 'q_' . $qid;
            $tipo = (string) $question['tipo'];
            $required = (int) ($question['obrigatoria'] ?? 1) === 1;
            $error = $formErrors[$field] ?? '';
            $options = $question['opcoes'] ?? [];
            ?>
            <div class="field-block">
                <?php if (!empty($question['grupo_nome'])): ?>
                    <span class="survey-dimension"><?= e((string) $question['grupo_nome']) ?></span>
                <?php endif; ?>
                <p class="survey-question" id="label-<?= e($field) ?>">
                    <?= e((string) $question['enunciado']) ?>
                    <?= $required ? '<span class="required">*</span>' : '' ?>
                </p>

                <?php if (in_array($tipo, ['E', 'B', 'S'], true)): ?>
                    <div class="scale-options" role="group" aria-labelledby="label-<?= e($field) ?>">
                        <?php foreach ($options as $option): ?>
                            <?php
                            $val = (string) ($option['valor_numerico'] ?? $option['id'] ?? '');
                            $label = (string) ($option['rotulo'] ?? $val);
                            ?>
                            <label class="scale-option">
                                <input type="radio" name="<?= e($field) ?>" value="<?= e($val) ?>" <?= old($field) === $val ? 'checked' : '' ?>>
                                <span><?= e($label) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                <?php elseif ($tipo === 'M'): ?>
                    <?php foreach ($options as $option): ?>
                        <?php
                        $val = (string) ($option['valor_numerico'] ?? $option['id'] ?? '');
                        $label = (string) ($option['rotulo'] ?? $val);
                        $oldValues = (array) old($field, []);
                        ?>
                        <label class="checkbox-option">
                            <input type="checkbox" name="<?= e($field) ?>[]" value="<?= e($val) ?>" <?= in_array($val, $oldValues, true) ? 'checked' : '' ?>>
                            <?= e($label) ?>
                        </label>
                    <?php endforeach; ?>
                <?php else: ?>
                    <textarea name="<?= e($field) ?>" id="<?= e($field) ?>" rows="4" class="survey-textarea"><?= old($field) ?></textarea>
                <?php endif; ?>

                <?php if ($error !== ''): ?>
                    <small class="field-error"><?= e($error) ?></small>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <div class="actions top-gap">
            <button type="submit" class="btn btn-primary">Enviar respostas</button>
            <a class="btn btn-secondary" href="<?= e(tenant_url()) ?>">Voltar</a>
        </div>
    </form>
</section>
