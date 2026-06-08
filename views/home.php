<?php $tenantName = (string) (($tenant['nome'] ?? 'Cliente')); ?>
<div class="survey-home">
<section class="hero card tenant-hero home-hero">
    <p class="eyebrow"><?= e(strtoupper($tenantName)) ?> · NR-1 · RISCOS PSICOSSOCIAIS</p>
    <h1>Pesquisa Psicossocial</h1>
    <p class="hero-seal">Canal anônimo · Dados agregados na gestão · Alinhado à NR-1</p>
    <p>
        Este portal permite que colaboradores respondam pesquisas sobre ambiente de trabalho, clima organizacional
        e fatores psicossociais. Suas respostas são tratadas com confidencialidade e, quando configurado como anônimo,
        não são associadas à sua identidade.
    </p>

    <div class="actions hero-actions">
        <?php if (!empty($survey)): ?>
            <a class="btn btn-primary btn-hero-main" href="<?= e(tenant_url('/responder')) ?>">Responder pesquisa</a>
            <span class="hero-survey-name"><?= e((string) $survey['titulo']) ?></span>
        <?php else: ?>
            <span class="btn btn-secondary disabled">Nenhuma pesquisa aberta no momento</span>
        <?php endif; ?>
        <a class="btn btn-ghost" href="<?= e(tenant_url('/politica')) ?>">Política de privacidade</a>
    </div>
</section>

<section class="stats-grid top-gap home-stats">
    <article class="stat-card">
        <strong>Anonimato</strong>
        <span>Respostas não identificam o colaborador quando a pesquisa está marcada como anônima.</span>
    </article>
    <article class="stat-card">
        <strong>Confidencialidade</strong>
        <span>Dados utilizados apenas para gestão de riscos ocupacionais e psicossociais.</span>
    </article>
    <article class="stat-card">
        <strong>NR-1</strong>
        <span>Contribui para o inventário de riscos e planos de ação da organização.</span>
    </article>
</section>

<?php if ($info = flash('info')): ?>
    <section class="card top-gap">
        <p class="muted"><?= e((string) $info) ?></p>
    </section>
<?php endif; ?>
</div>
