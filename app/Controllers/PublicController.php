<?php

declare(strict_types=1);

final class PublicController
{
    private PesquisaRepository $repo;

    public function __construct(private readonly array $tenant)
    {
        $this->repo = new PesquisaRepository(Database::connection());
    }

    public function home(): void
    {
        $survey = $this->repo->findActiveSurvey((int) $this->tenant['id']);

        View::render('home', [
            'title' => 'Pesquisa Psicossocial',
            'tenant' => $this->tenant,
            'survey' => $survey,
        ]);
    }

    public function respond(): void
    {
        $survey = $this->repo->findActiveSurvey((int) $this->tenant['id']);
        if ($survey === null) {
            flash('info', 'Não há pesquisa aberta no momento.');
            redirect(tenant_path());
        }

        $versao = (int) ($survey['versao'] ?? 1);
        $questions = $this->repo->listQuestions((int) $survey['id'], $versao);

        View::render('respond', [
            'title' => 'Responder pesquisa',
            'tenant' => $this->tenant,
            'survey' => $survey,
            'questions' => $questions,
            'csrf' => Security::csrfToken(),
            'formErrors' => errors(),
        ]);
    }

    public function submitRespond(): void
    {
        if (!Security::validateCsrf($_POST['_csrf'] ?? null)) {
            back_with_errors(['_form' => 'Sessão expirada. Tente novamente.'], tenant_path('/responder'));
        }

        $survey = $this->repo->findActiveSurvey((int) $this->tenant['id']);
        if ($survey === null) {
            back_with_errors(['_form' => 'Esta pesquisa não está mais disponível.'], tenant_path());
        }

        $versao = (int) ($survey['versao'] ?? 1);
        $questions = $this->repo->listQuestions((int) $survey['id'], $versao);
        $answers = [];
        $errors = [];

        foreach ($questions as $question) {
            $qid = (int) $question['id'];
            $tipo = (string) $question['tipo'];
            $required = (int) ($question['obrigatoria'] ?? 1) === 1;
            $field = 'q_' . $qid;
            $options = $question['opcoes'] ?? [];

            if (in_array($tipo, ['E', 'B', 'S'], true)) {
                $value = $_POST[$field] ?? '';
                if ($required && $value === '') {
                    $errors[$field] = 'Selecione uma opção.';
                    continue;
                }
                if ($value !== '') {
                    $answers[$qid] = ['texto' => null, 'numerico' => (float) $value, 'opcoes' => [(int) $value]];
                }
                continue;
            }

            if ($tipo === 'M') {
                $values = $_POST[$field] ?? [];
                if (!is_array($values)) {
                    $values = $values !== '' ? [(string) $values] : [];
                }
                $values = array_values(array_filter(array_map('strval', $values)));
                if ($required && !$values) {
                    $errors[$field] = 'Selecione ao menos uma opção.';
                    continue;
                }
                if ($values) {
                    $numeric = array_map('floatval', $values);
                    $answers[$qid] = [
                        'texto' => implode(', ', $values),
                        'numerico' => count($numeric) ? array_sum($numeric) / count($numeric) : null,
                        'opcoes' => array_map('intval', $values),
                    ];
                }
                continue;
            }

            $value = trim((string) ($_POST[$field] ?? ''));
            if ($required && $value === '') {
                $errors[$field] = 'Preencha este campo.';
                continue;
            }
            if ($value !== '') {
                $answers[$qid] = ['texto' => $value, 'numerico' => null];
            }
        }

        if ($errors) {
            with_old_input($_POST);
            back_with_errors($errors, tenant_path('/responder'));
        }

        if (!$answers) {
            back_with_errors(['_form' => 'Informe ao menos uma resposta.'], tenant_path('/responder'));
        }

        $token = Security::sessionToken();
        $this->repo->saveResponses(
            (int) $survey['id'],
            (int) $this->tenant['id'],
            $versao,
            $token,
            $answers
        );
        clear_old_input();
        redirect(tenant_path('/obrigado'));
    }

    public function thankyou(): void
    {
        View::render('thankyou', [
            'title' => 'Obrigado',
            'tenant' => $this->tenant,
        ]);
    }

    public function policy(): void
    {
        View::render('policy', [
            'title' => 'Política de privacidade',
            'tenant' => $this->tenant,
        ]);
    }
}
